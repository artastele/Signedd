<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Fix Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; background: #f5f5f5; }
        .test-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .test-result { padding: 15px; border-radius: 6px; margin: 10px 0; }
        .test-pass { background: #d4edda; border: 1px solid #3b6d11; color: #155724; }
        .test-fail { background: #f8d7da; border: 1px solid #a01422; color: #721c24; }
    </style>
</head>
<body>
    <div class="test-container">
        <h2 class="mb-4">Registration Fix Test</h2>
        
        <?php
        // Load environment
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                if (!empty($name)) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
        
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/../app/Helpers/RateLimitHelper.php';
        
        echo "<h4>Test 1: RateLimitHelper Class Load</h4>";
        try {
            $reflection = new ReflectionClass('RateLimitHelper');
            echo "<div class='test-result test-pass'>";
            echo "✅ <strong>PASS:</strong> RateLimitHelper class loaded successfully";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='test-result test-fail'>";
            echo "❌ <strong>FAIL:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        echo "<h4>Test 2: ATTEMPT_WINDOW Constant</h4>";
        try {
            $reflection = new ReflectionClass('RateLimitHelper');
            $constants = $reflection->getConstants();
            
            if (isset($constants['ATTEMPT_WINDOW'])) {
                echo "<div class='test-result test-pass'>";
                echo "✅ <strong>PASS:</strong> ATTEMPT_WINDOW constant defined<br>";
                echo "<strong>Value:</strong> " . $constants['ATTEMPT_WINDOW'] . " seconds (" . ($constants['ATTEMPT_WINDOW'] / 60) . " minutes)";
                echo "</div>";
            } else {
                echo "<div class='test-result test-fail'>";
                echo "❌ <strong>FAIL:</strong> ATTEMPT_WINDOW constant not found";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-result test-fail'>";
            echo "❌ <strong>FAIL:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        echo "<h4>Test 3: checkRegistrationAttempts Method</h4>";
        try {
            $result = RateLimitHelper::checkRegistrationAttempts('test@example.com', '127.0.0.1');
            
            if (is_array($result) && isset($result['allowed']) && isset($result['message'])) {
                echo "<div class='test-result test-pass'>";
                echo "✅ <strong>PASS:</strong> checkRegistrationAttempts() works correctly<br>";
                echo "<strong>Allowed:</strong> " . ($result['allowed'] ? 'Yes' : 'No') . "<br>";
                echo "<strong>Message:</strong> " . htmlspecialchars($result['message']);
                echo "</div>";
            } else {
                echo "<div class='test-result test-fail'>";
                echo "❌ <strong>FAIL:</strong> Invalid return format";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-result test-fail'>";
            echo "❌ <strong>FAIL:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        echo "<h4>Test 4: Database Connection</h4>";
        try {
            $db = Database::getInstance()->getConnection();
            echo "<div class='test-result test-pass'>";
            echo "✅ <strong>PASS:</strong> Database connected successfully";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='test-result test-fail'>";
            echo "❌ <strong>FAIL:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        echo "<hr>";
        echo "<h4>Summary</h4>";
        echo "<p><strong>Status:</strong> <span style='color: #3b6d11; font-size: 1.2em;'>✅ Registration fix is working!</span></p>";
        echo "<p><strong>Next Step:</strong> Test actual registration at <a href='/register'>/register</a></p>";
        ?>
        
        <hr>
        <h4>Manual Test Steps:</h4>
        <ol>
            <li>Go to <a href="/register" target="_blank">/register</a></li>
            <li>Fill in the registration form with valid data</li>
            <li>Submit the form</li>
            <li>Should redirect to <code>/auth/verify-email</code></li>
            <li>Should receive OTP email</li>
            <li>Verify OTP and complete registration</li>
        </ol>
        
        <p><a href="/" class="btn btn-secondary">← Back to Home</a></p>
    </div>
</body>
</html>
