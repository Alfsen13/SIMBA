<?php

/*
 * Penjaga tiga perbaikan pada alur remedial.
 *
 * 1. Nama siswa di notifikasi guru. Kolomnya `name`, bukan `nama`. Versi lama
 *    menulis `->nama` lalu menutupinya dengan `?? 'Siswa'`, jadi atribut null
 *    tidak pernah error dan TIDAK PERNAH terlihat sebagai bug — semua
 *    notifikasi guru berbunyi "Siswa <nama>" selama ini.
 *
 * 2. Idempotensi. Tanpa guard, siswa yang bertahan berisiko tinggi mendapat
 *    baris `materi_remedial` BARU setiap kali trigger dijalankan selama ia
 *    belum menyelesaikannya, plus satu notifikasi tiap kali.
 *
 * 3. Satu trigger. Remedial hanya boleh dibuat lewat
 *    `RemedialService::prosesSiswaBerisiko()`. Dulu dipakai scheduler 02:00
 *    DAN tombol "Jalankan Prediksi"; sekarang scheduler DIHAPUS (segala AI
 *    dijalankan live) dan tombolnya dipisah per pelajar: tombol "Buat
 *    Remedial" di halaman prediksi memanggil `prosesSiswaBerisiko($siswaId)`.
 */

use App\Jobs\GenerateRemedialAI;
use App\Models\Kelas;
use App\Models\MateriRemedial;
use App\Models\PrediksiAi;
use App\Models\User;
use App\Notifications\RisikoTinggiDetected;
use App\Services\AIProviderService;
use App\Services\RemedialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create(['name' => 'Bu Rina']);
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->siswa = User::factory()->verified()->create(['name' => 'Sari Dewi']);
    $this->siswa->kelasDiikuti()->attach($this->kelas->id);

    $this->prediksi = PrediksiAi::create([
        'siswa_id' => $this->siswa->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 0.87,
        'feature_vector' => [2, 25, 60, 1, 1, 70, 65, 2, 4, 90],
    ]);

    config(['services.remedial.cooldown_hari' => 7]);
});

/** Buat satu remedial lama untuk siswa, dengan umur yang bisa diatur. */
function remedialBuatRiwayat(int $siswaId, int $prediksiId, string $status, ?int $umurHari = null): MateriRemedial
{
    return MateriRemedial::create([
        'siswa_id' => $siswaId,
        'prediksi_ai_id' => $prediksiId,
        'topik_lemah' => 'Fotosintesis',
        'ringkasan' => 'Ringkasan.',
        'latihan' => '',
        'provider_llm' => 'ollama',
        'status' => $status,
        'generated_at' => $umurHari === null ? now() : now()->subDays($umurHari),
    ]);
}

// ---------------------------------------------------------------------------
// 1. Nama siswa di notifikasi guru
// ---------------------------------------------------------------------------

test('notifikasi guru menyebut nama siswa sebenarnya, bukan teks "Siswa"', function () {
    // `Queue::fake()` mencegah GenerateRemedialAI jalan inline (QUEUE_CONNECTION
    // = sync di test), dan `Notification::fake()` mengecek payloadnya. Kalau
    // hanya salah satu, yang diuji bukan trigger-nya.
    Queue::fake();
    Notification::fake();

    app(RemedialService::class)->prosesSiswaBerisiko();

    Notification::assertSentTo(
        $this->guru,
        RisikoTinggiDetected::class,
        function ($notifikasi) {
            // Tanpa ini, `->nama` yang null + `?? 'Siswa'` lolos begitu saja.
            expect($notifikasi->siswaNama)->toBe('Sari Dewi')
                ->and($notifikasi->toNotifikasi($this->guru)['pesan'])
                ->toContain('Sari Dewi');

            return true;
        }
    );
});

test('kolom nama siswa memang `name`, jadi regression ini tidak salah baca skema', function () {
    // Penjaga supaya ada yang "memperbaiki" ->name kembali jadi ->nama.
    expect(Schema::hasColumn('users', 'name'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'nama'))->toBeFalse();
});

