<?php

/*
 * Remedial per-pertemuan.
 *
 * Aturan bisnis:
 * - Skor pertemuan dihitung dari komponen yang SUDAH dinilai saja. Tugas yang
 *   belum dinilai / kuis yang belum dikerjakan TIDAK dihitung sebagai 0.
 * - Bila pertemuan punya tugas dan kuis, bobotnya 80% tugas + 20% kuis.
 * - Skor di bawah ambang (default 75) -> remedial khusus pertemuan itu.
 * - Idempotensi terpisah: (siswa, pertemuan) yang belum selesai / masih dalam
 *   cooldown tidak dibuat ulang, dan TIDAK diblokir remedial risiko global.
 */

use App\Jobs\GenerateRemedialPertemuanAI;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\MateriRemedial;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\Tugas;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\RemedialService;
use App\Services\RemedialTugasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.remedial.cooldown_hari' => 7,
        'services.remedial.ambang_pertemuan' => 75,
        'services.remedial.bobot_tugas' => 0.8,
        'services.remedial.bobot_kuis' => 0.2,
    ]);

    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $this->siswa->kelasDiikuti()->attach($this->kelas->id);

    $this->pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan Fotosintesis',
        'urutan' => 1,
    ]);
});

function remPertTugas($kelas, $guru, Pertemuan $pertemuan, string $judul = 'Tugas'): Tugas
{
    return Tugas::create([
        'judul' => $judul,
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
        'tipe' => 'pilihan_ganda',
        'soal' => [['pertanyaan' => 'Soal uji.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
    ]);
}

function remPertNil(Tugas $tugas, $siswa, ?int $nilai): PengumpulanTugas
{
    return PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['0'],
        'nilai' => $nilai,
    ]);
}

function remPertKuis($kelas, Pertemuan $pertemuan, $siswa, int $skor): Kuis
{
    $kuis = Kuis::create([
        'judul' => 'Kuis '.$pertemuan->id.'-'.$skor,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'is_aktif' => true,
        'soal' => [['pertanyaan' => 'Soal uji.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
    ]);

    KuisHasil::create([
        'kuis_id' => $kuis->id,
        'siswa_id' => $siswa->id,
        'skor' => $skor,
        'jawaban_siswa' => [0],
    ]);

    return $kuis;
}

function remPertRiwayat(int $siswaId, ?int $pertemuanId, string $status, ?int $umurHari = null): MateriRemedial
{
    return MateriRemedial::create([
        'siswa_id' => $siswaId,
        'pertemuan_id' => $pertemuanId,
        'sumber' => $pertemuanId === null ? 'prediksi' : 'pertemuan',
        'topik_lemah' => 'Fotosintesis',
        'ringkasan' => 'Ringkasan.',
        'latihan' => '',
        'provider_llm' => 'ollama',
        'status' => $status,
        'generated_at' => $umurHari === null ? now() : now()->subDays($umurHari),
    ]);
}

// ---------------------------------------------------------------------------
// Skema & perhitungan skor
// ---------------------------------------------------------------------------

test('kolom baru materi_remedial benar-benar ada di skema', function () {
    $kolom = Schema::getColumnListing('materi_remedial');

    expect($kolom)->toContain('pertemuan_id')
        ->and($kolom)->toContain('sumber')
        ->and($kolom)->toContain('skor_pertemuan');
});

test('skor pertemuan memakai bobot 80 tugas dan 20 kuis saat keduanya ada', function () {
    $t1 = remPertTugas($this->kelas, $this->guru, $this->pertemuan, 'T1');
    $t2 = remPertTugas($this->kelas, $this->guru, $this->pertemuan, 'T2');
    remPertNil($t1, $this->siswa, 70);
    remPertNil($t2, $this->siswa, 90);
    remPertKuis($this->kelas, $this->pertemuan, $this->siswa, 50);

    // tugas = (70+90)/2 = 80 ; kuis = 50 -> 0.8*80 + 0.2*50 = 74
    expect(app(RemedialService::class)->skorPertemuan($this->siswa->id, $this->pertemuan->id))
        ->toBe(74.0);
});

test('skor pertemuan memakai tugas saja bila pertemuan tidak punya kuis', function () {
    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, 60);

    expect(app(RemedialService::class)->skorPertemuan($this->siswa->id, $this->pertemuan->id))
        ->toBe(60.0);
});

test('komponen yang belum dinilai tidak dihitung sebagai nol', function () {
    $dinilai = remPertTugas($this->kelas, $this->guru, $this->pertemuan, 'Dinilai');
    $belum = remPertTugas($this->kelas, $this->guru, $this->pertemuan, 'Belum');
    remPertNil($dinilai, $this->siswa, 40);
    remPertNil($belum, $this->siswa, null);

    // Bukan 20 (40 dan 0 dibagi dua) melainkan 40.
    expect(app(RemedialService::class)->skorPertemuan($this->siswa->id, $this->pertemuan->id))
        ->toBe(40.0);
});

test('skor pertemuan null bila belum ada komponen yang dinilai', function () {
    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, null);

    expect(app(RemedialService::class)->skorPertemuan($this->siswa->id, $this->pertemuan->id))
        ->toBeNull();
});

