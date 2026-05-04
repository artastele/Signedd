<?php
// Complete system status checker

// Load environment variables
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
$appEnv = getenv('APP_ENV') ?: 'production';

?>
<!DOCTYPE html>
<html>
<head>
    <title>SPED LMS - System Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #a01422; margin-bottom: 10px; }
        h2 { color: #1e4072; margin-top: 30px; border-bottom: 2px solid #1e4072; padding-bottom: 10px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        .check { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; }
        .check-title { font-weight: bold; margin-bottom: 5px; }
        .check-result { margin-left: 20px; }
        .icon-ok { color: #28a745; font-weight: bold; }
        .icon-fail { color: #dc3545; font-weight: bold; }
        .icon-warn { color: #ffc107; font-weight: bold; }
        .actions { margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #a01422; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #800f1a; }
        .btn-secondary { background: #1e4072; }
        .btn-secondary:hover { background: #152f56; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1e4072; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 SPED LMS System Status</h1>
        <p>Complete system diagnostic and setup verification</p>

        <?php
        $allGood = true;
        $issues = [];
        $warnings = [];

        // Check 1: PHP Version
        echo "<h2>1. PHP Environment</h2>";
        echo "<div class='check'>";
        echo "<div class='check-title'>PHP Version</div>";
        echo "<div class='check-result'>";
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            echo "<span class='icon-ok'>✓</span> PHP $phpVersion (OK)";
        } else {
            echo "<span class='icon-fail'>✗</span> PHP $phpVersion (Need 7.4+)";
            $allGood = false;
            $issues[] = "PHP version too old. Need 7.4 or higher.";
        }
        echo "</div></div>";

        // Check 2: Required Extensions
        echo "<div class='check'>";
        echo "<div class='check-title'>Required PHP Extensions</div>";
        echo "<div class='check-result'>";
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo "<span class='icon-ok'>✓</span> $ext<br>";
            } else {
                echo "<span class='icon-fail'>✗</span> $ext (MISSING)<br>";
                $allGood = false;
                $issues[] = "PHP extension '$ext' is not installed.";
            }
        }
        echo "</div></div>";

        // Check 3: Environment File
        echo "<h2>2. Configuration</h2>";
        echo "<div class='check'>";
        echo "<div class='check-title'>.env File</div>";
        echo "<div class='check-result'>";
        if (file_exists($envFile)) {
            echo "<span class='icon-ok'>✓</span> .env file exists<br>";
            echo "<span class='icon-ok'>✓</span> APP_ENV: $appEnv<br>";
            if ($appEnv === 'development') {
                echo "<span class='icon-warn'>⚠</span> Development mode enabled (CSRF validation lenient)";
            }
        } else {
            echo "<span class='icon-fail'>✗</span> .env file not found";
            $allGood = false;
            $issues[] = ".env file is missing. Copy .env.example to .env";
        }
        echo "</div></div>";

        // Check 4: Database Connection
        echo "<h2>3. Database</h2>";
        $dbConnected = false;
        $dbExists = false;
        $tablesExist = false;

        echo "<div class='check'>";
        echo "<div class='check-title'>MySQL Connection</div>";
        echo "<div class='check-result'>";
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<span class='icon-ok'>✓</span> Connected to MySQL server<br>";
            echo "Host: $host<br>";
            echo "User: $username<br>";
            $dbConnected = true;
        } catch (PDOException $e) {
            echo "<span class='icon-fail'>✗</span> Cannot connect to MySQL<br>";
            echo "Error: " . $e->getMessage();
            $allGood = false;
            $issues[] = "Cannot connect to MySQL. Check DB_HOST, DB_USER, DB_PASS in .env";
        }
        echo "</div></div>";

        if ($dbConnected) {
            echo "<div class='check'>";
            echo "<div class='check-title'>Database '$dbname'</div>";
            echo "<div class='check-result'>";
            try {
                $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
                if ($stmt->fetch()) {
                    echo "<span class='icon-ok'>✓</span> Database exists<br>";
                    $dbExists = true;
                    
                    // Connect to database
                    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } else {
                    echo "<span class='icon-fail'>✗</span> Database does not exist<br>";
                    $allGood = false;
                    $issues[] = "Database '$dbname' not found. Run create-database.php";
                }
            } catch (PDOException $e) {
                echo "<span class='icon-fail'>✗</span> Error: " . $e->getMessage();
                $allGood = false;
            }
            echo "</div></div>";
        }

        if ($dbExists) {
            echo "<div class='check'>";
            echo "<div class='check-title'>Database Tables</div>";
            echo "<div class='check-result'>";
            
            $requiredTables = [
                'users', 'csrf_tokens', 'notifications', 'role_requests',
                'enrollment_submissions', 'enrollment_documents', 'student_records',
                'assessment_records', 'iep_meetings', 'iep_documents',
                'activity_log', 'login_log'
            ];
            
            $missingTables = [];
            $existingTables = [];
            
            foreach ($requiredTables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->fetch()) {
                    $existingTables[] = $table;
                } else {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                echo "<span class='icon-ok'>✓</span> All required tables exist (" . count($existingTables) . " tables)<br>";
                $tablesExist = true;
                
                // Check admin user
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                $adminCount = $stmt->fetchColumn();
                if ($adminCount > 0) {
                    echo "<span class='icon-ok'>✓</span> Admin user exists<br>";
                } else {
                    echo "<span class='icon-warn'>⚠</span> No admin user found<br>";
                    $warnings[] = "No admin user found. Default admin may not be created.";
                }
            } else {
                echo "<span class='icon-fail'>✗</span> Missing tables: " . implode(', ', $missingTables) . "<br>";
                echo "<span class='icon-ok'>✓</span> Existing tables: " . implode(', ', $existingTables) . "<br>";
                $allGood = false;
                $issues[] = "Database tables are missing. Run run-migration.php";
            }
            echo "</div></div>";
        }

        // Check 5: File Permissions
        echo "<h2>4. File System</h2>";
        $directories = [
            'public/uploads' => 'File uploads',
            'logs' => 'Error logs'
        ];
        
        foreach ($directories as $dir => $desc) {
            echo "<div class='check'>";
            echo "<div class='check-title'>$desc ($dir)</div>";
            echo "<div class='check-result'>";
            
            $fullPath = __DIR__ . '/../' . $dir;
            if (file_exists($fullPath)) {
                if (is_writable($fullPath)) {
                    echo "<span class='icon-ok'>✓</span> Directory exists and is writable";
                } else {
                    echo "<span class='icon-warn'>⚠</span> Directory exists but not writable";
                    $warnings[] = "Directory '$dir' is not writable. Run: chmod 755 $dir";
                }
            } else {
                echo "<span class='icon-warn'>⚠</span> Directory does not exist";
                $warnings[] = "Directory '$dir' does not exist. Create it manually.";
            }
            echo "</div></div>";
        }

        // Check 6: Composer Dependencies
        echo "<h2>5. Dependencies</h2>";
        echo "<div class='check'>";
        echo "<div class='check-title'>Composer Packages</div>";
        echo "<div class='check-result'>";
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            echo "<span class='icon-ok'>✓</span> Composer dependencies installed<br>";
            
            // Check PHPMailer
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                echo "<span class='icon-ok'>✓</span> PHPMailer available<br>";
            } else {
                echo "<span class='icon-warn'>⚠</span> PHPMailer not found<br>";
                $warnings[] = "PHPMailer not available. Email features may not work.";
            }
            
            // Check Google Client
            if (class_exists('Google_Client')) {
                echo "<span class='icon-ok'>✓</span> Google API Client available<br>";
            } else {
                echo "<span class='icon-warn'>⚠</span> Google API Client not found (optional)<br>";
            }
        } else {
            echo "<span class='icon-warn'>⚠</span> Composer dependencies not installed<br>";
            $warnings[] = "Run 'composer install' to install dependencies.";
        }
        echo "</div></div>";

        // Summary
        echo "<h2>Summary</h2>";
        
        if ($allGood && empty($warnings)) {
            echo "<div class='status success'>";
            echo "<strong>✓ System is ready!</strong><br>";
            echo "All checks passed. You can now use the system.";
            echo "</div>";
        } elseif ($allGood && !empty($warnings)) {
            echo "<div class='status warning'>";
            echo "<strong>⚠ System is functional with warnings</strong><br>";
            echo "The system will work, but some features may be limited:";
            echo "<ul>";
            foreach ($warnings as $warning) {
                echo "<li>$warning</li>";
            }
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='status error'>";
            echo "<strong>✗ System setup incomplete</strong><br>";
            echo "Please fix these issues:";
            echo "<ul>";
            foreach ($issues as $issue) {
                echo "<li>$issue</li>";
            }
            echo "</ul>";
            if (!empty($warnings)) {
                echo "<br><strong>Warnings:</strong>";
                echo "<ul>";
                foreach ($warnings as $warning) {
                    echo "<li>$warning</li>";
                }
                echo "</ul>";
            }
            echo "</div>";
        }

        // Actions
        echo "<div class='actions'>";
        echo "<h3>Quick Actions</h3>";
        
        if (!$dbExists) {
            echo "<a href='create-database.php' class='btn'>1. Create Database</a>";
        }
        
        if ($dbExists && !$tablesExist) {
            echo "<a href='run-migration.php' class='btn'>2. Run Migration</a>";
        }
        
        if ($tablesExist) {
            echo "<a href='test-login.php' class='btn btn-secondary'>Test Login</a>";
            echo "<a href='login' class='btn btn-secondary'>Go to Login</a>";
            echo "<a href='register' class='btn btn-secondary'>Register</a>";
        }
        
        echo "<a href='system-status.php' class='btn btn-secondary'>Refresh Status</a>";
        echo "</div>";

        // Default Credentials
        if ($tablesExist) {
            echo "<div class='status info'>";
            echo "<strong>Default Admin Credentials</strong><br>";
            echo "Email: <code>admin@spedlms.local</code><br>";
            echo "Password: <code>password</code><br>";
            echo "<small>⚠️ Change this password after first login!</small>";
            echo "</div>";
        }
        ?>

    </div>
</body>
</html>
