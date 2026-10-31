<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a fresh session
$request = Illuminate\Http\Request::create('/test', 'GET');
$app->instance('request', $request);

// Get the actual store from the manager
$manager = app('session');
$store = $manager->driver();
$store->start();

echo 'Session ID: ' . $store->getId() . PHP_EOL;

// Put data
$store->put('test', 'value');
echo 'Data put in session' . PHP_EOL;

// Get handler
$handler = $store->getHandler();
echo 'Handler: ' . get_class($handler) . PHP_EOL;

// Manually call handler write with the same data as save()
$sessionId = $store->getId();
$attributes = $store->all();
$serialized = json_encode($attributes);

echo 'Session ID: ' . $sessionId . PHP_EOL;
echo 'Attributes: ' . json_encode($attributes) . PHP_EOL;
echo 'Serialized: ' . $serialized . PHP_EOL;

// Call handler write
$result = $handler->write($sessionId, $serialized);
echo 'Handler write result: ' . ($result ? 'true' : 'false') . PHP_EOL;

// Check Redis
$redis = app('redis')->connection('cache');
$key = 'simba-cache-session-' . $sessionId;
$data = $redis->get($key);
echo "Redis key: $key" . PHP_EOL;
echo "Redis data: " . ($data ?? 'NOT FOUND') . PHP_EOL;