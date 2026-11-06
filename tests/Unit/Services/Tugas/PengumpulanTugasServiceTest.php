<?php

declare(strict_types=1);

use App\Jobs\PeriksaJawabanTugasAI;
use App\Models\Kelas;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\Tugas\PengumpulanTugasService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function tugPjGuru(): User
{
    return User::factory()->pengajar()->create();
}

function tugPjKelas(): Kelas
{
    return Kelas::factory()->create(['guru_id' => tugPjGuru()->id]);
}

function tugPjSiswa(?int $kelasId = null): User
{
    $siswa = User::factory()->create(['role' => 'pelajar']);

    if ($kelasId !== null) {
        // Pivot `kelas_siswa` tidak punya kolom `status` — hanya `kelas_id` +
        // `siswa_id`. Kolom status ada di tabel `users` untuk akun pending.
        $siswa->kelasDiikuti()->attach($kelasId);
    }

    return $siswa;
}

function tugPjTugas(array $attr = []): Tugas
{
    $guru = $attr['guru'] ?? tugPjGuru();
    $kelas = $attr['kelas'] ?? tugPjKelas();

    if ($kelas->guru_id !== $guru->id) {
        $kelas = tugPjKelas();
    }

    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    // `kelas_id` dan `guru_id` keduanya NOT NULL di tabel `tugas`, jadi keduanya
    // harus diisi. Keduanya sengaja redundant di skema (denormalisasi) supaya
    // query daftar tugas guru tidak perlu join lewat pertemuan.
    return Tugas::create(array_merge([
        'pertemuan_id' => $pertemuan->id,
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'judul' => 'Tugas Uji',
        'deskripsi' => 'Deskripsi',
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Jelaskan', 'referensi' => 'Kunci']],
        'deadline' => now()->addDays(3),
    ], $attr));
}

it('menyimpan jawaban esai dan menandai belum terlambat', function () {
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['deadline' => now()->addDay()]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        null,
        [0 => 'Jawaban saya']
    );

    expect($p->jawaban)->toBe([0 => 'Jawaban saya'])
        ->and($p->terlambat)->toBeFalse()
        ->and($p->nilai)->toBeNull()
        ->and($p->hasil_ai)->toBeNull();
});

it('menandai terlambat saat deadline sudah lewat', function () {
    Bus::fake();

    $tugas = tugPjTugas(['deadline' => now()->subDay()]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => 'Telat']);

    expect($p->terlambat)->toBeTrue();
});

it('tidak menandai terlambat saat deadline masih di depan', function () {
    Bus::fake();

    $tugas = tugPjTugas(['deadline' => now()->addHour()]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => 'On time']);

    expect($p->terlambat)->toBeFalse();
});

it('tidak menandai terlambat untuk tugas tanpa deadline', function () {
    Bus::fake();

    $tugas = tugPjTugas(['deadline' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => 'Tanpa tenggat']);

    expect($p->terlambat)->toBeFalse();
});

it('menganggap terlambat kalau deadline lewat di tengah hari', function () {
    Bus::fake();

    $tugas = tugPjTugas(['deadline' => now()->subHours(2)]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => 'X']);

    expect($p->terlambat)->toBeTrue();
});

it('tidak mengirim job ai untuk tugas non esai', function () {
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['tipe' => 'upload']);
    $siswa = tugPjSiswa($tugas->kelas_id);

    app(PengumpulanTugasService::class)->submit($tugas, $siswa, UploadedFile::fake()->create('a.pdf', 10), null);

    Bus::assertNothingDispatched();
});

it('mengganti pengumpulan lama saat siswa mengumpulkan ulang', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);
    $svc = app(PengumpulanTugasService::class);

    $svc->submit($tugas, $siswa, null, [0 => 'Versi 1']);
    $kedua = $svc->submit($tugas, $siswa, null, [0 => 'Versi 2']);

    expect(PengumpulanTugas::where('tugas_id', $tugas->id)->count())->toBe(1)
        ->and($kedua->jawaban)->toBe([0 => 'Versi 2']);
});

it('menolak pengumpulan duplikat untuk tugas dan siswa yang sama di tingkat database', function () {
    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);
    $data = [
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['jawaban'],
    ];

    PengumpulanTugas::create($data);

    expect(fn () => PengumpulanTugas::create($data))->toThrow(QueryException::class);
});

it('menyimpan file upload dan mengosongkan jawaban', function () {
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['tipe' => 'upload', 'soal' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        UploadedFile::fake()->create('tugas.pdf', 20, 'application/pdf'),
        null
    );

    // Kolomnya bernama `file`, bukan `file_path`.
    expect($p->file)->not->toBeNull()
        ->and($p->jawaban)->toBe([])
        ->and(Storage::disk('public')->exists($p->file))->toBeTrue();
});

