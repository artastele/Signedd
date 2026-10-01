<?php
// Upload SchemaManager.php and schema.sql to /signedtest.site.je/htdocs/config/

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$localManager = dirname(__DIR__) . '/config/SchemaManager.php';
$remoteManager = '/signedtest.site.je/htdocs/config/SchemaManager.php';

if (@ftp_put($conn, $remoteManager, $localManager, FTP_BINARY)) {
    echo "[OK] Uploaded SchemaManager.php\n";
} else {
    echo "[FAIL] Failed to upload SchemaManager.php\n";
}

$localSchema = dirname(__DIR__) . '/config/schema.sql';
$remoteSchema = '/signedtest.site.je/htdocs/config/schema.sql';

if (@ftp_put($conn, $remoteSchema, $localSchema, FTP_BINARY)) {
    echo "[OK] Uploaded schema.sql\n";
} else {
    echo "[FAIL] Failed to upload schema.sql\n";
}

ftp_close($conn);
