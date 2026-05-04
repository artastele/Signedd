<?php
// Simple diagnostic: Why can't I login?

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

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'sped_lms';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Why Can't I Login?</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #a01422; }
        .problem { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .solution { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .step { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .btn { display: inline-block; padding: 12px 24px; margin: 10px 5px; background: #a01422; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn:hover { background: #800f1a; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .icon { font-size: 24px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Why Can't I Login?</h1>
        <p>Let me check what's preventing you from logging in...</p>

        <?php
        $canLogin = true;
        $problems = [];
        $steps = [];

        // Check 1: Database connection
        echo "<h2>Checking Database Connection...</h2>";
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='solution'><span class='icon'>✅</span> Database connection successful!</div>";
        } catch (PDOException $e) {
            $canLogin = false;
            echo "<div class='problem'><span class='icon'>❌</span> <strong>Cannot connect to database!</strong><br>";
            echo "Error: " . $e->getMessage() . "</div>";
            
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                $problems[] = "Database '$dbname' does not exist";
                $steps[] = [
                    'title' => 'Create the database',
                    'action' => 'Click here to create database',
                    'link' => 'create-database.php'
                ];
            } else {
                $problems[] = "Cannot connect to MySQL server";
                $steps[] = [
                    'title' => 'Check MySQL is running',
                    'action' => 'Start XAMPP/WAMP and ensure MySQL is running'
                ];
                $steps[] = [
                    'title' => 'Check database credentials in .env',
                    'action' => 'Verify DB_HOST, DB_USER, DB_PASS are correct'
                ];
            }
        }

        // Check 2: Required tables exist
        if ($canLogin) {
            echo "<h2>Checking Database Tables...</h2>";
            
            $requiredTables = ['users', 'csrf_tokens'];
            $missingTables = [];
            
            foreach ($requiredTables as $table) {
                try {
                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                    if (!$stmt->fetch()) {
                        $missingTables[] = $table;
                    }
                } catch (PDOException $e) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                echo "<div class='solution'><span class='icon'>✅</span> All required tables exist!</div>";
            } else {
                $canLogin = false;
                echo "<div class='problem'><span class='icon'>❌</span> <strong>Missing database tables!</strong><br>";
                echo "Missing: " . implode(', ', $missingTables) . "</div>";
                
                $problems[] = "Database tables are not created";
                $steps[] = [
                    'title' => 'Run database migration',
                    'action' => 'Click here to create all tables',
                    'link' => 'run-migration.php'
                ];
            }
        }

        // Check 3: Admin user exists
        if ($canLogin) {
            echo "<h2>Checking Admin User...</h2>";
            
            try {
                $stmt = $pdo->query("SELECT * FROM users WHERE email = 'admin@spedlms.local' LIMIT 1");
                $admin = $stmt->fetch();
                
                if ($admin) {
                    echo "<div class='solution'><span class='icon'>✅</span> Admin user exists!</div>";
                    echo "<div class='warning'>";
                    echo "<strong>Default Admin Credentials:</strong><br>";
                    echo "Email: <code>admin@spedlms.local</code><br>";
                    echo "Password: <code>password</code>";
                    echo "</div>";
                } else {
                    echo "<div class='warning'><span class='icon'>⚠️</span> Admin user not found. You can register a new account instead.</div>";
                }
            } catch (PDOException $e) {
                echo "<div class='warning'><span class='icon'>⚠️</span> Could not check for admin user: " . $e->getMessage() . "</div>";
            }
        }

        // Check 4: CSRF token table
        if ($canLogin) {
            echo "<h2>Checking CSRF Protection...</h2>";
            
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM csrf_tokens");
                $count = $stmt->fetchColumn();
                echo "<div class='solution'><span class='icon'>✅</span> CSRF protection is set up! ($count tokens in database)</div>";
            } catch (PDOException $e) {
                echo "<div class='warning'><span class='icon'>⚠️</span> CSRF table issue (but should work in development mode)</div>";
            }
        }

        // Summary
        echo "<hr>";
        echo "<h2>Summary</h2>";
        
        if ($canLogin) {
            echo "<div class='solution'>";
            echo "<h3><span class='icon'>🎉</span> You CAN login!</h3>";
            echo "<p>Everything is set up correctly. You should be able to login now.</p>";
            echo "<a href='login' class='btn'>Go to Login Page</a>";
            echo "<a href='test-login.php' class='btn' style='background: #1e4072;'>Test Login (No CSRF)</a>";
            echo "</div>";
        } else {
            echo "<div class='problem'>";
            echo "<h3><span class='icon'>❌</span> You CANNOT login yet</h3>";
            echo "<p><strong>Problems found:</strong></p>";
            echo "<ul>";
            foreach ($problems as $problem) {
                echo "<li>$problem</li>";
            }
            echo "</ul>";
            echo "</div>";
            
            echo "<h3>Follow these steps to fix:</h3>";
            foreach ($steps as $i => $step) {
                $num = $i + 1;
                echo "<div class='step'>";
                echo "<strong>Step $num: {$step['title']}</strong><br>";
                if (isset($step['link'])) {
                    echo "<a href='{$step['link']}' class='btn'>{$step['action']}</a>";
                } else {
                    echo "<p>{$step['action']}</p>";
                }
                echo "</div>";
            }
        }

        // Additional help
        echo "<hr>";
        echo "<h3>Other Helpful Tools</h3>";
        echo "<a href='system-status.php' class='btn' style='background: #6c757d;'>Complete System Status</a>";
        echo "<a href='check-database.php' class='btn' style='background: #6c757d;'>Database Check</a>";
        ?>

    </div>
</body>
</html>
