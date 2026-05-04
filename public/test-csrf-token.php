<?php
/**
 * Test CSRF Token Generation
 */

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/CSRFHelper.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Token Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; padding: 2rem; }
        .card { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">CSRF Token Test</h5>
        </div>
        <div class="card-body">
            <h6>Session ID:</h6>
            <code><?php echo session_id(); ?></code>
            
            <h6 class="mt-3">Generated Token:</h6>
            <code><?php 
                try {
                    $token = CSRFHelper::getToken();
                    echo $token;
                } catch (Exception $e) {
                    echo 'ERROR: ' . $e->getMessage();
                }
            ?></code>
            
            <h6 class="mt-3">Token Length:</h6>
            <p><?php echo strlen($token ?? ''); ?> characters</p>
            
            <h6 class="mt-3">Test Form:</h6>
            <form method="POST" action="/Signedd/public/login">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                <input type="email" name="email" class="form-control mb-2" placeholder="Email" value="test@example.com">
                <input type="password" name="password" class="form-control mb-2" placeholder="Password" value="password">
                <button type="submit" class="btn btn-danger w-100">Test Login</button>
            </form>
            
            <div class="alert alert-info mt-3">
                <strong>Note:</strong> This form includes a CSRF token. Try submitting it to test if CSRF protection is working.
            </div>
        </div>
    </div>
</body>
</html>
