<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\Materi;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\Tugas;
use App\Models\User;
use App\Services\RemedialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->remedial = app(RemedialService::class);

    $this->siswa = User::factory()->verified()->create();
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->siswa->kelasDiikuti()->attach($this->kelas->id);
});

function remedialPanggilGetWeakTopics($service, int $siswaId): string
{
    $reflection = new ReflectionMethod($service, 'getWeakTopics');
    $reflection->setAccessible(true);

    return $reflection->invoke($service, $siswaId);
}

function remedialBuatPertemuan($kelas, string $nama): Pertemuan
{
    return Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => $nama,
        'urutan' => 1,
    ]);
}

function remedialBuatMateri($kelas, $guru, Pertemuan $pertemuan, string $judul): Materi
{
    return Materi::factory()->create([
        'judul' => $judul,
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
}

function remedialBuatKuis($kelas, Pertemuan $pertemuan, ?int $materiId, int $skor, $siswa): void
{
    remedialBuatKuisPendukung($kelas, $pertemuan, $materiId, [], $skor, $siswa);
}

/**
 * Kuis + satu hasil siswa, dengan materi pendukung opsional (isi pivot).
 *
 * @param  array<int, int>  $materiPendukung
 */
function remedialBuatKuisPendukung($kelas, Pertemuan $pertemuan, ?int $materiId, array $materiPendukung, int $skor, $siswa): Kuis
{
    $kuis = Kuis::create([
        'judul' => 'Kuis '.$pertemuan->nama_pertemuan.' #'.$pertemuan->id.'-'.$skor,
        'kelas_id' => $kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'materi_id' => $materiId,
        'soal' => [['pertanyaan' => 'Soal uji.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
    ]);

    if ($materiPendukung !== []) {
        $kuis->materiPendukung()->sync($materiPendukung);
    }

    KuisHasil::create([
        'kuis_id' => $kuis->id,
        'siswa_id' => $siswa->id,
        'skor' => $skor,
        'jawaban_siswa' => [],
    ]);

    return $kuis;
}

test('getWeakTopics memakai database nyata tanpa error skema', function () {
    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toBeString()
        ->and($hasil)->toBe('Belum teridentifikasi');
});

test('setiap kolom yang dipakai pencarian topik lemah benar-benar ada di skema', function () {
    $kolomDibutuhkan = [
        'kuis_hasil' => ['siswa_id', 'kuis_id', 'skor'],
        'kuis' => ['id', 'pertemuan_id', 'materi_id'],
        'pengumpulan_tugas' => ['siswa_id', 'tugas_id', 'nilai'],
        'tugas' => ['id', 'pertemuan_id', 'materi_id'],
        'materi' => ['id', 'judul'],
        'pertemuan' => ['id', 'nama_pertemuan'],
        'kuis_materi' => ['kuis_id', 'materi_id'],
        'tugas_materi' => ['tugas_id', 'materi_id'],
    ];

    $hilang = [];

    foreach ($kolomDibutuhkan as $tabel => $kolom) {
        $ada = Schema::getColumnListing($tabel);

        foreach (array_diff($kolom, $ada) as $nama) {
            $hilang[] = "{$tabel}.{$nama}";
        }
    }

    expect($hilang)->toBe(
        [],
        'Kolom berikut tidak ada di skema: '.implode(', ', $hilang).
        '. SQLite diam-diam meloloskan query ke kolom fiktif sehingga bug tidak terlihat di test, sementara MySQL melempar QueryException di produksi.'
    );
});

test('query topik lemah tidak melempar error MySQL untuk kolom fiktif', function () {
    $kolomAda = function (string $tabel, array $kolom): bool {
        $ada = Schema::getColumnListing($tabel);

        return count(array_diff($kolom, $ada)) === 0;
    };

    expect($kolomAda('kuis_hasil', ['siswa_id', 'kuis_id', 'skor']))->toBeTrue()
        ->and($kolomAda('kuis', ['id', 'pertemuan_id', 'materi_id']))->toBeTrue()
        ->and($kolomAda('pengumpulan_tugas', ['siswa_id', 'tugas_id', 'nilai']))->toBeTrue()
        ->and($kolomAda('tugas', ['id', 'pertemuan_id', 'materi_id']))->toBeTrue();

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toBe('Belum teridentifikasi');

    $sqlTerpakai = collect([
        DB::table('kuis_hasil')->join('kuis', 'kuis.id', '=', 'kuis_hasil.kuis_id')->toSql(),
        DB::table('pengumpulan_tugas')->join('tugas', 'tugas.id', '=', 'pengumpulan_tugas.tugas_id')->toSql(),
    ])->implode(' ');

    expect($sqlTerpakai)->not->toContain('siswa_id\' =')
        ->and($sqlTerpakai)->not->toContain('`topik`');
});

test('getWeakTopics melaporkan pertemuan dengan skor terendah beserta rata-ratanya', function () {
    $lemah = remedialBuatPertemuan($this->kelas, 'Pertemuan Fotosintesis');
    $sedang = remedialBuatPertemuan($this->kelas, 'Pertemuan Genetika');
    $tinggi = remedialBuatPertemuan($this->kelas, 'Pertemuan Pecahan');
    $tertinggi = remedialBuatPertemuan($this->kelas, 'Pertemuan Trigonometri');

    foreach ([[$lemah, 30], [$sedang, 55], [$tinggi, 90], [$tertinggi, 95]] as [$pertemuan, $skor]) {
        $kuis = Kuis::create([
            'judul' => 'Kuis '.$pertemuan->nama_pertemuan,
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $pertemuan->id,
            'soal' => [['pertanyaan' => 'Soal uji.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
        ]);
        KuisHasil::create([
            'kuis_id' => $kuis->id,
            'siswa_id' => $this->siswa->id,
            'skor' => $skor,
            'jawaban_siswa' => [],
        ]);
    }

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toStartWith('Pertemuan Fotosintesis (rata-rata 30)')
        ->and($hasil)->toContain('Pertemuan Genetika (rata-rata 55)')
        ->and($hasil)->toContain('Pertemuan Pecahan (rata-rata 90)')
        ->and($hasil)->not->toContain('Trigonometri');
});

test('getWeakTopics mengabaikan pengumpulan tugas yang belum dinilai', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Ecologi');

    $tugas = Tugas::factory()->create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'nilai' => null,
    ]);

    expect(remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id))->toBe('Belum teridentifikasi');
});

test('getWeakTopics memadukan nilai tugas yang sudah dinilai', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Tata Boga');

    $tugas = Tugas::factory()->create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'nilai' => 55,
    ]);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toContain('Pertemuan Tata Boga')
        ->and($hasil)->toContain('55');
});

