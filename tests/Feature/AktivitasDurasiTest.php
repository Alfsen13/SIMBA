<?php

/*
 * Durasi belajar (`rata_durasi_belajar`) tidak pernah punya data karena tidak
 * ada yang menulis `aktivitas_log.metadata.durasi_menit`. Test ini menjaga
 * heartbeat-nya: endpoint durasi mengakumulasi detik ke metadata log, lalu
 * menghitung ulang snapshot fitur pertemuan.
 */

use App\Models\AktivitasLog;
use App\Models\FiturSiswaHistory;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\FiturPertemuanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Prefix helper unik per file (fungsi global di Pest).
function adrGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function adrKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function adrSiswa(Kelas $kelas, string $nama = 'Siswa Durasi'): User
{
    $siswa = User::factory()->verified()->create(['name' => $nama, 'role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $siswa;
}

function adrPertemuan(Kelas $kelas, int $urutan = 1): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => "Pertemuan {$urutan}",
        'urutan' => $urutan,
    ]);
}

function adrLogPertemuan(User $siswa): AktivitasLog
{
    return AktivitasLog::where('siswa_id', $siswa->id)
        ->where('tipe', 'buka_pertemuan')
        ->latest('id')
        ->firstOrFail();
}

it('mengakumulasi durasi ke metadata log dan menghitung ulang rata_durasi_belajar', function () {
    $guru = adrGuru();
    $kelas = adrKelas($guru);
    $pertemuan = adrPertemuan($kelas);
    $siswa = adrSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.pertemuan.show', $pertemuan))->assertOk();

    $log = adrLogPertemuan($siswa);

    $this->actingAs($siswa)
        ->post(route('pelajar.aktivitas.durasi', $log), ['detik' => 90])
        ->assertOk()
        ->assertJson(['ok' => true]);

    // 90 detik = 1,5 menit, boleh pecahan (dulu di-cast int sehingga hilang).
    expect((float) $log->fresh()->metadata['durasi_menit'])->toBe(1.5);

    $this->actingAs($siswa)
        ->post(route('pelajar.aktivitas.durasi', $log), ['detik' => 90])
        ->assertOk();

    expect((float) $log->fresh()->metadata['durasi_menit'])->toBe(3.0);

    $history = FiturSiswaHistory::where('siswa_id', $siswa->id)
        ->where('pertemuan_id', $pertemuan->id)
        ->firstOrFail();

    expect((float) $history->rata_durasi_belajar)->toBe(3.0);
});

it('mempertahankan metadata lain (materi_id) saat menambah durasi', function () {
    $guru = adrGuru();
    $kelas = adrKelas($guru);
    $pertemuan = adrPertemuan($kelas);
    $siswa = adrSiswa($kelas);

    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $this->actingAs($siswa)->get(route('pelajar.materi.show', $materi))->assertOk();

    $log = AktivitasLog::where('siswa_id', $siswa->id)->where('tipe', 'buka_materi')->firstOrFail();

    $this->actingAs($siswa)
        ->post(route('pelajar.aktivitas.durasi', $log), ['detik' => 60])
        ->assertOk();

    $metadata = $log->fresh()->metadata;

    expect($metadata['materi_id'])->toBe($materi->id)
        ->and((float) $metadata['durasi_menit'])->toBe(1.0);
});

it('menolak durasi untuk log milik siswa lain', function () {
    $guru = adrGuru();
    $kelas = adrKelas($guru);
    $pertemuan = adrPertemuan($kelas);
    $siswa = adrSiswa($kelas, 'Pemilik');
    $lain = adrSiswa($kelas, 'Penyusup');

    $this->actingAs($siswa)->get(route('pelajar.pertemuan.show', $pertemuan))->assertOk();
    $log = adrLogPertemuan($siswa);

    $this->actingAs($lain)
        ->post(route('pelajar.aktivitas.durasi', $log), ['detik' => 30])
        ->assertForbidden();
});

it('memvalidasi jumlah detik', function () {
    $guru = adrGuru();
    $kelas = adrKelas($guru);
    $pertemuan = adrPertemuan($kelas);
    $siswa = adrSiswa($kelas);

    $this->actingAs($siswa)->get(route('pelajar.pertemuan.show', $pertemuan))->assertOk();
    $log = adrLogPertemuan($siswa);

    $this->actingAs($siswa)
        ->post(route('pelajar.aktivitas.durasi', $log), ['detik' => 0])
        ->assertSessionHasErrors('detik');

    $this->actingAs($siswa)
        ->post(route('pelajar.aktivitas.durasi', $log), [])
        ->assertSessionHasErrors('detik');
});

it('menampilkan penanda heartbeat durasi di halaman pertemuan', function () {
    $guru = adrGuru();
    $kelas = adrKelas($guru);
    $pertemuan = adrPertemuan($kelas);
    $siswa = adrSiswa($kelas);

    $this->actingAs($siswa)
        ->get(route('pelajar.pertemuan.show', $pertemuan))
        ->assertOk()
        ->assertSee('id="aktivitas-durasi"', false)
        ->assertSee(route('pelajar.aktivitas.durasi', adrLogPertemuan($siswa)), false);
});

it('mengeluarkan frekuensi akses dari deret grafik indikator', function () {
    $grafik = FiturPertemuanService::fiturModel();

    expect($grafik)->not->toContain('frekuensi_akses_pertemuan')
        ->and($grafik)->toHaveCount(6)
        // Urutan tetap mengikuti FEATURES sebagai satu-satunya sumber.
        ->and($grafik[0])->toBe('rata_durasi_belajar');
});
