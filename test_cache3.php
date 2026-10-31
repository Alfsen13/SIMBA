<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cacheManager = app('cache');
echo 'Cache manager: ' . get_class($cacheManager) . PHP_EOL;

// Try to flush the cache manager
if (method_exists($cacheManager, 'flush')) {
    $cacheManager->flush();
    echo 'Flushed cache manager' . PHP_EOL;
}

// Try to get the session store again
$cache = app('cache')->store('session');
echo 'Store class: ' . get_class($cache) . PHP_EOL;

$redis = $cache->getRedis();
echo 'Redis class: ' . get_class($redis) . PHP_EOL;
echo 'Redis ping: ' . ($redis->ping() ? 'yes' : 'no') . PHP_EOL;