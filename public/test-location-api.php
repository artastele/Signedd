<?php
// Direct test of LocationController
require_once __DIR__ . '/../app/Controllers/LocationController.php';

echo "<h1>Location API Test</h1>";

echo "<h2>Test 1: Get Provinces</h2>";
$controller = new LocationController();
$controller->getProvinces();

echo "<hr><h2>Test 2: Get Cities (Cebu)</h2>";
$controller2 = new LocationController();
$controller2->getCities('Cebu');

echo "<hr><h2>Test 3: Get Barangays (Cebu, Cebu City)</h2>";
$controller3 = new LocationController();
$controller3->getBarangays('Cebu', 'Cebu City');
?>
