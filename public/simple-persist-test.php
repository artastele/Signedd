<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Simple Persistence Test</h2>";

$db = Database::getInstance()->getConnection();

try {
    // Step 1: Insert a test record
    echo "<h3>Step 1: Inserting test record...</h3>";
    
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        submitted_at, created_at
    ) VALUES (
        2, 'new', '2026-2027', 0, 'test',
        'SIMPLE', 'PERSIST', '2020-01-01', 'Male', 'Grade 1',
        NOW(), NOW()
    )";
    
    $result = $db->exec($sql);
    $insertId = $db->lastInsertId();
    
    echo "<p style='color: green;'>✓ Inserted record ID: <strong>$insertId</strong></p>";
    
    // Step 2: Verify immediately
    echo "<h3>Step 2: Immediate verification...</h3>";
    $stmt = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId");
    $record = $stmt->fetch();
    
    if ($record) {
        echo "<p style='color: green;'>✓ Record found immediately after insert</p>";
    } else {
        echo "<p style='color: red;'>✗ Record NOT found immediately - this is very bad!</p>";
        exit;
    }
    
    // Step 3: Count all records
    echo "<h3>Step 3: Counting all records...</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM enrollment_submissions");
    $count = $stmt->fetch();
    echo "<p>Total records before refresh: <strong>{$count['count']}</strong></p>";
    
    // Step 4: Simulate page refresh by creating new connection
    echo "<h3>Step 4: Simulating page refresh (new connection)...</h3>";
    $db = null; // Close connection
    sleep(1); // Wait a moment
    $db = Database::getInstance()->getConnection(); // New connection
    echo "<p style='color: green;'>✓ New connection established</p>";
    
    // Step 5: Check if record still exists
    echo "<h3>Step 5: Checking if record persists...</h3>";
    $stmt = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId");
    $record = $stmt->fetch();
    
    if ($record) {
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓✓✓ SUCCESS! Data PERSISTS after reconnection!</p>";
        echo "<pre>" . print_r($record, true) . "</pre>";
        
        // Count again
        $stmt = $db->query("SELECT COUNT(*) as count FROM enrollment_submissions");
        $count = $stmt->fetch();
        echo "<p>Total records after refresh: <strong>{$count['count']}</strong></p>";
        
    } else {
        echo "<p style='color: red; font-size: 18px; font-weight: bold;'>✗✗✗ FAILED! Data DISAPPEARED after reconnection!</p>";
        echo "<p>This means MySQL is NOT saving data permanently.</p>";
        
        // Count again
        $stmt = $db->query("SELECT COUNT(*) as count FROM enrollment_submissions");
        $count = $stmt->fetch();
        echo "<p>Total records after refresh: <strong>{$count['count']}</strong></p>";
        
        echo "<h3>Possible Causes:</h3>";
        echo "<ul>";
        echo "<li>MySQL server is restarting between requests</li>";
        echo "<li>Data directory is not writable</li>";
        echo "<li>InnoDB is not properly configured</li>";
        echo "<li>Disk is full</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='diagnose-db.php'>View All Data</a> | <a href='check-mysql-config.php'>Check Config</a></p>";
?>
