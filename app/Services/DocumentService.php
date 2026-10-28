<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DocumentService
{
    public function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'pdf' => $this->extractFromPdf($file),
            'txt', 'md', 'csv' => $file->getContent(),
            'docx' => $this->extractFromDocxString($file->get()),
            default => throw new RuntimeException("Tipe file {$extension} tidak didukung."),
        };
    }

    public function extractFromPdfFile(string $path): string
    {
        $extractor = app(DocumentExtractorService::class);
        $result = $extractor->extract($path);

        return $result['text'];
    }

    protected function extractFromPdf(UploadedFile $file): string
    {
        try {

            $tmp = tempnam(sys_get_temp_dir(), 'simba_pdf_');
            file_put_contents($tmp, $file->get());

            try {
                $extractor = app(DocumentExtractorService::class);
                $result = $extractor->extract($tmp);

                return $result['text'];
            } finally {
                unlink($tmp);
            }
        } catch (\Throwable $e) {
            Log::warning('PDF parse gagal: {msg}', ['msg' => $e->getMessage()]);
            throw new RuntimeException('Gagal membaca PDF: '.$e->getMessage());
        }
    }

    public function extractFromDocxString(string $content): string
    {
        $zip = new \ZipArchive;
        $tmpPath = tempnam(sys_get_temp_dir(), 'docx_');
        file_put_contents($tmpPath, $content);
        $ok = $zip->open($tmpPath);

        if ($ok !== true) {
            unlink($tmpPath);
            throw new RuntimeException('Gagal membuka file DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($tmpPath);

        if ($xml === false) {
            throw new RuntimeException('Tidak dapat membaca konten DOCX.');
        }

        $text = strip_tags($xml);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    public function extractPagesFromPdfFile(string $path): array
    {
        try {
            $extractor = app(DocumentExtractorService::class);
            $result = $extractor->extractPages($path);

            return $result['pages'];
        } catch (\Throwable $e) {
            Log::warning('PDF parse file gagal: {msg}', ['msg' => $e->getMessage()]);
            throw new RuntimeException('Gagal membaca PDF: '.$e->getMessage());
        }
    }

    public function extractPagesFromPdfContent(string $content): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'simba_pdf_');
        file_put_contents($tmp, $content);

        try {
            return $this->extractPagesFromPdfFile($tmp);
        } finally {
            unlink($tmp);
        }
    }

    public function chunkTextWithPages(array $pages, int $chunkSize = 1000, int $overlap = 200): array
    {
        $allChunks = [];

        foreach ($pages as $pageData) {
            $pageNum = $pageData['page'];
            $paragraphs = explode("\n", $pageData['text']);
            $paragraphs = array_filter($paragraphs, fn ($p) => trim($p) !== '');
            $paragraphs = array_values($paragraphs);

            $currentChunk = '';

            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);

                if (mb_strlen($currentChunk) + mb_strlen($paragraph) + 1 <= $chunkSize) {
                    $currentChunk .= ($currentChunk === '' ? '' : "\n").$paragraph;

                    continue;
                }

                if ($currentChunk !== '') {
                    $allChunks[] = ['content' => $currentChunk, 'page_start' => $pageNum, 'page_end' => $pageNum];
                }

                $words = explode(' ', $currentChunk);
                $overlapText = '';
                $overlapCount = 0;
                foreach (array_reverse($words) as $word) {
                    if ($overlapCount + mb_strlen($word) + 1 > $overlap) {
                        break;
                    }
                    $overlapText = $word.' '.$overlapText;
                    $overlapCount += mb_strlen($word) + 1;
                }

                $currentChunk = trim($overlapText)."\n".$paragraph;
            }

            if ($currentChunk !== '') {
                $allChunks[] = ['content' => $currentChunk, 'page_start' => $pageNum, 'page_end' => $pageNum];
            }
        }

        $indexed = [];
        foreach ($allChunks as $i => $chunk) {
            $indexed[] = [
                'index' => $i,
                'content' => $chunk['content'],
                'page_start' => $chunk['page_start'],
                'page_end' => $chunk['page_end'],
            ];
        }

        return $indexed;
    }

    public function chunkText(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        if (empty(trim($text))) {
            return [];
        }

        $paragraphs = explode("\n", $text);
        $paragraphs = array_filter($paragraphs, fn ($p) => trim($p) !== '');
        $paragraphs = array_values($paragraphs);

        $chunks = [];
        $currentChunk = '';
        $index = 0;

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if (mb_strlen($currentChunk) + mb_strlen($paragraph) + 1 <= $chunkSize) {
                $currentChunk .= ($currentChunk === '' ? '' : "\n").$paragraph;

                continue;
            }

            $chunks[] = $currentChunk;

            $words = explode(' ', $currentChunk);
            $overlapText = '';

            $overlapCount = 0;
            foreach (array_reverse($words) as $word) {
                if ($overlapCount + mb_strlen($word) + 1 > $overlap) {
                    break;
                }
                $overlapText = $word.' '.$overlapText;
                $overlapCount += mb_strlen($word) + 1;
            }

            $currentChunk = trim($overlapText)."\n".$paragraph;
        }

        if ($currentChunk !== '') {
            $chunks[] = $currentChunk;
        }

        $indexedChunks = [];
        foreach ($chunks as $i => $chunk) {
            $indexedChunks[] = [
                'index' => $i,
                'content' => $chunk,
                'page_start' => null,
                'page_end' => null,
            ];
        }

        return $indexedChunks;
    }
}

