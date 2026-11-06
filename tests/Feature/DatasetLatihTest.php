<?php

use App\Services\DatasetLatihService;
use App\Services\FiturPertemuanService;
use App\Services\KlasifikasiRisikoService;
use App\Services\PrediksiMendatangService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake();
});

/**
 * Snapshot sintetis yang cukup untuk `ml:train`: 12 siswa, 36 baris RF, dan 48
 * pasangan SVR. 3 siswa terakhir = kelompok uji `splitGrouped`, dan ketiganya
 * sengaja diberi label berbeda supaya ketiga kelas muncul di data uji.
 *
 * Rentang nilai mengikuti aslinya: sampel SVR mentah 0-100 (dinormalkan di
 * `trainOne`), sampel RF sudah 0-1 (dinormalkan di `buildDataset`).
 *
 * @return array<string, mixed>
 */
function ddlSnapshot(): array
{
    $label = KlasifikasiRisikoService::LABELS;
    $profil = [
        'aman' => [95.0, 0.0, 0.0, 95.0],
        'perlu_pantau' => [70.0, 25.0, 15.0, 70.0],
        'berisiko_tinggi' => [45.0, 60.0, 45.0, 45.0],
    ];

    $rfSamples = $rfLabels = $rfSiswa = [];
    $svrSamples = $svrTargets = $svrSiswa = $svrKelas = [];

    for ($siswa = 1; $siswa <= 12; $siswa++) {
        $kategori = $label[($siswa - 1) % 3];
        $dasar = $profil[$kategori];
        $nilai = $dasar[0];

        for ($k = 0; $k < 3; $k++) {
            $rfSamples[] = array_map(
                fn ($v) => min(1.0, max(0.0, ($v + $k) / 100)),
                $dasar
            );
            $rfLabels[] = $kategori;
            $rfSiswa[] = $siswa;
        }

        for ($k = 0; $k < 4; $k++) {
            $svrSamples[] = [$nilai, 100.0, $nilai, $nilai, $nilai, 0.0];
            $svrTargets[] = max(0.0, $nilai - $k * 2);
            $svrSiswa[] = $siswa;
            $svrKelas[] = 1;
        }
    }

    return [
        'versi' => 'dataset-uji',
        'dibuat_pada' => now()->toIso8601String(),
        'sumber' => 'test',
        'rf' => [
            'target' => KlasifikasiRisikoService::TARGET,
            'fitur' => KlasifikasiRisikoService::FITUR,
            'label' => KlasifikasiRisikoService::LABELS,
            'sumber_label' => 'baseline_deviation_scoring',
            'samples' => $rfSamples,
            'labels' => $rfLabels,
            'siswa_ids' => $rfSiswa,
        ],
        'svr' => [
            'target' => PrediksiMendatangService::TARGET,
            'fitur' => FiturPertemuanService::fiturModel(),
            'samples' => $svrSamples,
            'targets' => $svrTargets,
            'siswa_ids' => $svrSiswa,
            'kelas_ids' => $svrKelas,
        ],
        'ringkasan' => [
            'rf_samples' => count($rfSamples),
            'rf_siswa_unik' => count(array_unique($rfSiswa)),
            'svr_samples' => count($svrSamples),
            'svr_siswa_unik' => count(array_unique($svrSiswa)),
            'distribusi_label' => [
                'aman' => 12,
                'perlu_pantau' => 12,
                'berisiko_tinggi' => 12,
            ],
        ],
    ];
}

test('snapshot ditulis lalu dimuat kembali utuh', function () {
    $svc = app(DatasetLatihService::class);
    $asli = ddlSnapshot();

    expect($svc->ada())->toBeFalse();

    $svc->tulis($asli);

    expect($svc->ada())->toBeTrue();

    $dimuat = $svc->muat();

    expect($dimuat)->not->toBeNull()
        ->and($svc->datasetRf($dimuat))->toEqual([$asli['rf']['samples'], $asli['rf']['labels'], $asli['rf']['siswa_ids']])
        ->and($svc->datasetSvr($dimuat))->toEqual([
            $asli['svr']['samples'],
            $asli['svr']['targets'],
            $asli['svr']['siswa_ids'],
            $asli['svr']['kelas_ids'],
        ])
        ->and($svc->alasanFiturTidakCocok($dimuat))->toBeNull();
});

