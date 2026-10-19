<?php
// deploy/run_live_fix_sync.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$baseRemote = '/signedtest.site.je/htdocs';

$fixScript = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "=== RUNNING COMPREHENSIVE LIVE SYNC ===\n";

    // 1. Align sections.school_id with their adviser's school_id
    $pdo->exec("
        UPDATE sections s
        JOIN users u ON s.adviser_teacher_id = u.id
        SET s.school_id = u.school_id
        WHERE u.school_id IS NOT NULL
    ");
    echo "[OK] Aligned sections school_id to match advisers.\n";

    // 2. Link student_records with section_name 'MANGGA' to Section 2 ('SPED-Mangga')
    $linkedCount = $pdo->exec("
        UPDATE student_records sr
        JOIN sections s ON (
            s.id = 2 
            OR LOWER(REPLACE(REPLACE(s.section_name, 'SPED-', ''), '-', '')) = LOWER(REPLACE(REPLACE(sr.section_name, 'SPED-', ''), '-', ''))
        )
        SET sr.section_id = s.id,
            sr.school_id = s.school_id,
            sr.assigned_teacher_id = COALESCE(sr.assigned_teacher_id, s.adviser_teacher_id)
        WHERE (sr.section_name LIKE '%MANGGA%' OR sr.assigned_teacher_id = s.adviser_teacher_id)
          AND (sr.section_id IS NULL OR sr.section_id = 0)
    ");
    echo "[OK] Linked $linkedCount students to matching section.\n";

    // 3. Also link enrollment_submissions for these students
    $pdo->exec("
        UPDATE enrollment_submissions es
        JOIN student_records sr ON sr.enrollment_id = es.id
        SET es.section_id = sr.section_id,
            es.target_school_id = sr.school_id
        WHERE sr.section_id IS NOT NULL
    ");
    echo "[OK] Linked enrollment_submissions to sections.\n";

    // 4. Update sections.current_count
    $pdo->exec("
        UPDATE sections s
        SET current_count = (
            SELECT COUNT(*) 
            FROM student_records sr 
            WHERE sr.section_id = s.id
        )
    ");
    echo "[OK] Updated sections current_count.\n";

    // 5. Verify the results
    echo "\n=== UPDATED SECTIONS ===\n";
    $stmtSec = $pdo->query("
        SELECT s.id, s.school_id, s.section_name, s.max_capacity, s.current_count, u.name as adviser_name
        FROM sections s
        LEFT JOIN users u ON s.adviser_teacher_id = u.id
    ");
    foreach ($stmtSec->fetchAll(PDO::FETCH_ASSOC) as $s) {
        echo "Section #{$s['id']}: '{$s['section_name']}' | School ID: {$s['school_id']} | Adviser: {$s['adviser_name']} | Enrolled: {$s['current_count']} / {$s['max_capacity']}\n";
    }

    echo "\n=== STUDENTS SUMMARY IN SECTION 2 ===\n";
    $stmtSt = $pdo->query("SELECT id, student_name, school_id, section_name, section_id, assigned_teacher_id FROM student_records WHERE section_id = 2");
    $students = $stmtSt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total students in Section 2: " . count($students) . "\n";
    foreach ($students as $st) {
        echo "- Student #{$st['id']}: {$st['student_name']} (School {$st['school_id']}, SecID {$st['section_id']}, Teacher {$st['assigned_teacher_id']})\n";
    }

    echo "\n=== SYNC SUCCESSFUL ===\n";
} catch (\Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'fix_');
file_put_contents($tmp, $fixScript);
$remoteTarget = $baseRemote . '/run_live_fix_sync.php';
ftp_put($conn, $remoteTarget, $tmp, FTP_BINARY);
@unlink($tmp);
ftp_close($conn);

echo "[OK] Uploaded run_live_fix_sync.php\n";
