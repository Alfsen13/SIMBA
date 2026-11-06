<?php

use App\Jobs\PeriksaJawabanTugasAI;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\PenilaianAIService;
use App\Services\Tugas\TugasPresenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create([
        'guru_id' => $this->guru->id,
    ]);
    $this->pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
});

test('guru dapat membuat tugas esai dengan soal', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.tugas.store'), [
            'judul' => 'Tugas Esai Fotosintesis',
            'deskripsi' => 'Jawab dengan benar.',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'tipe' => 'essai',
            'soal' => json_encode([
                ['pertanyaan' => 'Jelaskan proses fotosintesis!', 'referensi' => 'Menyebutkan cahaya, klorofil, dan hasil glukosa.'],
            ]),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tugas', [
        'kelas_id' => $this->kelas->id,
        'tipe' => 'essai',
    ]);

    $tugas = Tugas::where('kelas_id', $this->kelas->id)->first();
    expect($tugas->soal)->toBeArray()
        ->and($tugas->soal[0]['pertanyaan'])->toBe('Jelaskan proses fotosintesis!');
});

test('tugas esai wajib memiliki soal json', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.tugas.store'), [
            'judul' => 'Tugas esai tanpa soal',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'tipe' => 'essai',
        ])
        ->assertSessionHasErrors('soal');
});

test('siswa dapat mengumpulkan jawaban esai dan memicu penilaian AI', function () {
    Queue::fake();

    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $tugas = Tugas::create([
        'judul' => 'Esai Fotosintesis',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [
            ['pertanyaan' => 'Jelaskan proses fotosintesis!', 'referensi' => 'Cahaya, klorofil, glukosa.'],
        ],
    ]);

    $this->actingAs($siswa)
        ->post(route('pelajar.tugas.submit', $tugas), [
            'jawaban' => ['0' => 'Fotosintesis memakai cahaya dan klorofil untuk membuat glukosa.'],
        ])
        ->assertRedirect();

    $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)
        ->where('siswa_id', $siswa->id)->first();

    expect($pengumpulan)->not->toBeNull()
        ->and($pengumpulan->jawaban[0])->toBe('Fotosintesis memakai cahaya dan klorofil untuk membuat glukosa.');

    Queue::assertPushed(PeriksaJawabanTugasAI::class, function (PeriksaJawabanTugasAI $job) use ($pengumpulan) {
        return $job->pengumpulanId === $pengumpulan->id;
    });
});

test('tugas timer yang baru dimulai tetap dapat dikumpulkan dan belum berstatus selesai', function () {
    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);
    $tugas = Tugas::factory()->denganTimer()->create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
    ]);

    $this->actingAs($siswa)
        ->post(route('pelajar.tugas.mulai', $tugas))
        ->assertRedirect(route('pelajar.tugas.show', $tugas));

    $this->get(route('pelajar.tugas.show', $tugas))
        ->assertOk()
        ->assertSee('name="file"', false)
        ->assertDontSee(__('siswa.tugas.sudah_dikumpulkan'));

    $this->get(route('pelajar.tugas.index'))
        ->assertOk()
        ->assertSee(__('siswa.shared.status_belum'))
        ->assertDontSee(__('siswa.shared.status_dikumpulkan'));

    $this->get(route('pelajar.dashboard'))
        ->assertOk()
        ->assertSee($tugas->judul)
        ->assertDontSee(__('siswa.shared.status_selesai'));
});

test('tugas timer menunjukkan status dikumpulkan setelah file benar-benar dikirim', function () {
    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);
    $tugas = Tugas::factory()->denganTimer()->create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
    ]);

    $this->actingAs($siswa)
        ->post(route('pelajar.tugas.mulai', $tugas))
        ->assertRedirect();

    $this->post(route('pelajar.tugas.submit', $tugas), [
        'file' => UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $this->get(route('pelajar.tugas.show', $tugas))
        ->assertOk()
        ->assertSee(__('siswa.tugas.sudah_dikumpulkan'))
        ->assertDontSee('name="file"', false);

    $this->get(route('pelajar.tugas.index'))
        ->assertOk()
        ->assertSee(__('siswa.shared.status_dikumpulkan'));
});

