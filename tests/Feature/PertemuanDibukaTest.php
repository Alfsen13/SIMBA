<?php

/*
 * Buka/kunci pertemuan oleh guru.
 *
 * Pertemuan yang belum dibuka:
 *  - disembunyikan dari daftar pelajar (kelas, tugas, kuis, AI Tutor);
 *  - seluruh kontennya (pertemuan/materi/tugas/kuis/AI Tutor) diblokir;
 *  - tidak dihitung dalam fitur/prediksi per-siswa.
 *
 * Prefix helper: `pd` (wajib unik - fungsi di file Pest bersifat global).
 */

use App\Models\FiturSiswaHistory;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\FiturPertemuanService;
use App\Services\MLService;
use App\Services\PrediksiMendatangService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function pdGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function pdKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function pdSiswa(Kelas $kelas): User
{
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $siswa;
}

function pdPertemuan(Kelas $kelas, int $urutan, bool $dibuka = true): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => "Pertemuan {$urutan}",
        'urutan' => $urutan,
        'dibuka' => $dibuka,
    ]);
}

// ---------------------------------------------------------------------------
// Skema & default
// ---------------------------------------------------------------------------

test('kolom dibuka ada di tabel pertemuan', function () {
    expect(Schema::hasColumn('pertemuan', 'dibuka'))->toBeTrue();
});

test('pertemuan baru default terbuka lewat model (kompatibilitas kode lama)', function () {
    $kelas = pdKelas(pdGuru());

    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Tanpa dibuka',
        'urutan' => 1,
    ]);

    expect($pertemuan->fresh()->dibuka)->toBeTrue();
});

test('dibuka bisa disetel eksplisit terkunci', function () {
    $kelas = pdKelas(pdGuru());

    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Terkunci',
        'urutan' => 1,
        'dibuka' => false,
    ]);

    expect($pertemuan->fresh()->dibuka)->toBeFalse();
});

// ---------------------------------------------------------------------------
// Guru: buat & buka/kunci
// ---------------------------------------------------------------------------

test('pertemuan yang dibuat tanpa mencentang dibuka akan terkunci', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);

    $this->actingAs($guru)->post(route('pengajar.kelas.pertemuan.store', $kelas), [
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ])->assertRedirect(route('pengajar.kelas.show', $kelas));

    expect(Pertemuan::where('kelas_id', $kelas->id)->firstOrFail()->dibuka)->toBeFalse();
});

test('pertemuan yang dibuat dengan centang dibuka akan terbuka', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);

    $this->actingAs($guru)->post(route('pengajar.kelas.pertemuan.store', $kelas), [
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
        'dibuka' => '1',
    ])->assertRedirect(route('pengajar.kelas.show', $kelas));

    expect(Pertemuan::where('kelas_id', $kelas->id)->firstOrFail()->dibuka)->toBeTrue();
});

test('guru dapat membuka dan mengunci pertemuan', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);

    $this->actingAs($guru)
        ->post(route('pengajar.pertemuan.buka', $pertemuan))
        ->assertRedirect()
        ->assertSessionHas('success');
    expect($pertemuan->fresh()->dibuka)->toBeTrue();

    $this->actingAs($guru)
        ->post(route('pengajar.pertemuan.kunci', $pertemuan))
        ->assertRedirect()
        ->assertSessionHas('success');
    expect($pertemuan->fresh()->dibuka)->toBeFalse();
});

test('guru lain tidak boleh membuka atau mengunci pertemuan', function () {
    $kelas = pdKelas(pdGuru());
    $pertemuan = pdPertemuan($kelas, 1, false);
    $guruLain = pdGuru();

    $this->actingAs($guruLain)
        ->post(route('pengajar.pertemuan.buka', $pertemuan))
        ->assertForbidden();
    $this->actingAs($guruLain)
        ->post(route('pengajar.pertemuan.kunci', $pertemuan))
        ->assertForbidden();

    expect($pertemuan->fresh()->dibuka)->toBeFalse();
});

test('update tanpa field dibuka mempertahankan status', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, true);

    $this->actingAs($guru)->put(route('pengajar.pertemuan.update', $pertemuan), [
        'nama_pertemuan' => 'Diubah',
        'urutan' => 1,
    ])->assertRedirect();

    expect($pertemuan->fresh()->dibuka)->toBeTrue();
});

test('update dengan dibuka=0 mengunci pertemuan', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, true);

    $this->actingAs($guru)->put(route('pengajar.pertemuan.update', $pertemuan), [
        'nama_pertemuan' => 'Diubah',
        'urutan' => 1,
        'dibuka' => '0',
    ])->assertRedirect();

    expect($pertemuan->fresh()->dibuka)->toBeFalse();
});

// ---------------------------------------------------------------------------
// Akses pelajar
// ---------------------------------------------------------------------------

test('pertemuan terkunci disembunyikan dari daftar kelas pelajar', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    pdPertemuan($kelas, 1, true);
    pdPertemuan($kelas, 2, false);
    $siswa = pdSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.kelas.show', $kelas))
        ->assertOk()
        ->assertSee('Pertemuan 1')
        ->assertDontSee('Pertemuan 2');
});

test('pelajar tidak bisa membuka pertemuan terkunci', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);
    $siswa = pdSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.pertemuan.show', $pertemuan))
        ->assertForbidden();

    $this->assertDatabaseMissing('aktivitas_log', ['siswa_id' => $siswa->id, 'tipe' => 'buka_pertemuan']);
});

test('pelajar bisa membuka pertemuan yang sudah dibuka', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, true);
    $siswa = pdSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.pertemuan.show', $pertemuan))->assertOk();
});

