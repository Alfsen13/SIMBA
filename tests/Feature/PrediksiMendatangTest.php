<?php

use App\Models\FiturSiswaHistory;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\PrediksiMendatang;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\User;
use App\Services\FiturPertemuanService;
use App\Services\PrediksiMendatangService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake();
    $this->svc = app(PrediksiMendatangService::class);
});

function pmBuatSiswa(string $nama = 'Siswa'): User
{
    return User::factory()->verified()->create([
        'name' => $nama,
        'role' => 'pelajar',
    ]);
}

/**
 * Buat satu kelas dengan sejumlah pertemuan berurutan.
 *
 * @return array{0: Kelas, 1: array<int, Pertemuan>}
 */
function pmKelasDenganPertemuan(int $jumlah = 4): array
{
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::create([
        'nama_kelas' => 'Kelas PM',
        'kode_kelas' => 'PM-'.uniqid(),
        'guru_id' => $guru->id,
        'kategori' => 'Umum',
    ]);

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
 * Isi riwayat fitur per-pertemuan untuk satu siswa. `$nullDari` men-null-kan
 * `rata_nilai` mulai urutan itu (mensimulasikan pertemuan tanpa nilai).
 *
 * @param  array<int, Pertemuan>  $pertemuans
 */
function pmRiwayat(int $siswaId, array $pertemuans, float $awal, float $per, ?int $nullDari = null): void
{
    foreach ($pertemuans as $urutan => $pertemuan) {
        $nilai = max(0, min(100, $awal + $per * $urutan));
        if ($nullDari !== null && $urutan >= $nullDari) {
            $nilai = null;
        }

        FiturSiswaHistory::create([
            'siswa_id' => $siswaId,
            'pertemuan_id' => $pertemuan->id,
            'tanggal' => now()->startOfDay(),
            'frekuensi_akses_pertemuan' => $urutan * 2,
            'rata_durasi_belajar' => 20 + $urutan,
            'persen_tugas_terkumpul' => 50 + $urutan,
            'tren_nilai' => $urutan > 1 ? 1.5 : null,
            'persen_kehadiran' => 80 + $urutan,
            'rata_nilai' => $nilai,
            'jumlah_pertemuan_tidak_aktif' => max(0, 3 - $urutan),
        ]);
    }
}

// ---------------------------------------------------------------------
// Dataset
// ---------------------------------------------------------------------

test('pasangan training diambil dari pertemuan berurutan', function () {
    [$kelas, $pertemuans] = pmKelasDenganPertemuan(4);
    $siswa = pmBuatSiswa();
    pmRiwayat($siswa->id, $pertemuans, 60, 1.0);

    [$samples, $targets, $ids, $kelasIds] = $this->svc->buildDataset();

    // 4 baris -> 3 pasangan (1->2, 2->3, 3->4).
    expect($samples)->toHaveCount(3);
    expect($targets)->toHaveCount(3);
    expect($ids)->each->toBe($siswa->id);
    expect($kelasIds)->each->toBe($kelas->id);
});

test('tidak ada pasangan bila riwayat hanya satu pertemuan', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(1);
    $siswa = pmBuatSiswa();
    pmRiwayat($siswa->id, $pertemuans, 60, 1.0);

    [$samples] = $this->svc->buildDataset();

    expect($samples)->toBeEmpty();
});

test('baris dengan target null tidak dipakai sebagai pasangan', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    $siswa = pmBuatSiswa();
    // Pertemuan 4 tidak punya nilai -> pasangan 3->4 dibuang.
    pmRiwayat($siswa->id, $pertemuans, 60, 1.0, nullDari: 4);

    [$samples, $targets] = $this->svc->buildDataset();

    expect($samples)->toHaveCount(2);
    foreach ($targets as $t) {
        expect($t)->not->toBeNull();
    }
});

