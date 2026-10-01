<?php
/**
 * Fix .htaccess and upload a PHP test file to InfinityFree
 */

$rootDir = dirname(__DIR__);
$envFile = $rootDir . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$ftpHost   = $config['FTP_HOST'];
$ftpPort   = intval($config['FTP_PORT']);
$ftpUser   = $config['FTP_USER'];
$ftpPass   = $config['FTP_PASS'];
$remoteDir = rtrim($config['FTP_REMOTE_DIR'], '/');

echo "Connecting to FTP...\n";
$conn = @ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn) { echo "[ERROR] Cannot connect\n"; exit(1); }
$login = @ftp_login($conn, $ftpUser, $ftpPass);
if (!$login) { echo "[ERROR] FTP login failed\n"; exit(1); }
ftp_pasv($conn, true);
echo "[OK] Connected!\n\n";

// 1. Upload fixed .htaccess (LiteSpeed/InfinityFree compatible)
$htaccess = 'Options -Indexes
RewriteEngine On
RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
';

$tmp = tempnam(sys_get_temp_dir(), 'hta');
file_put_contents($tmp, $htaccess);
if (@ftp_put($conn, $remoteDir . '/.htaccess', $tmp, FTP_ASCII)) {
    echo "[OK] .htaccess updated (LiteSpeed compatible)\n";
} else {
    echo "[FAIL] .htaccess upload failed\n";
}
unlink($tmp);

// 2. Upload phpinfo test file
$testPhp = '<?php phpinfo(); ?>';
$tmp2 = tempnam(sys_get_temp_dir(), 'php');
file_put_contents($tmp2, $testPhp);
if (@ftp_put($conn, $remoteDir . '/info.php', $tmp2, FTP_ASCII)) {
    echo "[OK] info.php uploaded - visit http://signedtest.site.je/info.php to test PHP\n";
} else {
    echo "[FAIL] info.php upload failed\n";
}
unlink($tmp2);

ftp_close($conn);
echo "\nDone! Check http://signedtest.site.je/info.php\n";
