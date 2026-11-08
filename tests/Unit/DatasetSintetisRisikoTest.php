<?php

use App\Services\DatasetSintetisRisikoService;
use App\Services\KlasifikasiRisikoService;
use App\Services\PrediksiRisikoService;
use Illuminate\Support\Facades\Storage;

test('data risiko sintetis memakai label baseline dan memisahkan siswa uji', function () {
    $service = app(DatasetSintetisRisikoService::class);
    $data = $service->bangun();
    $rf = $data['rf'];
    $risiko = app(PrediksiRisikoService::class);
    $latih = $uji = $labelLatih = $labelUji = [];

    expect($service->bangun())->toBe($data)
        ->and($rf['samples'])->toHaveCount(480)
        ->and($rf['fitur'])->toBe(KlasifikasiRisikoService::FITUR);

    foreach ($rf['samples'] as $i => $sample) {
        expect($sample)->toHaveCount(4);
        foreach ($sample as $angka) {
            expect($angka)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1);
        }
        $parameter = array_combine($rf['fitur'], array_map(fn ($angka) => $angka * 100, $sample));
        expect($parameter['persen_terlambat'] + $parameter['persen_terlewat'])->toBeLessThanOrEqual(100)
            ->and($rf['labels'][$i])->toBe($risiko->hitungSkor($parameter)['kategori_risiko']);
        if ($rf['siswa_ids'][$i] <= 84) {
            $latih[$rf['siswa_ids'][$i]] = true;
            $labelLatih[$rf['labels'][$i]] = true;
        } else {
            $uji[$rf['siswa_ids'][$i]] = true;
            $labelUji[$rf['labels'][$i]] = true;
        }
    }

    expect(array_intersect_key($latih, $uji))->toBeEmpty()
        ->and(array_keys($labelLatih))->toHaveCount(3)
        ->and(array_keys($labelUji))->toHaveCount(3);
});

test('perintah melatih model risiko tanpa mengubah model nilai', function () {
    $disk = 'risiko-'.uniqid();
    config(['filesystems.default' => $disk]);
    Storage::fake($disk);
    Storage::put('ml/svr_nilai_pertemuan_berikutnya.ser', 'model nilai lama');

    $this->artisan('ml:train-sintetis-risiko')->assertSuccessful();

    expect(Storage::get('ml/svr_nilai_pertemuan_berikutnya.ser'))->toBe('model nilai lama')
        ->and(Storage::exists(DatasetSintetisRisikoService::PATH))->toBeTrue()
        ->and(Storage::exists('ml/rf_kategori_risiko.ser'))->toBeTrue();
    $benchmark = json_decode(Storage::get('ml/benchmark_rf_kategori_risiko.json'), true);
    expect($benchmark['sumber_dataset'])->toContain('sintetis')
        ->and($benchmark['sumber_label'])->toBe(PrediksiRisikoService::METODE)
        ->and($benchmark['train_samples'])->toBe(336)
        ->and($benchmark['test_samples'])->toBe(144);
});

test('kegagalan pelatihan risiko dilaporkan sebagai kegagalan perintah', function () {
    $disk = 'risiko-gagal-'.uniqid();
    config(['filesystems.default' => $disk]);
    Storage::fake($disk);
    $this->mock(KlasifikasiRisikoService::class)
        ->shouldReceive('train')
        ->once()
        ->andThrow(new RuntimeException('Pelatihan gagal'));

    $this->artisan('ml:train-sintetis-risiko')
        ->expectsOutputToContain('Pelatihan gagal')
        ->assertFailed();
});
