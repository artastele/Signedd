<?php
/**
 * Immediate location fix sync script for signedtest.site.je
 */
$rootDir = dirname(__DIR__);
$envFile = $rootDir . '/.env.infinityfree';

if (!file_exists($envFile)) {
    echo "[ERROR] Missing .env.infinityfree\n";
    exit(1);
}

$config    = parse_ini_file($envFile);
$ftpHost   = $config['FTP_HOST'] ?? 'ftpupload.net';
$ftpPort   = intval($config['FTP_PORT'] ?? 21);
$ftpUser   = $config['FTP_USER'] ?? '';
$ftpPass   = $config['FTP_PASS'] ?? '';
$remoteDir = rtrim($config['FTP_REMOTE_DIR'] ?? '/htdocs', '/');

echo "Connecting to $ftpHost...\n";
$conn = @ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn) { echo "[ERROR] Connect failed\n"; exit(1); }

if (!@ftp_login($conn, $ftpUser, $ftpPass)) {
    echo "[ERROR] Login failed\n";
    ftp_close($conn);
    exit(1);
}
ftp_pasv($conn, true);
echo "[+] Connected!\n";

$filesToUpload = [
    'app/Controllers/LocationController.php' => $remoteDir . '/app/Controllers/LocationController.php',
    'routes/web.php' => $remoteDir . '/routes/web.php',
    'public/js/enrollment.js' => $remoteDir . '/js/enrollment.js',
    'app/Views/enrollment/steps/step2_current_address.php' => $remoteDir . '/app/Views/enrollment/steps/step2_current_address.php',
    'public/api-provinces.php' => $remoteDir . '/api-provinces.php',
    'public/api-cities.php' => $remoteDir . '/api-cities.php',
    'public/api-barangays.php' => $remoteDir . '/api-barangays.php',
    'public/data/philippines.json' => $remoteDir . '/data/philippines.json',
    'public/data/philippines.json' => $remoteDir . '/public/data/philippines.json',
    'app/Views/components/upload-zone.php' => $remoteDir . '/app/Views/components/upload-zone.php',
    'public/css/custom.css' => $remoteDir . '/css/custom.css',
];

foreach ($filesToUpload as $localRel => $remotePath) {
    $localPath = $rootDir . '/' . $localRel;
    if (!file_exists($localPath)) {
        echo "[SKIP] Local missing: $localRel\n";
        continue;
    }
    
    // Ensure remote directory exists
    $dir = dirname($remotePath);
    $parts = explode('/', trim($dir, '/'));
    $cur = '';
    foreach ($parts as $p) {
        if (!$p) continue;
        $cur .= '/' . $p;
        @ftp_mkdir($conn, $cur);
    }
    
    if (@ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
        echo "[OK] Uploaded: $localRel -> $remotePath\n";
    } else {
        echo "[FAIL] Upload failed: $localRel -> $remotePath\n";
    }
}

ftp_close($conn);
echo "\n[DONE] Immediate location fix sync completed!\n";
