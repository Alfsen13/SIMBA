<?php

/*
 * Fase 1 "Early Warning System berbasis pertemuan": fondasi data.
 *
 * Yang dijaga di sini adalah lapisan data yang belum ada sebelumnya:
 * 1. Jadwal pertemuan (tanggal_mulai/selesai) — dasar window prediksi.
 * 2. Atribusi aktivitas per pertemuan — sebelumnya `aktivitas_log` tidak
 *    pernah ditulis aplikasi sama sekali.
 * 3. Modul kehadiran (x4 persen_kehadiran) — tabel sebelumnya tidak ada.
 *
 * Test ini sengaja mengecek skema lewat Schema::hasColumn karena SQLite
 * menoleransi query ke kolom yang tidak ada (bug hanya muncul di MySQL).
 */

use App\Models\Kehadiran;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function jkGuru(): User
{
    return User::factory()->pengajar()->verified()->create();
}

function jkKelas(User $guru): Kelas
{
    return Kelas::factory()->create(['guru_id' => $guru->id]);
}

function jkSiswa(Kelas $kelas, string $nama = 'Siswa'): User
{
    $siswa = User::factory()->verified()->create(['name' => $nama, 'role' => 'pelajar']);
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $siswa;
}

function jkPertemuan(Kelas $kelas, array $attrs = []): Pertemuan
{
    return Pertemuan::create(array_merge([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan Uji',
        'urutan' => 1,
        'deskripsi' => null,
    ], $attrs));
}

// ---------------------------------------------------------------------------
// Jadwal pertemuan
// ---------------------------------------------------------------------------

test('guru dapat menyimpan jadwal pertemuan', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);

    $this->actingAs($guru)->post(route('pengajar.kelas.pertemuan.store', $kelas), [
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-07',
    ])->assertRedirect(route('pengajar.kelas.show', $kelas));

    // Dibaca lewat cast, bukan raw DB: `date` di SQLite tersimpan dengan format
    // berbeda dari MySQL (jebakan yang sama seperti fitur_siswa_history).
    $pertemuan = Pertemuan::where('kelas_id', $kelas->id)->firstOrFail();
    expect($pertemuan->tanggal_mulai->toDateString())->toBe('2026-10-01')
        ->and($pertemuan->tanggal_selesai->toDateString())->toBe('2026-10-07');
});

test('tanggal selesai tidak boleh sebelum tanggal mulai', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);

    $this->actingAs($guru)->from(route('pengajar.kelas.pertemuan.create', $kelas))
        ->post(route('pengajar.kelas.pertemuan.store', $kelas), [
            'nama_pertemuan' => 'Pertemuan 1',
            'urutan' => 1,
            'tanggal_mulai' => '2026-10-07',
            'tanggal_selesai' => '2026-10-01',
        ])->assertSessionHasErrors('tanggal_selesai');
});

test('jadwal pertemuan opsional', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);

    $this->actingAs($guru)->post(route('pengajar.kelas.pertemuan.store', $kelas), [
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ])->assertRedirect(route('pengajar.kelas.show', $kelas));

    $pertemuan = Pertemuan::where('kelas_id', $kelas->id)->firstOrFail();
    expect($pertemuan->tanggal_mulai)->toBeNull()
        ->and($pertemuan->tanggal_selesai)->toBeNull();
});

// ---------------------------------------------------------------------------
// Kehadiran
// ---------------------------------------------------------------------------

test('guru menyimpan kehadiran siswa kelasnya', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);
    $pertemuan = jkPertemuan($kelas);
    $ana = jkSiswa($kelas, 'Ana');
    $budi = jkSiswa($kelas, 'Budi');

    $this->actingAs($guru)->put(route('pengajar.kehadiran.update', $pertemuan), [
        'status' => [$ana->id => 'hadir', $budi->id => 'alpha'],
        'catatan' => [$budi->id => 'Tanpa keterangan'],
    ])->assertRedirect(route('pengajar.pertemuan.show', $pertemuan));

    $this->assertDatabaseHas('kehadiran', [
        'siswa_id' => $ana->id,
        'pertemuan_id' => $pertemuan->id,
        'status' => 'hadir',
        'dicatat_oleh' => $guru->id,
    ]);
    $this->assertDatabaseHas('kehadiran', [
        'siswa_id' => $budi->id,
        'pertemuan_id' => $pertemuan->id,
        'status' => 'alpha',
        'catatan' => 'Tanpa keterangan',
    ]);
});

