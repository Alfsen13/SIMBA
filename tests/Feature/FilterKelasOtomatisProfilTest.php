<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\ProfilPendidikan;

uses(RefreshDatabase::class);

test('kelas sesuai profil ditampilkan terlebih dahulu untuk pelajar', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $guru = User::factory()->pengajar()->verified()->create();
    $jalur = JalurPendidikan::create(['kode' => 'formal', 'nama' => 'Formal', 'urutan' => 1, 'aktif' => true]);
    $jenjang = JenjangPendidikan::create(['jalur_id' => $jalur->id, 'kode' => 'smp', 'nama' => 'SMP', 'urutan' => 1, 'aktif' => true]);
    $tingkat = TingkatPendidikan::create(['jenjang_id' => $jenjang->id, 'kode' => 'kelas_8', 'nama' => 'Kelas 8', 'urutan' => 2, 'aktif' => true]);
    ProfilPendidikan::create(['user_id' => $siswa->id, 'jalur_id' => $jalur->id, 'jenjang_id' => $jenjang->id, 'tingkat_id' => $tingkat->id]);

    Kelas::factory()->create(['nama_kelas' => 'Sesuai Profil', 'guru_id' => $guru->id, 'status' => 'dibuka', 'jalur_id' => $jalur->id, 'jenjang_id' => $jenjang->id, 'tingkat_id' => $tingkat->id, 'aturan_kelayakan' => 'sesuai_profil']);
    Kelas::factory()->create(['nama_kelas' => 'Terbuka', 'guru_id' => $guru->id, 'status' => 'dibuka', 'aturan_kelayakan' => 'terbuka']);

    $this->actingAs($siswa)->get(route('pelajar.kelas.available'))
        ->assertOk()
        ->assertSee('Sesuai Profil')
        ->assertSee('Terbuka');
});
