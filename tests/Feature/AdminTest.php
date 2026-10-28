<?php

use App\Models\AplikasiPengajar;
use App\Models\Kelas;
use App\Models\User;
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
    expect($user->status)->toBe('verified');
});

test('operator can reject a pending user', function () {
    $user = User::factory()->create(['status' => 'pending']);

    $this->actingAs($this->operator)
        ->post(route('operator.users.reject', $user))
        ->assertRedirect();

    $user->refresh();
    expect($user->status)->toBe('rejected');
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
