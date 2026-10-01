<?php
// Check image paths on remote server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

// Check files in /signedtest.site.je/htdocs/images
echo "=== Listing /signedtest.site.je/htdocs/images ===\n";
$images = ftp_nlist($conn, '/signedtest.site.je/htdocs/images');
if ($images) {
    foreach ($images as $img) echo "  $img\n";
} else {
    echo "  (NO IMAGES FOUND OR DIRECTORY DOES NOT EXIST!)\n";
}

ftp_close($conn);
