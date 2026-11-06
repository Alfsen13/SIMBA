<?php

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\EmbeddingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\mock;

it('hapus chunk parsial dan menandai dokumen gagal saat penyimpanan chunk gagal', function () {
    Storage::fake('public');
    Storage::disk('public')->put('documents/modul.txt', 'isi materi');

    $user = User::factory()->operator()->create();
    $document = Document::create([
        'name' => 'Modul',
        'original_name' => 'modul.txt',
        'path' => 'documents/modul.txt',
        'type' => 'txt',
        'size' => 10,
        'status' => 'pending',
        'user_id' => $user->id,
    ]);

    $documentService = mock(DocumentService::class);
    $documentService->shouldReceive('chunkText')->once()->andReturn([
        ['content' => 'bagian pertama', 'index' => 0],
        ['content' => 'bagian kedua', 'index' => 1],
    ]);

    $embedding = mock(EmbeddingService::class);
    $embedding->shouldReceive('embedBatch')->once()->andReturn([[1.0, 0.0], [1.0, 0.0]]);

    $event = 'eloquent.creating: '.DocumentChunk::class;
    Event::listen($event, function (DocumentChunk $chunk): void {
        if ($chunk->chunk_index === 1) {
            throw new RuntimeException('Gagal menyimpan chunk uji.');
        }
    });

    try {
        (new ProcessDocument($document))->handle($documentService, $embedding);
    } finally {
        Event::forget($event);
    }

    expect($document->fresh()->status)->toBe('failed')
        ->and($document->chunks()->count())->toBe(0);
});

it('menandai dokumen gagal jika hasil embedding tidak mencakup semua chunk', function () {
    Storage::fake('public');
    Storage::disk('public')->put('documents/modul.txt', 'isi materi');

    $user = User::factory()->operator()->create();
    $document = Document::create([
        'name' => 'Modul',
        'original_name' => 'modul.txt',
        'path' => 'documents/modul.txt',
        'type' => 'txt',
        'size' => 10,
        'status' => 'pending',
        'user_id' => $user->id,
    ]);

    mock(DocumentService::class)
        ->shouldReceive('chunkText')
        ->once()
        ->andReturn([
            ['content' => 'bagian pertama', 'index' => 0],
            ['content' => 'bagian kedua', 'index' => 1],
        ]);
    mock(EmbeddingService::class)
        ->shouldReceive('embedBatch')
        ->once()
        ->andReturn([[1.0, 0.0]]);

    (new ProcessDocument($document))->handle(
        app(DocumentService::class),
        app(EmbeddingService::class),
    );

    expect($document->fresh()->status)->toBe('failed')
        ->and($document->chunks()->count())->toBe(0);
});

it('RAG hanya mencari chunk dari dokumen yang selesai diproses', function () {
    if (method_exists(DB::connection()->getPdo(), 'sqliteCreateFunction')) {
        DB::connection()->getPdo()->sqliteCreateFunction(
            'JSON_LENGTH',
            fn (?string $json): int => count(json_decode($json ?? '[]', true) ?: [])
        );
    }

    $user = User::factory()->operator()->create();
    $completed = Document::create([
        'name' => 'Siap',
        'original_name' => 'siap.txt',
        'path' => 'documents/siap.txt',
        'type' => 'txt',
        'status' => 'completed',
        'user_id' => $user->id,
    ]);
    $failed = Document::create([
        'name' => 'Gagal',
        'original_name' => 'gagal.txt',
        'path' => 'documents/gagal.txt',
        'type' => 'txt',
        'status' => 'failed',
        'user_id' => $user->id,
    ]);

    $completed->chunks()->create([
        'content' => 'materi valid',
        'embedding' => [1.0, 0.0],
        'chunk_index' => 0,
    ]);
    $failed->chunks()->create([
        'content' => 'materi parsial',
        'embedding' => [1.0, 0.0],
        'chunk_index' => 0,
    ]);
    $completed->chunks()->create([
        'content' => 'embedding lama tidak kompatibel',
        'embedding' => [1.0, 0.0, 10.0],
        'chunk_index' => 1,
    ]);

    $embedding = mock(EmbeddingService::class)->makePartial();
    $embedding->shouldReceive('embed')->once()->with('materi')->andReturn([1.0, 0.0]);

    $hasil = $embedding->searchChunks('materi', minScore: 0.5);

    expect($hasil)->toHaveCount(1)
        ->and($hasil[0]['content'])->toBe('materi valid');
});

it('pencarian RAG tidak menambah query relasi per dokumen', function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        DB::connection()->getPdo()->sqliteCreateFunction(
            'JSON_LENGTH',
            fn (?string $json): int => count(json_decode($json ?? '[]', true) ?: [])
        );
    }

    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan RAG',
        'urutan' => 1,
    ]);

    $buatDokumen = function (int $index) use ($guru, $kelas, $pertemuan): void {
        $materi = Materi::factory()->create([
            'judul' => "Materi {$index}",
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'pertemuan_id' => $pertemuan->id,
        ]);
        $document = Document::create([
            'name' => "Dokumen {$index}",
            'original_name' => "dokumen-{$index}.txt",
            'path' => "documents/dokumen-{$index}.txt",
            'type' => 'txt',
            'status' => 'completed',
            'user_id' => $guru->id,
            'materi_id' => $materi->id,
            'kelas_id' => $kelas->id,
        ]);
        $document->chunks()->create([
            'content' => "Isi materi {$index}",
            'embedding' => [1.0, 0.0],
            'chunk_index' => 0,
        ]);
    };

    $buatDokumen(1);
    $embedding = mock(EmbeddingService::class)->makePartial();
    $embedding->shouldReceive('embed')->andReturn([1.0, 0.0]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $hasilSatuDokumen = $embedding->searchChunks('materi', minScore: 0.5);
    $querySatuDokumen = count(DB::getQueryLog());
    DB::disableQueryLog();

    $buatDokumen(2);
    $buatDokumen(3);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $hasilTigaDokumen = $embedding->searchChunks('materi', minScore: 0.5);
    $queryTigaDokumen = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($hasilSatuDokumen)->toHaveCount(1)
        ->and($hasilTigaDokumen)->toHaveCount(3)
        ->and($queryTigaDokumen)->toBe($querySatuDokumen);
});
