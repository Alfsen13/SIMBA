<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test session decryption with URL decoding
$encrypted = urldecode('eyJpdiI6ImFRYW9Za2xWRXQvcy9ITThZZWY5ekE9PSIsInZhbHVlIjoiQ2ErOE1NZ1pGUlRtSEF2WkVYNHp6RlJlYWtNSi9nM2tINVRDRXd1dk1BZ0xhUkV4WUxCaDBwVHRrVmFOM05NZXR2aVk1ZmJFOXFQZ2tWSXFFUWZiWG14eE1YYUpxb0EvZFdncXlQTmVlN1l0Ym80eTJQd2dRZU1VSFpzQ3p2RTVlZ1B3MWpTR0VlMVlkZDFyNFk2TDRoUTZkbnNWNmN2VURDSHFpQjBGZVppV3lDYTJiVG5jNjVyQkFjaGhZN3pIRGVwOVJ5SVBQS0FvQUZsZ0xFZE1rTXJZTEh0MkdKcFdWYnpHNUQ1ckxVcU15RU9hY2hFbnVlZ0dRPT0iLCJtYWMiOiJhZDIwMTgwMjJhNzY4ZjI1ZjA1NjUyYzM3MjU3OWY3ZDAwYjVhODcxYzU4N2MwMzU4ZTg3MTQyY2M1Nzc4NWY2NDVhMzMwIn0%3D');

try {
    $decrypted = app('encrypter')->decrypt($encrypted);
    echo 'Decrypted: ' . $decrypted . PHP_EOL;

    // Check if session exists in Redis
    $redis = app('redis')->connection();
    $sessionKey = 'laravel_database_laravel_cache_session_' . $decrypted;
    $data = $redis->get($sessionKey);
    echo 'Session data: ' . $data . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}