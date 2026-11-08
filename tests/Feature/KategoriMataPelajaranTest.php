<?php

use App\Models\AplikasiPengajar;
use App\Models\Document;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\KategoriMataPelajaran;
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

function katmapPengelolaOperator(): User
{
    return User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);
}

function katmapKategori(string $nama): KategoriMataPelajaran
{
    return KategoriMataPelajaran::firstOrCreate(['nama' => $nama]);
}

test('kolom kategori ada pada tabel mata_pelajaran', function () {
    expect(Schema::getColumnListing('mata_pelajaran'))->toContain('kategori');
});

test('kategori tidak menyediakan route show tanpa handler', function () {
    $this->actingAs(katmapPengelolaOperator())
        ->get('/pengelola-operator/kategori-mata-pelajaran/Bahasa%20%26%20Komunikasi')
        ->assertMethodNotAllowed();
});

test('seeder mata pelajaran mengisi kategori dan idempoten', function () {
    $this->seed(MataPelajaranSeeder::class);
    $jumlah = MataPelajaran::count();
    $this->seed(MataPelajaranSeeder::class);

    expect($jumlah)->toBeGreaterThan(10)
        ->and(MataPelajaran::count())->toBe($jumlah)
        ->and(KategoriMataPelajaran::count())->toBeGreaterThanOrEqual(5)
        ->and(MataPelajaran::whereNull('kategori')->count())->toBe(0)
        ->and(MataPelajaran::where('kategori', '')->count())->toBe(0)
        ->and(MataPelajaran::distinct()->count('kategori'))->toBeGreaterThanOrEqual(5)
        ->and(MataPelajaran::where('aktif', false)->count())->toBe(0);
});

test('form buat kelas memuat mata pelajaran sesuai kategori operator', function () {
    $this->seed(MataPelajaranSeeder::class);

    $operator = User::factory()->operator()->create(['kategori_mapel' => 'Sains']);

    $this->actingAs($operator)
        ->get(route('operator.kelas.create'))
        ->assertOk()
        ->assertSee('Sains')
        ->assertSee('Matematika')
        ->assertDontSee('Bahasa & Komunikasi')
        ->assertDontSee('Informatika')
        ->assertSee('name="aturan_kelayakan" value="sesuai_jenjang"', false)
        ->assertSee('name="status" value="dibuka"', false);
});

test('form tambah kelas melokalkan petunjuk operator', function () {
    $operator = User::factory()->operator()->create(['kategori_mapel' => 'Matematika']);
    $url = route('operator.kelas.create');

    $this->actingAs($operator)
        ->get($url)
        ->assertOk()
        ->assertSee('Hanya mata pelajaran pada kategori yang menjadi tanggung jawab operator.');

    $this->from($url)
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect($url);

    $this->get($url)
        ->assertOk()
        ->assertSee('Only subjects in the category managed by this operator are available.');
});

test('daftar kelas melokalkan import dan memberi label serta nama aksesibel pada kontrol', function () {
    $operator = User::factory()->operator()->create(['kategori_mapel' => 'Matematika']);
    $guru = User::factory()->pengajar()->verified()->create();
    $kelasDiampu = Kelas::factory()->create([
        'nama_kelas' => 'Kelas Diampu',
        'kategori' => 'Matematika',
        'guru_id' => $guru->id,
    ]);
    $kelasKosong = Kelas::factory()->create([
        'nama_kelas' => 'Kelas Kosong',
        'kategori' => 'Matematika',
        'guru_id' => null,
    ]);
    $url = route('operator.kelas.index');

    $this->actingAs($operator)
        ->from($url)
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect($url);

    $response = $this->withSession(['import_errors' => ['Invalid class row']])
        ->get($url)
        ->assertOk()
        ->assertSee('Class template (CSV/Excel)')
        ->assertSee('Import classes')
        ->assertSee('There was an error importing classes')
        ->assertSee('for="kelas-import-file"', false)
        ->assertSee('id="kelas-import-file"', false)
        ->assertSee('Edit class '.$kelasDiampu->nama_kelas, false)
        ->assertSee('Delete class '.$kelasDiampu->nama_kelas, false)
        ->assertSee('Revoke teaching rights for '.$kelasDiampu->nama_kelas, false)
        ->assertSee('Delete class '.$kelasKosong->nama_kelas, false);

    expect(substr_count($response->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(11);
});

test('operator menyimpan kelas lengkap dengan mata pelajaran dan aturan', function () {
    $this->seed(MataPelajaranSeeder::class);
    $mapel = MataPelajaran::where('kode', 'matematika')->firstOrFail();
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 2, 'aktif' => true]);

    $this->actingAs(User::factory()->operator()->create(['kategori_mapel' => $mapel->kategori]))
        ->post(route('operator.kelas.store'), [
            'nama_kelas' => 'Kelas Matematika',
            'kategori' => 'Sains',
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
        'kategori' => 'Sains',
    ]);
});

