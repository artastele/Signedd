<?php
// Route Diagnostic Test
// This file helps diagnose routing issues

echo "<h1>SPED LMS - Route Diagnostic Test</h1>";
echo "<hr>";

// 1. Check PHP version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 7.4 or higher<br>";
echo "<strong>Status: " . (version_compare(phpversion(), '7.4.0', '>=') ? '✅ OK' : '❌ FAIL') . "</strong><br>";

// 2. Check file structure
echo "<hr><h2>2. File Structure</h2>";
$files = [
    'index.php' => __DIR__ . '/index.php',
    '.htaccess' => __DIR__ . '/.htaccess',
    'routes/web.php' => __DIR__ . '/../routes/web.php',
    'config/db.php' => __DIR__ . '/../config/db.php',
];

foreach ($files as $name => $path) {
    echo "$name: " . (file_exists($path) ? '✅ EXISTS' : '❌ MISSING') . "<br>";
}

// 3. Check .htaccess
echo "<hr><h2>3. .htaccess Configuration</h2>";
$htaccessPath = __DIR__ . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($htaccessPath)) . "</pre>";
} else {
    echo "❌ .htaccess file is missing!<br>";
}

// 4. Check Apache mod_rewrite
echo "<hr><h2>4. Apache mod_rewrite</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? '✅ ENABLED' : '❌ DISABLED') . "<br>";
} else {
    echo "⚠️ Cannot detect (apache_get_modules not available)<br>";
}

// 5. Check current request info
echo "<hr><h2>5. Current Request Info</h2>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Calculate base path
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName === '/' ? '' : $scriptName;
echo "<strong>Detected BASE_PATH: " . ($basePath ?: '/') . "</strong><br>";

// 6. Test route parsing
echo "<hr><h2>6. Route Parsing Test</h2>";
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

echo "Original path: $path<br>";

if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . trim($path, '/');

echo "Processed path: $path<br>";

// 7. Test URLs
echo "<hr><h2>7. Test URLs</h2>";
echo "Your installation is at: <strong>$basePath</strong><br><br>";

$testUrls = [
    'Home' => $basePath . '/',
    'Login' => $basePath . '/login',
    'Register' => $basePath . '/register',
    'Dashboard' => $basePath . '/dashboard',
];

echo "<ul>";
foreach ($testUrls as $name => $url) {
    echo "<li><a href='$url' target='_blank'>$name</a> - <code>$url</code></li>";
}
echo "</ul>";

// 8. Database connection test
echo "<hr><h2>8. Database Connection</h2>";
try {
    require_once __DIR__ . '/../config/db.php';
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connection successful<br>";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists<br>";
        
        // Count users
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "Total users: " . $result['count'] . "<br>";
    } else {
        echo "❌ Users table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// 9. Session test
echo "<hr><h2>9. Session Test</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
    echo "Session ID: " . session_id() . "<br>";
    if (isset($_SESSION['user_id'])) {
        echo "✅ User is logged in (ID: " . $_SESSION['user_id'] . ")<br>";
    } else {
        echo "⚠️ No user logged in<br>";
    }
} else {
    echo "❌ Session is not active<br>";
}

// 10. Recommendations
echo "<hr><h2>10. Recommendations</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
echo "<strong>Correct URL to access your application:</strong><br>";
echo "<a href='$basePath/' style='font-size: 18px;'>http://localhost$basePath/</a><br><br>";
echo "<strong>DO NOT use:</strong><br>";
echo "❌ http://localhost$basePath/public/<br>";
echo "❌ http://localhost$basePath/public/login<br><br>";
echo "<strong>USE instead:</strong><br>";
echo "✅ http://localhost$basePath/<br>";
echo "✅ http://localhost$basePath/login<br>";
echo "</div>";

echo "<hr>";
echo "<p><a href='$basePath/'>Go to Home Page</a></p>";
?>
