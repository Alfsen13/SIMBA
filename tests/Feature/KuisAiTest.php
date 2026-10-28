<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\AIProviderService;

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
