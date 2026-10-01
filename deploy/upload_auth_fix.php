<?php
// Upload updated AuthController.php to remote server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$localAuth = dirname(__DIR__) . '/app/Controllers/AuthController.php';
$remoteAuth = '/signedtest.site.je/htdocs/app/Controllers/AuthController.php';

if (@ftp_put($conn, $remoteAuth, $localAuth, FTP_BINARY)) {
    echo "[OK] Uploaded updated AuthController.php to $remoteAuth\n";
} else {
    echo "[FAIL] Upload failed\n";
}

ftp_close($conn);
