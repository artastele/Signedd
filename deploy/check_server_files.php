<?php
// Download and inspect key files from the server
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

// Download .htaccess
$tmpHta = tempnam(sys_get_temp_dir(), 'hta');
ftp_get($conn, $tmpHta, '/htdocs/.htaccess', FTP_ASCII);
echo "=== /htdocs/.htaccess ===\n";
echo file_get_contents($tmpHta) . "\n";

// Download first 30 lines of index.php
$tmpIdx = tempnam(sys_get_temp_dir(), 'idx');
ftp_get($conn, $tmpIdx, '/htdocs/index.php', FTP_ASCII);
echo "=== /htdocs/index.php (first 10 lines) ===\n";
$lines = file($tmpIdx);
echo implode('', array_slice($lines, 0, 10)) . "\n";

// Check if info.php is there
$tmpInfo = tempnam(sys_get_temp_dir(), 'inf');
if (@ftp_get($conn, $tmpInfo, '/htdocs/info.php', FTP_ASCII)) {
    echo "=== /htdocs/info.php ===\n";
    echo file_get_contents($tmpInfo) . "\n";
}

ftp_close($conn);
