<?php
// Create database if it doesn't exist

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'sped_lms';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

echo "<h2>Creating Database</h2>";
echo "<pre>";

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    echo "Creating database '$dbname'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "✓ Database '$dbname' created successfully!\n\n";
    echo "Next step: <a href='run-migration.php'>Run Schema Migration</a>\n";
    echo "Or go back to: <a href='check-database.php'>Database Check</a>\n";
    
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n\n";
    echo "Please check your MySQL credentials in .env file\n";
}

echo "</pre>";
?>
