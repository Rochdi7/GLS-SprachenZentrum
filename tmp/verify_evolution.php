<?php

// Run this from the project root: php tmp/verify_evolution.php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Crm\Stats\GroupEvolutionService;
use App\Services\Crm\Crm;

$service = app(GroupEvolutionService::class);
$crm = app(Crm::class);

$strStoreId = 50970; 
$startDate = '2025-09-01'; 
$endDate = '2026-06-01'; 

echo "Running GroupEvolutionService for store $strStoreId from $startDate to $endDate...\n";

// I'll call fetchClasses directly to see the raw data
$reflector = new ReflectionObject($service);
$method = $reflector->getMethod('fetchClasses');
$method->setAccessible(true);
$classes = $method->invoke($service, $crm, $strStoreId, false);

foreach ($classes as $c) {
    if (stripos($c['NAME'] ?? '', 'Nizar') !== false) {
        echo "\nNizar raw class data:\n";
        echo "NAME: " . ($c['NAME'] ?? 'N/A') . "\n";
        echo "START_DATE: " . ($c['START_DATE'] ?? 'N/A') . "\n";
        // ...
    }
}

$report = $service->build($crm, $strStoreId, $startDate, $endDate, true);

echo "\n--- SEARCHING FOR 'Nizar' IN REPORT ---\n";
foreach ($report['groups'] as $g) {
    if (stripos($g['name'], 'Nizar') !== false) {
        print_r($g);
    }
}

echo "\n--- DIAGNOSTICS (Relevant parts) ---\n";
$diag = $report['diag'] ?? [];
echo "Classes with START_DATE: " . ($diag['classes_with_start'] ?? 0) . "\n";
echo "Allocations fetched: " . ($diag['allocations_fetched'] ?? 0) . "\n";

// I want to see if 'Herr Nizar 10H' has a start month in classStartMonth
// I'll add a temporary log in GroupEvolutionService to debug.
