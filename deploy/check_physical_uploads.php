<?php
// Check physical existence of uploaded school files on FTP server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$filesToCheck = [
    '/signedtest.site.je/htdocs/uploads/schools/school_1_1786529718.jpg',
    '/signedtest.site.je/htdocs/uploads/pubmats/pubmat_1_1786530672.jpg',
    '/signedtest.site.je/htdocs/public/uploads/schools/school_1_1786529718.jpg',
    '/signedtest.site.je/htdocs/public/uploads/pubmats/pubmat_1_1786530672.jpg',
];

echo "=== Checking Physical Files on Remote Server ===\n";
foreach ($filesToCheck as $path) {
    $size = @ftp_size($conn, $path);
    if ($size !== -1) {
        echo "[EXISTS] $path (Size: $size bytes)\n";
    } else {
        echo "[NOT FOUND] $path\n";
    }
}

ftp_close($conn);
