<?php
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

echo "=== pdsp_domains ===\n";
try {
    $rows = $db->query("SELECT * FROM pdsp_domains")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['id']} | Name: {$row['domain_name']} | Code: {$row['domain_code']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
