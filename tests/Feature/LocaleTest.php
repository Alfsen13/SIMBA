<?php

use App\Support\Bahasa;
use Illuminate\Support\Facades\App;

it('default locale adalah Bahasa Indonesia', function () {
    expect(config('locales.default'))->toBe('id');
    expect(config('locales.supported'))->toHaveKeys(['id', 'en']);

    $this->get('/login');
    expect(App::getLocale())->toBe('id');
});

it('halaman login tampil dalam Bahasa Indonesia secara default', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Masuk ke Akun Anda')
        ->assertSee('Ingat saya');
});

it('mengganti bahasa menyimpan preferensi di session dan mengubah UI', function () {
    $this->from('/login')->post('/locale', ['locale' => 'en'])
        ->assertRedirect('/login')
        ->assertSessionHas('locale', 'en');

    $this->get('/login')
        ->assertOk()
        ->assertSee('Sign in to Your Account')
        ->assertSee('Remember me');
});

it('menolak bahasa yang tidak didukung', function () {
    $this->from('/login')->post('/locale', ['locale' => 'fr'])
        ->assertSessionHasErrors('locale');

    $this->get('/login')->assertSee('Masuk ke Akun Anda');
});

it('preferensi bahasa bertahan lintas request', function () {
    $this->withSession(['locale' => 'en']);

    $this->get('/login')->assertSee('Sign in to Your Account');

    $this->get('/register')->assertSee('Create a New Account');
});

it('instruksi bahasa AI mengikuti locale aktif', function () {
    App::setLocale('id');
    expect(Bahasa::aiName())->toBe('Bahasa Indonesia');
    expect(Bahasa::instruksi())->toContain('Bahasa Indonesia');

    App::setLocale('en');
    expect(Bahasa::aiName())->toBe('English');
    expect(Bahasa::instruksi())->toContain('English');
});

it('semua namespace lang kustom punya key yang sama di id dan en', function () {
    $namespaces = [
        'auth', 'common', 'guru_analitik', 'guru_kelas', 'guru_soal',
        'komponen', 'navigation', 'notifications', 'operator', 'publik', 'siswa',
    ];

    $flatten = function (array $a, string $prefix = '') use (&$flatten): array {
        $out = [];
        foreach ($a as $k => $v) {
            $key = $prefix === '' ? (string) $k : $prefix.'.'.$k;
            $out += is_array($v) ? $flatten($v, $key) : [$key => $v];
        }

        return $out;
    };

    foreach ($namespaces as $ns) {
        $id = array_keys($flatten(require lang_path("id/$ns.php")));
        $en = array_keys($flatten(require lang_path("en/$ns.php")));

        expect($id)->toEqualCanonicalizing($en);
    }
});

it('label tombol yang di-bind Alpine punya terjemahan id copy-exact dan en', function () {
    App::setLocale('id');
    expect(__('guru_analitik.prediksi.run'))->toBe('Jalankan Prediksi');
    expect(__('guru_soal.menyusun'))->toBe('Menyusun...');
    expect(__('siswa.kuis.sisa_waktu'))->toBe('Sisa waktu:');

    App::setLocale('en');
    expect(__('guru_analitik.prediksi.run'))->toBe('Run Prediction');
    expect(__('guru_soal.menyusun'))->toBe('Drafting...');
    expect(__('siswa.kuis.sisa_waktu'))->toBe('Time left:');
});
