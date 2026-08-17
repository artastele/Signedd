<?php
// Check FTP directory structure to find where files actually landed

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

echo "=== FTP Root (/) listing ===\n";
$root = ftp_nlist($conn, '/');
if ($root) foreach ($root as $f) echo "  $f\n";

echo "\n=== /htdocs listing ===\n";
$htdocs = ftp_nlist($conn, '/htdocs');
if ($htdocs) {
    foreach (array_slice($htdocs, 0, 20) as $f) echo "  $f\n";
    echo "  (" . count($htdocs) . " total items)\n";
} else {
    echo "  (empty or not found)\n";
}

echo "\n=== Current working dir ===\n";
echo ftp_pwd($conn) . "\n";

ftp_close($conn);
