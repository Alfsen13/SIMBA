<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\PrediksiRisikoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function predRiskSvc(): PrediksiRisikoService
{
    return app(PrediksiRisikoService::class);
}

function predRiskIdeal(): array
{
    return [
        'rata_nilai' => 100,
        'persen_terlambat' => 0,
        'persen_terlewat' => 0,
        'nilai_keseluruhan' => 100,
    ];
}

test('hitungSkor sesuai contoh spesifikasi', function () {
    $hasil = predRiskSvc()->hitungSkor([
        'rata_nilai' => 70,
        'persen_terlambat' => 20,
        'persen_terlewat' => 10,
        'nilai_keseluruhan' => 75,
    ]);

    expect($hasil['status_data'])->toBe(PrediksiRisikoService::STATUS_LENGKAP);
    // (|100-70| + |20-0| + |10-0| + |100-75|) / 4 = (30+20+10+25)/4
    expect($hasil['skor_penyimpangan'])->toBe(21.25);
    expect($hasil['kategori_risiko'])->toBe('perlu_pantau');
});

test('profil ideal menghasilkan skor 0 (aman)', function () {
    $hasil = predRiskSvc()->hitungSkor(predRiskIdeal());

    expect($hasil['skor_penyimpangan'])->toBe(0.0);
    expect($hasil['kategori_risiko'])->toBe('aman');
});

test('kebalikan profil ideal menghasilkan skor 100 (berisiko tinggi)', function () {
    $hasil = predRiskSvc()->hitungSkor([
        'rata_nilai' => 0,
        'persen_terlambat' => 100,
        'persen_terlewat' => 100,
        'nilai_keseluruhan' => 0,
    ]);

    expect($hasil['skor_penyimpangan'])->toBe(100.0);
    expect($hasil['kategori_risiko'])->toBe('berisiko_tinggi');
});

test('parameter null membuat status data_tidak_lengkap tanpa skor', function () {
    foreach (PrediksiRisikoService::PARAMETERS as $kosong) {
        $parameter = [
            'rata_nilai' => 80,
            'persen_terlambat' => 10,
            'persen_terlewat' => 5,
            'nilai_keseluruhan' => 80,
        ];
        $parameter[$kosong] = null;

        $hasil = predRiskSvc()->hitungSkor($parameter);

        expect($hasil['status_data'])->toBe(PrediksiRisikoService::STATUS_TIDAK_LENGKAP)
            ->and($hasil['skor_penyimpangan'])->toBeNull()
            ->and($hasil['kategori_risiko'])->toBeNull();
    }
});

test('parameter yang tidak dikirim sama seperti null', function () {
    $hasil = predRiskSvc()->hitungSkor(['rata_nilai' => 80]);

    expect($hasil['status_data'])->toBe(PrediksiRisikoService::STATUS_TIDAK_LENGKAP);
});

test('parameter di luar rentang 0-100 ditandai data_tidak_valid, bukan di-clamp', function () {
    // Di-clamp berarti 120 diam-diam jadi 100 dan skornya terlihat bagus.
    // Status terpisah memaksa data yang rusak kelihatan apa adanya.
    foreach ([-1, 101, 150] as $nilai) {
        foreach (PrediksiRisikoService::PARAMETERS as $kolom) {
            $parameter = [
                'rata_nilai' => 80,
                'persen_terlambat' => 10,
                'persen_terlewat' => 5,
                'nilai_keseluruhan' => 80,
            ];
            $parameter[$kolom] = $nilai;

            $hasil = predRiskSvc()->hitungSkor($parameter);

            expect($hasil['status_data'])->toBe(PrediksiRisikoService::STATUS_TIDAK_VALID)
                ->and($hasil['skor_penyimpangan'])->toBeNull()
                ->and($hasil['kategori_risiko'])->toBeNull();
        }
    }
});

