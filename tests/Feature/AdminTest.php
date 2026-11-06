<?php

use App\Models\AplikasiPengajar;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
        ->assertOk();
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
        'role' => 'pelajar',
    ])->assertForbidden();

    expect($pengajarLainKategori->refresh()->name)->not->toBe('Pengajar Diubah');
});

test('operator tidak dapat memberikan peran operator', function () {
    $pelajar = User::factory()->verified()->create(['role' => 'pelajar']);

    $this->actingAs($this->operator)
        ->put(route('operator.users.update', $pelajar), [
            'name' => $pelajar->name,
            'email' => $pelajar->email,
            'role' => 'operator',
        ])
        ->assertSessionHasErrors('role');

    expect($pelajar->refresh()->role)->toBe('pelajar');
});

test('operator tidak dapat mengelola akun operator yang sudah ada', function () {
    $operatorLain = User::factory()->operator()->create();

    $this->actingAs($this->operator)
        ->get(route('operator.users.edit', $operatorLain))
        ->assertForbidden();

    $this->put(route('operator.users.update', $operatorLain), [
        'name' => 'Operator Diubah',
        'email' => $operatorLain->email,
        'role' => 'pelajar',
    ])->assertForbidden();

    expect($operatorLain->refresh()->role)->toBe('operator')
        ->and($operatorLain->name)->not->toBe('Operator Diubah');
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
    $this->actingAs($this->operator)
        ->post(route('operator.kelas.store'), [
            'nama_kelas' => 'Kelas Operator',
            'nama_kelas' => 'Kelas Operator',
            'deskripsi' => 'Kelas dibuat operator.',
            'kategori' => 'Matematika',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas Operator',
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
