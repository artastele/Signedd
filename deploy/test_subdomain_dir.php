<?php
// Test uploading to /signedtest.site.je/htdocs/

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

@ftp_mkdir($conn, '/signedtest.site.je');
@ftp_mkdir($conn, '/signedtest.site.je/htdocs');

$tmp = tempnam(sys_get_temp_dir(), 'html');
file_put_contents($tmp, '<html><body><h1>Hello from Subfolder Domain Test!</h1></body></html>');

if (@ftp_put($conn, '/signedtest.site.je/htdocs/hello.html', $tmp, FTP_ASCII)) {
    echo "[OK] Uploaded to /signedtest.site.je/htdocs/hello.html\n";
} else {
    echo "[FAIL] Could not upload to /signedtest.site.je/htdocs/hello.html\n";
}

unlink($tmp);
ftp_close($conn);
