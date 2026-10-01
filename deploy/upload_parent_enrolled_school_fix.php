<?php
// Upload updated parent.php (enrolled school card without dropdown) to live test server via FTP

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$local = dirname(__DIR__) . '/app/Views/dashboard/parent.php';
$remote = '/signedtest.site.je/htdocs/app/Views/dashboard/parent.php';

if (@ftp_put($conn, $remote, $local, FTP_BINARY)) {
    echo "[OK] Uploaded app/Views/dashboard/parent.php\n";
} else {
    echo "[FAIL] Upload failed\n";
}

ftp_close($conn);
