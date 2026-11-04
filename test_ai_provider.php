<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test AIProviderService
$ai = app(App\Services\AIProviderService::class);

echo "Testing AIProviderService...\n";
echo "sendLocalPreferred:\n";

try {
    $result = $ai->sendLocalPreferred("Apa itu fisika?");
    echo "Result: $result\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}