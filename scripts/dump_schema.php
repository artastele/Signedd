<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SHOW CREATE TABLE enrollment_submissions");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'] . "\n";
