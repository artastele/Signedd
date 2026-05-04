<?php
// Quick database diagnostic script

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

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

// Test 1: Can we connect to MySQL server?
echo "1. Testing MySQL connection...\n";
try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    echo "   ✓ Connected to MySQL server\n\n";
} catch (PDOException $e) {
    echo "   ✗ FAILED: " . $e->getMessage() . "\n\n";
    echo "SOLUTION: Check your MySQL credentials in .env file\n";
    echo "DB_HOST=$host\n";
    echo "DB_USER=$username\n";
    echo "DB_PASS=" . (empty($password) ? '(empty)' : '(set)') . "\n";
    exit;
}

// Test 2: Does the database exist?
echo "2. Checking if database '$dbname' exists...\n";
try {
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "   ✓ Database '$dbname' exists\n\n";
    } else {
        echo "   ✗ Database '$dbname' does NOT exist\n\n";
        echo "SOLUTION: Create the database by running:\n";
        echo "CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
        
        echo "Or run this script to create it automatically:\n";
        echo "<a href='create-database.php'>Create Database Now</a>\n";
        exit;
    }
} catch (PDOException $e) {
    echo "   ✗ FAILED: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Can we connect to the database?
echo "3. Testing connection to database '$dbname'...\n";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "   ✓ Connected to database '$dbname'\n\n";
} catch (PDOException $e) {
    echo "   ✗ FAILED: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Check if tables exist
echo "4. Checking if tables exist...\n";
$requiredTables = ['users', 'csrf_tokens', 'notifications', 'enrollment_submissions'];
$missingTables = [];

foreach ($requiredTables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "   ✓ Table '$table' exists\n";
    } else {
        echo "   ✗ Table '$table' MISSING\n";
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "\nSOLUTION: Run the schema migration\n";
    echo "The schema will be automatically applied when you first access the application.\n";
    echo "Or you can manually import: config/schema.sql\n\n";
    echo "<a href='run-migration.php'>Run Migration Now</a>\n";
} else {
    echo "\n✓ All required tables exist!\n\n";
    echo "Your database is properly set up.\n";
    echo "You can now <a href='../public/login'>Login</a> or <a href='../public/register'>Register</a>\n";
}

echo "</pre>";
?>
