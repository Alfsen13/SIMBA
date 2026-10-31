<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cache = app('cache')->store('session');
$redis = $cache->getRedis();
echo 'Redis prefix: ' . $redis->getPrefix() . PHP_EOL;

$handler = app('session')->getHandler();
echo 'Handler: ' . get_class($handler) . PHP_EOL;