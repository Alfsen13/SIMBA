<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test EncryptCookies middleware directly
$encrypter = app('encrypter');
$encryptCookies = new \Illuminate\Cookie\Middleware\EncryptCookies($encrypter);

// Check the except array
$reflection = new ReflectionClass($encryptCookies);
$property = $reflection->getProperty('except');
$property->setAccessible(true);
$except = $property->getValue($encryptCookies);

echo 'EncryptCookies except array:' . PHP_EOL;
print_r($except);

// Check static neverEncrypt array
$reflection = new ReflectionClass(\Illuminate\Cookie\Middleware\EncryptCookies::class);
$property = $reflection->getProperty('neverEncrypt');
$property->setAccessible(true);
$neverEncrypt = $property->getValue($encryptCookies);

echo PHP_EOL . 'EncryptCookies static neverEncrypt array:' . PHP_EOL;
print_r($neverEncrypt);

// Test isDisabled for our cookie names
$testNames = ['laravel_session', 'laravel-session', 'XSRF-TOKEN'];
foreach ($testNames as $name) {
    $result = $encryptCookies->isDisabled($name);
    echo "isDisabled('$name'): " . ($result ? 'true' : 'false') . PHP_EOL;
}