<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function rnPertemuan(Kelas $kelas, int $urutan = 1): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => "Pertemuan {$urutan}",
        'urutan' => $urutan,
    ]);
}

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->pertemuan = rnPertemuan($this->kelas);

    $this->siswa = User::factory()->verified()->create(['name' => 'Siswa Satu', 'role' => 'pelajar']);
    $this->siswa->kelasDiikuti()->attach($this->kelas->id);

    $this->kuis = Kuis::create([
        'judul' => 'Kuis Aljabar',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
        'soal' => [],
        'is_aktif' => true,
    ]);

    $this->tugas = Tugas::create([
        'judul' => 'Tugas Aljabar',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'pilihan_ganda',
        'soal' => [],
    ]);
});

it('menampilkan matriks nilai kuis dan tugas per siswa', function () {
    KuisHasil::create(['kuis_id' => $this->kuis->id, 'siswa_id' => $this->siswa->id, 'skor' => 80, 'jawaban_siswa' => []]);
    PengumpulanTugas::create(['tugas_id' => $this->tugas->id, 'siswa_id' => $this->siswa->id, 'nilai' => 70, 'jawaban' => []]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai', ['kelas' => $this->kelas->id]))
        ->assertOk()
        ->assertSee('Siswa Satu')
        ->assertSee('K1')
        ->assertSee('T1')
        ->assertSee('80.0')
        ->assertSee('70.0')
        // Rata-rata (80 + 70) / 2.
        ->assertSee('75.0');
});

it('tidak menghitung nilai yang belum ada sebagai nol', function () {
    KuisHasil::create(['kuis_id' => $this->kuis->id, 'siswa_id' => $this->siswa->id, 'skor' => 90, 'jawaban_siswa' => []]);
    PengumpulanTugas::create(['tugas_id' => $this->tugas->id, 'siswa_id' => $this->siswa->id, 'nilai' => 40, 'jawaban' => []]);

    // Tugas kedua sengaja tidak dikumpulkan; kalau dihitung 0 rata-ratanya 43.3.
    $tugasKedua = Tugas::create([
        'judul' => 'Tugas Kedua',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'pilihan_ganda',
        'soal' => [],
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai', ['kelas' => $this->kelas->id]))
        ->assertOk()
        ->assertSee('T2')
        ->assertSee('65.0')
        ->assertDontSee('43.3');
});

it('mengurutkan kolom menurut pertemuan', function () {
    $p2 = rnPertemuan($this->kelas, 2);
    $kuisDua = Kuis::create([
        'judul' => 'Kuis Pertemuan 2',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $p2->id,
        'soal' => [],
        'is_aktif' => true,
    ]);

    $html = $this->actingAs($this->guru)
        ->get(route('pengajar.nilai', ['kelas' => $this->kelas->id]))
        ->assertOk()
        ->getContent();

    // Kuis pertemuan 1 harus muncul sebelum kuis pertemuan 2.
    expect(strpos($html, 'Kuis Aljabar'))->toBeLessThan(strpos($html, 'Kuis Pertemuan 2'))
        ->and($kuisDua->id)->toBeGreaterThan($this->kuis->id);
});

it('tidak menampilkan siswa kelas guru lain', function () {
    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);
    $siswaLain = User::factory()->verified()->create(['name' => 'Siswa Lain', 'role' => 'pelajar']);
    $siswaLain->kelasDiikuti()->attach($kelasLain->id);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai', ['kelas' => $kelasLain->id]))
        ->assertOk()
        ->assertDontSee('Siswa Lain');
});

it('menampilkan pesan saat kelas belum punya kuis atau tugas', function () {
    $kelasKosong = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $siswaKosong = User::factory()->verified()->create(['name' => 'Siswa Kosong', 'role' => 'pelajar']);
    $siswaKosong->kelasDiikuti()->attach($kelasKosong->id);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai', ['kelas' => $kelasKosong->id]))
        ->assertOk()
        ->assertSee('belum punya kuis atau tugas');
});

it('menolak pelajar mengakses rekap nilai guru', function () {
    $this->actingAs($this->siswa)
        ->get(route('pengajar.nilai'))
        ->assertRedirect(route('pelajar.dashboard'));
});

it('menautkan nama siswa ke halaman detail nilai', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai', ['kelas' => $this->kelas->id]))
        ->assertOk()
        ->assertSee(route('pengajar.nilai.show', ['siswa' => $this->siswa->id, 'kelas' => $this->kelas->id]));
});

it('menampilkan detail nilai kuis dan tugas satu siswa', function () {
    KuisHasil::create(['kuis_id' => $this->kuis->id, 'siswa_id' => $this->siswa->id, 'skor' => 80, 'jawaban_siswa' => []]);
    PengumpulanTugas::create(['tugas_id' => $this->tugas->id, 'siswa_id' => $this->siswa->id, 'nilai' => 70, 'jawaban' => []]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai.show', ['siswa' => $this->siswa->id, 'kelas' => $this->kelas->id]))
        ->assertOk()
        ->assertSee('Siswa Satu')
        ->assertSee('Kuis Aljabar')
        ->assertSee('Tugas Aljabar')
        ->assertSee('80.0')
        ->assertSee('70.0')
        ->assertSee('Cetak PDF');
});

it('menandai tugas yang belum dinilai di halaman detail', function () {
    PengumpulanTugas::create(['tugas_id' => $this->tugas->id, 'siswa_id' => $this->siswa->id, 'nilai' => null, 'jawaban' => []]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai.show', ['siswa' => $this->siswa->id]))
        ->assertOk()
        ->assertSee('Belum dinilai');
});

it('menolak detail siswa di luar kelas guru', function () {
    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);
    $siswaLain = User::factory()->verified()->create(['name' => 'Siswa Lain', 'role' => 'pelajar']);
    $siswaLain->kelasDiikuti()->attach($kelasLain->id);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai.show', ['siswa' => $siswaLain->id]))
        ->assertNotFound();
});

it('menolak filter kelas milik guru lain di halaman detail', function () {
    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.nilai.show', ['siswa' => $this->siswa->id, 'kelas' => $kelasLain->id]))
        ->assertNotFound();
});

it('mengunduh daftar nilai siswa sebagai PDF', function () {
    KuisHasil::create(['kuis_id' => $this->kuis->id, 'siswa_id' => $this->siswa->id, 'skor' => 80, 'jawaban_siswa' => []]);

    $response = $this->actingAs($this->guru)
        ->get(route('pengajar.nilai.pdf', ['siswa' => $this->siswa->id, 'kelas' => $this->kelas->id]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and($response->getContent())->toStartWith('%PDF');
});
