<?php
// Simple MySQL check compatible with old versions
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== SIMPLE MYSQL CHECK ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. MySQL Version
    echo "1. MySQL VERSION:\n";
    $version = $db->query("SELECT VERSION()")->fetchColumn();
    echo "   $version\n\n";
    
    // 2. Autocommit
    echo "2. AUTOCOMMIT:\n";
    $autocommit = $db->query("SELECT @@autocommit")->fetchColumn();
    echo "   " . ($autocommit ? "✓ ON (1)" : "❌ OFF (0)") . "\n\n";
    
    // 3. Table Engine
    echo "3. TABLE ENGINE:\n";
    $result = $db->query("SHOW TABLE STATUS LIKE 'enrollment_submissions'")->fetch();
    if ($result) {
        echo "   Engine: {$result['Engine']}\n";
        echo "   Rows: {$result['Rows']}\n\n";
    } else {
        echo "   ❌ Table not found!\n\n";
    }
    
    // 4. Current count
    echo "4. CURRENT DATA:\n";
    $count = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   Total records: $count\n\n";
    
    // 5. Test INSERT
    echo "5. TESTING INSERT...\n";
    $testSql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        date_signed, submitted_at
    ) VALUES (
        777, 'new', '2026-2027', 0, 'pending',
        'SimpleTest', 'Check', '2020-01-01', 'Male', 'SPED Program',
        CURDATE(), NOW()
    )";
    
    $db->exec($testSql);
    $insertId = $db->lastInsertId();
    echo "   Insert ID: $insertId\n";
    
    // Verify immediately
    $check = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch();
    if ($check) {
        echo "   ✓ Record found: {$check['first_name']} {$check['last_name']}\n";
    } else {
        echo "   ❌ Record NOT found!\n";
    }
    
    // Count after
    $newCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   New total: $newCount\n";
    echo "   Added: " . ($newCount - $count) . "\n\n";
    
    // 6. Wait and check again
    echo "6. WAITING 3 SECONDS...\n";
    sleep(3);
    
    $finalCheck = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch();
    if ($finalCheck) {
        echo "   ✓✓✓ RECORD STILL EXISTS!\n";
        echo "   Data is persisting correctly.\n\n";
        
        echo "=== RESULT ===\n";
        echo "✓ MySQL is working correctly.\n";
        echo "✓ Autocommit is " . ($autocommit ? "ON" : "OFF") . "\n";
        echo "✓ Data persists after insert.\n\n";
        
        if (!$autocommit) {
            echo "⚠️  WARNING: Autocommit is OFF\n";
            echo "This might cause issues. Enable it in my.ini:\n";
            echo "[mysqld]\n";
            echo "autocommit=1\n";
        }
    } else {
        echo "   ❌❌❌ RECORD DISAPPEARED!\n";
        echo "   This is the problem!\n\n";
        
        echo "=== DIAGNOSIS ===\n";
        echo "❌ Data is being inserted but then deleted/rolled back.\n\n";
        
        echo "POSSIBLE CAUSES:\n";
        echo "1. Autocommit is OFF (current: " . ($autocommit ? "ON" : "OFF") . ")\n";
        echo "2. MySQL is in READ-ONLY mode\n";
        echo "3. There's a trigger deleting data\n";
        echo "4. InnoDB is not flushing to disk\n\n";
        
        echo "FIX:\n";
        echo "1. Open XAMPP Control Panel\n";
        echo "2. Stop MySQL\n";
        echo "3. Edit my.ini (Config button)\n";
        echo "4. Find [mysqld] section\n";
        echo "5. Add or change: autocommit=1\n";
        echo "6. Save and restart MySQL\n";
    }
    
    $finalCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "\nFinal count: $finalCount records\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
