<?php

use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\TingkatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registrasi pelajar bisa menyimpan profil pendidikan', function () {
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 1, 'aktif' => true]);

    $response = $this->post(route('register'), [
        'name' => 'Siswa Baru',
        'email' => 'siswa.baru@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'jalur_id' => $jalur->id,
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
        'institusi' => 'SMPN 1',
        'program' => 'Reguler',
        'minat' => 'Matematika',
    ]);

    $response->assertRedirect(route('pending'));
    $this->assertDatabaseHas('profil_pendidikan', [
        'jalur_id' => $jalur->id,
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
        'institusi' => 'SMPN 1',
    ]);
});

test('registrasi pelajar belajar mandiri boleh tanpa jenjang dan tingkat', function () {
    $jalur = JalurPendidikan::create(['kode' => 'self_learning', 'nama' => 'Belajar Mandiri', 'urutan' => 3, 'aktif' => true]);

    $this->post(route('register'), [
        'name' => 'Belajar Mandiri',
        'email' => 'bm@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'jalur_id' => $jalur->id,
        'minat' => 'Pemrograman',
    ])->assertRedirect(route('pending'));

    $this->assertDatabaseHas('profil_pendidikan', [
        'jalur_id' => $jalur->id,
        'jenjang_id' => null,
        'tingkat_id' => null,
    ]);
});
