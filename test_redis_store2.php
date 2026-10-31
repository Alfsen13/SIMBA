<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a fresh session
$request = Illuminate\Http\Request::create('/test', 'GET');
$app->instance('request', $request);

// Get the actual store from the manager
$manager = app('session');
$store = $manager->driver();
$store->start();
$handler = $store->getHandler();

// Get handler's cache
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

// Check the redis factory reference
$reflectionStore = new ReflectionClass($handlerStore);
$propertyRedis = $reflectionStore->getProperty('redis');
$propertyRedis->setAccessible(true);
$handlerRedis = $propertyRedis->getValue($handlerStore);
$containerRedis = $propertyRedis->getValue($containerStore);

echo 'Handler store redis: ' . get_class($handlerRedis) . PHP_EOL;
echo 'Container store redis: ' . get_class($containerRedis) . PHP_EOL;
echo 'Same redis factory: ' . ($handlerRedis === $containerRedis ? 'true' : 'false') . PHP_EOL;

// Check connection name
$propertyConn = $reflectionStore->getProperty('connection');
$propertyConn->setAccessible(true);
$handlerConn = $propertyConn->getValue($handlerStore);
$containerConn = $propertyConn->getValue($containerStore);
echo 'Handler connection name: ' . $handlerConn . PHP_EOL;
echo 'Container connection name: ' . $containerConn . PHP_EOL;

// Get connections
$handlerConnection = $handlerStore->connection();
$containerConnection = $containerStore->connection();
echo 'Handler connection: ' . get_class($handlerConnection) . PHP_EOL;
echo 'Container connection: ' . get_class($containerConnection) . PHP_EOL;
echo 'Same connection: ' . ($handlerConnection === $containerConnection ? 'true' : 'false') . PHP_EOL;