<?php
require "vendor/autoload.php";
$class = "App\\Jobs\\GeneratePertemuanJob";
$r = new ReflectionClass($class);
$p = $r->getProperty("queue");
echo "queue: " . $p->getDeclaringClass()->getName() . " - " . ($p->getType() ?? "none") . "\n";

$j = new App\Jobs\GeneratePertemuanJob(1, "test", 5);
echo "Instance queue: " . $j->queue . "\n";