test('parameter bukan angka ditandai data_tidak_valid', function () {
    $hasil = predRiskSvc()->hitungSkor([
        'rata_nilai' => 'tidak ada',
        'persen_terlambat' => 10,
        'persen_terlewat' => 5,
        'nilai_keseluruhan' => 80,
    ]);

    expect($hasil['status_data'])->toBe(PrediksiRisikoService::STATUS_TIDAK_VALID)
        ->and($hasil['skor_penyimpangan'])->toBeNull();
});

test('nilai tepat di batas 0 dan 100 tetap dianggap valid', function () {
    $hasil = predRiskSvc()->hitungSkor([
        'rata_nilai' => 0,
        'persen_terlambat' => 0,
        'persen_terlewat' => 0,
        'nilai_keseluruhan' => 100,
    ]);

    expect($hasil['status_data'])->toBe(PrediksiRisikoService::STATUS_LENGKAP)
        ->and($hasil['skor_penyimpangan'])->not->toBeNull();
});

test('kategori ambang: <20 aman, <40 perlu_pantau, >=40 berisiko_tinggi', function () {
    expect(PrediksiRisikoService::kategoriDariSkor(19.99))->toBe('aman');
    expect(PrediksiRisikoService::kategoriDariSkor(20.0))->toBe('perlu_pantau');
    expect(PrediksiRisikoService::kategoriDariSkor(39.99))->toBe('perlu_pantau');
    expect(PrediksiRisikoService::kategoriDariSkor(40.0))->toBe('berisiko_tinggi');
    expect(PrediksiRisikoService::kategoriDariSkor(100.0))->toBe('berisiko_tinggi');
});

// -------------------------------------------------------------------------
// Parameter per-pertemuan
// -------------------------------------------------------------------------

function predRiskKonteks(): array
{
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return [$kelas, $pertemuan, $siswa];
}

function predRiskKuisSelesai(Kelas $kelas, Pertemuan $pertemuan): Kuis
{
    return Kuis::create([
        'judul' => 'Kuis '.fake()->word(),
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'soal' => [],
        'is_aktif' => false,
        'tanggal_selesai' => now()->subDay(),
    ]);
}

function predRiskTugasLewat(Kelas $kelas, Pertemuan $pertemuan): Tugas
{
    return Tugas::create([
        'judul' => 'Tugas '.fake()->word(),
        'kelas_id' => $kelas->id,
        'guru_id' => $kelas->guru_id,
        'pertemuan_id' => $pertemuan->id,
        'tipe' => 'upload',
        'deadline' => now()->subDay(),
    ]);
}

test('parameterPertemuan menghitung 4 parameter dari kuis & tugas pertemuan itu', function () {
    [$kelas, $pertemuan, $siswa] = predRiskKonteks();

    foreach ([80, 60] as $skor) {
        $kuis = predRiskKuisSelesai($kelas, $pertemuan);
        KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $siswa->id, 'skor' => $skor]);
    }

    $tugasA = predRiskTugasLewat($kelas, $pertemuan);
    PengumpulanTugas::create([
        'tugas_id' => $tugasA->id, 'siswa_id' => $siswa->id, 'nilai' => 70, 'terlambat' => true,
    ]);
    $tugasB = predRiskTugasLewat($kelas, $pertemuan);
    PengumpulanTugas::create([
        'tugas_id' => $tugasB->id, 'siswa_id' => $siswa->id, 'nilai' => null, 'terlambat' => false,
    ]);

    $parameter = predRiskSvc()->parameterPertemuan($siswa->id, $pertemuan);

    expect((float) $parameter['rata_nilai'])->toBe(70.0)
        // 1 dari 2 tugas (penyebut = seluruh tugas yang deadline-nya lewat)
        ->and((float) $parameter['persen_terlambat'])->toBe(50.0)
        ->and((float) $parameter['persen_terlewat'])->toBe(0.0)
        ->and((float) $parameter['nilai_keseluruhan'])->toBe(70.0);
});

