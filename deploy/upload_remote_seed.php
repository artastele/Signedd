<?php
// Upload seed_remote_demo_staff.php to live server via FTP

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$local = dirname(__DIR__) . '/deploy/seed_remote_demo_staff.php';
$remote = '/signedtest.site.je/htdocs/deploy/seed_remote_demo_staff.php';

// Ensure deploy directory exists
@ftp_mkdir($conn, '/signedtest.site.je/htdocs/deploy');

if (@ftp_put($conn, $remote, $local, FTP_BINARY)) {
    echo "[OK] Uploaded deploy/seed_remote_demo_staff.php\n";
} else {
    echo "[FAIL] Upload failed\n";
}

ftp_close($conn);
