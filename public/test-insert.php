<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Testing Direct INSERT</h2>";

$db = Database::getInstance()->getConnection();

try {
    // Test 1: Simple INSERT
    echo "<h3>Test 1: Direct INSERT</h3>";
    
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        submitted_at
    ) VALUES (
        :parent_id, :enrollment_type, :school_year, :is_draft, :status,
        :last_name, :first_name, :birth_date, :sex, :grade_level_to_enroll,
        :submitted_at
    )";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        'parent_id' => 999,
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'is_draft' => false,
        'status' => 'pending',
        'last_name' => 'TEST',
        'first_name' => 'Direct Insert',
        'birth_date' => '2020-01-01',
        'sex' => 'Male',
        'grade_level_to_enroll' => 'Grade 1',
        'submitted_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        $insertId = $db->lastInsertId();
        echo "<p style='color: green;'>✓ INSERT executed successfully. Insert ID: $insertId</p>";
        
        // Check if in transaction
        if ($db->inTransaction()) {
            echo "<p style='color: orange;'>⚠ Database is IN TRANSACTION - committing now...</p>";
            $db->commit();
        } else {
            echo "<p style='color: green;'>✓ Database is NOT in transaction (autocommit mode)</p>";
        }
        
        // Verify the record exists
        $verifyStmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = :id");
        $verifyStmt->execute(['id' => $insertId]);
        $record = $verifyStmt->fetch();
        
        if ($record) {
            echo "<p style='color: green;'>✓ VERIFIED: Record exists in database!</p>";
            echo "<pre>" . print_r($record, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>✗ FAILED: Record NOT found in database after INSERT!</p>";
        }
        
        // Check total count
        $countStmt = $db->query("SELECT COUNT(*) as count FROM enrollment_submissions");
        $count = $countStmt->fetch();
        echo "<p>Total records in table: <strong>{$count['count']}</strong></p>";
        
    } else {
        echo "<p style='color: red;'>✗ INSERT failed</p>";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='diagnose-db.php'>View All Data</a></p>";
?>
