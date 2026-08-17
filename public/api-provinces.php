<?php
// Direct provinces endpoint (bypass routing)
$controllerPath = file_exists(__DIR__ . '/app/Controllers/LocationController.php')
    ? __DIR__ . '/app/Controllers/LocationController.php'
    : __DIR__ . '/../app/Controllers/LocationController.php';

require_once $controllerPath;

$controller = new LocationController();
$controller->getProvinces();
