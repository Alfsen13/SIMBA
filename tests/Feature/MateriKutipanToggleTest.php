<?php

/*
 * Tombol sembunyikan/tampilkan "Kutipan dari AI Tutor" di halaman materi pelajar.
 *
 * Prefix helper: `mkt` (wajib unik - fungsi di file Pest bersifat global).
 */

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function mktGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function mktKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function mktPertemuan(Kelas $kelas): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan Materi',
        'urutan' => 1,
        'dibuka' => true,
    ]);
}

function mktSiswa(Kelas $kelas): User
{
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $siswa;
}

it('halaman materi menampilkan tombol sembunyikan/tampilkan kutipan AI Tutor', function () {
    $guru = mktGuru();
    $kelas = mktKelas($guru);
    $pertemuan = mktPertemuan($kelas);

    $materi = Materi::factory()->create([
        'judul' => 'Materi Kutipan',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $document = Document::create([
        'name' => 'materi.pdf',
        'original_name' => 'materi.pdf',
        'path' => 'documents/Umum/materi.pdf',
        'user_id' => $guru->id,
        'materi_id' => $materi->id,
        'kelas_id' => $kelas->id,
        'status' => 'completed',
    ]);

    DocumentChunk::create([
        'document_id' => $document->id,
        'content' => 'Isi kutipan pertama dari dokumen.',
        'chunk_index' => 0,
    ]);

    $siswa = mktSiswa($kelas);

    $response = $this->actingAs($siswa)->get(route('pelajar.materi.show', $materi));

    $response->assertOk()
        ->assertSee('id="kutipan-section"', false)
        ->assertSee('x-data="{ open: true }"', false)
        ->assertSee('id="kutipan-list"', false)
        ->assertSee('@click="open = !open"', false)
        ->assertSee(__('siswa.materi.sembunyikan_kutipan'))
        ->assertSee(__('siswa.materi.tampilkan_kutipan'))
        ->assertSee('Isi kutipan pertama dari dokumen.', false);
});
