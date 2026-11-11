<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\BloomTaxonomyService;
use App\Services\EmbeddingService;
use App\Services\KuisGeneratorService;
use App\Services\Tugas\SoalTugasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.kuis_rag.enabled' => false,
        'services.symbols.enabled' => false,
    ]);
    $embedding = Mockery::mock(EmbeddingService::class)->makePartial();
    $embedding->shouldReceive('embedBatch')->andReturnUsing(function (array $texts) {
        return array_map(fn ($i) => $i === 0 ? [1.0, 0.0] : [0.2, sqrt(0.96)], array_keys($texts));
    });
    app()->instance(EmbeddingService::class, $embedding);
});

function akurasiSoalPertemuan(): Pertemuan
{
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan Akurasi',
        'urutan' => 1,
    ]);

    $materi = Materi::create([
        'judul' => 'Hukum Ohm',
        'bab' => 'Bab 1',
        'deskripsi' => 'Hubungan tegangan, arus, dan hambatan.',
        'pertemuan_id' => $pertemuan->id,
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
    ]);

    MateriBabSummary::create([
        'materi_id' => $materi->id,
        'ringkasan' => 'Hukum Ohm: V = I × R.',
    ]);

    return $pertemuan->fresh(['materi.babSummary', 'materi.document.chunks', 'kelas']);
}

it('prompt pilihan ganda tidak meminta penilaian akurasi dari AI', function () {
    $pertemuan = akurasiSoalPertemuan();
    $prompt = null;

    $this->mock(AIProviderService::class, function ($mock) use (&$prompt) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($p) use (&$prompt) {
                $prompt = $p;

                return true;
            })
            ->andReturn(json_encode([[
                'pertanyaan' => 'Berapa nilai R jika V = 12 V dan I = 2 A?',
                'pilihan' => ['4 Ω', '6 Ω', '8 Ω', '24 Ω'],
                'jawaban' => 1,
            ]]));
    });

    app(KuisGeneratorService::class)->generate($pertemuan, 1, 'sedang', null);

    expect($prompt)->not->toContain('"akurasi_materi"')
        ->not->toContain('"catatan_akurasi"');
});

it('prompt esai tidak meminta penilaian akurasi dari AI', function () {
    $pertemuan = akurasiSoalPertemuan();
    $prompt = null;

    $this->mock(AIProviderService::class, function ($mock) use (&$prompt) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($p) use (&$prompt) {
                $prompt = $p;

                return true;
            })
            ->andReturn(json_encode([[
                'pertanyaan' => 'Jelaskan hukum Ohm beserta arti setiap besaran.',
                'referensi' => 'V = I × R, dengan V tegangan, I arus, dan R hambatan.',
            ]]));
    });

    app(KuisGeneratorService::class)->generateEssai($pertemuan, 1, null);

    expect($prompt)->not->toContain('"akurasi_materi"')
        ->not->toContain('"catatan_akurasi"');
});

it('menghitung kemiripan pilihan ganda tanpa menggunakan angka dari AI', function () {
    $pertemuan = akurasiSoalPertemuan();

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->andReturn(json_encode([
                [
                    'pertanyaan' => 'Berapa nilai R jika V = 12 V dan I = 2 A?',
                    'pilihan' => ['4 Ω', '6 Ω', '8 Ω', '24 Ω'],
                    'jawaban' => 1,
                    'akurasi_materi' => 130,
                    'catatan_akurasi' => '  Sesuai rumus V = I × R pada materi.  ',
                ],
                [
                    'pertanyaan' => 'Satuan hambatan listrik adalah ...',
                    'pilihan' => ['Volt', 'Ohm', 'Ampere', 'Watt'],
                    'jawaban' => 1,
                ],
            ]));
    });

    $soal = app(KuisGeneratorService::class)->generate($pertemuan, 2, 'sedang', null);

    expect($soal[0]['kemiripan_materi'])->toEqualWithDelta(0.2, 0.000001)
        ->and($soal[0]['kemiripan_pertanyaan'])->toBe($soal[0]['pertanyaan'])
        ->and($soal[0])->not->toHaveKey('akurasi_materi')
        ->and($soal[1]['kemiripan_materi'])->toEqualWithDelta(0.2, 0.000001);
});

it('menghitung kemiripan esai tanpa menggunakan angka dari AI', function () {
    $pertemuan = akurasiSoalPertemuan();

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->andReturn(json_encode([[
                'pertanyaan' => 'Jelaskan hukum Ohm beserta arti setiap besaran.',
                'referensi' => 'V = I × R.',
                'akurasi_materi' => 92,
                'catatan_akurasi' => 'Definisi dan satuan diambil langsung dari materi.',
            ]]));
    });

    $soal = app(KuisGeneratorService::class)->generateEssai($pertemuan, 1, null);

    expect($soal[0]['kemiripan_materi'])->toEqualWithDelta(0.2, 0.000001)
        ->and($soal[0])->not->toHaveKey('akurasi_materi');
});

it('menyimpan akurasi materi saat tugas disimpan lewat normalisasi soal', function () {
    $svc = app(SoalTugasService::class);

    $pg = $svc->normalisasiDaftar([[
        'pertanyaan' => 'Berapa 2 + 2?',
        'pilihan' => ['3', '4', '5', '6'],
        'jawaban' => 1,
        'akurasi_materi' => 88,
        'catatan_akurasi' => 'Contoh penjumlahan pada materi.',
    ]], 'pilihan_ganda');

    $esai = $svc->normalisasiDaftar([[
        'pertanyaan' => 'Jelaskan hasil penjumlahan.',
        'referensi' => '2 + 2 = 4.',
        'akurasi_materi' => '75',
        'catatan_akurasi' => '  Topik sesuai materi.  ',
    ]], 'essai');

    expect($pg[0]['akurasi_materi'])->toBe(88)
        ->and($pg[0]['catatan_akurasi'])->toBe('Contoh penjumlahan pada materi.')
        ->and($esai[0]['akurasi_materi'])->toBe(75)
        ->and($esai[0]['catatan_akurasi'])->toBe('Topik sesuai materi.');
});