// ---------------------------------------------------------------------------
// Penjadwalan
// ---------------------------------------------------------------------------

test('prosesSiswaBerisiko menjadwalkan remedial pertemuan walau tanpa prediksi risiko', function () {
    Queue::fake();

    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, 50);

    $hasil = app(RemedialService::class)->prosesSiswaBerisiko();

    expect($hasil['dijadwalkan'])->toBe(1)
        ->and($hasil['dilewati'])->toBe(0);

    Queue::assertPushed(GenerateRemedialPertemuanAI::class, function (GenerateRemedialPertemuanAI $job) {
        return $job->siswaId === $this->siswa->id
            && $job->pertemuanId === $this->pertemuan->id
            && abs($job->skorPertemuan - 50.0) < 0.001;
    });
});

test('skor tepat di ambang tidak memicu remedial', function () {
    Queue::fake();

    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, 75);

    expect(app(RemedialService::class)->prosesSiswaBerisiko()['dijadwalkan'])->toBe(0);
    Queue::assertNotPushed(GenerateRemedialPertemuanAI::class);
});

test('remedial pertemuan yang belum selesai menahan penjadwalan ulang', function () {
    Queue::fake();

    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, 50);
    remPertRiwayat($this->siswa->id, $this->pertemuan->id, 'dibaca', 30);

    $hasil = app(RemedialService::class)->prosesSiswaBerisiko();

    expect($hasil['dijadwalkan'])->toBe(0)
        ->and($hasil['dilewati'])->toBe(1);

    Queue::assertNothingPushed();
});

test('cooldown per-pertemuan menahan penjadwalan walau remedial lama sudah selesai', function () {
    Queue::fake();

    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, 50);
    remPertRiwayat($this->siswa->id, $this->pertemuan->id, 'selesai', 2);

    expect(app(RemedialService::class)->prosesSiswaBerisiko()['dijadwalkan'])->toBe(0);
    Queue::assertNothingPushed();
});

test('remedial pertemuan yang terpisah tidak saling memblokir', function () {
    Queue::fake();

    $pertemuanLain = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan Lain',
        'urutan' => 2,
    ]);

    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, 50);
    remPertRiwayat($this->siswa->id, $pertemuanLain->id, 'dibaca', 1);

    expect(app(RemedialService::class)->prosesSiswaBerisiko()['dijadwalkan'])->toBe(1);
    Queue::assertPushed(GenerateRemedialPertemuanAI::class);
});

test('remedial risiko global tidak memblokir remedial pertemuan', function () {
    Queue::fake();

    PrediksiAi::create([
        'siswa_id' => $this->siswa->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 0.9,
    ]);
    remPertRiwayat($this->siswa->id, null, 'dibaca', 1);

    $t = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    remPertNil($t, $this->siswa, 50);

    expect(app(RemedialService::class)->prosesSiswaBerisiko()['dijadwalkan'])->toBe(1);
});

// ---------------------------------------------------------------------------
// Generate materi
// ---------------------------------------------------------------------------

test('generateForPertemuan menyimpan baris dengan pertemuan, sumber, dan skor', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('send')
            ->once()
            ->andReturn('{"ringkasan":"Fotosintesis mengubah cahaya menjadi glukosa.","latihan":"1. Jelaskan klorofil."}');
    });

    $hasil = app(RemedialService::class)->generateForPertemuan(
        $this->siswa->id,
        $this->pertemuan->id,
        42.5
    );

    expect($hasil)->toBeInstanceOf(MateriRemedial::class)
        ->and($hasil->pertemuan_id)->toBe($this->pertemuan->id)
        ->and($hasil->sumber)->toBe('pertemuan')
        ->and($hasil->skor_pertemuan)->toBe(42.5)
        ->and($hasil->prediksi_ai_id)->toBeNull()
        ->and($hasil->topik_lemah)->toContain('Pertemuan Fotosintesis')
        ->and($hasil->status)->toBe('belum_dibaca');
});

