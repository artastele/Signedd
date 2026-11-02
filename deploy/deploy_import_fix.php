<?php
// deploy/deploy_import_fix.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn || !ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("FTP connection/login failed.\n");
}
ftp_pasv($conn, true);

$baseLocal  = dirname(__DIR__);
$baseRemote = '/signedtest.site.je/htdocs';
$relPath = 'app/Services/IEPPartThreeImportService.php';

$localFile  = $baseLocal . '/' . $relPath;
$remoteFile = $baseRemote . '/' . $relPath;
$localSha1 = sha1_file($localFile);

echo "Uploading fixed {$relPath} to live server...\n";
$uploadOk = ftp_put($conn, $remoteFile, $localFile, FTP_BINARY);

if (!$uploadOk) {
    die("Upload failed!\n");
}

$verifyTemp = $baseLocal . '/deploy/backups/import_service_verify.tmp';
$fp = fopen($verifyTemp, 'w+');
ftp_fget($conn, $fp, $remoteFile, FTP_BINARY);
fclose($fp);

$remoteSha1 = sha1_file($verifyTemp);
@unlink($verifyTemp);
ftp_close($conn);

if ($remoteSha1 === $localSha1) {
    echo "SUCCESS: Deployed and verified {$relPath} (SHA1: {$localSha1})\n";
} else {
    echo "ERROR: Hash mismatch! Local: {$localSha1}, Remote: {$remoteSha1}\n";
    exit(1);
}
