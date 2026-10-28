<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DocumentChunk;
use App\Services\EmbeddingService;
use Illuminate\Console\Command;

class ReembedChunks extends Command
{
    protected $signature = 'document:reembed {--batch-size=50} {--delay=2000}';

    protected $description = 'Regenerate embeddings for all chunks that have empty embeddings';

    public function handle(EmbeddingService $embedding): int
    {
        $batchSize = (int) $this->option('batch-size');
        $delayMs = (int) $this->option('delay');

        $chunks = DocumentChunk::whereRaw('JSON_LENGTH(embedding) = 0')
            ->orderBy('document_id')
            ->orderBy('chunk_index')
            ->get();

        if ($chunks->isEmpty()) {
            $this->info('Tidak ada chunk yang perlu di-embed.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$chunks->count()} chunk dengan embedding kosong.");

        $byDocument = $chunks->groupBy('document_id');
        $total = 0;
        $failed = 0;

        foreach ($byDocument as $documentId => $docChunks) {
            $this->newLine();
            $this->info("Dokumen #{$documentId}: {$docChunks->count()} chunk(s)");

            $texts = $docChunks->pluck('content')->all();
            $chunkIds = $docChunks->pluck('id')->all();
            $textBatches = array_chunk($texts, $batchSize);
            $idBatches = array_chunk($chunkIds, $batchSize);

            foreach ($textBatches as $batchIdx => $textBatch) {
                $embeddings = [];
                $retries = 0;

                while (true) {
                    try {
                        $embeddings = $embedding->embedBatch($textBatch, $batchSize, 0);
                        break;
                    } catch (\Throwable $e) {
                        if ($retries < 3 && str_contains($e->getMessage(), '429')) {
                            $retries++;
                            $wait = 20 * $retries;
                            $this->warn("  Rate limited, tunggu {$wait}s (retry {$retries}/3)...");
                            sleep($wait);
                        } else {
                            $this->error('  Batch '.($batchIdx + 1)." gagal: {$e->getMessage()}");
                            $failed += count($textBatch);
                            $embeddings = [];
                            break;
                        }
                    }
                }

                $ids = $idBatches[$batchIdx] ?? [];
                foreach ($textBatch as $i => $text) {
                    if (! empty($embeddings[$i])) {
                        DocumentChunk::where('id', $ids[$i])->update(['embedding' => $embeddings[$i]]);
                        $total++;
                    }
                }

                $this->info('  Batch '.($batchIdx + 1).'/'.count($textBatches).' selesai.');

                if ($batchIdx < count($textBatches) - 1) {
                    usleep($delayMs * 1000);
                }
            }

            sleep(2);
        }

        $this->newLine();
        $this->info("Selesai: {$total} chunk berhasil, {$failed} chunk gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
