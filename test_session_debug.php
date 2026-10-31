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

echo 'Store: ' . get_class($store) . PHP_EOL;
$store->start();

echo 'Session ID: ' . $store->getId() . PHP_EOL;

// Put data
$store->put('test', 'value');
echo 'Data put in session' . PHP_EOL;

echo 'store->all(): ' . json_encode($store->all()) . PHP_EOL;

// Use reflection to access protected attributes
$reflection = new ReflectionClass($store);
$property = $reflection->getProperty('attributes');
$property->setAccessible(true);
$attributes = $property->getValue($store);
echo 'store->attributes: ' . json_encode($attributes) . PHP_EOL;

// Check serialization
$property = $reflection->getProperty('serialization');
$property->setAccessible(true);
$serialization = $property->getValue($store);
echo 'serialization: ' . $serialization . PHP_EOL;

// Call session save
$store->save();
echo 'Session saved' . PHP_EOL;

// Check Redis
$redis = app('redis')->connection('cache');
$sessionId = $store->getId();
$key = 'simba-cache-session-' . $sessionId;
$data = $redis->get($key);
echo "Redis key: $key" . PHP_EOL;
echo "Redis data: " . ($data ?? 'NOT FOUND') . PHP_EOL;