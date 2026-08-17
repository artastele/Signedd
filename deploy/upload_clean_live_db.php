<?php
// Upload live database cleaner endpoint to /signedtest.site.je/htdocs/clean_live_db.php

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
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $count = 0;
    foreach ($tables as $table) {
        try {
            $db->exec("TRUNCATE TABLE `$table`;");
            $count++;
        } catch (Exception $e) {
            // Ignore view errors
        }
    }
    
    @$db->exec("INSERT INTO `db_version` (`version`) VALUES (58);");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => "Database cleaned successfully! $count tables truncated.",
        'tables_truncated' => $count
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'cln');
file_put_contents($tmp, $scriptContent);

if (@ftp_put($conn, '/signedtest.site.je/htdocs/clean_live_db.php', $tmp, FTP_BINARY)) {
    echo "[OK] Uploaded clean_live_db.php\n";
} else {
    echo "[FAIL] Upload clean_live_db.php failed\n";
}

unlink($tmp);
ftp_close($conn);
