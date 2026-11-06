<?php

use App\Models\KategoriMataPelajaran;
use App\Models\MataPelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('calon pengajar bisa mendaftar dengan data dasar', function () {
    Storage::fake('local');

    MataPelajaran::create([
        'kode' => 'matematika', 'nama' => 'Matematika',
        'kategori' => 'Matematika & Ilmu Alam', 'aktif' => true,
    ]);
    KategoriMataPelajaran::create(['nama' => 'Matematika & Ilmu Alam']);

    $this->post(route('register'), [
        'name' => 'Guru Baru',
        'email' => 'guru.baru@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '1234567890',
        'pendidikan_tertinggi' => 'S1 Pendidikan Matematika',
        'pengalaman_mengajar' => '3 tahun',
        'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        'ijazah' => UploadedFile::fake()->create('ijazah.pdf', 100, 'application/pdf'),
        'kategori_mapel' => 'Matematika & Ilmu Alam',
        'mata_pelajaran' => ['Aljabar', 'Geometri'],
        'jenjang_dan_tingkat' => 'SMP kelas 7-9',
    ])->assertRedirect(route('pending'));

    $this->assertDatabaseHas('users', [
        'email' => 'guru.baru@example.com',
        'role' => 'pengajar',
        'status' => 'pending',
        'nip' => '1234567890',
    ]);
});
