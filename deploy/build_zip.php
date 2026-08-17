<?php
/**
 * SignED — Build FLAT Deployment ZIP for InfinityFree
 *
 * Structure on server (/htdocs):
 *   index.php        <- from public/index.php (already self-detecting)
 *   .htaccess        <- routes ALL requests to index.php
 *   .env             <- from .env.test-server
 *   app/
 *   config/
 *   routes/
 *   vendor/
 *   css/             <- from public/css/
 *   js/              <- from public/js/
 *   images/          <- from public/images/
 *   data/            <- from public/data/
 *   templates/       <- from public/templates/
 *   api-*.php        <- from public/api-*.php
 */

$rootDir  = dirname(__DIR__);
$zipPath  = $rootDir . '/signedtest_deploy.zip';
$publicDir = $rootDir . '/public';

echo "=========================================================\n";
echo "   SignED - Build FLAT InfinityFree Deployment ZIP\n";
echo "=========================================================\n\n";

if (!class_exists('ZipArchive')) {
    echo "[ERROR] PHP ZipArchive extension not enabled.\n"; exit(1);
}

// -------------------------------------------------------
// 1. App-level files (app/, config/, routes/, vendor/)
//    go to root of ZIP as-is
// -------------------------------------------------------
$appExclude = [
    '^\\.git', '^\\.vscode', '^\\.idea',
    '^\\.env', '^\\.env\\.',
    '^logs/', '^scratch/',
    '^signedtest_deploy\\.zip$', '^deploy\\.zip$',
    '^composer\\.phar$',
    '^public/',       // handled separately below
    '^deploy/',       // exclude deploy scripts
    '^\\.DS_Store', '^Thumbs\\.db',
    '\\.md$', '\\.sql$',
    '^signed_', '^composer\\.json$', '^composer\\.lock$',
    '^\\.htaccess$', '^\\.gitignore$',
];

// -------------------------------------------------------
// 2. public/ files go to ROOT of ZIP (not under public/)
//    Exception: public/uploads/ excluded (user data)
// -------------------------------------------------------
$publicExclude = [
    '^uploads/',
];

function collectFiles($baseDir, $excludePatterns, $prefix = '') {
    $result = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($it as $item) {
        if (!$item->isFile()) continue;
        $rel = str_replace('\\', '/', substr($item->getPathname(), strlen($baseDir) + 1));
        $skip = false;
        foreach ($excludePatterns as $p) {
            if (preg_match('#' . $p . '#i', $rel)) { $skip = true; break; }
        }
        if (!$skip) {
            $result[] = [
                'local' => $item->getPathname(),
                'zip'   => $prefix . $rel,
            ];
        }
    }
    return $result;
}

echo "[1/4] Collecting app-level files (app/, config/, routes/, vendor/)...\n";
$appFiles = collectFiles($rootDir, $appExclude);
echo "      Found: " . count($appFiles) . " files\n";

echo "[2/4] Collecting public/ files (css/, js/, images/, index.php etc.)...\n";
$publicFiles = collectFiles($publicDir, $publicExclude);
echo "      Found: " . count($publicFiles) . " files (will go to root)\n";

$total = count($appFiles) + count($publicFiles) + 3; // +3 for .env, .htaccess, index.php
echo "\n[3/4] Building ZIP ($total files)...\n";

if (file_exists($zipPath)) unlink($zipPath);
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    echo "[ERROR] Cannot create ZIP.\n"; exit(1);
}

// Add app-level files
foreach ($appFiles as $f) {
    $zip->addFile($f['local'], $f['zip']);
}

// Add public/ files at root level (css/, js/, images/, index.php, etc.)
foreach ($publicFiles as $f) {
    $zip->addFile($f['local'], $f['zip']);
}

// Add .env.test-server as .env
$serverEnv = $rootDir . '/.env.test-server';
if (file_exists($serverEnv)) {
    $zip->addFile($serverEnv, '.env');
    echo "      [+] .env.test-server => .env\n";
}

// Add root .htaccess that routes everything to index.php (public/)
$htaccess = <<<'HTACCESS'
# SignED — InfinityFree .htaccess
# Routes all requests to index.php

<IfModule mod_rewrite.c>
    RewriteEngine On

    # Serve existing files/dirs directly (css, js, images, etc.)
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Everything else → index.php
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
HTACCESS;

$zip->addFromString('.htaccess', $htaccess);
echo "      [+] Root .htaccess (flat routing)\n";

$zip->close();

$sizeMB = round(filesize($zipPath) / 1024 / 1024, 2);
echo "\n[4/4] ZIP created: signedtest_deploy.zip ({$sizeMB} MB)\n";
echo "\n=========================================================\n";
echo " UPLOAD INSTRUCTIONS (InfinityFree File Manager):\n";
echo "=========================================================\n";
echo " 1. Go to File Manager → /htdocs\n";
echo " 2. DELETE everything currently in /htdocs\n";
echo " 3. Upload signedtest_deploy.zip\n";
echo " 4. Extract → files land directly in /htdocs root\n";
echo " 5. phpMyAdmin → Import deploy/config/schema.sql\n";
echo " 6. Visit: http://signedtest.site.je\n\n";
echo "ZIP: $zipPath\n\n";