test('tautan detail pertemuan memiliki target sentuh dan hover tema gelap yang terbaca', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, true);
    Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    Tugas::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    Kuis::create([
        'judul' => 'Kuis Pertemuan',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'soal' => [],
        'durasi_menit' => 30,
        'is_aktif' => true,
    ]);
    $siswa = pdSiswa($kelas);

    $response = $this->actingAs($siswa)->get(route('pelajar.pertemuan.show', $pertemuan));
    $response->assertOk()
        ->assertSee('min-h-11 min-w-11', false)
        ->assertSee('flex min-h-11 flex-col justify-center py-2 px-3 -mx-3 rounded-lg hover:bg-purple-50 dark:hover:bg-slate-800', false)
        ->assertSee('flex min-h-11 flex-col justify-center py-2 px-3 -mx-3 rounded-lg hover:bg-emerald-50 dark:hover:bg-slate-800', false)
        ->assertSee('flex min-h-11 flex-col justify-center py-2 px-3 -mx-3 rounded-lg hover:bg-amber-50 dark:hover:bg-slate-800', false);
});

test('materi dari pertemuan terkunci diblokir', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);
    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    $siswa = pdSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.materi.show', $materi))->assertForbidden();
});

test('tugas dari pertemuan terkunci diblokir dan tidak tampil di daftar', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);
    $tugas = Tugas::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Tugas Rahasia',
    ]);
    $siswa = pdSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.tugas.show', $tugas))->assertForbidden();
    $this->actingAs($siswa)->get(route('pelajar.tugas.index'))
        ->assertOk()
        ->assertDontSee('Tugas Rahasia');
});

test('kuis dari pertemuan terkunci diblokir dan tidak tampil di daftar', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);
    $kuis = Kuis::create([
        'judul' => 'Kuis Rahasia',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'soal' => [],
        'durasi_menit' => 30,
        'is_aktif' => true,
    ]);
    $siswa = pdSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.kuis.show', $kuis))->assertForbidden();
    $this->actingAs($siswa)->get(route('pelajar.kuis.index'))
        ->assertOk()
        ->assertDontSee('Kuis Rahasia');
});

test('ai tutor pertemuan terkunci diblokir dan tidak tampil di daftar', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);
    Materi::factory()->create([
        'judul' => 'Materi Rahasia',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    $siswa = pdSiswa($kelas);

    $this->actingAs($siswa)
        ->get(route('pelajar.ai-tutor.chat', [$kelas, $pertemuan]))
        ->assertForbidden();

    $this->actingAs($siswa)
        ->get(route('pelajar.ai-tutor.pertemuan', $kelas))
        ->assertOk()
        ->assertDontSee('Materi Rahasia');
});

// ---------------------------------------------------------------------------
// Fitur & prediksi
// ---------------------------------------------------------------------------

test('simpan fitur tidak membuat baris untuk pertemuan terkunci', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);
    $siswa = pdSiswa($kelas);

    $hasil = app(FiturPertemuanService::class)->simpan($siswa->id, $pertemuan);

    expect($hasil)->toBeNull();
    $this->assertDatabaseMissing('fitur_siswa_history', [
        'siswa_id' => $siswa->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
});

test('backfill melewati pertemuan terkunci', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    pdPertemuan($kelas, 1, true);
    $terkunci = pdPertemuan($kelas, 2, false);
    $siswa = pdSiswa($kelas);

    $disimpan = app(FiturPertemuanService::class)->backfill($kelas->id);

    // Hanya pertemuan ke-1 (terbuka) untuk 1 siswa.
    expect($disimpan)->toBe(1);
    $this->assertDatabaseMissing('fitur_siswa_history', [
        'siswa_id' => $siswa->id,
        'pertemuan_id' => $terkunci->id,
    ]);
});

test('pertemuanSampai melewati pertemuan terkunci', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $p1 = pdPertemuan($kelas, 1, true);
    $p2 = pdPertemuan($kelas, 2, false);
    $p3 = pdPertemuan($kelas, 3, true);

    $ids = app(FiturPertemuanService::class)->pertemuanSampai($p3)->pluck('id')->all();

    expect($ids)->toBe([$p1->id, $p3->id])
        ->and($ids)->not->toContain($p2->id);
});

test('kuis pertemuan terkunci tidak dihitung di parameter akademik', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $pertemuan = pdPertemuan($kelas, 1, false);
    $siswa = pdSiswa($kelas);

    $kuis = Kuis::create([
        'judul' => 'Kuis Terkunci',
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'soal' => [],
        'durasi_menit' => 30,
        'is_aktif' => false,
    ]);
    KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $siswa->id, 'skor' => 100, 'jawaban_siswa' => []]);

    $fitur = app(MLService::class)->computeAcademicFeatures($siswa->id);

    expect($fitur['rata_nilai'])->toBeNull()
        ->and($fitur['nilai_keseluruhan'])->toBeNull();
});

test('dataset regresi tidak memakai baris pertemuan terkunci', function () {
    $guru = pdGuru();
    $kelas = pdKelas($guru);
    $p1 = pdPertemuan($kelas, 1, true);
    $p2 = pdPertemuan($kelas, 2, false);
    $siswa = pdSiswa($kelas);

    foreach ([[$p1, 60.0], [$p2, 80.0]] as [$p, $nilai]) {
        FiturSiswaHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $p->id,
            'tanggal' => now()->startOfDay(),
            'rata_nilai' => $nilai,
        ]);
    }

    [, $targets] = app(PrediksiMendatangService::class)->buildDataset();
    expect($targets)->toBeEmpty();

    // Setelah dibuka, pasangan muncul lagi.
    $p2->update(['dibuka' => true]);

    [, $targets] = app(PrediksiMendatangService::class)->buildDataset();
    expect($targets)->toBe([80.0]);
});
