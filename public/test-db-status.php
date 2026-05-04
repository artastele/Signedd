<?php
\/\/ Quick database status check
require_once __DIR__ . '\/..\config\/db.php';

header('Content-Type: text\/plain');

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connection: SUCCESS\n\n";
    
    \/\/ Check enrollment_submissions table
    $stmt = $db->query("SHOW TABLES LIKE 'enrollment_submissions'");
    if ($stmt->rowCount() > 0) {
        echo "✓ enrollment_submissions table: EXISTS\n";
        
        $count = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
        echo "  Total records: $count\n";
        
        if ($count > 0) {
            $recent = $db->query("SELECT id, parent_id, first_name, last_name, status, is_draft, submitted_at 
                                  FROM enrollment_submissions 
                                  ORDER BY id DESC LIMIT 5")->fetchAll();
            echo "\n  Recent enrollments:\n";
            foreach ($recent as $row) {
                echo "  - ID: {$row['id']}, Parent: {$row['parent_id']}, Name: {$row['first_name']} {$row['last_name']}, Status: {$row['status']}, Draft: " . ($row['is_draft'] ? 'YES' : 'NO') . ", Submitted: {$row['submitted_at']}\n";
            }
        }
    } else {
        echo "✗ enrollment_submissions table: NOT FOUND\n";
    }
    
    echo "\n";
    
    \/\/ Check users table
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ users table: EXISTS\n";
        $count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "  Total users: $count\n";
        
        if ($count > 0) {
            $users = $db->query("SELECT id, name, email, role, status FROM users ORDER BY id DESC LIMIT 5")->fetchAll();
            echo "\n  Recent users:\n";
            foreach ($users as $user) {
                echo "  - ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}, Status: {$user['status']}\n";
            }
        }
    } else {
        echo "✗ users table: NOT FOUND\n";
    }
    
    echo "\n";
    
    \/\/ Check for any errors in recent operations
    echo "=== RECENT ERROR LOG (last 10 lines) ===\n";
    $logFile = __DIR__ . '\/..\logs\/php_error.log';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $recentLines = array_slice($lines, -10);
        foreach ($recentLines as $line) {
            echo $line;
        }
    } else {
        echo "No error log found\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
