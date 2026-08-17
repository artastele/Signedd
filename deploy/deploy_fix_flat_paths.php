<?php
// Migrate all uploaded files and update config/env.php on live server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

// 1. Upload updated config/env.php
$localEnv = dirname(__DIR__) . '/config/env.php';
$remoteEnv = '/signedtest.site.je/htdocs/config/env.php';

if (@ftp_put($conn, $remoteEnv, $localEnv, FTP_BINARY)) {
    echo "[OK] Uploaded config/env.php\n";
} else {
    echo "[FAIL] Upload config/env.php failed\n";
}

// 2. Recursive migration function
function migrateDir($conn, $src, $dst) {
    @ftp_mkdir($conn, $dst);
    $items = @ftp_nlist($conn, $src);
    if (!$items) return;
    
    foreach ($items as $item) {
        $name = basename($item);
        if ($name == '.' || $name == '..') continue;
        
        $srcPath = $src . '/' . $name;
        $dstPath = $dst . '/' . $name;
        
        $size = @ftp_size($conn, $srcPath);
        if ($size === -1) {
            migrateDir($conn, $srcPath, $dstPath);
        } else {
            $tmp = tempnam(sys_get_temp_dir(), 'ftpmig');
            if (@ftp_get($conn, $tmp, $srcPath, FTP_BINARY)) {
                if (@ftp_put($conn, $dstPath, $tmp, FTP_BINARY)) {
                    echo "[COPIED] $srcPath => $dstPath ($size bytes)\n";
                } else {
                    echo "[FAIL PUT] $dstPath\n";
                }
            } else {
                echo "[FAIL GET] $srcPath\n";
            }
            @unlink($tmp);
        }
    }
}

echo "=== Syncing Uploaded Files to Flat Webroot ===\n";
migrateDir($conn, '/signedtest.site.je/htdocs/public/uploads', '/signedtest.site.je/htdocs/uploads');

ftp_close($conn);
