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

            foreach ($this->chunkParagraphs($paragraphs, $chunkSize, $overlap) as $content) {
                $allChunks[] = ['content' => $content, 'page_start' => $pageNum, 'page_end' => $pageNum];
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

        $indexedChunks = [];
        foreach ($this->chunkParagraphs($paragraphs, $chunkSize, $overlap) as $i => $chunk) {
            $indexedChunks[] = [
                'index' => $i,
                'content' => $chunk,
                'page_start' => null,
                'page_end' => null,
            ];
        }

        return $indexedChunks;
    }

    private function chunkParagraphs(array $paragraphs, int $chunkSize, int $overlap): array
    {
        $chunkSize = max(1, $chunkSize);
        $overlap = max(0, min($overlap, $chunkSize - 1));
        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            $candidate = $currentChunk === '' ? $paragraph : $currentChunk."\n".$paragraph;
            if (mb_strlen($candidate) <= $chunkSize) {
                $currentChunk = $candidate;

                continue;
            }

            if ($currentChunk !== '') {
                $chunks[] = $currentChunk;
            }

            $overlapText = $this->getOverlapText($currentChunk, $overlap);
            $candidate = $overlapText === '' ? $paragraph : $overlapText."\n".$paragraph;
            $segments = $this->splitTextIntoChunks($candidate, $chunkSize, $overlap);

            foreach (array_slice($segments, 0, -1) as $segment) {
                $chunks[] = $segment;
            }
            $currentChunk = end($segments) ?: '';
        }

        if ($currentChunk !== '') {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    private function getOverlapText(string $chunk, int $overlap): string
    {
        if ($chunk === '' || $overlap === 0) {
            return '';
        }

        $overlapText = '';
        $overlapCount = 0;
        foreach (array_reverse(explode(' ', $chunk)) as $word) {
            if ($overlapCount + mb_strlen($word) + 1 > $overlap) {
                break;
            }
            $overlapText = $word.' '.$overlapText;
            $overlapCount += mb_strlen($word) + 1;
        }

        return trim($overlapText);
    }

    private function splitTextIntoChunks(string $text, int $chunkSize, int $overlap): array
    {
        $length = mb_strlen($text);
        $chunks = [];
        $offset = 0;

        while ($offset < $length) {
            if ($length - $offset <= $chunkSize) {
                $terakhir = trim(mb_substr($text, $offset));
                if ($terakhir !== '') {
                    $chunks[] = $terakhir;
                }

                break;
            }

            // Potongan yang jatuh di tengah penanda rumus ditutup sebelum
            // penandanya; chunk berikutnya tetap memuat penanda itu utuh
            // karena overlap selalu lebih panjang dari satu token.
            $batas = $offset + $chunkSize;
            $geser = RumusPenanda::batasSebelumPenanda($text, $batas);
            if ($geser !== null && $geser > $offset) {
                $batas = $geser;
            }

            $chunk = trim(mb_substr($text, $offset, $batas - $offset));
            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            $offset = max($offset + 1, $batas - $overlap);
        }

        return $chunks;
    }
}
