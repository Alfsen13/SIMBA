<?php

use App\Models\AplikasiPengajar;
use App\Models\JenjangPendidikan;
use App\Models\Kelas;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Database\Seeders\MasterPendidikanSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->operator = User::factory()->operator()->create();
});

test('operator dashboard returns 200', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.dashboard'))
        ->assertOk();
});

test('operator users index returns 200', function () {
    User::factory()->count(3)->create();

    $this->actingAs($this->operator)
        ->get(route('operator.users.index'))
        ->assertOk()
        ->assertSee('grid grid-cols-2', false)
        ->assertSee('py-3', false)
        ->assertSee('p-3.5', false);
});

test('operator users index explains when no accounts are available', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);

    $this->actingAs($this->operator)
        ->get(route('operator.users.index'))
        ->assertOk()
        ->assertSee(__('operator.empty_users'));
});

test('operator users index offers to clear filters when there are no matches', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);

    $this->actingAs($this->operator)
        ->get(route('operator.users.index', ['search' => 'akun-tidak-ditemukan']))
        ->assertOk()
        ->assertSee(__('operator.empty_users_filtered'))
        ->assertSee(__('operator.clear_users_filters'))
        ->assertSee(route('operator.users.index'));
});

test('operator users search and status filters preserve each other', function () {
    User::factory()->create([
        'name' => 'Akun Dicari',
        'status' => 'pending',
    ]);
    User::factory()->create([
        'name' => 'Akun Dicari Verified',
        'status' => 'verified',
    ]);

    $response = $this->actingAs($this->operator)
        ->get(route('operator.users.index', ['status' => 'pending', 'search' => 'Akun Dicari']))
        ->assertOk()
        ->assertSee('Akun Dicari')
        ->assertDontSee('Akun Dicari Verified')
        ->assertSee('name="status" value="pending"', false)
        ->assertSee(route('operator.users.index', ['search' => 'Akun Dicari', 'status' => 'verified']));

    expect($response->getContent())->toContain(route('operator.users.index', ['search' => 'Akun Dicari']));
});

test('operator users index labels icon-only actions accessibly', function () {
    $user = User::factory()->create([
        'name' => 'Akun Contoh',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->operator)
        ->get(route('operator.users.index'))
        ->assertOk();

    $html = $response->getContent();

    foreach ([
        __('operator.action_view_user', ['name' => $user->name]),
        __('operator.action_verify_user', ['name' => $user->name]),
        __('operator.action_reject_user', ['name' => $user->name]),
        __('operator.action_delete_user', ['name' => $user->name]),
    ] as $label) {
        $escapedLabel = preg_quote(e($label), '/');
        $pattern = '/<(?:a|button)\b(?=[^>]*\baria-label="'.$escapedLabel.'")[^>]*>\s*<svg\b(?=[^>]*\baria-hidden="true")[^>]*>/s';

        expect(preg_match($pattern, $html))->toBe(1);
    }
});

test('operator can verify a pending user', function () {
    $user = User::factory()->create(['status' => 'pending']);

    $this->actingAs($this->operator)
        ->post(route('operator.users.verify', $user))
        ->assertRedirect();

    $user->refresh();
    expect($user->status)->toBe('verified')
        ->and($user->verified_by)->toBe($this->operator->id)
        ->and($user->verified_at)->not->toBeNull();
});

test('operator can reject a pending user', function () {
    $user = User::factory()->create(['status' => 'pending']);

    $this->actingAs($this->operator)
        ->post(route('operator.users.reject', $user))
        ->assertRedirect();

    $user->refresh();
    expect($user->status)->toBe('rejected')
        ->and($user->verified_by)->toBe($this->operator->id)
        ->and($user->verified_at)->not->toBeNull();
});

test('operator hanya dapat mengubah status akun yang masih pending', function (string $action, string $status) {
    $user = User::factory()->create([
        'status' => $status,
        'verified_by' => null,
        'verified_at' => null,
    ]);

    $this->actingAs($this->operator)
        ->from(route('operator.users.show', $user))
        ->post(route('operator.users.'.$action, $user))
        ->assertRedirect(route('operator.users.show', $user))
        ->assertSessionHas('error');

    expect($user->refresh()->status)->toBe($status)
        ->and($user->verified_by)->toBeNull()
        ->and($user->verified_at)->toBeNull();
})->with([
    'akun ditolak tidak bisa diverifikasi ulang' => ['verify', 'rejected'],
    'akun verified tidak bisa ditolak' => ['reject', 'verified'],
]);

test('operator berkategori tidak dapat membuka atau mengubah akun pengajar di luar kategorinya', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);
    $pengajarLainKategori = User::factory()->pengajar()->verified()->create();
    AplikasiPengajar::create([
        'user_id' => $pengajarLainKategori->id,
        'kategori_mapel' => 'Bahasa Indonesia',
    ]);

    $this->actingAs($this->operator)
        ->get(route('operator.users.show', $pengajarLainKategori))
        ->assertForbidden();

    $this->get(route('operator.users.berkas', [
        'user' => $pengajarLainKategori,
        'jenis' => 'cv',
    ]))->assertForbidden();

    $this->get(route('operator.users.edit', $pengajarLainKategori))
        ->assertForbidden();

    $this->put(route('operator.users.update', $pengajarLainKategori), [
        'name' => 'Pengajar Diubah',
        'email' => $pengajarLainKategori->email,
    ])->assertForbidden();

    $this->put(route('operator.users.update', $pengajarLainKategori), [
        'name' => 'Pengajar Diubah',
        'email' => $pengajarLainKategori->email,
        'role' => 'pelajar',
    ])->assertForbidden();

    expect($pengajarLainKategori->refresh()->name)->not->toBe('Pengajar Diubah');
});

