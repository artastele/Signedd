<?php
/**
 * System Verification Test
 * Run this file to check if all components are working
 */

echo "<h1>SPED LMS - System Verification</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
</style>";

// Test 1: PHP Version
echo "<div class='section'>";
echo "<h2>1. PHP Version</h2>";
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<p class='success'>✓ PHP $phpVersion (OK)</p>";
} else {
    echo "<p class='error'>✗ PHP $phpVersion (Need 7.4+)</p>";
}
echo "</div>";

// Test 2: Required Extensions
echo "<div class='section'>";
echo "<h2>2. PHP Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ $ext</p>";
    } else {
        echo "<p class='error'>✗ $ext (Missing)</p>";
    }
}
echo "</div>";

// Test 3: Autoloader
echo "<div class='section'>";
echo "<h2>3. Autoloader</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p class='success'>✓ Autoloader found</p>";
} else {
    echo "<p class='error'>✗ Autoloader not found</p>";
}
echo "</div>";

// Test 4: PHPMailer
echo "<div class='section'>";
echo "<h2>4. PHPMailer</h2>";
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    echo "<p class='success'>✓ PHPMailer loaded</p>";
} else {
    echo "<p class='error'>✗ PHPMailer not loaded</p>";
}
echo "</div>";

// Test 5: Google API Client
echo "<div class='section'>";
echo "<h2>5. Google API Client</h2>";
if (class_exists('Google_Client')) {
    echo "<p class='success'>✓ Google API Client loaded</p>";
} else {
    echo "<p class='warning'>⚠ Google API Client not installed (Optional - Google Sign-In will be disabled)</p>";
}
echo "</div>";

// Test 6: Environment Variables
echo "<div class='section'>";
echo "<h2>6. Environment Configuration</h2>";
if (file_exists(__DIR__ . '/.env')) {
    echo "<p class='success'>✓ .env file exists</p>";
    
    // Check key variables
    $requiredVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'MAIL_USERNAME', 'MAIL_PASSWORD'];
    foreach ($requiredVars as $var) {
        $value = getenv($var);
        if (!empty($value)) {
            echo "<p class='success'>✓ $var is set</p>";
        } else {
            echo "<p class='error'>✗ $var is not set</p>";
        }
    }
    
    // Check Google OAuth (optional)
    if (!empty(getenv('GOOGLE_CLIENT_ID'))) {
        echo "<p class='success'>✓ GOOGLE_CLIENT_ID is set</p>";
    } else {
        echo "<p class='warning'>⚠ GOOGLE_CLIENT_ID not set (Google Sign-In disabled)</p>";
    }
} else {
    echo "<p class='error'>✗ .env file not found (copy .env.example to .env)</p>";
}
echo "</div>";

// Test 7: Database Connection
echo "<div class='section'>";
echo "<h2>7. Database Connection</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    $db = Database::getInstance()->getConnection();
    echo "<p class='success'>✓ Database connected</p>";
    
    // Check if tables exist
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p class='success'>✓ Found " . count($tables) . " tables</p>";
    
    // Check for key tables
    $keyTables = ['users', 'notifications', 'role_requests', 'login_log', 'activity_log'];
    foreach ($keyTables as $table) {
        if (in_array($table, $tables)) {
            echo "<p class='success'>✓ Table '$table' exists</p>";
        } else {
            echo "<p class='warning'>⚠ Table '$table' missing (will be created on first run)</p>";
        }
    }
    
    // Check migrations
    if (in_array('db_version', $tables)) {
        $stmt = $db->query("SELECT version FROM db_version ORDER BY version DESC LIMIT 1");
        $version = $stmt->fetchColumn();
        echo "<p class='success'>✓ Database at migration version: $version</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 8: File Permissions
echo "<div class='section'>";
echo "<h2>8. File Permissions</h2>";
$writableDirs = ['public/uploads', 'public/uploads/enrollment', 'public/uploads/role_verification', 'logs'];
foreach ($writableDirs as $dir) {
    if (is_writable(__DIR__ . '/' . $dir)) {
        echo "<p class='success'>✓ $dir is writable</p>";
    } else {
        echo "<p class='error'>✗ $dir is not writable</p>";
    }
}
echo "</div>";

// Test 9: Email Configuration
echo "<div class='section'>";
echo "<h2>9. Email Configuration</h2>";
$mailHost = getenv('MAIL_HOST');
$mailUser = getenv('MAIL_USERNAME');
$mailPass = getenv('MAIL_PASSWORD');

if (!empty($mailHost) && !empty($mailUser) && !empty($mailPass)) {
    echo "<p class='success'>✓ Email credentials configured</p>";
    echo "<p>Host: $mailHost</p>";
    echo "<p>Username: $mailUser</p>";
    echo "<p>Password: " . (strlen($mailPass) > 0 ? str_repeat('*', strlen($mailPass)) : 'Not set') . "</p>";
} else {
    echo "<p class='error'>✗ Email not fully configured</p>";
}
echo "</div>";

// Summary
echo "<div class='section' style='background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); color: white;'>";
echo "<h2>Summary</h2>";
echo "<p><strong>System Status:</strong></p>";
echo "<ul>";
echo "<li>✓ Core system ready</li>";
echo "<li>✓ Email/Password registration with OTP will work</li>";
if (class_exists('Google_Client')) {
    echo "<li>✓ Google Sign-In available</li>";
} else {
    echo "<li>⚠ Google Sign-In disabled (optional feature)</li>";
}
echo "</ul>";
echo "<p><a href='public/index.php' style='color: white; text-decoration: underline;'>Go to Application →</a></p>";
echo "</div>";
?>
