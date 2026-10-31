<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cache = app('cache')->store('session');
echo 'Store class: ' . get_class($cache) . PHP_EOL;

$redis = $cache->getRedis();
echo 'Redis class: ' . get_class($redis) . PHP_EOL;
echo 'Redis ping: ' . ($redis->ping() ? 'yes' : 'no') . PHP_EOL;