<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $redis = app('redis')->connection();
    echo 'Redis connection: ' . get_class($redis) . PHP_EOL;
    echo 'Redis ping: ' . ($redis->ping() ? 'yes' : 'no') . PHP_EOL;

    $handler = new \Illuminate\Session\RedisSessionHandler($redis, 'laravel_session', 120);
    echo 'RedisSessionHandler created successfully' . PHP_EOL;
    echo 'Handler class: ' . get_class($handler) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
}