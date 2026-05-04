<?php
// Test direct INSERT to diagnose the issue
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== TESTING DIRECT INSERT ===\n\n";
    
    // Check autocommit
    $autocommit = $db->query("SELECT @@autocommit")->fetch();
    echo "Autocommit: " . ($autocommit['@@autocommit'] ? 'ON' : 'OFF') . "\n\n";
    
    // Simple test INSERT with valid parent ID (2)
    $testSql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll
    ) VALUES (
        2, 'test', '2026-2027', 0, 'pending',
        'TestLast', 'TestFirst', '2020-01-01', 'Male', 'Grade 1'
    )";
    
    echo "Executing SQL:\n$testSql\n\n";
    
    $result = $db->exec($testSql);
    echo "exec() returned: " . ($result === false ? 'FALSE' : $result) . "\n";
    
    $lastId = $db->lastInsertId();
    echo "lastInsertId(): $lastId\n\n";
    
    // Verify immediately
    echo "Verifying insert...\n";
    $verify = $db->query("SELECT * FROM enrollment_submissions WHERE parent_id = 2 AND first_name = 'TestFirst'")->fetch();
    
    if ($verify) {
        echo "✓ SUCCESS - Record found in database!\n";
        echo "ID: {$verify['id']}\n";
        echo "Name: {$verify['first_name']} {$verify['last_name']}\n";
    } else {
        echo "❌ FAILED - Record NOT found in database!\n";
        echo "The INSERT executed but data was not persisted.\n";
    }
    
    // Check table structure
    echo "\n=== TABLE STRUCTURE ===\n";
    $columns = $db->query("DESCRIBE enrollment_submissions")->fetchAll();
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']} (Null: {$col['Null']}, Key: {$col['Key']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
