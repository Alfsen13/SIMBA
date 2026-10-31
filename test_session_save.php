<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate a request
$request = Illuminate\Http\Request::create('/test', 'GET');
$app->instance('request', $request);

// Start session
$session = app('session');
$session->start();

echo 'Session ID: ' . $session->getId() . PHP_EOL;
echo 'Session started: ' . ($session->isStarted() ? 'yes' : 'no') . PHP_EOL;

// Put some data
$session->put('test', 'value');
echo 'Data put in session' . PHP_EOL;

// Save session
$session->save();
echo 'Session saved' . PHP_EOL;

// Check Redis
$redis = app('redis')->connection();
$sessionId = $session->getId();
$key = 'simba-database-simba-cache-session_' . $sessionId;
$data = $redis->get($key);
echo 'Redis key: ' . $key . PHP_EOL;
echo 'Redis data: ' . $data . PHP_EOL;

// Try with the cache prefix
$cache = app('cache')->store('session');
$cacheKey = 'simba-cache-session_' . $sessionId;
$data2 = $redis->get('simba-database-' . $cacheKey);
echo 'Cache key: ' . $cacheKey . PHP_EOL;
echo 'Redis data (cache): ' . $data2 . PHP_EOL;