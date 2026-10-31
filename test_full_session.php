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

// Save
$session->save();
echo 'Session saved' . PHP_EOL;

// Check the handler directly
$handler = $session->getHandler();
echo 'Handler: ' . get_class($handler) . PHP_EOL;

// Check if handler is CacheBasedSessionHandler
if ($handler instanceof \Illuminate\Session\CacheBasedSessionHandler) {
    echo 'Using CacheBasedSessionHandler' . PHP_EOL;
    $cache = $handler->getCache();
    echo 'Handler cache: ' . get_class($cache) . PHP_EOL;
    echo 'Cache store name: ' . $cache->getStore() . PHP_EOL;
    
    // Check the underlying Redis store
    $store = $cache->getStore();
    echo 'Underlying store: ' . get_class($store) . PHP_EOL;
    if (method_exists($store, 'getRedis')) {
        $redis = $store->getRedis();
        echo 'Redis connection: ' . get_class($redis) . PHP_EOL;
    }
}