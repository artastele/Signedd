<?php
// Check users in database
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== CHECKING USERS ===\n\n";
    
    // Get all users
    $users = $db->query("SELECT id, name, email, role FROM users ORDER BY id")->fetchAll();
    
    if (empty($users)) {
        echo "❌ NO USERS FOUND IN DATABASE\n";
    } else {
        echo "Total users: " . count($users) . "\n\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']} | Name: {$user['name']} | Email: {$user['email']} | Role: {$user['role']}\n";
        }
    }
    
    echo "\n=== CHECKING ENROLLMENTS ===\n\n";
    
    // Get all enrollments
    $enrollments = $db->query("SELECT id, parent_id, first_name, last_name, status FROM enrollment_submissions ORDER BY id DESC")->fetchAll();
    
    if (empty($enrollments)) {
        echo "❌ NO ENROLLMENTS FOUND IN DATABASE\n";
    } else {
        echo "Total enrollments: " . count($enrollments) . "\n\n";
        foreach ($enrollments as $e) {
            echo "ID: {$e['id']} | Parent ID: {$e['parent_id']} | Student: {$e['first_name']} {$e['last_name']} | Status: {$e['status']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
