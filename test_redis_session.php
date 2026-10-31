<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get a valid session from Redis
$redis = app('redis')->connection();
$keys = $redis->keys('laravel-database-laravel-cache-session-*');
if (count($keys) > 0) {
    $key = $keys[0];
    $sessionData = $redis->get($key);
    echo 'Found session: ' . $key . PHP_EOL;
    echo 'Data: ' . $sessionData . PHP_EOL;
} else {
    echo 'No sessions found' . PHP_EOL;
}