it('tidak menyimpan akurasi yang tidak valid saat tugas disimpan', function () {
    $svc = app(SoalTugasService::class);

    $hasil = $svc->normalisasiDaftar([[
        'pertanyaan' => 'Berapa 2 + 2?',
        'pilihan' => ['3', '4', '5', '6'],
        'jawaban' => 1,
        'akurasi_materi' => 'tinggi',
        'catatan_akurasi' => '   ',
    ]], 'pilihan_ganda');

    expect($hasil[0])->not->toHaveKey('akurasi_materi')
        ->and($hasil[0])->not->toHaveKey('catatan_akurasi');
});

it('lengkapiOtomatis kuis mempertahankan field akurasi saat soal disimpan', function () {
    $hasil = app(BloomTaxonomyService::class)->lengkapiOtomatis([[
        'pertanyaan' => 'Berapa nilai R jika V = 12 V dan I = 2 A?',
        'pilihan' => ['4 Ω', '6 Ω', '8 Ω', '24 Ω'],
        'jawaban' => 1,
        'akurasi_materi' => 90,
        'catatan_akurasi' => 'Berdasarkan rumus pada materi.',
    ]]);

    expect($hasil[0]['akurasi_materi'])->toBe(90)
        ->and($hasil[0]['catatan_akurasi'])->toBe('Berdasarkan rumus pada materi.');
});

it('komponen menampilkan kemiripan hanya untuk pertanyaan yang dihitung', function () {
    $html = Blade::render('<x-akurasi-soal :soal="$var" />', ['var' => 'q']);

    expect($html)->toContain("hasOwnProperty.call(q, 'kemiripan_materi')")
        ->toContain('q.kemiripan_pertanyaan === q.pertanyaan')
        ->not->toContain('q.akurasi_materi');
});

it('mempertahankan nilai kemiripan negatif dan nol saat menyimpan tugas', function (float $nilai) {
    $soal = app(SoalTugasService::class)->normalisasiDaftar([[
        'pertanyaan' => 'Pertanyaan',
        'kemiripan_pertanyaan' => 'Pertanyaan',
        'kemiripan_materi' => $nilai,
    ]], 'essai');

    expect($soal[0]['kemiripan_materi'])->toBe($nilai);
})->with([-0.2, 0.0, 0.2]);

it('tidak menyimpan kemiripan jika pertanyaan berubah', function () {
    $soal = app(SoalTugasService::class)->normalisasiDaftar([[
        'pertanyaan' => 'Pertanyaan baru',
        'kemiripan_pertanyaan' => 'Pertanyaan lama',
        'kemiripan_materi' => 0.2,
    ]], 'essai');

    expect($soal[0])->not->toHaveKey('kemiripan_materi');
});

it('tetap menghasilkan soal ketika embedding gagal', function () {
    $pertemuan = akurasiSoalPertemuan();
    $this->mock(AIProviderService::class, fn ($mock) => $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([[
        'pertanyaan' => 'Jelaskan hukum Ohm.',
        'referensi' => 'V = I × R.',
    ]])));
    $this->mock(EmbeddingService::class, fn ($mock) => $mock->shouldReceive('embedBatch')->once()->andThrow(new RuntimeException('Embedding gagal')));

    $soal = app(KuisGeneratorService::class)->generateEssai($pertemuan, 1, null);

    expect($soal[0]['kemiripan_materi'])->toBeNull();
});

it('menghasilkan kemiripan kosinus negatif dan nol tanpa membulatkan ke integer', function (array $vector, float $expected) {
    $pertemuan = akurasiSoalPertemuan();
    $this->mock(AIProviderService::class, fn ($mock) => $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([[
        'pertanyaan' => 'Jelaskan hukum Ohm.',
        'referensi' => 'V = I × R.',
    ]])));
    $embedding = Mockery::mock(EmbeddingService::class)->makePartial();
    $embedding->shouldReceive('embedBatch')->once()->andReturn([[1.0, 0.0], $vector]);
    app()->instance(EmbeddingService::class, $embedding);

    $soal = app(KuisGeneratorService::class)->generateEssai($pertemuan, 1, null);

    expect($soal[0]['kemiripan_materi'])->toEqualWithDelta($expected, 0.000001);
})->with([
    [[-0.2, sqrt(0.96)], -0.2],
    [[0.0, 1.0], 0.0],
]);

it('tidak menampilkan nilai palsu untuk vektor embedding nol', function () {
    $pertemuan = akurasiSoalPertemuan();
    $this->mock(AIProviderService::class, fn ($mock) => $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([[
        'pertanyaan' => 'Jelaskan hukum Ohm.',
        'referensi' => 'V = I × R.',
    ]])));
    $this->mock(EmbeddingService::class, fn ($mock) => $mock->shouldReceive('embedBatch')->once()->andReturn([[1.0, 0.0], [0.0, 0.0]]));

    $soal = app(KuisGeneratorService::class)->generateEssai($pertemuan, 1, null);

    expect($soal[0]['kemiripan_materi'])->toBeNull();
});
