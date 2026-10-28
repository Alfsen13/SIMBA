<?php

use App\Models\FiturSiswaHistory;
use App\Models\GroundTruthRisiko;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\FiturPertemuanService;
use App\Services\PrediksiMendatangService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake();
    $this->svc = app(PrediksiMendatangService::class);
});

/**
 * Prefix "bm" dipakai di semua helper file ini karena fungsi di file Pest
 * bersifat global, dan nama generik seperti buatSiswa() sudah dipakai file test
 * lain (fatal "Cannot redeclare function" saat seluruh suite dijalankan).
 */
function bmBuatKelasDenganPertemuan(int $jumlah = 4): array
{
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

    $pertemuans = [];
    for ($u = 1; $u <= $jumlah; $u++) {
        $pertemuans[$u] = Pertemuan::create([
            'kelas_id' => $kelas->id,
            'nama_pertemuan' => "Pertemuan {$u}",
            'urutan' => $u,
        ]);
    }

    return [$kelas, $pertemuans];
}

/**
 * Isi riwayat fitur per-pertemuan untuk satu siswa.
 *
 * @param  array<int, Pertemuan>  $pertemuans
 */
function bmRiwayat(int $siswaId, array $pertemuans, float $awalNilai, float $perPertemuan): void
{
    foreach ($pertemuans as $urutan => $pertemuan) {
        FiturSiswaHistory::create([
            'siswa_id' => $siswaId,
            'pertemuan_id' => $pertemuan->id,
            'tanggal' => now()->startOfDay(),
            'frekuensi_akses_pertemuan' => $urutan * 2,
            'rata_durasi_belajar' => 20 + $urutan,
            'persen_tugas_terkumpul' => 50 + $urutan,
            'tren_nilai' => $urutan > 1 ? 1.0 : null,
            'persen_kehadiran' => 80 + $urutan,
            'rata_nilai' => max(0, min(100, $awalNilai + $perPertemuan * $urutan)),
            'jumlah_pertemuan_tidak_aktif' => max(0, 3 - $urutan),
        ]);
    }
}

function bmSiswa(string $nama = 'Siswa'): User
{
    return User::factory()->verified()->create([
        'name' => $nama,
        'role' => 'pelajar',
    ]);
}

/**
 * 12 siswa x 4 pertemuan -> 36 pasangan, cukup melewati guard minimal 30.
 * Variasi antar siswa dibuat berbeda agar split per siswa bermakna.
 *
 * @return array{0: Kelas, 1: array<int, Pertemuan>}
 */
