<?php

/**
 * UI + alur pendaftaran Konsep Fitur: form registrasi dinamis per jalur
 * (pelajar) dan data aplikasi + unggah berkas (calon pengajar).
 *
 * Prefix helper: `regUi` (wajib unik — fungsi di file Pest bersifat global).
 */

use App\Models\AplikasiPengajar;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\KategoriMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('form registrasi menampilkan field konsep fitur', function () {
    $html = $this->get(route('register'))->assertOk()->getContent();

    expect($html)->toContain('enctype="multipart/form-data"')
        ->toContain('id="reg-master"')
        ->toContain('name="jalur_id"')
        ->toContain('x-bind:required="role === \'pelajar\' && jalurKode === \'formal\'"')
        ->toContain('name="institusi"')
        ->toContain('name="minat"')
        ->toContain('name="pendidikan_tertinggi"')
        ->toContain('name="pengalaman_mengajar"')
        ->toContain('name="kategori_mapel"')
        ->toContain(__('auth.kategori_mapel_preview_empty'))
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
    expect($html)->toContain('value="'.$jalur->id.'">Formal</option>')
        ->not->toContain('Nonaktif')
        ->toContain('"nama":"SMP"')
        ->toContain('"nama":"Kelas 8"');
});

test('select kategori mapel hanya menawarkan kategori master aktif', function () {
    KategoriMataPelajaran::create(['nama' => 'Matematika & Ilmu Alam']);
    KategoriMataPelajaran::create(['nama' => 'Kategori Nonaktif']);
    MataPelajaran::create(['kode' => 'fisika', 'nama' => 'Fisika', 'kategori' => 'Matematika & Ilmu Alam', 'aktif' => true]);
    MataPelajaran::create(['kode' => 'kimia', 'nama' => 'Kimia', 'kategori' => 'Matematika & Ilmu Alam', 'aktif' => true]);
    MataPelajaran::create(['kode' => 'seni', 'nama' => 'Seni Budaya', 'kategori' => null, 'aktif' => true]);
    MataPelajaran::create(['kode' => 'lama', 'nama' => 'Mapel Lama', 'kategori' => 'Kategori Nonaktif', 'aktif' => false]);

    $html = $this->get(route('register'))->assertOk()->getContent();

    // Kategori sama muncul sekali; kategori NULL & mapel nonaktif tidak ditawarkan.
    // Catatan: `&` di-escape Blade menjadi `&amp;` di HTML.
    expect($html)->toContain('value="Matematika &amp; Ilmu Alam"')
        ->not->toContain('value="Kategori Nonaktif"')
        ->not->toContain('Mapel Lama')
        ->not->toContain('value="null"');
});

