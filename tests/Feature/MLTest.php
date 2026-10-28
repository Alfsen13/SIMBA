<?php

use App\Models\FiturHybridSiswa;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use App\Services\MLService;

beforeEach(function () {
    $this->ml = app(MLService::class);
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
});

/**
 * Siswa di kelas dengan satu kuis & satu tugas yang sudah lewat deadline.
 */
function mlSiswaDenganKelas(Kelas $kelas): User
{
    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($kelas->id);

    return $siswa;
}

function mlKuisMacet(Kelas $kelas, int $skorAwal = 0): Kuis
{
    return Kuis::create([
        'judul' => 'Kuis '.fake()->word(),
        'kelas_id' => $kelas->id,
        'soal' => [],
        'durasi_menit' => 30,
        'tanggal_mulai' => now()->subDays(10),
        'tanggal_selesai' => now()->subDays(2),
        'is_aktif' => false,
    ]);
}

function mlTugasMacet(Kelas $kelas): Tugas
{
    return Tugas::create([
        'judul' => 'Tugas '.fake()->word(),
        'kelas_id' => $kelas->id,
        'guru_id' => $kelas->guru_id,
        'tipe' => 'upload',
        'deadline' => now()->subDay(),
    ]);
}

test('computeAllAcademicFeatures membuat baris fitur untuk siswa yang belum punya', function () {
    $ada = FiturHybridSiswa::count();
    $siswaBaru = mlSiswaDenganKelas($this->kelas);

    expect(FiturHybridSiswa::where('siswa_id', $siswaBaru->id)->exists())->toBeFalse();

    $this->ml->computeAllAcademicFeatures();

    expect(FiturHybridSiswa::where('siswa_id', $siswaBaru->id)->exists())->toBeTrue();
    expect(FiturHybridSiswa::count())->toBe($ada + 1);
});

test('computeAllAcademicFeatures membuat fitur walau tabel fitur masih kosong', function () {
    mlSiswaDenganKelas($this->kelas);
    mlSiswaDenganKelas($this->kelas);
    mlSiswaDenganKelas($this->kelas);

    expect(FiturHybridSiswa::count())->toBe(0);

    $this->ml->computeAllAcademicFeatures();

    expect(FiturHybridSiswa::count())->toBe(3);
});

test('fitur akademik kosong untuk siswa tanpa kelas', function () {
    $siswa = User::factory()->verified()->create();

    $fitur = $this->ml->computeAcademicFeatures($siswa->id);

    expect($fitur)->toBe([
        'rata_nilai' => null,
        'persen_terlambat' => null,
        'persen_terlewat' => null,
        'nilai_keseluruhan' => null,
    ]);
});

test('fitur akademik dihitung dari kuis dan tugas nyata', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);

    // 2 kuis selesai: 80 dan 60
    foreach ([80, 60] as $skor) {
        $kuis = mlKuisMacet($this->kelas);
        KuisHasil::create(['kuis_id' => $kuis->id, 'siswa_id' => $siswa->id, 'skor' => $skor]);
    }

    // 2 tugas lewat deadline: A terlambat & dinilai 70, B dikumpulkan tapi BELUM dinilai
    $tugasA = mlTugasMacet($this->kelas);
    PengumpulanTugas::create([
        'tugas_id' => $tugasA->id, 'siswa_id' => $siswa->id, 'nilai' => 70, 'terlambat' => true,
    ]);
    $tugasB = mlTugasMacet($this->kelas);
    PengumpulanTugas::create([
        'tugas_id' => $tugasB->id, 'siswa_id' => $siswa->id, 'nilai' => null, 'terlambat' => false,
    ]);

    $fitur = $this->ml->computeAcademicFeatures($siswa->id);

    // rata dari 80, 60, dan 70 (tugas yang belum dinilai tidak masuk)
    expect((float) $fitur['rata_nilai'])->toBe(70.0);

    // 1 dari 2 pengumpulan terlambat
    expect((float) $fitur['persen_terlambat'])->toBe(50.0);

    // kedua tugas sudah dikumpulkan -> tidak ada yang terlewat
    expect((float) $fitur['persen_terlewat'])->toBe(0.0);

    // perolehan 210 / (2 kuis * 100 + 1 tugas dinilai * 100 = 300)
    expect((float) $fitur['nilai_keseluruhan'])->toBe(70.0);
});

