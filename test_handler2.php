<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cache = app('cache')->store('session');
echo 'Store class: ' . get_class($cache) . PHP_EOL;

$handler = app('session')->getHandler();
echo 'Handler class: ' . get_class($handler) . PHP_EOL;

if (method_exists($handler, 'getCache')) {
    $cache = $handler->getCache();
    echo 'Handler cache: ' . get_class($cache) . PHP_EOL;
}