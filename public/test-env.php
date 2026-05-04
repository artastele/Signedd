<?php
// Test if .env is loaded

// Load .env manually (same as index.php)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (empty($line) || strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Environment Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #a01422; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1e4072; color: white; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Environment Variables Test</h1>
        
        <h2>Critical Variables</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td><code>APP_ENV</code></td>
                <td><?php echo getenv('APP_ENV') ?: '(not set)'; ?></td>
                <td><?php echo getenv('APP_ENV') === 'development' ? '<span class="success">✓ Development Mode</span>' : '<span class="error">✗ Not Development</span>'; ?></td>
            </tr>
            <tr>
                <td><code>DB_HOST</code></td>
                <td><?php echo getenv('DB_HOST') ?: '(not set)'; ?></td>
                <td><?php echo getenv('DB_HOST') ? '<span class="success">✓ Set</span>' : '<span class="error">✗ Not Set</span>'; ?></td>
            </tr>
            <tr>
                <td><code>DB_NAME</code></td>
                <td><?php echo getenv('DB_NAME') ?: '(not set)'; ?></td>
                <td><?php echo getenv('DB_NAME') ? '<span class="success">✓ Set</span>' : '<span class="error">✗ Not Set</span>'; ?></td>
            </tr>
            <tr>
                <td><code>DB_USER</code></td>
                <td><?php echo getenv('DB_USER') ?: '(not set)'; ?></td>
                <td><?php echo getenv('DB_USER') ? '<span class="success">✓ Set</span>' : '<span class="error">✗ Not Set</span>'; ?></td>
            </tr>
            <tr>
                <td><code>ENCRYPTION_KEY</code></td>
                <td><?php echo getenv('ENCRYPTION_KEY') ? '(set - ' . strlen(getenv('ENCRYPTION_KEY')) . ' chars)' : '(not set)'; ?></td>
                <td><?php echo getenv('ENCRYPTION_KEY') ? '<span class="success">✓ Set</span>' : '<span class="error">✗ Not Set</span>'; ?></td>
            </tr>
        </table>

        <h2>What This Means</h2>
        <?php if (getenv('APP_ENV') === 'development'): ?>
            <div style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
                <strong>✓ Development Mode is ACTIVE</strong><br>
                CSRF validation will be lenient and won't block your login attempts.
            </div>
        <?php else: ?>
            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
                <strong>✗ Development Mode is NOT active</strong><br>
                This is why CSRF validation is failing! The .env file is not being loaded properly.
            </div>
        <?php endif; ?>

        <h2>Next Steps</h2>
        <p>Now try to login again:</p>
        <a href="login" style="display: inline-block; padding: 10px 20px; background: #a01422; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a>
        <a href="why-cant-i-login.php" style="display: inline-block; padding: 10px 20px; background: #1e4072; color: white; text-decoration: none; border-radius: 5px;">Check System Status</a>
    </div>
</body>
</html>
