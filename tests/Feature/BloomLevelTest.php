<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\BloomTaxonomyService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $this->pertemuan = Pertemuan::create([
        'kelas_id' => $this->kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);
    $this->materi = Materi::factory()->create([
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
    ]);
    MateriBabSummary::create([
        'materi_id' => $this->materi->id,
        'ringkasan' => 'Fotosintesis mengubah cahaya, air, dan karbon dioksida menjadi glukosa dan oksigen.',
    ]);
});

test('generate soal kuis menyimpan level bloom per soal', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            [
                'pertanyaan' => 'Analisis faktor yang memengaruhi laju fotosintesis.',
                'pilihan' => ['Intensitas cahaya', 'Warna dusk', 'Ukuran pots', 'Jenis tanah'],
                'jawaban' => 0,
                'bloom' => 'C1',
            ],
        ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $this->pertemuan->id,
            'jumlah' => 1,
            'kesulitan' => 'sedang',
        ])
        ->assertOk()
        ->assertJsonPath('soal.0.bloom', 'C4');
});
test('level bloom dari AI dikunci ke rentang kesulitan yang diminta', function (string $kesulitan, string $harapan) {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            [
                'pertanyaan' => 'Buat karya orisinal tentang siklus air.',
                'pilihan' => ['Poster', 'Puisi', 'Peta', 'Lagu'],
                'jawaban' => 0,
                'bloom' => 'C6',
            ],
        ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $this->pertemuan->id,
            'jumlah' => 1,
            'kesulitan' => $kesulitan,
        ])
        ->assertOk()
        ->assertJsonPath('soal.0.bloom', $harapan);
})->with([
    ['mudah', 'C2'],
    ['sedang', 'C4'],
    ['sulit', 'C5'],
]);

test('generate soal esai memakai rentang default C2-C4', function () {
    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            [
                'pertanyaan' => 'Buat poster orisinal tentang siklus air.',
                'referensi' => 'Poster sederhana bermateri siklus air.',
                'bloom' => 'C6',
            ],
            [
                'pertanyaan' => 'Analisislah pengaruh suhu terhadap laju reaksi kimia.',
                'referensi' => 'Suhu memengaruhi collisions.',
                'bloom' => 'C4',
            ],
        ]));
    });

    $response = $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $this->pertemuan->id,
            'tipe' => 'essai',
            'jumlah' => 2,
            'kesulitan' => 'sedang',
        ])
        ->assertOk();

    $soal = $response->json('soal');
    $rentang = app(BloomTaxonomyService::class)->allowedRange(null);

    expect($soal[0]['bloom'])->toBeIn($rentang)
        ->and($soal[1]['bloom'])->toBeIn($rentang)
        ->and($soal[0]['bloom'])->toBe('C4')
        ->and($soal[1]['bloom'])->toBe('C4');
});

test('level bloom tersimpan di dalam JSON soal kuis dan tugas', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.tugas.store'), [
            'judul' => 'Esai Bloom',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'tipe' => 'essai',
            'soal' => json_encode([
                ['pertanyaan' => 'Jelaskan fungsi stomata.', 'referensi' => 'Pertukaran gas.', 'bloom' => 'C2'],
            ]),
        ])
        ->assertRedirect();

    $tugas = Tugas::where('kelas_id', $this->kelas->id)->first();

    expect($tugas->soal[0]['bloom'])->toBe('C2');
});

test('halaman create dan edit kuis mengirim daftar level bloom', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.kuis.create', ['pertemuan_id' => $this->pertemuan->id]))
        ->assertOk()
        ->assertViewHas('bloomLevels')
        ->assertSee('C6', false);
});

test('halaman create dan edit tugas mengirim daftar level bloom', function () {
    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.create', ['pertemuan_id' => $this->pertemuan->id]))
        ->assertOk()
        ->assertViewHas('bloomLevels')
        ->assertSee('Menganalisis');
});

test('fitur bloom dapat dimatikan lewat config tanpa mengubah bentuk soal', function () {
    config(['services.bloom.enabled' => false]);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($prompt) {
                return ! str_contains($prompt, 'Taksonomi Bloom')
                    && ! str_contains($prompt, '"bloom"');
            })
            ->andReturn(json_encode([
                [
                    'pertanyaan' => 'Apa hasil utama fotosintesis?',
                    'pilihan' => ['Glukosa dan oksigen', 'Karbon dioksida', 'Nitrogen', 'Air'],
                    'jawaban' => 0,
                    'bloom' => 'C6',
                ],
            ]));
    });

    $soal = $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $this->pertemuan->id,
            'jumlah' => 1,
            'kesulitan' => 'sedang',
        ])
        ->assertOk()
        ->json('soal');

    expect($soal[0])->toHaveKeys(['pertanyaan', 'pilihan', 'jawaban'])
        ->and($soal[0])->not->toHaveKey('bloom');
});