test('pencarian guru pengampu tidak merujuk kolom fiktif dan benar-benar menemukan guru', function () {
    // `users` tidak punya kolom `user_id` — id-nya `id`. Rantai
    // whereHas->whereHas yang lama memakai `where("user_id")`, dan itu persis
    // jebakan SQLite: kolom fiktif hanya mengembalikan kosong tanpa error,
    // sedangkan MySQL melempar Column not found. Karena itu hasilnya diuji
    // langsung, bukan sekadar "tidak error".
    expect(Schema::getColumnListing('users'))->not->toContain('user_id');

    $svc = app(RemedialService::class);
    $cari = new ReflectionMethod($svc, 'guruPengampu');
    $cari->setAccessible(true);

    expect($cari->invoke($svc, $this->siswa->id))->not->toBeNull()
        ->and($cari->invoke($svc, $this->siswa->id)->id)->toBe($this->guru->id)
        // Batas: siswa yang tidak ikut kelas mana pun tidak boleh "menemu" guru.
        ->and($cari->invoke($svc, 999999))->toBeNull();
});

test('siswa yang tidak ada guru pengampu tidak membuat error dan tetap dijadwalkan', function () {
    Queue::fake();
    Notification::fake();

    $yatim = User::factory()->verified()->create();
    PrediksiAi::create([
        'siswa_id' => $yatim->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 0.9,
    ]);

    $hasil = app(RemedialService::class)->prosesSiswaBerisiko();

    expect($hasil['dijadwalkan'])->toBe(2);
    Notification::assertSentToTimes($this->guru, RisikoTinggiDetected::class, 1);
});

// ---------------------------------------------------------------------------
// 2. Idempotensi
// ---------------------------------------------------------------------------

test('remedial tidak dibuat ulang selama masih ada yang belum diselesaikan', function () {
    Queue::fake();
    Notification::fake();

    remedialBuatRiwayat($this->siswa->id, $this->prediksi->id, 'dibaca', 30);

    $hasil = app(RemedialService::class)->prosesSiswaBerisiko();

    expect($hasil['dijadwalkan'])->toBe(0)
        ->and($hasil['dilewati'])->toBe(1);

    Queue::assertNothingPushed();
    expect(MateriRemedial::where('siswa_id', $this->siswa->id)->count())->toBe(1);
    // Notifikasi guru juga harus ikut ditahan, bukan hanya jobnya.
    Notification::assertNothingSent();
});

test('remedial tidak dibuat ulang dalam masa cooldown walau yang lama sudah selesai', function () {
    Queue::fake();

    remedialBuatRiwayat($this->siswa->id, $this->prediksi->id, 'selesai', 2);

    $hasil = app(RemedialService::class)->prosesSiswaBerisiko();

    expect($hasil['dijadwalkan'])->toBe(0)
        ->and(MateriRemedial::where('siswa_id', $this->siswa->id)->count())->toBe(1);

    Queue::assertNothingPushed();
});

test('remedial dibuat lagi setelah cooldown lewat dan tidak ada yang menggantung', function () {
    Queue::fake();

    remedialBuatRiwayat($this->siswa->id, $this->prediksi->id, 'selesai', 10);

    $hasil = app(RemedialService::class)->prosesSiswaBerisiko();

    expect($hasil['dijadwalkan'])->toBe(1)
        ->and($hasil['dilewati'])->toBe(0);

    Queue::assertPushed(GenerateRemedialAI::class);
});

test('cooldown 0 menonaktifkan lapis cooldown, bukan lapis yang belum selesai', function () {
    Queue::fake();
    config(['services.remedial.cooldown_hari' => 0]);

    remedialBuatRiwayat($this->siswa->id, $this->prediksi->id, 'selesai', 1);
    expect(app(RemedialService::class)->prosesSiswaBerisiko()['dijadwalkan'])->toBe(1);

    // Masih ada yang belum selesai -> tetap dilewati meski cooldown 0.
    remedialBuatRiwayat($this->siswa->id, $this->prediksi->id, 'dibaca', 1);
    expect(app(RemedialService::class)->prosesSiswaBerisiko()['dijadwalkan'])->toBe(0);
});

test('siswa yang dipulihkan lalu kembali berisiko bisa dapat remedial baru', function () {
    Queue::fake();

    remedialBuatRiwayat($this->siswa->id, $this->prediksi->id, 'selesai', 40);

    expect(app(RemedialService::class)->alasanDilewati($this->prediksi))->toBeNull();
});

test('remedial milik siswa lain tidak ikut menyetop siswa ini', function () {
    Queue::fake();

    $orangLain = User::factory()->verified()->create();
    remedialBuatRiwayat($orangLain->id, $this->prediksi->id, 'dibaca', 1);

    expect(app(RemedialService::class)->prosesSiswaBerisiko()['dijadwalkan'])->toBe(1);
});