test('menyimpan kehadiran dua kali meng-update, bukan menggandakan baris', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);
    $pertemuan = jkPertemuan($kelas);
    $ana = jkSiswa($kelas, 'Ana');

    $this->actingAs($guru)->put(route('pengajar.kehadiran.update', $pertemuan), [
        'status' => [$ana->id => 'hadir'],
    ]);
    $this->actingAs($guru)->put(route('pengajar.kehadiran.update', $pertemuan), [
        'status' => [$ana->id => 'izin'],
    ]);

    $baris = Kehadiran::where('siswa_id', $ana->id)->where('pertemuan_id', $pertemuan->id)->get();
    expect($baris)->toHaveCount(1)
        ->and($baris->first()->status)->toBe('izin');
});

test('kehadiran menolak siswa yang bukan anggota kelas', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);
    $pertemuan = jkPertemuan($kelas);
    $anggota = jkSiswa($kelas, 'Ana');
    $orangLuar = User::factory()->verified()->create(['role' => 'pelajar']);

    $this->actingAs($guru)->put(route('pengajar.kehadiran.update', $pertemuan), [
        'status' => [$anggota->id => 'hadir', $orangLuar->id => 'hadir'],
    ]);

    $this->assertDatabaseHas('kehadiran', [
        'siswa_id' => $anggota->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    $this->assertDatabaseMissing('kehadiran', [
        'siswa_id' => $orangLuar->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
});

test('guru lain tidak boleh mengakses kehadiran kelas orang', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);
    $pertemuan = jkPertemuan($kelas);
    $anggota = jkSiswa($kelas, 'Ana');
    $guruLain = jkGuru();

    $this->actingAs($guruLain)
        ->get(route('pengajar.kehadiran.edit', $pertemuan))
        ->assertForbidden();

    $this->actingAs($guruLain)
        ->put(route('pengajar.kehadiran.update', $pertemuan), [
            'status' => [$anggota->id => 'hadir'],
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('kehadiran', 0);
});

// ---------------------------------------------------------------------------
// Atribusi aktivitas
// ---------------------------------------------------------------------------

test('membuka pertemuan mencatat aktivitas dengan atribusi pertemuan', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);
    $pertemuan = jkPertemuan($kelas);
    $siswa = jkSiswa($kelas, 'Ana');

    $this->actingAs($siswa)
        ->get(route('pelajar.pertemuan.show', $pertemuan))
        ->assertOk();

    $this->assertDatabaseHas('aktivitas_log', [
        'siswa_id' => $siswa->id,
        'tipe' => 'buka_pertemuan',
        'pertemuan_id' => $pertemuan->id,
        'kelas_id' => $kelas->id,
    ]);
});

test('membuka materi mencatat aktivitas dengan pertemuan materi', function () {
    $guru = jkGuru();
    $kelas = jkKelas($guru);
    $pertemuan = jkPertemuan($kelas);
    $siswa = jkSiswa($kelas, 'Ana');
    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $this->actingAs($siswa)
        ->get(route('pelajar.materi.show', $materi))
        ->assertOk();

    $this->assertDatabaseHas('aktivitas_log', [
        'siswa_id' => $siswa->id,
        'tipe' => 'buka_materi',
        'pertemuan_id' => $pertemuan->id,
        'kelas_id' => $kelas->id,
    ]);
});

// ---------------------------------------------------------------------------
// Kontrak skema (penjaga bug SQLite-menutupi-MySQL)
// ---------------------------------------------------------------------------

test('kontrak skema: kolom fase 1 ada di ketiga tabel', function () {
    expect(Schema::hasTable('kehadiran'))->toBeTrue();

    foreach (['siswa_id', 'pertemuan_id', 'status', 'catatan', 'dicatat_oleh'] as $kolom) {
        expect(Schema::hasColumn('kehadiran', $kolom))->toBeTrue();
    }

    foreach (['pertemuan_id', 'kelas_id'] as $kolom) {
        expect(Schema::hasColumn('aktivitas_log', $kolom))->toBeTrue();
    }

    foreach (['tanggal_mulai', 'tanggal_selesai'] as $kolom) {
        expect(Schema::hasColumn('pertemuan', $kolom))->toBeTrue();
    }
});
