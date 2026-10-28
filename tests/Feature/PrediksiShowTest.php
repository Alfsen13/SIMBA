<?php

use App\Models\FiturHybridSiswa;
use App\Models\FiturSiswaHistory;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\User;
use App\Services\FiturPertemuanService;
use App\Services\KlasifikasiRisikoService;
use App\Services\PrediksiMendatangService;
use App\Services\PrediksiRisikoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function psBuatPertemuan(Kelas $kelas, int $jumlah): array
{
    $out = [];
    for ($u = 1; $u <= $jumlah; $u++) {
        $out[] = Pertemuan::create([
            'kelas_id' => $kelas->id,
            'nama_pertemuan' => "Pertemuan {$u}",
            'urutan' => $u,
        ]);
    }

    return $out;
}

function psRiwayat(int $siswaId, array $pertemuans, float $awal, float $per): void
{
    foreach ($pertemuans as $urutan => $pertemuan) {
        FiturSiswaHistory::create([
            'siswa_id' => $siswaId,
            'pertemuan_id' => $pertemuan->id,
            'tanggal' => now()->startOfDay(),
            'frekuensi_akses_pertemuan' => 2 + $urutan,
            'rata_durasi_belajar' => 20 + $urutan,
            'persen_tugas_terkumpul' => 50 + $urutan,
            'tren_nilai' => $urutan > 0 ? 1.0 : null,
            'persen_kehadiran' => 80 + $urutan,
            'rata_nilai' => max(0, min(100, $awal + $per * $urutan)),
            'jumlah_pertemuan_tidak_aktif' => max(0, 3 - $urutan),
        ]);
    }
}

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);

    $this->siswa = User::factory()->verified()->create(['name' => 'Andi Belajar', 'role' => 'pelajar']);
    $this->siswa->kelasDiikuti()->attach($this->kelas->id);

    $this->siswaLuar = User::factory()->verified()->create(['name' => 'Luar Kelas', 'role' => 'pelajar']);

    $this->pertemuans = psBuatPertemuan($this->kelas, 2);
    psRiwayat($this->siswa->id, $this->pertemuans, 75, 3);

    FiturHybridSiswa::create([
        'siswa_id' => $this->siswa->id,
        'rata_nilai' => 78,
        'persen_terlambat' => 5,
        'persen_terlewat' => 0,
        'nilai_keseluruhan' => 82,
    ]);

    PrediksiAi::create([
        'siswa_id' => $this->siswa->id,
        'hasil' => 'aman',
        'skor_risiko' => 0.12,
        'feature_vector' => [78, 5, 0, 82],
    ]);
});

it('menampilkan halaman monitoring dengan dropdown siswa', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show'))
        ->assertOk()
        ->assertSee('siswa-select')
        ->assertSee('Andi Belajar');
});

it('menampilkan data indikator untuk siswa yang dipilih', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Andi Belajar')
        ->assertSee('Rata Nilai')
        ->assertSee('Rata-rata Durasi Belajar')
        ->assertDontSee('Frekuensi Akses Pertemuan')
        ->assertSee('aman');
});

it('menolak akses siswa di luar kelas pengajar', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswaLuar->id]))
        ->assertNotFound();
});

it('mengarahkan pelajar menjauh dari halaman pengajar', function () {
    $pelajar = User::factory()->verified()->create(['role' => 'pelajar']);

    $this->actingAs($pelajar)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertRedirect(route('pelajar.dashboard'));
});

it('backfill menyimpan snapshot fitur per-pertemuan untuk tiap siswa', function () {
    // Backfill menghitung ULANG dari data mentah, jadi sediakan kuis bernilai
    // agar `rata_nilai` punya sumber (baris history manual di beforeEach akan
    // ditimpa oleh hasil hitung).
    $kuis = Kuis::create([
        'judul' => 'Kuis Pertemuan 1',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuans[0]->id,
        'soal' => [],
        'is_aktif' => true,
    ]);

    KuisHasil::create([
        'kuis_id' => $kuis->id,
        'siswa_id' => $this->siswa->id,
        'skor' => 80,
        'jawaban_siswa' => [],
    ]);

    $jumlah = app(FiturPertemuanService::class)->backfill();

    $history = FiturSiswaHistory::where('siswa_id', $this->siswa->id)
        ->whereNotNull('pertemuan_id')
        ->get();

    // 2 pertemuan x 1 siswa anggota kelas.
    expect($jumlah)->toBe(2)
        ->and($history)->toHaveCount(2)
        ->and($history->first()->tanggal->toDateString())->toBe(now()->toDateString())
        ->and((float) $history->first()->rata_nilai)->toBe(80.0);
});

