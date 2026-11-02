<?php
// deploy/deploy_preview_debug_updates.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn || !ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("FTP connection/login failed.\n");
}
ftp_pasv($conn, true);

$baseLocal  = dirname(__DIR__);
$baseRemote = '/signedtest.site.je/htdocs';

$files = [
    'app/Controllers/IEPController.php',
    'public/js/iep-steps-import.js'
];

foreach ($files as $relPath) {
    $localFile  = $baseLocal . '/' . $relPath;
    $remoteFile = $baseRemote . '/' . $relPath;
    $localSha1 = sha1_file($localFile);

    echo "Uploading {$relPath} to live server...\n";
    $uploadOk = ftp_put($conn, $remoteFile, $localFile, FTP_BINARY);
    if (!$uploadOk) {
        die("Upload failed for {$relPath}!\n");
    }

    $verifyTemp = $baseLocal . '/deploy/backups/' . basename($relPath) . '.verify';
    $fp = fopen($verifyTemp, 'w+');
    ftp_fget($conn, $fp, $remoteFile, FTP_BINARY);
    fclose($fp);

    $remoteSha1 = sha1_file($verifyTemp);
    @unlink($verifyTemp);

    if ($remoteSha1 === $localSha1) {
        echo "SUCCESS: Deployed & verified {$relPath} (SHA1: {$localSha1})\n";
    } else {
        echo "ERROR: Hash mismatch for {$relPath}\n";
        exit(1);
    }
}

ftp_close($conn);
echo "All updates deployed successfully.\n";
