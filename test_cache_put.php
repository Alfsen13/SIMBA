<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check the cache store directly
$cache = app('cache')->store('session');
echo 'Cache store: ' . get_class($cache) . PHP_EOL;

$store = $cache->getStore();
echo 'Underlying store: ' . get_class($store) . PHP_EOL;

if (method_exists($store, 'connection')) {
    $redis = $store->connection();
    echo 'Redis connection: ' . get_class($redis) . PHP_EOL;
}

// Test putting directly
$result = $cache->put('direct_test', 'direct_value', 60);
echo 'Direct put: ' . ($result ? 'true' : 'false') . PHP_EOL;

$value = $cache->get('direct_test');
echo 'Direct get: ' . $value . PHP_EOL;

// Check Redis directly
$redis = app('redis')->connection('cache');
$data = $redis->get('simba-cache-session-direct_test');
echo 'Redis direct_test: ' . ($data ?? 'NOT FOUND') . PHP_EOL;