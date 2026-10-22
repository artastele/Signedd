<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$localModel = dirname(__DIR__) . '/app/Models/IEPModel.php';

$targets = [
    '/signedtest.site.je/htdocs/app/Models/IEPModel.php',
    '/htdocs/app/Models/IEPModel.php'
];

foreach ($targets as $remotePath) {
    if (ftp_put($conn, $remotePath, $localModel, FTP_BINARY)) {
        echo "[OK] Uploaded $remotePath successfully\n";
    } else {
        echo "[FAIL] Failed to upload $remotePath\n";
    }
}

// Cleanup remote diagnostic files
$diagFiles = [
    '/signedtest.site.je/htdocs/diag_assessment.php',
    '/htdocs/diag_assessment.php'
];
foreach ($diagFiles as $df) {
    if (@ftp_delete($conn, $df)) {
        echo "[CLEANUP] Deleted $df\n";
    }
}

ftp_close($conn);
