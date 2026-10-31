<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cacheManager = app('cache');

// Test getting config directly
$config = $app['config']["cache.stores.session"];
echo 'Config from app: ' . PHP_EOL;
print_r($config);

// Test the cache manager's getConfig method
$reflection = new ReflectionClass($cacheManager);
$method = $reflection->getMethod('getConfig');
$method->setAccessible(true);
$config = $method->invoke($cacheManager, 'session');
echo 'getConfig result: ' . PHP_EOL;
print_r($config);

// Test building the store
try {
    $repository = $cacheManager->build($config);
    echo 'Built repository: ' . get_class($repository) . PHP_EOL;
    echo 'Store: ' . get_class($repository->getStore()) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
}