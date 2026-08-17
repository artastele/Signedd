<?php
// Upload updated index.php to /signedtest.site.je/htdocs/index.php

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$localIndex = dirname(__DIR__) . '/public/index.php';
$remoteIndex = '/signedtest.site.je/htdocs/index.php';

if (@ftp_put($conn, $remoteIndex, $localIndex, FTP_BINARY)) {
    echo "[OK] Uploaded updated index.php to $remoteIndex\n";
} else {
    echo "[FAIL] Upload failed\n";
}

ftp_close($conn);
