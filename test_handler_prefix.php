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

// Check handler prefix
$handler = $session->getHandler();
if ($handler instanceof \Illuminate\Session\CacheBasedSessionHandler) {
    $reflection = new ReflectionClass($handler);
    $property = $reflection->getProperty('prefix');
    $property->setAccessible(true);
    $prefix = $property->getValue($handler);
    echo 'Handler prefix: "' . $prefix . '"' . PHP_EOL;
    
    $property = $reflection->getProperty('minutes');
    $property->setAccessible(true);
    $minutes = $property->getValue($handler);
    echo 'Handler minutes: ' . $minutes . PHP_EOL;
    
    $property = $reflection->getProperty('cache');
    $property->setAccessible(true);
    $cache = $property->getValue($handler);
    echo 'Handler cache: ' . get_class($cache) . PHP_EOL;
}

// Save through session
$session->save();
echo 'Session saved' . PHP_EOL;

// Check Redis directly with the cache connection
$redis = app('redis')->connection('cache');
$sessionId = $session->getId();
$prefix = ''; // default prefix
$key = $prefix . $sessionId;
$data = $redis->get($key);
echo "Redis key: $key" . PHP_EOL;
echo "Redis data: " . ($data ?? 'NOT FOUND') . PHP_EOL;