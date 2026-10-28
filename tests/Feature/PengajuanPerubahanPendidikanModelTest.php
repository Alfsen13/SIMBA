<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\PengajuanPerubahanPendidikan;

uses(RefreshDatabase::class);

test('pengajuan perubahan pendidikan tersimpan dengan status terpisah dari akun', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_9', 'nama' => 'Kelas 9', 'urutan' => 3, 'aktif' => true]);

    $pengajuan = PengajuanPerubahanPendidikan::create([
        'user_id' => $siswa->id,
        'jalur_id' => $jalur->id,
        'jenjang_id' => $jenjang->id,
        'tingkat_id' => $tingkat->id,
        'jenis' => 'kenaikan_kelas',
        'status' => 'pending',
    ]);

    expect($pengajuan->status)->toBe('pending');
    expect($siswa->status)->toBe('verified');
});
