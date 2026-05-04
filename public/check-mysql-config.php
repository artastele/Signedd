<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>MySQL Configuration Check</h2>";

$db = Database::getInstance()->getConnection();

try {
    // Check storage engine
    echo "<h3>Table Storage Engine:</h3>";
    $stmt = $db->query("SHOW TABLE STATUS WHERE Name = 'enrollment_submissions'");
    $tableStatus = $stmt->fetch();
    
    if ($tableStatus) {
        echo "<p>Engine: <strong style='color: " . ($tableStatus['Engine'] == 'InnoDB' ? 'green' : 'red') . ";'>{$tableStatus['Engine']}</strong></p>";
        echo "<p>Row Format: <strong>{$tableStatus['Row_format']}</strong></p>";
        echo "<p>Rows: <strong>{$tableStatus['Rows']}</strong></p>";
        echo "<p>Data Length: <strong>{$tableStatus['Data_length']}</strong></p>";
        
        if ($tableStatus['Engine'] != 'InnoDB') {
            echo "<p style='color: red;'>⚠ WARNING: Table is not using InnoDB engine! This may cause data loss.</p>";
        }
    }
    
    // Check autocommit
    echo "<h3>Connection Settings:</h3>";
    $stmt = $db->query("SELECT @@autocommit as autocommit");
    $autocommit = $stmt->fetch();
    echo "<p>Autocommit: <strong style='color: " . ($autocommit['autocommit'] ? 'green' : 'red') . ";'>" . ($autocommit['autocommit'] ? 'ON' : 'OFF') . "</strong></p>";
    
    // Check if in transaction
    $inTransaction = $db->inTransaction();
    echo "<p>In Transaction: <strong style='color: " . ($inTransaction ? 'red' : 'green') . ";'>" . ($inTransaction ? 'YES' : 'NO') . "</strong></p>";
    
    // Check transaction isolation level
    $stmt = $db->query("SELECT @@transaction_isolation as isolation");
    $isolation = $stmt->fetch();
    echo "<p>Transaction Isolation: <strong>{$isolation['isolation']}</strong></p>";
    
    // Check MySQL version
    $stmt = $db->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "<p>MySQL Version: <strong>{$version['version']}</strong></p>";
    
    // Check data directory (where data is stored)
    $stmt = $db->query("SELECT @@datadir as datadir");
    $datadir = $stmt->fetch();
    echo "<p>Data Directory: <strong>{$datadir['datadir']}</strong></p>";
    
    // Check if using tmpdir
    $stmt = $db->query("SELECT @@tmpdir as tmpdir");
    $tmpdir = $stmt->fetch();
    echo "<p>Temp Directory: <strong>{$tmpdir['tmpdir']}</strong></p>";
    
    // Test persistent INSERT
    echo "<hr><h3>Testing Persistent INSERT:</h3>";
    
    // Insert test record
    $testSql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        submitted_at, created_at
    ) VALUES (
        2, 'new', '2026-2027', 0, 'test',
        'PERSIST', 'TEST', '2020-01-01', 'Male', 'Grade 1',
        NOW(), NOW()
    )";
    
    $db->exec($testSql);
    $insertId = $db->lastInsertId();
    echo "<p>Inserted record with ID: <strong>$insertId</strong></p>";
    
    // Force commit
    $db->exec("COMMIT");
    echo "<p style='color: green;'>✓ Forced COMMIT</p>";
    
    // Close and reconnect
    $db = null;
    $db = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✓ Reconnected to database</p>";
    
    // Check if record still exists
    $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = ?");
    $stmt->execute([$insertId]);
    $record = $stmt->fetch();
    
    if ($record) {
        echo "<p style='color: green;'>✓✓✓ SUCCESS! Record PERSISTS after reconnection!</p>";
        echo "<pre>" . print_r($record, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗✗✗ FAILED! Record DISAPPEARED after reconnection!</p>";
        echo "<p><strong>This indicates MySQL is not persisting data to disk!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>
