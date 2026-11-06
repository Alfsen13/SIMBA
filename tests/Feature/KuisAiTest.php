<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\User;
use App\Jobs\GenerateSoalKuisAI;
use App\Services\AIProviderService;
use Illuminate\Support\Facades\Queue;

function buatPertemuanBermateri(User $guru): Pertemuan
{
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    MateriBabSummary::create([
        'materi_id' => $materi->id,
        'ringkasan' => 'Fotosintesis adalah proses mengubah energi cahaya menjadi energi kimia pada tumbuhan.',
    ]);

    return $pertemuan;
}

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
});

test('guru dapat generate soal kuis dari materi pertemuan', function () {
    $pertemuan = buatPertemuanBermateri($this->guru);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            [
                'pertanyaan' => 'Apa hasil utama fotosintesis?',
                'pilihan' => ['Oksigen dan glukosa', 'Karbon dioksida', 'Nitrogen', 'Air saja'],
                'jawaban' => 0,
            ],
            [
                'pertanyaan' => 'Dimana fotosintesis berlangsung?',
                'pilihan' => ['Di akar', 'Di kloroplas', 'Di buah', 'Di batang'],
                'jawaban' => 1,
            ],
        ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'jumlah' => 2,
            'kesulitan' => 'mudah',
            'topik' => 'Fotosintesis',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'soal')
        ->assertJsonPath('soal.0.jawaban', 0)
        ->assertJsonPath('soal.1.pilihan.0', 'Di akar');
});

test('generator kuis menolak jumlah soal yang kurang dari permintaan', function () {
    $pertemuan = buatPertemuanBermateri($this->guru);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->times(3)->andReturn(json_encode([
            [
                'pertanyaan' => 'Apa hasil utama fotosintesis?',
                'pilihan' => ['Oksigen', 'Glukosa', 'Nitrogen', 'Air'],
                'jawaban' => 0,
            ],
        ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'jumlah' => 2,
            'kesulitan' => 'mudah',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false);
});

test('generator kuis mengulang respons dengan pilihan atau kunci jawaban tidak valid', function () {
    $pertemuan = buatPertemuanBermateri($this->guru);
    $responsTidakValid = json_encode([
        [
            'pertanyaan' => 'Soal dengan pilihan tidak lengkap',
            'pilihan' => ['A', 'B', 'C'],
            'jawaban' => 0,
        ],
        [
            'pertanyaan' => 'Soal dengan kunci jawaban tidak valid',
            'pilihan' => ['A', 'B', 'C', 'D'],
            'jawaban' => 'Z',
        ],
    ]);
    $responsValid = json_encode([
        [
            'pertanyaan' => 'Apa hasil utama fotosintesis?',
            'pilihan' => ['Oksigen', 'Glukosa', 'Nitrogen', 'Air'],
            'jawaban' => 'B',
        ],
    ]);

    $this->mock(AIProviderService::class, function ($mock) use ($responsTidakValid, $responsValid) {
        $mock->shouldReceive('sendLocalPreferred')
            ->twice()
            ->andReturn($responsTidakValid, $responsValid);
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'jumlah' => 1,
            'kesulitan' => 'mudah',
        ])
        ->assertSuccessful()
        ->assertJsonPath('soal.0.pertanyaan', 'Apa hasil utama fotosintesis?')
        ->assertJsonPath('soal.0.jawaban', 1)
        ->assertJsonCount(4, 'soal.0.pilihan');
});

test('generate kuis dijadwalkan ke queue dan menyediakan kunci status', function () {
    $pertemuan = buatPertemuanBermateri($this->guru);
    Queue::fake();

    $response = $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'jumlah' => 2,
            'kesulitan' => 'mudah',
        ])
        ->assertStatus(202)
        ->assertJsonPath('status', 'queued');

    $key = $response->json('key');
    expect($key)->toStartWith('ai-soal:kuis:'.$this->guru->id.':');

    Queue::assertPushed(GenerateSoalKuisAI::class, fn ($job) => $job->cacheKey === $key
        && $job->userId === $this->guru->id
        && $job->pertemuanId === $pertemuan->id);
});

test('generate soal menolak pertemuan tanpa materi', function () {
    $kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Kosong',
        'urutan' => 1,
    ]);

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'jumlah' => 2,
            'kesulitan' => 'sedang',
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('generate soal divalidasi parameter', function () {
    $pertemuan = buatPertemuanBermateri($this->guru);

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'jumlah' => 99,
            'kesulitan' => 'extreme',
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['jumlah', 'kesulitan']]);
});

test('generate soal menolak pertemuan kelas guru lain', function () {
    $guruLain = User::factory()->pengajar()->verified()->create();
    $pertemuan = buatPertemuanBermateri($guruLain);

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'jumlah' => 2,
            'kesulitan' => 'mudah',
        ])
        ->assertForbidden();
});
