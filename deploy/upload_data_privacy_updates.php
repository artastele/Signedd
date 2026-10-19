<?php
// SignED — Upload Data Privacy Act Updates to Live Server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
if (!file_exists($envFile)) {
    die("[ERROR] .env.infinityfree not found\n");
}
$config = parse_ini_file($envFile);

$conn = @ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn) {
    die("[ERROR] Could not connect to FTP host {$config['FTP_HOST']}\n");
}

if (!@ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("[ERROR] FTP login failed for user {$config['FTP_USER']}\n");
}

ftp_pasv($conn, true);
echo "[OK] Connected to InfinityFree FTP successfully.\n";

$baseRemote = '/signedtest.site.je/htdocs';
$baseLocal  = dirname(__DIR__);

$files = [
    'app/Views/components/data_privacy_modal.php',
    'app/Views/auth/register.php',
    'app/Controllers/AuthController.php',
    'app/Views/enrollment/steps/step1_learner_info.php',
    'app/Views/enrollment/steps/step7_documents_signature.php',
    'app/Views/enrollment/form.php',
    'app/Controllers/EnrollmentController.php',
    'app/Views/auth/claim_parent.php',
    'app/Controllers/ParentClaimController.php',
    'app/Views/auth/role_select.php',
    'app/Controllers/RoleController.php',
    'app/Views/profile/index.php',
];

// Helper to ensure remote dir exists
function ensureRemoteDir($conn, $dir) {
    $parts = explode('/', trim($dir, '/'));
    $current = '';
    foreach ($parts as $p) {
        $current .= '/' . $p;
        if (!@ftp_chdir($conn, $current)) {
            @ftp_mkdir($conn, $current);
        }
    }
}

$successCount = 0;
foreach ($files as $relPath) {
    $localFile  = $baseLocal . '/' . $relPath;
    $remoteFile = $baseRemote . '/' . $relPath;

    if (!file_exists($localFile)) {
        echo "[SKIP] Local file not found: $relPath\n";
        continue;
    }

    $remoteDir = dirname($remoteFile);
    ensureRemoteDir($conn, $remoteDir);

    if (@ftp_put($conn, $remoteFile, $localFile, FTP_BINARY)) {
        echo "[SUCCESS] Uploaded: $relPath\n";
        $successCount++;
    } else {
        echo "[FAIL] Failed to upload: $relPath\n";
    }
}

ftp_close($conn);

echo "\nFinished uploading $successCount / " . count($files) . " files.\n";
