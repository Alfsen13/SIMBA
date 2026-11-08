<?php

use App\Models\AplikasiPengajar;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('alur registrasi pelajar lengkap terintegrasi', function () {
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_9', 'nama' => 'Kelas 9', 'urutan' => 3, 'aktif' => true]);

    $response = $this->post(route('register'), [
        'name' => 'Pelajar Integrasi',
        'email' => 'integrasi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'nisn' => '0091234567',
        'jalur_id' => $jalur->id,
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
        'institusi' => 'SMP Integrasi',
        'minat' => 'Sains',
    ]);

    $response->assertRedirect(route('pending'));
    $this->assertDatabaseHas('users', ['email' => 'integrasi@example.com', 'status' => 'pending']);
    $this->assertDatabaseHas('profil_pendidikan', ['institusi' => 'SMP Integrasi', 'minat' => 'Sains']);
});

test('alur verifikasi pengajar terpisah dari status akun', function () {
    $guru = User::factory()->pengajar()->create(['status' => 'pending']);
    AplikasiPengajar::create([
        'user_id' => $guru->id,
        'status_aplikasi' => 'pending',
        'pendidikan_tertinggi' => 'S1',
    ]);

    expect($guru->status)->toBe('pending');
    expect($guru->fresh()->status)->toBe('pending');
});
