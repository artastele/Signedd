<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>MySQL Status Check</h2>";

$db = Database::getInstance()->getConnection();

try {
    // Check if MySQL is using innodb_force_recovery
    echo "<h3>InnoDB Status:</h3>";
    $stmt = $db->query("SHOW VARIABLES LIKE 'innodb_force_recovery'");
    $recovery = $stmt->fetch();
    echo "<p>innodb_force_recovery: <strong>{$recovery['Value']}</strong></p>";
    if ($recovery['Value'] != '0') {
        echo "<p style='color: red;'>⚠ WARNING: InnoDB is in recovery mode! Data will not persist!</p>";
    }
    
    // Check innodb_flush_log_at_trx_commit
    $stmt = $db->query("SHOW VARIABLES LIKE 'innodb_flush_log_at_trx_commit'");
    $flush = $stmt->fetch();
    echo "<p>innodb_flush_log_at_trx_commit: <strong>{$flush['Value']}</strong></p>";
    
    // Check if InnoDB is enabled
    $stmt = $db->query("SHOW ENGINES");
    $engines = $stmt->fetchAll();
    echo "<h3>Available Storage Engines:</h3>";
    echo "<table border='1'><tr><th>Engine</th><th>Support</th><th>Comment</th></tr>";
    foreach ($engines as $engine) {
        $color = $engine['Support'] == 'DEFAULT' ? 'green' : ($engine['Support'] == 'YES' ? 'blue' : 'red');
        echo "<tr style='color: $color;'>";
        echo "<td><strong>{$engine['Engine']}</strong></td>";
        echo "<td>{$engine['Support']}</td>";
        echo "<td>{$engine['Comment']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check data directory permissions
    $stmt = $db->query("SELECT @@datadir as datadir");
    $datadir = $stmt->fetch();
    echo "<h3>Data Directory:</h3>";
    echo "<p><strong>{$datadir['datadir']}</strong></p>";
    
    // Try to create and persist a simple test
    echo "<hr><h3>Persistence Test:</h3>";
    
    // Create test table
    $db->exec("DROP TABLE IF EXISTS test_persist");
    $db->exec("CREATE TABLE test_persist (id INT PRIMARY KEY AUTO_INCREMENT, data VARCHAR(50), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
    echo "<p style='color: green;'>✓ Created test table</p>";
    
    // Insert data
    $db->exec("INSERT INTO test_persist (data) VALUES ('TEST DATA')");
    $insertId = $db->lastInsertId();
    echo "<p style='color: green;'>✓ Inserted test record (ID: $insertId)</p>";
    
    // Force flush
    $db->exec("FLUSH TABLES");
    echo "<p style='color: green;'>✓ Flushed tables</p>";
    
    // Verify
    $stmt = $db->query("SELECT * FROM test_persist");
    $test = $stmt->fetch();
    if ($test) {
        echo "<p style='color: green;'>✓ Test record found: {$test['data']}</p>";
    } else {
        echo "<p style='color: red;'>✗ Test record NOT found!</p>";
    }
    
    // Clean up
    $db->exec("DROP TABLE test_persist");
    
    echo "<hr>";
    echo "<h3>Recommendation:</h3>";
    echo "<p><strong>Please check the following:</strong></p>";
    echo "<ol>";
    echo "<li>Restart XAMPP MySQL service completely</li>";
    echo "<li>Check if your disk has enough space</li>";
    echo "<li>Check MySQL error log in: C:\\xampp\\mysql\\data\\*.err</li>";
    echo "<li>Check if antivirus is blocking MySQL writes</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>