test('kategori mapel di luar master ditolak', function () {
    KategoriMataPelajaran::create(['nama' => 'Ilmu Sosial & Humaniora']);
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

test('klaim mapel katalog harus aktif dan sesuai kategori yang dipilih', function () {
    KategoriMataPelajaran::create(['nama' => 'Matematika & Ilmu Alam']);
    KategoriMataPelajaran::create(['nama' => 'Ilmu Sosial']);
    MataPelajaran::create(['kode' => 'matematika', 'nama' => 'Matematika', 'kategori' => 'Matematika & Ilmu Alam', 'aktif' => true]);
    MataPelajaran::create(['kode' => 'sejarah', 'nama' => 'Sejarah', 'kategori' => 'Ilmu Sosial', 'aktif' => true]);
    MataPelajaran::create(['kode' => 'lama', 'nama' => 'Mapel Lama', 'kategori' => 'Matematika & Ilmu Alam', 'aktif' => false]);

    $this->post(route('register'), [
        'name' => 'Guru Klaim Mapel',
        'email' => 'guru.klaim@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '19870010',
        'kategori_mapel' => 'Matematika & Ilmu Alam',
        'mata_pelajaran' => ['Sejarah', 'Mapel Lama'],
    ])->assertSessionHasErrors(['mata_pelajaran.0', 'mata_pelajaran.1']);

    $this->assertDatabaseCount('users', 0);
});

test('pendaftaran calon pengajar menyimpan aplikasi beserta berkasnya', function () {
    Storage::fake('public');

    KategoriMataPelajaran::create(['nama' => 'Matematika & Ilmu Alam']);
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

    $user = User::where('email', 'calon.guru@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('pengajar')
        ->and($user->status)->toBe('pending')
        ->and($user->nip)->toBe('19870001')
        ->and($user->pendidikan_tertinggi)->toBeNull(); // field aplikasi tidak bocor ke users

    $aplikasi = AplikasiPengajar::where('user_id', $user->id)->first();
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

test('pendaftaran membatalkan data dan membersihkan berkas jika penyimpanan aplikasi gagal', function () {
    Storage::fake('public');
    KategoriMataPelajaran::create(['nama' => 'Matematika']);
    MataPelajaran::create(['kode' => 'matematika', 'nama' => 'Matematika', 'kategori' => 'Matematika', 'aktif' => true]);

    DB::statement("CREATE TRIGGER fail_aplikasi_pengajar BEFORE INSERT ON aplikasi_pengajar BEGIN SELECT RAISE(ABORT, 'Simulated application storage failure.'); END");
    $this->withoutExceptionHandling();

    try {
        expect(fn () => $this->post(route('register'), [
            'name' => 'Guru Gagal',
            'email' => 'guru.gagal@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pengajar',
            'nip' => '19870011',
            'kategori_mapel' => 'Matematika',
            'pendidikan_tertinggi' => 'S1',
            'cv' => UploadedFile::fake()->create('cv.pdf', 128, 'application/pdf'),
        ]))->toThrow(QueryException::class);
    } finally {
        DB::statement('DROP TRIGGER IF EXISTS fail_aplikasi_pengajar');
    }

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('aplikasi_pengajar', 0);
    expect(Storage::disk('public')->allFiles('aplikasi_pengajar'))->toBe([]);
});

test('pendaftaran pengajar tanpa kategori ditolak', function () {
    $this->post(route('register'), [
        'name' => 'Guru Polos',
        'email' => 'guru.polos@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '19870002',
    ])->assertSessionHasErrors('kategori_mapel');

    $this->assertDatabaseCount('aplikasi_pengajar', 0);
    $this->assertDatabaseMissing('users', ['email' => 'guru.polos@example.com']);
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

test('nisn hanya wajib untuk pendaftaran jalur formal', function (string $kode, bool $wajib) {
    $jalur = JalurPendidikan::create(['kode' => $kode, 'nama' => $kode, 'urutan' => 1, 'aktif' => true]);
    $data = [
        'name' => 'Pelajar Uji',
        'email' => 'jalur@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'jalur_id' => $jalur->id,
    ];

    if ($kode !== 'self_learning') {
        $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'uji', 'nama' => 'Uji', 'urutan' => 1, 'aktif' => true]);
        $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'uji', 'nama' => 'Uji', 'urutan' => 1, 'aktif' => true]);
        $data['jenjang_id'] = $jenjang->id;
        $data['tingkat_id'] = $tingkat->id;
    }

    if ($wajib) {
        $this->postJson(route('register'), $data)->assertUnprocessable()->assertJsonValidationErrors('nisn');
        $this->assertDatabaseMissing('users', ['email' => $data['email']]);
    } else {
        $this->post(route('register'), $data)->assertRedirect(route('pending'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => $data['email'], 'nisn' => null]);
    }
})->with([
    'formal' => ['formal', true],
    'nonformal' => ['nonformal', false],
    'mandiri' => ['self_learning', false],
]);

test('nisn opsional tetap harus unik bila diisi', function () {
    $jalur = JalurPendidikan::create(['kode' => 'self_learning', 'nama' => 'Mandiri', 'urutan' => 1, 'aktif' => true]);
    User::factory()->create(['nisn' => '0091234567']);

    $this->postJson(route('register'), [
        'name' => 'Pelajar Uji',
        'email' => 'duplikat@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'jalur_id' => $jalur->id,
        'nisn' => '0091234567',
    ])->assertUnprocessable()->assertJsonValidationErrors('nisn');
});

test('registrasi pelajar menolak jenjang yang tidak termasuk jalur pilihan', function () {
    $jalurFormal = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jalurNonformal = JalurPendidikan::create(['kode' => 'nonformal', 'nama' => 'Nonformal', 'urutan' => 2, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalurNonformal->id, 'kode' => 'paket-b', 'nama' => 'Paket B', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas-9', 'nama' => 'Kelas 9', 'urutan' => 1, 'aktif' => true]);

    $this->post(route('register'), [
        'name' => 'Siswa Jenjang Salah',
        'email' => 'siswa.jenjang.salah@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'nisn' => '0091234568',
        'jalur_id' => $jalurFormal->id,
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
    ])->assertSessionHasErrors('jenjang_id');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('profil_pendidikan', 0);
});

test('registrasi pelajar menolak tingkat dari jenjang lain', function () {
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjangSmp = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $jenjangSma = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'sma', 'nama' => 'SMA', 'urutan' => 2, 'aktif' => true]);
    $tingkatSma = TingkatPendidikan::create(['jenjang_id' => $jenjangSma->id, 'kode' => 'kelas-10', 'nama' => 'Kelas 10', 'urutan' => 1, 'aktif' => true]);

    $this->post(route('register'), [
        'name' => 'Siswa Tingkat Salah',
        'email' => 'siswa.tingkat.salah@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pelajar',
        'nisn' => '0091234569',
        'jalur_id' => $jalur->id,
        'jenjang_id' => $jenjangSmp->id,
        'tingkat_id' => $tingkatSma->id,
    ])->assertSessionHasErrors('tingkat_id');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('profil_pendidikan', 0);
});

test('registrasi pengajar menolak data profil pendidikan khusus pelajar', function () {
    KategoriMataPelajaran::create(['nama' => 'Matematika']);
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);

    $this->post(route('register'), [
        'name' => 'Guru Dengan Profil Siswa',
        'email' => 'guru.profil.siswa@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'pengajar',
        'nip' => '19870012',
        'kategori_mapel' => 'Matematika',
        'jalur_id' => $jalur->id,
    ])->assertSessionHasErrors('jalur_id');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('profil_pendidikan', 0);
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
