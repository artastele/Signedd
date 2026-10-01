<?php
// Migrate existing uploaded files from /public/uploads/ to /uploads/ on flat server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

function copyFtpDir($conn, $src, $dst) {
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
            // Directory
            copyFtpDir($conn, $srcPath, $dstPath);
        } else {
            // File
            $tmp = tempnam(sys_get_temp_dir(), 'ftp');
            if (@ftp_get($conn, $tmp, $srcPath, FTP_BINARY)) {
                if (@ftp_put($conn, $dstPath, $tmp, FTP_BINARY)) {
                    echo "[MOVED] $srcPath => $dstPath ($size bytes)\n";
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

echo "=== Migrating uploaded files on remote server ===\n";
copyFtpDir($conn, '/signedtest.site.je/htdocs/public/uploads', '/signedtest.site.je/htdocs/uploads');

ftp_close($conn);
