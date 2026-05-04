<?php
// Run database schema migration

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

echo "<h2>Running Schema Migration</h2>";
echo "<pre>";

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read schema file
    $schemaFile = __DIR__ . '/../config/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    echo "Reading schema file...\n";
    $sql = file_get_contents($schemaFile);
    
    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && strpos($stmt, '--') !== 0;
        }
    );
    
    echo "Executing " . count($statements) . " SQL statements...\n\n";
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $pdo->exec($statement);
            $executed++;
            
            // Show progress for CREATE TABLE statements
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            }
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "✗ Error: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }
    
    echo "\n";
    echo "Executed: $executed statements\n";
    echo "Errors: $errors\n\n";
    
    if ($errors === 0) {
        echo "✓ Schema migration completed successfully!\n\n";
        echo "You can now:\n";
        echo "- <a href='check-database.php'>Verify Database Setup</a>\n";
        echo "- <a href='login'>Login</a> (Default: admin@spedlms.local / password)\n";
        echo "- <a href='register'>Register</a> a new account\n";
    } else {
        echo "⚠ Migration completed with some errors. Check the messages above.\n";
    }
    
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n\n";
    echo "Please make sure:\n";
    echo "1. Database '$dbname' exists (run create-database.php first)\n";
    echo "2. MySQL credentials in .env are correct\n";
}

echo "</pre>";
?>