test('upload tugas menolak ekstensi yang tidak didukung oleh formulir', function () {
    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);
    $tugas = Tugas::create([
        'judul' => 'Upload Tugas',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'upload',
    ]);

    $this->actingAs($siswa)
        ->post(route('pelajar.tugas.submit', $tugas), [
            'file' => UploadedFile::fake()->create('program.exe', 10, 'application/octet-stream'),
        ])
        ->assertSessionHasErrors('file');

    expect(PengumpulanTugas::where('tugas_id', $tugas->id)->exists())->toBeFalse();
});

test('penilaian ai menyimpan hasil benar mendekati salah dan skor saran', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            ['indeks' => 0, 'status' => 'benar', 'skor' => 90, 'alasan' => 'Jawaban lengkap dan tepat.'],
            ['indeks' => 1, 'status' => 'mendekati', 'skor' => 60, 'alasan' => 'Kurang detail.'],
        ]));
    });

    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $tugas = Tugas::create([
        'judul' => 'Esai Ganda',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [
            ['pertanyaan' => 'Soal 1?'],
            ['pertanyaan' => 'Soal 2?'],
        ],
    ]);

    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['Jawaban 1.', 'Jawaban 2.'],
    ]);

    app(PenilaianAIService::class)->nilai($pengumpulan);

    $pengumpulan->refresh();

    expect($pengumpulan->nilai_ai)->toBe(75)
        ->and($pengumpulan->hasil_ai[0]['status'])->toBe('benar')
        ->and($pengumpulan->hasil_ai[1]['status'])->toBe('mendekati')
        ->and($pengumpulan->koreksi_ai_at)->not->toBeNull();
});

test('penilaian ai meneruskan kegagalan provider agar job dapat mencoba ulang', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andThrow(new RuntimeException('Provider tidak tersedia.'));
    });

    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);
    $tugas = Tugas::create([
        'judul' => 'Esai Gagal',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Jelaskan konsepnya.']],
    ]);
    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['Jawaban siswa.'],
    ]);

    expect(fn () => app(PenilaianAIService::class)->nilai($pengumpulan))
        ->toThrow(RuntimeException::class, 'Provider tidak tersedia.');

    $job = new PeriksaJawabanTugasAI($pengumpulan->id);
    expect($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([30, 120]);

    $job->failed(new RuntimeException('Provider tidak tersedia.'));

    expect($pengumpulan->fresh()->koreksi_ai_gagal)->toBeTrue();

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.penilaian', $tugas))
        ->assertOk()
        ->assertSee('Pemeriksaan AI gagal setelah beberapa percobaan.');

    $this->actingAs($siswa)
        ->get(route('pelajar.tugas.show', $tugas))
        ->assertOk()
        ->assertSee('Pemeriksaan AI gagal setelah beberapa percobaan.');
});

test('penilaian ai menganggap keluaran yang tidak valid sebagai kegagalan job', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn('bukan json');
    });

    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);
    $tugas = Tugas::create([
        'judul' => 'Esai Respons Tidak Valid',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Jelaskan konsepnya.']],
    ]);
    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['Jawaban siswa.'],
    ]);

    expect(fn () => app(PenilaianAIService::class)->nilai($pengumpulan))
        ->toThrow(UnexpectedValueException::class);
});

test('indeks 1-based dari AI tetap dipetakan ke soal yang benar', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            ['indeks' => 1, 'status' => 'benar', 'skor' => 90, 'alasan' => 'Tepat.'],
            ['indeks' => 2, 'status' => 'salah', 'skor' => 20, 'alasan' => 'Keliru.'],
        ]));
    });

    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $tugas = Tugas::create([
        'judul' => 'Esai 1-based',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [
            ['pertanyaan' => 'Soal 1?'],
            ['pertanyaan' => 'Soal 2?'],
        ],
    ]);

    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['Jawaban 1.', 'Jawaban 2.'],
    ]);

    app(PenilaianAIService::class)->nilai($pengumpulan);
    $pengumpulan->refresh();

    expect($pengumpulan->hasil_ai[0]['indeks'])->toBe(0)
        ->and($pengumpulan->hasil_ai[0]['status'])->toBe('benar')
        ->and($pengumpulan->hasil_ai[1]['indeks'])->toBe(1)
        ->and($pengumpulan->hasil_ai[1]['status'])->toBe('salah');

    $baris = app(TugasPresenter::class)->barisPenilaian($tugas->soal, $pengumpulan);

    expect($baris[0]['label'])->toBe('Benar')
        ->and($baris[1]['label'])->toBe('Salah');
});