test('operator tidak dapat membuat kelas dengan kategori yang berbeda dari mata pelajarannya', function () {
    $mapel = MataPelajaran::create([
        'kode' => 'matematika-form',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
    ]);
    $jalur = JalurPendidikan::create(['kode' => 'formal-form', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp-form', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8-form', 'nama' => 'Kelas 8', 'urutan' => 1, 'aktif' => true]);

    $this->actingAs(User::factory()->operator()->create(['kategori_mapel' => 'Matematika']))
        ->from(route('operator.kelas.create'))
        ->post(route('operator.kelas.store'), [
            'nama_kelas' => 'Kelas Kategori Tidak Cocok',
            'kategori' => 'Bahasa',
            'mata_pelajaran_id' => $mapel->id,
            'jenjang_id' => $jenjang->id,
            'tingkat_id' => $tingkat->id,
        ])
        ->assertRedirect(route('operator.kelas.create'))
        ->assertSessionHasErrors('kategori');

    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Kategori Tidak Cocok']);
});

test('operator tidak dapat mengubah kategori kelas tanpa menyesuaikan mata pelajarannya', function () {
    $mapel = MataPelajaran::create([
        'kode' => 'matematika-edit',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
    ]);
    $kelas = Kelas::factory()->create([
        'kategori' => 'Matematika',
        'mata_pelajaran_id' => $mapel->id,
    ]);

    $this->actingAs(User::factory()->operator()->create(['kategori_mapel' => 'Matematika']))
        ->from(route('operator.kelas.edit', $kelas))
        ->put(route('operator.kelas.update', $kelas), [
            'nama_kelas' => $kelas->nama_kelas,
            'kategori' => 'Bahasa',
            'mata_pelajaran_id' => $mapel->id,
            'jenjang_id' => $kelas->jenjang_id,
            'tingkat_id' => $kelas->tingkat_id,
            'status' => $kelas->status,
            'aturan_kelayakan' => $kelas->aturan_kelayakan,
        ])
        ->assertRedirect(route('operator.kelas.edit', $kelas))
        ->assertSessionHasErrors('kategori');

    expect($kelas->fresh()->kategori)->toBe('Matematika')
        ->and($kelas->fresh()->mata_pelajaran_id)->toBe($mapel->id);
});

test('status dan aturan kelayakan yang kosong memakai default', function () {
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 1, 'aktif' => true]);
    $operator = User::factory()->operator()->create(['kategori_mapel' => 'Matematika']);

    $this->actingAs($operator)
        ->post(route('operator.kelas.store'), [
            'nama_kelas' => 'Kelas Polos',
            'jenjang_id' => $jenjang->id,
            'tingkat_id' => $tingkat->id,
        ])
        ->assertRedirect(route('operator.kelas.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas Polos',
        'status' => 'dibuka',
        'aturan_kelayakan' => 'sesuai_profil',
        'mata_pelajaran_id' => null,
        'kategori' => 'Matematika',
    ]);

    $kelas = Kelas::where('nama_kelas', 'Kelas Polos')->firstOrFail();
    $this->get(route('operator.kelas.index'))->assertSee('Kelas Polos');
    $this->get(route('operator.kelas.edit', $kelas))->assertOk();
    $this->put(route('operator.kelas.update', $kelas), [
        'nama_kelas' => 'Kelas Polos Diubah',
        'kategori' => '',
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
    ])->assertRedirect(route('operator.kelas.index'))->assertSessionHasNoErrors();
    expect($kelas->fresh()->kategori)->toBe('Matematika');
});

