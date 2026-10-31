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

// Get handler
$handler = $session->getHandler();

// Manually call handler write with the same parameters
$sessionId = $session->getId();
$data = json_encode($session->all());

echo 'Session ID: ' . $sessionId . PHP_EOL;
echo 'Data: ' . $data . PHP_EOL;

// Call handler write
$result = $handler->write($sessionId, $data);
echo 'Handler write result: ' . ($result ? 'true' : 'false') . PHP_EOL;

// Check cache store directly
$cache = app('cache')->store('session');
$cacheResult = $cache->put($sessionId, $data, 7200); // 120 minutes = 7200 seconds
echo 'Cache put result: ' . ($cacheResult ? 'true' : 'false') . PHP_EOL;

// Check Redis directly
$redis = app('redis')->connection('cache');
$key = 'simba-cache-session-' . $sessionId;
$redisData = $redis->get($key);
echo "Redis key: $key" . PHP_EOL;
echo "Redis data: " . ($redisData ?? 'NOT FOUND') . PHP_EOL;