<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$kernel = app('router')->getMiddlewareGroups();
echo 'Web middleware group:' . PHP_EOL;
print_r($kernel['web'] ?? 'Not found');

echo PHP_EOL . 'API middleware group:' . PHP_EOL;
print_r($kernel['api'] ?? 'Not found');