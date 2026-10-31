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

// Manually call handler write
$handler = $session->getHandler();
echo 'Handler: ' . get_class($handler) . PHP_EOL;

$data = $session->all();
echo 'Session data: ' . json_encode($data) . PHP_EOL;

$serialized = json_encode($data);
echo 'Serialized: ' . $serialized . PHP_EOL;

$result = $handler->write($session->getId(), $serialized);
echo 'Handler write result: ' . ($result ? 'true' : 'false') . PHP_EOL;

// Check Redis
$redis = app('redis')->connection('cache');
$sessionId = $session->getId();
$key = 'simba-cache-session-' . $sessionId;
$data = $redis->get($key);
echo "Redis key: $key" . PHP_EOL;
echo "Redis data: " . ($data ?? 'NOT FOUND') . PHP_EOL;