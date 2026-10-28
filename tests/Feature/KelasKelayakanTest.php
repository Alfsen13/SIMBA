<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Kelas;
use App\Models\User;

uses(RefreshDatabase::class);

test('kelas punya kolom kelayakan', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create([
        'guru_id' => $guru->id,
        'status' => 'dibuka',
        'aturan_kelayakan' => 'sesuai_profil',
    ]);

    expect($kelas->fresh()->aturan_kelayakan)->toBe('sesuai_profil');
});
