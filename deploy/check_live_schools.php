<?php
// Query live test database for schools table logo_path and pubmat_path

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$host = $config['DB_HOST'] ?? 'sql102.infinityfree.com';
$user = $config['DB_USER'] ?? 'if0_42187079';
$pass = $config['DB_PASS'] ?? 'kbiwziPLUi7zx';
$name = $config['DB_NAME'] ?? 'if0_42187079_sped_test';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "=== Schools Table Records in Live Database ===\n";
    $stmt = $pdo->query("SELECT id, school_id, school_name, logo_path, pubmat_path FROM schools");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($schools as $s) {
        echo "ID: " . $s['id'] . "\n";
        echo "  Name: " . $s['school_name'] . "\n";
        echo "  Code: " . $s['school_id'] . "\n";
        echo "  logo_path: '" . ($s['logo_path'] ?? '') . "'\n";
        echo "  pubmat_path: '" . ($s['pubmat_path'] ?? '') . "'\n";
        echo "----\n";
    }
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
