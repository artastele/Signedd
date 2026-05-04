<?php
// SPED LMS — Setup Verification Script
// Run this file to verify your installation

echo "<h1>SPED LMS Setup Verification</h1>";
echo "<hr>";

// Check PHP version
echo "<h3>1. PHP Version</h3>";
$phpVersion = phpversion();
echo "Current: <strong>$phpVersion</strong> ";
echo version_compare($phpVersion, '7.4.0', '>=') ? "✓ OK" : "✗ FAIL (requires 7.4+)";
echo "<br><br>";

// Check required extensions
echo "<h3>2. Required PHP Extensions</h3>";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "✓ Loaded" : "✗ Missing") . "<br>";
}
echo "<br>";

// Check directory permissions
echo "<h3>3. Directory Permissions</h3>";
$dirs = [
    'public/uploads' => 'public/uploads',
    'public/uploads/enrollment' => 'public/uploads/enrollment',
    'public/uploads/role_verification' => 'public/uploads/role_verification',
    'logs' => 'logs'
];

foreach ($dirs as $label => $dir) {
    $writable = is_writable($dir);
    echo "$label: " . ($writable ? "✓ Writable" : "✗ Not writable") . "<br>";
}
echo "<br>";

// Check configuration files
echo "<h3>4. Configuration Files</h3>";
$files = [
    '.env' => '.env (copy from .env.example)',
    'config/db.php' => 'config/db.php',
    'config/schema.sql' => 'config/schema.sql',
    'config/permissions.php' => 'config/permissions.php'
];

foreach ($files as $file => $label) {
    $exists = file_exists($file);
    echo "$label: " . ($exists ? "✓ Exists" : "✗ Missing") . "<br>";
}
echo "<br>";

// Check database connection
echo "<h3>5. Database Connection</h3>";
if (file_exists('.env')) {
    echo "✓ .env file found<br>";
    echo "Attempting database connection...<br>";
    
    try {
        require_once 'config/db.php';
        $db = Database::getInstance()->getConnection();
        echo "✓ <strong>Database connection successful!</strong><br>";
        
        // Check if schema is applied
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables found: " . count($tables) . "<br>";
        
        if (count($tables) > 0) {
            echo "✓ Schema appears to be applied<br>";
        } else {
            echo "⚠ No tables found. Schema will be applied on first access.<br>";
        }
        
    } catch (Exception $e) {
        echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
        echo "Please check your .env configuration.<br>";
    }
} else {
    echo "✗ .env file not found. Copy .env.example to .env and configure.<br>";
}
echo "<br>";

// Summary
echo "<hr>";
echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>If .env is missing, copy .env.example to .env and configure database credentials</li>";
echo "<li>Ensure all directories are writable</li>";
echo "<li>Access the application at <a href='public/index.php'>public/index.php</a></li>";
echo "<li>Default admin login: admin@spedlms.local / password</li>";
echo "<li><strong>Change the default admin password immediately!</strong></li>";
echo "</ol>";

echo "<p><a href='public/index.php' style='display:inline-block;padding:10px 20px;background:#a01422;color:white;text-decoration:none;border-radius:6px;'>Launch SPED LMS</a></p>";
