<?php
// Direct insert test to diagnose the issue
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIRECT INSERT TEST ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check current state
    echo "1. Checking current autocommit status...\n";
    $autocommit = $db->query("SELECT @@autocommit")->fetchColumn();
    echo "   Autocommit: " . ($autocommit ? 'ON' : 'OFF') . "\n\n";
    
    // Check if in transaction
    echo "2. Checking transaction status...\n";
    $inTransaction = $db->inTransaction();
    echo "   In transaction: " . ($inTransaction ? 'YES' : 'NO') . "\n\n";
    
    // Count before insert
    echo "3. Counting records before insert...\n";
    $beforeCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   Records before: $beforeCount\n\n";
    
    // Try a simple insert
    echo "4. Attempting direct INSERT...\n";
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        date_signed, submitted_at
    ) VALUES (
        2, 'new', '2026-2027', 0, 'pending',
        'TestLastName', 'TestFirstName', '2020-01-01', 'Male', 'SPED Program',
        CURDATE(), NOW()
    )";
    
    $result = $db->exec($sql);
    echo "   Exec result: " . ($result !== false ? "SUCCESS (affected rows: $result)" : "FAILED") . "\n";
    
    $insertId = $db->lastInsertId();
    echo "   Last insert ID: $insertId\n\n";
    
    // Force commit
    echo "5. Forcing COMMIT...\n";
    $db->exec("COMMIT");
    echo "   Commit executed\n\n";
    
    // Count after insert
    echo "6. Counting records after insert...\n";
    $afterCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   Records after: $afterCount\n";
    echo "   Difference: " . ($afterCount - $beforeCount) . "\n\n";
    
    // Verify the record exists
    if ($insertId > 0) {
        echo "7. Verifying record with ID $insertId...\n";
        $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = ?");
        $stmt->execute([$insertId]);
        $record = $stmt->fetch();
        
        if ($record) {
            echo "   ✓ Record FOUND in database\n";
            echo "   Name: {$record['first_name']} {$record['last_name']}\n";
            echo "   Grade: {$record['grade_level_to_enroll']}\n";
            echo "   Status: {$record['status']}\n";
        } else {
            echo "   ❌ Record NOT FOUND despite insert ID!\n";
            echo "   This indicates a SERIOUS transaction/persistence issue.\n";
        }
    }
    
    echo "\n=== DIAGNOSIS ===\n";
    if ($afterCount > $beforeCount) {
        echo "✓ INSERT is working correctly.\n";
        echo "The issue may be with the EnrollmentModel logic or form submission.\n";
    } else {
        echo "❌ INSERT is NOT persisting data.\n";
        echo "This is a database configuration issue.\n";
        echo "\nPossible causes:\n";
        echo "- MySQL is in READ-ONLY mode\n";
        echo "- Table is locked\n";
        echo "- Storage engine issue (InnoDB vs MyISAM)\n";
        echo "- Insufficient permissions\n";
        echo "- Disk space issue\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
