<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\DocumentFormulaReview;
use App\Models\Materi;
use App\Services\DocumentService;
use App\Services\EmbeddingService;
use App\Services\MateriRingkasanService;
use App\Services\RumusPenanda;
use App\Services\RumusPerbaikanChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MateriReingest extends Command
{
    protected $signature = 'materi:reingest
                            {--materi= : Hanya materi dengan id ini, dipisah koma}
                            {--kategori= : Hanya dokumen dengan kategori ini}
                            {--dry-run : Laporkan isi hasil ekstraksi tanpa menulis apa pun}';

    protected $description = 'Menyusun ulang chunk dokumen PDF supaya teks rumus memakai penanda, lalu memasang kembali rumus yang sudah disetujui';

    public function handle(
        DocumentService $documentService,
        EmbeddingService $embedding,
        RumusPerbaikanChunk $perbaikan,
        MateriRingkasanService $ringkasan,
    ): int {
        $dokumen = $this->dokumen();

        if ($dokumen->isEmpty()) {
            $this->info('Tidak ada dokumen yang cocok.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            return $this->pratinjau($dokumen, $documentService);
        }

        $gagal = 0;

        foreach ($dokumen as $document) {
            $label = '#'.$document->id.' '.$document->original_name;

            try {
                $jumlah = $this->tanam($document, $documentService, $embedding);
                $terapkan = $this->pasangRumus($document, $embedding, $perbaikan);

                $materi = $document->materi;

                if ($materi instanceof Materi) {
                    $ringkasan->sinkronkanRumus($materi);
                }

                $this->info("{$label}: {$jumlah} chunk, {$terapkan['tambal']} rumus ditambal"
                    .' ('.$terapkan['penanda'].' lewat penanda)'
                    .($terapkan['tambah'] > 0 ? ", {$terapkan['tambah']} potongan baru" : ''));
            } catch (Throwable $e) {
                $gagal++;
                $this->error("{$label}: gagal — {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Selesai: '.($dokumen->count() - $gagal).' dokumen berhasil, '.$gagal.' gagal.');

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return Collection<int, Document> */
    private function dokumen(): Collection
    {
        $materi = $this->option('materi');

        return Document::query()
            ->where('type', 'pdf')
            ->where('status', 'completed')
            ->when(is_string($materi) && $materi !== '', function ($query) use ($materi): void {
                $query->whereIn('materi_id', array_map('intval', preg_split('/\s*,\s*/', $materi, -1, PREG_SPLIT_NO_EMPTY) ?: []));
            })
            ->when(is_string($this->option('kategori')) && $this->option('kategori') !== '',
                fn ($query) => $query->where('kategori', (string) $this->option('kategori')))
            ->orderBy('id')
            ->get();
    }

    /** @param  Collection<int, Document>  $dokumen */
    private function pratinjau(Collection $dokumen, DocumentService $documentService): int
    {
        foreach ($dokumen as $document) {
            $bahan = $this->bahan($document, $documentService);
            $penanda = preg_match_all(RumusPenanda::POLA, $bahan['text']);

            $this->line('#'.$document->id.' '.$document->original_name.':'
                .' '.$penanda.' penanda, '.count($bahan['chunks']).' chunk, '
                .$document->formulaReviews()->count().' review ('
                .$document->formulaReviews()->where('status', DocumentFormulaReview::STATUS_APPROVED)->count().' disetujui)');
        }

        $this->newLine();
        $this->info('Dry run: tidak ada yang ditulis.');

        return self::SUCCESS;
    }

    /** @return array{tambal: int, penanda: int, tambah: int} */
    private function pasangRumus(Document $document, EmbeddingService $embedding, RumusPerbaikanChunk $perbaikan): array
    {
        $reviews = $document->formulaReviews()->where('status', DocumentFormulaReview::STATUS_APPROVED)
            ->whereNotNull('latex')->orderBy('id')->get();

        if ($reviews->isEmpty()) {
            return ['tambal' => 0, 'penanda' => 0, 'tambah' => 0];
        }

        $rencana = $perbaikan->rencanakan($reviews->all(), $document->chunks()->orderBy('id')->get());
        $tambal = $rencana['konten'];
        $mandiri = $reviews->whereIn('id', $rencana['mandiri']);
        $tambah = [];

        foreach ($mandiri as $ulasan) {
            $tambah[$ulasan->id] = RumusPerbaikanChunk::isiMandiri($ulasan);
        }

        $vektor = $embedding->embedBatch([...array_values($tambal), ...array_values($tambah)]);

        if (count($vektor) !== count($tambal) + count($tambah)
            || collect($vektor)->contains(fn ($v) => ! is_array($v) || $v === [])) {
            throw new RuntimeException('Embedding rumus tidak tersedia.');
        }

        $nomor = 0;

        foreach ($tambal as $id => $content) {
            DocumentChunk::whereKey($id)->update(['content' => $content, 'embedding' => $vektor[$nomor++]]);
        }

        $index = (int) $document->chunks()->max('chunk_index');

        foreach ($mandiri as $ulasan) {
            $baru = $document->chunks()->create([
                'content' => $tambah[$ulasan->id],
                'embedding' => $vektor[$nomor++],
                'chunk_index' => ++$index,
                'kategori' => $document->kategori,
                'page_start' => $ulasan->page,
                'page_end' => $ulasan->page,
            ]);
            $ulasan->update(['document_chunk_id' => $baru->id]);
        }

        foreach ($reviews as $ulasan) {
            $tersentuh = $rencana['tambalan'][$ulasan->id] ?? null;

            $ulasan->update([
                'repaired_chunk_ids' => $tersentuh,
                'repaired_via_marker' => $tersentuh === null ? null : in_array((int) $ulasan->id, $rencana['penanda'], true),
            ]);
        }

        return ['tambal' => count($rencana['tambalan']), 'penanda' => count($rencana['penanda']), 'tambah' => count($tambah)];
    }

    /**
     * Ingest lewat jalur yang sama dengan upload, tanpa menjadwalkan pindai
     * rumus: guru yang menekan tombol pindai sesudahnya.
     */
    private function tanam(Document $document, DocumentService $documentService, EmbeddingService $embedding): int
    {
        $job = new ProcessDocument($document);
        $job->deteksi = false;
        $document->update(['status' => 'processing']);

        try {
            $jumlah = $job->ingest($documentService, $embedding);
        } catch (Throwable $e) {
            // Chunk lama masih utuh bila kegagalan terjadi sebelum penulisannya.
            if ($job->sudahMenghapus) {
                $document->chunks()->delete();
            }
            $document->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            throw $e;
        }

        $document->update(['status' => 'completed', 'error_message' => null]);

        return $jumlah;
    }

    /** @return array{pages: list<array{page: int, text: string}>, text: string, chunks: list<array<string, mixed>>} */
    private function bahan(Document $document, DocumentService $documentService): array
    {
        $path = Storage::disk('public')->path($document->path);

        if (! is_file($path)) {
            throw new RuntimeException('File PDF tidak ditemukan: '.$path);
        }

        return (new ProcessDocument($document))->bahan(
            $documentService,
            'pdf',
            $path,
            (int) config('services.rag.chunk_size', 1000),
            (int) config('services.rag.chunk_overlap', 200),
        );
    }
}