test('parameterPertemuan mengabaikan komponen pertemuan lain', function () {
    [$kelas, $pertemuan, $siswa] = predRiskKonteks();
    $lain = Pertemuan::create(['kelas_id' => $kelas->id, 'nama_pertemuan' => 'P2', 'urutan' => 2]);

    $kuisLain = predRiskKuisSelesai($kelas, $lain);
    KuisHasil::create(['kuis_id' => $kuisLain->id, 'siswa_id' => $siswa->id, 'skor' => 30]);

    $parameter = predRiskSvc()->parameterPertemuan($siswa->id, $pertemuan);

    // pertemuan ini tidak punya komponen -> semua null
    expect($parameter)->toBe([
        'rata_nilai' => null,
        'persen_terlambat' => null,
        'persen_terlewat' => null,
        'nilai_keseluruhan' => null,
    ]);
});

test('jalankan menghitung batch risiko dengan hasil yang sama seperti per siswa', function () {
    [$kelas, $pertemuan, $siswa] = predRiskKonteks();
    $kuis = predRiskKuisSelesai($kelas, $pertemuan);
    KuisHasil::create([
        'kuis_id' => $kuis->id,
        'siswa_id' => $siswa->id,
        'skor' => 80,
    ]);
    $tugas = predRiskTugasLewat($kelas, $pertemuan);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'nilai' => 60,
        'terlambat' => true,
    ]);

    $expected = predRiskSvc()->parameterPertemuan($siswa->id, $pertemuan);
    $laporan = predRiskSvc()->jalankan(backfill: false);
    $actual = PrediksiRisikoPertemuan::where('siswa_id', $siswa->id)
        ->where('pertemuan_id', $pertemuan->id)
        ->firstOrFail();

    expect($laporan['pertemuan'])->toBe(1)
        ->and($actual->rata_nilai)->toEqual($expected['rata_nilai'])
        ->and($actual->persen_terlambat)->toEqual($expected['persen_terlambat'])
        ->and($actual->persen_terlewat)->toEqual($expected['persen_terlewat'])
        ->and($actual->nilai_keseluruhan)->toEqual($expected['nilai_keseluruhan']);
});

// -------------------------------------------------------------------------
// Penyimpanan & snapshot
// -------------------------------------------------------------------------

test('simpanPertemuan membuat lalu memperbarui satu baris (unik)', function () {
    [$kelas, $pertemuan, $siswa] = predRiskKonteks();
    $kuis = predRiskKuisSelesai($kelas, $pertemuan);
    KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $siswa->id, 'skor' => 90]);
    $tugas = predRiskTugasLewat($kelas, $pertemuan);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id, 'siswa_id' => $siswa->id, 'nilai' => 90, 'terlambat' => false,
    ]);

    predRiskSvc()->simpanPertemuan($siswa->id, $pertemuan);
    predRiskSvc()->simpanPertemuan($siswa->id, $pertemuan);

    expect(PrediksiRisikoPertemuan::count())->toBe(1);
    expect(PrediksiRisikoPertemuan::first()->status_data)->toBe(PrediksiRisikoService::STATUS_LENGKAP);
    expect(PrediksiRisikoPertemuan::first()->kategori_risiko)->toBe('aman');
});

test('perbaruiSnapshot menulis prediksi_ai dari data lengkap', function () {
    [$kelas, $pertemuan, $siswa] = predRiskKonteks();
    $kuis = predRiskKuisSelesai($kelas, $pertemuan);
    KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $siswa->id, 'skor' => 40]);
    $tugas = predRiskTugasLewat($kelas, $pertemuan);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id, 'siswa_id' => $siswa->id, 'nilai' => 40, 'terlambat' => false,
    ]);

    predRiskSvc()->simpanPertemuan($siswa->id, $pertemuan);
    $pred = predRiskSvc()->perbaruiSnapshot($siswa->id);

    expect($pred)->not->toBeNull();
    // rata 40 -> dev 60; nilai_keseluruhan 40 -> dev 60; terlambat/terlewat 0
    expect($pred->skor_risiko)->toBe(30.0);
    expect($pred->hasil)->toBe('perlu_pantau');
    expect(PrediksiAi::count())->toBe(1);
});