function bmSeedCukup(int $jumlah = 12): array
{
    [$kelas, $pertemuans] = bmBuatKelasDenganPertemuan(4);

    for ($i = 0; $i < $jumlah; $i++) {
        bmRiwayat(bmSiswa("S{$i}")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }

    return [$kelas, $pertemuans];
}

// ---------------------------------------------------------------------
// Kontrak skema ground truth
// ---------------------------------------------------------------------

test('tabel ground_truth_risiko punya kolom yang diharapkan', function () {
    $kolom = Schema::getColumnListing('ground_truth_risiko');

    foreach ([
        'id', 'siswa_id', 'tanggal', 'label', 'sumber',
        'catatan', 'confirmed_by', 'confirmed_at',
        'created_at', 'updated_at',
    ] as $harap) {
        expect($kolom)->toContain($harap);
    }
});

test('ground truth menolak label yang tidak dikenal', function () {
    $siswa = bmSiswa();

    expect(fn () => GroundTruthRisiko::create([
        'siswa_id' => $siswa->id,
        'tanggal' => now()->toDateString(),
        'label' => 'sangat_berisiko',
        'sumber' => 'guru',
    ]))->toThrow(QueryException::class);
});

test('satu siswa hanya boleh punya satu label per tanggal', function () {
    $siswa = bmSiswa();

    GroundTruthRisiko::create([
        'siswa_id' => $siswa->id,
        'tanggal' => now()->toDateString(),
        'label' => 'aman',
        'sumber' => 'guru',
    ]);

    expect(fn () => GroundTruthRisiko::create([
        'siswa_id' => $siswa->id,
        'tanggal' => now()->toDateString(),
        'label' => 'berisiko_tinggi',
        'sumber' => 'guru',
    ]))->toThrow(QueryException::class);
});

test('ground truth menerima tiga label yang sama dengan klasifikasi', function () {
    $labelKlasifikasi = ['berisiko_tinggi', 'perlu_pantau', 'aman'];

    $siswa = bmSiswa();

    foreach ($labelKlasifikasi as $i => $label) {
        GroundTruthRisiko::create([
            'siswa_id' => $siswa->id,
            'tanggal' => now()->subDays($i + 1)->toDateString(),
            'label' => $label,
            'sumber' => 'guru',
        ]);
    }

    $tersimpan = GroundTruthRisiko::where('siswa_id', $siswa->id)
        ->pluck('label')
        ->all();

    expect($tersimpan)->toEqualCanonicalizing($labelKlasifikasi);
});

test('scope dikonfirmasi hanya mengambil label sumber guru', function () {
    $siswa = bmSiswa();

    GroundTruthRisiko::create([
        'siswa_id' => $siswa->id,
        'tanggal' => now()->subDay()->toDateString(),
        'label' => 'aman',
        'sumber' => 'guru',
    ]);

    GroundTruthRisiko::create([
        'siswa_id' => $siswa->id,
        'tanggal' => now()->toDateString(),
        'label' => 'berisiko_tinggi',
        'sumber' => 'sistem',
    ]);

    expect(GroundTruthRisiko::dikonfirmasi()->count())->toBe(1)
        ->and(GroundTruthRisiko::count())->toBe(2);
});

test('tanggal ground truth dibaca sebagai date lewat cast', function () {
    $siswa = bmSiswa();

    $baris = GroundTruthRisiko::create([
        'siswa_id' => $siswa->id,
        'tanggal' => now()->toDateString(),
        'label' => 'perlu_pantau',
        'sumber' => 'guru',
    ]);

    $baris->refresh();

    expect($baris->tanggal)->toBeInstanceOf(Carbon::class)
        ->and($baris->tanggal->toDateString())->toBe(now()->toDateString());
});

test('hapus siswa ikut menghapus label ground truth-nya', function () {
    $siswa = bmSiswa();

    GroundTruthRisiko::create([
        'siswa_id' => $siswa->id,
        'tanggal' => now()->toDateString(),
        'label' => 'aman',
        'sumber' => 'guru',
    ]);

    $siswa->delete();

    expect(GroundTruthRisiko::where('siswa_id', $siswa->id)->count())->toBe(0);
});

// ---------------------------------------------------------------------
// Benchmark regresi
// ---------------------------------------------------------------------

test('hanya SVR kernel linear yang dipakai sebagai kandidat', function () {
    expect(PrediksiMendatangService::KANDIDAT)
        ->toBe(['svr_linear' => 'SVR kernel linear']);
});

test('OLS dan SVR RBF dikeluarkan dari benchmark tapi buildCandidate masih mengenali keduanya', function () {
    // buildCandidate sengaja tidak diratakan: menambah kandidat nanti cukup
    // satu baris di KANDIDAT. Kalau ini ikut dihapus, menambah kandidat berarti
    // menulis ulang logika.
    $svc = new PrediksiMendatangService;
    $build = fn (string $k) => (new ReflectionMethod($svc, 'buildCandidate'))
        ->invoke($svc, $k);

    expect($build('svr_linear'))->toBeInstanceOf(SVR::class)
        ->and($build('linear'))->toBeInstanceOf(LeastSquares::class)
        ->and($build('svr_rbf'))->toBeInstanceOf(SVR::class)
        ->and($build('tidak_ada'))->toBeNull();
});

test('training melaporkan kandidat yang sedang dipakai', function () {
    bmSeedCukup();

    $m = $this->svc->train()['models'][PrediksiMendatangService::TARGET];

    expect($m)->toHaveKey('benchmark');

    foreach (array_keys(PrediksiMendatangService::KANDIDAT) as $kunci) {
        expect($m['benchmark'])->toHaveKey($kunci);
        expect($m['benchmark'][$kunci])->toHaveKey('nama');
    }
});

test('kandidat yang berhasil melaporkan mae rmse r2 dalam skala poin nilai', function () {
    bmSeedCukup();

    $m = $this->svc->train()['models'][PrediksiMendatangService::TARGET];

    $berhasil = array_filter($m['benchmark'], fn ($b) => ! isset($b['error']));

    expect($berhasil)->not->toBeEmpty();

    foreach ($berhasil as $b) {
        expect($b)->toHaveKeys(['mae', 'rmse', 'r2']);
        expect($b['mae'])->toBeGreaterThan(0.0);
        // RMSE >= MAE secara matematis; kalau terbalik, ada bug metrik.
        expect($b['rmse'])->toBeGreaterThanOrEqual($b['mae'] - 0.02);
    }
});

test('pemenang adalah kandidat dengan MAE terendah', function () {
    bmSeedCukup();

    $m = $this->svc->train()['models'][PrediksiMendatangService::TARGET];

    $mae = [];
    foreach ($m['benchmark'] as $kunci => $b) {
        if (! isset($b['error'])) {
            $mae[$kunci] = (float) $b['mae'];
        }
    }

    expect($mae)->not->toBeEmpty();

    $minimum = min($mae);
    $seri = array_keys(array_filter($mae, fn ($v) => abs($v - $minimum) < 0.005));

    // Seri MAE harus jatuh ke kandidat paling sederhana di antara yang seri.
    $harap = null;
    foreach (array_keys(PrediksiMendatangService::KANDIDAT) as $kunci) {
        if (in_array($kunci, $seri, true)) {
            $harap = $kunci;
            break;
        }
    }

    expect($m['pemenang'])->toBe($harap);
});

test('metrik level model adalah milik kandidat pemenang', function () {
    bmSeedCukup();

    $m = $this->svc->train()['models'][PrediksiMendatangService::TARGET];
    $menang = $m['benchmark'][$m['pemenang']];

    expect($m['mae'])->toBe($menang['mae'])
        ->and($m['rmse'])->toBe($menang['rmse'])
        ->and($m['r2'])->toBe($menang['r2']);
});

test('jumlah sampel split konsisten untuk seluruh kandidat', function () {
    bmSeedCukup();

    $m = $this->svc->train()['models'][PrediksiMendatangService::TARGET];

    expect($m['train_samples'] + $m['test_samples'])->toBe($m['total_samples'])
        ->and($m['test_samples'])->toBeGreaterThan(0);
});

test('file benchmark ditulis per target dengan isi lengkap', function () {
    bmSeedCukup();

    $this->svc->train();

    $path = 'ml/benchmark_'.PrediksiMendatangService::TARGET.'.json';

    expect(Storage::exists($path))->toBeTrue();

    $isi = json_decode(Storage::get($path), true);

    expect($isi)->toHaveKeys([
        'target', 'fitur', 'total_samples',
        'train_samples', 'test_samples', 'pemenang', 'pemenang_nama',
        'metode_pemilihan', 'benchmark', 'dilatih_pada',
    ]);

    expect($isi['fitur'])->toBe(FiturPertemuanService::fiturModel())
        ->and($isi['fitur'])->not->toContain('frekuensi_akses_pertemuan')
        ->and($isi['target'])->toBe(PrediksiMendatangService::TARGET)
        ->and(array_keys($isi['benchmark']))->toBe(
            array_keys(PrediksiMendatangService::KANDIDAT)
        );
});

test('hanya satu model produksi per target, bukan satu per kandidat', function () {
    bmSeedCukup();

    $m = $this->svc->train()['models'][PrediksiMendatangService::TARGET];

    foreach (array_keys(PrediksiMendatangService::KANDIDAT) as $kunci) {
        expect(Storage::exists('ml/svr_'.PrediksiMendatangService::TARGET."_{$kunci}.ser"))->toBeFalse();
    }

    expect(Storage::exists('ml/svr_'.PrediksiMendatangService::TARGET.'.ser'))->toBeTrue()
        ->and($m['pemenang'])->toBeString()
        ->and($m['pemenang_nama'])->toBe(PrediksiMendatangService::KANDIDAT[$m['pemenang']]);
});

test('artifact model bisa dimuat kembali dan tetap kelas SVR', function () {
    bmSeedCukup();

    $this->svc->train();

    // trainOne() serialize, predict() unserialize. Kalau kelas pemenang tidak
    // ada di allowlist loadModel(), pemuatan diam-diam gagal jadi null dan
    // seluruh prediksi hilang tanpa error yang terlihat. LeastSquares sengaja
    // ikut dipasang walau OLS tidak lagi jadi kandidat, supaya artifact lama di
    // storage tidak langsung rusak kalau dikembalikan.
    $payload = Storage::get('ml/svr_'.PrediksiMendatangService::TARGET.'.ser');
    $model = unserialize($payload, [
        'allowed_classes' => [SVR::class, LeastSquares::class],
    ]);

    expect($model)->toBeInstanceOf(SVR::class);
});

test('prediksi tetap di rentang 0-100 walau model mengeskapolasi', function () {
    [, $pertemuans] = bmSeedCukup();
    $this->svc->train();

    $siswa = bmSiswa('Ekstrim');
    bmRiwayat($siswa->id, $pertemuans, 99.0, 0.0);

    $hasil = $this->svc->predict($siswa->id);

    expect($hasil)->not->toHaveKey('error');

    $nilai = $hasil[PrediksiMendatangService::TARGET] ?? null;
    if ($nilai !== null) {
        expect($nilai)->toBeGreaterThanOrEqual(0.0)
            ->and($nilai)->toBeLessThanOrEqual(100.0);
    }
});