test('operator tidak dapat menyimpan kelas di luar kategori atau tanpa kategori operator', function () {
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 1, 'aktif' => true]);
    $operator = User::factory()->operator()->create(['kategori_mapel' => 'Matematika']);
    $kelas = Kelas::factory()->create(['kategori' => 'Matematika', 'mata_pelajaran_id' => null]);
    $data = ['nama_kelas' => 'Kelas Lain', 'kategori' => 'Bahasa', 'jenjang_id' => $jenjang->id, 'tingkat_id' => $tingkat->id];

    $this->actingAs($operator)->post(route('operator.kelas.store'), $data)->assertSessionHasErrors('kategori');
    $this->put(route('operator.kelas.update', $kelas), $data)->assertSessionHasErrors('kategori');
    expect($kelas->fresh()->kategori)->toBe('Matematika');
    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Lain']);

    $this->actingAs(katmapOperator())
        ->post(route('operator.kelas.store'), $data)
        ->assertSessionHasErrors(['kategori' => __('operator.kelas_operator_tanpa_kategori')]);
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
        'kategori' => $mapel->kategori,
        'aturan_kelayakan' => 'khusus',
        'status' => 'dibuka',
    ]);

    $response = $this->actingAs(User::factory()->operator()->create(['kategori_mapel' => $mapel->kategori]))
        ->get(route('operator.kelas.edit', $kelas))
        ->assertOk()
        ->assertSee('Informatika')
        ->assertSee('value="'.$mapel->id.'"', false)
        ->assertSee(__('common.aturan_khusus'))
        ->assertSee('name="jenjang_id"', false)
        ->assertSee('name="tingkat_id"', false)
        ->assertSee($kelas->nama_kelas);

    expect($response->getContent())
        ->toMatch('/<select\s+name="jenjang_id"[^>]*\brequired\b/s')
        ->toMatch('/<select\s+name="tingkat_id"[^>]*\brequired\b/s');
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

