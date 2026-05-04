<?php
// Test if PDO connection is causing the issue
header('Content-Type: text/plain; charset=utf-8');

echo "=== PDO PERSISTENCE TEST ===\n\n";

try {
    // Create connection WITHOUT using Database singleton
    $host = 'localhost';
    $dbname = 'sped_lms';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    echo "1. Creating NEW PDO connection...\n";
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "   ✓ Connected\n\n";
    
    // Set autocommit explicitly
    echo "2. Setting autocommit...\n";
    $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    $pdo->exec("SET autocommit=1");
    echo "   ✓ Autocommit enabled\n\n";
    
    // Check current count
    echo "3. Counting records...\n";
    $before = $pdo->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   Before: $before\n\n";
    
    // Insert using exec (not prepare)
    echo "4. Inserting with exec()...\n";
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        date_signed, submitted_at
    ) VALUES (
        888, 'new', '2026-2027', 0, 'pending',
        'PDOTest', 'Direct', '2020-01-01', 'Female', 'SPED Program',
        CURDATE(), NOW()
    )";
    
    $affected = $pdo->exec($sql);
    $insertId = $pdo->lastInsertId();
    echo "   Affected rows: $affected\n";
    echo "   Insert ID: $insertId\n\n";
    
    // Verify immediately
    echo "5. Verifying immediately...\n";
    $check1 = $pdo->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch();
    if ($check1) {
        echo "   ✓ Found: {$check1['first_name']} {$check1['last_name']}\n\n";
    } else {
        echo "   ❌ NOT FOUND!\n\n";
    }
    
    // Count again
    $after = $pdo->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "6. After insert: $after records\n";
    echo "   Difference: " . ($after - $before) . "\n\n";
    
    // Close connection explicitly
    echo "7. Closing connection...\n";
    $pdo = null;
    echo "   ✓ Connection closed\n\n";
    
    // Open NEW connection and check
    echo "8. Opening NEW connection to verify...\n";
    $pdo2 = new PDO($dsn, $username, $password, $options);
    $check2 = $pdo2->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch();
    
    if ($check2) {
        echo "   ✓✓✓ DATA PERSISTED! Record still exists!\n";
        echo "   Name: {$check2['first_name']} {$check2['last_name']}\n";
        echo "   Status: {$check2['status']}\n\n";
        
        echo "=== CONCLUSION ===\n";
        echo "✓ PDO is working correctly.\n";
        echo "✓ Data persists after connection close.\n";
        echo "The issue must be in the application code.\n";
    } else {
        echo "   ❌❌❌ DATA LOST! Record disappeared!\n\n";
        
        echo "=== CONCLUSION ===\n";
        echo "❌ CRITICAL MYSQL ISSUE!\n";
        echo "Data is being inserted but not persisted.\n";
        echo "\nACTION REQUIRED:\n";
        echo "1. Check MySQL configuration (my.ini or my.cnf)\n";
        echo "2. Ensure autocommit=1 in [mysqld] section\n";
        echo "3. Restart MySQL server\n";
        echo "4. Check disk space\n";
        echo "5. Check MySQL error log\n";
    }
    
    $final = $pdo2->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "\nFinal count: $final records\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
