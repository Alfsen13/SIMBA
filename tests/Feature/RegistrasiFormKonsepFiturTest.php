<?php

/**
 * UI + alur pendaftaran Konsep Fitur: form registrasi dinamis per jalur
 * (pelajar) dan data aplikasi + unggah berkas (calon pengajar).
 *
 * Prefix helper: `regUi` (wajib unik — fungsi di file Pest bersifat global).
 */

use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\MataPelajaran;
use App\Models\TingkatPendidikan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('form registrasi menampilkan field konsep fitur', function () {
    $html = $this->get(route('register'))->assertOk()->getContent();

    expect($html)->toContain('enctype="multipart/form-data"')
        ->toContain('id="reg-master"')
        ->toContain('name="jalur_id"')
        ->toContain('name="institusi"')
        ->toContain('name="minat"')
        ->toContain('name="pendidikan_tertinggi"')
        ->toContain('name="pengalaman_mengajar"')
        ->toContain('name="kategori_mapel"')
        ->toContain('name="mata_pelajaran[]"')
        ->toContain('name="jenjang_dan_tingkat"')
        ->toContain('name="cv"')
        ->toContain('name="ijazah"')
        ->toContain('accept=".pdf,.doc,.docx"');
});

test('form registrasi memuat opsi master pendidikan aktif', function () {
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 1, 'aktif' => true]);
    JalurPendidikan::create(['kode' => 'nonaktif', 'nama' => 'Nonaktif', 'urutan' => 9, 'aktif' => false]);

    $html = $this->get(route('register'))->assertOk()->getContent();

    // Opsi jalur dirender statis; payload JSON berisi jenjang & tingkat untuk cascading Alpine.
    expect($html)->toContain('value="' . $jalur->id . '">Formal</option>')
        ->not->toContain('Nonaktif')
        ->toContain('"nama":"SMP"')
        ->toContain('"nama":"Kelas 8"');
});

test('select kategori mapel hanya menawarkan kategori master aktif', function () {
    MataPelajaran::create(['kode' => 'fisika', 'nama' => 'Fisika', 'kategori' => 'Matematika & Ilmu Alam', 'aktif' => true]);
    MataPelajaran::create(['kode' => 'kimia', 'nama' => 'Kimia', 'kategori' => 'Matematika & Ilmu Alam', 'aktif' => true]);
    MataPelajaran::create(['kode' => 'seni', 'nama' => 'Seni Budaya', 'kategori' => null, 'aktif' => true]);
    MataPelajaran::create(['kode' => 'lama', 'nama' => 'Mapel Lama', 'kategori' => 'Kategori Nonaktif', 'aktif' => false]);

    $html = $this->get(route('register'))->assertOk()->getContent();

    // Kategori sama muncul sekali; kategori NULL & mapel nonaktif tidak ditawarkan.
    // Catatan: `&` di-escape Blade menjadi `&amp;` di HTML.
    expect($html)->toContain('value="Matematika &amp; Ilmu Alam"')
        ->not->toContain('value="Kategori Nonaktif"')
        ->not->toContain('value="null"');
});

test('kategori mapel di luar master ditolak', function () {
    MataPelajaran::create(['kode' => 'ppkn', 'nama' => 'PPKn', 'kategori' => 'Ilmu Sosial & Humaniora', 'aktif' => true]);

    $this->post(route('register'), [
        'name' => 'Guru Asing',
        'email' => 'guru.asing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '19870009',
        'kategori_mapel' => 'Kategori Karangan',
    ])->assertSessionHasErrors('kategori_mapel');

    $this->assertDatabaseCount('users', 0);
});

test('pendaftaran calon pengajar menyimpan aplikasi beserta berkasnya', function () {
    Storage::fake('public');

    MataPelajaran::create([
        'kode' => 'matematika', 'nama' => 'Matematika',
        'kategori' => 'Matematika & Ilmu Alam', 'aktif' => true,
    ]);

    $response = $this->post(route('register'), [
        'name' => 'Calon Guru',
        'email' => 'calon.guru@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '19870001',
        'pendidikan_tertinggi' => 'S1 Pendidikan Matematika',
        'pengalaman_mengajar' => '3 tahun mengajar SMP',
        'kategori_mapel' => 'Matematika & Ilmu Alam',
        'mata_pelajaran' => ['Matematika', 'Fisika'],
        'jenjang_dan_tingkat' => 'SMP Kelas 7-9',
        'cv' => UploadedFile::fake()->create('cv.pdf', 128, 'application/pdf'),
        'ijazah' => UploadedFile::fake()->create('ijazah.pdf', 64, 'application/pdf'),
    ]);

    $response->assertRedirect(route('pending'));

    $user = \App\Models\User::where('email', 'calon.guru@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('pengajar')
        ->and($user->status)->toBe('pending')
        ->and($user->nip)->toBe('19870001')
        ->and($user->pendidikan_tertinggi)->toBeNull(); // field aplikasi tidak bocor ke users

    $aplikasi = \App\Models\AplikasiPengajar::where('user_id', $user->id)->first();
    expect($aplikasi)->not->toBeNull()
        ->and($aplikasi->status_aplikasi)->toBe('pending')
        ->and($aplikasi->pendidikan_tertinggi)->toBe('S1 Pendidikan Matematika')
        ->and($aplikasi->pengalaman_mengajar)->toBe('3 tahun mengajar SMP')
        ->and($aplikasi->kategori_mapel)->toBe('Matematika & Ilmu Alam')
        ->and($aplikasi->jenjang_dan_tingkat)->toBe('SMP Kelas 7-9')
        ->and($aplikasi->mata_pelajaran)->toBe(['Matematika', 'Fisika']);

    Storage::disk('public')->assertExists($aplikasi->cv_path);
    Storage::disk('public')->assertExists($aplikasi->ijazah_path);
});

test('pendaftaran pengajar tanpa data aplikasi tidak membuat baris aplikasi', function () {
    $this->post(route('register'), [
        'name' => 'Guru Polos',
        'email' => 'guru.polos@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '19870002',
    ])->assertRedirect(route('pending'));

    $this->assertDatabaseCount('aplikasi_pengajar', 0);
    $this->assertDatabaseHas('users', ['email' => 'guru.polos@example.com', 'role' => 'pengajar', 'status' => 'pending']);
});

test('pendaftaran pelajar menyimpan nisn dan profil tanpa baris aplikasi', function () {
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 1, 'aktif' => true]);

    $this->post(route('register'), [
        'name' => 'Siswa Baru',
        'email' => 'siswa.formal@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'nisn' => '0091234567',
        'jalur_id' => $jalur->id,
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
        'institusi' => 'SMPN 1',
        'minat' => 'Matematika',
    ])->assertRedirect(route('pending'));

    $this->assertDatabaseHas('users', ['nisn' => '0091234567', 'role' => 'pelajar']);
    $this->assertDatabaseHas('profil_pendidikan', ['institusi' => 'SMPN 1', 'minat' => 'Matematika']);
    $this->assertDatabaseCount('aplikasi_pengajar', 0);
});

test('unggahan cv bukan pdf/doc ditolak', function () {
    $this->post(route('register'), [
        'name' => 'Guru File Aneh',
        'email' => 'file.aneh@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '19870003',
        'cv' => UploadedFile::fake()->create('foto.png', 10, 'image/png'),
    ])->assertSessionHasErrors('cv');

    $this->assertDatabaseCount('users', 0);
});
