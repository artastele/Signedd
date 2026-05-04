<?php
/**
 * Setup Security Module Tables
 * Run this once to create the required database tables
 * 
 * Access: http://localhost/Signedd/public/setup-security-tables.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

$results = [];

try {
    $db = Database::getInstance()->getConnection();
    
    // Disable foreign key checks temporarily
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    // ============================================
    // Create CSRF Tokens Table (v9)
    // ============================================
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS csrf_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255) NOT NULL,
                token VARCHAR(255) NOT NULL UNIQUE,
                user_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                used BOOLEAN DEFAULT FALSE,
                used_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_session_id (session_id),
                INDEX idx_token (token),
                INDEX idx_expires_at (expires_at)
            )
        ");
        $results[] = ['✓ csrf_tokens table', 'CREATED'];
    } catch (Exception $e) {
        $results[] = ['✗ csrf_tokens table', 'ERROR: ' . $e->getMessage()];
    }
    
    // ============================================
    // Create Rate Limit Log Table (v10)
    // ============================================
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS rate_limit_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255),
                ip_address VARCHAR(45),
                attempt_type ENUM('login', 'registration', 'password_reset') NOT NULL,
                success BOOLEAN DEFAULT FALSE,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email_time (email, attempted_at),
                INDEX idx_ip_time (ip_address, attempted_at),
                INDEX idx_attempted_at (attempted_at)
            )
        ");
        $results[] = ['✓ rate_limit_log table', 'CREATED'];
    } catch (Exception $e) {
        $results[] = ['✗ rate_limit_log table', 'ERROR: ' . $e->getMessage()];
    }
    
    // ============================================
    // Create DLP Settings Table (v11)
    // ============================================
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS dlp_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                description TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_setting_key (setting_key)
            )
        ");
        $results[] = ['✓ dlp_settings table', 'CREATED'];
        
        // Insert default DLP settings
        $db->exec("
            INSERT IGNORE INTO dlp_settings (setting_key, setting_value, description) VALUES
            ('dlp_enable_watermark', 'true', 'Enable watermark on sensitive documents'),
            ('dlp_enable_screenshot_block', 'true', 'Block screenshot attempts'),
            ('dlp_enable_copy_block', 'true', 'Block copy/paste on sensitive pages'),
            ('dlp_enable_print_block', 'true', 'Block printing of sensitive documents'),
            ('dlp_enable_export_block', 'true', 'Block export functionality'),
            ('dlp_watermark_format', '{user} | {timestamp} | {ip}', 'Watermark format string'),
            ('dlp_sensitive_pages', 'iep,assessment,student_records', 'Comma-separated list of sensitive page types')
        ");
        $results[] = ['✓ DLP settings', 'INSERTED'];
    } catch (Exception $e) {
        $results[] = ['✗ dlp_settings table', 'ERROR: ' . $e->getMessage()];
    }
    
    // ============================================
    // Create Encryption Audit Table (v8)
    // ============================================
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS encryption_audit (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_name VARCHAR(100) NOT NULL,
                record_id INT NOT NULL,
                field_name VARCHAR(100) NOT NULL,
                action ENUM('encrypted', 'decrypted') NOT NULL,
                performed_by INT,
                performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_table_record (table_name, record_id),
                INDEX idx_performed_at (performed_at)
            )
        ");
        $results[] = ['✓ encryption_audit table', 'CREATED'];
    } catch (Exception $e) {
        $results[] = ['✗ encryption_audit table', 'ERROR: ' . $e->getMessage()];
    }
    
    // Re-enable foreign key checks
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    
    $success = true;
} catch (Exception $e) {
    $results[] = ['✗ Database Connection', 'ERROR: ' . $e->getMessage()];
    $success = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Tables Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a01422 0%, #1e4072 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .container {
            max-width: 600px;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        .result-row {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .result-row:last-child {
            border-bottom: none;
        }
        .result-name {
            flex: 1;
            font-weight: 500;
        }
        .result-status {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 600;
            min-width: 120px;
            text-align: center;
        }
        .result-status.success {
            background: #d4edda;
            color: #155724;
        }
        .result-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .alert-box {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
            <h1>🔧 Security Tables Setup</h1>
            <p>Creating required database tables for security modules</p>
        </div>

        <!-- Results -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Setup Results</h5>
            </div>
            <div class="card-body p-0">
                <?php foreach ($results as $result): ?>
                    <div class="result-row">
                        <div class="result-name"><?php echo htmlspecialchars($result[0]); ?></div>
                        <div class="result-status <?php echo strpos($result[1], 'ERROR') !== false ? 'error' : 'success'; ?>">
                            <?php echo htmlspecialchars($result[1]); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Status Message -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✓ Success!</strong> All security tables have been created.
                <br><br>
                <strong>Next steps:</strong>
                <ol>
                    <li>Go to login page: <a href="/Signedd/public/login">http://localhost/Signedd/public/login</a></li>
                    <li>Try logging in - CSRF protection should now work</li>
                    <li>Run tests: <a href="/Signedd/public/test-security-modules.php">test-security-modules.php</a></li>
                </ol>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>✗ Error!</strong> Some tables could not be created. Check the results above.
                <br><br>
                <strong>Troubleshooting:</strong>
                <ul>
                    <li>Verify database connection in .env file</li>
                    <li>Check database user has CREATE TABLE permissions</li>
                    <li>Check PHP error logs</li>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div style="text-align: center; color: white; margin-top: 2rem; margin-bottom: 2rem;">
            <p>Security Tables Setup | SPED LMS</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
