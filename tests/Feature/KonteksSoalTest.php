<?php

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\MateriBabSummary;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\AIProviderService;
use App\Services\KuisGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.kuis_rag.enabled' => false,
        'services.symbols.enabled' => false,
    ]);

    $this->guru = User::factory()->pengajar()->verified()->create();
    $this->kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
});

function buatPertemuanDengan(User $guru, Kelas $kelas, string $nama, array $judulMateri, int $urutan = 1): Pertemuan
{
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => $nama,
        'urutan' => $urutan,
    ]);

    foreach ($judulMateri as $judul) {
        $materi = Materi::create([
            'judul' => $judul,
            'bab' => 'Bab 1',
            'deskripsi' => 'Deskripsi '.$judul,
            'pertemuan_id' => $pertemuan->id,
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
        ]);

        MateriBabSummary::create([
            'materi_id' => $materi->id,
            'ringkasan' => 'Ringkasan '.$judul,
        ]);
    }

    return $pertemuan->fresh(['materi.babSummary', 'materi.document.chunks', 'kelas']);
}

function konteksUntuk(Pertemuan $pertemuan, ?string $topik = null, array $sumber = []): string
{
    $svc = app(KuisGeneratorService::class);
    $m = new ReflectionMethod($svc, 'buildMateriContext');
    $m->setAccessible(true);

    return $m->invoke($svc, $pertemuan, $topik, $sumber);
}

it('memakai materi pertemuan sendiri saat guru tidak memilih sumber lain', function () {
    $pertemuan = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);

    $konteks = konteksUntuk($pertemuan);

    expect($konteks)->toContain('Fotosintesis')
        ->and($konteks)->toContain('Ringkasan Fotosintesis');
});

it('bisa memakai materi dari pertemuan lain yang dipilih guru', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    $lain = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);

    $konteks = konteksUntuk($asal, null, ['pertemuan_ids' => [$lain->id]]);

    expect($konteks)->toContain('Genetika')
        ->and($konteks)->not->toContain('Materi: Fotosintesis');
});

it('menggabungkan beberapa pertemuan sebagai konteks sekaligus', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    $kedua = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);
    $ketiga = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 3', ['Ekosistem'], 3);

    $konteks = konteksUntuk($asal, null, [
        'pertemuan_ids' => [$kedua->id, $ketiga->id],
    ]);

    expect($konteks)->toContain('Genetika')
        ->and($konteks)->toContain('Ekosistem');
});

it('materi yang dicentang mempersempit hanya pertemuan itu, pertemuan lain tetap utuh', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis', 'Respirasi']);
    $lain = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);

    $materiDipilih = $asal->materi->firstWhere('judul', 'Fotosintesis');

    $konteks = konteksUntuk($asal, null, [
        'pertemuan_ids' => [$asal->id, $lain->id],
        'materi_ids' => [$materiDipilih->id],
    ]);

    expect($konteks)->toContain('Materi: Fotosintesis')
        ->and($konteks)->not->toContain('Materi: Respirasi')
        ->and($konteks)->toContain('Genetika');
});

it('materi dari pertemuan yang tidak dipilih diabaikan', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    $lain = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);

    $materiLain = $lain->materi->first();

    $konteks = konteksUntuk($asal, null, [
        'pertemuan_ids' => [$asal->id],
        'materi_ids' => [$materiLain->id],
    ]);

    expect($konteks)->toContain('Fotosintesis')
        ->and($konteks)->not->toContain('Genetika');
});

it('id pertemuan yang tidak dikenal jatuh kembali ke pertemuan asal', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);

    $konteks = konteksUntuk($asal, null, ['pertemuan_ids' => [999999]]);

    expect($konteks)->toContain('Fotosintesis');
});

it('service menolak ID pertemuan milik guru lain meski FormRequest dilewati', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);

    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);
    $asing = buatPertemuanDengan($guruLain, $kelasLain, 'Pertemuan Asing', ['Materi Rahasia']);

    // Lapisan kedua: caller internal bisa meneruskan ID tanpa lewat FormRequest,
    // jadi service sendiri yang harus menahan.
    $konteks = konteksUntuk($asal, null, ['pertemuan_ids' => [$asing->id]]);

    expect($konteks)->toContain('Fotosintesis')
        ->and($konteks)->not->toContain('Materi Rahasia');
});

it('sumberKonteks hanya memuat pertemuan milik guru beserta materinya', function () {
    $milik = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis', 'Respirasi']);

    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);
    buatPertemuanDengan($guruLain, $kelasLain, 'Pertemuan Asing', ['Materi Rahasia']);

    $sumber = app(KuisGeneratorService::class)->sumberKonteks($this->guru->id);

    expect($sumber)->toHaveCount(1)
        ->and($sumber[0]['id'])->toBe($milik->id)
        ->and($sumber[0]['nama'])->toBe('Pertemuan 1')
        ->and($sumber[0]['materi'])->toHaveCount(2)
        ->and($sumber[0]['materi'][0]['judul'])->toBe('Fotosintesis');
});

