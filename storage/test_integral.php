<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Document;
use App\Services\DocumentService;

$docs = Document::where('status', 'completed')->get();
$doc = null;
foreach ($docs as $d) {
    $p = storage_path('app/public/' . $d->path);
    if (file_exists($p)) { $doc = $d; break; }
}
$path = storage_path('app/public/' . $doc->path);
$service = app(DocumentService::class);
$service->extractFromPdfFile($path);

// Extract pages to find integral context
$pages = $service->extractPagesFromPdfFile($path);

echo "=== Integral dalam '{$doc->name}' ===\n\n";

$found = 0;
foreach ($pages as $pageData) {
    $text = $pageData['text'];
    if (mb_strpos($text, '∫') !== false) {
        $found++;
        echo "--- Halaman {$pageData['page']} ---\n";
        // Find lines containing ∫
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            if (mb_strpos($line, '∫') !== false) {
                echo "  " . trim($line) . "\n";
            }
        }
        echo "\n";
    }
}
echo "Total halaman dengan simbol ∫: {$found}\n";
