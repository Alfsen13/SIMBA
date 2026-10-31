<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'session.driver: ' . config('session.driver') . PHP_EOL;
echo 'session.connection: ' . config('session.connection') . PHP_EOL;
echo 'session.store: ' . config('session.store') . PHP_EOL;