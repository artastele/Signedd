<?php
// Test local app environment and database connection

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "[OK] Local Application DB Connection Successful!\n";
    echo "  DB Host: " . env('DB_HOST') . "\n";
    echo "  DB Name: " . env('DB_NAME') . "\n";
    echo "  DB User: " . env('DB_USER') . "\n";
    
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    echo "  User count: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "[ERROR] Local DB Connection Failed: " . $e->getMessage() . "\n";
}
