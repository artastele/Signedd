<?php
// Direct barangays endpoint (bypass routing)
$controllerPath = file_exists(__DIR__ . '/app/Controllers/LocationController.php')
    ? __DIR__ . '/app/Controllers/LocationController.php'
    : __DIR__ . '/../app/Controllers/LocationController.php';

require_once $controllerPath;

$province = $_GET['province'] ?? '';
$city = $_GET['city'] ?? '';
$controller = new LocationController();
$controller->getBarangays($province, $city);
