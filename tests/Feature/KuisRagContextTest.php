<?php

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\KuisGeneratorService;
use App\Services\RAGService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.kuis_rag.enabled' => true,
        'services.kuis_rag.top_k' => 6,
        'services.kuis_rag.min_score' => 0.35,
        'services.kuis_rag.max_chars' => 8000,
        'services.symbols.enabled' => false,
    ]);
});

function buatPertemuan(array $attr = []): Pertemuan
{
    // Nilai role tidak relevan untuk logika yang diuji; yang penting user
    // ada supaya foreign key guru_id terpenuhi.
    $guruId = User::factory()->create(['role' => 'pengajar'])->id;

    $kelas = Kelas::create([
        'nama_kelas' => 'Kelas RAG',
        'kode_kelas' => 'RAG-'.uniqid(),
        'guru_id' => $guruId,
        'kategori' => $attr['kategori'] ?? 'Matematika',
    ]);

    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan RAG',
        'urutan' => 1,
    ]);

    $materi = Materi::create([
        'judul' => $attr['judul'] ?? 'Integral Parsial',
        'bab' => 'Bab 1',
        'deskripsi' => $attr['deskripsi'] ?? 'Materi tentang integral',
        'pertemuan_id' => $pertemuan->id,
        'kelas_id' => $kelas->id,
        'guru_id' => $guruId,
    ]);

    return $pertemuan->fresh(['materi', 'kelas']);
}

function panggilContext(Pertemuan $pertemuan, ?string $topik = null): string
{
    $svc = app(KuisGeneratorService::class);
    $m = new ReflectionMethod($svc, 'buildMateriContext');
    $m->setAccessible(true);

    return $m->invoke($svc, $pertemuan, $topik);
}

