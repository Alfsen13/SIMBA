<?php

use App\Jobs\GenerateRemedialAI;
use App\Jobs\NilaiRemedialTugasAI;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\MateriRemedial;
use App\Models\Notifikasi;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\RemedialService;
use App\Services\RemedialTugasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create([
        'guru_id' => $this->guru->id,
    ]);
    $this->siswa = User::factory()->verified()->create();
    $this->siswa->kelasDiikuti()->attach($this->kelas->id);

    $this->prediksi = PrediksiAi::create([
        'siswa_id' => $this->siswa->id,
        'hasil' => 'berisiko_tinggi',
        'skor_risiko' => 0.87,
        'feature_vector' => [2, 25, 60, 1, 1, 70, 65, 2, 4, 90],
    ]);

    $this->remedial = MateriRemedial::create([
        'siswa_id' => $this->siswa->id,
        'prediksi_ai_id' => $this->prediksi->id,
        'topik_lemah' => 'Fotosintesis',
        'ringkasan' => 'Fotosintesis mengubah cahaya, air, dan CO2 menjadi glukosa dan oksigen.',
        'latihan' => '',
        'provider_llm' => 'ollama',
        'status' => 'belum_dibaca',
        'generated_at' => now(),
    ]);
});

function remedialGenerateJson(): string
{
    return json_encode([
        'tujuan' => [
            ['kode' => 'C2', 'level' => 'Memahami', 'tujuan' => 'Siswa dapat menjelaskan proses fotosintesis.'],
            ['kode' => 'C3', 'level' => 'Mengaplikasikan', 'tujuan' => 'Siswa dapat menghitung laju fotosintesis.'],
        ],
        'soal' => [
            ['pertanyaan' => 'Jelaskan peran cahaya dalam fotosintesis.', 'referensi' => 'Cahaya sebagai sumber energi.', 'bloom' => 'C2'],
            ['pertanyaan' => 'Enzim manakah yang terlibat dan apa fungsinya?', 'referensi' => 'Rubisco, RuBP.', 'bloom' => 'C3'],
            ['pertanyaan' => 'Bandingkan fotosintesis C3 dan C4.', 'referensi' => 'Fiksasi CO2 berbeda.', 'bloom' => 'C4'],
        ],
    ]);
}

function remedialNilaiJson(): string
{
    return json_encode([
        'per_soal' => [
            ['indeks' => 0, 'status' => 'benar', 'skor' => 95, 'alasan' => 'Jawaban tepat dan sesuai referensi.', 'bloom' => 'C2'],
            ['indeks' => 1, 'status' => 'benar', 'skor' => 85, 'alasan' => 'Sebutan enzim kurang lengkap.', 'bloom' => 'C3'],
            ['indeks' => 2, 'status' => 'mendekati', 'skor' => 60, 'alasan' => 'Perbandingan belum mendalam.', 'bloom' => 'C4'],
        ],
        'mastery' => [
            'tercapai' => true,
            'alasan' => 'Rata-rata skor 80 dan mayoritas soal benar pada level target.',
        ],
    ]);
}

function remedialBungkusArray(string $json): string
{
    // Bentuk yang benar-benar pernah dikembalikan qwen2.5:7b: seluruh objek
    // dibungkus array, plus prosa penutup setelah fence.
    return "```json\n".json_encode([json_decode($json, true)])."\n```\n\nSemoga membantu, silakan dicek.";
}

test('generateTugas meminta AI dengan konteks Taksonomi Bloom dan menyimpan tujuan serta soal', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($prompt, $context, $system) {
                return str_contains($system, 'Taksonomi Bloom')
                    && str_contains($prompt, 'C1-C6')
                    && str_contains($prompt, 'bloom')
                    && str_contains($prompt, 'Fotosintesis');
            })
            ->andReturn(remedialGenerateJson());
    });

    $result = app(RemedialTugasService::class)->generateTugas($this->remedial);
    $this->remedial->refresh();

    expect($result)->toBeTrue()
        ->and($this->remedial->bloom_tujuan)->toBeArray()
        ->and($this->remedial->bloom_tujuan[0]['kode'])->toBe('C2')
        ->and($this->remedial->tugas_remedial)->toHaveCount(3)
        ->and($this->remedial->tugas_remedial[1]['bloom'])->toBe('C3');
});

