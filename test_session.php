<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

session(['test' => 'value']);
echo 'Session test: ' . session('test') . PHP_EOL;

$redis = app('redis')->connection();
$keys = $redis->keys('laravel_database_*session*');
echo 'Session keys in Redis: ' . count($keys) . PHP_EOL;