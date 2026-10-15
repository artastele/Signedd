<?php
// SignED — Upload SMS & Universal GC Parent Claim Updates to Live Server

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
    'app/Services/SmsService.php',
    'app/Views/auth/claim_universal.php',
    'app/Controllers/ParentClaimController.php',
    'routes/web.php',
    'app/Models/MasterlistModel.php',
    'app/Controllers/MasterlistController.php',
    'app/Views/masterlist/index.php',
    'app/Controllers/IEPImplementationController.php',
    'app/Controllers/LearningController.php',
    'app/Controllers/DashboardController.php',
    'app/Views/layouts/navbar.php',
    'app/Controllers/AdminController.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/EnrollmentController.php',
    'app/Controllers/RoleController.php',
    'app/Models/ProgressReportModel.php',
    'app/Views/auth/claim_parent.php',
    'app/Views/auth/register.php',
    'app/Views/auth/role_select.php',
    'app/Views/dashboard/general.php',
    'app/Views/dashboard/general_teacher.php',
    'app/Views/dashboard/parent.php',
    'app/Views/dashboard/teacher.php',
    'app/Views/enrollment/form.php',
    'app/Views/enrollment/steps/step1_learner_info.php',
    'app/Views/enrollment/steps/step7_documents_signature.php',
    'app/Views/profile/index.php',
    'app/Views/components/data_privacy_modal.php',
    'public/css/custom.css',
    'config/schema.sql',
    'public/run_live_sms_migration.php',
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
        echo "[FAILED] Failed uploading: $relPath\n";
    }
}

ftp_close($conn);
echo "\nFinished uploading {$successCount} / " . count($files) . " files.\n";
