<?php

use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\PengajuanPerubahanPendidikan;
use App\Models\ProfilPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $this->jalur = JalurPendidikan::create([
        'kode' => 'formal',
        'nama' => 'Formal',
        'urutan' => 1,
        'aktif' => true,
    ]);
    $this->jenjangSmp = JenjangPendidikan::create([
        'jalur_id' => $this->jalur->id,
        'kode' => 'smp',
        'nama' => 'SMP',
        'urutan' => 1,
        'aktif' => true,
    ]);
    $this->jenjangSma = JenjangPendidikan::create([
        'jalur_id' => $this->jalur->id,
        'kode' => 'sma',
        'nama' => 'SMA',
        'urutan' => 2,
        'aktif' => true,
    ]);
    $this->tingkatSmp = TingkatPendidikan::create([
        'jenjang_id' => $this->jenjangSmp->id,
        'kode' => 'kelas_9',
        'nama' => 'Kelas 9',
        'urutan' => 3,
        'aktif' => true,
    ]);
    $this->tingkatSma = TingkatPendidikan::create([
        'jenjang_id' => $this->jenjangSma->id,
        'kode' => 'kelas_10',
        'nama' => 'Kelas 10',
        'urutan' => 1,
        'aktif' => true,
    ]);

    ProfilPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
    ]);
});

test('siswa tidak dapat mengajukan tingkat yang bukan bagian dari jenjang tujuan', function () {
    $this->actingAs($this->siswa)
        ->post(route('pelajar.pengajuan-pendidikan.store'), [
            'jenis' => 'perpindahan_jenjang',
            'jenjang_id' => $this->jenjangSmp->id,
            'tingkat_id' => $this->tingkatSma->id,
            'alasan' => 'Saya ingin memperbarui jenjang pendidikan.',
        ])
        ->assertSessionHasErrors('tingkat_id');

    expect($this->siswa->pengajuanPerubahanPendidikan()->exists())->toBeFalse();
});

test('siswa dapat mengajukan tingkat yang sesuai dengan jenjang tujuan', function () {
    $this->actingAs($this->siswa)
        ->post(route('pelajar.pengajuan-pendidikan.store'), [
            'jenis' => 'kenaikan_kelas',
            'jenjang_id' => $this->jenjangSmp->id,
            'tingkat_id' => $this->tingkatSmp->id,
            'alasan' => 'Saya ingin naik ke tingkat kelas sembilan.',
        ])
        ->assertRedirect(route('pelajar.pengajuan-pendidikan.index'))
        ->assertSessionHasNoErrors();

    $pengajuan = $this->siswa->pengajuanPerubahanPendidikan()->first();

    expect($pengajuan)->not->toBeNull()
        ->and($pengajuan->jenjang_id)->toBe($this->jenjangSmp->id)
        ->and($pengajuan->tingkat_id)->toBe($this->tingkatSmp->id);
});

test('pengajuan aktif yang sudah ada mencegah pengiriman pengajuan kedua', function () {
    PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSmp->id,
        'tingkat_id' => $this->tingkatSmp->id,
        'jenis' => 'kenaikan_kelas',
        'status' => 'under_review',
        'catatan_operator' => 'Menunggu keputusan operator.',
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.pengajuan-pendidikan.store'), [
            'jenis' => 'kenaikan_kelas',
            'jenjang_id' => $this->jenjangSma->id,
            'tingkat_id' => $this->tingkatSma->id,
            'alasan' => 'Saya ingin mengajukan perubahan ke tingkat berikutnya.',
        ])
        ->assertRedirect(route('pelajar.pengajuan-pendidikan.index'))
        ->assertSessionHas('error', 'Masih ada pengajuan perubahan pendidikan yang sedang diproses.');

    expect($this->siswa->pengajuanPerubahanPendidikan()->count())->toBe(1);
});

test('persetujuan ulang yang statusnya berubah tidak memperbarui profil siswa', function () {
    $operator = User::factory()->operator()->verified()->create();
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSma->id,
        'tingkat_id' => $this->tingkatSma->id,
        'jenis' => 'perpindahan_jenjang',
        'status' => 'pending',
        'catatan_operator' => 'Saya ingin pindah ke jenjang SMA.',
    ]);

    $statusBerubahSesudahBinding = false;
    DB::listen(function (QueryExecuted $query) use ($pengajuan, &$statusBerubahSesudahBinding): void {
        $sql = strtolower($query->sql);

        if (! $statusBerubahSesudahBinding
            && str_starts_with(ltrim($sql), 'select')
            && str_contains($sql, 'pengajuan_perubahan_pendidikan')
            && str_contains($sql, 'limit 1')) {
            $statusBerubahSesudahBinding = true;
            DB::table('pengajuan_perubahan_pendidikan')
                ->where('id', $pengajuan->id)
                ->update(['status' => 'rejected']);
        }
    });

    $this->actingAs($operator)
        ->post(route('operator.pengajuan-perubahan-pendidikan.setujui', $pengajuan), [
            'catatan_operator' => '',
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'Pengajuan ini sudah diproses sebelumnya.');

    expect($statusBerubahSesudahBinding)->toBeTrue()
        ->and($pengajuan->fresh()->status)->toBe('rejected')
        ->and($this->siswa->profilPendidikan->jenjang_id)->toBeNull();
});

test('operator tidak dapat menyetujui tujuan pendidikan yang sudah tidak tersedia', function (string $perubahan) {
    $operator = User::factory()->operator()->verified()->create();
    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $this->siswa->id,
        'jalur_id' => $this->jalur->id,
        'jenjang_id' => $this->jenjangSma->id,
        'tingkat_id' => $this->tingkatSma->id,
        'jenis' => 'perpindahan_jenjang',
        'status' => 'pending',
        'catatan_operator' => 'Saya ingin pindah ke jenjang SMA.',
    ]);

    if ($perubahan === 'jenjang_dihapus') {
        $pengajuan->update(['jenjang_id' => null]);
    } else {
        $this->tingkatSma->update(['aktif' => false]);
    }

    $this->actingAs($operator)
        ->post(route('operator.pengajuan-perubahan-pendidikan.setujui', $pengajuan))
        ->assertRedirect()
        ->assertSessionHas('error', 'Tujuan pendidikan pada pengajuan sudah tidak aktif atau tidak valid. Profil siswa tidak diubah.');

    expect($pengajuan->fresh()->status)->toBe('pending')
        ->and($this->siswa->profilPendidikan->jenjang_id)->toBeNull()
        ->and($this->siswa->profilPendidikan->tingkat_id)->toBeNull();
})->with([
    'jenjang telah dihapus' => 'jenjang_dihapus',
    'tingkat telah dinonaktifkan' => 'tingkat_dinonaktifkan',
]);
