<?php
// Check all root directories in FTP

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

echo "Raw ftp_rawlist of /:\n";
$list = ftp_rawlist($conn, '/');
foreach ($list as $line) {
    echo $line . "\n";
}

ftp_close($conn);
