<?php
require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();
$db->exec('DELETE FROM enrollment_submissions WHERE id = 3');
echo "Test record deleted\n";
