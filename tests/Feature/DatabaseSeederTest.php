<?php

use App\Models\KategoriMataPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('seeder menyiapkan operator dan kelas tanpa data pengajar atau siswa', function () {
    config(['seeding.operator_password' => 'nilai-untuk-test']);

    $this->seed(DatabaseSeeder::class);

    $pengelola = User::where('role', 'pengelola_operator')->sole();
    $operators = User::where('role', 'operator')->get();

    expect(Hash::check('nilai-untuk-test', $pengelola->password))->toBeTrue()
        ->and($operators)->toHaveCount(KategoriMataPelajaran::count())
        ->and($operators->every(fn (User $user) => $user->kategori_mapel !== null && Hash::check('nilai-untuk-test', $user->password)))->toBeTrue()
        ->and(User::whereIn('role', ['pengajar', 'siswa'])->count())->toBe(0)
        ->and(Kelas::count())->toBeGreaterThan(0)
        ->and(Kelas::whereNotNull('guru_id')->count())->toBe(0)
        ->and(DB::table('aplikasi_pengajar')->count())->toBe(0)
        ->and(DB::table('pengajar_kompetensi')->count())->toBe(0);
});

test('seeder ulang mempertahankan perubahan akun kelas dan mata pelajaran', function () {
    config(['seeding.operator_password' => 'nilai-untuk-test']);
    $this->seed(DatabaseSeeder::class);

    $operator = User::where('role', 'operator')->firstOrFail();
    $operator->update(['name' => 'Nama diubah', 'password' => 'sandi-diubah']);
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::where('kode_kelas', 'MTKSD4XQ')->firstOrFail();
    $kelas->update(['nama_kelas' => 'Kelas diubah', 'guru_id' => $guru->id]);
    $mapel = MataPelajaran::where('kode', 'matematika')->firstOrFail();
    $mapel->update(['nama' => 'Mapel diubah', 'aktif' => false]);
    $jumlahAkun = User::count();
    $jumlahKelas = Kelas::count();

    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe($jumlahAkun)
        ->and(Kelas::count())->toBe($jumlahKelas)
        ->and($operator->fresh()->name)->toBe('Nama diubah')
        ->and(Hash::check('sandi-diubah', $operator->fresh()->password))->toBeTrue()
        ->and($kelas->fresh()->nama_kelas)->toBe('Kelas diubah')
        ->and($kelas->fresh()->guru_id)->toBe($guru->id)
        ->and($mapel->fresh()->nama)->toBe('Mapel diubah')
        ->and((bool) $mapel->fresh()->aktif)->toBeFalse();
});

test('seeder mempertahankan operator kategori yang sudah ada tanpa membuat akun baru', function () {
    config(['seeding.operator_password' => 'nilai-untuk-test']);
    $operator = User::factory()->operator()->verified()->create([
        'email' => 'operator.lama@simba.test',
        'kategori_mapel' => 'Sains',
    ]);

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::where('role', 'operator')->where('kategori_mapel', 'Sains')->sole()->id)->toBe($operator->id)
        ->and(User::where('email', 'operator.sains@simba.test')->exists())->toBeFalse()
        ->and(KategoriMataPelajaran::where('nama', 'Matematika & Ilmu Alam')->exists())->toBeFalse()
        ->and(MataPelajaran::where('kode', 'matematika')->value('kategori'))->toBe('Sains');
});

test('seeder meminta kata sandi sebelum membuat data', function () {
    config(['seeding.operator_password' => null]);

    expect(fn () => $this->seed(DatabaseSeeder::class))
        ->toThrow(RuntimeException::class, 'SEED_OPERATOR_PASSWORD');

    expect(User::count())->toBe(0)
        ->and(Kelas::count())->toBe(0);
});

test('seeder menolak alamat akun yang sudah dipakai peran lain', function () {
    config(['seeding.operator_password' => 'nilai-untuk-test']);
    $user = User::factory()->pengajar()->create(['email' => 'pengelola_operator@simba.test']);

    expect(fn () => $this->seed(DatabaseSeeder::class))
        ->toThrow(RuntimeException::class, 'sudah digunakan untuk peran atau kategori lain');

    expect($user->fresh()->role)->toBe('pengajar');
});
