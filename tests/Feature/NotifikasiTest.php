<?php

use App\Models\Notifikasi;
use App\Models\User;
use App\Notifications\RemedialTersedia;
use App\Notifications\RisikoTinggiDetected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->verified()->create();
    $this->notif = Notifikasi::create([
        'user_id' => $this->user->id,
        'judul' => 'Test Notifikasi',
        'pesan' => 'Ini adalah pesan notifikasi.',
        'tipe' => 'info',
    ]);
});

test('user can see notifikasi page', function () {
    $this->actingAs($this->user)
        ->get(route('pelajar.notifikasi'))
        ->assertOk()
        ->assertSee('Test Notifikasi');
});

test('user can mark notifikasi as read', function () {
    $this->actingAs($this->user)
        ->post(route('pelajar.notifikasi.read', $this->notif))
        ->assertRedirect();

    expect($this->notif->fresh()->is_read)->toBeTrue();
});

test('user cannot mark other users notifikasi as read', function () {
    $other = User::factory()->verified()->create();
    $otherNotif = Notifikasi::create([
        'user_id' => $other->id,
        'judul' => 'Other',
        'pesan' => 'Other message',
    ]);

    $this->actingAs($this->user)
        ->post(route('pelajar.notifikasi.read', $otherNotif))
        ->assertForbidden();
});

test('user can mark all notifikasi as read', function () {
    Notifikasi::create([
        'user_id' => $this->user->id,
        'judul' => 'Second',
        'pesan' => 'Second message',
    ]);

    $this->actingAs($this->user)
        ->post(route('pelajar.notifikasi.read-all'))
        ->assertRedirect();

    expect($this->user->notifikasi()->where('is_read', false)->count())->toBe(0);
});

test('operator can access notifikasi page', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user)
        ->get(route('operator.notifikasi'))
        ->assertOk();
});

test('guru can access notifikasi page', function () {
    $guru = User::factory()->pengajar()->verified()->create();

    $this->actingAs($guru)
        ->get(route('pengajar.notifikasi'))
        ->assertOk();
});

test('tabel notifikasis punya kolom link', function () {
    expect(Schema::hasColumn('notifikasis', 'link'))->toBeTrue();
});

test('RemedialTersedia menyimpan deep link ke halaman remedial siswa', function () {
    $this->user->notify(new RemedialTersedia(123));

    $notifikasi = Notifikasi::where('user_id', $this->user->id)
        ->where('tipe', 'remedial')
        ->latest()
        ->first();

    expect($notifikasi)->not->toBeNull()
        ->and($notifikasi->link)->toBe('/pelajar/remedial/123');
});

test('RisikoTinggiDetected menyimpan deep link ke halaman remedial guru', function () {
    $guru = User::factory()->pengajar()->verified()->create();

    $guru->notify(new RisikoTinggiDetected('Budi', 0.82, 7));

    $notifikasi = Notifikasi::where('user_id', $guru->id)
        ->where('tipe', 'peringatan')
        ->latest()
        ->first();

    expect($notifikasi)->not->toBeNull()
        ->and($notifikasi->link)->toBe('/pengajar/remedial');
});

test('halaman notifikasi menampilkan tombol Lihat bila link tersedia', function () {
    Notifikasi::create([
        'user_id' => $this->user->id,
        'judul' => 'Remedial baru',
        'pesan' => 'Buka materinya.',
        'tipe' => 'remedial',
        'link' => '/pelajar/remedial/9',
    ]);

    $this->actingAs($this->user)
        ->get(route('pelajar.notifikasi'))
        ->assertOk()
        ->assertSee('Remedial baru')
        ->assertSee('Lihat')
        ->assertSee('/pelajar/remedial/9', false);
});

test('halaman notifikasi tidak menampilkan tombol Lihat bila link kosong', function () {
    $this->actingAs($this->user)
        ->get(route('pelajar.notifikasi'))
        ->assertOk()
        ->assertDontSee('Lihat');
});
