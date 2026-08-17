<?php
// Remove api_create_assignment_table.php from server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

@ftp_delete($conn, '/signedtest.site.je/htdocs/api_create_assignment_table.php');
echo "[OK] Cleaned up api_create_assignment_table.php\n";

ftp_close($conn);
