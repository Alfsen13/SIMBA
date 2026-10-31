<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test session decryption
$encrypted = 'eyJpdiI6IjRQRkFMdDNhWDN1ZWtmZGZqc2FUT1E9PSIsInZhbHVlIjoiS3dGWnJBd3R5ZWFOUHhaa2wvU2NvWHNuN
GxyKy8wVlZLVVlULzFPWkUzckdHeUN0NjdDbWxUL2tFQjNIRDRDRDZENlRyZjJpeTBkREpFbHRzSDFBQ0ltUy82NFB3Ty9ucUoreHpBNklOUDdybTkyUkNX
UTJLOG1OVzM5YnZ2RjgiLCJtYWMiOiI2YWVjZWFlMWQ5YTc5YWI3NjliNzAyMTE0YjAxZDQ2MmVlZTg3ODMxMjc1YWFkOGJiOTYwODJmZTI5NTliZTY0Iiw
idGFnIjoiIn0%3D';

$decrypted = app('encrypter')->decrypt($encrypted);
echo 'Decrypted: ' . $decrypted . PHP_EOL;

// Check if session exists in Redis
$redis = app('redis')->connection();
$sessionKey = 'laravel_database_laravel_cache_session_' . $decrypted;
$data = $redis->get($key);
echo 'Session data: ' . $data . PHP_EOL;