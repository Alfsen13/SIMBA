<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$manager = app('session');
echo 'Session manager: ' . get_class($manager) . PHP_EOL;

$handler = $manager->getHandler();
echo 'Handler: ' . get_class($handler) . PHP_EOL;

if ($handler instanceof \Illuminate\Session\CacheBasedSessionHandler) {
    echo 'Using CacheBasedSessionHandler' . PHP_EOL;
    $cache = $handler->getCache();
    echo 'Cache: ' . get_class($cache) . PHP_EOL;
} elseif ($handler instanceof \Illuminate\Session\RedisSessionHandler) {
    echo 'Using RedisSessionHandler' . PHP_EOL;
    $redis = $handler->getRedis();
    echo 'Redis: ' . get_class($redis) . PHP_EOL;
    echo 'Redis prefix: ' . $redis->getPrefix() . PHP_EOL;
}