it('menampilkan riwayat indikator di data grafik', function () {
    $response = $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk();

    $response->assertSee('prediksi-show-data')
        ->assertSee('Pertemuan 1')
        ->assertSee('Pertemuan 2');
});

it('payload grafik adalah JSON murni yang bisa di-parse, bukan ekspresi JavaScript', function () {
    $html = $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->getContent();

    preg_match(
        '#<script id="prediksi-show-data" type="application/json">(.*?)</script>#s',
        $html,
        $m
    );

    expect(array_key_exists(1, $m))
        ->toBeTrue('script data grafik tidak ditemukan di HTML');

    $payload = trim($m[1]);

    expect($payload)
        ->not->toStartWith('JSON.parse(')
        ->not->toContain('JSON.parse(');

    $data = json_decode($payload, true);

    // Frekuensi akses sengaja keluar dari grafik "Perkembangan Indikator"
    // (skalanya beda jauh dari indikator lain); 7 fitur model jadi 6 deret.
    $kunciGrafik = array_column($data['riwayat']['features'], 'key');

    expect($kunciGrafik)->not->toContain('frekuensi_akses_pertemuan')
        ->and($data['riwayat']['features'])->toHaveCount(6);

    expect(json_last_error_msg())->toBe('No error')
        ->and($data)->toBeArray()
        ->and($data)->toHaveKeys(['riwayat', 'aktivitas'])
        ->and($data['riwayat'])->toHaveKeys(['labels', 'features'])
        ->and($data['riwayat']['features'])->toHaveCount(6)
        ->and($data['riwayat']['labels'])->toHaveCount(2);

    // Grafik butuh >= 2 titik; satu titik = garis tanpa arah.
    expect(count($data['riwayat']['labels']))->toBeGreaterThan(1);
});

it('menampilkan kartu prediksi pertemuan berikutnya dengan alasan saat model belum ada', function () {
    // PENTING: fake disk di sini, bukan hanya di test berikutnya. Tanpa ini
    // test ini membaca artifact model NYATA dari storage/app/ml kalau ada.
    Storage::fake();

    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Prediksi Nilai Pertemuan Berikutnya')
        ->assertSee('belum dilatih');
});

it('menampilkan nilai prediksi saat model regresi tersedia', function () {
    Storage::fake();

    // 12 siswa x 4 pertemuan -> 36 pasangan, cukup untuk dilatih.
    [$kelas, $pertemuans] = (function () {
        $guru = User::factory()->pengajar()->verified()->create();
        $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

        return [$kelas, psBuatPertemuan($kelas, 4)];
    })();

    foreach (range(0, 11) as $i) {
        $s = User::factory()->verified()->create(['role' => 'pelajar']);
        psRiwayat($s->id, $pertemuans, 50 + $i * 2, 0.5 + $i * 0.05);
    }

    app(PrediksiMendatangService::class)->train();

    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Prediksi Nilai Pertemuan Berikutnya')
        ->assertSee('Nilai Pertemuan Berikutnya')
        ->assertDontSee('belum dilatih');
});

