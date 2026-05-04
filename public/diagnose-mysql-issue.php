<?php
// Deep diagnosis of MySQL issue
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEEP MYSQL DIAGNOSIS ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Check MySQL version
    echo "1. MySQL VERSION:\n";
    $version = $db->query("SELECT VERSION()")->fetchColumn();
    echo "   $version\n\n";
    
    // 2. Check autocommit setting
    echo "2. AUTOCOMMIT STATUS:\n";
    $autocommit = $db->query("SELECT @@autocommit")->fetchColumn();
    echo "   @@autocommit = " . ($autocommit ? 'ON (1)' : 'OFF (0)') . "\n";
    
    $sessionAutocommit = $db->query("SELECT @@session.autocommit")->fetchColumn();
    echo "   @@session.autocommit = " . ($sessionAutocommit ? 'ON (1)' : 'OFF (0)') . "\n";
    
    $globalAutocommit = $db->query("SELECT @@global.autocommit")->fetchColumn();
    echo "   @@global.autocommit = " . ($globalAutocommit ? 'ON (1)' : 'OFF (0)') . "\n\n";
    
    // 3. Check transaction isolation level (compatible with old MySQL)
    echo "3. TRANSACTION ISOLATION:\n";
    try {
        $isolation = $db->query("SELECT @@tx_isolation")->fetchColumn();
        echo "   $isolation\n\n";
    } catch (Exception $e) {
        echo "   (Not available in this MySQL version)\n\n";
    }
    
    // 4. Check if in transaction
    echo "4. TRANSACTION STATE:\n";
    $inTrans = $db->inTransaction();
    echo "   PDO inTransaction(): " . ($inTrans ? 'YES' : 'NO') . "\n\n";
    
    // 5. Check table engine
    echo "5. TABLE ENGINE:\n";
    $engine = $db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA='sped_lms' AND TABLE_NAME='enrollment_submissions'")->fetchColumn();
    echo "   enrollment_submissions: $engine\n\n";
    
    // 6. Test INSERT with immediate verification
    echo "6. TESTING INSERT + IMMEDIATE READ:\n";
    
    $beforeCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   Before: $beforeCount records\n";
    
    // Insert test record
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        date_signed, submitted_at
    ) VALUES (
        999, 'new', '2026-2027', 0, 'pending',
        'DiagTest', 'MySQL', '2020-01-01', 'Male', 'SPED Program',
        CURDATE(), NOW()
    )";
    
    $result = $db->exec($sql);
    $insertId = $db->lastInsertId();
    echo "   INSERT executed: ID = $insertId\n";
    
    // Immediate read in same connection
    $stmt = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId");
    $record = $stmt->fetch();
    
    if ($record) {
        echo "   ✓ Record FOUND immediately after INSERT\n";
        echo "   Name: {$record['first_name']} {$record['last_name']}\n";
    } else {
        echo "   ❌ Record NOT FOUND immediately after INSERT!\n";
    }
    
    // Count after
    $afterCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   After: $afterCount records\n";
    echo "   Difference: " . ($afterCount - $beforeCount) . "\n\n";
    
    // 7. Wait 2 seconds and check again
    echo "7. WAITING 2 SECONDS...\n";
    sleep(2);
    
    $stmt2 = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId");
    $record2 = $stmt2->fetch();
    
    if ($record2) {
        echo "   ✓ Record STILL EXISTS after 2 seconds\n";
    } else {
        echo "   ❌ Record DISAPPEARED after 2 seconds!\n";
        echo "   THIS IS THE PROBLEM - Data is being rolled back!\n";
    }
    
    $finalCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   Final count: $finalCount records\n\n";
    
    // 8. Check for any triggers or events
    echo "8. CHECKING FOR TRIGGERS:\n";
    $triggers = $db->query("SHOW TRIGGERS FROM sped_lms WHERE `Table` = 'enrollment_submissions'")->fetchAll();
    if (empty($triggers)) {
        echo "   No triggers found\n\n";
    } else {
        echo "   ⚠️  TRIGGERS FOUND:\n";
        foreach ($triggers as $trigger) {
            echo "   - {$trigger['Trigger']}: {$trigger['Event']} {$trigger['Timing']}\n";
        }
        echo "\n";
    }
    
    // 9. Check MySQL error log
    echo "9. MYSQL VARIABLES:\n";
    $vars = [
        'innodb_flush_log_at_trx_commit',
        'sync_binlog',
        'innodb_support_xa',
        'innodb_rollback_on_timeout'
    ];
    
    foreach ($vars as $var) {
        try {
            $value = $db->query("SELECT @@$var")->fetchColumn();
            echo "   $var = $value\n";
        } catch (Exception $e) {
            echo "   $var = (not available)\n";
        }
    }
    
    echo "\n=== DIAGNOSIS COMPLETE ===\n";
    
    if ($finalCount > $beforeCount) {
        echo "✓ Data is persisting correctly.\n";
    } else {
        echo "❌ DATA IS NOT PERSISTING!\n";
        echo "\nPossible causes:\n";
        echo "1. MySQL is configured with autocommit=0 globally\n";
        echo "2. There's a trigger deleting data\n";
        echo "3. InnoDB is not flushing to disk\n";
        echo "4. Connection is being closed before commit\n";
        echo "5. Another process is deleting the data\n";
        echo "\nRECOMMENDED FIX:\n";
        echo "Check your MySQL my.ini/my.cnf file and ensure:\n";
        echo "  autocommit = 1\n";
        echo "  innodb_flush_log_at_trx_commit = 1\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