test('siswa berbeda dihitung terpisah', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    $a = pmBuatSiswa('A');
    $b = pmBuatSiswa('B');
    pmRiwayat($a->id, $pertemuans, 60, 1.0);
    pmRiwayat($b->id, $pertemuans, 70, -1.0);

    [, , $ids] = $this->svc->buildDataset();

    expect(array_unique($ids))->toHaveCount(2);
});

// ---------------------------------------------------------------------
// Training
// ---------------------------------------------------------------------

test('training menolak data yang tidak cukup', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 2; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 60, 0.5);
    }

    $this->svc->train();
})->throws(RuntimeException::class);

test('training menghasilkan file model dan median', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    // 12 siswa x 3 pasangan = 36 pasangan, cukup di atas minimal 30.
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }

    $laporan = $this->svc->train();

    expect($laporan)->toHaveKey('models');
    expect($laporan['models'][PrediksiMendatangService::TARGET])->not->toHaveKey('error');
    expect($laporan['models'][PrediksiMendatangService::TARGET]['total_samples'])->toBeGreaterThanOrEqual(30);

    expect(Storage::exists('ml/svr_nilai_pertemuan_berikutnya.ser'))->toBeTrue();
    expect(Storage::exists('ml/svr_nilai_pertemuan_berikutnya_medians.json'))->toBeTrue();
});

test('metrik regresi dilaporkan dalam skala poin nilai', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }

    $laporan = $this->svc->train();
    $m = $laporan['models'][PrediksiMendatangService::TARGET];

    expect($m)->toHaveKeys(['mae', 'rmse', 'test_samples']);
    expect($m['test_samples'])->toBeGreaterThan(0);
    expect($m['mae'])->toBeGreaterThan(0.0);
    expect($m['rmse'])->toBeGreaterThan(0.0);
});

// ---------------------------------------------------------------------
// Prediksi
// ---------------------------------------------------------------------

test('prediksi mengembalikan nilai dalam rentang 0-100', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }
    $this->svc->train();

    $siswa = pmBuatSiswa('Target');
    pmRiwayat($siswa->id, $pertemuans, 70, -1.0);

    $hasil = $this->svc->predict($siswa->id);

    expect($hasil)->not->toHaveKey('error');
    expect($hasil[PrediksiMendatangService::TARGET])->toBeGreaterThanOrEqual(0.0)
        ->and($hasil[PrediksiMendatangService::TARGET])->toBeLessThanOrEqual(100.0);
});

test('prediksi tanpa model mengembalikan pesan error', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    $siswa = pmBuatSiswa();
    pmRiwayat($siswa->id, $pertemuans, 70, -1.0);

    $hasil = $this->svc->predict($siswa->id);

    expect($hasil)->toHaveKey('error');
});

test('prediksi tanpa fitur siswa mengembalikan error', function () {
    $hasil = $this->svc->predict(999999);

    expect($hasil)->toHaveKey('error');
});

test('hasil prediksi tersimpan ke tabel prediksi_mendatang', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }
    $this->svc->train();

    $siswa = pmBuatSiswa('Tersimpan');
    pmRiwayat($siswa->id, $pertemuans, 70, -1.0);
    $this->svc->predict($siswa->id);

    $baris = PrediksiMendatang::where('siswa_id', $siswa->id)->first();

    expect($baris)->not->toBeNull();
    expect($baris->horizon_hari)->toBe(1);
    expect($baris->calculated_at)->not->toBeNull();
});

test('predictAll memproses semua siswa berfitur', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }
    $this->svc->train();

    $a = pmBuatSiswa('A');
    $b = pmBuatSiswa('B');
    pmRiwayat($a->id, $pertemuans, 70, -1.0);
    pmRiwayat($b->id, $pertemuans, 40, -1.0);

    $hasil = $this->svc->predictAll();

    expect($hasil)->toHaveKeys([$a->id, $b->id]);
});

