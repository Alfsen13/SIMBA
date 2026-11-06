<?php

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('kelas ditutup tidak ditampilkan pada daftar tersedia', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $guru = User::factory()->pengajar()->verified()->create();
    Kelas::factory()->create(['nama_kelas' => 'Kelas Dibuka', 'guru_id' => $guru->id, 'status' => 'dibuka']);
    Kelas::factory()->create(['nama_kelas' => 'Kelas Ditutup', 'guru_id' => $guru->id, 'status' => 'ditutup']);

    $this->actingAs($siswa)->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee('Kelas Dibuka')
        ->assertDontSee('Kelas Ditutup');
});

test('pencarian tidak menampilkan kelas ditutup yang kodenya cocok', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $guru = User::factory()->pengajar()->verified()->create();
    Kelas::factory()->create([
        'nama_kelas' => 'Kelas Ditutup',
        'kode_kelas' => 'CARI-1234',
        'guru_id' => $guru->id,
        'status' => 'ditutup',
    ]);

    $this->actingAs($siswa)
        ->get(route('pelajar.kelas.available', ['search' => 'CARI-1234']))
        ->assertOk()
        ->assertDontSee('Kelas Ditutup');
});
