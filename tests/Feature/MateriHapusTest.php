<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// Prefix helper unik per file (fungsi global di Pest) — jangan diganti generik.
function mhpsGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function mhpsKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function mhpsPertemuan(Kelas $kelas): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan Uji',
        'urutan' => 1,
        'deskripsi' => null,
    ]);
}

it('guru melihat tombol hapus materi di halaman pertemuan', function () {
    $guru = mhpsGuru();
    $kelas = mhpsKelas($guru);
    $pertemuan = mhpsPertemuan($kelas);
    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $this->actingAs($guru)
        ->get(route('pengajar.pertemuan.show', $pertemuan))
        ->assertOk()
        ->assertSee(route('pengajar.materi.destroy', $materi), false)
        ->assertSee('Hapus');
});

it('guru dapat menghapus materi beserta filenya', function () {
    Bus::fake();
    Storage::fake('public');

    $guru = mhpsGuru();
    $kelas = mhpsKelas($guru);
    $pertemuan = mhpsPertemuan($kelas);

    Storage::disk('public')->put('materi/hapus.pdf', '%PDF-1.4 dummy');

    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
        'file_path' => 'materi/hapus.pdf',
        'file_type' => 'pdf',
    ]);

    $this->actingAs($guru)
        ->delete(route('pengajar.materi.destroy', $materi))
        ->assertRedirect(route('pengajar.pertemuan.show', $pertemuan));

    $this->assertDatabaseMissing('materi', ['id' => $materi->id]);
    Storage::disk('public')->assertMissing('materi/hapus.pdf');
});

it('guru lain tidak boleh menghapus materi', function () {
    $guru = mhpsGuru();
    $kelas = mhpsKelas($guru);
    $pertemuan = mhpsPertemuan($kelas);
    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $this->actingAs(mhpsGuru())
        ->delete(route('pengajar.materi.destroy', $materi))
        ->assertForbidden();

    $this->assertDatabaseHas('materi', ['id' => $materi->id]);
});