test('generateTugas menoleransi referensi berbentuk array dari AI', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->andReturn(json_encode([
                'tujuan' => [
                    ['kode' => 'C2', 'level' => 'Memahami', 'tujuan' => 'Siswa dapat menjelaskan fotosintesis.'],
                ],
                'soal' => [
                    ['pertanyaan' => 'Apa itu fotosintesis?', 'referensi' => ['cahaya', 'klorofil', 'glukosa'], 'bloom' => 'C2'],
                ],
            ]));
    });

    $ok = app(RemedialTugasService::class)->generateTugas($this->remedial);

    expect($ok)->toBeTrue()
        ->and($this->remedial->refresh()->tugas_remedial[0]['referensi'])->toBe('cahaya, klorofil, glukosa');
});

test('generateTugas menahan level remedial di rentang C1-C5', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->andReturn(json_encode([
                'tujuan' => [
                    ['kode' => 'C1', 'level' => 'Mengingat', 'tujuan' => 'Siswa menyebutTahapan fotosintesis.'],
                ],
                'soal' => [
                    [
                        'pertanyaan' => 'Buatlah karya orisinal tentang siklus air.',
                        'referensi' => 'Karya visual bermateri siklus air.',
                        'bloom' => 'C6',
                    ],
                    [
                        'pertanyaan' => 'Sebutkan Pigmen penyerap energi cahaya.',
                        'referensi' => 'Klorofil.',
                        'bloom' => 'C6',
                    ],
                ],
            ]));
    });

    app(RemedialTugasService::class)->generateTugas($this->remedial);

    $soal = $this->remedial->refresh()->tugas_remedial;

    expect($soal[0]['bloom'])->toBe('C5')
        ->and($soal[1]['bloom'])->toBe('C1');
});

test('generateTugas tidak menimpa tugas yang sudah ada', function () {
    $this->remedial->update([
        'tugas_remedial' => [['pertanyaan' => 'Soal lama.', 'referensi' => '', 'bloom' => 'C1']],
    ]);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldNotReceive('sendLocalPreferred');
    });

    app(RemedialTugasService::class)->generateTugas($this->remedial->refresh());

    expect($this->remedial->refresh()->tugas_remedial[0]['pertanyaan'])->toBe('Soal lama.');
});

test('nilai menyimpan hasil, skor, level Bloom, dan verdict mastery', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'],
        'status' => 'selesai',
    ]);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($prompt, $context, $system) {
                return str_contains($system, 'Taksonomi Bloom')
                    && str_contains($prompt, 'mastery')
                    && str_contains($prompt, 'benar');
            })
            ->andReturn(remedialNilaiJson());
    });

    $result = app(RemedialTugasService::class)->nilai($this->remedial->refresh());
    $this->remedial->refresh();

    expect($result)->toBeTrue()
        ->and($this->remedial->bloom_tercapai)->toBeTrue()
        ->and($this->remedial->mastery_alasan)->toContain('Rata-rata skor 80')
        ->and($this->remedial->dinilai_at)->not->toBeNull()
        ->and($this->remedial->status)->toBe('selesai')
        ->and($this->remedial->hasil_remedial[0]['status'])->toBe('benar')
        ->and($this->remedial->hasil_remedial[0]['bloom'])->toBe('C2');
});

test('nilai mengabaikan jawaban kosong', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => ['', 'Jawaban 2.', ''],
    ]);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(fn ($prompt) => str_contains($prompt, 'Jawaban 2.') && ! str_contains($prompt, 'Jawaban 1.'))
            ->andReturn(remedialNilaiJson());
    });

    app(RemedialTugasService::class)->nilai($this->remedial->refresh());
});

