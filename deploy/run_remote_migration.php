<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn) { die("Could not connect to FTP\n"); }
if (!ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) { die("FTP login failed\n"); }
ftp_pasv($conn, true);

$scriptContent = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "--- RUNNING V65 LIVE MIGRATIONS ---\n";

    // 1. claim_token on student_records
    try {
        $pdo->exec("ALTER TABLE student_records ADD COLUMN claim_token VARCHAR(64) NULL DEFAULT NULL AFTER lis_synced_at");
        $pdo->exec("CREATE INDEX idx_student_records_claim_token ON student_records (claim_token)");
        echo "[SUCCESS] Added claim_token column to student_records.\n";
    } catch (Exception $e) {
        echo "[INFO] student_records.claim_token: " . $e->getMessage() . "\n";
    }

    // 2. parent_id nullable on enrollment_submissions
    try {
        $pdo->exec("ALTER TABLE enrollment_submissions MODIFY parent_id INT NULL DEFAULT NULL");
        echo "[SUCCESS] enrollment_submissions.parent_id is now NULLABLE.\n";
    } catch (Exception $e) {
        echo "[INFO] enrollment_submissions.parent_id: " . $e->getMessage() . "\n";
    }

    // 3. sip_path on schools
    try {
        $pdo->exec("ALTER TABLE schools ADD COLUMN sip_path VARCHAR(500) NULL AFTER logo_path");
        echo "[SUCCESS] Added sip_path to schools.\n";
    } catch (Exception $e) {
        echo "[INFO] schools.sip_path: " . $e->getMessage() . "\n";
    }

    // 4. fsl_cert_path on users
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN fsl_cert_path VARCHAR(500) NULL AFTER status");
        echo "[SUCCESS] Added fsl_cert_path to users.\n";
    } catch (Exception $e) {
        echo "[INFO] users.fsl_cert_path: " . $e->getMessage() . "\n";
    }

    // 5. fsl_cert_issue_date on users
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN fsl_cert_issue_date DATE NULL AFTER fsl_cert_path");
        echo "[SUCCESS] Added fsl_cert_issue_date to users.\n";
    } catch (Exception $e) {
        echo "[INFO] users.fsl_cert_issue_date: " . $e->getMessage() . "\n";
    }

    echo "--- MIGRATION FINISHED SUCCESSFULLY ---\n";
} catch (Exception $e) {
    echo "[FATAL] " . $e->getMessage() . "\n";
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'mig');
file_put_contents($tmp, $scriptContent);

$remoteTarget = '/signedtest.site.je/htdocs/run_v65_migration.php';
echo "Uploading migration runner to $remoteTarget...\n";
if (ftp_put($conn, $remoteTarget, $tmp, FTP_BINARY)) {
    echo "[OK] Uploaded runner successfully.\n";
} else {
    echo "[FAIL] Failed to upload runner.\n";
}
unlink($tmp);
ftp_close($conn);

echo "Executing via HTTP: http://signedtest.site.je/run_v65_migration.php ...\n\n";
$ctx = stream_context_create(['http' => ['timeout' => 30]]);
$result = file_get_contents('http://signedtest.site.je/run_v65_migration.php', false, $ctx);
echo $result . "\n";

// Now delete the temp migration file via FTP
$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);
ftp_delete($conn, $remoteTarget);
ftp_close($conn);
echo "[CLEANUP] Deleted run_v65_migration.php from server.\n";
