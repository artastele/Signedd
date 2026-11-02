<?php
// deploy/download_prod_files.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 20);
if (!$conn || !ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("FTP connection failed.\n");
}
ftp_pasv($conn, true);

$baseRemote = '/signedtest.site.je/htdocs';
$backupDir = dirname(__DIR__) . '/deploy/backups/live_predeploy_' . date('Ymd_His');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$files = [
    'config/db.php',
    'app/Models/MasterlistModel.php',
    'app/Views/dashboard/admin.php',
    'app/Views/dashboard/principal.php',
    'app/Views/masterlist/index.php'
];

echo "Backing up live files to: " . $backupDir . "\n";

foreach ($files as $f) {
    $remotePath = $baseRemote . '/' . $f;
    $localPath = $backupDir . '/' . str_replace('/', '_', $f);
    $size = ftp_size($conn, $remotePath);
    if ($size > 0) {
        $fp = fopen($localPath, 'w+');
        if (ftp_fget($conn, $fp, $remotePath, FTP_BINARY)) {
            $hash = sha1_file($localPath);
            echo "[SAVED] {$f} ({$size} bytes) SHA1: {$hash}\n";
        } else {
            echo "[ERROR] Failed to download {$f}\n";
        }
        fclose($fp);
    } else {
        echo "[INFO] Remote file does not exist: {$f}\n";
    }
}

ftp_close($conn);
echo "Backup complete.\n";
