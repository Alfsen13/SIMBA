<?php

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('filter kelas tersedia berdasarkan pencarian kata kunci', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $guru = User::factory()->pengajar()->verified()->create();
    Kelas::factory()->create(['nama_kelas' => 'Matematika Dasar', 'guru_id' => $guru->id]);
    Kelas::factory()->create(['nama_kelas' => 'Bahasa Indonesia', 'guru_id' => $guru->id]);

    $this->actingAs($siswa)->get(route('pelajar.kelas.available', ['search' => 'Matematika']))
        ->assertOk()
        ->assertSee('Matematika Dasar')
        ->assertSee('name="search"', false)
        ->assertSee('value="Matematika"', false);
});

test('pagination kelas tersedia mempertahankan filter pencarian', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $guru = User::factory()->pengajar()->verified()->create();

    foreach (range(1, 11) as $nomor) {
        Kelas::factory()->create([
            'nama_kelas' => "Matematika {$nomor}",
            'guru_id' => $guru->id,
        ]);
    }

    $this->actingAs($siswa)
        ->get(route('pelajar.kelas.available', ['search' => 'Matematika']))
        ->assertOk()
        ->assertSee('search=Matematika', false);
});