it('sumberKonteks bisa dibatasi ke satu kelas', function () {
    $kelasLain = Kelas::factory()->create(['guru_id' => $this->guru->id]);
    buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    buatPertemuanDengan($this->guru, $kelasLain, 'Pertemuan X', ['Geometri']);

    $sumber = app(KuisGeneratorService::class)->sumberKonteks($this->guru->id, $this->kelas->id);

    expect($sumber)->toHaveCount(1)
        ->and($sumber[0]['materi'][0]['judul'])->toBe('Fotosintesis');
});

it('generate soal memakai konteks dari pertemuan yang dipilih', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    $lain = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);

    $promptDikirim = '';

    $this->mock(AIProviderService::class, function ($mock) use (&$promptDikirim) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($prompt) use (&$promptDikirim) {
                $promptDikirim = $prompt;

                return true;
            })
            ->andReturn(json_encode([
                [
                    'pertanyaan' => 'Apa konsep dasar genetika?',
                    'pilihan' => ['Gen', 'Kromosom', 'Sel', 'Enzim'],
                    'jawaban' => 1,
                ],
            ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $asal->id,
            'jumlah' => 1,
            'kesulitan' => 'sedang',
            'konteks_pertemuan_ids' => [$lain->id],
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($promptDikirim)->toContain('Genetika')
        ->and($promptDikirim)->not->toContain('Materi: Fotosintesis');
});

it('generate soal menolak sumber konteks dari kelas guru lain', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);

    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);
    $pertemuanAsing = buatPertemuanDengan($guruLain, $kelasLain, 'Pertemuan Asing', ['Materi Rahasia']);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldNotReceive('sendLocalPreferred');
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $asal->id,
            'jumlah' => 1,
            'kesulitan' => 'sedang',
            'konteks_pertemuan_ids' => [$pertemuanAsing->id],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('konteks_pertemuan_ids.0');
});

it('generate soal menolak materi dari kelas guru lain', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);

    $guruLain = User::factory()->pengajar()->verified()->create();
    $kelasLain = Kelas::factory()->create(['guru_id' => $guruLain->id]);
    $pertemuanAsing = buatPertemuanDengan($guruLain, $kelasLain, 'Pertemuan Asing', ['Materi Rahasia']);

    $this->mock(AIProviderService::class, function ($mock) {
        $mock->shouldNotReceive('sendLocalPreferred');
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.kuis.generate'), [
            'pertemuan_id' => $asal->id,
            'jumlah' => 1,
            'kesulitan' => 'sedang',
            'konteks_pertemuan_ids' => [$asal->id],
            'konteks_materi_ids' => [$pertemuanAsing->materi->first()->id],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('konteks_materi_ids.0');
});

it('generate esai juga memakai sumber konteks yang dipilih', function () {
    $asal = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    $lain = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);

    $promptDikirim = '';

    $this->mock(AIProviderService::class, function ($mock) use (&$promptDikirim) {
        $mock->shouldReceive('sendLocalPreferred')
            ->once()
            ->withArgs(function ($prompt) use (&$promptDikirim) {
                $promptDikirim = $prompt;

                return true;
            })
            ->andReturn(json_encode([
                [
                    'pertanyaan' => 'Jelaskan konsep pewarisan sifat.',
                    'referensi' => 'Gen dan alel.',
                ],
            ]));
    });

    $this->actingAs($this->guru)
        ->postJson(route('pengajar.tugas.generate'), [
            'pertemuan_id' => $asal->id,
            'tipe' => 'essai',
            'jumlah' => 1,
            'kesulitan' => 'sedang',
            'konteks_pertemuan_ids' => [$lain->id],
        ])
        ->assertOk();

    expect($promptDikirim)->toContain('Genetika')
        ->and($promptDikirim)->not->toContain('Materi: Fotosintesis');
});

it('halaman create dan edit kuis mengirim daftar sumber konteks', function () {
    $pertemuan = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);

    $this->actingAs($this->guru)
        ->get(route('pengajar.kuis.create', ['pertemuan_id' => $pertemuan->id]))
        ->assertOk()
        ->assertViewHas('sumberKonteks')
        ->assertSee('Konteks Materi')
        ->assertSee('Genetika');
});

it('halaman create dan edit tugas mengirim daftar sumber konteks', function () {
    $pertemuan = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);
    buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 2', ['Genetika'], 2);

    $this->actingAs($this->guru)
        ->get(route('pengajar.tugas.create', ['pertemuan_id' => $pertemuan->id]))
        ->assertOk()
        ->assertViewHas('sumberKonteks')
        ->assertSee('Konteks Materi');
});

it('dropdown pertemuan pada halaman edit menampilkan nama yang benar', function () {
    $pertemuan = buatPertemuanDengan($this->guru, $this->kelas, 'Pertemuan 1', ['Fotosintesis']);

    $kuis = Kuis::create([
        'judul' => 'Kuis Uji',
        'kelas_id' => $this->kelas->id,
        'pertemuan_id' => $pertemuan->id,
        'durasi_menit' => 5,
        'is_aktif' => true,
        'soal' => [],
    ]);

    $this->actingAs($this->guru)
        ->get(route('pengajar.kuis.edit', $kuis))
        ->assertOk()
        // Pertemuan tidak punya kolom `nama`, jadi mapping harus pakai
        // nama_pertemuan; kalau salah, label dropdownnya kosong.
        ->assertSee('&quot;nama&quot;:&quot;Pertemuan 1&quot;', false);
});