test('getWeakTopics mengembalikan fallback saat belum ada nilai dan tidak ada chat history', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Seismic');
    $materi = Materi::factory()->create([
        'judul' => 'Gelombang Seismik',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toBe('Belum teridentifikasi');
});

test('getWeakTopics memprioritaskan atribusi per materi saat materi_id terisi', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Gabungan');

    // Satu pertemuan, dua materi: yang lemah harus disebut judul MATERI,
    // bukan nama pertemuan, supaya prompt remedial benar-benar presisi.
    $materiLemah = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Sistem Pernapasan');
    $materiKuat = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Sistem Digesti');

    remedialBuatKuis($this->kelas, $pertemuan, $materiLemah->id, 28, $this->siswa);
    remedialBuatKuis($this->kelas, $pertemuan, $materiKuat->id, 92, $this->siswa);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toStartWith('Sistem Pernapasan (rata-rata 28)')
        ->and($hasil)->not->toContain('Pertemuan Gabungan');
});

test('getWeakTopics memakai nilai tugas per materi saat materi_id terisi', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Biologi');
    $materi = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Ekosistem');

    $tugas = Tugas::factory()->create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuan->id,
        'materi_id' => $materi->id,
    ]);

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'nilai' => 41,
    ]);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toBe('Ekosistem (rata-rata 41)');
});

test('getWeakTopics memakai materi pendukung dari pivot kuis', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Pivot Kuis');

    // Kuis ini TIDAK punya materi utama, tapi dua materi pendukung, jadi
    // atribusi per materi harus tetap jalan lewat pivot.
    $lemah = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Fotosintesis');
    $kuat = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Transpor Air');

    remedialBuatKuisPendukung(
        $this->kelas,
        $pertemuan,
        null,
        [$lemah->id, $kuat->id],
        37,
        $this->siswa
    );

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toStartWith('Fotosintesis (rata-rata 37)')
        ->and($hasil)->not->toContain('Pertemuan Pivot Kuis');
});

test('getWeakTopics memakai materi pendukung dari pivot tugas', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Pivot Tugas');
    $materi = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Belajar Geologi');

    $tugas = Tugas::factory()->create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);
    $tugas->materiPendukung()->sync([$materi->id]);

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $this->siswa->id,
        'nilai' => 44,
    ]);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toBe('Belajar Geologi (rata-rata 44)');
});

