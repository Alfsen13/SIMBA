<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate a request with session cookie
$cookie = 'laravel-session=eyJpdiI6IjRQRkFMdDNhWDN1ZWtmZGZqc2FUT1E9PSIsInZhbHVlIjoiS3dGWnJBd3R5ZWFOUHhaa2wvU2NvWHNuN
GxyKy8wVlZLVVlULzFPWkUzckdHeUN0NjdDbWxUL2tFQjNIRDRDRDZENlRyZjJpeTBkREpFbHRzSDFBQ0ltUy82NFB3Ty9ucUoreHpBNklOUDdybTkyUkNX
UTJLOG1OVzM5YnZ2RjgiLCJtYWMiOiI2YWVjZWFlMWQ5YTc5YWI3NjliNzAyMTE0YjAxZDQ2MmVlZTg3ODMxMjc1YWFkOGJiOTYwODJmZTI5NTliZTY0Iiw
idGFnIjoiIn0%3D';

$request = Illuminate\Http\Request::create('/', 'GET', [], [], [
    'laravel-session' => $cookie,
], []);

$app->instance('request', $request);

// Boot the kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test session
$session = $request->session();
echo 'Session ID: ' . $session->getId() . PHP_EOL;
echo 'Session data: ' . json_encode($session->all()) . PHP_EOL;

// Check if user is authenticated
$user = Illuminate\Support\Facades\Auth::user();
if ($user) {
    echo 'Authenticated as: ' . $user->email . PHP_EOL;
} else {
    echo 'Not authenticated' . PHP_EOL;
}