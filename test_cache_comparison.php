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

// Get handler
$handler = $store->getHandler();

// Get handler's cache
$reflection = new ReflectionClass($handler);
$property = $reflection->getProperty('cache');
$property->setAccessible(true);
$handlerCache = $property->getValue($handler);

echo 'Handler cache: ' . get_class($handlerCache) . PHP_EOL;

// Get cache store from container
$containerCache = app('cache')->store('session');
echo 'Container cache: ' . get_class($containerCache) . PHP_EOL;

// Are they the same object?
echo 'Same object: ' . ($handlerCache === $containerCache ? 'true' : 'false') . PHP_EOL;

// Test putting via handler's cache
$sessionId = $store->getId();
$attributes = $store->all();
$serialized = json_encode($attributes);

$result = $handlerCache->put($sessionId, $serialized, 7200);
echo 'Handler cache put: ' . ($result ? 'true' : 'false') . PHP_EOL;

// Test putting via container cache
$result = $containerCache->put($sessionId . '_2', $serialized, 7200);
echo 'Container cache put: ' . ($result ? 'true' : 'false') . PHP_EOL;

// Check Redis
$redis = app('redis')->connection('cache');
$key1 = 'simba-cache-session-' . $sessionId;
$key2 = 'simba-cache-session-' . $sessionId . '_2';
$data1 = app('redis')->connection('cache')->get($key1);
$data2 = app('redis')->connection('cache')->get($key2);
echo "Redis key1: " . ($data1 ?? 'NOT FOUND') . PHP_EOL;
echo "Redis key2: " . ($data2 ?? 'NOT FOUND') . PHP_EOL;