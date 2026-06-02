<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\Crm\Stats\GroupEvolutionService::class);
$crm = app(App\Services\Crm\Crm::class);

$reflector = new ReflectionObject($service);
$method = $reflector->getMethod('fetchClasses');
$method->setAccessible(true);
$classes = $method->invoke($service, $crm, 50970, false);

if (!empty($classes)) {
    $keys = array_keys($classes[0]);
    sort($keys);
    file_put_contents('tmp/class_keys.txt', implode("\n", $keys));
    echo "Keys dumped to tmp/class_keys.txt\n";
} else {
    echo "No classes found.\n";
}