test('kategori yang dipakai kelas atau operator tidak dapat dihapus', function () {
    $kategori = 'Ilmu Alam';
    katmapKategori($kategori);
    $mapel = MataPelajaran::create([
        'kode' => 'fisika-ilmu-alam',
        'nama' => 'Fisika',
        'kategori' => $kategori,
    ]);
    $kelas = Kelas::factory()->create([
        'kategori' => $kategori,
        'mata_pelajaran_id' => $mapel->id,
    ]);
    $operator = User::factory()->operator()->create(['kategori_mapel' => $kategori]);
    $pengelola = katmapPengelolaOperator();

    $this->actingAs($pengelola)
        ->from(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->delete(route('pengelola-operator.kategori-mata-pelajaran.destroy', $kategori))
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertSessionHas('error');

    expect($mapel->fresh()->kategori)->toBe($kategori)
        ->and($kelas->fresh()->mata_pelajaran_id)->toBe($mapel->id)
        ->and($operator->fresh()->kategori_mapel)->toBe($kategori);
});

test('kategori yang dipakai aplikasi pengajar tidak dapat dihapus', function () {
    $kategori = 'Bahasa';
    katmapKategori($kategori);
    $mapel = MataPelajaran::create([
        'kode' => 'bahasa-indonesia',
        'nama' => 'Bahasa Indonesia',
        'kategori' => $kategori,
    ]);
    $pengajar = User::factory()->pengajar()->create();
    AplikasiPengajar::create([
        'user_id' => $pengajar->id,
        'kategori_mapel' => $kategori,
    ]);
    $pengelola = katmapPengelolaOperator();

    $this->actingAs($pengelola)
        ->delete(route('pengelola-operator.kategori-mata-pelajaran.destroy', $kategori))
        ->assertRedirect();

    expect($mapel->fresh()->kategori)->toBe($kategori);
});

test('menghapus kategori yang tidak digunakan mempertahankan mata pelajaran', function () {
    $kategori = 'Sains';
    katmapKategori($kategori);
    $mapel = MataPelajaran::create([
        'kode' => 'kimia-sains',
        'nama' => 'Kimia',
        'kategori' => $kategori,
    ]);

    $this->actingAs(katmapPengelolaOperator())
        ->delete(route('pengelola-operator.kategori-mata-pelajaran.destroy', $kategori))
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertSessionHas('success');

    expect($mapel->fresh())->not->toBeNull()
        ->and($mapel->fresh()->kategori)->toBeNull();
});

test('mengubah kategori memperbarui semua referensi tanpa mengganti nama mata pelajaran', function () {
    $kategoriLama = 'Matematika';
    $kategoriBaru = 'Sains';
    katmapKategori($kategoriLama);
    $mapel = MataPelajaran::create([
        'kode' => 'fisika-kategori',
        'nama' => 'Fisika',
        'kategori' => $kategoriLama,
    ]);
    $kelas = Kelas::factory()->create(['kategori' => $kategoriLama]);
    $operator = User::factory()->operator()->create(['kategori_mapel' => $kategoriLama]);
    $pengajar = User::factory()->pengajar()->create();
    $aplikasi = AplikasiPengajar::create([
        'user_id' => $pengajar->id,
        'kategori_mapel' => $kategoriLama,
    ]);
    $document = Document::create([
        'name' => 'Materi',
        'original_name' => 'materi.txt',
        'path' => 'documents/materi.txt',
        'user_id' => $pengajar->id,
        'kategori' => $kategoriLama,
    ]);
    $chunk = $document->chunks()->create([
        'content' => 'Materi',
        'chunk_index' => 0,
        'kategori' => $kategoriLama,
    ]);
    $lain = Document::create([
        'name' => 'Lain',
        'original_name' => 'lain.txt',
        'path' => 'documents/lain.txt',
        'user_id' => $pengajar->id,
        'kategori' => 'Bahasa',
    ]);

    $this->actingAs(katmapPengelolaOperator())
        ->put(route('pengelola-operator.kategori-mata-pelajaran.update', $kategoriLama), [
            'kategori' => $kategoriBaru,
        ])
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertSessionHas('success');

    expect($mapel->fresh()->kategori)->toBe($kategoriBaru)
        ->and($mapel->fresh()->nama)->toBe('Fisika')
        ->and($kelas->fresh()->kategori)->toBe($kategoriBaru)
        ->and($operator->fresh()->kategori_mapel)->toBe($kategoriBaru)
        ->and($aplikasi->fresh()->kategori_mapel)->toBe($kategoriBaru)
        ->and($document->fresh()->kategori)->toBe($kategoriBaru)
        ->and($chunk->fresh()->kategori)->toBe($kategoriBaru)
        ->and($lain->fresh()->kategori)->toBe('Bahasa');
});

test('mengubah kategori ke nama yang sudah digunakan ditolak', function () {
    katmapKategori('Kategori Asal');
    katmapKategori('Kategori Tujuan');
    $mapelAsal = MataPelajaran::create([
        'kode' => 'mapel-asal',
        'nama' => 'Mapel Asal',
        'kategori' => 'Kategori Asal',
    ]);
    $mapelTujuan = MataPelajaran::create([
        'kode' => 'mapel-tujuan',
        'nama' => 'Mapel Tujuan',
        'kategori' => 'Kategori Tujuan',
    ]);
    $kelas = Kelas::factory()->create(['kategori' => 'Kategori Asal']);

    $this->actingAs(katmapPengelolaOperator())
        ->from(route('pengelola-operator.kategori-mata-pelajaran.edit', 'Kategori Asal'))
        ->put(route('pengelola-operator.kategori-mata-pelajaran.update', 'Kategori Asal'), [
            'kategori' => 'Kategori Tujuan',
        ])
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.edit', 'Kategori Asal'))
        ->assertSessionHasErrors('kategori');

    expect($mapelAsal->fresh()->kategori)->toBe('Kategori Asal')
        ->and($mapelTujuan->fresh()->kategori)->toBe('Kategori Tujuan')
        ->and($kelas->fresh()->kategori)->toBe('Kategori Asal');
});

test('membuat kategori baru tidak membuat mata pelajaran semu', function () {
    $sebelum = MataPelajaran::count();
    $pengelola = katmapPengelolaOperator();

    $this->actingAs($pengelola)
        ->post(route('pengelola-operator.kategori-mata-pelajaran.store'), [
            'kategori' => 'Sains Baru',
        ])
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertSessionHas('success');

    expect(KategoriMataPelajaran::where('nama', 'Sains Baru')->exists())->toBeTrue()
        ->and(MataPelajaran::count())->toBe($sebelum)
        ->and(MataPelajaran::where('kategori', 'Sains Baru')->exists())->toBeFalse();

    $this->get(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertOk()
        ->assertSee('Sains Baru')
        ->assertSee('0');

    $this->get(route('pengelola-operator.operator.create'))
        ->assertOk()
        ->assertSee('Sains Baru');

    $this->post(route('pengelola-operator.operator.store'), [
        'name' => 'Operator Sains',
        'email' => 'operator.sains@example.test',
        'password' => 'password123',
        'kategori_mapel' => 'Sains Baru',
    ])->assertRedirect(route('pengelola-operator.operator.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'operator.sains@example.test',
        'kategori_mapel' => 'Sains Baru',
    ]);
});

test('backfill membuat katalog dari referensi kategori lama', function () {
    MataPelajaran::create(['kode' => 'legacy-mapel', 'nama' => 'Mapel Lama', 'kategori' => 'Kategori Mapel Lama']);
    Kelas::factory()->create(['kategori' => 'Kategori Kelas Lama']);
    User::factory()->operator()->create(['kategori_mapel' => 'Kategori Operator Lama']);
    $pengajar = User::factory()->pengajar()->create();
    AplikasiPengajar::create([
        'user_id' => $pengajar->id,
        'kategori_mapel' => 'Kategori Aplikasi Lama',
    ]);

    $migration = require database_path('migrations/2026_11_06_162832_backfill_kategori_mata_pelajaran_from_mata_pelajaran.php');
    $migration->up();

    expect(KategoriMataPelajaran::query()->pluck('nama')->all())
        ->toContain('Kategori Mapel Lama')
        ->toContain('Kategori Kelas Lama')
        ->toContain('Kategori Operator Lama')
        ->toContain('Kategori Aplikasi Lama');
});

test('halaman kategori mengikuti locale dan memberi ukuran minimum pada kontrol', function () {
    $kategori = katmapKategori('Matematika');
    $pengelola = katmapPengelolaOperator();
    $url = route('pengelola-operator.kategori-mata-pelajaran.index');

    $this->actingAs($pengelola)
        ->get($url)
        ->assertOk()
        ->assertSee('Kategori Mata Pelajaran')
        ->assertSee('Jumlah Mapel')
        ->assertSee("confirm('Hapus kategori ini?')", false);

    $this->from($url)
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect($url);

    $response = $this->get($url)
        ->assertOk()
        ->assertSee('Subject Categories')
        ->assertSee('Subject Count')
        ->assertSee("confirm('Delete this category?')", false)
        ->assertSee(__('pengelola_operator.kategori_mata_pelajaran.edit'))
        ->assertSee(__('pengelola_operator.kategori_mata_pelajaran.delete'));

    expect(substr_count($response->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(3)
        ->and(substr_count($response->getContent(), 'min-w-11'))->toBeGreaterThanOrEqual(3);

    $this->get(route('pengelola-operator.kategori-mata-pelajaran.create'))
        ->assertOk()
        ->assertSee('Add Subject Category')
        ->assertSee('Category Name')
        ->assertSee('Example: Mathematics')
        ->assertSee('Back')
        ->assertSee('Save')
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    $this->get(route('pengelola-operator.kategori-mata-pelajaran.edit', $kategori->nama))
        ->assertOk()
        ->assertSee('Edit Subject Category')
        ->assertSee('value="Matematika"', false)
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    $this->delete(route('pengelola-operator.kategori-mata-pelajaran.destroy', $kategori->nama))
        ->assertRedirect($url);

    $emptyResponse = $this->get($url)
        ->assertOk()
        ->assertSee('There are no subject categories yet.')
        ->assertSee('Add the First Category');

    expect(substr_count($emptyResponse->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(2)
        ->and(substr_count($emptyResponse->getContent(), 'min-w-11'))->toBeGreaterThanOrEqual(2);
});

test('pesan kategori dan validasi nama duplikat mengikuti locale Inggris', function () {
    $kategoriDipakai = katmapKategori('Kategori Terpakai');
    $kategoriBebas = katmapKategori('Kategori Bebas');
    Kelas::factory()->create(['kategori' => $kategoriDipakai->nama]);

    $this->actingAs(katmapPengelolaOperator())
        ->from(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.index'));

    $this->post(route('pengelola-operator.kategori-mata-pelajaran.store'), [
        'kategori' => 'Kategori Baru',
    ])
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertSessionHas('success', __('pengelola_operator.kategori_mata_pelajaran.created'));

    $this->from(route('pengelola-operator.kategori-mata-pelajaran.create'))
        ->post(route('pengelola-operator.kategori-mata-pelajaran.store'), [
            'kategori' => $kategoriDipakai->nama,
        ])
        ->assertSessionHasErrors(['kategori'], __('pengelola_operator.kategori_mata_pelajaran.duplicate'));

    $this->put(route('pengelola-operator.kategori-mata-pelajaran.update', $kategoriBebas->nama), [
        'kategori' => 'Kategori Diperbarui',
    ])
        ->assertRedirect(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertSessionHas('success', __('pengelola_operator.kategori_mata_pelajaran.updated'));

    $this->from(route('pengelola-operator.kategori-mata-pelajaran.edit', 'Kategori Diperbarui'))
        ->put(route('pengelola-operator.kategori-mata-pelajaran.update', 'Kategori Diperbarui'), [
            'kategori' => $kategoriDipakai->nama,
        ])
        ->assertSessionHasErrors(['kategori'], __('pengelola_operator.kategori_mata_pelajaran.duplicate'));

    $this->from(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->delete(route('pengelola-operator.kategori-mata-pelajaran.destroy', $kategoriDipakai->nama))
        ->assertSessionHas('error', __('pengelola_operator.kategori_mata_pelajaran.in_use'));

    $this->delete(route('pengelola-operator.kategori-mata-pelajaran.destroy', 'Kategori Diperbarui'))
        ->assertSessionHas('success', __('pengelola_operator.kategori_mata_pelajaran.deleted'));
});
