<?php
// Upload logo-fixed view files to remote server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$baseLocal = dirname(__DIR__) . '/app/Views/';
$baseRemote = '/signedtest.site.je/htdocs/app/Views/';

$files = [
    'auth/login.php',
    'auth/register.php',
    'auth/verify_email.php',
    'layouts/sidebar.php'
];

foreach ($files as $rel) {
    $local = $baseLocal . $rel;
    $remote = $baseRemote . $rel;
    if (@ftp_put($conn, $remote, $local, FTP_BINARY)) {
        echo "[OK] Uploaded $rel\n";
    } else {
        echo "[FAIL] Failed to upload $rel\n";
    }
}

ftp_close($conn);