test('generateTugas mencoba lagi saat output pertama tidak bisa di-parse', function () {
    // Regresi: sebelumnya satu output rusak langsung `return false` tanpa log,
    // dan pada qwen2.5:7b itu terjadi sekitar separuh waktu -> tugas remedial
    // sering tidak pernah punya soal.
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->twice()
            ->andReturn('maaf, saya tidak bisa membantu', remedialGenerateJson());
    });

    $ok = app(RemedialTugasService::class)->generateTugas($this->remedial->refresh());
    $this->remedial->refresh();

    expect($ok)->toBeTrue()
        ->and($this->remedial->tugas_remedial)->toHaveCount(3)
        ->and($this->remedial->bloom_tujuan)->not->toBeNull();
});

test('generateTugas menyerah setelah semua percobaan gagal dan mencatat log', function () {
    Log::spy();

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->times(3)
            ->andReturn('bukan json');
    });

    $ok = app(RemedialTugasService::class)->generateTugas($this->remedial->refresh());
    $this->remedial->refresh();

    expect($ok)->toBeFalse()
        ->and($this->remedial->tugas_remedial)->toBeNull();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn ($m) => str_contains($m, 'tanpa soal'))
        ->once();
});

test('nilai mencoba lagi saat output penilaian tidak bisa di-parse', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'],
        'status' => 'selesai',
    ]);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->twice()
            ->andReturn('{rusak', remedialNilaiJson());
    });

    $ok = app(RemedialTugasService::class)->nilai($this->remedial->refresh());
    $this->remedial->refresh();

    expect($ok)->toBeTrue()
        ->and($this->remedial->hasil_remedial)->toHaveCount(3)
        ->and($this->remedial->bloom_tercapai)->toBeTrue();
});

test('generateTugas toleran saat AI membungkus objek di dalam array dan menambah prosa', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->andReturn(remedialBungkusArray(remedialGenerateJson()));
    });

    $ok = app(RemedialTugasService::class)->generateTugas($this->remedial->refresh());
    $this->remedial->refresh();

    expect($ok)->toBeTrue()
        ->and($this->remedial->tugas_remedial)->toHaveCount(3);
});

test('generateTugas toleran saat tujuan dikirim sebagai objek tunggal', function () {
    $json = json_decode(remedialGenerateJson(), true);
    $json['tujuan'] = $json['tujuan'][0];

    $this->mock(AIProviderService::class, function ($mock) use ($json) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->andReturn(json_encode($json));
    });

    $ok = app(RemedialTugasService::class)->generateTugas($this->remedial->refresh());
    $this->remedial->refresh();

    expect($ok)->toBeTrue()
        ->and($this->remedial->bloom_tujuan)->toHaveCount(1)
        ->and($this->remedial->tugas_remedial)->toHaveCount(3);
});

test('nilai toleran saat AI membungkus objek di dalam array', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'],
        'status' => 'selesai',
    ]);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->andReturn(remedialBungkusArray(remedialNilaiJson()));
    });

    $ok = app(RemedialTugasService::class)->nilai($this->remedial->refresh());
    $this->remedial->refresh();

    expect($ok)->toBeTrue()
        ->and($this->remedial->hasil_remedial)->toHaveCount(3);
});

