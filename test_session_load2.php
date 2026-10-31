<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test with a real session cookie from a previous login
$cookie = 'laravel-session=eyJpdiI6ImFRYW9Za2xWRXQvcy9ITThZZWY5ekE9PSIsInZhbHVlIjoiQ2ErOE1NZ1pGUlRtSEF2WkVYNHp6RlJlYWtNSi9nM2tINVRDRXd1dk1BZ0xhUkV4WUxCaDBwVHRrVmFOM05NZXR2aVk1ZmJFOXFQZ2tWSXFFUWZiWG14eE1YYUpxb0EvZFdncXlQTmVlN1l0Ym80eTJQd2dRZU1VSFpzQ3p2RTVlZ1B3MWpTR0VlMVlkZDFyNFk2TDRoUTZkbnNWNmN2VURDSHFpQjBGZVppV3lDYTJiVG5jNjVyQkFjaGhZN3pIRGVwOVJ5SVBQS0FvQUZsZ0xFZE1rTXJZTEh0MkdKcFdWYnpHNUQ1ckxVcU15RU9hY2hFbnVlZ0dRPT0iLCJtYWMiOiJhZDIwMTgwMjJhNzY4ZjI1ZjA1NjUyYzM3MjU3OWY3ZDAwYjVhODcxYzU4N2MwMzU4ZTg3MTQyY2M1Nzc4NWY2NDVhMzMwIn0%3D';

$request = Illuminate\Http\Request::create('/', 'GET', [], [], [
    'laravel-session' => $cookie,
], []);

$app->instance('request', $request);

// Run through the kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

echo 'Response status: ' . $response->getStatusCode() . PHP_EOL;

// Check session
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