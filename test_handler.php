<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$handler = app('session')->getHandler();
echo 'Handler class: ' . get_class($handler) . PHP_EOL;

$redis = app('redis')->connection();
echo 'Redis connected: ' . ($redis->ping() ? 'yes' : 'no') . PHP_EOL;