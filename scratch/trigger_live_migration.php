<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    echo "[FAIL] FTP Login failed.\n";
    exit;
}
ftp_pasv($conn, true);

$localFile = dirname(__DIR__) . '/deploy/upload_migrate_live_db.php';
$remoteFile = '/signedtest.site.je/htdocs/deploy_migrate.php';

if (ftp_put($conn, $remoteFile, $localFile, FTP_BINARY)) {
    echo "[OK] Uploaded deploy_migrate.php to live server.\n";
} else {
    echo "[FAIL] Could not upload deploy_migrate.php\n";
}

ftp_close($conn);

// Ping live server script via HTTP
echo "Triggering live DB migration via http://signedtest.site.je/deploy_migrate.php ...\n";
$ch = curl_init('http://signedtest.site.je/deploy_migrate.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
curl_close($ch);

echo "--- LIVE MIGRATION RESPONSE ---\n";
echo $res . "\n";