// ---------------------------------------------------------------------------
// 3. Satu trigger, sekarang dipanggil per pelajar (live)
// ---------------------------------------------------------------------------

test('tombol Buat Remedial per pelajar memanggil trigger untuk siswa itu', function () {
    // Guard kelanjutan: dulu tombol "Jalankan Prediksi" memicu remedial untuk
    // SEMUA pelajar sekaligus. Karena semua AI kini dijalankan sinkron, itu
    // berarti menunggu puluhan panggilan AI dalam satu request. Sekarang
    // trigger dipanggil per pelajar lewat tombol "Buat Remedial". Mock dipakai
    // supaya yang diuji benar-benar sambungan controller -> trigger, bukan
    // hasil tebakan model ML pada data siswa yang diuji.
    Queue::fake();
    Storage::fake();

    $this->mock(RemedialService::class, function ($mock) {
        $mock->shouldReceive('prosesSiswaBerisiko')->once()->with($this->siswa->id)
            ->andReturn(['dijadwalkan' => 1, 'dilewati' => 0]);
    });

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.remedial', $this->siswa))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('tombol Buat Remedial menolak pelajar di luar kelas guru', function () {
    Queue::fake();
    Storage::fake();

    $orangLain = User::factory()->verified()->create(['role' => 'pelajar']);

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.remedial', $orangLain))
        ->assertForbidden();

    expect(MateriRemedial::count())->toBe(0);
});

test('Jalankan Prediksi TIDAK lagi membuat remedial sekaligus', function () {
    Queue::fake();
    Storage::fake();

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.run'))
        ->assertRedirect()
        ->assertSessionHas('success', fn ($pesan) => str_contains($pesan, 'Buat Remedial'));

    // Tidak ada remedial otomatis; guru membuatnya per pelajar.
    expect(MateriRemedial::count())->toBe(0);
    Queue::assertNotPushed(GenerateRemedialAI::class);
});

test('job yang dijalankan dua kali hanya menghasilkan satu remedial', function () {
    // Celah nyata: trigger ditekan dua kali untuk pelajar yang sama, dan saat
    // pemanggilan kedua belum ada baris remedial sama sekali (pemanggilan
    // pertama masih memanggil AI). Guard hanya di saat dispatch tidak akan
    // menahan yang kedua. Karena AI kini sinkron, pemanggilan pertama selesai
    // dan menulis barisnya sebelum yang kedua jalan.
    Storage::fake();

    // `once()`, bukan `twice()`: justru itu buktinya — jalan kedua harus ditolak
    // sebelum menyentuh AI sama sekali.
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('send')
            ->once()
            ->andReturn('{"ringkasan":"Fotosintesis adalah proses mengubah cahaya menjadi glukosa.","latihan":"1. Jelaskan klorofil."}');
    });

    $job = new GenerateRemedialAI($this->siswa->id, $this->prediksi->id);

    $job->handle(app(RemedialService::class));

    expect(MateriRemedial::where('siswa_id', $this->siswa->id)->count())->toBe(1);

    // Jalankan lagi: harus ditolak karena sekarang sudah ada remedial aktif.
    $job->handle(app(RemedialService::class));

    expect(MateriRemedial::where('siswa_id', $this->siswa->id)->count())->toBe(1);
});

test('remedial hanya dibuat lewat satu trigger dan scheduler sudah dihapus', function () {
    // Regression arsitektur: kalau loop diduplikasi lagi di controller/job,
    // remedial bisa muncul dari salah satu jalur saja. Dan kalau scheduler
    // dihidupkan lagi, AI-nya kembali jalan di belakang layar.
    $isiController = file_get_contents(app_path('Http/Controllers/Web/Guru/PrediksiController.php'));

    expect($isiController)->toContain('prosesSiswaBerisiko(')
        // Dispatch langsung di luar trigger = implementasi terpisah.
        ->and($isiController)->not->toContain('GenerateRemedialAI::dispatch')
        ->and($isiController)->not->toContain('GenerateRemedialPertemuanAI::dispatch')
        // Scheduler dihapus: penjadwalan deteksi tidak boleh balik lagi.
        ->and(file_get_contents(base_path('bootstrap/app.php')))->not->toContain('withSchedule')
        ->and(file_exists(app_path('Jobs/RunDeteksiAI.php')))->toBeFalse();
});
