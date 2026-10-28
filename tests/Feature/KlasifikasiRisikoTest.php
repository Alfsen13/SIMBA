<?php

use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\User;
use App\Services\KlasifikasiRisikoService;
use App\Services\PrediksiRisikoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake();
    $this->svc = app(KlasifikasiRisikoService::class);
});

function rfKonteks(): array
{
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    return [$kelas, $pertemuan];
}

/**
 * Tulis satu baris riwayat risiko. Kategori TIDAK diisi manual: dihitung
 * lewat `PrediksiRisikoService`, jadi label latih RF benar-benar berasal dari
 * aturan baseline - persis seperti di produksi.
 */
function rfBuatBaris(Kelas $kelas, Pertemuan $pertemuan, int $siswaId, array $parameter, string $status = 'lengkap'): PrediksiRisikoPertemuan
{
    $hasil = app(PrediksiRisikoService::class)->hitungSkor($parameter);

    return PrediksiRisikoPertemuan::create([
        'siswa_id' => $siswaId,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        ...$parameter,
        'skor_penyimpangan' => $hasil['skor_penyimpangan'],
        'kategori_risiko' => $hasil['kategori_risiko'],
        'status_data' => $status === 'lengkap' ? $hasil['status_data'] : $status,
    ]);
}

/**
 * 12 siswa x 4 pertemuan = 48 baris berstatus lengkap di tiga kelas kategori.
 * Data sengaja terpisah jauh supaya RF punya pola yang jelas.
 *
 * @return array{0: Kelas, 1: array<int, int>}
 */
function rfDatasetSiapLatih(): array
{
    [$kelas, $pertemuan] = rfKonteks();
    $siswaIds = [];

    for ($i = 0; $i < 12; $i++) {
        $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
        $siswaIds[] = $siswa->id;

        // Kelas kategori diselang-seling (bukan berurutan) supaya 3 siswa
        // terakhir yang jadi data uji mewakili ketiga kelas, bukan satu kelas.
        $basis = [5, 25, 70][$i % 3];

        foreach (range(1, 4) as $urutan) {
            $pertemuanBaris = $urutan === 1
                ? $pertemuan
                : Pertemuan::create([
                    'kelas_id' => $kelas->id,
                    'nama_pertemuan' => "Pertemuan {$urutan}",
                    'urutan' => $urutan,
                ]);

            rfBuatBaris($kelas, $pertemuanBaris, $siswa->id, [
                'rata_nilai' => max(0, 100 - $basis),
                'persen_terlambat' => $basis,
                'persen_terlewat' => $basis,
                'nilai_keseluruhan' => max(0, 100 - $basis),
            ]);
        }
    }

    return [$kelas, $siswaIds];
}

// -------------------------------------------------------------------------
// Dataset
// -------------------------------------------------------------------------

test('dataset klasifikasi hanya memakai baris berstatus lengkap', function () {
    [$kelas, $pertemuan] = rfKonteks();
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);

    $kedua = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 2',
        'urutan' => 2,
    ]);

    rfBuatBaris($kelas, $pertemuan, $siswa->id, [
        'rata_nilai' => 80, 'persen_terlambat' => 5, 'persen_terlewat' => 0, 'nilai_keseluruhan' => 80,
    ]);

    // Satu siswa boleh punya banyak baris, tapi baris tanpa parameter lengkap
    // tidak boleh ikut jadi sampel latih.
    rfBuatBaris($kelas, $kedua, $siswa->id, [
        'rata_nilai' => null, 'persen_terlambat' => 5, 'persen_terlewat' => 0, 'nilai_keseluruhan' => 80,
    ], status: 'data_tidak_lengkap');

    [$samples, $labels, $ids] = $this->svc->buildDataset();

    expect($samples)->toHaveCount(1)
        ->and($labels)->toHaveCount(1)
        ->and($ids)->toBe([$siswa->id]);
});

test('vektor fitur klasifikasi memakai 4 parameter baseline dalam skala 0-1', function () {
    [$kelas, $pertemuan] = rfKonteks();
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);

    rfBuatBaris($kelas, $pertemuan, $siswa->id, [
        'rata_nilai' => 80, 'persen_terlambat' => 10, 'persen_terlewat' => 0, 'nilai_keseluruhan' => 60,
    ]);

    [$samples] = $this->svc->buildDataset();

    expect(KlasifikasiRisikoService::FITUR)->toHaveCount(4)
        ->and($samples[0])->toBe([0.8, 0.1, 0.0, 0.6]);
});

// -------------------------------------------------------------------------
// Pelatihan
// -------------------------------------------------------------------------

test('pelatihan menolak data yang tidak cukup', function () {
    [$kelas, $pertemuan] = rfKonteks();
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    rfBuatBaris($kelas, $pertemuan, $siswa->id, [
        'rata_nilai' => 80, 'persen_terlambat' => 5, 'persen_terlewat' => 0, 'nilai_keseluruhan' => 80,
    ]);

    $this->svc->train();
})->throws(RuntimeException::class);

