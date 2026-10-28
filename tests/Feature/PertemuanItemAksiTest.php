<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// Prefix helper unik per file (fungsi global di Pest) — jangan diganti generik.
function piaGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function piaKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function piaPertemuan(Kelas $kelas): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan Uji',
        'urutan' => 1,
        'deskripsi' => null,
    ]);
}

function piaTugas(Kelas $kelas, User $guru, Pertemuan $pertemuan): Tugas
{
    return Tugas::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
}

function piaKuis(Kelas $kelas, Pertemuan $pertemuan): Kuis
{
    return Kuis::create([
        'judul' => 'Kuis Uji',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'is_aktif' => true,
        'soal' => [['pertanyaan' => 'Soal uji.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
    ]);
}

it('guru melihat tombol hapus tugas dan kuis di halaman pertemuan', function () {
    $guru = piaGuru();
    $kelas = piaKelas($guru);
    $pertemuan = piaPertemuan($kelas);
    $tugas = piaTugas($kelas, $guru, $pertemuan);
    $kuis = piaKuis($kelas, $pertemuan);

    $this->actingAs($guru)
        ->get(route('pengajar.pertemuan.show', $pertemuan))
        ->assertOk()
        ->assertSee(route('pengajar.tugas.destroy', $tugas), false)
        ->assertSee(route('pengajar.kuis.destroy', $kuis), false);
});

it('guru dapat menghapus tugas dan kembali ke halaman pertemuan', function () {
    $guru = piaGuru();
    $kelas = piaKelas($guru);
    $pertemuan = piaPertemuan($kelas);
    $tugas = piaTugas($kelas, $guru, $pertemuan);

    $this->actingAs($guru)
        ->delete(route('pengajar.tugas.destroy', $tugas))
        ->assertRedirect(route('pengajar.pertemuan.show', $pertemuan));

    $this->assertDatabaseMissing('tugas', ['id' => $tugas->id]);
});

it('guru dapat menghapus kuis dan kembali ke halaman pertemuan', function () {
    $guru = piaGuru();
    $kelas = piaKelas($guru);
    $pertemuan = piaPertemuan($kelas);
    $kuis = piaKuis($kelas, $pertemuan);

    $this->actingAs($guru)
        ->delete(route('pengajar.kuis.destroy', $kuis))
        ->assertRedirect(route('pengajar.pertemuan.show', $pertemuan));

    $this->assertDatabaseMissing('kuis', ['id' => $kuis->id]);
});

it('guru lain tidak dapat menghapus tugas atau kuis', function () {
    $guru = piaGuru();
    $kelas = piaKelas($guru);
    $pertemuan = piaPertemuan($kelas);
    $tugas = piaTugas($kelas, $guru, $pertemuan);
    $kuis = piaKuis($kelas, $pertemuan);

    $lain = piaGuru();

    $this->actingAs($lain)->delete(route('pengajar.tugas.destroy', $tugas))->assertForbidden();
    $this->actingAs($lain)->delete(route('pengajar.kuis.destroy', $kuis))->assertForbidden();

    $this->assertDatabaseHas('tugas', ['id' => $tugas->id]);
    $this->assertDatabaseHas('kuis', ['id' => $kuis->id]);
});

it('halaman detail materi menampilkan viewer dokumen pdf', function () {
    Bus::fake();
    Storage::fake('public');
    Storage::disk('public')->put('materi/lihat.pdf', '%PDF-1.4 dummy');

    $guru = piaGuru();
    $kelas = piaKelas($guru);
    $pertemuan = piaPertemuan($kelas);

    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
        'file_path' => 'materi/lihat.pdf',
        'file_type' => 'pdf',
    ]);

    $this->actingAs($guru)
        ->get(route('pengajar.materi.show', $materi))
        ->assertOk()
        ->assertSee('<iframe', false)
        ->assertSee(Storage::url('materi/lihat.pdf'), false);
});

it('halaman tambah materi menampilkan pratinjau dokumen', function () {
    $guru = piaGuru();
    $kelas = piaKelas($guru);
    $pertemuan = piaPertemuan($kelas);

    $this->actingAs($guru)
        ->get(route('pengajar.materi.create', ['pertemuan_id' => $pertemuan->id]))
        ->assertOk()
        ->assertSee('pilih($event)', false);
});

it('halaman edit materi menampilkan viewer file saat ini', function () {
    Storage::fake('public');
    Bus::fake();
    Storage::disk('public')->put('materi/lama.pdf', '%PDF-1.4 dummy');

    $guru = piaGuru();
    $kelas = piaKelas($guru);
    $pertemuan = piaPertemuan($kelas);

    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
        'file_path' => 'materi/lama.pdf',
        'file_type' => 'pdf',
    ]);

    $this->actingAs($guru)
        ->get(route('pengajar.materi.edit', $materi))
        ->assertOk()
        ->assertSee(Storage::url('materi/lama.pdf'), false);
});
