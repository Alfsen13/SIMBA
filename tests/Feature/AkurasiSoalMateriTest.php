<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\BloomTaxonomyService;
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

it('prompt generate pilihan ganda meminta penilaian akurasi soal terhadap materi', function () {
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

    expect($prompt)->toContain('"akurasi_materi"')
        ->toContain('"catatan_akurasi"');
});

it('prompt generate esai meminta penilaian akurasi soal terhadap materi', function () {
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

    expect($prompt)->toContain('"akurasi_materi"')
        ->toContain('"catatan_akurasi"');
});

it('memparsing akurasi materi dari jawaban AI pilihan ganda dan meng-clamp ke rentang 0-100', function () {
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

    expect($soal[0]['akurasi_materi'])->toBe(100)
        ->and($soal[0]['catatan_akurasi'])->toBe('Sesuai rumus V = I × R pada materi.')
        ->and($soal[1])->not->toHaveKey('akurasi_materi')
        ->and($soal[1])->not->toHaveKey('catatan_akurasi');
});

it('memparsing akurasi materi dari jawaban AI esai', function () {
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

    expect($soal[0]['akurasi_materi'])->toBe(92)
        ->and($soal[0]['catatan_akurasi'])->toBe('Definisi dan satuan diambil langsung dari materi.');
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

it('komponen badge akurasi soal merender badge dan catatan dengan binding Alpine', function () {
    $html = Blade::render('<x-akurasi-soal :soal="$var" />', ['var' => 'q']);

    expect($html)->toContain('q.akurasi_materi !== undefined')
        ->toContain('q.akurasi_materi >= 80')
        ->toContain('q.catatan_akurasi')
        ->toContain('akurasi_materi"></span>%');
});