test('operator tidak dapat mengubah peran melalui form edit', function (string $currentRole, string $requestedRole) {
    $user = User::factory()->verified()->create(['role' => $currentRole]);

    $this->actingAs($this->operator)
        ->get(route('operator.users.edit', $user))
        ->assertOk()
        ->assertDontSee('name="role"', false);

    $this->actingAs($this->operator)
        ->put(route('operator.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $requestedRole,
        ])
        ->assertSessionHasErrors('role');

    expect($user->refresh()->role)->toBe($currentRole);
})->with([
    'pelajar menjadi pengajar' => ['pelajar', 'pengajar'],
    'pengajar menjadi pelajar' => ['pengajar', 'pelajar'],
    'pelajar menjadi operator' => ['pelajar', 'operator'],
]);

test('operator tidak dapat mengelola akun operator yang sudah ada', function () {
    $operatorLain = User::factory()->operator()->create();

    $this->actingAs($this->operator)
        ->get(route('operator.users.edit', $operatorLain))
        ->assertForbidden();

    $this->put(route('operator.users.update', $operatorLain), [
        'name' => 'Operator Diubah',
        'email' => $operatorLain->email,
    ])->assertForbidden();

    $this->put(route('operator.users.update', $operatorLain), [
        'name' => 'Operator Diubah',
        'email' => $operatorLain->email,
        'role' => 'pelajar',
    ])->assertForbidden();

    expect($operatorLain->refresh()->role)->toBe('operator')
        ->and($operatorLain->name)->not->toBe('Operator Diubah');
});

test('operator dapat mengubah akun pengajar dalam kategorinya', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);
    $pengajar = User::factory()->pengajar()->verified()->create();
    AplikasiPengajar::create(['user_id' => $pengajar->id, 'kategori_mapel' => 'Matematika']);

    $this->actingAs($this->operator)
        ->put(route('operator.users.update', $pengajar), [
            'name' => 'Nama Baru',
            'email' => $pengajar->email,
        ])
        ->assertRedirect(route('operator.users.index'))
        ->assertSessionHasNoErrors();

    expect($pengajar->fresh()->name)->toBe('Nama Baru');
});

test('operator tidak dapat memverifikasi atau menolak akun operator', function (string $action) {
    $operatorLain = User::factory()->operator()->create([
        'status' => 'pending',
        'verified_by' => null,
        'verified_at' => null,
    ]);

    $this->actingAs($this->operator)
        ->post(route('operator.users.'.$action, $operatorLain))
        ->assertForbidden();

    expect($operatorLain->refresh()->status)->toBe('pending')
        ->and($operatorLain->verified_by)->toBeNull()
        ->and($operatorLain->verified_at)->toBeNull();
})->with([
    'verifikasi' => 'verify',
    'penolakan' => 'reject',
]);

test('operator import menolak berkas xls yang tidak didukung parser', function () {
    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [
            'file' => UploadedFile::fake()->create('pengguna.xls', 10, 'application/vnd.ms-excel'),
        ])
        ->assertSessionHasErrors('file');
});

test('operator can delete a student regardless of operator category', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);
    $student = User::factory()->create(['role' => 'pelajar']);

    $this->actingAs($this->operator)
        ->delete(route('operator.users.destroy', $student))
        ->assertRedirect(route('operator.users.index'));

    $this->assertDatabaseMissing('users', ['id' => $student->id]);
});

test('operator can delete a teacher in their category', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);
    $teacher = User::factory()->pengajar()->create();
    AplikasiPengajar::create([
        'user_id' => $teacher->id,
        'kategori_mapel' => 'Matematika',
    ]);

    $this->actingAs($this->operator)
        ->delete(route('operator.users.destroy', $teacher))
        ->assertRedirect(route('operator.users.index'));

    $this->assertDatabaseMissing('users', ['id' => $teacher->id]);
});

