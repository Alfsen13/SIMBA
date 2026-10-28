<?php

use App\Models\Kelas;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create([
        'guru_id' => $this->guru->id,
    ]);
    $this->pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
});

test('halaman penilaian menampilkan soal dan jawaban siswa pilihan ganda', function () {
    $siswa = User::factory()->verified()->create([
        'name' => 'Siswa Satu',
        'role' => 'pelajar',
    ]);
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $tugas = Tugas::create([
        'judul' => 'PG',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'pilihan_ganda',
        'soal' => [
            ['pertanyaan' => 'Ibu kota Indonesia?', 'pilihan' => ['Bandung', 'Jakarta', 'Surabaya', 'Medan'], 'jawaban' => 1],
        ],
    ]);

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['2'],
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.penilaian', $tugas))
        ->assertOk()
        ->assertSee('Siswa Satu')
        ->assertSee('Ibu kota Indonesia?')
        ->assertSee('Surabaya')
        ->assertSee('Jakarta');
});
