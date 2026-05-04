<?php
/**
 * Security Modules Testing Script
 * Tests all 4 security modules: Encryption, CSRF, Rate Limiting, DLP
 * 
 * Access: http://localhost/Signedd/public/test-security-modules.php
 */

// Start session
session_start();

// Load environment
require_once __DIR__ . '/../vendor/autoload.php';

// Load helpers
require_once __DIR__ . '/../app/Helpers/EncryptionHelper.php';
require_once __DIR__ . '/../app/Helpers/CSRFHelper.php';
require_once __DIR__ . '/../app/Helpers/RateLimitHelper.php';
require_once __DIR__ . '/../app/Helpers/DLPHelper.php';
require_once __DIR__ . '/../config/db.php';

// Set up test session
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['role'] = 'admin';

$results = [];
$testsPassed = 0;
$testsFailed = 0;

// ============================================
// TEST 1: ENCRYPTION HELPER
// ============================================
$results['encryption'] = [];

try {
    // Test 1.1: Basic encryption/decryption
    $plaintext = 'sensitive@email.com';
    $encrypted = EncryptionHelper::encrypt($plaintext);
    $decrypted = EncryptionHelper::decrypt($encrypted);
    
    if ($plaintext === $decrypted) {
        $results['encryption'][] = ['✓ Basic encryption/decryption', 'PASS'];
        $testsPassed++;
    } else {
        $results['encryption'][] = ['✗ Basic encryption/decryption', 'FAIL - Decrypted value does not match'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['encryption'][] = ['✗ Basic encryption/decryption', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 1.2: Empty string handling
    $encrypted = EncryptionHelper::encrypt('');
    $decrypted = EncryptionHelper::decrypt($encrypted);
    
    if ($encrypted === '' && $decrypted === '') {
        $results['encryption'][] = ['✓ Empty string handling', 'PASS'];
        $testsPassed++;
    } else {
        $results['encryption'][] = ['✗ Empty string handling', 'FAIL'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['encryption'][] = ['✗ Empty string handling', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 1.3: Encryption detection
    $plaintext = 'test data';
    $encrypted = EncryptionHelper::encrypt($plaintext);
    
    if (EncryptionHelper::isEncrypted($encrypted) && !EncryptionHelper::isEncrypted($plaintext)) {
        $results['encryption'][] = ['✓ Encryption detection', 'PASS'];
        $testsPassed++;
    } else {
        $results['encryption'][] = ['✗ Encryption detection', 'FAIL'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['encryption'][] = ['✗ Encryption detection', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 1.4: Field-level encryption
    $data = [
        'email' => 'user@example.com',
        'phone' => '09123456789',
        'name' => 'John Doe'
    ];
    $encrypted = EncryptionHelper::encryptFields($data, ['email', 'phone']);
    $decrypted = EncryptionHelper::decryptFields($encrypted, ['email', 'phone']);
    
    if ($decrypted['email'] === $data['email'] && $decrypted['phone'] === $data['phone'] && $decrypted['name'] === $data['name']) {
        $results['encryption'][] = ['✓ Field-level encryption', 'PASS'];
        $testsPassed++;
    } else {
        $results['encryption'][] = ['✗ Field-level encryption', 'FAIL'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['encryption'][] = ['✗ Field-level encryption', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 1.5: Token generation
    $token = EncryptionHelper::generateToken(32);
    
    if (strlen($token) === 64 && ctype_xdigit($token)) { // 32 bytes = 64 hex chars
        $results['encryption'][] = ['✓ Token generation', 'PASS'];
        $testsPassed++;
    } else {
        $results['encryption'][] = ['✗ Token generation', 'FAIL - Invalid token format'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['encryption'][] = ['✗ Token generation', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

// ============================================
// TEST 2: CSRF HELPER
// ============================================
$results['csrf'] = [];

try {
    // Test 2.1: Token generation
    $token = CSRFHelper::generateToken();
    
    if (!empty($token) && strlen($token) > 0) {
        $results['csrf'][] = ['✓ Token generation', 'PASS'];
        $testsPassed++;
    } else {
        $results['csrf'][] = ['✗ Token generation', 'FAIL - Empty token'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['csrf'][] = ['✗ Token generation', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 2.2: Get or create token
    $token1 = CSRFHelper::getToken();
    $token2 = CSRFHelper::getToken();
    
    if ($token1 === $token2) {
        $results['csrf'][] = ['✓ Get or create token (consistency)', 'PASS'];
        $testsPassed++;
    } else {
        $results['csrf'][] = ['✗ Get or create token (consistency)', 'FAIL - Tokens differ'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['csrf'][] = ['✗ Get or create token (consistency)', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 2.3: Token validation (valid token)
    $token = CSRFHelper::generateToken();
    $valid = CSRFHelper::validateToken($token);
    
    if ($valid === true) {
        $results['csrf'][] = ['✓ Token validation (valid token)', 'PASS'];
        $testsPassed++;
    } else {
        $results['csrf'][] = ['✗ Token validation (valid token)', 'FAIL - Valid token rejected'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['csrf'][] = ['✗ Token validation (valid token)', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 2.4: Token validation (invalid token)
    $valid = CSRFHelper::validateToken('invalid_token_12345');
    
    if ($valid === false) {
        $results['csrf'][] = ['✓ Token validation (invalid token)', 'PASS'];
        $testsPassed++;
    } else {
        $results['csrf'][] = ['✗ Token validation (invalid token)', 'FAIL - Invalid token accepted'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['csrf'][] = ['✗ Token validation (invalid token)', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 2.5: Token one-time use
    $token = CSRFHelper::generateToken();
    CSRFHelper::validateToken($token); // First use - should pass
    $secondUse = CSRFHelper::validateToken($token); // Second use - should fail
    
    if ($secondUse === false) {
        $results['csrf'][] = ['✓ Token one-time use', 'PASS'];
        $testsPassed++;
    } else {
        $results['csrf'][] = ['✗ Token one-time use', 'FAIL - Token reused'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['csrf'][] = ['✗ Token one-time use', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

// ============================================
// TEST 3: RATE LIMIT HELPER
// ============================================
$results['ratelimit'] = [];

try {
    // Test 3.1: Check login attempts (no limit)
    $result = RateLimitHelper::checkLoginAttempts('newuser@example.com', '192.168.1.100');
    
    if ($result['allowed'] === true) {
        $results['ratelimit'][] = ['✓ Check login attempts (no limit)', 'PASS'];
        $testsPassed++;
    } else {
        $results['ratelimit'][] = ['✗ Check login attempts (no limit)', 'FAIL - Should allow'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['ratelimit'][] = ['✗ Check login attempts (no limit)', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 3.2: Record login attempt
    $email = 'testuser' . time() . '@example.com';
    RateLimitHelper::recordLoginAttempt($email, false, '192.168.1.101');
    
    $results['ratelimit'][] = ['✓ Record login attempt', 'PASS'];
    $testsPassed++;
} catch (Exception $e) {
    $results['ratelimit'][] = ['✗ Record login attempt', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 3.3: Rate limit threshold (email)
    $email = 'ratelimit' . time() . '@example.com';
    
    // Record 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        RateLimitHelper::recordLoginAttempt($email, false, '192.168.1.102');
    }
    
    // Check if rate limited
    $result = RateLimitHelper::checkLoginAttempts($email, '192.168.1.102');
    
    if ($result['allowed'] === false) {
        $results['ratelimit'][] = ['✓ Rate limit threshold (email)', 'PASS'];
        $testsPassed++;
    } else {
        $results['ratelimit'][] = ['✗ Rate limit threshold (email)', 'FAIL - Should be rate limited'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['ratelimit'][] = ['✗ Rate limit threshold (email)', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 3.4: Clear login attempts
    $email = 'cleartest' . time() . '@example.com';
    
    // Record attempts
    for ($i = 0; $i < 3; $i++) {
        RateLimitHelper::recordLoginAttempt($email, false, '192.168.1.103');
    }
    
    // Clear attempts
    RateLimitHelper::clearLoginAttempts($email);
    
    // Check if cleared
    $result = RateLimitHelper::checkLoginAttempts($email, '192.168.1.103');
    
    if ($result['allowed'] === true) {
        $results['ratelimit'][] = ['✓ Clear login attempts', 'PASS'];
        $testsPassed++;
    } else {
        $results['ratelimit'][] = ['✗ Clear login attempts', 'FAIL - Attempts not cleared'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['ratelimit'][] = ['✗ Clear login attempts', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 3.5: Get remaining attempts
    $email = 'remaining' . time() . '@example.com';
    
    // Record 2 failed attempts
    for ($i = 0; $i < 2; $i++) {
        RateLimitHelper::recordLoginAttempt($email, false, '192.168.1.104');
    }
    
    $remaining = RateLimitHelper::getRemainingAttempts($email);
    
    if ($remaining === 3) { // 5 max - 2 used = 3 remaining
        $results['ratelimit'][] = ['✓ Get remaining attempts', 'PASS'];
        $testsPassed++;
    } else {
        $results['ratelimit'][] = ['✗ Get remaining attempts', 'FAIL - Expected 3, got ' . $remaining];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['ratelimit'][] = ['✗ Get remaining attempts', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

// ============================================
// TEST 4: DLP HELPER
// ============================================
$results['dlp'] = [];

try {
    // Test 4.1: Check if enabled
    $enabled = DLPHelper::isEnabled('watermark');
    
    if (is_bool($enabled)) {
        $results['dlp'][] = ['✓ Check if enabled', 'PASS'];
        $testsPassed++;
    } else {
        $results['dlp'][] = ['✗ Check if enabled', 'FAIL - Invalid return type'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['dlp'][] = ['✗ Check if enabled', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 4.2: Get setting
    $setting = DLPHelper::getSetting('dlp_enable_watermark', 'false');
    
    if (!empty($setting)) {
        $results['dlp'][] = ['✓ Get setting', 'PASS'];
        $testsPassed++;
    } else {
        $results['dlp'][] = ['✗ Get setting', 'FAIL - Empty setting'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['dlp'][] = ['✗ Get setting', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 4.3: Generate watermark
    $watermark = DLPHelper::generateWatermark('John Doe', 'john@example.com');
    
    if (strpos($watermark, 'John Doe') !== false) {
        $results['dlp'][] = ['✓ Generate watermark', 'PASS'];
        $testsPassed++;
    } else {
        $results['dlp'][] = ['✗ Generate watermark', 'FAIL - User name not in watermark'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['dlp'][] = ['✗ Generate watermark', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 4.4: Get watermark HTML
    $html = DLPHelper::getWatermarkHTML('Test Watermark');
    
    if (strpos($html, 'dlp-watermark') !== false && strpos($html, 'Test Watermark') !== false) {
        $results['dlp'][] = ['✓ Get watermark HTML', 'PASS'];
        $testsPassed++;
    } else {
        $results['dlp'][] = ['✗ Get watermark HTML', 'FAIL - Invalid HTML'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['dlp'][] = ['✗ Get watermark HTML', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

try {
    // Test 4.5: Check if sensitive page
    $isSensitive = DLPHelper::isSensitivePage('iep');
    
    if (is_bool($isSensitive)) {
        $results['dlp'][] = ['✓ Check if sensitive page', 'PASS'];
        $testsPassed++;
    } else {
        $results['dlp'][] = ['✗ Check if sensitive page', 'FAIL - Invalid return type'];
        $testsFailed++;
    }
} catch (Exception $e) {
    $results['dlp'][] = ['✗ Check if sensitive page', 'ERROR: ' . $e->getMessage()];
    $testsFailed++;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Modules Test Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a01422 0%, #1e4072 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .container {
            max-width: 1000px;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .card-header {
            background: linear-gradient(135deg, #a01422 0%, #1e4072 100%);
            color: white;
            border-radius: 8px 8px 0 0;
            padding: 1.5rem;
        }
        .card-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }
        .test-row {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-row:last-child {
            border-bottom: none;
        }
        .test-name {
            flex: 1;
            font-weight: 500;
        }
        .test-result {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 600;
            min-width: 120px;
            text-align: center;
        }
        .test-result.pass {
            background: #d4edda;
            color: #155724;
        }
        .test-result.fail {
            background: #f8d7da;
            color: #721c24;
        }
        .test-result.error {
            background: #f5c6cb;
            color: #721c24;
        }
        .summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .summary-stat {
            text-align: center;
            padding: 1rem;
        }
        .summary-stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #a01422;
        }
        .summary-stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .header {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #a01422;
            margin: 0;
        }
        .header p {
            color: #666;
            margin: 0.5rem 0 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔒 Security Modules Test Results</h1>
            <p>Testing Encryption, CSRF, Rate Limiting, and DLP modules</p>
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="row">
                <div class="col-md-3">
                    <div class="summary-stat">
                        <div class="summary-stat-value" style="color: #28a745;"><?php echo $testsPassed; ?></div>
                        <div class="summary-stat-label">Tests Passed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-stat">
                        <div class="summary-stat-value" style="color: #dc3545;"><?php echo $testsFailed; ?></div>
                        <div class="summary-stat-label">Tests Failed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?php echo $testsPassed + $testsFailed; ?></div>
                        <div class="summary-stat-label">Total Tests</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-stat">
                        <div class="summary-stat-value" style="color: <?php echo $testsFailed === 0 ? '#28a745' : '#dc3545'; ?>;">
                            <?php echo round(($testsPassed / ($testsPassed + $testsFailed)) * 100); ?>%
                        </div>
                        <div class="summary-stat-label">Pass Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Results -->
        <?php foreach ($results as $module => $tests): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <?php 
                        $icons = [
                            'encryption' => '🔐',
                            'csrf' => '🛡️',
                            'ratelimit' => '⏱️',
                            'dlp' => '🚫'
                        ];
                        echo ($icons[$module] ?? '✓') . ' ' . ucfirst($module) . ' Tests';
                        ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($tests as $test): ?>
                        <div class="test-row">
                            <div class="test-name"><?php echo htmlspecialchars($test[0]); ?></div>
                            <div class="test-result <?php echo strtolower(explode(' ', $test[1])[0]); ?>">
                                <?php echo htmlspecialchars($test[1]); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Instructions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">📋 Next Steps</h5>
            </div>
            <div class="card-body">
                <h6>1. Configure Encryption Key</h6>
                <p>Add to your <code>.env</code> file:</p>
                <pre><code>ENCRYPTION_KEY=<?php echo bin2hex(random_bytes(16)); ?></code></pre>

                <h6>2. Test Login with CSRF Protection</h6>
                <p>Go to <a href="/Signedd/public/login" target="_blank">/login</a> and verify:</p>
                <ul>
                    <li>CSRF token is present in form (inspect element)</li>
                    <li>Login works normally</li>
                    <li>Rate limiting blocks after 5 failed attempts</li>
                </ul>

                <h6>3. Test Rate Limiting</h6>
                <p>Try logging in with wrong password 5+ times from same IP:</p>
                <ul>
                    <li>After 5 attempts, you should see: "Too many login attempts"</li>
                    <li>Wait 15 minutes or clear <code>rate_limit_log</code> table</li>
                </ul>

                <h6>4. Test DLP on Sensitive Pages</h6>
                <p>Once IEP/Assessment pages are built, verify:</p>
                <ul>
                    <li>Watermark appears on page</li>
                    <li>Right-click is disabled</li>
                    <li>Ctrl+P (print) is blocked</li>
                    <li>Ctrl+C (copy) is blocked</li>
                </ul>

                <h6>5. Database Verification</h6>
                <p>Check that new tables were created:</p>
                <pre><code>SELECT * FROM csrf_tokens;
SELECT * FROM rate_limit_log;
SELECT * FROM dlp_settings;
SELECT * FROM encryption_audit;</code></pre>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; color: white; margin-top: 2rem; margin-bottom: 2rem;">
            <p>Security Modules Test Suite | SPED LMS</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