it('menampilkan label model dan label ambang skor secara terpisah', function () {
    // Model bisa melabel berbeda dari aturan ambang skor. Halaman wajib
    // menampilkan keduanya supaya guru tidak bingung saat hasilnya beda:
    // di sini model bilang `perlu_pantau`, tapi skor 5 jelas `aman`.
    PrediksiAi::where('siswa_id', $this->siswa->id)->first()
        ->update(['hasil' => 'perlu_pantau', 'skor_risiko' => 5]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Prediksi model')
        ->assertSee('Ambang skor')
        ->assertSee('perlu_pantau')
        ->assertSee('aman');
});

it('menampilkan glosarium istilah tanpa tabel benchmark per-kandidat', function () {
    Storage::fake();

    [$kelas, $pertemuans] = (function () {
        $guru = User::factory()->pengajar()->verified()->create();
        $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

        return [$kelas, psBuatPertemuan($kelas, 4)];
    })();

    foreach (range(0, 11) as $i) {
        $s = User::factory()->verified()->create(['role' => 'pelajar']);
        psRiwayat($s->id, $pertemuans, 50 + $i * 2, 0.5 + $i * 0.05);
    }

    app(PrediksiMendatangService::class)->train();

    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Kenapa ramalan ini seperti ini?')
        ->assertSee('SVR kernel linear')
        // Tabel benchmark per-kandidat sengaja disembunyikan dari guru.
        ->assertDontSee('DIPAKAI')
        ->assertDontSee('Kandidat');

    // Istilah MAE/RMSE/R^2 tetap dijelaskan supaya guru tidak menebak-nebak.
    // Istilah MAE/RMSE/R^2 tetap dijelaskan supaya guru tidak menebak-nebak.
    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Arti istilah')
        ->assertSee('Mean Absolute Error')
        ->assertSee('Root Mean Squared Error')
        ->assertSee('koefisien determinasi');
});

it('memakai label baseline yang jujur, bukan label lama keluaran Random Forest', function () {
    // Dua label lama menyesatkan dan pernah tampil di halaman ini:
    // "Keluaran model Random Forest" (kategori ini bukan keluaran model
    // terlatih, melainkan aturan baseline deterministik) dan ambang lama
    // "aman <= 0.25" (skor risiko kini berskala 0-100, ambangnya 20/40).
    foreach (['id', 'en'] as $locale) {
        app()->setLocale($locale);

        expect(__('guru_analitik.prediksi.model_output_title'))
            ->not->toContain('Random Forest')
            ->and(__('guru_analitik.prediksi.threshold_status_title'))
            ->not->toContain('0.25');
    }
});

it('menampilkan perbandingan Random Forest vs acuan baseline saat model RF tersedia', function () {
    Storage::fake();

    [$kelas, $pertemuans] = (function () {
        $guru = User::factory()->pengajar()->verified()->create();
        $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

        return [$kelas, psBuatPertemuan($kelas, 4)];
    })();

    // Model RF butuh minimal 30 baris risiko berstatus lengkap dari 3 siswa.
    // Kategori dihitung lewat aturan baseline, bukan ditulis manual.
    foreach (range(0, 11) as $i) {
        $s = User::factory()->verified()->create(['role' => 'pelajar']);
        psRiwayat($s->id, $pertemuans, 50 + $i * 2, 0.5 + $i * 0.05);

        $basis = [5, 25, 70][$i % 3];

        foreach ($pertemuans as $p) {
            $parameter = [
                'rata_nilai' => 100 - $basis,
                'persen_terlambat' => $basis,
                'persen_terlewat' => $basis,
                'nilai_keseluruhan' => 100 - $basis,
            ];
            $skor = app(PrediksiRisikoService::class)->hitungSkor($param = $parameter);

            PrediksiRisikoPertemuan::create([
                'siswa_id' => $s->id,
                'kelas_id' => $kelas->id,
                'pertemuan_id' => $p->id,
                ...$param,
                'skor_penyimpangan' => $skor['skor_penyimpangan'],
                'kategori_risiko' => $skor['kategori_risiko'],
                'status_data' => $skor['status_data'],
            ]);
        }
    }

    app(PrediksiMendatangService::class)->train();
    app(KlasifikasiRisikoService::class)->train();

    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Perbandingan Random Forest')
        ->assertSee('Akurasi')
        ->assertSee('Confusion matrix')
        // Caveat wajib ada: akurasi tinggi = meniru baseline, bukan bukti
        // RF lebih jago memprediksi risiko nyata.
        ->assertSee('meniru aturan tersebut');
});
