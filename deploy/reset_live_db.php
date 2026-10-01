<?php
// Live database clean reset script for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (file_exists(__DIR__ . '/config/db.php')) {
    require_once __DIR__ . '/config/db.php';
} elseif (file_exists(__DIR__ . '/../config/db.php')) {
    require_once __DIR__ . '/../config/db.php';
}

header('Content-Type: text/plain');

try {
    $db = Database::getInstance()->getConnection();
    echo "--- EXECUTING LIVE DATABASE WIPE & RESET FOR CLEAN TESTING ---\n\n";

    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Truncate test tables
    $tablesToTruncate = [
        'role_requests',
        'teacher_assignments',
        'notifications',
        'audit_logs',
        'activity_logs',
        'login_logs',
        'students',
        'schools',
        'users'
    ];

    foreach ($tablesToTruncate as $tbl) {
        try {
            $db->exec("TRUNCATE TABLE `$tbl`");
            echo "[SUCCESS] Truncated table: `$tbl`\n";
        } catch (PDOException $e) {
            echo "[INFO] Table `$tbl`: " . $e->getMessage() . "\n";
        }
    }

    // 2. Seed Default System Admin Account
    $passHash = password_hash('password', PASSWORD_BCRYPT);
    $stmt = $db->prepare("
        INSERT INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider, school_id)
        VALUES (1, 'System Admin', 'admin@spedlms.local', :pass, 'admin', 'active', 1, 'local', NULL)
    ");
    $stmt->execute(['pass' => $passHash]);
    echo "\n[SUCCESS] Default System Admin seeded (email: admin@spedlms.local, pass: password)\n";

    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "\n--- LIVE DATABASE RESET COMPLETE & READY FOR CLEAN TESTING! ---\n";

} catch (Throwable $ex) {
    echo "[ERROR] Reset failed: " . $ex->getMessage() . "\n";
}
