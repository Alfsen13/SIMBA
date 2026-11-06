<?php

use App\Models\Kelas;
use App\Models\MateriRemedial;
use App\Models\User;

test('guru hanya memuat satu halaman remedial dan dapat membuka halaman berikutnya', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    foreach (range(1, 21) as $nomor) {
        MateriRemedial::create([
            'siswa_id' => $siswa->id,
            'topik_lemah' => "Topik remedial {$nomor}",
            'generated_at' => now()->subMinutes($nomor),
        ]);
    }

    $this->actingAs($guru)
        ->get(route('pengajar.remedial.index'))
        ->assertOk()
        ->assertSee('21 materi')
        ->assertSee('Topik remedial 1')
        ->assertDontSee('Topik remedial 21')
        ->assertSee('page=2', false);

    $this->actingAs($guru)
        ->get(route('pengajar.remedial.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Topik remedial 21');
});