test('GenerateRemedialAI setelah membuat materi juga membuatkan tugas remedial', function () {
    // Pastikan job menguji jalur pembuatan, bukan pemulihan remedial lama.
    MateriRemedial::where('siswa_id', $this->siswa->id)->delete();

    $remedial = new MateriRemedial([
        'siswa_id' => $this->siswa->id,
        'prediksi_ai_id' => $this->prediksi->id,
        'topik_lemah' => 'Fotosintesis',
        'ringkasan' => 'Ringkasan materi foto sintesis.',
        'latihan' => 'Latihan.',
        'provider_llm' => 'ollama',
        'status' => 'belum_dibaca',
    ]);

    $this->mock(RemedialService::class, function ($mock) use ($remedial) {
        $mock->shouldReceive('generateForPrediksi')->once()->andReturn($remedial);
        $mock->shouldReceive('alasanDilewati')->once()->andReturnNull();
    });

    $this->mock(RemedialTugasService::class, function ($mock) use ($remedial) {
        $mock->shouldReceive('generateTugas')->once()->with($remedial)->andReturn(true);
    });

    (new GenerateRemedialAI($this->siswa->id, $this->prediksi->id))->handle(app(RemedialService::class));
});

test('job remedial dapat melanjutkan pembuatan tugas setelah percobaan gagal', function () {
    $percobaan = 0;
    $this->mock(RemedialTugasService::class, function ($mock) use (&$percobaan) {
        $mock->shouldReceive('generateTugas')
            ->twice()
            ->andReturnUsing(function (MateriRemedial $remedial) use (&$percobaan) {
                $percobaan++;

                if ($percobaan === 1) {
                    return false;
                }

                $remedial->update(['tugas_remedial' => [['pertanyaan' => 'Latihan remedial']]]);

                return true;
            });
    });

    $job = new GenerateRemedialAI($this->siswa->id, $this->prediksi->id);

    expect(fn () => $job->handle(app(RemedialService::class)))
        ->toThrow(RuntimeException::class, 'Gagal membuat tugas untuk remedial');

    $job->handle(app(RemedialService::class));

    expect($this->remedial->fresh()->tugas_remedial)->toBe([['pertanyaan' => 'Latihan remedial']]);
});

test('generateForPrediksi membuat baris remedial dan notifikasi tanpa error skema', function () {
    $this->remedial->delete();

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('send')
            ->once()
            ->andReturn('{"ringkasan":"Fotosintesis adalah proses mengubah cahaya menjadi glukosa.","latihan":"1. Jelaskan peran klorofil."}');
    });

    $hasil = app(RemedialService::class)->generateForPrediksi($this->prediksi->refresh());

    expect($hasil)->toBeInstanceOf(MateriRemedial::class)
        ->and($hasil->siswa_id)->toBe($this->siswa->id)
        ->and($hasil->ringkasan)->toContain('Fotosintesis')
        ->and($hasil->latihan)->toContain('klorofil')
        ->and($hasil->status)->toBe('belum_dibaca');

    $notifikasi = Notifikasi::where('user_id', $this->siswa->id)
        ->where('tipe', 'remedial')
        ->latest()
        ->first();

    expect($notifikasi)->not->toBeNull()
        ->and($notifikasi->link)->toBe("/pelajar/remedial/{$hasil->id}");
});

test('generateForPrediksi mengisi topik_lemah dari nilai|kuis nyata', function () {
    $this->remedial->delete();

    $pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan Fotosintesis',
        'urutan' => 1,
    ]);

    $kuis = Kuis::create([
        'judul' => 'Kuis Fotosintesis',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'soal' => [['pertanyaan' => 'Fungsi klorofil?', 'pilihan' => ['Menyerap cahaya', 'Menyerap air'], 'jawaban' => 0]],
    ]);
    KuisHasil::create([
        'kuis_id' => $kuis->id,
        'siswa_id' => $this->siswa->id,
        'skor' => 25,
        'jawaban_siswa' => [0],
    ]);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('send')
            ->once()
            ->andReturn('{"ringkasan":"Ringkasan.","latihan":"Latihan."}');
    });

    $hasil = app(RemedialService::class)->generateForPrediksi($this->prediksi->refresh());

    expect($hasil->topik_lemah)->toContain('Pertemuan Fotosintesis')
        ->and($hasil->topik_lemah)->toContain('25');
});

