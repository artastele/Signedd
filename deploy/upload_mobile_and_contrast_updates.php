<?php
// SignED — Upload Mobile Responsive Redesign & Color Contrast Polish to Live Server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
if (!file_exists($envFile)) {
    die("[ERROR] .env.infinityfree not found\n");
}
$config = parse_ini_file($envFile);

echo "[+] Connecting to FTP host {$config['FTP_HOST']}...\n";
$conn = @ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if (!$conn) {
    die("[ERROR] Could not connect to FTP host {$config['FTP_HOST']}\n");
}

if (!@ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("[ERROR] FTP login failed for user {$config['FTP_USER']}\n");
}

ftp_pasv($conn, true);
echo "[OK] Connected and authenticated to InfinityFree FTP successfully.\n";

$baseRemote = '/signedtest.site.je/htdocs';
$baseLocal  = dirname(__DIR__);

// Helper to ensure remote dir exists
function ensureRemoteDir($conn, $dir) {
    $parts = explode('/', trim($dir, '/'));
    $current = '';
    foreach ($parts as $p) {
        if (!$p) continue;
        $current .= '/' . $p;
        if (!@ftp_chdir($conn, $current)) {
            @ftp_mkdir($conn, $current);
        }
    }
}

// File mappings: local relative path => array of remote paths (supports both flat webroot and nested paths)
$mappings = [
    // Core styling & updated dashboards
    'public/css/custom.css' => [
        $baseRemote . '/css/custom.css',
        $baseRemote . '/public/css/custom.css',
    ],
    'app/Views/dashboard/general.php' => [
        $baseRemote . '/app/Views/dashboard/general.php',
    ],
    'app/Views/dashboard/general_teacher.php' => [
        $baseRemote . '/app/Views/dashboard/general_teacher.php',
    ],
    'app/Views/dashboard/parent.php' => [
        $baseRemote . '/app/Views/dashboard/parent.php',
    ],
    'app/Views/dashboard/teacher.php' => [
        $baseRemote . '/app/Views/dashboard/teacher.php',
    ],

    // Synchronize other recently updated files
    'app/Controllers/AuthController.php' => [
        $baseRemote . '/app/Controllers/AuthController.php',
    ],
    'app/Controllers/EnrollmentController.php' => [
        $baseRemote . '/app/Controllers/EnrollmentController.php',
    ],
    'app/Controllers/RoleController.php' => [
        $baseRemote . '/app/Controllers/RoleController.php',
    ],
    'app/Views/auth/claim_parent.php' => [
        $baseRemote . '/app/Views/auth/claim_parent.php',
    ],
    'app/Views/auth/register.php' => [
        $baseRemote . '/app/Views/auth/register.php',
    ],
    'app/Views/auth/role_select.php' => [
        $baseRemote . '/app/Views/auth/role_select.php',
    ],
    'app/Views/enrollment/form.php' => [
        $baseRemote . '/app/Views/enrollment/form.php',
    ],
    'app/Views/enrollment/steps/step1_learner_info.php' => [
        $baseRemote . '/app/Views/enrollment/steps/step1_learner_info.php',
    ],
    'app/Views/enrollment/steps/step7_documents_signature.php' => [
        $baseRemote . '/app/Views/enrollment/steps/step7_documents_signature.php',
    ],
    'app/Views/profile/index.php' => [
        $baseRemote . '/app/Views/profile/index.php',
    ],
];

$successCount = 0;
$totalTargetUploads = 0;

foreach ($mappings as $localRel => $remoteDestinations) {
    $localPath = $baseLocal . '/' . $localRel;
    if (!file_exists($localPath)) {
        echo "[SKIP] Local missing: $localRel\n";
        continue;
    }

    foreach ($remoteDestinations as $remotePath) {
        $totalTargetUploads++;
        $remoteDir = dirname($remotePath);
        ensureRemoteDir($conn, $remoteDir);

        if (@ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
            echo "[SUCCESS] Uploaded $localRel => $remotePath\n";
            $successCount++;
        } else {
            echo "[FAIL] Could not upload $localRel => $remotePath\n";
        }
    }
}

ftp_close($conn);

echo "\n[DONE] Successfully uploaded $successCount of $totalTargetUploads destination targets.\n";
