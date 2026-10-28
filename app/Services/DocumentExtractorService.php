<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Smalot\PdfParser\Parser;

 
class DocumentExtractorService
{
    protected string $pythonPath;

    protected string $scriptPath;

    protected int $timeout;

    public function __construct()
    {
        $this->pythonPath = config('services.pdf.python_path', 'py -X utf8');
        $this->scriptPath = base_path('app/Python/extract_text.py');
        $this->timeout = (int) config('services.pdf.timeout', 300);
    }

     
    public function extract(string $pdfPath): array
    {
        if (! file_exists($pdfPath)) {
            throw new RuntimeException("PDF tidak ditemukan: {$pdfPath}");
        }

        try {
            return $this->extractViaPython($pdfPath);
        } catch (\Throwable $e) {
            Log::warning('DocumentExtractorService::python gagal, fallback smalot: {msg}', [
                'msg' => $e->getMessage(),
            ]);

            return $this->extractViaSmalot($pdfPath);
        }
    }

     
    public function extractPages(string $pdfPath): array
    {
        if (! file_exists($pdfPath)) {
            throw new RuntimeException("PDF tidak ditemukan: {$pdfPath}");
        }

        try {
            $result = $this->extractViaPython($pdfPath);
            if (isset($result['pages_detail']) && is_array($result['pages_detail']) && ! empty($result['pages_detail'])) {
                return [
                    'text' => $result['text'],
                    'pages' => $result['pages_detail'],
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentExtractorService::extractPages python gagal: {msg}', [
                'msg' => $e->getMessage(),
            ]);
        }

        return $this->extractPagesViaSmalot($pdfPath);
    }

    protected function extractViaPython(string $pdfPath): array
    {
        $script = $this->scriptPath;
        $quotedScript = escapeshellarg($script);
        $quotedPdf = escapeshellarg($pdfPath);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "{$this->pythonPath} {$quotedScript} {$quotedPdf} 2>NUL";
        } else {
            $command = "{$this->pythonPath} {$quotedScript} {$quotedPdf} 2>/dev/null";
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open($command, $descriptors, $pipes);

        if (! is_resource($proc)) {
            throw new RuntimeException("Gagal memulai proses Python ekstraktor.");
        }

        $started = microtime(true);
        $output = '';
        $timeout = $this->timeout;
        $timedOut = false;

        while (true) {
            $read = [$pipes[1]];
            $write = null;
            $except = null;

            $changed = @stream_select($read, $write, $except, 1, 0);

            if ($changed !== false && ! empty($read)) {
                $chunk = @fread($pipes[1], 8192);
                if ($chunk !== false && $chunk !== '') {
                    $output .= $chunk;
                }
            }

            $procStatus = @proc_get_status($proc);

            if (! $procStatus || ! $procStatus['running']) {
                break;
            }

            if (microtime(true) - $started > $timeout) {
                $timedOut = true;
                @proc_terminate($proc);
                usleep(300000);
                @proc_terminate($proc, 9);
                break;
            }
        }

        $stderr = @stream_get_contents($pipes[2]);
        @fclose($pipes[0]);
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        $exitCode = @proc_close($proc);

        if ($timedOut) {
            throw new RuntimeException("Python ekstraktor melebihi batas waktu {$timeout} detik.");
        }

        $raw = $output;
        $json = $this->extractJsonFromOutput($raw);

        if ($exitCode !== 0 || empty($json)) {
            throw new RuntimeException("Python ekstraktor gagal (exit={$exitCode}): ".mb_substr($raw.(string) $stderr, 0, 500));
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            throw new RuntimeException('Output JSON tidak valid: '.mb_substr($json, 0, 500));
        }

        $elapsed = microtime(true) - $started;
        Log::info('DocumentExtractorService python selesai: {method} ({reason}) dalam {t}s, {len} chars', [
            'method' => $data['chosen_method'] ?? '?',
            'reason' => $data['reason'] ?? '?',
            't' => round($elapsed, 1),
            'len' => $data['text_length'] ?? 0,
        ]);

        return $data;
    }

    protected function extractJsonFromOutput(string $output): string
    {

        $start = strpos($output, '{');
        if ($start === false) {
            return '';
        }

        $depth = 0;
        $inString = false;
        $escaped = false;
        $len = strlen($output);

        for ($i = $start; $i < $len; $i++) {
            $ch = $output[$i];
            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($ch === '\\') {
                    $escaped = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }

                continue;
            }
            if ($ch === '"') {
                $inString = true;
            } elseif ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    return mb_substr($output, $start, $i - $start + 1);
                }
            }
        }

        return '';
    }

    protected function extractViaSmalot(string $pdfPath): array
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();
        $text = $this->normalize($text);

        return [
            'chosen_method' => 'smalot',
            'text' => $text,
            'text_length' => mb_strlen($text),
            'candidates' => [],
            'pages_detail' => [],
        ];
    }

     
    protected function extractPagesViaSmalot(string $pdfPath): array
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);
        $pages = $pdf->getPages();

        $result = [];
        $full = [];
        foreach ($pages as $pageNum => $page) {
            $text = $this->normalize($page->getText());
            if (! empty($text)) {
                $result[] = ['page' => $pageNum + 1, 'text' => $text];
                $full[] = $text;
            }
        }

        return [
            'text' => implode("\n\n", $full),
            'pages' => $result,
        ];
    }

    protected function normalize(string $text): string
    {
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', '', $text);

        return trim($text);
    }
}

