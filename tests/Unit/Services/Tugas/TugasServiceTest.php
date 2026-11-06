<?php

declare(strict_types=1);

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\Tugas\TugasService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tgGuru(): User
{
    return User::factory()->pengajar()->create();
}

function tgKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function tgSiswa(int $kelasId): User
{
    $siswa = User::factory()->create(['role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelasId);

    return $siswa;
}

function tgPertemuan(int $kelasId): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelasId,
        'nama_pertemuan' => 'Pertemuan',
        'urutan' => 1,
    ]);
}

it('membuat tugas dengan normalisasi soal dari string json', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas',
        'tipe' => 'pilihan_ganda',
        'soal' => json_encode([
            ['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 1],
        ]),
        'deadline' => now()->addDay(),
    ], $guru->id);

    expect($tugas->guru_id)->toBe($guru->id)
        ->and($tugas->soal)->toBeArray()
        ->and($tugas->soal[0]['pilihan'])->toBe(['a', 'b', 'c', 'd'])
        ->and($tugas->soal[0]['jawaban'])->toBe(1);
});

it('mengubah tipe soal menjadi null nya soal kosong', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas upload',
        'tipe' => 'upload',
        'soal' => null,
        'deadline' => null,
    ], $guru->id);

    expect($tugas->soal)->toBeNull();
});

it('normalisasi dekode json string sekaligus data array', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Esai',
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Q', 'referensi' => 'K']],
        'deadline' => null,
    ], $guru->id);

    // `soal` tanpa level tetap disimpan utuh, lalu dilengkapi level Bloom
    // otomatis oleh normalisasiSoal (dropdown builder hanya punya
    // "Tidak ditentukan"). Pertanyaan "Q" tidak memuat kata kerja kognitif,
    // jadi heuristics jatuh ke C2 di dalam rentang default C2-C4.
    expect($tugas->soal)->toBe([['pertanyaan' => 'Q', 'referensi' => 'K', 'bloom' => 'C2']]);
});

it('menyimpan materi pendukung dalam pivot tanpa kolom timestamp', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);
    $materi = Materi::factory()->create(['kelas_id' => $kelas->id]);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas',
        'tipe' => 'upload',
    ], $guru->id, [$materi->id]);

    $tugas->unsetRelations();
    expect($tugas->materiPendukung()->pluck('materi.id'))->toContain($materi->id);

    $catatan = $tugas->materiPendukung()->get();

    // Memastikan pivot tidak memaksa kolom timestamp yang tidak ada.
    expect($catatan->pluck('id'))->toContain($materi->id);
});

it('membatalkan pembuatan tugas jika sinkronisasi materi pendukung gagal', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);

    expect(fn () => app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas gagal',
        'tipe' => 'upload',
    ], $guru->id, [PHP_INT_MAX]))->toThrow(QueryException::class);

    expect(Tugas::where('judul', 'Tugas gagal')->exists())->toBeFalse();
});

it('sync materi pendukung menghapus materi yang dilepas', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);
    $materiA = Materi::factory()->create(['kelas_id' => $kelas->id]);
    $materiB = Materi::factory()->create(['kelas_id' => $kelas->id]);

    $svc = app(TugasService::class);
    $tugas = $svc->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas',
        'tipe' => 'upload',
    ], $guru->id, [$materiA->id, $materiB->id]);

    $svc->perbarui($tugas, [
        'judul' => 'Tugas (edit)',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'tipe' => 'upload',
    ], [$materiB->id]);

    $tugas->unsetRelations();
    expect($tugas->materiPendukung()->pluck('materi.id')->all())->toBe([$materiB->id]);
    expect($tugas->materiPendukung()->count())->toBe(1);
});

it('membatalkan perubahan tugas jika sinkronisasi materi pendukung gagal', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);
    $materi = Materi::factory()->create(['kelas_id' => $kelas->id]);
    $service = app(TugasService::class);
    $tugas = $service->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Judul lama',
        'tipe' => 'upload',
    ], $guru->id, [$materi->id]);

    expect(fn () => $service->perbarui($tugas, [
        'judul' => 'Judul baru',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'tipe' => 'upload',
    ], [PHP_INT_MAX]))->toThrow(QueryException::class);

    expect($tugas->fresh()->judul)->toBe('Judul lama')
        ->and($tugas->materiPendukung()->pluck('materi.id')->all())->toBe([$materi->id]);
});

it('perbarui mempertahankan soal lama saat request tidak membawa soal', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Esai',
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Q1', 'referensi' => '']],
    ], $guru->id);

    $updated = app(TugasService::class)->perbarui($tugas, [
        'judul' => 'Ganti judul',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'tipe' => 'essai',
    ]);

    // Soal lama ikut dilengkapi level Bloom (C2) saat pertama kali disimpan, dan
    // level itu harus tetap utuh saat kueri update tidak membawa `soal`.
    expect($updated->judul)->toBe('Ganti judul')
        ->and($updated->soal)->toBe([['pertanyaan' => 'Q1', 'referensi' => '', 'bloom' => 'C2']]);
});

it('perbarui menghapus soal saat form mengirim json kosong', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Esai',
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Q1', 'referensi' => '']],
    ], $guru->id);

    $updated = app(TugasService::class)->perbarui($tugas, [
        'judul' => 'Bersihkan soal',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'tipe' => 'essai',
        'soal' => '[]',
    ]);

    expect($updated->soal)->toBeNull();
});

