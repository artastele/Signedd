<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Force INSERT Test</h2>";

$db = Database::getInstance()->getConnection();

try {
    // Disable foreign key checks temporarily
    echo "<h3>Step 1: Disable Foreign Key Checks</h3>";
    $db->exec("SET FOREIGN_KEY_CHECKS=0");
    echo "<p style='color: green;'>✓ Foreign key checks disabled</p>";
    
    // Ensure autocommit is ON
    echo "<h3>Step 2: Enable Autocommit</h3>";
    $db->exec("SET autocommit=1");
    echo "<p style='color: green;'>✓ Autocommit enabled</p>";
    
    // Check if in transaction
    echo "<h3>Step 3: Check Transaction State</h3>";
    if ($db->inTransaction()) {
        echo "<p style='color: orange;'>⚠ In transaction - rolling back...</p>";
        $db->rollBack();
    } else {
        echo "<p style='color: green;'>✓ Not in transaction</p>";
    }
    
    // Simple INSERT
    echo "<h3>Step 4: Execute Simple INSERT</h3>";
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        submitted_at, created_at
    ) VALUES (
        2, 'new', '2026-2027', 0, 'pending',
        'FORCE', 'TEST', '2020-01-01', 'Male', 'Grade 1',
        NOW(), NOW()
    )";
    
    echo "<p>Executing SQL...</p>";
    $result = $db->exec($sql);
    
    if ($result === false) {
        echo "<p style='color: red;'>✗ exec() returned FALSE</p>";
        print_r($db->errorInfo());
    } else {
        echo "<p style='color: green;'>✓ exec() returned: $result (rows affected)</p>";
        
        $insertId = $db->lastInsertId();
        echo "<p>Last Insert ID: <strong>$insertId</strong></p>";
        
        // Force commit if in transaction
        if ($db->inTransaction()) {
            echo "<p style='color: orange;'>⚠ Still in transaction - committing...</p>";
            $db->commit();
        }
        
        // Immediate verification
        echo "<h3>Step 5: Immediate Verification</h3>";
        $stmt = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId");
        $record = $stmt->fetch();
        
        if ($record) {
            echo "<p style='color: green;'>✓✓✓ SUCCESS! Record EXISTS in database!</p>";
            echo "<pre>" . print_r($record, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>✗✗✗ FAILED! Record NOT FOUND even after successful INSERT!</p>";
            echo "<p><strong>This indicates a serious database configuration issue.</strong></p>";
        }
        
        // Check total count
        $countStmt = $db->query("SELECT COUNT(*) as count FROM enrollment_submissions");
        $count = $countStmt->fetch();
        echo "<p>Total records in table: <strong>{$count['count']}</strong></p>";
    }
    
    // Re-enable foreign key checks
    echo "<h3>Step 6: Re-enable Foreign Key Checks</h3>";
    $db->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "<p style='color: green;'>✓ Foreign key checks re-enabled</p>";
    
    // Check MySQL version and settings
    echo "<hr><h3>Database Information:</h3>";
    $stmt = $db->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "<p>MySQL Version: <strong>{$version['version']}</strong></p>";
    
    $stmt = $db->query("SHOW VARIABLES LIKE 'autocommit'");
    $autocommit = $stmt->fetch();
    echo "<p>Autocommit: <strong>{$autocommit['Value']}</strong></p>";
    
    $stmt = $db->query("SHOW VARIABLES LIKE 'sql_mode'");
    $sqlMode = $stmt->fetch();
    echo "<p>SQL Mode: <strong>{$sqlMode['Value']}</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>EXCEPTION: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='diagnose-db.php'>Check All Data</a></p>";
?>