it('memakai ringkasan bab saat RAG tidak menemukan apa pun', function () {
    $pertemuan = buatPertemuan();

    DB::table('materi_bab_summary')->insert([
        'materi_id' => $pertemuan->materi->first()->id,
        'ringkasan' => 'Ringkasan yang ditulis guru.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $konteks = panggilContext($pertemuan->fresh(['materi.babSummary', 'kelas']), 'integral');

    expect($konteks)->toContain('Ringkasan guru: Ringkasan yang ditulis guru.');
});

it('tidak melempar exception saat RAG kosong dan tidak ada ringkasan', function () {
    $pertemuan = buatPertemuan();

    $konteks = panggilContext($pertemuan->fresh(['materi.babSummary', 'materi.document.chunks', 'kelas']), 'integral');

    expect($konteks)->toContain('Materi: Integral Parsial');
});

it('melewati RAG sepenuhnya saat dimatikan lewat config', function () {
    config(['services.kuis_rag.enabled' => false]);

    $pertemuan = buatPertemuan();

    DB::table('materi_bab_summary')->insert([
        'materi_id' => $pertemuan->materi->first()->id,
        'ringkasan' => 'Ringkasan guru tetap dipakai.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $konteks = panggilContext($pertemuan->fresh(['materi.babSummary', 'kelas']), 'integral');

    expect($konteks)->toContain('Ringkasan guru: Ringkasan guru tetap dipakai.')
        ->and($konteks)->not->toContain('diambil otomatis berdasarkan topik');
});

it('merangkai chunk RAG di depan ringkasan guru ketika retrieval mengembalikan data', function () {
    $pertemuan = buatPertemuan();

    DB::table('materi_bab_summary')->insert([
        'materi_id' => $pertemuan->materi->first()->id,
        'ringkasan' => 'Ringkasan singkat dari guru.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $svc = app(KuisGeneratorService::class);

    // Stub RAG: kembalikan satu chunk supaya jalur teratas teruji.
    $stub = new class extends RAGService
    {
        public function retrieve(string $query, ?int $k = null, ?float $minScore = null, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): array
        {
            return [[
                'id' => 1,
                'document_id' => 9,
                'content' => 'Chunk dari dokumen: integral parsial uv minus v du.',
                'score' => 0.82,
                'source' => 'modul.pdf',
                'materi_id' => 1,
                'materi_judul' => 'Integral Parsial',
                'page_start' => 12,
                'page_end' => 12,
            ]];
        }
    };

    $m = new ReflectionProperty(KuisGeneratorService::class, 'rag');
    $m->setAccessible(true);
    $m->setValue($svc, $stub);

    $ctx = new ReflectionMethod($svc, 'buildMateriContext');
    $ctx->setAccessible(true);
    $konteks = $ctx->invoke($svc, $pertemuan->fresh(['materi.babSummary', 'kelas']), 'integral');

    expect($konteks)->toContain('Kutipan dari dokumen pembelajaran')
        ->and($konteks)->toContain('Chunk dari dokumen: integral parsial uv minus v du.')
        ->and($konteks)->toContain('hlm. 12')
        ->and($konteks)->toContain('Ringkasan guru: Ringkasan singkat dari guru.');

    // Chunk RAG harus mendahului ringkasan guru.
    expect(strpos($konteks, 'Kutipan dari dokumen pembelajaran'))
        ->toBeLessThan(strpos($konteks, 'Ringkasan guru:'));
});

it('memakai isi chunk dokumen sebagai jaring pengaman terakhir', function () {
    $pertemuan = buatPertemuan();
    $materi = $pertemuan->materi->first();

    $docId = DB::table('documents')->insertGetId([
        'name' => 'modul',
        'original_name' => 'modul.pdf',
        'path' => 'documents/x.pdf',
        'type' => 'application/pdf',
        'size' => 100,
        'status' => 'done',
        'user_id' => DB::table('users')->value('id'),
        'materi_id' => $materi->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    for ($i = 0; $i < 3; $i++) {
        DB::table('document_chunks')->insert([
            'document_id' => $docId,
            'content' => 'Isi dokumen bagian '.($i + 1).' tentang integral.',
            'chunk_index' => $i,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $konteks = panggilContext(
        $pertemuan->fresh(['materi.babSummary', 'materi.document.chunks', 'kelas']),
        'integral'
    );

    expect($konteks)->toContain('Isi:')
        ->and($konteks)->toContain('Isi dokumen bagian 1 tentang integral.');
});

it('menghormati batas karakter RAG', function () {
    config(['services.kuis_rag.max_chars' => 200]);

    $pertemuan = buatPertemuan();

    $svc = app(KuisGeneratorService::class);
    $stub = new class extends RAGService
    {
        public function retrieve(string $query, ?int $k = null, ?float $minScore = null, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): array
        {
            return [
                ['id' => 1, 'document_id' => 1, 'content' => str_repeat('x', 500), 'score' => 0.9, 'source' => 'a.pdf', 'materi_judul' => 'A', 'page_start' => null, 'page_end' => null],
                ['id' => 2, 'document_id' => 2, 'content' => str_repeat('y', 500), 'score' => 0.8, 'source' => 'b.pdf', 'materi_judul' => 'B', 'page_start' => null, 'page_end' => null],
            ];
        }
    };

    $p = new ReflectionProperty(KuisGeneratorService::class, 'rag');
    $p->setAccessible(true);
    $p->setValue($svc, $stub);

    $m = new ReflectionMethod($svc, 'buildRagContext');
    $m->setAccessible(true);
    $hasil = $m->invoke($svc, $pertemuan->fresh(['materi', 'kelas']), 'integral');

    // Chunk kedua tidak muat sehingga harus terpotus.
    expect($hasil)->toContain('Kutipan dari dokumen pembelajaran')
        ->and($hasil)->not->toContain('yyyy');
});

it('mengulang retrieval tanpa filter kategori saat kategori tidak cocok', function () {
    $pertemuan = buatPertemuan();

    $svc = app(KuisGeneratorService::class);

    $stub = new class extends RAGService
    {
        /** @var array<int, array<int, ?string>> */
        public array $calls = [];

        public function retrieve(string $query, ?int $k = null, ?float $minScore = null, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): array
        {
            $this->calls[] = [$kategori, $pertemuanId];

            // Hanya panggilan tanpa kategori yang menghasilkan data,
            // meniru dokumen yang kategorinya berbeda dari kelas.
            return $kategori === null
                ? [['id' => 1, 'document_id' => 1, 'content' => 'Temuan dari dokumen.', 'score' => 0.7, 'source' => 'd.pdf', 'materi_judul' => 'M', 'page_start' => null, 'page_end' => null]]
                : [];
        }
    };

    $p = new ReflectionProperty(KuisGeneratorService::class, 'rag');
    $p->setAccessible(true);
    $p->setValue($svc, $stub);

    $m = new ReflectionMethod($svc, 'buildRagContext');
    $m->setAccessible(true);
    $hasil = $m->invoke($svc, $pertemuan->fresh(['materi', 'kelas']), 'integral');

    expect($hasil)->toContain('Temuan dari dokumen.')
        ->and($stub->calls)->toHaveCount(2)
        ->and($stub->calls[0][0])->toBe('Matematika')
        ->and($stub->calls[1][0])->toBeNull()
        ->and($stub->calls[1][1])->toBe($pertemuan->id);
});

it('membatasi retrieval ke pertemuan yang diminta saja', function () {
    $pertemuan = buatPertemuan();

    $svc = app(KuisGeneratorService::class);
    $stub = new class extends RAGService
    {
        public ?int $seenPertemuan = null;

        public function retrieve(string $query, ?int $k = null, ?float $minScore = null, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): array
        {
            $this->seenPertemuan = $pertemuanId;

            return [];
        }
    };

    $p = new ReflectionProperty(KuisGeneratorService::class, 'rag');
    $p->setAccessible(true);
    $p->setValue($svc, $stub);

    $m = new ReflectionMethod($svc, 'buildRagContext');
    $m->setAccessible(true);
    $m->invoke($svc, $pertemuan->fresh(['materi', 'kelas']), 'integral');

    expect($stub->seenPertemuan)->toBe($pertemuan->id);
});
