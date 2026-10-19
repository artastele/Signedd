<?php
// deploy/inspect_live_students.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$baseRemote = '/signedtest.site.je/htdocs';

$inspectScript = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

try {
    $pdo = Database::getInstance()->getConnection();
    echo "--- INSPECTING LIVE STUDENT RECORDS ---\n";
    $stmt = $pdo->query("SELECT id, school_id, student_name, lrn, section_name, section_id, assigned_teacher_id FROM student_records");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total student_records: " . count($rows) . "\n";
    foreach ($rows as $r) {
        echo "Student ID: {$r['id']} | Name: {$r['student_name']} | School: {$r['school_id']} | Section: '{$r['section_name']}' | SecID: " . var_export($r['section_id'], true) . " | Teacher: " . var_export($r['assigned_teacher_id'], true) . "\n";
    }

    echo "\n--- SCHOOLS ---\n";
    $stmtSchools = $pdo->query("SELECT id, school_name FROM schools");
    foreach ($stmtSchools->fetchAll(PDO::FETCH_ASSOC) as $sc) {
        echo "School ID: {$sc['id']} | Name: {$sc['school_name']}\n";
    }

    echo "\n--- USERS ---\n";
    $stmtUsers = $pdo->query("SELECT id, name, email, role, school_id FROM users");
    foreach ($stmtUsers->fetchAll(PDO::FETCH_ASSOC) as $u) {
        echo "User ID: {$u['id']} | Name: {$u['name']} | Role: {$u['role']} | School: {$u['school_id']} | Email: {$u['email']}\n";
    }

    echo "\n--- SECTIONS ---\n";
    $stmtSec = $pdo->query("SELECT * FROM sections");
    $rowsSec = $stmtSec->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsSec as $s) {
        echo "Section ID: {$s['id']} | Name: '{$s['section_name']}' | School: {$s['school_id']} | Adviser: {$s['adviser_teacher_id']} | Count: {$s['current_count']}\n";
    }
} catch (\Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'insp_');
file_put_contents($tmp, $inspectScript);
$remoteTarget = $baseRemote . '/inspect_students_live.php';
ftp_put($conn, $remoteTarget, $tmp, FTP_BINARY);
@unlink($tmp);
ftp_close($conn);

echo "[OK] Uploaded inspect_students_live.php\n";
