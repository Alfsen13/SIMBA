<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cacheManager = app('cache');

// Try to access the stores property via reflection
$reflection = new ReflectionClass($cacheManager);
$property = $reflection->getProperty('stores');
$property->setAccessible(true);
$stores = $property->getValue($cacheManager);

echo 'Cached stores:' . PHP_EOL;
print_r(array_keys($stores));

// Try to unset the session store
if (isset($stores['session'])) {
    unset($stores['session']);
    $property->setValue($cacheManager, $stores);
    echo 'Unset session store' . PHP_EOL;
}

// Try to get the session store again
$cache = app('cache')->store('session');
echo 'Store class: ' . get_class($cache) . PHP_EOL;