test('siswa melihat badge bloom pada soal kuis dan tugas', function () {
    $siswa = User::factory()->verified()->create();
    $siswa->kelasDiikuti()->attach($this->kelas->id);

    $kuis = Kuis::create([
        'judul' => 'Kuis Bloom',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
        'durasi_menit' => 5,
        'is_aktif' => true,
        'soal' => [
            [
                'pertanyaan' => 'Analisis pengaruh cahaya terhadap fotosintesis.',
                'pilihan' => ['Naik', 'Turun', 'Tetap', 'Nol'],
                'jawaban' => 0,
                'bloom' => 'C4',
            ],
        ],
    ]);

    $this->actingAs($siswa)
        ->get(route('pelajar.kuis.show', $kuis))
        ->assertOk()
        ->assertSee('C4');

    $tugas = Tugas::create([
        'judul' => 'Tugas Bloom',
        'kelas_id' => $this->kelas->id,
        'guru_id' => $this->guru->id,
        'pertemuan_id' => $this->pertemuan->id,
        'tipe' => 'essai',
        'soal' => [
            ['pertanyaan' => 'Jelaskan tahap reaksi gelap.', 'referensi' => 'Fiksasi CO2.', 'bloom' => 'C2'],
        ],
    ]);

    $this->actingAs($siswa)
        ->get(route('pelajar.tugas.show', $tugas))
        ->assertOk()
        ->assertSee('C2');
});

test('soal kuis yang diketik guru manual tetap mendapat level bloom', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.kuis.store'), [
            'judul' => 'Kuis Manual',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'durasi_menit' => 5,
            'soal' => json_encode([
                ['pertanyaan' => 'Apa ibu kota Indonesia?', 'pilihan' => ['Jakarta', 'Bandung', 'Surabaya', 'Medan'], 'jawaban' => 0, 'bloom' => null],
                ['pertanyaan' => 'Analisis pengaruh suhu terhadap laju reaksi.', 'pilihan' => ['Naik', 'Turun', 'Tetap', 'Nol'], 'jawaban' => 0, 'bloom' => null],
            ]),
        ])
        ->assertRedirect();

    $soal = Kuis::where('kelas_id', $this->kelas->id)->firstOrFail()->soal;
    $rentang = app(BloomTaxonomyService::class)->allowedRange(null);

    expect($soal[0]['bloom'])->toBeIn($rentang)
        ->and($soal[1]['bloom'])->toBeIn($rentang);
});

test('level bloom pilihan guru pada kuis manual tidak ditimpa saat disimpan', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.kuis.store'), [
            'judul' => 'Kuis Manual Dipilih',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'durasi_menit' => 5,
            'soal' => json_encode([
                ['pertanyaan' => 'Sebutkan ciri metamorfosis kupu-kupu.', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0, 'bloom' => 'C5'],
            ]),
        ])
        ->assertRedirect();

    expect(Kuis::where('kelas_id', $this->kelas->id)->firstOrFail()->soal[0]['bloom'])->toBe('C5');
});

test('soal tugas manual mendapat level bloom dan terkunci ke rentang default', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.tugas.store'), [
            'judul' => 'Esai Manual',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'tipe' => 'essai',
            'soal' => json_encode([
                ['pertanyaan' => 'Rancang percobaan untuk menguji pengaruh suhu pada laju reaksi.', 'referensi' => 'Ubah suhu, ukur waktu.', 'bloom' => null],
            ]),
        ])
        ->assertRedirect();

    $tugas = Tugas::where('kelas_id', $this->kelas->id)->firstOrFail();

    expect($tugas->soal[0]['bloom'])->toBeIn(['C2', 'C3', 'C4']);
});

test('level bloom kosong pada kuis lama dilengkapi saat kuis diupdate', function () {
    $kuis = Kuis::create([
        'judul' => 'Kuis Lama',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $this->pertemuan->id,
        'durasi_menit' => 5,
        'is_aktif' => true,
        'soal' => [
            ['pertanyaan' => 'Apa ibu kota Indonesia?', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
        ],
    ]);

    expect($kuis->soal[0])->not->toHaveKey('bloom');

    $this->actingAs($this->guru)
        ->put(route('pengajar.kuis.update', $kuis), [
            'judul' => 'Kuis Lama Diperbarui',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'durasi_menit' => 5,
            'soal' => json_encode($kuis->soal),
        ])
        ->assertRedirect();

    expect($kuis->fresh()->soal[0]['bloom'])->toBeIn(['C1', 'C2', 'C3', 'C4', 'C5', 'C6']);
});

test('soal kuis dari form disimpan sebagai JSON tunggal bukan dobel-encode', function () {
    $this->actingAs($this->guru)
        ->post(route('pengajar.kuis.store'), [
            'judul' => 'Kuis Bentuk Json',
            'kelas_id' => $this->kelas->id,
            'pertemuan_id' => $this->pertemuan->id,
            'durasi_menit' => 5,
            'soal' => json_encode([
                ['pertanyaan' => 'Apa ibu kota Indonesia?', 'pilihan' => ['a', 'b', 'c', 'd'], 'jawaban' => 0],
            ]),
        ])
        ->assertRedirect();

    $kuis = Kuis::where('kelas_id', $this->kelas->id)->firstOrFail();

    // Atribut mentah harus berupa JSON yang langsung bisa di-decode jadi daftar
    // soal. Kalau dobel-encode, nilainya jadi string yang berisi JSON sehingga
    // cast `array` membacanya sebagai string dan foreach di view siswa gagal.
    $raw = DB::table('kuis')->where('id', $kuis->id)->value('soal');

    expect(json_decode($raw, true))->toBeArray()
        ->and(json_decode($raw, true)[0]['pertanyaan'])->toBe('Apa ibu kota Indonesia?')
        ->and($kuis->soal)->toBeArray()
        ->and($kuis->soal[0])->toBeArray();
});