test('tugas yang sudah dikirim tapi belum dinilai tidak dihitung terlewat', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);

    $tugas = mlTugasMacet($this->kelas);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id, 'siswa_id' => $siswa->id, 'nilai' => null, 'terlambat' => false,
    ]);

    $fitur = $this->ml->computeAcademicFeatures($siswa->id);

    // sudah dikirim, jadi bukan terlewat meski belum dinilai
    expect((float) $fitur['persen_terlewat'])->toBe(0.0);
    // dan belum menyumbang nilai apa pun
    expect($fitur['rata_nilai'])->toBeNull();
    expect($fitur['nilai_keseluruhan'])->toBeNull();
});

test('tugas yang lewat deadline tanpa pengumpulan terhitung terlewat', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);

    mlTugasMacet($this->kelas);
    $terkirim = mlTugasMacet($this->kelas);
    PengumpulanTugas::create(['tugas_id' => $terkirim->id, 'siswa_id' => $siswa->id, 'nilai' => 50]);

    $fitur = $this->ml->computeAcademicFeatures($siswa->id);

    // 1 dari 2 tugas tidak dikirim
    expect((float) $fitur['persen_terlewat'])->toBe(50.0);
});

test('tugas dan kuis yang belum lewat deadline tidak dihitung', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);

    // tugas deadline masih di depan
    Tugas::create([
        'judul' => 'Tugas Mendatang', 'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id, 'tipe' => 'upload', 'deadline' => now()->addWeek(),
    ]);

    $fitur = $this->ml->computeAcademicFeatures($siswa->id);

    expect($fitur['persen_terlewat'])->toBeNull();
    expect($fitur['nilai_keseluruhan'])->toBeNull();
});

test('guru prediksi page returns 200', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);

    $this->actingAs($this->guru)
        ->get(route('pengajar.prediksi'))
        ->assertOk();
});

test('guru can run prediksi', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);
    FiturHybridSiswa::create([
        'siswa_id' => $siswa->id,
        'rata_nilai' => 70, 'persen_terlambat' => 20, 'persen_terlewat' => 10,
        'nilai_keseluruhan' => 68,
    ]);

    $this->actingAs($this->guru)
        ->post(route('pengajar.prediksi.run'))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('halaman parameter siswa read-only untuk guru', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);
    FiturHybridSiswa::create([
        'siswa_id' => $siswa->id,
        'rata_nilai' => 78.5, 'persen_terlambat' => 10, 'persen_terlewat' => 5,
        'nilai_keseluruhan' => 74,
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.fitur-siswa'))
        ->assertOk()
        ->assertSee('78.5')
        ->assertSee('Parameter Akademik Siswa')
        // tidak ada lagi form input manual
        ->assertDontSee('name="persen_kehadiran"');
});

test('fitur siswa tidak bisa ditulis manual dari halaman guru', function () {
    $siswa = mlSiswaDenganKelas($this->kelas);
    FiturHybridSiswa::create([
        'siswa_id' => $siswa->id,
        'rata_nilai' => 78.5, 'persen_terlambat' => 10, 'persen_terlewat' => 5,
        'nilai_keseluruhan' => 74,
    ]);

    $this->actingAs($this->guru)
        ->from(route('pengajar.fitur-siswa'))
        ->post(route('pengajar.fitur-siswa.update', $siswa), ['rata_nilai' => 0])
        ->assertRedirect()
        ->assertSessionHas('error');

    // nilai tidak berubah
    expect(FiturHybridSiswa::where('siswa_id', $siswa->id)->first()->rata_nilai)->toBe(78.5);
});
