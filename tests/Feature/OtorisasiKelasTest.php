<?php

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->guruLain = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->kelasLain = Kelas::factory()->create(['guru_id' => $this->guruLain->id]);
    $this->pertemuan = otorisasiKelasBuatPertemuan($this->kelas, 'Pertemuan Sendiri');
    $this->pertemuanLain = otorisasiKelasBuatPertemuan($this->kelasLain, 'Pertemuan Guru Lain');
});

function otorisasiKelasBuatPertemuan(Kelas $kelas, string $nama): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => $nama,
        'urutan' => 1,
    ]);
}

function otorisasiKelasBuatAktivitas(string $jenis, Kelas $kelas, Pertemuan $pertemuan, User $guru): Kuis|Tugas|Materi
{
    return match ($jenis) {
        'kuis' => Kuis::create([
            'judul' => 'Kuis Uji Otorisasi',
            'kelas_id' => $kelas->id,
            'pertemuan_id' => $pertemuan->id,
            'soal' => [['pertanyaan' => 'Soal?', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
        ]),
        'tugas' => Tugas::create([
            'judul' => 'Tugas Uji Otorisasi',
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'pertemuan_id' => $pertemuan->id,
            'tipe' => 'upload',
        ]),
        'materi' => Materi::create([
            'judul' => 'Materi Uji Otorisasi',
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'pertemuan_id' => $pertemuan->id,
        ]),
    };
}

function otorisasiKelasPayload(string $jenis, Kelas $kelas, Pertemuan $pertemuan): array
{
    $payload = [
        'judul' => 'Judul Diperbarui',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
    ];

    return match ($jenis) {
        'kuis' => $payload + [
            'soal' => json_encode([['pertanyaan' => 'Soal?', 'pilihan' => ['A', 'B'], 'jawaban' => 0]]),
            'durasi_menit' => 30,
            'is_aktif' => true,
        ],
        'tugas' => $payload + [
            'tipe' => 'upload',
            'soal' => null,
        ],
        'materi' => $payload + [
            'deskripsi' => 'Deskripsi',
        ],
    };
}

function otorisasiKelasRoute(string $jenis, string $aksi, mixed $model = null): string
{
    $nama = match ($jenis) {
        'kuis' => 'pengajar.kuis.',
        'tugas' => 'pengajar.tugas.',
        'materi' => 'pengajar.materi.',
    }.$aksi;

    return $model === null ? route($nama) : route($nama, $model);
}

it('menolak kelas milik pengajar lain pada form buat dan ubah', function (string $jenis) {
    $aktivitas = otorisasiKelasBuatAktivitas($jenis, $this->kelas, $this->pertemuan, $this->guru);
    $payload = otorisasiKelasPayload($jenis, $this->kelasLain, $this->pertemuanLain);

    $this->actingAs($this->guru)
        ->post(otorisasiKelasRoute($jenis, 'store'), $payload)
        ->assertSessionHasErrors('kelas_id');

    $this->actingAs($this->guru)
        ->put(otorisasiKelasRoute($jenis, 'update', $aktivitas), $payload)
        ->assertSessionHasErrors('kelas_id');

    $this->assertDatabaseHas($aktivitas->getTable(), [
        'id' => $aktivitas->id,
        'kelas_id' => $this->kelas->id,
    ]);
})->with(['kuis', 'tugas', 'materi']);

it('menolak pertemuan yang bukan milik kelas yang dipilih', function (string $jenis) {
    $aktivitas = otorisasiKelasBuatAktivitas($jenis, $this->kelas, $this->pertemuan, $this->guru);
    $payload = otorisasiKelasPayload($jenis, $this->kelas, $this->pertemuanLain);

    $this->actingAs($this->guru)
        ->post(otorisasiKelasRoute($jenis, 'store'), $payload)
        ->assertSessionHasErrors('pertemuan_id');

    $this->actingAs($this->guru)
        ->put(otorisasiKelasRoute($jenis, 'update', $aktivitas), $payload)
        ->assertSessionHasErrors('pertemuan_id');

    $this->assertDatabaseHas($aktivitas->getTable(), [
        'id' => $aktivitas->id,
        'pertemuan_id' => $this->pertemuan->id,
    ]);
})->with(['kuis', 'tugas', 'materi']);

it('menerima kelas dan pertemuan milik pengajar yang sama', function (string $jenis) {
    $aktivitas = otorisasiKelasBuatAktivitas($jenis, $this->kelas, $this->pertemuan, $this->guru);

    $this->actingAs($this->guru)
        ->put(
            otorisasiKelasRoute($jenis, 'update', $aktivitas),
            otorisasiKelasPayload($jenis, $this->kelas, $this->pertemuan)
        )
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas($aktivitas->getTable(), [
        'id' => $aktivitas->id,
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
    ]);
})->with(['kuis', 'tugas', 'materi']);

it('menolak format file materi yang tidak didukung pemrosesan dokumen', function (string $namaFile, string $mime) {
    $payload = otorisasiKelasPayload('materi', $this->kelas, $this->pertemuan);
    $payload['file'] = UploadedFile::fake()->create($namaFile, 10, $mime);

    $this->actingAs($this->guru)
        ->post(otorisasiKelasRoute('materi', 'store'), $payload)
        ->assertSessionHasErrors('file');
})->with([
    'dokumen Word lama' => ['materi.doc', 'application/msword'],
    'presentasi PowerPoint lama' => ['materi.ppt', 'application/vnd.ms-powerpoint'],
    'lembar kerja Excel lama' => ['materi.xls', 'application/vnd.ms-excel'],
]);

it('menerima dan menjadwalkan pemrosesan format materi yang didukung', function () {
    Bus::fake();
    Storage::fake('public');

    $payload = otorisasiKelasPayload('materi', $this->kelas, $this->pertemuan);
    $payload['file'] = UploadedFile::fake()->create('materi.pdf', 10, 'application/pdf');

    $this->actingAs($this->guru)
        ->post(otorisasiKelasRoute('materi', 'store'), $payload)
        ->assertRedirect();

    $materi = Materi::where('judul', 'Judul Diperbarui')->firstOrFail();
    expect(Document::where('materi_id', $materi->id)->exists())->toBeTrue();
    Bus::assertDispatched(ProcessDocument::class);
});
