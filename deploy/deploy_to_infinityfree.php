<?php
/**
 * InfinityFree Safe Deployment Tool
 * 
 * Usage:
 *   php deploy/deploy_to_infinityfree.php             (Perform live deployment)
 *   php deploy/deploy_to_infinityfree.php --dry-run   (Preview files to be uploaded without uploading)
 */

$rootDir = dirname(__DIR__);
$envFile = $rootDir . '/.env.infinityfree';

echo "=========================================================\n";
echo "       SignED - InfinityFree Deployment Manager          \n";
echo "=========================================================\n\n";

if (!file_exists($envFile)) {
    echo "[!] Configuration file not found: .env.infinityfree\n";
    echo "[!] Please create .env.infinityfree in project root with:\n\n";
    echo "FTP_HOST=ftpupload.net\n";
    echo "FTP_PORT=21\n";
    echo "FTP_USER=if0_XXXXXXXX\n";
    echo "FTP_PASS=your_ftp_password\n";
    echo "FTP_REMOTE_DIR=/htdocs\n\n";
    exit(1);
}

// Parse configuration file
$config = parse_ini_file($envFile);

$ftpHost = $config['FTP_HOST'] ?? 'ftpupload.net';
$ftpPort = intval($config['FTP_PORT'] ?? 21);
$ftpUser = $config['FTP_USER'] ?? '';
$ftpPass = $config['FTP_PASS'] ?? '';
$remoteDir = rtrim($config['FTP_REMOTE_DIR'] ?? '/htdocs', '/');

if (empty($ftpUser) || empty($ftpPass)) {
    echo "[ERROR] FTP_USER and FTP_PASS must be set in .env.infinityfree\n";
    exit(1);
}

$isDryRun = in_array('--dry-run', $argv);
if ($isDryRun) {
    echo ">>> RUNNING IN DRY-RUN MODE (No files will be uploaded) <<<\n\n";
}

// Folders / files to completely ignore for safety
$excludePatterns = [
    '^\.git',
    '^\.vscode',
    '^\.idea',
    '^\.env',               // CRITICAL: Protect live remote .env from being overwritten
    '^\.env\.',
    '^logs/',
    '^scratch/',
    '^deploy\.zip$',
    '^composer\.phar$',
    'vendor/',             // Ignore composer vendor dependencies
    '^public/uploads/',     // Protect live user uploads
    '^\.DS_Store',
    '^Thumbs\.db',
    '\.md$',                // Exclude local markdown documentation files
    '\.sql$',               // Exclude SQL backup/seed files from direct web upload
    '^deploy/'              // Exclude deployment scripts directory from remote sync
];

echo "[1/4] Scanning local project files...\n";

function getFilesToUpload($baseDir, $excludePatterns) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isFile()) {
            $relativePath = str_replace('\\', '/', substr($item->getPathname(), strlen($baseDir) + 1));
            
            $excluded = false;
            foreach ($excludePatterns as $pattern) {
                if (preg_match('#' . $pattern . '#i', $relativePath)) {
                    $excluded = true;
                    break;
                }
            }

            if (!$excluded) {
                $files[] = $relativePath;
            }
        }
    }
    return $files;
}

$filesToUpload = getFilesToUpload($rootDir, $excludePatterns);
echo "[+] Found " . count($filesToUpload) . " safe files to synchronize.\n\n";

if ($isDryRun) {
    echo "Files that will be synchronized:\n";
    foreach ($filesToUpload as $f) {
        echo " - $f\n";
    }
    echo "\n[SUCCESS] Dry run complete. " . count($filesToUpload) . " files verified safe.\n";
    exit(0);
}

echo "[2/4] Connecting to InfinityFree FTP ($ftpHost:$ftpPort)...\n";
$conn = @ftp_connect($ftpHost, $ftpPort, 15);

if (!$conn) {
    echo "[ERROR] Could not connect to FTP host '$ftpHost'. Please check host and port.\n";
    exit(1);
}

$login = @ftp_login($conn, $ftpUser, $ftpPass);
if (!$login) {
    echo "[ERROR] FTP login failed for user '$ftpUser'. Please check your password in .env.infinityfree.\n";
    ftp_close($conn);
    exit(1);
}

// Enable passive mode (required for InfinityFree / firewalls)
ftp_pasv($conn, true);
echo "[+] FTP login successful. Passive mode enabled.\n\n";

echo "[3/4] Ensuring remote root directory exists ($remoteDir)...\n";
@ftp_mkdir($conn, $remoteDir);
@ftp_chdir($conn, $remoteDir);

echo "[4/4] Synchronizing files...\n";

function ensureRemoteDirExists($conn, $path) {
    $parts = explode('/', trim($path, '/'));
    $current = '';
    foreach ($parts as $part) {
        if (empty($part)) continue;
        $current .= '/' . $part;
        @ftp_mkdir($conn, $current);
    }
}

$successCount = 0;
$failCount = 0;

foreach ($filesToUpload as $index => $relPath) {
    $localFilePath = $rootDir . '/' . $relPath;
    $remoteFilePath = $remoteDir . '/' . $relPath;
    $remoteFileDir = dirname($remoteFilePath);

    ensureRemoteDirExists($conn, $remoteFileDir);

    $num = $index + 1;
    $total = count($filesToUpload);
    echo "[$num/$total] Uploading: $relPath ... ";

    if (@ftp_put($conn, $remoteFilePath, $localFilePath, FTP_BINARY)) {
        echo "OK\n";
        $successCount++;
    } else {
        echo "FAILED\n";
        $failCount++;
    }
}

ftp_close($conn);

echo "\n=========================================================\n";
echo " Deployment Finished: $successCount uploaded, $failCount failed.\n";
echo "=========================================================\n";

