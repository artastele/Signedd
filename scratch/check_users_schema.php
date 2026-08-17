<?php
require_once __DIR__ . '/../config/db.php';
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("DESCRIBE users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