test('generateForPrediksi meneruskan kegagalan AI agar job dapat mencoba ulang', function () {
    $this->remedial->delete();

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('send')->once()->andThrow(new RuntimeException('AI mati'));
        $mock->shouldReceive('sendLocalPreferred')->once()->andThrow(new RuntimeException('Ollama mati'));
    });

    expect(fn () => app(RemedialService::class)->generateForPrediksi($this->prediksi->refresh()))
        ->toThrow(RuntimeException::class, 'Semua provider AI gagal membuat materi remedial.');

    expect(MateriRemedial::where('siswa_id', $this->siswa->id)->count())->toBe(0);

    $job = new GenerateRemedialAI($this->siswa->id, $this->prediksi->id);
    expect($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([30, 120]);
});

test('siswa mengumpulkan jawaban remedial dan memicu penilaian AI', function () {
    Queue::fake();

    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.remedial.submit', $this->remedial), [
            'jawaban' => ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'],
        ])
        ->assertRedirect(route('pelajar.remedial.show', $this->remedial));

    $this->remedial->refresh();

    expect($this->remedial->jawaban_remedial)->toHaveCount(3)
        ->and($this->remedial->jawaban_remedial[0])->toBe('Jawaban 1.')
        ->and($this->remedial->status)->toBe('dibaca')
        ->and($this->remedial->bloom_tercapai)->toBeNull();

    Queue::assertPushed(NilaiRemedialTugasAI::class, function (NilaiRemedialTugasAI $job) {
        return $job->remedialId === $this->remedial->id;
    });
});

test('siswa dapat meminta penilaian ulang jika hasil AI belum tersedia', function () {
    Queue::fake();

    $answers = ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'];
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => $answers,
        'status' => 'dibaca',
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.remedial.submit', $this->remedial), ['jawaban' => $answers])
        ->assertRedirect(route('pelajar.remedial.show', $this->remedial));

    Queue::assertPushed(NilaiRemedialTugasAI::class, fn (NilaiRemedialTugasAI $job) => $job->remedialId === $this->remedial->id);
});

test('siswa tidak dapat mengubah jawaban saat penilaian remedial masih menunggu', function () {
    Queue::fake();

    $answers = ['Jawaban asli.', 'Jawaban 2.', 'Jawaban 3.'];
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => $answers,
        'status' => 'dibaca',
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.remedial.submit', $this->remedial), [
            'jawaban' => ['Jawaban diubah.', 'Jawaban 2.', 'Jawaban 3.'],
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($this->remedial->refresh()->jawaban_remedial)->toBe($answers);
    Queue::assertNothingPushed();
});

test('job penilaian melempar kegagalan agar antrean dapat mencoba ulang', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'],
        'status' => 'dibaca',
    ]);

    $service = $this->mock(RemedialTugasService::class, function ($mock) {
        $mock->shouldReceive('nilai')->once()->andReturnFalse();
    });

    expect(fn () => (new NilaiRemedialTugasAI($this->remedial->id))->handle($service))
        ->toThrow(RuntimeException::class);

    expect($this->remedial->refresh()->status)->toBe('dibaca');
});

test('kegagalan akhir penilaian tersimpan dan siswa dapat mengirim ulang penilaian', function () {
    Queue::fake();
    $answers = ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'];
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => $answers,
        'status' => 'dibaca',
    ]);

    (new NilaiRemedialTugasAI($this->remedial->id))->failed(new RuntimeException('Provider AI gagal'));

    $this->actingAs($this->siswa)
        ->get(route('pelajar.remedial.show', $this->remedial))
        ->assertOk()
        ->assertSee(__('siswa.remedial.penilaian_remedial_gagal'));

    $this->actingAs($this->siswa)
        ->post(route('pelajar.remedial.submit', $this->remedial), ['jawaban' => $answers])
        ->assertRedirect(route('pelajar.remedial.show', $this->remedial));

    expect($this->remedial->refresh()->penilaian_gagal_at)->toBeNull();
    Queue::assertPushed(NilaiRemedialTugasAI::class);
});

