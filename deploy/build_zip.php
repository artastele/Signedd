<?php
/**
 * SignED — Create Deployment ZIP for InfinityFree
 * Creates a zip file ready to upload via InfinityFree File Manager
 */

$rootDir  = dirname(__DIR__);
$zipPath  = $rootDir . '/signedtest_deploy.zip';

$excludePatterns = [
    '^\\.git',
    '^\\.vscode',
    '^\\.idea',
    '^\\.env',
    '^\\.env\\.',
    '^logs/',
    '^scratch/',
    '^deploy\\.zip$',
    '^signedtest_deploy\\.zip$',
    '^composer\\.phar$',
    'vendor/',
    '^public/uploads/',
    '^\\.DS_Store',
    '^Thumbs\\.db',
    '\\.md$',
    '\\.sql$',
    '^deploy/',
];

echo "=========================================================\n";
echo "   SignED - Build Deployment ZIP for InfinityFree\n";
echo "=========================================================\n\n";

if (!class_exists('ZipArchive')) {
    echo "[ERROR] PHP ZipArchive extension is not enabled.\n";
    exit(1);
}

function getFilesToZip($baseDir, $excludePatterns) {
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

$files = getFilesToZip($rootDir, $excludePatterns);

echo "[1/3] Found " . count($files) . " files to package.\n";

if (file_exists($zipPath)) {
    unlink($zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    echo "[ERROR] Cannot create ZIP file at: $zipPath\n";
    exit(1);
}

echo "[2/3] Building ZIP...\n";

// Add all project files
foreach ($files as $relPath) {
    $fullPath = $rootDir . '/' . $relPath;
    $zip->addFile($fullPath, $relPath);
}

// Add the test server .env as .env (the critical config)
$serverEnvFile = $rootDir . '/.env.test-server';
if (file_exists($serverEnvFile)) {
    $zip->addFile($serverEnvFile, '.env');
    echo "[+] Included .env.test-server as .env\n";
}

$zip->close();

$sizeMB = round(filesize($zipPath) / 1024 / 1024, 2);
echo "[3/3] ZIP created: signedtest_deploy.zip ({$sizeMB} MB)\n\n";
echo "=========================================================\n";
echo " NEXT STEPS:\n";
echo "=========================================================\n";
echo " 1. Open InfinityFree Control Panel for if0_42187079\n";
echo " 2. Go to: Online File Manager\n";
echo " 3. Navigate to: /htdocs\n";
echo " 4. Upload: signedtest_deploy.zip (drag & drop or upload button)\n";
echo " 5. Right-click the zip → Extract\n";
echo " 6. Go to phpMyAdmin → import deploy/config/schema.sql\n";
echo "    (upload the SQL file directly from your computer)\n";
echo " 7. Visit: http://signedtest.site.je\n\n";
echo "ZIP location: $zipPath\n\n";
