<?php
// Simple login test without CSRF

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

// Start session
session_start();

// Connect to database
require_once __DIR__ . '/../config/db.php';

echo "<h2>Login Test (No CSRF)</h2>";
echo "<pre>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "Testing login for: $email\n\n";
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Find user
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "✗ User not found\n";
        } else {
            echo "✓ User found: {$user['name']}\n";
            echo "  Role: {$user['role']}\n";
            echo "  Status: {$user['status']}\n";
            echo "  Email Verified: " . ($user['email_verified'] ? 'Yes' : 'No') . "\n\n";
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                echo "✓ Password correct\n\n";
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email_verified'] = $user['email_verified'];
                
                echo "✓ Session created\n";
                echo "  Session ID: " . session_id() . "\n\n";
                
                echo "You can now go to: <a href='../public/dashboard'>Dashboard</a>\n";
            } else {
                echo "✗ Password incorrect\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "</pre>";
} else {
    echo "</pre>";
    echo "<form method='POST'>";
    echo "<p><label>Email: <input type='email' name='email' value='admin@spedlms.local' required></label></p>";
    echo "<p><label>Password: <input type='password' name='password' value='password' required></label></p>";
    echo "<p><button type='submit'>Test Login</button></p>";
    echo "</form>";
    
    echo "<hr>";
    echo "<h3>Quick Links:</h3>";
    echo "<ul>";
    echo "<li><a href='check-database.php'>Check Database Setup</a></li>";
    echo "<li><a href='create-database.php'>Create Database</a></li>";
    echo "<li><a href='run-migration.php'>Run Migration</a></li>";
    echo "<li><a href='login'>Normal Login Page</a></li>";
    echo "</ul>";
}
?>