test('rata-rata per materi dihitung per nilai, bukan rata-rata dari dua rata-rata', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Rata-rata');

    $materi = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Statistika Inferensial');
    $lain = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Aljabar Linear');

    // Materi jadi utama sekali (40) dan pendukung dua kali (80, 100).
    // Benar: (40+80+100)/3 = 73.3. Salah: (40 + avg(80,100))/2 = 65.
    remedialBuatKuisPendukung($this->kelas, $pertemuan, $materi->id, [], 40, $this->siswa);
    remedialBuatKuisPendukung($this->kelas, $pertemuan, $lain->id, [$materi->id], 80, $this->siswa);
    remedialBuatKuisPendukung($this->kelas, $pertemuan, $lain->id, [$materi->id], 100, $this->siswa);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toStartWith('Statistika Inferensial (rata-rata 73.3)')
        ->and($hasil)->toContain('Aljabar Linear (rata-rata 90)')
        ->and($hasil)->not->toContain('rata-rata 65');
});

test('materi utama sekaligus pendukung di kuis yang sama dihitung satu kali', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Ganda');

    $materi = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Ekonomi Mikro');

    // Pivot sengaja diisi materi yang sama dengan materi utama: satu nilai
    // tidak boleh dihitung dua kali untuk materi yang sama.
    remedialBuatKuisPendukung($this->kelas, $pertemuan, $materi->id, [$materi->id], 60, $this->siswa);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toBe('Ekonomi Mikro (rata-rata 60)');
});