test('berkas aplikasi tetap tersimpan jika transaksi penghapusan akun gagal', function () {
    Storage::fake('public');
    $teacher = User::factory()->pengajar()->create();
    $application = AplikasiPengajar::create([
        'user_id' => $teacher->id,
        'kategori_mapel' => 'Matematika',
        'cv_path' => 'aplikasi_pengajar/cv/cv-gagal-hapus.pdf',
    ]);
    Storage::disk('public')->put($application->cv_path, 'CV');
    DB::statement("CREATE TRIGGER fail_user_delete BEFORE DELETE ON users BEGIN SELECT RAISE(ABORT, 'Simulated delete failure.'); END");
    $this->withoutExceptionHandling();

    try {
        expect(fn () => $this->actingAs($this->operator)
            ->delete(route('operator.users.destroy', $teacher)))
            ->toThrow(QueryException::class);
    } finally {
        DB::statement('DROP TRIGGER IF EXISTS fail_user_delete');
    }

    $this->assertModelExists($teacher);
    $this->assertModelExists($application);
    Storage::disk('public')->assertExists($application->cv_path);
});

test('operator cannot delete a teacher in another category', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);
    $teacher = User::factory()->pengajar()->create();
    AplikasiPengajar::create([
        'user_id' => $teacher->id,
        'kategori_mapel' => 'Bahasa Indonesia',
    ]);

    $this->actingAs($this->operator)
        ->delete(route('operator.users.destroy', $teacher))
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $teacher->id]);
});

test('operator kelas index returns 200', function () {
    Kelas::factory()->count(2)->create();

    $this->actingAs($this->operator)
        ->get(route('operator.kelas.index'))
        ->assertOk();
});

test('operator can create a kelas', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika']);
    $this->seed(MasterPendidikanSeeder::class);
    $jenjang = JenjangPendidikan::where('kode', 'smp')->firstOrFail();
    $tingkat = TingkatPendidikan::where('jenjang_id', $jenjang->id)->firstOrFail();

    $this->actingAs($this->operator)
        ->post(route('operator.kelas.store'), [
            'nama_kelas' => 'Kelas Operator',
            'deskripsi' => 'Kelas dibuat operator.',
            'jenjang_id' => $jenjang->id,
            'tingkat_id' => $tingkat->id,
        ])
        ->assertRedirect(route('operator.kelas.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas Operator',
        'kategori' => 'Matematika',
    ]);
});

test('non-operator cannot access operator pages', function () {
    $user = User::factory()->verified()->create();

    $this->actingAs($user)
        ->get(route('operator.dashboard'))
        ->assertRedirect();
});

test('operator user show page returns 200', function () {
    $user = User::factory()->create();

    $this->actingAs($this->operator)
        ->get(route('operator.users.show', $user))
        ->assertOk();
});

test('operator user show menampilkan profil dan berkas pendaftaran pengajar', function () {
    Storage::fake('public');

    $pengajar = User::factory()->pengajar()->create(['nip' => null, 'status' => 'pending']);

    AplikasiPengajar::create([
        'user_id' => $pengajar->id,
        'pendidikan_tertinggi' => 'S1 Pendidikan Matematika',
        'pengalaman_mengajar' => '3 tahun',
        'kategori_mapel' => 'Matematika & Ilmu Alam',
        'mata_pelajaran' => ['Aljabar', 'Geometri'],
        'jenjang_dan_tingkat' => 'SMP kelas 7-9',
        'cv_path' => 'aplikasi_pengajar/cv/cv-guru.pdf',
        'ijazah_path' => 'aplikasi_pengajar/ijazah/ijazah-guru.pdf',
        'status_aplikasi' => 'pending',
    ]);

    Storage::disk('public')->put('aplikasi_pengajar/cv/cv-guru.pdf', 'dummy');
    Storage::disk('public')->put('aplikasi_pengajar/ijazah/ijazah-guru.pdf', 'dummy');

    $this->actingAs($this->operator)
        ->get(route('operator.users.show', $pengajar))
        ->assertOk()
        ->assertSee(__('operator.profil_pengajar'))
        ->assertSee('S1 Pendidikan Matematika')
        ->assertSee('SMP kelas 7-9')
        ->assertSee('Aljabar')
        ->assertSee('title="'.__('operator.preview_cv').'"', false)
        ->assertSee('title="'.__('operator.preview_ijazah').'"', false)
        ->assertSee('aria-expanded="false" aria-controls="cv-doc"', false)
        ->assertSee('aria-expanded="false" aria-controls="ijazah-doc"', false)
        ->assertSee("toggleDoc('cv-doc', this)", false)
        ->assertSee("toggleDoc('ijazah-doc', this)", false)
        ->assertSee(route('operator.users.berkas', ['user' => $pengajar, 'jenis' => 'cv']))
        ->assertSee(route('operator.users.berkas', ['user' => $pengajar, 'jenis' => 'ijazah']));

    $this->actingAs($this->operator)
        ->get(route('operator.users.berkas', ['user' => $pengajar, 'jenis' => 'cv']))
        ->assertOk();

    $this->actingAs($this->operator)
        ->get(route('operator.users.berkas', ['user' => $pengajar, 'jenis' => 'ijazah']))
        ->assertOk();
});

test('berkas pendaftaran yang tidak tersedia menghasilkan 404', function () {
    Storage::fake('public');

    $pengajar = User::factory()->pengajar()->create();

    $this->actingAs($this->operator)
        ->get(route('operator.users.berkas', ['user' => $pengajar, 'jenis' => 'cv']))
        ->assertNotFound();
});
