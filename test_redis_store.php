<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get handler's cache
$manager = app('session');
$store = $manager->driver();
$store->start();
$handler = $store->getHandler();

$reflection = new ReflectionClass($handler);
$property = $reflection->getProperty('cache');
$property->setAccessible(true);
$handlerCache = $property->getValue($handler);

$containerCache = app('cache')->store('session');

// Get the underlying RedisStore
$handlerStore = $handlerCache->getStore();
$containerStore = $containerCache->getStore();

echo 'Handler store: ' . get_class($handlerStore) . PHP_EOL;
echo 'Container store: ' . get_class($containerStore) . PHP_EOL;
echo 'Same store object: ' . ($handlerStore === $containerStore ? 'true' : 'false') . PHP_EOL;

// Check Redis connections
if (method_exists($handlerStore, 'connection')) {
    $handlerRedis = $handlerStore->connection();
    echo 'Handler Redis: ' . get_class($handlerRedis) . PHP_EOL;
}

if (method_exists($containerStore, 'connection')) {
    $containerRedis = $containerStore->connection();
    echo 'Container Redis: ' . get_class($containerRedis) . PHP_EOL;
}

// Check if they're the same Redis connection
$handlerRedis = $handlerStore->connection();
$containerRedis = $containerStore->connection();
echo 'Same Redis connection: ' . ($handlerRedis === $containerRedis ? 'true' : 'false') . PHP_EOL;