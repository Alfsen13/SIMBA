<?php

use App\Models\User;
use App\Services\MLService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->ml = app(MLService::class);
});

test('computeAcademicFeatures mengembalikan 4 kunci parameter', function () {
    $siswa = User::factory()->create();

    $features = $this->ml->computeAcademicFeatures($siswa->id);

    expect($features)->toHaveKeys([
        'rata_nilai',
        'persen_terlambat',
        'persen_terlewat',
        'nilai_keseluruhan',
    ]);
});

test('label fitur urut sama dengan urutan feature vector', function () {
    expect(array_keys(MLService::FEATURE_LABELS))->toBe(MLService::FEATURES);
});