test('job pertemuan dijalankan dua kali hanya menghasilkan satu remedial', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('send')
            ->once()
            ->andReturn('{"ringkasan":"Ringkasan materi.","latihan":"Latihan."}');
    });

    $this->mock(RemedialTugasService::class, function ($mock) {
        $mock->shouldReceive('generateTugas')->once()->andReturn(true);
    });

    $job = new GenerateRemedialPertemuanAI($this->siswa->id, $this->pertemuan->id, 40.0);
    $job->handle(app(RemedialService::class));

    expect(MateriRemedial::where('siswa_id', $this->siswa->id)->where('pertemuan_id', $this->pertemuan->id)->count())->toBe(1);

    $job->handle(app(RemedialService::class));

    expect(MateriRemedial::where('siswa_id', $this->siswa->id)->where('pertemuan_id', $this->pertemuan->id)->count())->toBe(1);
});

test('dispatch ganda untuk pertemuan yang sama hanya menumpuk satu job unik', function () {
    Queue::fake();

    GenerateRemedialPertemuanAI::dispatch($this->siswa->id, $this->pertemuan->id, 40.0);
    GenerateRemedialPertemuanAI::dispatch($this->siswa->id, $this->pertemuan->id, 35.0);

    Queue::assertPushed(GenerateRemedialPertemuanAI::class, 1);
});

test('pertemuan berbeda tetap menumpuk job masing-masing', function () {
    Queue::fake();

    $lain = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan lain',
        'urutan' => 2,
    ]);

    GenerateRemedialPertemuanAI::dispatch($this->siswa->id, $this->pertemuan->id, 40.0);
    GenerateRemedialPertemuanAI::dispatch($this->siswa->id, $lain->id, 40.0);

    Queue::assertPushed(GenerateRemedialPertemuanAI::class, 2);
});

// ---------------------------------------------------------------------------
// Pemicu real-time
// ---------------------------------------------------------------------------

test('submit kuis dengan skor rendah menjadwalkan remedial pertemuan', function () {
    Queue::fake();

    $kuis = Kuis::create([
        'judul' => 'Kuis 1',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
        'is_aktif' => true,
        'soal' => [
            ['pertanyaan' => 'Q1', 'pilihan' => ['A', 'B'], 'jawaban' => 0],
            ['pertanyaan' => 'Q2', 'pilihan' => ['A', 'B'], 'jawaban' => 0],
        ],
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kuis.submit', $kuis), [
            'jawaban' => ['0' => '1', '1' => '1'],
        ])
        ->assertRedirect(route('pelajar.kuis.hasil', $kuis));

    Queue::assertPushed(GenerateRemedialPertemuanAI::class, function (GenerateRemedialPertemuanAI $job) {
        return $job->siswaId === $this->siswa->id && $job->pertemuanId === $this->pertemuan->id;
    });
});

test('guru menyimpan nilai rendah menjadwalkan remedial pertemuan', function () {
    Queue::fake();

    $tugas = remPertTugas($this->kelas, $this->guru, $this->pertemuan);
    $pengumpulan = remPertNil($tugas, $this->siswa, null);

    $this->actingAs($this->guru)
        ->post(route('pengajar.tugas.nilai', $tugas), [
            'nilai' => [$pengumpulan->id => 40],
        ])
        ->assertRedirect();

    Queue::assertPushed(GenerateRemedialPertemuanAI::class, function (GenerateRemedialPertemuanAI $job) {
        return $job->siswaId === $this->siswa->id
            && $job->pertemuanId === $this->pertemuan->id
            && abs($job->skorPertemuan - 40.0) < 0.001;
    });
});

test('submit kuis dengan skor aman tidak menjadwalkan remedial', function () {
    Queue::fake();

    $kuis = Kuis::create([
        'judul' => 'Kuis 2',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
        'is_aktif' => true,
        'soal' => [
            ['pertanyaan' => 'Q1', 'pilihan' => ['A', 'B'], 'jawaban' => 0],
            ['pertanyaan' => 'Q2', 'pilihan' => ['A', 'B'], 'jawaban' => 0],
        ],
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kuis.submit', $kuis), [
            'jawaban' => ['0' => '0', '1' => '0'],
        ])
        ->assertRedirect(route('pelajar.kuis.hasil', $kuis));

    Queue::assertNotPushed(GenerateRemedialPertemuanAI::class);
});