test('pelatihan menyimpan artifact dan benchmark klasifikasi', function () {
    rfDatasetSiapLatih();

    $laporan = $this->svc->train();

    expect(Storage::exists($this->svc->modelPath()))->toBeTrue()
        ->and(Storage::exists($this->svc->benchmarkPath()))->toBeTrue()
        ->and($laporan['total_samples'])->toBeGreaterThanOrEqual(30)
        ->and($laporan['siswa_unik'])->toBe(12)
        ->and($laporan['test_samples'])->toBeGreaterThan(0);

    $benchmark = $this->svc->loadBenchmark();

    expect($benchmark['fitur'])->toBe(KlasifikasiRisikoService::FITUR)
        ->and($benchmark['label'])->toBe(KlasifikasiRisikoService::LABELS)
        ->and($benchmark['sumber_label'])->toBe(PrediksiRisikoService::METODE)
        ->and($benchmark['versi_model'] ?? null)->toBeString()
        ->and($benchmark['akurasi'])->toBeGreaterThanOrEqual(0.0)
        ->and($benchmark['akurasi'])->toBeLessThanOrEqual(1.0)
        ->and($benchmark['confusion_matrix'])->toBeArray();
});

test('metrik klasifikasi dilaporkan per kelas dan makro', function () {
    rfDatasetSiapLatih();

    $laporan = $this->svc->train();

    foreach (['akurasi', 'precision_macro', 'recall_macro', 'f1_macro', 'per_kelas'] as $kunci) {
        expect($laporan)->toHaveKey($kunci);
    }

    // Label latih dari aturan baseline yang pemisahannya jelas, jadi model
    // terlatih semestinya menirunya dengan baik di data uji.
    expect($laporan['akurasi'])->toBeGreaterThan(0.7);

    expect(array_keys($laporan['per_kelas']))->not->toBeEmpty();
});

// -------------------------------------------------------------------------
// Klasifikasi ulang
// -------------------------------------------------------------------------

test('klasifikasi tanpa model terlatih tidak mengubah apa pun', function () {
    [$kelas, $pertemuan] = rfKonteks();
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    rfBuatBaris($kelas, $pertemuan, $siswa->id, [
        'rata_nilai' => 80, 'persen_terlambat' => 5, 'persen_terlewat' => 0, 'nilai_keseluruhan' => 80,
    ]);

    $hasil = $this->svc->klasifikasiSemua();

    expect($hasil['model'])->toBeFalse()
        ->and($hasil['diklasifikasi'])->toBe(0)
        ->and(PrediksiRisikoPertemuan::first()->kategori_risiko_rf)->toBeNull();
});

test('klasifikasi mengisi kategori_risiko_rf tanpa menimpa kategori baseline', function () {
    [$kelas] = rfDatasetSiapLatih();
    $this->svc->train();

    // Pertemuan baru = satu baris yang BELUM pernah diklasifikasi.
    $baru = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan Baru',
        'urutan' => 99,
    ]);
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $segar = rfBuatBaris($kelas, $baru, $siswa->id, [
        'rata_nilai' => 20, 'persen_terlambat' => 80, 'persen_terlewat' => 80, 'nilai_keseluruhan' => 20,
    ]);

    $hasil = $this->svc->klasifikasiSemua();

    expect($hasil['model'])->toBeTrue()
        ->and($hasil['diklasifikasi'])->toBe(PrediksiRisikoPertemuan::count());

    $segar->refresh();

    // Acuan tetap baseline; RF punya kolom sendiri.
    expect($segar->kategori_risiko)->toBe('berisiko_tinggi')
        ->and($segar->kategori_risiko_rf)->toBeIn(KlasifikasiRisikoService::LABELS);

    // Tidak ada baris lengkap yang tertinggal tanpa hasil klasifikasi.
    expect(PrediksiRisikoPertemuan::whereNull('kategori_risiko_rf')->count())->toBe(0);
});

test('model klasifikasi dengan daftar fitur lama dianggap belum siap', function () {
    rfDatasetSiapLatih();
    $this->svc->train();

    Storage::put(
        $this->svc->benchmarkPath(),
        json_encode([
            'target' => KlasifikasiRisikoService::TARGET,
            'fitur' => ['rata_nilai', 'persen_terlambat'],
        ])
    );

    // Instance baru supaya cache benchmark tidak menyembunyikan isi terbaru.
    $hasil = app(KlasifikasiRisikoService::class)->klasifikasiSemua();

    expect($hasil['model'])->toBeFalse()
        ->and($hasil['diklasifikasi'])->toBe(0)
        ->and(PrediksiRisikoPertemuan::whereNull('kategori_risiko_rf')->count())
        ->toBe(PrediksiRisikoPertemuan::count());
});

// -------------------------------------------------------------------------
// Kontrak skema & command
// -------------------------------------------------------------------------

test('skema menyediakan kolom kategori_risiko_rf terpisah dari kategori baseline', function () {
    $kolom = Schema::getColumnListing('prediksi_risiko_pertemuan');

    expect($kolom)->toContain('kategori_risiko')
        ->toContain('kategori_risiko_rf');
});

test('ml:train bisa melewati pelatihan dan tetap sinkron nilai aktual', function () {
    $this->artisan('ml:train', [
        '--skip-regression' => true,
        '--skip-klasifikasi' => true,
    ])->assertSuccessful();
});

test('ml:train --skip-regression tetap melatih dan menempelkan hasil RF', function () {
    [$kelas] = rfDatasetSiapLatih();

    $this->artisan('ml:train', ['--skip-regression' => true])
        ->assertSuccessful();

    expect(Storage::exists('ml/rf_'.KlasifikasiRisikoService::TARGET.'.ser'))->toBeTrue()
        ->and(PrediksiRisikoPertemuan::whereNull('kategori_risiko_rf')->count())->toBe(0);
});