test('jawaban kosong tidak menggeser pemetaan hasil AI', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            ['indeks' => 0, 'status' => 'benar', 'skor' => 80, 'alasan' => 'Tepat.'],
        ]));
    });

    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $tugas = Tugas::create([
        'judul' => 'Esai Kosong',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [
            ['pertanyaan' => 'Soal 1?'],
            ['pertanyaan' => 'Soal 2?'],
        ],
    ]);

    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['', 'Jawaban 2.'],
    ]);

    app(PenilaianAIService::class)->nilai($pengumpulan);
    $pengumpulan->refresh();

    expect($pengumpulan->hasil_ai)->toHaveCount(1)
        ->and($pengumpulan->hasil_ai[0]['indeks'])->toBe(1);

    $baris = app(TugasPresenter::class)->barisPenilaian($tugas->soal, $pengumpulan);

    expect($baris[0]['hasil'])->toBeNull()
        ->and($baris[1]['hasil'])->not->toBeNull()
        ->and($baris[1]['label'])->toBe('Benar');
});

test('halaman penilaian menampilkan hasil AI untuk tugas esai', function () {
    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $tugas = Tugas::create([
        'judul' => 'Esai',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Soal 1?']],
    ]);

    PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['Jawaban 1.'],
        'nilai_ai' => 90,
        'hasil_ai' => [['indeks' => 0, 'status' => 'benar', 'skor' => 90, 'alasan' => 'Tepat.']],
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.penilaian', $tugas))
        ->assertOk()
        ->assertSee('Nilai AI: 90');
});

test('penjadwalan ulang pemeriksaan menghapus status gagal sebelumnya', function () {
    Queue::fake();

    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);
    $tugas = Tugas::create([
        'judul' => 'Esai Pemeriksaan Ulang',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [['pertanyaan' => 'Jelaskan konsepnya.']],
    ]);
    $pengumpulan = PengumpulanTugas::create([
        'tugas_id' => $tugas->id,
        'siswa_id' => $siswa->id,
        'jawaban' => ['Jawaban siswa.'],
        'koreksi_ai_gagal' => true,
    ]);

    $this->actingAs($this->guru)
        ->post(route('pengajar.tugas.periksa-ai', $tugas))
        ->assertRedirect();

    expect($pengumpulan->fresh()->koreksi_ai_gagal)->toBeFalse();
    Queue::assertPushed(PeriksaJawabanTugasAI::class);
});

test('periksa-ai hanya berlaku untuk tugas esai', function () {
    Queue::fake();

    $tugasUpload = Tugas::create([
        'judul' => 'Upload',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'upload',
    ]);

    $this->actingAs($this->guru)
        ->post(route('pengajar.tugas.periksa-ai', $tugasUpload))
        ->assertRedirect();

    Queue::assertNotPushed(PeriksaJawabanTugasAI::class);
});

test('siswa kuis membandingkan jawaban numeric dengan kunci index', function () {
    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $kuis = Kuis::create([
        'judul' => 'Kuis 1',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
        'durasi_menit' => 5,
        'is_aktif' => true,
        'soal' => [
            ['pertanyaan' => 'Hukum Ohm?', 'pilihan' => ['A', 'B', 'C', 'D'], 'jawaban' => 1],
            ['pertanyaan' => 'Satuan arus?', 'pilihan' => ['Volt', 'Ampere', 'Ohm', 'Watt'], 'jawaban' => 0],
        ],
    ]);

    $this->actingAs($siswa)
        ->post(route('pelajar.kuis.submit', $kuis), [
            'jawaban' => ['0' => '1', '1' => '0'],
        ])
        ->assertRedirect(route('pelajar.kuis.hasil', $kuis));

    $this->assertDatabaseHas('kuis_hasil', [
        'kuis_id' => $kuis->id,
        'siswa_id' => $siswa->id,
        'skor' => 100,
    ]);
});
