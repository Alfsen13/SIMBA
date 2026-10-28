<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\AIProviderService;

function buatPertemuanMateriTugas(User $guru): Pertemuan
{
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan AI',
        'urutan' => 1,
    ]);

    $materi = Materi::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'pertemuan_id' => $pertemuan->id,
    ]);

    MateriBabSummary::create([
        'materi_id' => $materi->id,
        'ringkasan' => 'Sistem persamaan linear dua variabel diselesaikan dengan eliminasi dan substitusi.',
    ]);

    return $pertemuan;
}

beforeEach(function () {
    $this->guru = User::factory()->pengajar()->verified()->create();
});

test('guru dapat generate soal pilihan ganda untuk tugas', function () {
    $pertemuan = buatPertemuanMateriTugas($this->guru);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            [
                'pertanyaan' => 'Metode apa yang tepat menyelesaikan SPLDV?',
                'pilihan' => ['Eliminasi', 'Perkalian', 'Pengurangan biasa', 'Tidak ada'],
                'jawaban' => 0,
            ],
        ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'tipe' => 'pilihan_ganda',
            'jumlah' => 1,
            'kesulitan' => 'sedang',
            'topik' => 'SPLDV',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'soal')
        ->assertJsonPath('soal.0.jawaban', 0)
        ->assertJsonPath('soal.0.pilihan.1', 'Perkalian');
});

test('guru dapat generate soal esai untuk tugas', function () {
    $pertemuan = buatPertemuanMateriTugas($this->guru);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andReturn(json_encode([
            [
                'pertanyaan' => 'Sebutkan langkah-langkah eliminasi untuk SPLDV.',
                'referensi' => 'Samakan koefisien, kurangkan untuk menghilangkan satu variabel, lalu substitusikan hasilnya.',
            ],
        ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'tipe' => 'essai',
            'jumlah' => 1,
            'kesulitan' => 'mudah',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'soal')
        ->assertJsonPath('soal.0.referensi', 'Samakan koefisien, kurangkan untuk menghilangkan satu variabel, lalu substitusikan hasilnya.');
});

test('generate soal tugas divalidasi parameter', function () {
    $pertemuan = buatPertemuanMateriTugas($this->guru);

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'tipe' => 'essai',
            'jumlah' => 99,
            'kesulitan' => 'extreme',
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['jumlah', 'kesulitan']]);
});

test('generate soal tugas menolak pertemuan pada kelas guru lain', function () {
    $guruLain = User::factory()->pengajar()->verified()->create();
    $pertemuan = buatPertemuanMateriTugas($guruLain);

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'tipe' => 'pilihan_ganda',
            'jumlah' => 2,
            'kesulitan' => 'mudah',
        ])
        ->assertForbidden();
});

test('generate soal tugas menolak pertemuan tanpa materi', function () {
    $kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Kosong',
        'urutan' => 1,
    ]);

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'tipe' => 'essai',
            'jumlah' => 2,
            'kesulitan' => 'mudah',
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('generate soal tugas mengembalikan 422 saat AI gagal', function () {
    $pertemuan = buatPertemuanMateriTugas($this->guru);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldReceive('sendLocalPreferred')->once()->andThrow(new Exception('AI down'));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $pertemuan->id,
            'tipe' => 'pilihan_ganda',
            'jumlah' => 2,
            'kesulitan' => 'mudah',
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

function xDataTugasSeimbang(string $ekspresi): bool
{
    $pasangan = ['(' => ')', '{' => '}', '[' => ']'];
    $tumpukan = [];
    $dalamString = false;
    $panjang = strlen($ekspresi);

    for ($i = 0; $i < $panjang; $i++) {
        $c = $ekspresi[$i];

        if ($dalamString) {
            if ($c === '\\') {
                $i++;
            } elseif ($c === "'") {
                $dalamString = false;
            }

            continue;
        }

        if ($c === "'") {
            $dalamString = true;
        } elseif (isset($pasangan[$c])) {
            $tumpukan[] = $pasangan[$c];
        } elseif ($c === ')' || $c === '}' || $c === ']') {
            if (array_pop($tumpukan) !== $c) {
                return false;
            }
        }
    }

    return $tumpukan === [] && ! $dalamString;
}

test('x-data tugasBuilder di halaman create tugas valid (regression Blade }})', function () {
    $pertemuan = buatPertemuanMateriTugas($this->guru);

    $html = $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.create', ['pertemuan_id' => $pertemuan->id]))
        ->assertSuccessful()
        ->getContent();

    expect(preg_match('/x-data="(tugasBuilder\([^"]*\))"/', $html, $cocok))->toBe(1);
    expect(xDataTugasSeimbang($cocok[1]))->toBeTrue();
    expect($cocok[1])->toContain('sumberKonteks:');
});