test('vektor input SVR memakai fiturModel tanpa frekuensi akses', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(2);
    $siswa = pmBuatSiswa();
    pmRiwayat($siswa->id, $pertemuans, 60, 1.0);

    [$samples] = $this->svc->buildDataset();

    expect(FiturPertemuanService::fiturModel())->not->toContain('frekuensi_akses_pertemuan')
        ->and(FiturPertemuanService::fiturModel())->toHaveCount(6)
        ->and($samples[0])->toHaveCount(count(FiturPertemuanService::fiturModel()));
});

test('model yang dilatih untuk daftar fitur lama dianggap belum siap', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }
    $this->svc->train();

    // Tiru artifact lama: benchmark menyebut daftar 7 fitur (masih ada frekuensi).
    Storage::put(
        'ml/benchmark_'.PrediksiMendatangService::TARGET.'.json',
        json_encode([
            'target' => PrediksiMendatangService::TARGET,
            'fitur' => FiturPertemuanService::FEATURES,
        ])
    );

    $siswa = pmBuatSiswa('Basi');
    pmRiwayat($siswa->id, $pertemuans, 70, -1.0);

    expect($this->svc->predict($siswa->id))->toHaveKey('error')
        ->and(PrediksiMendatang::where('siswa_id', $siswa->id)->exists())->toBeFalse();
});

// ---------------------------------------------------------------------
// Metadata hasil & nilai aktual
// ---------------------------------------------------------------------

/**
 * Siapkan 12 siswa terlatih + satu siswa yang diramalkan, lalu kembalikan
 * [kelas, pertemuans, siswa, pertemuan target baru].
 *
 * @return array{0: Kelas, 1: array<int, Pertemuan>, 2: User, 3: Pertemuan}
 */
function pmSekaligusMetadata(): array
{
    [$kelas, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }

    app(PrediksiMendatangService::class)->train();

    // Pertemuan baru TANPA baris fitur: inilah pertemuan target ramalan.
    // Kalau barisnya sudah ada, pertemuan terakhir siswa akan bergeser ke sini.
    $target = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 5',
        'urutan' => 5,
    ]);

    $siswa = pmBuatSiswa('Meta');
    pmRiwayat($siswa->id, $pertemuans, 70, -1.0);

    return [$kelas, $pertemuans, $siswa, $target];
}

test('persentase meleset diukur dari nilai ideal, bukan besar kesalahan', function () {
    // Contoh konsep: 90 -> 10%, 75 -> 25%, 100 -> 0%.
    expect(PrediksiMendatangService::persentaseMeleset(90.0))->toBe(10.0)
        ->and(PrediksiMendatangService::persentaseMeleset(75.0))->toBe(25.0)
        ->and(PrediksiMendatangService::persentaseMeleset(100.0))->toBe(0.0)
        ->and(PrediksiMendatangService::persentaseMeleset(60.0))->toBe(40.0);
});

test('persentase meleset menolak nilai ideal nol', function () {
    PrediksiMendatangService::persentaseMeleset(80.0, 0.0);
})->throws(InvalidArgumentException::class);

test('benchmark menyimpan versi model dan daftar fitur', function () {
    [, $pertemuans] = pmKelasDenganPertemuan(4);
    for ($i = 0; $i < 12; $i++) {
        pmRiwayat(pmBuatSiswa("S$i")->id, $pertemuans, 50 + $i * 2, 0.3 + $i * 0.02);
    }
    $this->svc->train();

    $benchmark = $this->svc->loadBenchmark();

    expect($benchmark['versi_model'] ?? null)->toBeString()
        ->and($benchmark['versi_model'])->not->toBe('')
        ->and($benchmark['fitur'])->toBe(FiturPertemuanService::fiturModel())
        ->and($this->svc->versiModel())->toBe($benchmark['versi_model']);
});

