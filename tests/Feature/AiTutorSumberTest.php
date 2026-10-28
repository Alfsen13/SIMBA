<?php

/*
 * Rujukan sumber (chip sitasi) di jawaban AI Tutor.
 *
 * AI menulis sitasi `[[Judul Materi]]`; klien mengubahnya jadi link memakai
 * `window.materiUrlMap`. Judul dari AI sering beda kapitalisasi dari kolom
 * `materi.judul` (DB menyimpan huruf kecil), sehingga peta dikunci dengan
 * judul ternormalisasi (huruf kecil, tanpa tanda baca/label halaman).
 *
 * Prefix helper: `aiTutor` (wajib unik - fungsi di file Pest bersifat global).
 */

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function aiTutorGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function aiTutorKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function aiTutorSiswa(Kelas $kelas): User
{
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $siswa;
}

function aiTutorPertemuan(Kelas $kelas): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan AI',
        'urutan' => 1,
        'dibuka' => true,
    ]);
}

function aiTutorUrlMap(string $html): array
{
    preg_match('/window\.materiUrlMap = (\{.*?\});/s', $html, $m);

    return $m ? (json_decode($m[1], true) ?: []) : [];
}

it('peta url materi memakai kunci ternormalisasi agar sitasi AI beda kapitalisasi tetap diklik', function () {
    $guru = aiTutorGuru();
    $kelas = aiTutorKelas($guru);
    $pertemuan = aiTutorPertemuan($kelas);

    $materi = Materi::factory()->create([
        'judul' => 'Konsep Dasar Manajemen Rantai Pasok',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $siswa = aiTutorSiswa($kelas);

    $response = $this->actingAs($siswa)->get(route('pelajar.ai-tutor.chat', [$kelas, $pertemuan]));
    $response->assertOk();

    $map = aiTutorUrlMap($response->getContent());

    expect($map)->toHaveKey('konsep dasar manajemen rantai pasok')
        ->and($map['konsep dasar manajemen rantai pasok'])
        ->toBe(route('pelajar.materi.show', $materi->id));
});

it('judul materi dengan tanda baca dan spasi ganda dinormalisasi sama', function () {
    $guru = aiTutorGuru();
    $kelas = aiTutorKelas($guru);
    $pertemuan = aiTutorPertemuan($kelas);

    Materi::factory()->create([
        'judul' => 'Hukum  Hidrostatistika, dan Pengapian!',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $siswa = aiTutorSiswa($kelas);

    $response = $this->actingAs($siswa)->get(route('pelajar.ai-tutor.chat', [$kelas, $pertemuan]));
    $response->assertOk();

    expect(aiTutorUrlMap($response->getContent()))
        ->toHaveKey('hukum hidrostatistika dan pengapian');
});
