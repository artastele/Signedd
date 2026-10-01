<?php
$env = parse_ini_file(__DIR__ . '/../.env.infinityfree');
$conn = ftp_connect($env['FTP_HOST'], intval($env['FTP_PORT']), 30);
ftp_login($conn, $env['FTP_USER'], $env['FTP_PASS']);
ftp_pasv($conn, true);

$code = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "--- RUNNING COMPREHENSIVE LIVE MIGRATIONS ---\n";

    // 1. Sections Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `sections` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `school_id` INT NULL,
        `section_name` VARCHAR(100) NOT NULL,
        `grade_level` VARCHAR(50) NOT NULL DEFAULT 'SPED',
        `room_number` VARCHAR(50) NULL,
        `max_capacity` INT NOT NULL DEFAULT 15,
        `current_count` INT NOT NULL DEFAULT 0,
        `adviser_teacher_id` INT NULL,
        `school_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
        `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`school_id`),
        INDEX (`adviser_teacher_id`),
        INDEX (`grade_level`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[OK] sections table checked/created\n";

    // 2. traditional_iep_documents table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `traditional_iep_documents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `uploaded_by` INT NOT NULL,
        `document_type` ENUM('dll', 'physical_iep', 'assessment_report', 'progress_note', 'other') NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `file_path` VARCHAR(255) NOT NULL,
        `file_size` INT NULL,
        `school_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
        `quarter` VARCHAR(20) NULL,
        `notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`student_id`),
        INDEX (`uploaded_by`),
        INDEX (`document_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[OK] traditional_iep_documents table checked/created\n";

    // Columns to add helper
    function addColumnIfMissing($pdo, $table, $col, $def) {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
                echo "[OK] Added $table.$col\n";
            } else {
                echo "[SKIP] $table.$col already exists\n";
            }
        } catch (Exception $e) {
            echo "[ERR] $table.$col: " . $e->getMessage() . "\n";
        }
    }

    // student_records columns
    addColumnIfMissing($pdo, 'student_records', 'claim_token', 'VARCHAR(64) NULL DEFAULT NULL');
    addColumnIfMissing($pdo, 'student_records', 'section_id', 'INT NULL');
    addColumnIfMissing($pdo, 'student_records', 'learning_track', "ENUM('unassigned', 'lms', 'traditional') NOT NULL DEFAULT 'unassigned'");
    addColumnIfMissing($pdo, 'student_records', 'lms_invite_status', "ENUM('none', 'sent', 'accepted', 'declined') NOT NULL DEFAULT 'none'");
    addColumnIfMissing($pdo, 'student_records', 'lms_invited_at', 'DATETIME NULL');
    addColumnIfMissing($pdo, 'student_records', 'lms_accepted_at', 'DATETIME NULL');
    addColumnIfMissing($pdo, 'student_records', 'learner_user_id', 'INT NULL');

    // enrollment_submissions columns
    try { $pdo->exec("ALTER TABLE enrollment_submissions MODIFY parent_id INT NULL DEFAULT NULL"); echo "[OK] enrollment_submissions.parent_id is nullable\n"; } catch (Exception $e){}
    addColumnIfMissing($pdo, 'enrollment_submissions', 'survey_has_internet', 'TINYINT(1) NULL DEFAULT 0');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'survey_devices', 'VARCHAR(255) NULL');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'survey_willing_online', 'TINYINT(1) NULL DEFAULT 0');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'learning_track', "ENUM('unassigned', 'lms', 'traditional') NOT NULL DEFAULT 'unassigned'");
    addColumnIfMissing($pdo, 'enrollment_submissions', 'has_device', 'TINYINT(1) NULL DEFAULT 0');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'willing_digital', 'TINYINT(1) NULL DEFAULT 0');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'section_id', 'INT NULL');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'lms_track', "VARCHAR(50) NULL DEFAULT 'unset'");
    addColumnIfMissing($pdo, 'enrollment_submissions', 'lms_invite_status', "ENUM('none', 'sent', 'accepted', 'declined') NOT NULL DEFAULT 'none'");
    addColumnIfMissing($pdo, 'enrollment_submissions', 'lms_invite_sent_at', 'DATETIME NULL');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'lms_invite_accepted_at', 'DATETIME NULL');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'lms_credentials_generated_at', 'DATETIME NULL');
    addColumnIfMissing($pdo, 'enrollment_submissions', 'learner_user_id', 'INT NULL');

    // schools columns
    addColumnIfMissing($pdo, 'schools', 'sip_path', 'VARCHAR(500) NULL');

    // users columns
    addColumnIfMissing($pdo, 'users', 'fsl_cert_path', 'VARCHAR(500) NULL');
    addColumnIfMissing($pdo, 'users', 'fsl_cert_issue_date', 'DATE NULL');
    addColumnIfMissing($pdo, 'users', 'profile_photo', 'VARCHAR(255) NULL');
    addColumnIfMissing($pdo, 'users', 'phone_number', 'VARCHAR(50) NULL');
    addColumnIfMissing($pdo, 'users', 'bio', 'TEXT NULL');

    echo "\nALL_MIGRATIONS_COMPLETE\n";
} catch (Exception $e) {
    echo "[FATAL] " . $e->getMessage() . "\n";
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'mig');
file_put_contents($tmp, $code);
if (ftp_put($conn, '/signedtest.site.je/htdocs/run_v65_migration.php', $tmp, FTP_BINARY)) {
    echo "Uploaded updated run_v65_migration.php successfully\n";
} else {
    echo "Upload failed\n";
}
unlink($tmp);
ftp_close($conn);
