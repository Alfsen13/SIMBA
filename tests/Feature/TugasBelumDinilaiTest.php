<?php

use App\Models\Kelas;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\MLService;

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $this->kelas = Kelas::factory()->create([
        'guru_id' => $this->guru->id,
    ]);

    $this->pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
});

function tugasLampau(Kelas $kelas, Pertemuan $pertemuan, string $judul, int $hariLalu = 5): Tugas
{
    return Tugas::create([
        'judul' => $judul,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'guru_id' => $kelas->guru_id,
        'tipe' => 'upload',
        'deadline' => now()->subDays($hariLalu),
    ]);
}

test('halaman tugas tidak menampilkan peringatan bila semua pengumpulan sudah dinilai', function () {
    $tugas = tugasLampau($this->kelas, $this->pertemuan, 'Tugas Sudah Dinilai');

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'jawaban' => [],
        'nilai' => 85,
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.index'))
        ->assertOk()
        ->assertDontSee('pengumpulan belum dinilai');
});

test('halaman tugas memberi tahu jumlah pengumpulan yang belum dinilai', function () {
    $tugas = tugasLampau($this->kelas, $this->pertemuan, 'Tugas Belum Dinilai');

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'jawaban' => [],
        'nilai' => null,
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.index'))
        ->assertOk()
        ->assertSee('1 pengumpulan belum dinilai')
        ->assertSee('Tugas Belum Dinilai')
        ->assertSee(route('pengajar.tugas.penilaian', $tugas), escape: false);
});

test('tugas yang deadline-nya belum lewat tidak memunculkan peringatan', function () {
    $tugas = Tugas::create([
        'judul' => 'Tugas Masih Berjalan',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
        'guru_id' => $this->guru->id,
        'tipe' => 'upload',
        'deadline' => now()->addDays(3),
    ]);

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'jawaban' => [],
        'nilai' => null,
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.index'))
        ->assertOk()
        ->assertDontSee('pengumpulan belum dinilai');
});

test('siswa yang sudah mengumpulkan tapi belum dinilai tidak dihitung sebagai nilai 0', function () {
    // Konsisten dengan MLService: nilai null dikeluarkan dari pembilang & penyebut
    $tugas = tugasLampau($this->kelas, $this->pertemuan, 'Tugas Menunggu Penilaian');

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'jawaban' => [],
        'nilai' => null,
    ]);

    $fitur = app(MLService::class)->computeAcademicFeatures($this->siswa->id);

    // penyebut nilai_keseluruhan hanya kuis; tugas null tidak menambah 100
    expect($fitur['nilai_keseluruhan'])->toBeNull();
});
