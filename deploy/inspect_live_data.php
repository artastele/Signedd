<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn || !ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("FTP login failed\n");
}
ftp_pasv($conn, true);

$scriptContent = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "=== LIVE SYSTEM INSPECTION ===\n\n";

    echo "--- 1. TEACHERS & STAFF ---\n";
    $users = $pdo->query("SELECT id, name, email, role, status, school_id FROM users WHERE role IN ('sped_teacher', 'general_teacher', 'master_teacher', 'principal', 'admin') ORDER BY role, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);

    echo "\n--- 2. SECTIONS ---\n";
    $sections = $pdo->query("SELECT s.*, u.name as adviser_name FROM sections s LEFT JOIN users u ON s.adviser_teacher_id = u.id ORDER BY s.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    print_r($sections);

    echo "\n--- 3. TEACHER ASSIGNMENTS ---\n";
    $ta = $pdo->query("SELECT ta.*, u.name as teacher_name FROM teacher_assignments ta LEFT JOIN users u ON ta.teacher_id = u.id ORDER BY ta.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    print_r($ta);

    echo "\n--- 4. STUDENT RECORDS ---\n";
    $students = $pdo->query("SELECT id, student_id, lrn, student_name, section_id, section_name, assigned_teacher_id, verified_by, school_id FROM student_records ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    print_r($students);

    echo "\n--- 5. IEP RECORDS ---\n";
    $ieps = $pdo->query("SELECT ir.id, ir.student_id, ir.drafted_by, ir.school_year, ir.status, ir.created_at, sr.student_name, sr.section_name as student_sec, u.name as drafted_by_name FROM iep_records ir LEFT JOIN student_records sr ON ir.student_id = sr.id LEFT JOIN users u ON ir.drafted_by = u.id ORDER BY ir.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    print_r($ieps);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'insp');
file_put_contents($tmp, $scriptContent);

$remoteTarget = '/signedtest.site.je/htdocs/inspect_live_data.php';
if (ftp_put($conn, $remoteTarget, $tmp, FTP_BINARY)) {
    echo "[OK] Uploaded inspect_live_data.php\n";
} else {
    echo "[FAIL] Upload failed\n";
}
unlink($tmp);
ftp_close($conn);

$ctx = stream_context_create(['http' => ['timeout' => 30]]);
$result = file_get_contents('http://signedtest.site.je/inspect_live_data.php', false, $ctx);
echo $result . "\n";

// Cleanup
$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);
ftp_delete($conn, $remoteTarget);
ftp_close($conn);
echo "[CLEANUP] Deleted inspect_live_data.php\n";
