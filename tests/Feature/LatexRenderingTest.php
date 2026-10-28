<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\MateriRemedial;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\KuisGeneratorService;
use App\Services\RemedialTugasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.kuis_rag.enabled' => false,
        'services.symbols.enabled' => false,
    ]);
});

function latexPertemuan(): Pertemuan
{
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);

    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan Rumus',
        'urutan' => 1,
    ]);

    $materi = Materi::create([
        'judul' => 'Rumus Kuadrat',
        'bab' => 'Bab 1',
        'deskripsi' => 'Materi persamaan kuadrat',
        'pertemuan_id' => $pertemuan->id,
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
    ]);

    MateriBabSummary::create([
        'materi_id' => $materi->id,
        'ringkasan' => 'Akar persamaan kuadrat dihitung dengan rumus abc.',
    ]);

    return $pertemuan->fresh(['materi.babSummary', 'materi.document.chunks', 'kelas']);
}

it('prompt generate soal pilihan ganda meminta rumus ditulis dalam LaTeX', function () {
    $pertemuan = latexPertemuan();
    $system = null;

    $this->mock(AIProviderService::class, function ($mock) use (&$system) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($prompt, $history, $sys) use (&$system) {
                $system = $sys;

                return true;
            })
            ->andReturn(json_encode([[
                'pertanyaan' => 'Akar dari $x^2-1=0$?',
                'pilihan' => ['$1$', '$-1$', '$0$', '$2$'],
                'jawaban' => 0,
            ]]));
    });

    app(KuisGeneratorService::class)->generate($pertemuan, 1, 'sedang', null);

    expect($system)->toContain('LaTeX');
});

it('prompt generate soal esai meminta rumus ditulis dalam LaTeX', function () {
    $pertemuan = latexPertemuan();
    $system = null;

    $this->mock(AIProviderService::class, function ($mock) use (&$system) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($prompt, $history, $sys) use (&$system) {
                $system = $sys;

                return true;
            })
            ->andReturn(json_encode([[
                'pertanyaan' => 'Tuliskan rumus abc.',
                'referensi' => '$x = \\frac{-b \\pm \\sqrt{b^2-4ac}}{2a}$',
            ]]));
    });

    app(KuisGeneratorService::class)->generateEssai($pertemuan, 1, null);

    expect($system)->toContain('LaTeX');
});

it('prompt generate tugas remedial meminta rumus ditulis dalam LaTeX', function () {
    $siswa = User::factory()->verified()->create();

    $prediksi = PrediksiAi::create([
        'siswa_id' => $siswa->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 0.87,
        'feature_vector' => [2, 25, 60, 1, 1, 70, 65, 2, 4, 90],
    ]);

    $remedial = MateriRemedial::create([
        'siswa_id' => $siswa->id,
        'prediksi_ai_id' => $prediksi->id,
        'topik_lemah' => 'Rumus abc',
        'ringkasan' => 'Akar persamaan kuadrat dihitung dengan rumus abc.',
        'latihan' => '',
        'provider_llm' => 'ollama',
        'status' => 'belum_dibaca',
        'generated_at' => now(),
    ]);

    $system = null;

    $this->mock(AIProviderService::class, function ($mock) use (&$system) {
        $mock->shouldReceive('sendLocalPreferred')
            ->atLeast()->once()
            ->withArgs(function ($prompt, $history, $sys) use (&$system) {
                $system = $sys;

                return true;
            })
            ->andReturn(json_encode([
                'tujuan' => [['kode' => 'C2', 'level' => 'Memahami', 'tujuan' => 'Menjelaskan rumus abc.']],
                'soal' => [['pertanyaan' => 'Tuliskan rumus abc.', 'referensi' => 'x = ...', 'bloom' => 'C2']],
            ]));
    });

    app(RemedialTugasService::class)->generateTugas($remedial);

    expect($system)->toContain('LaTeX');
});

it('komponen math menyimpan teks mentah agar bisa dirender KaTeX di klien', function () {
    $html = Blade::render('<x-math :value="$teks" />', ['teks' => 'Rumus: $x^2$']);

    expect($html)->toContain('class="math-content"')
        ->toContain('data-raw="Rumus: $x^2$"');
});