test('perbaruiSnapshot tidak menulis saat data tidak lengkap', function () {
    [, $pertemuan, $siswa] = predRiskKonteks();

    predRiskSvc()->simpanPertemuan($siswa->id, $pertemuan);

    expect(predRiskSvc()->perbaruiSnapshot($siswa->id))->toBeNull();
    expect(PrediksiAi::count())->toBe(0);
});

test('simpanPertemuan mencatat metode dan versi konfigurasi', function () {
    [$kelas, $pertemuan, $siswa] = predRiskKonteks();
    $kuis = predRiskKuisSelesai($kelas, $pertemuan);
    KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $siswa->id, 'skor' => 90]);

    $baris = predRiskSvc()->simpanPertemuan($siswa->id, $pertemuan);

    expect($baris->metode)->toBe(PrediksiRisikoService::METODE)
        ->and($baris->versi_konfigurasi)->toBe(PrediksiRisikoService::VERSI_KONFIGURASI);
});

test('simpanPertemuan menyimpan data_tidak_valid apa adanya tanpa skor', function () {
    [$kelas, $pertemuan, $siswa] = predRiskKonteks();

    // Nilai di luar rentang: parameternya disimpan apa adanya (bukti data
    // rusak), tetapi skor/kategori tidak dihitung.
    $tugas = predRiskTugasLewat($kelas, $pertemuan);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id, 'siswa_id' => $siswa->id, 'nilai' => 120, 'terlambat' => false,
    ]);

    $baris = predRiskSvc()->simpanPertemuan($siswa->id, $pertemuan);

    expect($baris->status_data)->toBe(PrediksiRisikoService::STATUS_TIDAK_VALID)
        ->and($baris->skor_penyimpangan)->toBeNull()
        ->and($baris->kategori_risiko)->toBeNull()
        // Snapshot dashboard tidak boleh dibuat dari data rusak.
        ->and(predRiskSvc()->perbaruiSnapshot($siswa->id))->toBeNull();
});

// -------------------------------------------------------------------------
// Kontrak skema (SQLite memetakan enum ke varchar dan tidak menegakkan
// daftar nilainya, jadi daftar enum diverifikasi dari sumber migrasi).
// -------------------------------------------------------------------------

test('skema prediksi_risiko_pertemuan memuat kolom hasil tiap metode', function () {
    $kolom = Schema::getColumnListing('prediksi_risiko_pertemuan');

    foreach (['metode', 'versi_konfigurasi', 'kategori_risiko_rf', 'prediksi_nilai_mendatang'] as $nama) {
        expect($kolom)->toContain($nama);
    }
});

test('skema prediksi_mendatang memuat metadata hasil SVR', function () {
    $kolom = Schema::getColumnListing('prediksi_mendatang');

    foreach ([
        'pertemuan_sumber_id',
        'pertemuan_target_id',
        'nilai_ideal',
        'persentase_meleset',
        'nilai_aktual',
        'mae_individual',
        'status_data',
        'model_versi',
    ] as $nama) {
        expect($kolom)->toContain($nama);
    }
});

test('migrasi alter mendeklarasikan enum status_data yang memuat data_tidak_valid', function () {
    $berkas = glob(database_path('migrations/*_add_hasil_model_columns.php'));

    expect($berkas)->not->toBeEmpty('migrasi add_hasil_model_columns tidak ditemukan');

    expect(file_get_contents($berkas[0]))
        ->toContain("'lengkap', 'data_tidak_lengkap', 'data_tidak_valid'");
});
