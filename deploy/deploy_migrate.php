<?php
// Remote database migration trigger for InfinityFree live site
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (file_exists(__DIR__ . '/config/db.php')) {
    require_once __DIR__ . '/config/db.php';
} elseif (file_exists(__DIR__ . '/../config/db.php')) {
    require_once __DIR__ . '/../config/db.php';
}

header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "--- EXECUTING REMOTE LIVE DATABASE MIGRATION ---\n";

    // 1. Add sip_path to schools table
    try {
        $pdo->exec("ALTER TABLE schools ADD COLUMN sip_path VARCHAR(500) NULL");
        echo "[SUCCESS] Added sip_path column to schools table.\n";
    } catch (PDOException $e) {
        echo "[INFO] sip_path: " . $e->getMessage() . "\n";
    }

    // 2. Add fsl_cert_path to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN fsl_cert_path VARCHAR(500) NULL");
        echo "[SUCCESS] Added fsl_cert_path column to users table.\n";
    } catch (PDOException $e) {
        echo "[INFO] fsl_cert_path: " . $e->getMessage() . "\n";
    }

    // 3. Add fsl_cert_issue_date to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN fsl_cert_issue_date DATE NULL");
        echo "[SUCCESS] Added fsl_cert_issue_date column to users table.\n";
    } catch (PDOException $e) {
        echo "[INFO] fsl_cert_issue_date: " . $e->getMessage() . "\n";
    }

    echo "\n--- REMOTE MIGRATION COMPLETE! ---\n";

} catch (Throwable $ex) {
    echo "[ERROR] Database migration failed: " . $ex->getMessage() . "\n";
}
