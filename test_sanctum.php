<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = \Illuminate\Http\Request::create('http://localhost:8000/api/chat/start', 'POST', [], [], [], [
    'HTTP_REFERER' => 'http://localhost:8000/pelajar/ai-tutor/kelas/10/pertemuan/1',
    'HTTP_ORIGIN' => 'http://localhost:8000',
]);

$result = \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::fromFrontend($request);
echo 'fromFrontend: ' . ($result ? 'true' : 'false') . PHP_EOL;
echo 'stateful domains: ' . json_encode(config('sanctum.stateful')) . PHP_EOL;