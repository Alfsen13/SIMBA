<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the cache store directly
$cache = app('cache')->store('session');
echo 'Cache store: ' . get_class($cache) . PHP_EOL;

// Test putting data
$result = $cache->put('test_key', 'test_value', 60);
echo 'Put result: ' . ($result ? 'true' : 'false') . PHP_EOL;

// Test getting data
$value = $cache->get('test_key');
echo 'Get value: ' . $value . PHP_EOL;

// Test Redis directly
$redis = app('redis')->connection('cache');
echo 'Redis connection: ' . get_class($redis) . PHP_EOL;
echo 'Redis ping: ' . ($redis->ping() ? 'yes' : 'no') . PHP_EOL;

// Try to set a key directly
$redis->set('test_direct', 'direct_value');
$direct = $redis->get('test_direct');
echo 'Direct Redis get: ' . $direct . PHP_EOL;