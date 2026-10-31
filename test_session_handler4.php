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
    
    // Test the cache
    $cache->put('test', 'value', 60);
    echo 'Cache test: ' . $cache->get('test') . PHP_EOL;
}