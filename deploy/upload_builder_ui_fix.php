<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$localBuilder = dirname(__DIR__) . '/app/Views/iep_implementation/lesson_slide_builder.php';
$localMasterlist = dirname(__DIR__) . '/app/Views/masterlist/index.php';

$targets = [
    '/signedtest.site.je/htdocs/app/Views/iep_implementation/lesson_slide_builder.php' => $localBuilder,
    '/htdocs/app/Views/iep_implementation/lesson_slide_builder.php' => $localBuilder,
    '/signedtest.site.je/htdocs/app/Views/masterlist/index.php' => $localMasterlist,
    '/htdocs/app/Views/masterlist/index.php' => $localMasterlist,
];

foreach ($targets as $remotePath => $localFile) {
    if (file_exists($localFile) && ftp_put($conn, $remotePath, $localFile, FTP_BINARY)) {
        echo "[OK] Uploaded $remotePath successfully\n";
    } else {
        echo "[FAIL] Failed to upload $remotePath\n";
    }
}

ftp_close($conn);
