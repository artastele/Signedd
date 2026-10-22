<?php
// deploy/upload_latest_fixes.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
if (!file_exists($envFile)) {
    die("Config file .env.infinityfree not found!\n");
}
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn) {
    die("Could not connect to FTP host {$config['FTP_HOST']}\n");
}
if (!ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("FTP login failed for user {$config['FTP_USER']}\n");
}
ftp_pasv($conn, true);

$baseLocal = dirname(__DIR__);
$baseRemote = '/signedtest.site.je/htdocs';

$files = [
    'config/db.php',
    'app/Controllers/AuthController.php',
    'app/Views/auth/register.php',
    'app/Views/auth/claim_parent.php',
    'app/Views/auth/claim_universal.php',
    'app/Controllers/ParentClaimController.php',
    'app/Controllers/PrincipalController.php',
    'app/Models/ProgressReportModel.php',
    'app/Controllers/ProgressReportController.php',
    'app/Models/StudentModel.php',
    'app/Models/AssessmentModel.php',
    'app/Controllers/AssessmentController.php',
    'app/Models/IEPModel.php',
    'app/Controllers/IEPController.php',
    'app/Views/iep/index.php',
    'app/Models/IEPMeetingModel.php',
    'app/Controllers/IEPMeetingController.php',
    'app/Models/MasterlistModel.php',
    'app/Controllers/MasterlistController.php',
    'app/Models/SectionModel.php',
    'app/Controllers/SectionController.php',
    'app/Views/layouts/sidebar.php',
    'app/Views/dashboard/parent.php',
    'app/Models/UserModel.php',
    'app/Controllers/AdminController.php',
    'app/Views/admin/login_logs.php',
    'app/Models/LessonPlanModel.php',
];

echo "=== Uploading modified files to InfinityFree ===\n";
$successCount = 0;
foreach ($files as $rel) {
    $local = $baseLocal . '/' . $rel;
    $remote = $baseRemote . '/' . $rel;
    
    // Ensure remote directory exists
    $dir = dirname($remote);
    @ftp_mkdir($conn, $dir);
    
    if (@ftp_put($conn, $remote, $local, FTP_BINARY)) {
        echo "[OK] Uploaded $rel\n";
        $successCount++;
    } else {
        echo "[FAIL] Failed to upload $rel\n";
    }
}

echo "\nSummary: $successCount / " . count($files) . " files uploaded.\n";

// Upload sync script
$syncScript = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "--- RUNNING SECTION AND LOGIN_LOG SYNC ---\n";

    // 1. Ensure login_log table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            email VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            result ENUM('success','failure') NOT NULL,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_attempted_at (attempted_at),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[OK] login_log table verified.\n";

    // 2. Link student_records where section_id is null to matching sections by section_name
    $linkedCount = $pdo->exec("
        UPDATE student_records sr
        JOIN sections s ON (
            (sr.school_id = s.school_id OR sr.school_id IS NULL OR s.school_id IS NULL)
            AND LOWER(TRIM(sr.section_name)) = LOWER(TRIM(s.section_name))
        )
        SET sr.section_id = s.id,
            sr.school_id = COALESCE(sr.school_id, s.school_id)
        WHERE sr.section_id IS NULL OR sr.section_id = 0
    ");
    echo "[OK] Linked $linkedCount student records to section IDs.\n";

    // 3. Link unassigned teachers to section advisers
    $advisedCount = $pdo->exec("
        UPDATE student_records sr
        JOIN sections s ON sr.section_id = s.id
        SET sr.assigned_teacher_id = s.adviser_teacher_id
        WHERE (sr.assigned_teacher_id IS NULL OR sr.assigned_teacher_id = 0)
          AND s.adviser_teacher_id IS NOT NULL
    ");
    echo "[OK] Assigned $advisedCount student records to section advisers.\n";

    // 4. Update section current_count
    $pdo->exec("
        UPDATE sections s
        SET current_count = (
            SELECT COUNT(*) 
            FROM student_records sr 
            WHERE (sr.section_id = s.id 
                   OR (sr.section_id IS NULL AND LOWER(TRIM(sr.section_name)) = LOWER(TRIM(s.section_name)))
                  )
              AND (s.school_id IS NULL OR sr.school_id = s.school_id OR sr.school_id IS NULL)
        )
    ");
    echo "[OK] Updated current_count for all sections.\n";

    // Display summary of sections
    $stmt = $pdo->query("SELECT id, school_id, section_name, current_count, max_capacity FROM sections");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCurrent Sections in Database:\n";
    foreach ($rows as $r) {
        echo "- Section #{$r['id']}: {$r['section_name']} (Enrolled: {$r['current_count']} / {$r['max_capacity']})\n";
    }

    echo "--- SYNC COMPLETE ---\n";
} catch (\Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
PHP;

$syncLocal = tempnam(sys_get_temp_dir(), 'sync_');
file_put_contents($syncLocal, $syncScript);
$syncRemote = $baseRemote . '/sync_sections_live.php';
if (ftp_put($conn, $syncRemote, $syncLocal, FTP_BINARY)) {
    echo "[OK] Uploaded sync_sections_live.php to live server.\n";
} else {
    echo "[FAIL] Could not upload sync_sections_live.php\n";
}
@unlink($syncLocal);

ftp_close($conn);
echo "=== Deployment finished ===\n";
