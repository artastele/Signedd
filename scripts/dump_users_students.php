<?php
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

echo "=== USERS ===\n";
foreach ($db->query("SELECT id, name, email, role, status FROM users")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Email: {$row['email']} | Role: {$row['role']} | Status: {$row['status']}\n";
}

echo "\n=== STUDENT RECORDS ===\n";
foreach ($db->query("SELECT id, student_name, lrn, enrollment_id FROM student_records")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "ID: {$row['id']} | Name: {$row['student_name']} | LRN: {$row['lrn']} | Enroll ID: {$row['enrollment_id']}\n";
}

echo "\n=== IEP RECORDS ===\n";
foreach ($db->query("SELECT id, student_id, status FROM iep_records")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "ID: {$row['id']} | Student ID: {$row['student_id']} | Status: {$row['status']}\n";
}
