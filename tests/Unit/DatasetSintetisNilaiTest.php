<?php

use App\Services\DatasetLatihService;
use App\Services\DatasetSintetisNilaiService;
use App\Services\FiturPertemuanService;
use App\Services\PrediksiMendatangService;
use Illuminate\Support\Facades\Storage;

test('simulasi dapat diulang dan memuat tujuh pola yang terpisah per siswa', function () {
    $service = new DatasetSintetisNilaiService;
    $data = $service->bangun();
    $svr = $data['svr'];
    $fitur = array_flip(FiturPertemuanService::fiturModel());

    expect($service->bangun())->toBe($data)
        ->and(app(DatasetLatihService::class)->alasanFiturTidakCocok($data))->toBeNull()
        ->and(array_count_values($svr['profil_siswa']))->toBe(array_fill_keys(array_keys(DatasetSintetisNilaiService::PROFIL), 20))
        ->and($svr['samples'])->toHaveCount(980);

    foreach ($svr['profil_siswa'] as $siswa => $nama) {
        $awal = ($siswa - 1) * 7;
        [$min, $max, $arah] = DatasetSintetisNilaiService::PROFIL[$nama];
        $nilaiAwal = $svr['samples'][$awal][$fitur['rata_nilai']];
        expect($nilaiAwal)->toBeGreaterThanOrEqual($min - 0.5)
            ->toBeLessThanOrEqual($max + 0.5);

        for ($i = 0; $i < 7; $i++) {
            $idx = $awal + $i;
            $nilai = $svr['samples'][$idx][$fitur['rata_nilai']];
            $target = $svr['targets'][$idx];
            $selisih = $target - $nilai;
            expect($svr['siswa_ids'][$idx])->toBe($siswa)
                ->and($nilai)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100)
                ->and($target)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
            if ($arah === 0) {
                expect(abs($selisih))->toBeLessThanOrEqual(2);
            } elseif ($arah > 0) {
                expect($selisih)->toBeGreaterThan(2);
            } else {
                expect($selisih)->toBeLessThan(-2);
            }
            if ($i < 6) {
                expect($target)->toBe($svr['samples'][$idx + 1][$fitur['rata_nilai']]);
            }
            if ($i > 0) {
                expect($svr['samples'][$idx][$fitur['tren_nilai']])
                    ->toBe(round($nilai - $svr['samples'][$idx - 1][$fitur['rata_nilai']], 1));
            }
        }
    }

    $uji = array_slice(array_keys($svr['profil_siswa']), -42);
    $latih = array_slice(array_keys($svr['profil_siswa']), 0, -42);
    expect(array_intersect($latih, $uji))->toBeEmpty()
        ->and(array_unique(array_map(fn ($id) => $svr['profil_siswa'][$id], $latih)))->toHaveCount(7)
        ->and(array_unique(array_map(fn ($id) => $svr['profil_siswa'][$id], $uji)))->toHaveCount(7);
});

test('pelatihan sintetis menyimpan model dan tidak mengubah model risiko', function () {
    $disk = 'sintetis-'.uniqid();
    config(['filesystems.default' => $disk]);
    Storage::fake($disk);
    Storage::put('ml/rf_test.ser', 'model risiko lama');

    $this->artisan('ml:train-sintetis-nilai')->assertSuccessful();

    expect(Storage::get('ml/rf_test.ser'))->toBe('model risiko lama')
        ->and(Storage::exists(DatasetSintetisNilaiService::PATH))->toBeTrue()
        ->and(Storage::exists('ml/svr_nilai_pertemuan_berikutnya.ser'))->toBeTrue();
    $benchmark = json_decode(Storage::get('ml/benchmark_nilai_pertemuan_berikutnya.json'), true);
    expect($benchmark['sumber_dataset'])->toContain('sintetis')
        ->and($benchmark['train_samples'])->toBe(686)
        ->and($benchmark['test_samples'])->toBe(294);
});

test('imputasi hanya menggunakan median siswa latih', function () {
    $disk = 'median-'.uniqid();
    config(['filesystems.default' => $disk]);
    Storage::fake($disk);
    $samples = $targets = $ids = $kelas = [];
    for ($id = 1; $id <= 10; $id++) {
        for ($i = 0; $i < 3; $i++) {
            $samples[] = [$id <= 7 ? 20.0 : 90.0, 100.0, 1.0, 100.0, 70.0, 0.0];
            $targets[] = 70.0;
            $ids[] = $id;
            $kelas[] = 1;
        }
    }

    app(PrediksiMendatangService::class)->train([$samples, $targets, $ids, $kelas]);
    $medians = json_decode(Storage::get('ml/svr_nilai_pertemuan_berikutnya_medians.json'), true);
    expect($medians[0])->toEqual(20.0);
});

test('kegagalan pelatihan sintetis dilaporkan sebagai kegagalan perintah', function () {
    $disk = 'gagal-'.uniqid();
    config(['filesystems.default' => $disk]);
    Storage::fake($disk);
    $this->mock(PrediksiMendatangService::class)
        ->shouldReceive('train')
        ->once()
        ->andThrow(new RuntimeException('Pelatihan gagal'));

    $this->artisan('ml:train-sintetis-nilai')
        ->expectsOutputToContain('Pelatihan gagal')
        ->assertFailed();
});
