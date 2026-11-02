<?php
// deploy/deploy_objective_fixes.php
// Safe targeted deployment of existing verified fixes (SO1, SO2, General Objective, Readiness vs Compliance)

$envFile = dirname(__DIR__) . '/.env.infinityfree';
if (!file_exists($envFile)) {
    die("Config file .env.infinityfree not found!\n");
}
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn) {
    die("Could not connect to FTP host {$config['FTP_HOST']}\n");
}
if (!ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("FTP login failed for user {$config['FTP_USER']}\n");
}
ftp_pasv($conn, true);

$baseLocal  = dirname(__DIR__);
$baseRemote = '/signedtest.site.je/htdocs';

$backupDir = $baseLocal . '/deploy/backups/target_fixes_' . date('Ymd_His');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Exactly the minimal required verified files
$filesToDeploy = [
    'app/Models/ObjectiveMetricsModel.php',
    'app/Views/dashboard/admin.php',
    'app/Views/dashboard/principal.php'
];

echo "================================================================\n";
echo " Deploying Verified Objective & Dashboard Fixes\n";
echo " Target: signedtest.site.je\n";
echo " Backup directory: " . basename($backupDir) . "\n";
echo "================================================================\n\n";

$deployedCount = 0;
$failedCount = 0;
$verificationResults = [];

foreach ($filesToDeploy as $relPath) {
    $localFile  = $baseLocal . '/' . $relPath;
    $remoteFile = $baseRemote . '/' . $relPath;

    if (!file_exists($localFile)) {
        echo "[ERROR] Local file not found: {$relPath}\n";
        $failedCount++;
        continue;
    }

    $localSha1 = sha1_file($localFile);
    $localSize = filesize($localFile);

    // Step 1: Backup remote file if it exists
    $localBackupFile = $backupDir . '/' . str_replace('/', '_', $relPath) . '.bak';
    $remoteSize = ftp_size($conn, $remoteFile);
    if ($remoteSize > 0) {
        $tempHandle = fopen($localBackupFile, 'w+');
        if (@ftp_fget($conn, $tempHandle, $remoteFile, FTP_BINARY)) {
            $priorHash = sha1_file($localBackupFile);
            echo "[BACKUP] Saved remote copy of {$relPath} ({$remoteSize} bytes, SHA1: {$priorHash})\n";
        }
        fclose($tempHandle);
    } else {
        echo "[INFO] Remote file {$relPath} does not exist yet (NEW FILE).\n";
    }

    // Step 2: Ensure remote directory exists
    $remoteDirParts = array_filter(explode('/', dirname($remoteFile)));
    $curDir = '';
    foreach ($remoteDirParts as $part) {
        $curDir .= '/' . $part;
        @ftp_mkdir($conn, $curDir);
    }

    // Step 3: Upload local file to remote
    $uploadOk = @ftp_put($conn, $remoteFile, $localFile, FTP_BINARY);
    if (!$uploadOk) {
        echo "  --> [FAILED] Could not upload {$relPath}\n\n";
        $failedCount++;
        continue;
    }

    // Step 4: Verify upload by re-downloading and checking SHA1
    $verifyTemp = $backupDir . '/' . str_replace('/', '_', $relPath) . '.verify';
    $vFp = fopen($verifyTemp, 'w+');
    $downloadOk = @ftp_fget($conn, $vFp, $remoteFile, FTP_BINARY);
    fclose($vFp);

    if ($downloadOk && sha1_file($verifyTemp) === $localSha1) {
        echo "  --> [VERIFIED SUCCESS] Uploaded & Verified {$relPath} ({$localSize} bytes, SHA1: {$localSha1})\n\n";
        $deployedCount++;
        $verificationResults[$relPath] = true;
    } else {
        echo "  --> [VERIFY FAILED] Hash mismatch or re-download failure for {$relPath}\n\n";
        $failedCount++;
        $verificationResults[$relPath] = false;
    }
    @unlink($verifyTemp);
}

ftp_close($conn);

echo "================================================================\n";
echo " Deployment Summary:\n";
echo " Successfully deployed & verified: {$deployedCount} / " . count($filesToDeploy) . " files\n";
if ($failedCount > 0) {
    echo " FAILED / UNVERIFIED: {$failedCount}\n";
    exit(1);
} else {
    echo " Status: 100% COMPLETE & VERIFIED!\n";
    exit(0);
}
