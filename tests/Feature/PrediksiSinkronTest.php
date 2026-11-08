<?php

/*
 * Prediksi risiko (Skor Penyimpangan dari Baseline) dijalankan melalui queue,
 * bukan di dalam request HTTP.
 *
 * Test di file ini menjaga request tetap singkat dan hasil job tetap tercatat.
 *
 * Prefix helper: `predSync` (wajib unik - fungsi di file Pest bersifat global).
 */

use App\Jobs\RunPrediksiJob;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\KlasifikasiRisikoService;
use App\Services\PrediksiMendatangService;
use App\Services\PrediksiRisikoService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
});

/** Siswa anggota kelas. */
function predSyncSiswa(Kelas $kelas): User
{
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $siswa;
}

/** Beri siswa satu kuis selesai + satu tugas terdinilai di pertemuan itu. */
function predSyncData(Kelas $kelas, User $siswa, int $pertemuanId, int $nilaiKuis = 80, int $nilaiTugas = 80): void
{
    $kuis = Kuis::create([
        'judul' => 'Kuis '.fake()->word(),
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuanId,
        'soal' => [],
        'is_aktif' => false,
        'tanggal_selesai' => now()->subDay(),
    ]);
    KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $siswa->id, 'skor' => $nilaiKuis]);

    $tugas = Tugas::create([
        'judul' => 'Tugas '.fake()->word(),
        'kelas_id' => $kelas->id,
        'guru_id' => $kelas->guru_id,
        'pertemuan_id' => $pertemuanId,
        'tipe' => 'upload',
        'deadline' => now()->subDay(),
    ]);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id, 'siswa_id' => $siswa->id, 'nilai' => $nilaiTugas, 'terlambat' => false,
    ]);
}

/** Siswa dengan data lengkap sehingga snapshot prediksi_ai ditulis. */
function predSyncSiswaLengkap(Kelas $kelas, int $pertemuanId): User
{
    $siswa = predSyncSiswa($kelas);
    predSyncData($kelas, $siswa, $pertemuanId);

    return $siswa;
}

/** Isi HTML sesudah kemunculan pertama $needle. */
function predSyncSesudah(string $html, string $needle, int $panjang = 2500): string
{
    $posisi = strpos($html, $needle);

    return $posisi === false ? '' : substr($html, $posisi, $panjang);
}

// -------------------------------------------------------------------------
// Kontrak skema. WAJIB ada: SQLite meloloskan INSERT ganda ke kolom yang
// tidak punya unique index, jadi test inilah satu-satunya penjaga yang benar
// -benar bisa membedakan di sini.
// -------------------------------------------------------------------------

test('prediksi_ai punya unique constraint pada siswa_id', function () {
    $index = collect(Schema::getIndexes('prediksi_ai'))
        ->first(fn ($i) => $i['unique'] && $i['columns'] === ['siswa_id']);

    expect($index)->not->toBeNull('unique index prediksi_ai(siswa_id) tidak ada');
});

test('prediksi_risiko_pertemuan unik per (siswa, pertemuan)', function () {
    $index = collect(Schema::getIndexes('prediksi_risiko_pertemuan'))
        ->first(fn ($i) => $i['unique'] && $i['columns'] === ['siswa_id', 'pertemuan_id']);

    expect($index)->not->toBeNull('unique index prediksi_risiko_pertemuan(siswa_id, pertemuan_id) tidak ada');
});

