<?php
// Remote migration script for InfinityFree live database on signedtest.site.je
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "--- EXECUTING REMOTE LIVE DATABASE MIGRATION ---\n";

    // 1. Add sip_path to schools table
    try {
        $pdo->exec("ALTER TABLE schools ADD COLUMN sip_path VARCHAR(500) NULL AFTER logo_path");
        echo "[SUCCESS] Added sip_path column to schools table.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "[INFO] Column sip_path already exists on schools table.\n";
        } else {
            echo "[WARNING] Error adding sip_path: " . $e->getMessage() . "\n";
        }
    }

    // 2. Add fsl_cert_path to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN fsl_cert_path VARCHAR(500) NULL AFTER status");
        echo "[SUCCESS] Added fsl_cert_path column to users table.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "[INFO] Column fsl_cert_path already exists on users table.\n";
        } else {
            echo "[WARNING] Error adding fsl_cert_path: " . $e->getMessage() . "\n";
        }
    }

    // 3. Add fsl_cert_issue_date to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN fsl_cert_issue_date DATE NULL AFTER fsl_cert_path");
        echo "[SUCCESS] Added fsl_cert_issue_date column to users table.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "[INFO] Column fsl_cert_issue_date already exists on users table.\n";
        } else {
            echo "[WARNING] Error adding fsl_cert_issue_date: " . $e->getMessage() . "\n";
        }
    }

    // 4. Add claim_token to student_records table (v65)
    try {
        $pdo->exec("ALTER TABLE student_records ADD COLUMN claim_token VARCHAR(64) NULL DEFAULT NULL AFTER lis_synced_at");
        $pdo->exec("CREATE INDEX idx_student_records_claim_token ON student_records (claim_token)");
        echo "[SUCCESS] Added claim_token column and index to student_records table.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "[INFO] Column claim_token already exists on student_records table.\n";
        } else {
            echo "[WARNING] Error adding claim_token: " . $e->getMessage() . "\n";
        }
    }

    // 5. Make parent_id NULLABLE on enrollment_submissions table (v65)
    try {
        $pdo->exec("ALTER TABLE enrollment_submissions MODIFY parent_id INT NULL DEFAULT NULL");
        echo "[SUCCESS] Updated enrollment_submissions.parent_id to be NULLABLE.\n";
    } catch (PDOException $e) {
        echo "[WARNING] Error modifying enrollment_submissions.parent_id: " . $e->getMessage() . "\n";
    }

    echo "\n--- REMOTE MIGRATION COMPLETE! ---\n";

} catch (Exception $ex) {
    echo "[ERROR] Database migration failed: " . $ex->getMessage() . "\n";
}
