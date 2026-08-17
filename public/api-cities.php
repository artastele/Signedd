<?php
// Direct cities endpoint (bypass routing)
$controllerPath = file_exists(__DIR__ . '/app/Controllers/LocationController.php')
    ? __DIR__ . '/app/Controllers/LocationController.php'
    : __DIR__ . '/../app/Controllers/LocationController.php';

require_once $controllerPath;

$province = $_GET['province'] ?? '';
$controller = new LocationController();
$controller->getCities($province);