test('snapshot yang diperbarui menggantikan berkas tanpa meninggalkan file sementara', function () {
    $svc = app(DatasetLatihService::class);
    $lama = ddlSnapshot();
    $baru = ddlSnapshot();
    $baru['versi'] = 'dataset-uji-baru';

    $svc->tulis($lama);
    $svc->tulis($baru);

    expect($svc->muat()['versi'])->toBe('dataset-uji-baru')
        ->and(Storage::allFiles('ml'))->toBe([DatasetLatihService::PATH]);
});

test('snapshot lama tetap utuh jika data baru gagal diserialisasi', function () {
    $svc = app(DatasetLatihService::class);
    $lama = ddlSnapshot();
    $svc->tulis($lama);
    $resource = fopen('php://memory', 'r');

    expect(fn () => $svc->tulis(['resource' => $resource]))
        ->toThrow(JsonException::class);
    fclose($resource);

    expect($svc->muat())->toEqual($lama)
        ->and(Storage::allFiles('ml'))->toBe([DatasetLatihService::PATH]);
});

test('snapshot tidak lengkap ditolak sebagai dataset', function () {
    $svc = app(DatasetLatihService::class);

    $tanpaRf = ddlSnapshot();
    unset($tanpaRf['rf']);

    $tanpaSvr = ddlSnapshot();
    unset($tanpaSvr['svr']);

    $timpang = ddlSnapshot();
    $timpang['svr']['targets'] = [1.0, 2.0];

    expect($svc->datasetRf($tanpaRf))->toBeNull()
        ->and($svc->datasetSvr($tanpaRf))->not->toBeNull()
        ->and($svc->datasetSvr($tanpaSvr))->toBeNull()
        ->and($svc->datasetRf($tanpaSvr))->not->toBeNull()
        ->and($svc->datasetSvr($timpang))->toBeNull()
        ->and($svc->datasetRf($timpang))->not->toBeNull();
});

test('daftar fitur snapshot yang sudah berubah dilaporkan', function () {
    $svc = app(DatasetLatihService::class);

    $lama = ddlSnapshot();
    $lama['svr']['fitur'] = ['rata_nilai_lama'];

    $gantiFitur = ddlSnapshot();
    $gantiFitur['rf']['fitur'] = ['rata_nilai'];

    expect($svc->alasanFiturTidakCocok(ddlSnapshot()))->toBeNull()
        ->and($svc->alasanFiturTidakCocok($lama))->toContain('tidak sama')
        ->and($svc->alasanFiturTidakCocok($gantiFitur))->toContain('tidak sama');
});

test('ml:train melatih SVR & RF dari berkas snapshot tanpa data di database', function () {
    app(DatasetLatihService::class)->tulis(ddlSnapshot());

    $this->artisan('ml:train', ['--dataset' => DatasetLatihService::PATH])
        ->assertExitCode(0);

    Storage::assertExists('ml/svr_'.PrediksiMendatangService::TARGET.'.ser');
    Storage::assertExists('ml/svr_'.PrediksiMendatangService::TARGET.'_medians.json');
    Storage::assertExists('ml/benchmark_'.PrediksiMendatangService::TARGET.'.json');
    Storage::assertExists(app(KlasifikasiRisikoService::class)->modelPath());
    Storage::assertExists(app(KlasifikasiRisikoService::class)->benchmarkPath());

    $benchmark = json_decode(
        Storage::get('ml/benchmark_'.PrediksiMendatangService::TARGET.'.json'),
        true
    );

    expect($benchmark['fitur'])->toBe(FiturPertemuanService::fiturModel())
        ->and($benchmark['pemenang'])->toBe('svr_linear');
});

test('ml:train menolak snapshot berfitur lama dan tidak menulis model', function () {
    $lama = ddlSnapshot();
    $lama['rf']['fitur'] = ['rata_nilai'];

    app(DatasetLatihService::class)->tulis($lama);

    $this->artisan('ml:train', ['--dataset' => DatasetLatihService::PATH])
        ->assertExitCode(1);

    Storage::assertMissing(app(KlasifikasiRisikoService::class)->modelPath());
    Storage::assertMissing('ml/svr_'.PrediksiMendatangService::TARGET.'.ser');
});

test('ml:train menolak snapshot yang tidak ada', function () {
    $this->artisan('ml:train', ['--dataset' => 'ml/tidak-ada.json'])
        ->assertExitCode(1);

    Storage::assertMissing('ml/svr_'.PrediksiMendatangService::TARGET.'.ser');
});
