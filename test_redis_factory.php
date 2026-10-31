<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$redis1 = app('redis');
$redis2 = app('redis');

echo 'Redis factory 1: ' . get_class($redis1) . PHP_EOL;
echo 'Redis factory 2: ' . get_class($redis2) . PHP_EOL;
echo 'Same factory: ' . ($redis1 === $redis2 ? 'true' : 'false') . PHP_EOL;

$conn1 = $redis1->connection('cache');
$conn2 = $redis2->connection('cache');

echo 'Connection 1: ' . get_class($conn1) . PHP_EOL;
echo 'Connection 2: ' . get_class($conn2) . PHP_EOL;
echo 'Same connection: ' . ($conn1 === $conn2 ? 'true' : 'false') . PHP_EOL;