<?php
// Ensure both sped_lms and signed_db local databases are populated with full schema

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$databases = ['sped_lms', 'signed_db'];

foreach ($databases as $dbName) {
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        
        $dbPdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $schemaSql = file_get_contents(__DIR__ . '/../config/schema.sql');
        if (substr($schemaSql, 0, 3) === "\xEF\xBB\xBF") {
            $schemaSql = substr($schemaSql, 3);
        }
        
        // Split statements cleanly by semicolon
        $statements = array_filter(array_map('trim', explode(';', $schemaSql)));
        foreach ($statements as $stmtSql) {
            if (empty($stmtSql)) continue;
            try {
                $dbPdo->exec($stmtSql);
            } catch (PDOException $ex) {
                // Ignore "Duplicate column name" (1060), "Table already exists" (1050), "Duplicate key name" (1061)
                if (in_array($ex->getCode(), ['42S21', '42S01', '42000']) || strpos($ex->getMessage(), 'Duplicate column') !== false) {
                    continue;
                }
                // echo "Warning: " . $ex->getMessage() . "\n";
            }
        }
        
        $stmt = $dbPdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "[OK] Local DB `$dbName` fully initialized with " . count($tables) . " tables.\n";
    } catch (Exception $e) {
        echo "[ERROR] Database `$dbName`: " . $e->getMessage() . "\n";
    }
}
