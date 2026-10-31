<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'APP_KEY from env(): ' . env('APP_KEY') . PHP_EOL;
echo 'APP_KEY from config(): ' . config('app.key') . PHP_EOL;