test('relasi materiPendukung bisa ditulis dan dibaca tanpa kolom timestamp', function () {
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Relasi');
    $materi = remedialBuatMateri($this->kelas, $this->guru, $pertemuan, 'Kimia Fisika');

    $kuis = Kuis::create([
        'judul' => 'Kuis tanpa timestamp',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'soal' => [['pertanyaan' => 'Soal uji.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
    ]);

    $kuis->materiPendukung()->sync([$materi->id]);

    // Tabel pivot sengaja tidak punya created_at/updated_at, jadi relasi
    // dengan withTimestamps() akan melempar error kolom tidak ditemukan.
    expect($kuis->materiPendukung()->pluck('materi.judul')->all())->toBe(['Kimia Fisika'])
        ->and(Schema::hasColumn('kuis_materi', 'created_at'))->toBeFalse();
});

test('getWeakTopics jatuh ke pertemuan saat tidak ada kuis atau tugas ber-materi_id', function () {
    // Semua kuis lama: kolom materi_id belum ada saat dibuat, jadi NULL.
    // Atribusi per materi harus kosong, bukan dianggap nilai 0.
    $pertemuan = remedialBuatPertemuan($this->kelas, 'Pertemuan Legacy');
    remedialBuatKuis($this->kelas, $pertemuan, null, 35, $this->siswa);

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toStartWith('Pertemuan Legacy (rata-rata 35)');
});

test('getWeakTopics mengurutkan dari nilai terendah', function () {
    $sangatLemah = remedialBuatPertemuan($this->kelas, 'Topik Paling Lemah');
    $agakLemah = remedialBuatPertemuan($this->kelas, 'Topik Agak Lemah');

    foreach ([[$sangatLemah, 20], [$agakLemah, 65]] as [$pertemuan, $skor]) {
        $kuis = Kuis::create([
            'judul' => 'Kuis '.$pertemuan->nama_pertemuan,
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $pertemuan->id,
            'soal' => [['pertanyaan' => 'Soal uji.', 'pilihan' => ['A', 'B'], 'jawaban' => 0]],
        ]);
        KuisHasil::create([
            'kuis_id' => $kuis->id,
            'siswa_id' => $this->siswa->id,
            'skor' => $skor,
            'jawaban_siswa' => [],
        ]);
    }

    $hasil = remedialPanggilGetWeakTopics($this->remedial, $this->siswa->id);

    expect($hasil)->toStartWith('Topik Paling Lemah')
        ->and($hasil)->toContain('Topik Agak Lemah');
});

test('parseResponse with valid JSON returns correct array', function () {
    $json = '{"ringkasan":"Ini ringkasan materi.","latihan":"Soal latihan 1, 2, 3"}';

    $reflection = new ReflectionMethod($this->remedial, 'parseResponse');
    $reflection->setAccessible(true);
    $result = $reflection->invoke($this->remedial, $json);

    expect($result)->toHaveKeys(['ringkasan', 'latihan']);
    expect($result['ringkasan'])->toBe('Ini ringkasan materi.');
    expect($result['latihan'])->toBe('Soal latihan 1, 2, 3');
});

test('parseResponse with Ringkasan and Latihan text format returns correct array', function () {
    $text = "Ringkasan: Ini ringkasan singkat tentang materi.\nLatihan: Soal latihan 1, 2, dan 3 beserta jawaban.";

    $reflection = new ReflectionMethod($this->remedial, 'parseResponse');
    $reflection->setAccessible(true);
    $result = $reflection->invoke($this->remedial, $text);

    expect($result['ringkasan'])->toBe('Ini ringkasan singkat tentang materi.');
    expect($result['latihan'])->toBe('Soal latihan 1, 2, dan 3 beserta jawaban.');
});

test('parseResponse menangkap format Ringkasan tanpa titik dua', function () {
    // Format yang paling sering keluar dari LLM lokal: label lalu teks
    // dipisah SPASI atau BARIS BARU, bukan titik dua. Kalau regexnya cuma
    // menerima titik dua, format ini lolos tanpa tertangkap dan seluruh
    // teks masuk ke $aiOutput mentah.
    $refleksi = new ReflectionMethod($this->remedial, 'parseResponse');
    $refleksi->setAccessible(true);

    $pakaiSpasi = $refleksi->invoke($this->remedial, "Ringkasan Isi ringkasan.\nLatihan Isi latihan.");
    $pakaiBarisBaru = $refleksi->invoke($this->remedial, "Ringkasan\nIsi ringkasan.\nLatihan\nIsi latihan.");

    expect($pakaiSpasi['ringkasan'])->toBe('Isi ringkasan.')
        ->and($pakaiSpasi['latihan'])->toBe('Isi latihan.')
        ->and($pakaiBarisBaru['ringkasan'])->toBe('Isi ringkasan.')
        ->and($pakaiBarisBaru['latihan'])->toBe('Isi latihan.');
});

test('parseResponse menolak teks polos alih-alih menyimpannya sebagai ringkasan', function () {
    // Output yang tidak sesuai format berarti model degenerasi atau provider
    // gagal. Teks mentah TIDAK boleh disimpan sebagai `ringkasan`, karena itu
    // mengisi materi_remedial dengan kalimat instruksi, bukan materi.
    $reflection = new ReflectionMethod($this->remedial, 'parseResponse');
    $reflection->setAccessible(true);

    expect($reflection->invoke($this->remedial, 'Teks biasa tanpa format khusus.'))->toBeNull()
        ->and($reflection->invoke($this->remedial, ''))->toBeNull()
        ->and($reflection->invoke($this->remedial, '   '))->toBeNull();
});

test('parseResponse menolak output yang berisi kalimat instruksi', function () {
    // Bentuk persis yang dulu tersimpan: model mengulang baris instruksi prompt.
    $degenerat = ". Jawaban harus sesuai dengan ringkasan dan harus muncul dalam urutan yang sama.\n"
        .'Jawaban harus diisi dengan kata-kata yang jelas.';

    $reflection = new ReflectionMethod($this->remedial, 'parseResponse');
    $reflection->setAccessible(true);

    expect($reflection->invoke($this->remedial, $degenerat))->toBeNull();
});

test('parseResponse meratakan latihan bertipe object menjadi string', function () {
    // Prompt meminta string, tapi model sering balas object {soal1, jawaban1}.
    // Kolom `latihan` bertipe string, jadi object harus diratakan.
    $json = '{"ringkasan":"Pecahan menyatakan bagian dari keseluruhan.",'
        .'"latihan":{"soal1":"Apa itu pecahan?","jawaban1":"Bagian dari keseluruhan."}}';

    $reflection = new ReflectionMethod($this->remedial, 'parseResponse');
    $reflection->setAccessible(true);
    $hasil = $reflection->invoke($this->remedial, $json);

    expect($hasil)->toBeArray()
        ->and($hasil['ringkasan'])->toBe('Pecahan menyatakan bagian dari keseluruhan.')
        ->and($hasil['latihan'])->toBeString()
        ->and($hasil['latihan'])->toContain('Apa itu pecahan?')
        ->and($hasil['latihan'])->toContain('Bagian dari keseluruhan.');
});

test('parseResponse menolak JSON yang ringkasannya kosong', function () {
    $reflection = new ReflectionMethod($this->remedial, 'parseResponse');
    $reflection->setAccessible(true);

    expect($reflection->invoke($this->remedial, '{"ringkasan":"","latihan":"a"}'))->toBeNull();
});

test('buildContext returns array with correct keys', function () {
    $prediksi = PrediksiAi::create([
        'siswa_id' => $this->siswa->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 0.85,
        'feature_vector' => [5, 30, 80, 1, 3, 90, 85, 4, 2, 95],
    ]);

    $reflection = new ReflectionMethod($this->remedial, 'buildContext');
    $reflection->setAccessible(true);
    $result = $reflection->invoke($this->remedial, $prediksi, 'Matematika');

    expect($result)->toHaveKeys(['skor_risiko', 'feature_text', 'topik_text', 'nilai_terendah']);
    expect($result['skor_risiko'])->toBe('0.85');
    expect($result['topik_text'])->toBe('Matematika');
    expect($result['feature_text'])->toBeString();
});