test('siswa yang sama tidak bisa punya dua baris prediksi', function () {
    $siswa = predSyncSiswa($this->kelas);

    PrediksiAi::create(['siswa_id' => $siswa->id, 'hasil' => 'aman', 'skor_risiko' => 20]);

    expect(fn () => DB::table('prediksi_ai')->insert([
        'siswa_id' => $siswa->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 90,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

// -------------------------------------------------------------------------
// Orchestrator
// -------------------------------------------------------------------------

test('jalankan dua kali untuk siswa yang sama tidak menambah baris prediksi', function () {
    predSyncSiswaLengkap($this->kelas, $this->pertemuan->id);
    $svc = app(PrediksiRisikoService::class);

    $svc->jalankan();
    $svc->jalankan();

    expect(PrediksiAi::count())->toBe(1);
    expect(PrediksiRisikoPertemuan::count())->toBe(1);
});

test('jalankan memperbarui baris yang sudah ada, bukan menyisipkan yang baru', function () {
    $siswa = predSyncSiswaLengkap($this->kelas, $this->pertemuan->id);
    $lama = PrediksiAi::create([
        'siswa_id' => $siswa->id, 'hasil' => 'aman', 'skor_risiko' => 1,
    ]);

    app(PrediksiRisikoService::class)->jalankan();

    expect(PrediksiAi::count())->toBe(1);
    expect(PrediksiAi::first()->id)->toBe($lama->id);
    expect((float) PrediksiAi::first()->skor_risiko)->not->toBe(1.0);
});

test('jalankan tidak menulis snapshot untuk data yang tidak lengkap', function () {
    // Siswa tanpa kuis/tugas: semua parameter null -> status data_tidak_lengkap.
    predSyncSiswa($this->kelas);

    app(PrediksiRisikoService::class)->jalankan();

    expect(PrediksiRisikoPertemuan::count())->toBe(1);
    expect(PrediksiRisikoPertemuan::first()->status_data)->toBe('data_tidak_lengkap');
    expect(PrediksiAi::count())->toBe(0);
});

// -------------------------------------------------------------------------
// Controller sinkron
// -------------------------------------------------------------------------

test('route run menjadwalkan satu job dan menampilkan status antrean', function () {
    Queue::fake();
    predSyncSiswaLengkap($this->kelas, $this->pertemuan->id);

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.run'))
        ->assertRedirect()
        ->assertSessionHas('info');

    Queue::assertPushedTimes(RunPrediksiJob::class, 1);
    expect(cache()->get(RunPrediksiJob::STATUS_CACHE_KEY)['status'])->toBe('queued');
});

test('route run tidak menghitung prediksi di dalam request', function () {
    $siswa = predSyncSiswaLengkap($this->kelas, $this->pertemuan->id);
    Queue::fake();

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.run'))
        ->assertRedirect()
        ->assertSessionHas('info');

    expect(PrediksiAi::where('siswa_id', $siswa->id)->exists())->toBeFalse()
        ->and(PrediksiRisikoPertemuan::where('siswa_id', $siswa->id)->exists())->toBeFalse();
});

test('route run tidak menjadwalkan prediksi duplikat saat proses masih aktif', function () {
    Queue::fake();
    Cache::put(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'processing']);

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.run'))
        ->assertRedirect()
        ->assertSessionHas('info');

    Queue::assertNothingPushed();
});

test('endpoint status hanya mengembalikan status prediksi saat ini', function () {
    Cache::put(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'processing']);

    $this->actingAs($this->guru)
        ->getJson(route('pengajar.prediksi.status'))
        ->assertOk()
        ->assertExactJson(['status' => 'processing']);
});

test('komponen status prediksi mempertahankan atribut alpine dan tidak menampilkan kode', function (string $route, string $status) {
    Cache::put(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => $status]);
    $html = $this->actingAs($this->guru)->get(route($route))->assertOk()->getContent();

    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($dom);
    $node = $xpath->query('//*[@role="status" and @x-data]')->item(0);
    expect($node)->not->toBeNull();
    $data = $node->getAttribute('x-data');
    expect($data)->toContain('state: "'.$status.'"')
        ->toContain('endpoint: "'.route('pengajar.prediksi.status').'"')
        ->toContain('this.poll()')
        ->toContain("this.state = 'unavailable'")
        ->and($node->getAttribute('x-init'))->toContain('poll()')
        ->and($xpath->query('//body//text()[not(ancestor::script) and contains(., "this.poll()")]')->length)->toBe(0);
})->with([
    ['pengajar.prediksi', 'queued'],
    ['pengajar.prediksi', 'processing'],
    ['pengajar.prediksi', 'failed'],
    ['pengajar.prediksi.show', 'queued'],
    ['pengajar.prediksi.show', 'processing'],
    ['pengajar.prediksi.show', 'failed'],
]);

test('job menyimpan hasil dan menandai status prediksi selesai', function () {
    predSyncSiswaLengkap($this->kelas, $this->pertemuan->id);

    app(RunPrediksiJob::class)->handle(
        app(PrediksiRisikoService::class),
        app(KlasifikasiRisikoService::class),
        app(PrediksiMendatangService::class)
    );

    expect(cache()->get(RunPrediksiJob::STATUS_CACHE_KEY)['status'])->toBe('completed')
        ->and(PrediksiAi::count())->toBe(1)
        ->and(PrediksiRisikoPertemuan::count())->toBe(1);
});

test('route latih-ulang tetap menghitung ulang tanpa error', function () {
    predSyncSiswaLengkap($this->kelas, $this->pertemuan->id);

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.retrain'))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('hanya guru yang bisa menjalankan prediksi', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);

    // RoleMiddleware di app ini me-redirect ke dashboard sesuai role, bukan
    // melempar 403 - jadi expectation-nya redirect, bukan forbidden.
    $this->actingAs($siswa)
        ->post(route('pengajar.prediksi.run'))
        ->assertRedirect(route('pelajar.dashboard'));

    $this->assertDatabaseCount('fitur_hybrid_siswa', 0);
    $this->assertDatabaseCount('prediksi_ai', 0);
});

test('user yang belum login diarahkan ke halaman login', function () {
    $this->post(route('pengajar.prediksi.run'))
        ->assertRedirect(route('login'));
});

// -------------------------------------------------------------------------
// View. Fokusnya: overlay harus di dalam scope Alpine yang sama dengan form-nya.
// -------------------------------------------------------------------------

test('tombol prediksi menampilkan status penjadwalan di scope form', function () {
    $html = $this->actingAs($this->guru)->get(route('pengajar.prediksi'))->getContent();

    foreach (['generating'] as $state) {
        $segmen = predSyncSesudah($html, 'x-data="{ '.$state.': false }"');

        expect($segmen)->not->toBe('', "blok x-data untuk $state tidak ditemukan");

        expect($segmen)
            ->toContain('<form')
            ->toContain('@submit="'.$state.' = true"')
            ->toContain('x-bind:disabled="'.$state.'"')
            ->toContain('x-show="'.$state.'"')
            ->toContain('x-cloak');
    }
});

test('tombol Latih Ulang Model disembunyikan dari halaman prediksi', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi'))
        ->assertOk()
        ->assertSee('Jalankan Prediksi')
        ->assertDontSee('Latih Ulang Model');
});

test('tombol prediksi memakai x-bind disabled, bukan :disabled', function () {
    $html = $this->actingAs($this->guru)->get(route('pengajar.prediksi'))->getContent();

    expect($html)->toContain('x-bind:disabled="generating"');
    expect($html)->not->toMatch('/(?<!x-bind):disabled/');
});

test('halaman monitoring siswa juga dapat menjadwalkan prediksi', function () {
    $siswa = predSyncSiswaLengkap($this->kelas, $this->pertemuan->id);
    Queue::fake();

    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $siswa->id]))
        ->assertOk()
        ->assertSee('Jalankan Prediksi');

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.run'))
        ->assertRedirect()
        ->assertSessionHas('info');

    expect(PrediksiAi::where('siswa_id', $siswa->id)->exists())->toBeFalse();
});
