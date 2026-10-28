<?php

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function pkAjukan(User $siswa, Kelas $kelas, string $status = 'pending'): void
{
    $siswa->semuaKelasDiikuti()->attach($kelas->id, ['status' => $status]);
}

beforeEach(function () {
    $this->operator = User::factory()->operator()->create();
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->siswa = User::factory()->verified()->create(['role' => 'pelajar']);
});

it('menampilkan pengajuan pendaftaran yang menunggu validasi', function () {
    pkAjukan($this->siswa, $this->kelas);

    $this->actingAs($this->operator)
        ->get(route('operator.pendaftaran-kelas.index'))
        ->assertOk()
        ->assertSee($this->siswa->name)
        ->assertSee($this->kelas->nama_kelas);
});

it('operator menyetujui pendaftaran sehingga siswa jadi anggota aktif', function () {
    pkAjukan($this->siswa, $this->kelas);

    $this->actingAs($this->operator)
        ->post(route('operator.pendaftaran-kelas.setujui', ['kelas' => $this->kelas->id, 'siswa' => $this->siswa->id]))
        ->assertRedirect();

    $pivot = DB::table('kelas_siswa')
        ->where('kelas_id', $this->kelas->id)
        ->where('siswa_id', $this->siswa->id)
        ->first();

    expect($pivot->status)->toBe('active')
        ->and($pivot->approved_by)->toBe($this->operator->id)
        ->and($pivot->approved_at)->not->toBeNull()
        ->and($this->siswa->kelasDiikuti()->where('kelas_id', $this->kelas->id)->exists())->toBeTrue();
});

it('operator menolak pendaftaran sehingga siswa bukan anggota', function () {
    pkAjukan($this->siswa, $this->kelas);

    $this->actingAs($this->operator)
        ->post(route('operator.pendaftaran-kelas.tolak', ['kelas' => $this->kelas->id, 'siswa' => $this->siswa->id]))
        ->assertRedirect();

    expect(DB::table('kelas_siswa')->where('kelas_id', $this->kelas->id)->where('siswa_id', $this->siswa->id)->value('status'))->toBe('rejected')
        ->and($this->siswa->kelasDiikuti)->toHaveCount(0);
});

it('siswa yang ditolak boleh mengajukan ulang', function () {
    pkAjukan($this->siswa, $this->kelas, 'rejected');

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kelas.enroll', $this->kelas))
        ->assertRedirect(route('pelajar.kelas.index'));

    expect(DB::table('kelas_siswa')->where('kelas_id', $this->kelas->id)->where('siswa_id', $this->siswa->id)->value('status'))->toBe('pending')
        ->and(DB::table('kelas_siswa')->count())->toBe(1);
});

it('menyetujui dua kali tidak menggandakan baris', function () {
    pkAjukan($this->siswa, $this->kelas);

    $url = route('operator.pendaftaran-kelas.setujui', ['kelas' => $this->kelas->id, 'siswa' => $this->siswa->id]);

    $this->actingAs($this->operator)->post($url)->assertRedirect();
    $this->actingAs($this->operator)->post($url)->assertRedirect();

    expect(DB::table('kelas_siswa')->count())->toBe(1)
        ->and(DB::table('kelas_siswa')->value('status'))->toBe('active');
});

it('non-operator tidak boleh mengakses halaman pendaftaran kelas', function () {
    $this->actingAs($this->guru)
        ->get(route('operator.pendaftaran-kelas.index'))
        ->assertRedirect(route('pengajar.dashboard'));

    $this->actingAs($this->siswa)
        ->get(route('operator.pendaftaran-kelas.index'))
        ->assertRedirect(route('pelajar.dashboard'));
});

it('kelas pending disembunyikan dari daftar tersedia', function () {
    pkAjukan($this->siswa, $this->kelas);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertDontSee($this->kelas->nama_kelas);
});

it('kelas yang ditolak muncul lagi di daftar tersedia', function () {
    pkAjukan($this->siswa, $this->kelas, 'rejected');

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee($this->kelas->nama_kelas);
});

it('siswa pending tidak bisa membuka detail kelas', function () {
    pkAjukan($this->siswa, $this->kelas);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kelas.show', $this->kelas))
        ->assertForbidden();
});

it('siswa pending bisa membatalkan pendaftaran', function () {
    pkAjukan($this->siswa, $this->kelas);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kelas.unenroll', $this->kelas))
        ->assertRedirect(route('pelajar.kelas.index'));

    expect(DB::table('kelas_siswa')->count())->toBe(0);
});

it('mengirim notifikasi ke siswa saat pendaftaran disetujui', function () {
    pkAjukan($this->siswa, $this->kelas);

    $this->actingAs($this->operator)
        ->post(route('operator.pendaftaran-kelas.setujui', ['kelas' => $this->kelas->id, 'siswa' => $this->siswa->id]));

    expect(DB::table('notifikasis')
        ->where('user_id', $this->siswa->id)
        ->where('judul', 'Pendaftaran Kelas Disetujui')
        ->exists())->toBeTrue();
});

it('kontrak skema kelas_siswa punya kolom status verifikasi', function () {
    $kolom = Schema::getColumnListing('kelas_siswa');

    expect($kolom)->toContain('status')
        ->toContain('approved_by')
        ->toContain('approved_at');
});
