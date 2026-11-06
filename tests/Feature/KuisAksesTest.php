<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $this->siswa->kelasDiikuti()->attach($this->kelas->id);
});

function kuisAksesBuatKuis(Kelas $kelas, array $attributes = []): Kuis
{
    return Kuis::create(array_merge([
        'judul' => 'Kuis Akses',
        'kelas_id' => $kelas->id,
        'soal' => [
            ['pertanyaan' => 'Berapa 1 + 1?', 'pilihan' => ['2', '3'], 'jawaban' => 0],
        ],
        'is_aktif' => true,
    ], $attributes));
}

it('menolak membuka dan mengirim kuis yang tidak aktif atau di luar jadwal', function (string $kondisi) {
    $attributes = match ($kondisi) {
        'nonaktif' => ['is_aktif' => false],
        'belum_mulai' => ['tanggal_mulai' => now()->addHour()],
        'sudah_berakhir' => ['tanggal_selesai' => now()->subHour()],
    };
    $kuis = kuisAksesBuatKuis($this->kelas, $attributes);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kuis.show', $kuis))
        ->assertForbidden();

    $this->actingAs($this->siswa)
        ->post(route('pelajar.kuis.submit', $kuis), ['jawaban' => ['0' => '0']])
        ->assertForbidden();

    expect(KuisHasil::where('kuis_id', $kuis->id)->exists())->toBeFalse();
})->with([
    'kuis nonaktif' => 'nonaktif',
    'kuis belum dimulai' => 'belum_mulai',
    'kuis sudah berakhir' => 'sudah_berakhir',
]);

it('hanya menampilkan kuis aktif yang berada dalam jadwal pengerjaan', function () {
    kuisAksesBuatKuis($this->kelas, ['judul' => 'Kuis Tersedia']);
    kuisAksesBuatKuis($this->kelas, ['judul' => 'Kuis Nonaktif', 'is_aktif' => false]);
    kuisAksesBuatKuis($this->kelas, ['judul' => 'Kuis Mendatang', 'tanggal_mulai' => now()->addHour()]);
    kuisAksesBuatKuis($this->kelas, ['judul' => 'Kuis Berakhir', 'tanggal_selesai' => now()->subHour()]);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kuis.index'))
        ->assertOk()
        ->assertSee('Kuis Tersedia')
        ->assertDontSee('Kuis Nonaktif')
        ->assertDontSee('Kuis Mendatang')
        ->assertDontSee('Kuis Berakhir');
});

it('mencegah hasil kuis ganda untuk siswa yang sama di tingkat database', function () {
    $kuis = kuisAksesBuatKuis($this->kelas);
    $hasil = [
        'kuis_id' => $kuis->id,
        'siswa_id' => $this->siswa->id,
        'skor' => 100,
        'jawaban_siswa' => ['0'],
    ];

    KuisHasil::create($hasil);

    expect(fn () => KuisHasil::create($hasil))->toThrow(QueryException::class);
});

it('dashboard hanya menampilkan kuis yang sedang tersedia dan pertemuannya dibuka', function () {
    kuisAksesBuatKuis($this->kelas, ['judul' => 'Kuis Dashboard Tersedia']);
    kuisAksesBuatKuis($this->kelas, ['judul' => 'Kuis Dashboard Mendatang', 'tanggal_mulai' => now()->addHour()]);
    kuisAksesBuatKuis($this->kelas, ['judul' => 'Kuis Dashboard Berakhir', 'tanggal_selesai' => now()->subHour()]);

    $pertemuanTerkunci = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan Terkunci',
        'urutan' => 1,
        'dibuka' => false,
    ]);
    kuisAksesBuatKuis($this->kelas, [
        'judul' => 'Kuis Dashboard Terkunci',
        'pertemuan_id' => $pertemuanTerkunci->id,
    ]);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.dashboard'))
        ->assertOk()
        ->assertSee('Kuis Dashboard Tersedia')
        ->assertDontSee('Kuis Dashboard Mendatang')
        ->assertDontSee('Kuis Dashboard Berakhir')
        ->assertDontSee('Kuis Dashboard Terkunci');
});

it('dashboard tidak menampilkan materi dan tugas dari pertemuan terkunci', function () {
    $pertemuanTerbuka = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan Terbuka',
        'urutan' => 1,
        'dibuka' => true,
    ]);
    $pertemuanTerkunci = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan Terkunci',
        'urutan' => 2,
        'dibuka' => false,
    ]);

    Materi::factory()->create([
        'judul' => 'Materi Terbuka',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuanTerbuka->id,
    ]);
    Materi::factory()->create([
        'judul' => 'Materi Terkunci',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuanTerkunci->id,
    ]);
    Tugas::factory()->create([
        'judul' => 'Tugas Terbuka',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuanTerbuka->id,
    ]);
    Tugas::factory()->create([
        'judul' => 'Tugas Terkunci',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuanTerkunci->id,
    ]);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.dashboard'))
        ->assertOk()
        ->assertSee('Materi Terbuka')
        ->assertSee('Tugas Terbuka')
        ->assertDontSee('Materi Terkunci')
        ->assertDontSee('Tugas Terkunci');
});

it('tetap mengizinkan siswa melihat hasil kuis miliknya setelah jadwal berakhir', function () {
    $kuis = kuisAksesBuatKuis($this->kelas, [
        'tanggal_selesai' => now()->subHour(),
        'is_aktif' => false,
    ]);
    KuisHasil::create([
        'kuis_id' => $kuis->id,
        'siswa_id' => $this->siswa->id,
        'skor' => 100,
        'jawaban_siswa' => ['0'],
    ]);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.kuis.hasil', $kuis))
        ->assertOk()
        ->assertSee('Kuis Akses')
        ->assertSee('100');
});

it('menolak siswa lain melihat hasil kuis', function () {
    $siswaLain = User::factory()->verified()->create(['role' => 'pelajar']);
    $kuis = kuisAksesBuatKuis($this->kelas);
    KuisHasil::create([
        'kuis_id' => $kuis->id,
        'siswa_id' => $this->siswa->id,
        'skor' => 100,
        'jawaban_siswa' => ['0'],
    ]);

    $this->actingAs($siswaLain)
        ->get(route('pelajar.kuis.hasil', $kuis))
        ->assertForbidden();
});