it('menghapus tugas beserta pengumpulannya', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);
    $siswa = tgSiswa($kelas->id);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas',
        'tipe' => 'upload',
    ], $guru->id);

    PengumpulanTugas::create(['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id]);

    app(TugasService::class)->hapus($tugas);

    expect(Tugas::find($tugas->id))->toBeNull();
});

it('daftar tugas hanya untuk guru pemilik', function () {
    $guruA = tgGuru();
    $guruB = tgGuru();
    $pertemuan = tgPertemuan(tgKelas($guruA)->id);
    $pertemuanB = tgPertemuan(tgKelas($guruB)->id);

    app(TugasService::class)->buat([
        'kelas_id' => $pertemuan->kelas_id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas A',
        'tipe' => 'upload',
    ], $guruA->id);

    app(TugasService::class)->buat([
        'kelas_id' => $pertemuanB->kelas_id,
        'pertemuan_id' => $pertemuanB->id,
        'judul' => 'Tugas B',
        'tipe' => 'upload',
    ], $guruB->id);

    $hanyaA = app(TugasService::class)->daftarUntukGuru($guruA->id);

    expect($hanyaA->total())->toBe(1)
        ->and($hanyaA->items()[0]->judul)->toBe('Tugas A')
        ->and($hanyaA->items()[0]->belum_dinilai)->toBe(0);
});

it('menghitung pengumpulan belum dinilai pada daftar guru', function () {
    $guru = tgGuru();
    $pertemuan = tgPertemuan(tgKelas($guru)->id);
    $siswa = tgSiswa($pertemuan->kelas_id);

    $tugas = app(TugasService::class)->buat([
        'kelas_id' => $pertemuan->kelas_id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Butuh dinilai',
        'tipe' => 'pilihan_ganda',
        'soal' => [['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0]],
    ], $guru->id);

    PengumpulanTugas::create(['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id]);

    $daftar = app(TugasService::class)->daftarUntukGuru($guru->id);

    expect($daftar->items()[0]->belum_dinilai)->toBe(1);
});

it('tugasBelumDinilai hanya yang lewat deadline dan belum dinilai', function () {
    $guru = tgGuru();
    $pertemuan = tgPertemuan(tgKelas($guru)->id);
    $siswa = tgSiswa($pertemuan->kelas_id);
    $svc = app(TugasService::class);

    $lewat = $svc->buat([
        'kelas_id' => $pertemuan->kelas_id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Lewat',
        'tipe' => 'pilihan_ganda',
        'soal' => [['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0]],
        'deadline' => now()->subDay(),
    ], $guru->id);

    $belumlewat = $svc->buat([
        'kelas_id' => $pertemuan->kelas_id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Masih buka',
        'tipe' => 'pilihan_ganda',
        'soal' => [['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0]],
        'deadline' => now()->addDay(),
    ], $guru->id);

    PengumpulanTugas::create(['tugas_id' => $lewat->id, 'siswa_id' => $siswa->id]);
    PengumpulanTugas::create(['tugas_id' => $belumlewat->id, 'siswa_id' => $siswa->id]);

    expect($svc->tugasBelumDinilai($guru->id)->pluck('id'))->toContain($lewat->id)
        ->not->toContain($belumlewat->id);
});

it('daftar siswa menampilkan status sudah dikumpulkan per siswa', function () {
    $guru = tgGuru();
    $pertemuan = tgPertemuan(tgKelas($guru)->id);
    $siswa = tgSiswa($pertemuan->kelas_id);
    $svc = app(TugasService::class);

    $tugas = $svc->buat([
        'kelas_id' => $pertemuan->kelas_id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Untuk siswa',
        'tipe' => 'upload',
    ], $guru->id);

    PengumpulanTugas::create(['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id]);

    $daftar = $svc->daftarUntukSiswa($siswa);

    expect($daftar->total())->toBe(1)
        ->and($daftar->items()[0]->sudah_dikumpulkan)->toBe(1);
});

it('siswa tidak melihat tugas dari kelas yang tidak diikutinya', function () {
    $guru = tgGuru();
    $pertemuanLain = tgPertemuan(tgKelas($guru)->id);
    $siswa = User::factory()->create(['role' => 'pelajar']);
    $svc = app(TugasService::class);

    $svc->buat([
        'kelas_id' => $pertemuanLain->kelas_id,
        'pertemuan_id' => $pertemuanLain->id,
        'judul' => 'Tidak untuk siswa ini',
        'tipe' => 'upload',
    ], $guru->id);

    expect($svc->daftarUntukSiswa($siswa)->total())->toBe(0);
});

it('pertemuanUntukGuru mengelompokkan per kelas hanya milik guru itu', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $pertemuan = tgPertemuan($kelas->id);

    $guruLain = tgGuru();
    $kelasLain = tgKelas($guruLain);
    tgPertemuan($kelasLain->id);

    $kelompok = app(TugasService::class)->pertemuanUntukGuru($guru->id);

    expect($kelompok->keys())->toContain($kelas->id)
        ->not->toContain($kelasLain->id);
    expect($kelompok[$kelas->id]->first()->id)->toBe($pertemuan->id);
});

it('semuaMateriUntukGuru hanya materi dari kelas guru itu', function () {
    $guru = tgGuru();
    $kelas = tgKelas($guru);
    $materiSendiri = Materi::factory()->create(['kelas_id' => $kelas->id]);

    $guruLain = tgGuru();
    $materiLain = Materi::factory()->create(['kelas_id' => tgKelas($guruLain)->id]);

    $materi = app(TugasService::class)->semuaMateriUntukGuru($guru->id);

    expect($materi->pluck('id'))->toContain($materiSendiri->id)
        ->not->toContain($materiLain->id);
});
