<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a fresh session
$request = Illuminate\Http\Request::create('/test', 'GET');
$app->instance('request', $request);

$session = app('session');
$session->start();

echo 'Session ID: ' . $session->getId() . PHP_EOL;

// Put data
$session->put('test', 'value');
echo 'Data put in session' . PHP_EOL;

// Save
$session->save();
echo 'Session saved' . PHP_EOL;

// Check Redis directly using the cache connection
$redis = app('redis')->connection('cache');
$sessionId = $session->getId();
echo 'Session ID: ' . $sessionId . PHP_EOL;

// Try different prefixes
$prefixes = [
    'simba-cache-session-',
    'simba-database-simba-cache-session-',
    'laravel-database-laravel-cache-session-',
    '',
];

foreach ($prefixes as $prefix) {
    $key = $prefix . $sessionId;
    $data = $redis->get($key);
    if ($data) {
        echo "Found with prefix '$prefix': $key" . PHP_EOL;
        echo "Data: $data" . PHP_EOL;
    }
}