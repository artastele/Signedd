<?php
// Test schema import
$host = 'localhost';
$dbname = 'sped_lms';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate database
    $pdo->exec('DROP DATABASE IF EXISTS ' . $dbname);
    $pdo->exec('CREATE DATABASE ' . $dbname . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    
    echo "Database created successfully\n";
    
    // Now connect to the new database and import schema
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $schema = file_get_contents('../config/schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $count = 0;
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
            $count++;
        }
    }
    
    echo "Schema imported successfully - $count statements executed\n";
    
    // Verify tables
    $result = $pdo->query('SHOW TABLES');
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables created: " . count($tables) . "\n";
    echo "Tables: " . implode(", ", $tables) . "\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
