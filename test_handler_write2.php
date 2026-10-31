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

// Check if handler is CacheBasedSessionHandler
if ($handler instanceof \Illuminate\Session\CacheBasedSessionHandler) {
    echo 'Using CacheBasedSessionHandler' . PHP_EOL;
    
    // Get the cache store from the handler
    $reflection = new ReflectionClass($handler);
    $property = $reflection->getProperty('cache');
    $property->setAccessible(true);
    $cache = $property->getValue($handler);
    echo 'Handler cache: ' . get_class($cache) . PHP_EOL;
    
    // Check the underlying store
    $store = $cache->getStore();
    echo 'Handler cache store: ' . get_class($store) . PHP_EOL;
    
    if (method_exists($store, 'connection')) {
        $redis = $store->connection();
        echo 'Handler Redis connection: ' . get_class($redis) . PHP_EOL;
    }
}

// Save through session
$session->save();
echo 'Session saved' . PHP_EOL;

// Check Redis directly with the cache connection
$redis = app('redis')->connection('cache');
$sessionId = $session->getId();
$key = 'simba-cache-session-' . $sessionId;
$data = $redis->get($key);
echo "Redis key: $key" . PHP_EOL;
echo "Redis data: " . ($data ?? 'NOT FOUND') . PHP_EOL;