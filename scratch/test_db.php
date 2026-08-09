<?php
require_once __DIR__ . '/../config/db.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "DB Connected Successfully!\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . implode(', ', $tables) . "\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
