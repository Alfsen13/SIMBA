<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AplikasiPengajar;

uses(RefreshDatabase::class);

test('aplikasi pengajar terpisah dari status akun', function () {
    $guru = User::factory()->pengajar()->create(['status' => 'pending']);
    $aplikasi = AplikasiPengajar::create([
        'user_id' => $guru->id,
        'status_aplikasi' => 'pending',
    ]);

    expect($aplikasi->status_aplikasi)->toBe('pending');
    expect($guru->status)->toBe('pending');
});