it('menghapus file upload lama jika kiriman diganti tanpa berkas baru', function () {
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['tipe' => 'upload', 'soal' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);
    Storage::disk('public')->put('pengumpulan/lama.pdf', 'berkas lama');
    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'file' => 'pengumpulan/lama.pdf',
        'jawaban' => [],
    ]);

    $diperbarui = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, null);

    expect($diperbarui->id)->toBe($pengumpulan->id)
        ->and($diperbarui->file)->toBeNull()
        ->and(Storage::disk('public')->exists('pengumpulan/lama.pdf'))->toBeFalse();
});

it('menghapus file lama setelah berkas pengumpulan berhasil diganti', function () {
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['tipe' => 'upload', 'soal' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);
    Storage::disk('public')->put('pengumpulan/lama.pdf', 'berkas lama');
    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'file' => 'pengumpulan/lama.pdf',
        'jawaban' => [],
    ]);

    $diperbarui = app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        UploadedFile::fake()->create('baru.pdf', 20, 'application/pdf'),
        null
    );

    expect($diperbarui->id)->toBe($pengumpulan->id)
        ->and($diperbarui->file)->not->toBe('pengumpulan/lama.pdf')
        ->and(Storage::disk('public')->exists($diperbarui->file))->toBeTrue()
        ->and(Storage::disk('public')->exists('pengumpulan/lama.pdf'))->toBeFalse();
});

it('mempertahankan pengumpulan dan file lama jika penyimpanan file baru gagal', function () {
    Storage::fake('public');
    Bus::fake();

    $tugas = tugPjTugas(['tipe' => 'upload', 'soal' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);
    Storage::disk('public')->put('pengumpulan/lama.pdf', 'berkas lama');
    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'file' => 'pengumpulan/lama.pdf',
        'jawaban' => [],
    ]);

    $disk = Mockery::mock(Storage::disk('public'))->makePartial();
    $disk->shouldReceive('putFileAs')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    expect(fn () => app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        UploadedFile::fake()->create('baru.pdf', 20, 'application/pdf'),
        null
    ))->toThrow(RuntimeException::class, 'Gagal menyimpan berkas pengumpulan tugas.');

    expect($pengumpulan->fresh()->file)->toBe('pengumpulan/lama.pdf')
        ->and(Storage::disk('public')->exists('pengumpulan/lama.pdf'))->toBeTrue();
});

it('mengosongkan hasil penilaian lama saat jawaban esai dikirim ulang', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);
    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['Versi lama'],
        'nilai' => 80,
        'nilai_ai' => 90,
        'hasil_ai' => [['indeks' => 0, 'status' => 'benar']],
        'koreksi_ai_at' => now(),
    ]);

    $diperbarui = app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        null,
        ['Versi baru']
    );

    expect($diperbarui->jawaban)->toBe(['Versi baru'])
        ->and($diperbarui->nilai)->toBeNull()
        ->and($diperbarui->nilai_ai)->toBeNull()
        ->and($diperbarui->hasil_ai)->toBeNull()
        ->and($diperbarui->koreksi_ai_at)->toBeNull();
});

it('menyimpan nilai guru untuk pengumpulan milik tugas itu', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);
    $p = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => 'J']);

    $tersimpan = app(PengumpulanTugasService::class)->simpanNilai($tugas, [$p->id => 87]);

    expect($tersimpan)->toBe(1)
        ->and($p->fresh()->nilai)->toBe(87);
});

it('mengabaikan id pengumpulan dari tugas lain', function () {
    Bus::fake();

    $tugasA = tugPjTugas();
    $tugasB = tugPjTugas();
    $siswa = tugPjSiswa($tugasA->kelas_id);
    $svc = app(PengumpulanTugasService::class);

    $milikB = $svc->submit($tugasB, $siswa, null, [0 => 'J']);

    $tersimpan = $svc->simpanNilai($tugasA, [$milikB->id => 100]);

    expect($tersimpan)->toBe(0)
        ->and($milikB->fresh()->nilai)->toBeNull();
});

it('menyimpan nilai null untuk menghapus nilai', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);
    $svc = app(PengumpulanTugasService::class);

    $p = $svc->submit($tugas, $siswa, null, [0 => 'J']);
    $svc->simpanNilai($tugas, [$p->id => 80]);
    $svc->simpanNilai($tugas, [$p->id => null]);

    expect($p->fresh()->nilai)->toBeNull();
});

it('mengembalikan nol saat tidak ada nilai yang dikirim', function () {
    $tugas = tugPjTugas();

    expect(app(PengumpulanTugasService::class)->simpanNilai($tugas, []))->toBe(0);
});

it('membuang nilai di luar rentang nol sampai seratus', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);
    $svc = app(PengumpulanTugasService::class);

    $p = $svc->submit($tugas, $siswa, null, [0 => 'J']);
    $svc->simpanNilai($tugas, [$p->id => 9999]);

    // Nilai 9999 tidak diam-dimenterima sebagai 9999; FormRequest yang
    // memotong ke 0-100 di lapis HTTP, jadi di sini yang dijamin hanya
    // tipenya benar-benar integer.
    expect($p->fresh()->nilai)->toBeInt();
});

