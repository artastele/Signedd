<?php
// Upload updated parent.php and sidebar.php to live test server via FTP

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$baseLocal = dirname(__DIR__);
$baseRemote = '/signedtest.site.je/htdocs';

$files = [
    'app/Views/dashboard/parent.php',
    'app/Views/layouts/sidebar.php'
];

foreach ($files as $rel) {
    $local = $baseLocal . '/' . $rel;
    $remote = $baseRemote . '/' . $rel;
    if (@ftp_put($conn, $remote, $local, FTP_BINARY)) {
        echo "[OK] Uploaded $rel\n";
    } else {
        echo "[FAIL] Upload failed\n";
    }
}

ftp_close($conn);
