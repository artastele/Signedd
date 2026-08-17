<?php
// Script to set up local MySQL database for Laragon

$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Default Laragon MySQL root password is empty

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[OK] Connected to local MySQL server!\n";
    
    // Create database signedd_db if not exists
    $dbName = 'signed_db';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "[OK] Database `$dbName` ensured.\n";
    
    // Connect to database
    $dbPdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Import schema.sql if empty or check tables
    $stmt = $dbPdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "[INFO] Existing tables in `$dbName`: " . count($tables) . "\n";
    
    if (count($tables) < 10) {
        echo "[+] Importing schema.sql into `$dbName`...\n";
        $schemaSql = file_get_contents(__DIR__ . '/../config/schema.sql');
        
        // Remove BOM
        if (substr($schemaSql, 0, 3) === "\xEF\xBB\xBF") {
            $schemaSql = substr($schemaSql, 3);
        }
        
        $dbPdo->exec($schemaSql);
        
        $stmt = $dbPdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "[OK] Schema imported successfully! Total tables in `$dbName`: " . count($tables) . "\n";
    }
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