test('siswa dapat mereset jawaban setelah kegagalan akhir penilaian remedial', function () {
    $this->remedial->update([
        'jawaban_remedial' => ['Jawaban 1.', 'Jawaban 2.', 'Jawaban 3.'],
        'status' => 'dibaca',
    ]);

    (new NilaiRemedialTugasAI($this->remedial->id))->failed(new RuntimeException('Provider AI gagal'));

    $this->actingAs($this->siswa)
        ->post(route('pelajar.remedial.reset', $this->remedial))
        ->assertRedirect();

    expect($this->remedial->refresh()->jawaban_remedial)->toBeNull()
        ->and($this->remedial->penilaian_gagal_at)->toBeNull();
});

test('siswa lain tidak dapat mengumpulkan jawaban remedial orang lain', function () {
    $siswaLain = User::factory()->verified()->create();

    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
    ]);

    $this->actingAs($siswaLain)
        ->post(route('pelajar.remedial.submit', $this->remedial), [
            'jawaban' => ['A', 'B', 'C'],
        ])
        ->assertForbidden();

    expect($this->remedial->refresh()->jawaban_remedial)->toBeNull();
});

test('jawaban remedial wajib diisi untuk setiap soal', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.remedial.submit', $this->remedial), [
            'jawaban' => ['', 'Jawaban 2.', ''],
        ])
        ->assertSessionHasErrors('jawaban.0');
});

test('siswa dapat mereset dan mengerjakan ulang remedial', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => ['A', 'B', 'C'],
        'hasil_remedial' => json_decode(remedialNilaiJson(), true)['per_soal'],
        'bloom_tercapai' => true,
        'mastery_alasan' => 'Mastery.',
        'dinilai_at' => now(),
        'status' => 'selesai',
    ]);

    $this->actingAs($this->siswa)
        ->post(route('pelajar.remedial.reset', $this->remedial))
        ->assertRedirect();

    $this->remedial->refresh();

    expect($this->remedial->jawaban_remedial)->toBeNull()
        ->and($this->remedial->hasil_remedial)->toBeNull()
        ->and($this->remedial->bloom_tercapai)->toBeNull()
        ->and($this->remedial->dinilai_at)->toBeNull()
        ->and($this->remedial->status)->toBe('dibaca');
});

test('guru dapat melihat hasil penilaian remedial siswa', function () {
    $this->remedial->update([
        'tugas_remedial' => json_decode(remedialGenerateJson(), true)['soal'],
        'jawaban_remedial' => ['A', 'B', 'C'],
        'hasil_remedial' => json_decode(remedialNilaiJson(), true)['per_soal'],
        'bloom_tercapai' => true,
        'mastery_alasan' => 'Rata-rata skor 80 dan mayoritas benar.',
        'dinilai_at' => now(),
        'status' => 'selesai',
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.remedial.show', $this->remedial))
        ->assertOk()
        ->assertSee('sudah mencapai tujuan pembelajaran')
        ->assertSee('Konteks Taksonomi Bloom')
        ->assertSee('Rata-rata skor 80');
});

test('siswa melihat form tugas remedial dan badge level Bloom', function () {
    $this->remedial->update([
        'bloom_tujuan' => [
            ['kode' => 'C2', 'level' => 'Memahami', 'tujuan' => 'Siswa dapat menjelaskan fotosintesis.'],
        ],
        'tugas_remedial' => [['pertanyaan' => 'Jelaskan fotosintesis!', 'referensi' => '', 'bloom' => 'C2']],
    ]);

    $this->actingAs($this->siswa)
        ->get(route('pelajar.remedial.show', $this->remedial))
        ->assertOk()
        ->assertSee('Tujuan Pembelajaran')
        ->assertSee('Level C2')
        ->assertSee('Kumpulkan Jawaban');
});
