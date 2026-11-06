<?php

use App\Models\MateriRemedial;
use App\Models\User;

test('daftar remedial siswa dipaginasi dan mempertahankan total riwayat', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);

    foreach (range(1, 21) as $nomor) {
        MateriRemedial::create([
            'siswa_id' => $siswa->id,
            'topik_lemah' => "Remedial siswa {$nomor}",
            'generated_at' => now()->subMinutes($nomor),
        ]);
    }

    $this->actingAs($siswa)
        ->get(route('pelajar.remedial.index'))
        ->assertOk()
        ->assertViewHas('remedials', fn ($remedials) => $remedials->total() === 21 && $remedials->count() === 20)
        ->assertSee('Remedial siswa 1')
        ->assertDontSee('Remedial siswa 21')
        ->assertSee('page=2', false);

    $this->actingAs($siswa)
        ->get(route('pelajar.remedial.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Remedial siswa 21');
});
