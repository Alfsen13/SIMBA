<?php

use App\Models\JalurPendidikan;
use App\Models\Kelas;
use App\Models\ProfilPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function bmSiswaDenganJalur(string $kodeJalur): User
{
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $jalur = JalurPendidikan::create(['kode' => $kodeJalur, 'nama' => ucfirst($kodeJalur), 'urutan' => 1, 'aktif' => true]);
    ProfilPendidikan::create(['user_id' => $siswa->id, 'jalur_id' => $jalur->id]);

    return $siswa;
}

function bmKelasDenganAturan(string $aturan, User $guru, string $nama = 'Kelas Contoh'): Kelas
{
    return Kelas::factory()->create([
        'guru_id' => $guru->id,
        'nama_kelas' => $nama,
        'status' => 'dibuka',
        'aturan_kelayakan' => $aturan,
    ]);
}

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
});

it('pelajar belajar mandiri hanya melihat kelas terbuka di daftar tersedia', function () {
    $siswa = bmSiswaDenganJalur('self_learning');

    bmKelasDenganAturan('terbuka', $this->guru, 'Kelas Terbuka Umum');
    bmKelasDenganAturan('sesuai_profil', $this->guru, 'Kelas Sesuai Profil');
    bmKelasDenganAturan('khusus', $this->guru, 'Kelas Khusus');

    $this->actingAs($siswa)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee('Kelas Terbuka Umum')
        ->assertDontSee('Kelas Sesuai Profil')
        ->assertDontSee('Kelas Khusus');
});

it('pelajar belajar mandiri ditolak mengajukan kelas yang bukan terbuka', function (string $aturan) {
    $siswa = bmSiswaDenganJalur('self_learning');
    $kelas = bmKelasDenganAturan($aturan, $this->guru);

    $this->actingAs($siswa)
        ->from(route('pelajar.kelas.available'))
        ->post(route('pelajar.kelas.enroll', $kelas))
        ->assertRedirect(route('pelajar.kelas.available'))
        ->assertSessionHas('error');

    expect(DB::table('kelas_siswa')->count())->toBe(0);
})->with(['sesuai_profil', 'khusus', 'sesuai_jenjang']);

it('pelajar belajar mandiri bisa mengajukan kelas terbuka', function () {
    $siswa = bmSiswaDenganJalur('self_learning');
    $kelas = bmKelasDenganAturan('terbuka', $this->guru);

    $this->actingAs($siswa)
        ->post(route('pelajar.kelas.enroll', $kelas))
        ->assertRedirect(route('pelajar.kelas.index'));

    expect(DB::table('kelas_siswa')
        ->where('siswa_id', $siswa->id)
        ->where('kelas_id', $kelas->id)
        ->value('status'))->toBe('pending');
});

it('pelajar jalur formal tidak terpengaruh aturan kelas terbuka', function () {
    $siswa = bmSiswaDenganJalur('formal');
    $kelas = bmKelasDenganAturan('sesuai_profil', $this->guru, 'Kelas Formal Biasa');

    $this->actingAs($siswa)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee('Kelas Formal Biasa');

    $this->actingAs($siswa)
        ->post(route('pelajar.kelas.enroll', $kelas))
        ->assertRedirect(route('pelajar.kelas.index'));

    expect(DB::table('kelas_siswa')->where('siswa_id', $siswa->id)->value('status'))->toBe('pending');
});

it('pelajar tanpa profil pendidikan tetap melihat semua kelas dibuka', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);

    bmKelasDenganAturan('terbuka', $this->guru, 'Terbuka Umum');
    bmKelasDenganAturan('sesuai_profil', $this->guru, 'Sesuai Profil Lama');

    $this->actingAs($siswa)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee('Terbuka Umum')
        ->assertSee('Sesuai Profil Lama');
});

it('halaman cari kelas menampilkan info aturan untuk pelajar belajar mandiri', function () {
    $siswa = bmSiswaDenganJalur('self_learning');

    $this->actingAs($siswa)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee(__('siswa.kelas.info_belajar_mandiri'));

    $siswaFormal = bmSiswaDenganJalur('formal');

    $this->actingAs($siswaFormal)
        ->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertDontSee(__('siswa.kelas.info_belajar_mandiri'));
});
