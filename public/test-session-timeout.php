<?php
// TEST SCRIPT: Session Timeout Fix Verification
// This script helps test if session timeout redirects work correctly

// Define BASE_PATH first (simulating index.php)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName === '/' ? '' : $scriptName;
define('BASE_PATH', $basePath);

// Start session
require_once __DIR__ . '/../app/Middleware/SessionMiddleware.php';
SessionMiddleware::start();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Timeout Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #a01422; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .code { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        button { background: #a01422; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #8a1120; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Session Timeout Test</h1>
        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A'); ?></p>

        <h2>1. BASE_PATH Configuration</h2>
        <?php if (defined('BASE_PATH')): ?>
            <div class="success">
                ✅ BASE_PATH is defined: <code><?php echo BASE_PATH === '' ? '(empty - root)' : BASE_PATH; ?></code>
            </div>
        <?php else: ?>
            <div class="error">
                ❌ BASE_PATH is NOT defined
            </div>
        <?php endif; ?>

        <h2>2. Session Status</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="info">
                ℹ️ You are logged in as User ID: <?php echo $_SESSION['user_id']; ?><br>
                Role: <?php echo $_SESSION['role'] ?? 'Unknown'; ?><br>
                Last Activity: <?php echo isset($_SESSION['last_activity']) ? date('g:i:s A', $_SESSION['last_activity']) : 'Not set'; ?>
            </div>
            
            <h3>Time Remaining</h3>
            <div class="code">
                <?php
                $remaining = SessionMiddleware::getTimeRemaining();
                $minutes = floor($remaining / 60);
                $seconds = $remaining % 60;
                echo "Time until timeout: {$minutes} minutes, {$seconds} seconds";
                ?>
            </div>

            <h3>Test Session Timeout</h3>
            <p>To test timeout redirect:</p>
            <ol>
                <li>Wait for session to expire (15 minutes), OR</li>
                <li>Manually expire session by clicking button below</li>
            </ol>
            
            <form method="POST" action="">
                <button type="submit" name="expire_session">Expire Session Now</button>
            </form>

            <?php
            if (isset($_POST['expire_session'])) {
                // Set last activity to 16 minutes ago (past timeout)
                $_SESSION['last_activity'] = time() - 960; // 16 minutes
                echo '<div class="success">✅ Session expired! Refresh this page to test redirect.</div>';
            }
            ?>

        <?php else: ?>
            <div class="info">
                ℹ️ You are not logged in. Please <a href="<?php echo BASE_PATH; ?>/login">login</a> first to test session timeout.
            </div>
        <?php endif; ?>

        <h2>3. Expected Redirect URL</h2>
        <div class="code">
            <?php
            $expectedRedirect = BASE_PATH . '/login?timeout=1';
            echo "When session expires, you should be redirected to:<br>";
            echo "<strong>$expectedRedirect</strong>";
            ?>
        </div>

        <h2>4. Test Results</h2>
        <div class="info">
            <strong>How to test:</strong>
            <ol>
                <li>Login to the system</li>
                <li>Come back to this page</li>
                <li>Click "Expire Session Now" button</li>
                <li>Refresh this page or navigate to any protected page</li>
                <li>You should be redirected to login page with timeout message</li>
            </ol>
        </div>

        <h2>5. Quick Links</h2>
        <div class="info">
            <a href="<?php echo BASE_PATH; ?>/login">Login Page</a> |
            <a href="<?php echo BASE_PATH; ?>/dashboard">Dashboard</a> |
            <a href="<?php echo BASE_PATH; ?>/enrollment/review">Enrollment Review</a>
        </div>

        <hr>
        <p><small>Test Script: test-session-timeout.php</small></p>
    </div>
</body>
</html>
