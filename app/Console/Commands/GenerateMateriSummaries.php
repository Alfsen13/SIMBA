<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\MateriBabSummary;
use App\Services\AIProviderService;
use App\Services\DocumentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateMateriSummaries extends Command
{
    protected $signature = 'materi:generate-summaries';

    protected $description = 'Generate ringkasan AI untuk semua materi yang sudah ada';

    public function handle(AIProviderService $ai, DocumentService $documentService): int
    {
        $this->info('Membuat ringkasan untuk dokumen yang sudah ada...');

        $documents = Document::whereNotNull('materi_id')
            ->whereIn('status', ['completed', 'failed'])
            ->get();

        if ($documents->isEmpty()) {
            $this->warn('Tidak ada dokumen dengan materi_id yang perlu diproses.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($documents as $document) {
            try {
                $chunks = $document->chunks()->pluck('content');
                if ($chunks->isNotEmpty()) {
                    $text = $chunks->implode("\n\n");
                } else {
                    $path = Storage::disk('public')->path($document->path);
                    if (! file_exists($path)) {
                        throw new \RuntimeException('File tidak ditemukan: '.$path);
                    }
                    $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
                    $text = match ($extension) {
                        'pdf' => $documentService->extractFromPdfFile($path),
                        'txt', 'md', 'csv' => file_get_contents($path),
                        'docx' => ProcessDocument::extractFromDocx($path),
                        'pptx' => ProcessDocument::extractFromZipXml($path, 'ppt/slides/'),
                        'xlsx' => ProcessDocument::extractFromZipXml($path, 'xl/sharedStrings.xml', 'xl/worksheets/'),
                        default => throw new \RuntimeException("Tipe file {$extension} tidak didukung."),
                    };
                }

                if (empty(trim($text))) {
                    throw new \RuntimeException('Teks kosong.');
                }

                $kategoriDir = $document->kategori ? 'summaries/'.$document->kategori : 'summaries';
                $textPath = $kategoriDir.'/materi_'.$document->materi_id.'.txt';
                Storage::disk('public')->put($textPath, $text);

                $ringkasan = $this->generateSummary($text, $ai);
                MateriBabSummary::updateOrCreate(
                    ['materi_id' => $document->materi_id],
                    ['ringkasan' => $ringkasan],
                );

                $success++;
            } catch (Throwable $e) {
                Log::error('GenerateMateriSummaries gagal untuk dokumen {id}: {msg}', [
                    'id' => $document->id,
                    'msg' => $e->getMessage(),
                ]);
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. Berhasil: {$success}, Gagal: {$failed}");

        return Command::SUCCESS;
    }

    protected function generateSummary(string $text, AIProviderService $ai): string
    {
        if (mb_strlen($text) <= 500) {
            return $text;
        }

        try {
            $prompt = "Buatlah ringkasan singkat (maksimal 3 paragraf) dari teks materi pembelajaran berikut. Gunakan bahasa yang jelas dan mudah dipahami.\n\nTeks:\n".mb_substr($text, 0, 8000);
            $summary = $ai->sendLocalPreferred($prompt, [], 'Anda adalah asisten yang membuat ringkasan materi pembelajaran.');

            if (mb_strlen($summary) > 10) {
                return $summary;
            }
        } catch (Throwable $e) {
            Log::warning('GenerateMateriSummaries: AI summary gagal, fallback ke teks awal: {msg}', ['msg' => $e->getMessage()]);
        }

        return mb_substr($text, 0, 1000);
    }
}
