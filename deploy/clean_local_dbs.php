<?php
// Clean all tables in local MySQL databases safely

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$databases = ['sped_lms', 'signed_db'];

foreach ($databases as $dbName) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $truncatedCount = 0;
        foreach ($tables as $table) {
            try {
                $pdo->exec("TRUNCATE TABLE `$table`;");
                $truncatedCount++;
            } catch (Exception $e) {
                // Ignore view or missing table errors
            }
        }
        
        @$pdo->exec("INSERT INTO `db_version` (`version`) VALUES (58);");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        
        echo "[OK] Local database `$dbName` cleaned! ($truncatedCount tables truncated)\n";
    } catch (Exception $e) {
        echo "[ERROR] Cleaning `$dbName`: " . $e->getMessage() . "\n";
    }
}
