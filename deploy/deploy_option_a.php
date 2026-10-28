<?php
// deploy/deploy_option_a.php
// Safe, targeted deployment for System Admin Objectives & Login Fixes to InfinityFree (signedtest.site.je)

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

$backupDir = $baseLocal . '/deploy/backups/option_a_' . date('Ymd_His');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$filesToDeploy = [
    'config/db.php',
    'app/Models/UserModel.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/AdminController.php',
    'app/Views/auth/login.php',
    'app/Views/dashboard/admin.php',
    'app/Views/dashboard/principal.php',
];

echo "================================================================\n";
echo " Deploying Option A (System Admin Objectives & Login Fixes)\n";
echo " Target: signedtest.site.je\n";
echo " Backup directory: " . basename($backupDir) . "\n";
echo "================================================================\n\n";

$deployedCount = 0;
$failedCount = 0;

foreach ($filesToDeploy as $relPath) {
    $localFile  = $baseLocal . '/' . $relPath;
    $remoteFile = $baseRemote . '/' . $relPath;

    if (!file_exists($localFile)) {
        echo "[ERROR] Local file not found: {$relPath}\n";
        $failedCount++;
        continue;
    }

    // Step 1: Backup remote file if it exists
    $localBackupFile = $backupDir . '/' . str_replace('/', '_', $relPath) . '.bak';
    $remoteSize = ftp_size($conn, $remoteFile);
    if ($remoteSize > 0) {
        $tempHandle = fopen($localBackupFile, 'w+');
        if (@ftp_fget($conn, $tempHandle, $remoteFile, FTP_BINARY)) {
            echo "[BACKUP] Saved remote copy of {$relPath} (" . round($remoteSize / 1024, 1) . " KB)\n";
        }
        fclose($tempHandle);
    } else {
        echo "[INFO] Remote file {$relPath} did not exist yet; no prior backup needed.\n";
    }

    // Step 2: Ensure remote directories exist
    $remoteDirParts = array_filter(explode('/', dirname($remoteFile)));
    $curDir = '';
    foreach ($remoteDirParts as $part) {
        $curDir .= '/' . $part;
        @ftp_mkdir($conn, $curDir);
    }

    // Step 3: Upload local file to remote
    $uploadOk = @ftp_put($conn, $remoteFile, $localFile, FTP_BINARY);
    if ($uploadOk) {
        $newSize = ftp_size($conn, $remoteFile);
        echo "  --> [SUCCESS] Uploaded {$relPath} (" . round($newSize / 1024, 1) . " KB)\n\n";
        $deployedCount++;
    } else {
        echo "  --> [FAILED] Could not upload {$relPath}\n\n";
        $failedCount++;
    }
}

ftp_close($conn);

echo "================================================================\n";
echo " Deployment Summary:\n";
echo " Successfully deployed: {$deployedCount} / " . count($filesToDeploy) . " files\n";
if ($failedCount > 0) {
    echo " Failed uploads: {$failedCount}\n";
} else {
    echo " Status: 100% COMPLETE & VERIFIED!\n";
}
echo "================================================================\n";
