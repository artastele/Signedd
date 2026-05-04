<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Database Diagnostic</h2>";

$db = Database::getInstance()->getConnection();

// Check if table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'enrollment_submissions'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Table 'enrollment_submissions' EXISTS</p>";
        
        // Check table structure
        echo "<h3>Table Structure:</h3>";
        $stmt = $db->query("DESCRIBE enrollment_submissions");
        $columns = $stmt->fetchAll();
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
        }
        echo "</table>";
        
        // Check row count
        $stmt = $db->query("SELECT COUNT(*) as count FROM enrollment_submissions");
        $count = $stmt->fetch();
        echo "<h3>Total Rows: {$count['count']}</h3>";
        
        // Show all data
        if ($count['count'] > 0) {
            echo "<h3>All Data:</h3>";
            $stmt = $db->query("SELECT id, parent_id, first_name, last_name, is_draft, status, submitted_at, created_at FROM enrollment_submissions ORDER BY id DESC");
            $rows = $stmt->fetchAll();
            echo "<table border='1'><tr><th>ID</th><th>Parent ID</th><th>Name</th><th>Is Draft</th><th>Status</th><th>Submitted At</th><th>Created At</th></tr>";
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['parent_id']}</td>";
                echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                echo "<td>" . ($row['is_draft'] ? 'YES' : 'NO') . "</td>";
                echo "<td>{$row['status']}</td>";
                echo "<td>{$row['submitted_at']}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>⚠ Table is EMPTY - no rows found!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Table 'enrollment_submissions' DOES NOT EXIST</p>";
        echo "<p>You need to run the schema migration!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

// Check database name
echo "<h3>Current Database:</h3>";
$stmt = $db->query("SELECT DATABASE() as db_name");
$dbInfo = $stmt->fetch();
echo "<p>Connected to database: <strong>{$dbInfo['db_name']}</strong></p>";

// Check all tables
echo "<h3>All Tables in Database:</h3>";
$stmt = $db->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";
?>
