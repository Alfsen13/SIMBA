<?php

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\User;

beforeEach(function () {
    $this->operator = User::factory()->operator()->create([
        'kategori_mapel' => 'Matematika',
    ]);
});

test('operator can browse teacher materials from subject to material detail', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
        'aktif' => true,
    ]);
    $pengajar = User::factory()->pengajar()->create([
        'name' => 'Pengajar Matematika',
    ]);
    $kelas = Kelas::factory()->create([
        'nama_kelas' => 'Kelas Matematika',
        'kategori' => 'Matematika',
        'mata_pelajaran_id' => $mataPelajaran->id,
        'guru_id' => $pengajar->id,
    ]);
    $materi = Materi::factory()->create([
        'judul' => 'Persamaan Linear',
        'kelas_id' => $kelas->id,
        'guru_id' => $pengajar->id,
    ]);

    $this->actingAs($this->operator)
        ->get(route('operator.mata-pelajaran.index'))
        ->assertOk()
        ->assertSee(route('operator.mata-pelajaran.show', $mataPelajaran));

    $this->get(route('operator.mata-pelajaran.show', $mataPelajaran))
        ->assertOk()
        ->assertSee($kelas->nama_kelas)
        ->assertSee(route('operator.kelas.materi', $kelas));

    $this->get(route('operator.kelas.materi', $kelas))
        ->assertOk()
        ->assertSee($materi->judul)
        ->assertSee(route('operator.materi.show', $materi));

    $this->get(route('operator.materi.show', $materi))
        ->assertOk()
        ->assertSee($materi->judul)
        ->assertSee($pengajar->name);
});

test('operator cannot browse materials outside their subject category', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'bahasa',
        'nama' => 'Bahasa Indonesia',
        'kategori' => 'Bahasa Indonesia',
        'aktif' => true,
    ]);
    $kelas = Kelas::factory()->create([
        'kategori' => 'Bahasa Indonesia',
        'mata_pelajaran_id' => $mataPelajaran->id,
    ]);

    $this->actingAs($this->operator)
        ->get(route('operator.mata-pelajaran.show', $mataPelajaran))
        ->assertForbidden();

    $this->get(route('operator.kelas.materi', $kelas))
        ->assertForbidden();
});
