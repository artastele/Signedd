<?php
// Upload a database inspector endpoint to /signedtest.site.je/htdocs/api_check_schools.php

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
    $stmt = $db->query("SELECT id, school_id, school_name, logo_path, pubmat_path FROM schools");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($schools, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'sch');
file_put_contents($tmp, $scriptContent);

if (@ftp_put($conn, '/signedtest.site.je/htdocs/api_check_schools.php', $tmp, FTP_BINARY)) {
    echo "[OK] Uploaded api_check_schools.php\n";
} else {
    echo "[FAIL] Upload failed\n";
}

unlink($tmp);
ftp_close($conn);