test('hasil prediksi memuat metadata pertemuan sumber dan target', function () {
    [, $pertemuans, $siswa, $target] = pmSekaligusMetadata();

    $hasil = $this->svc->predict($siswa->id);

    expect($hasil)->not->toHaveKey('error')
        ->and($hasil['pertemuan_sumber_id'])->toBe($pertemuans[4]->id)
        ->and($hasil['pertemuan_target_id'])->toBe($target->id)
        ->and((float) $hasil['nilai_ideal'])->toBe(100.0)
        ->and($hasil['status_data'])->toBe('lengkap')
        ->and($hasil['model_versi'])->not->toBeNull()
        // Target belum dinilai -> aktual & selisih belum bisa dihitung.
        ->and($hasil['nilai_aktual'])->toBeNull()
        ->and($hasil['mae_individual'])->toBeNull();

    expect((float) $hasil['persentase_meleset'])
        ->toBe(round(100 - (float) $hasil[PrediksiMendatangService::TARGET], 2));

    $baris = PrediksiMendatang::where('siswa_id', $siswa->id)->first();
    expect($baris->pertemuan_sumber_id)->toBe($pertemuans[4]->id)
        ->and($baris->pertemuan_target_id)->toBe($target->id)
        ->and($baris->model_versi)->not->toBeNull();
});

test('ramalan ditempelkan ke baris riwayat pertemuan sumber', function () {
    [$kelas, $pertemuans, $siswa] = pmSekaligusMetadata();

    PrediksiRisikoPertemuan::create([
        'siswa_id' => $siswa->id,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuans[4]->id,
        'rata_nilai' => 70,
        'persen_terlambat' => 0,
        'persen_terlewat' => 0,
        'nilai_keseluruhan' => 70,
        'skor_penyimpangan' => 7.5,
        'kategori_risiko' => 'aman',
        'status_data' => 'lengkap',
    ]);

    $hasil = $this->svc->predict($siswa->id);

    $riwayat = PrediksiRisikoPertemuan::where('siswa_id', $siswa->id)
        ->where('pertemuan_id', $pertemuans[4]->id)
        ->first();

    expect((float) $riwayat->prediksi_nilai_mendatang)
        ->toBe((float) $hasil[PrediksiMendatangService::TARGET]);
});

test('sinkronkanNilaiAktual mengisi nilai aktual dan selisih per siswa', function () {
    [, , $siswa, $target] = pmSekaligusMetadata();

    $hasil = $this->svc->predict($siswa->id);

    // Pertemuan target akhirnya berjalan dan dinilai.
    FiturSiswaHistory::create([
        'siswa_id' => $siswa->id,
        'pertemuan_id' => $target->id,
        'tanggal' => now()->startOfDay(),
        'rata_durasi_belajar' => 25,
        'persen_tugas_terkumpul' => 60,
        'tren_nilai' => 1.0,
        'persen_kehadiran' => 90,
        'rata_nilai' => 80,
        'jumlah_pertemuan_tidak_aktif' => 0,
    ]);

    expect($this->svc->sinkronkanNilaiAktual())->toBe(1);

    $baris = PrediksiMendatang::where('siswa_id', $siswa->id)->first();

    expect((float) $baris->nilai_aktual)->toBe(80.0)
        ->and((float) $baris->mae_individual)
        ->toBe(round(abs((float) $hasil[PrediksiMendatangService::TARGET] - 80), 2));

    // Dipanggil ulang tidak menulis apa-apa lagi.
    expect($this->svc->sinkronkanNilaiAktual())->toBe(0);
});

test('status data ditandai tidak lengkap bila fitur input kosong', function () {
    [, $pertemuans, $siswa] = pmSekaligusMetadata();

    FiturSiswaHistory::where('siswa_id', $siswa->id)
        ->where('pertemuan_id', $pertemuans[4]->id)
        ->update(['rata_durasi_belajar' => null]);

    $hasil = $this->svc->predict($siswa->id);

    expect($hasil)->not->toHaveKey('error')
        ->and($hasil['status_data'])->toBe('data_tidak_lengkap');
});
