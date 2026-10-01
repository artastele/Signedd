<?php
// Remove seed_live_admin.php from live server for security

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

@ftp_delete($conn, '/signedtest.site.je/htdocs/seed_live_admin.php');
echo "[OK] Cleaned up seed_live_admin.php from server.\n";

ftp_close($conn);
