<?php
// Upload inspector script to check schools table data on live test server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$scriptContent = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/Models/SchoolModel.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM schools");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    foreach ($schools as $s) {
        $logoUrl = SchoolModel::getSchoolLogoUrl($s, '');
        $logoPath = $s['logo_path'] ?? null;
        $relPath = ltrim($logoPath, '/');
        $fullPath = function_exists('public_path') ? public_path($relPath) : 'no_public_path_func';
        $exists = file_exists($fullPath);
        
        $results[] = [
            'school' => $s,
            'logoUrl_prefix' => substr($logoUrl, 0, 50),
            'fullPath' => $fullPath,
            'fileExists' => $exists
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($results, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'sch');
file_put_contents($tmp, $scriptContent);

if (@ftp_put($conn, '/signedtest.site.je/htdocs/api_debug_logo.php', $tmp, FTP_BINARY)) {
    echo "[OK] Uploaded api_debug_logo.php\n";
} else {
    echo "[FAIL] Upload failed\n";
}

unlink($tmp);
ftp_close($conn);
