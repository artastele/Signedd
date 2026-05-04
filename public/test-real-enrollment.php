<?php
// Test Real Enrollment Submission with Browser Console Logging

// Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

session_start();

// Check if user is logged in, if not create test session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'parent';
    $_SESSION['user_name'] = 'Test Parent';
    $_SESSION['email_verified'] = true;
}

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Real Enrollment</title>";
echo "<style>
body { font-family: Arial; padding: 20px; }
.success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }
.error { background: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }
.info { background: #d1ecf1; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #1e4072; color: white; }
</style>";
echo "</head><body>";

echo "<h1>🧪 Test Real Enrollment Submission</h1>";
echo "<hr>";

// Check session
echo "<div class='info'>";
echo "<strong>Session Info:</strong><br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";
echo "Name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "<br>";
echo "</div>";

// Check for session messages
if (isset($_SESSION['success'])) {
    echo "<div class='success'>✅ " . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo "<div class='error'>❌ " . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['errors'])) {
    echo "<div class='error'>❌ Validation Errors:<br>";
    foreach ($_SESSION['errors'] as $error) {
        echo "• $error<br>";
    }
    echo "</div>";
    unset($_SESSION['errors']);
}

// Check PHP error log
echo "<h2>📋 Recent PHP Errors (Enrollment Related)</h2>";
$errorLog = 'C:\\xampp\\php\\logs\\php_error_log';
if (file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -100); // Last 100 lines
    $enrollmentErrors = array_filter($recentLines, function($line) {
        return stripos($line, 'enrollment') !== false || 
               stripos($line, 'SUBMISSION DEBUG') !== false ||
               stripos($line, 'insert') !== false ||
               stripos($line, 'create()') !== false;
    });
    
    if (count($enrollmentErrors) > 0) {
        echo "<div class='error'><pre style='max-height: 400px; overflow-y: auto;'>" . implode('', $enrollmentErrors) . "</pre></div>";
    } else {
        echo "<div class='info'>No enrollment-related errors found in PHP log</div>";
    }
} else {
    echo "<div class='info'>PHP error log not found at: $errorLog</div>";
}

// Check database
echo "<h2>📊 Current Enrollments in Database</h2>";
try {
    require_once __DIR__ . '/../config/db.php';
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("SELECT id, parent_id, first_name, last_name, birth_place, status, is_draft, created_at 
                        FROM enrollment_submissions 
                        ORDER BY id DESC LIMIT 5");
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($enrollments) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Parent</th><th>Name</th><th>Birth Place</th><th>Status</th><th>Draft?</th><th>Created</th></tr>";
        foreach ($enrollments as $e) {
            echo "<tr>";
            echo "<td>{$e['id']}</td>";
            echo "<td>{$e['parent_id']}</td>";
            echo "<td>{$e['first_name']} {$e['last_name']}</td>";
            echo "<td>{$e['birth_place']}</td>";
            echo "<td>{$e['status']}</td>";
            echo "<td>" . ($e['is_draft'] ? 'Yes' : 'No') . "</td>";
            echo "<td>{$e['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>No enrollments found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>Database error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h2>🎯 Next Steps:</h2>";
echo "<ol>";
echo "<li>Go to the enrollment form: <a href='/Signedd/enrollment/create?type=new' target='_blank'>Open Enrollment Form</a></li>";
echo "<li>Fill out the form completely</li>";
echo "<li>Submit the form</li>";
echo "<li>Come back to this page and refresh to see results</li>";
echo "</ol>";

echo "<hr>";
echo "<button onclick='location.reload()' style='padding: 10px 20px; background: #a01422; color: white; border: none; cursor: pointer; border-radius: 5px;'>🔄 Refresh Page</button>";

echo "</body></html>";
?>
