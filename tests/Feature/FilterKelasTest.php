<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\ProfilPendidikan;

uses(RefreshDatabase::class);

test('filter kelas tersedia berdasarkan pencarian kata kunci', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $guru = User::factory()->pengajar()->verified()->create();
    Kelas::factory()->create(['nama_kelas' => 'Matematika Dasar', 'guru_id' => $guru->id]);
    Kelas::factory()->create(['nama_kelas' => 'Bahasa Indonesia', 'guru_id' => $guru->id]);

    $this->actingAs($siswa)->get(route('pelajar.kelas.available', ['search' => 'Matematika']))
        ->assertOk()
        ->assertSee('Matematika Dasar');
});
