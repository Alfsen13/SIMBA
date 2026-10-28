<?php

use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Database\Seeders\MataPelajaranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/** Helper dengan prefix topikal (helper Pest bersifat global per suite). */
function katmapOperator(): User
{
    return User::factory()->operator()->create();
}

test('kolom kategori ada pada tabel mata_pelajaran', function () {
    expect(Schema::getColumnListing('mata_pelajaran'))->toContain('kategori');
});

test('seeder mata pelajaran mengisi kategori dan idempoten', function () {
    $this->seed(MataPelajaranSeeder::class);
    $jumlah = MataPelajaran::count();
    $this->seed(MataPelajaranSeeder::class);

    expect($jumlah)->toBeGreaterThan(10)
        ->and(MataPelajaran::count())->toBe($jumlah)
        ->and(MataPelajaran::whereNull('kategori')->count())->toBe(0)
        ->and(MataPelajaran::where('kategori', '')->count())->toBe(0)
        ->and(MataPelajaran::distinct()->count('kategori'))->toBeGreaterThanOrEqual(5)
        ->and(MataPelajaran::where('aktif', false)->count())->toBe(0);
});

test('form buat kelas memuat mata pelajaran dikelompokkan per kategori', function () {
    $this->seed(MataPelajaranSeeder::class);

    $this->actingAs(katmapOperator())
        ->get(route('operator.kelas.create'))
        ->assertOk()
        ->assertSee('Matematika & Ilmu Alam')
        ->assertSee('Bahasa & Komunikasi')
        ->assertSee('Informatika')
        ->assertSee(__('common.aturan_sesuai_profil'))
        ->assertSee(__('operator.kelas_status_dibuka'));
});

test('operator menyimpan kelas lengkap dengan mata pelajaran dan aturan', function () {
    $this->seed(MataPelajaranSeeder::class);
    $mapel = MataPelajaran::where('kode', 'matematika')->firstOrFail();
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 2, 'aktif' => true]);

    $this->actingAs(katmapOperator())
        ->post(route('operator.kelas.store'), [
            'nama_kelas' => 'Kelas Matematika',
            'kategori' => 'Matematika & Ilmu Alam',
            'mata_pelajaran_id' => $mapel->id,
            'jalur_id' => $jalur->id,
            'jenjang_id' => $jenjang->id,
            'tingkat_id' => $tingkat->id,
            'kapasitas' => 30,
            'status' => 'ditutup',
            'aturan_kelayakan' => 'khusus',
            'periode' => '2026/2027 Ganjil',
            'deskripsi' => 'Persiapan ujian.',
        ])
        ->assertRedirect(route('operator.kelas.index'));

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas Matematika',
        'mata_pelajaran_id' => $mapel->id,
        'jalur_id' => $jalur->id,
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
        'kapasitas' => 30,
        'status' => 'ditutup',
        'aturan_kelayakan' => 'khusus',
        'periode' => '2026/2027 Ganjil',
        'kategori' => 'Matematika & Ilmu Alam',
    ]);
});

test('status dan aturan kelayakan yang kosong memakai default', function () {
    $this->actingAs(katmapOperator())
        ->post(route('operator.kelas.store'), ['nama_kelas' => 'Kelas Polos'])
        ->assertRedirect();

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas Polos',
        'status' => 'dibuka',
        'aturan_kelayakan' => 'sesuai_profil',
        'mata_pelajaran_id' => null,
    ]);
});

test('nilai status dan aturan kelayakan yang tidak sah ditolak', function () {
    $this->actingAs(katmapOperator())
        ->from(route('operator.kelas.create'))
        ->post(route('operator.kelas.store'), [
            'nama_kelas' => 'Kelas Rusak',
            'status' => 'ngawur',
            'aturan_kelayakan' => 'bebas',
        ])
        ->assertSessionHasErrors(['status', 'aturan_kelayakan']);

    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Rusak']);
});

test('form ubah kelas memuat nilai kelas saat ini beserta pilihan aturan', function () {
    $this->seed(MataPelajaranSeeder::class);
    $mapel = MataPelajaran::where('kode', 'informatika')->firstOrFail();
    $kelas = Kelas::factory()->create([
        'mata_pelajaran_id' => $mapel->id,
        'aturan_kelayakan' => 'khusus',
        'status' => 'dibuka',
    ]);

    $this->actingAs(katmapOperator())
        ->get(route('operator.kelas.edit', $kelas))
        ->assertOk()
        ->assertSee('Informatika')
        ->assertSee('value="' . $mapel->id . '"', false)
        ->assertSee(__('common.aturan_khusus'))
        ->assertSee($kelas->nama_kelas);
});

test('pengajar dapat mengubah aturan kelayakan kelasnya', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id, 'aturan_kelayakan' => 'khusus']);

    $this->actingAs($guru)
        ->put(route('pengajar.kelas.update', $kelas), [
            'nama_kelas' => $kelas->nama_kelas,
            'aturan_kelayakan' => 'terbuka',
        ])
        ->assertRedirect(route('pengajar.kelas.index'));

    expect($kelas->fresh()->aturan_kelayakan)->toBe('terbuka');

    // Opsi aturan dikosongkan: pertahankan nilai lama (kolom NOT NULL di MySQL).
    $this->actingAs($guru)
        ->put(route('pengajar.kelas.update', $kelas), [
            'nama_kelas' => 'Kelas Namabaru',
            'aturan_kelayakan' => '',
        ])
        ->assertRedirect(route('pengajar.kelas.index'));

    expect($kelas->fresh()->aturan_kelayakan)->toBe('terbuka')
        ->and($kelas->fresh()->nama_kelas)->toBe('Kelas Namabaru');

    $this->actingAs($guru)
        ->put(route('pengajar.kelas.update', $kelas), [
            'nama_kelas' => 'Kelas Namabaru',
            'aturan_kelayakan' => 'ngawur',
        ])
        ->assertSessionHasErrors('aturan_kelayakan');

    expect($kelas->fresh()->aturan_kelayakan)->toBe('terbuka');
});
