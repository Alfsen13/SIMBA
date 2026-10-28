<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\ProfilPendidikan;

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
