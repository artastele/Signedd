<?php
// Upload live admin account seeding script to /signedtest.site.je/htdocs/seed_live_admin.php

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$scriptContent = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    $passHash = password_hash('password', PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("
        INSERT INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider, school_id)
        VALUES (1, 'System Admin', 'admin@spedlms.local', :pass, 'admin', 'active', 1, 'local', NULL)
        ON DUPLICATE KEY UPDATE 
            name = 'System Admin',
            password_hash = :pass_update,
            role = 'admin',
            status = 'active',
            email_verified = 1;
    ");
    $stmt->execute(['pass' => $passHash, 'pass_update' => $passHash]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Admin account seeded successfully!',
        'email' => 'admin@spedlms.local',
        'password' => 'password'
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'adm');
file_put_contents($tmp, $scriptContent);

if (@ftp_put($conn, '/signedtest.site.je/htdocs/seed_live_admin.php', $tmp, FTP_BINARY)) {
    echo "[OK] Uploaded seed_live_admin.php\n";
} else {
    echo "[FAIL] Upload failed\n";
}

unlink($tmp);
ftp_close($conn);
