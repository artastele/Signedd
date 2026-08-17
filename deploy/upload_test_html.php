<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

// Upload plain hello.html - no PHP, no .htaccess
$tmp = tempnam(sys_get_temp_dir(), 'html');
file_put_contents($tmp, '<html><body><h1>Hello from SignED!</h1><p>PHP + files are deployed correctly.</p></body></html>');

if (@ftp_put($conn, '/htdocs/hello.html', $tmp, FTP_ASCII)) {
    echo "[OK] hello.html uploaded\n";
    echo "Visit: http://signedtest.site.je/hello.html\n";
} else {
    echo "[FAIL]\n";
}
unlink($tmp);
ftp_close($conn);
