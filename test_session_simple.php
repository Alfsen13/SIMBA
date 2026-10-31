<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test session directly
$session = app('session');
echo 'Session ID: ' . $session->getId() . PHP_EOL;
echo 'Session data: ' . json_encode($session->all()) . PHP_EOL;

// Test session store
$store = $session->getHandler();
echo 'Handler: ' . get_class($store) . PHP_EOL;

// Test reading a session from Redis
$redis = app('redis')->connection();
$keys = $redis->keys('laravel-database-laravel-cache-session-*');
if (count($keys) > 0) {
    $key = $keys[0];
    $data = $redis->get($key);
    echo 'First session key: ' . $key . PHP_EOL;
    echo 'Data: ' . $data . PHP_EOL;
} else {
    echo 'No sessions found' . PHP_EOL;
}