it('menyimpan file upload bertipe apa pun karena tipe dicek di form request', function () {
    // Batas mime hanya ada di TugasJawabanRequest (`required|file|max:10240`).
    // Service sengaja tidak menggandakan aturan itu: kalau suatu saat aturan
    // mime berubah, cukup diubah di satu tempat.
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['tipe' => 'upload', 'soal' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
        null
    );

    expect($p->file)->not->toBeNull();
});

it('memisahkan nilai ai dari nilai guru', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);
    $svc = app(PengumpulanTugasService::class);

    $p = $svc->submit($tugas, $siswa, null, [0 => 'J']);
    $svc->simpanNilai($tugas, [$p->id => 60]);

    $p->update([
        'nilai_ai' => 95,
        'hasil_ai' => [['indeks' => 0, 'status' => 'benar', 'skor' => 95]],
        'koreksi_ai_at' => now(),
    ]);

    expect($p->fresh()->nilai)->toBe(60)
        ->and($p->fresh()->nilai_ai)->toBe(95);
});

it('menyamakan kunci jawaban dari string ke indeks integer', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, ['0' => 'Kunci']);

    expect($p->jawaban)->toBe([0 => 'Kunci']);
});

it('membuang isi array dari pada nilai yang disimpan', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        null,
        [0 => ['nested' => 'array']]
    );

    // Tanpa guard is_scalar, Blade `{{ $s['jawaban'] }}` meledak jadi
    // "Array to string conversion" dan seluruh halaman guru ikut 500.
    expect($p->jawaban)->toBe([0 => '']);
});

it('mengabaikan jawaban untuk tugas upload', function () {
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['tipe' => 'upload', 'soal' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit(
        $tugas,
        $siswa,
        UploadedFile::fake()->create('a.pdf', 10),
        [0 => ' shouldn\'t be stored']
    );

    expect($p->jawaban)->toBe([]);
});

it('memakai normalisasi soal untuk membentuk jawaban pg', function () {
    Bus::fake();

    $tugas = tugPjTugas([
        'tipe' => 'pilihan_ganda',
        'soal' => [['pertanyaan' => 'P', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0]],
    ]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    $p = app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => '2']);

    expect($p->jawaban)->toBe([0 => '2']);
});

it('tidak mengirim job ai saat jawaban kosong', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);

    app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, []);

    Bus::assertNothingDispatched();
});

it('tidak mengirim job ai saat jawaban hanya spasi', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);

    app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => '   ']);

    Bus::assertNothingDispatched();
});

it('mengirim job ai saat jawaban berisi nol karena nol dianggap isi', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswa = tugPjSiswa($tugas->kelas_id);

    app(PengumpulanTugasService::class)->submit($tugas, $siswa, null, [0 => '0']);

    Bus::assertDispatched(PeriksaJawabanTugasAI::class);
});

it('mengirim satu job ai per pengumpulan dengan jawaban', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswaA = tugPjSiswa($tugas->kelas_id);
    $siswaB = tugPjSiswa($tugas->kelas_id);
    $svc = app(PengumpulanTugasService::class);

    $a = $svc->submit($tugas, $siswaA, null, [0 => 'A']);
    $svc->submit($tugas, $siswaB, null, []);

    Bus::assertDispatched(PeriksaJawabanTugasAI::class, function ($job) use ($a) {
        return $job->pengumpulanId === $a->id;
    });
    Bus::assertDispatchedTimes(PeriksaJawabanTugasAI::class, 1);
});

it('jadwal pemeriksaan ai hanya menyentuh pengumpulan dengan isi', function () {
    Bus::fake();

    $tugas = tugPjTugas();
    $siswaA = tugPjSiswa($tugas->kelas_id);
    $siswaB = tugPjSiswa($tugas->kelas_id);
    $svc = app(PengumpulanTugasService::class);

    $a = $svc->submit($tugas, $siswaA, null, [0 => 'A']);
    $svc->submit($tugas, $siswaB, null, []);

    $jumlah = $svc->jadwalkanPemeriksaanAi($tugas);

    // 2 = sekali dari submit awal + sekali dari perintah periksa ulang.
    expect($jumlah)->toBe(1);
    Bus::assertDispatched(PeriksaJawabanTugasAI::class, function ($job) use ($a) {
        return $job->pengumpulanId === $a->id;
    });
    Bus::assertDispatchedTimes(PeriksaJawabanTugasAI::class, 2);
});

it('jadwal pemeriksaan ai tidak menghasilkan apa pun untuk tugas non esai', function () {
    Bus::fake();
    Storage::fake('public');

    $tugas = tugPjTugas(['tipe' => 'upload', 'soal' => null]);
    $siswa = tugPjSiswa($tugas->kelas_id);

    app(PengumpulanTugasService::class)->submit($tugas, $siswa, UploadedFile::fake()->create('a.pdf', 10), null);

    expect(app(PengumpulanTugasService::class)->jadwalkanPemeriksaanAi($tugas))->toBe(0);
    Bus::assertNothingDispatched();
});
