<?php
// Migration script to fix enrollment schema
// Adds birth_place, removes place_of_birth_city/province and region columns

require_once __DIR__ . '/config/db.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "<h2>Enrollment Schema Migration</h2>";
    echo "<hr>";
    
    // Disable foreign key checks temporarily
    echo "1. Disabling foreign key checks...<br>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Check if birth_place column exists
    echo "2. Checking birth_place column...<br>";
    $result = $pdo->query("SHOW COLUMNS FROM enrollment_submissions WHERE Field = 'birth_place'");
    if ($result->rowCount() > 0) {
        echo "   ✓ birth_place already exists<br>";
    } else {
        $pdo->exec("ALTER TABLE enrollment_submissions ADD COLUMN birth_place VARCHAR(255) AFTER age");
        echo "   ✓ birth_place added<br>";
    }
    
    // Drop old columns only if they exist
    echo "3. Dropping old columns...<br>";
    
    $columnsToDrop = ['place_of_birth_city', 'place_of_birth_province', 'current_region', 'permanent_region'];
    
    foreach ($columnsToDrop as $column) {
        $result = $pdo->query("SHOW COLUMNS FROM enrollment_submissions WHERE Field = '$column'");
        if ($result->rowCount() > 0) {
            try {
                $pdo->exec("ALTER TABLE enrollment_submissions DROP COLUMN `$column`");
                echo "   ✓ $column dropped<br>";
            } catch (PDOException $e) {
                echo "   ✗ Failed to drop $column: " . $e->getMessage() . "<br>";
                throw $e;
            }
        } else {
            echo "   ✓ $column already removed<br>";
        }
    }
    
    // Re-enable foreign key checks
    echo "4. Re-enabling foreign key checks...<br>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Migration completed successfully!</h3>";
    echo "<p>The enrollment_submissions table has been updated.</p>";
    echo "<p><a href='enrollment'>Test the enrollment form</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Migration failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Ensure foreign key checks are enabled again.</p>";
}
?>
