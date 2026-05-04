<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Table Constraints Check</h2>";

$db = Database::getInstance()->getConnection();

try {
    // Get CREATE TABLE statement
    echo "<h3>enrollment_submissions Table Definition:</h3>";
    $stmt = $db->query("SHOW CREATE TABLE enrollment_submissions");
    $result = $stmt->fetch();
    
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
    echo htmlspecialchars($result['Create Table']);
    echo "</pre>";
    
    // Check foreign keys
    echo "<h3>Foreign Key Constraints:</h3>";
    $stmt = $db->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'sped_lms'
        AND TABLE_NAME = 'enrollment_submissions'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fks = $stmt->fetchAll();
    
    if (count($fks) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Constraint</th><th>Column</th><th>References Table</th><th>References Column</th></tr>";
        foreach ($fks as $fk) {
            echo "<tr>";
            echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
            echo "<td>{$fk['COLUMN_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_TABLE_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_COLUMN_NAME']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No foreign key constraints found.</p>";
    }
    
    // Test INSERT with user ID 2
    echo "<hr><h3>Test INSERT with User ID 2:</h3>";
    
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        submitted_at
    ) VALUES (
        2, 'new', '2026-2027', 0, 'pending',
        'TestLast', 'TestFirst', '2020-01-01', 'Male', 'Grade 1',
        NOW()
    )";
    
    $result = $db->exec($sql);
    
    if ($result) {
        $insertId = $db->lastInsertId();
        echo "<p style='color: green;'>✓ INSERT SUCCESS! Insert ID: $insertId</p>";
        
        // Verify
        $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = ?");
        $stmt->execute([$insertId]);
        $record = $stmt->fetch();
        
        if ($record) {
            echo "<p style='color: green;'>✓ VERIFIED: Record exists!</p>";
            echo "<pre>" . print_r($record, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>✗ Record not found after insert!</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ INSERT FAILED</p>";
        print_r($db->errorInfo());
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
