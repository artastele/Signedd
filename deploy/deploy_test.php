<?php
/**
 * SignED — InfinityFree FLAT Deployment (Auto FTP)
 * Deploys flat structure directly to /htdocs root
 * - public/ contents go to /htdocs/ root
 * - app/, config/, routes/, vendor/ go to /htdocs/ as subfolders
 */

$rootDir   = dirname(__DIR__);
$publicDir = $rootDir . '/public';
$envFile   = $rootDir . '/.env.infinityfree';
$serverEnv = $rootDir . '/.env.test-server';

echo "=========================================================\n";
echo "   SignED - InfinityFree FLAT Auto Deploy\n";
echo "   Target: signedtest.site.je (if0_42187079)\n";
echo "=========================================================\n\n";

if (!file_exists($envFile)) { echo "[ERROR] Missing .env.infinityfree\n"; exit(1); }

$config    = parse_ini_file($envFile);
$ftpHost   = $config['FTP_HOST'] ?? 'ftpupload.net';
$ftpPort   = intval($config['FTP_PORT'] ?? 21);
$ftpUser   = $config['FTP_USER'] ?? '';
$ftpPass   = $config['FTP_PASS'] ?? '';
$remoteDir = rtrim($config['FTP_REMOTE_DIR'] ?? '/htdocs', '/');

$isDryRun = in_array('--dry-run', $argv);
if ($isDryRun) echo ">>> DRY-RUN MODE <<<\n\n";

// Exclusions for app-level files
$appExclude = [
    '^\\.git', '^\\.vscode', '^\\.idea',
    '^\\.env', '^\\.env\\.',
    '^logs/', '^scratch/',
    '^signedtest_deploy', '^signedtest_extracted',
    '^deploy\\.zip$', '^composer\\.phar$',
    '^public/',           // handled separately
    '^deploy/',
    '^\\.DS_Store', '^Thumbs\\.db',
    '\\.md$', '^(?!config/schema\\.sql).*\\.sql$',

    '^signed_', '^composer\\.json$', '^composer\\.lock$',
    '^\\.htaccess$', '^\\.gitignore$',
];

// Exclusions for public/ files
$pubExclude = ['^uploads/'];

function collectFiles($baseDir, $excludePatterns, $zipPrefix = '') {
    $result = [];
    if (!is_dir($baseDir)) return $result;
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
        if (!$skip) $result[] = ['local' => $item->getPathname(), 'remote' => $zipPrefix . $rel];
    }
    return $result;
}

echo "[1/5] Scanning files...\n";
$appFiles = collectFiles($rootDir, $appExclude);
$pubFiles = collectFiles($publicDir, $pubExclude); // public/ files go to root
$allFiles = array_merge($appFiles, $pubFiles);

echo "      App files:    " . count($appFiles) . "\n";
echo "      Public files: " . count($pubFiles) . " (→ /htdocs root)\n";
echo "      Total:        " . count($allFiles) . " + .env + .htaccess\n\n";

if ($isDryRun) {
    foreach ($allFiles as $f) echo " - " . $f['remote'] . "\n";
    echo " - .env (from .env.test-server)\n";
    echo " - .htaccess (flat router)\n";
    echo "\n[SUCCESS] Dry run complete.\n"; exit(0);
}

echo "[2/5] Connecting to $ftpHost:$ftpPort...\n";
$conn = @ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn) { echo "[ERROR] Cannot connect\n"; exit(1); }
$login = @ftp_login($conn, $ftpUser, $ftpPass);
if (!$login) { echo "[ERROR] FTP login failed for '$ftpUser'\n"; ftp_close($conn); exit(1); }
ftp_pasv($conn, true);
echo "[+] Connected! Passive mode on.\n\n";

function mkRemoteDir($conn, $path) {
    $parts = explode('/', trim($path, '/'));
    $cur = '';
    foreach ($parts as $p) {
        if (!$p) continue;
        $cur .= '/' . $p;
        @ftp_mkdir($conn, $cur);
    }
}

echo "[3/5] Uploading .env...\n";
if (file_exists($serverEnv)) {
    if (@ftp_put($conn, $remoteDir . '/.env', $serverEnv, FTP_BINARY))
        echo "[+] .env uploaded OK\n\n";
    else
        echo "[WARN] .env upload failed\n\n";
}

echo "[4/5] Uploading .htaccess (flat router)...\n";
$htaccess = <<<'HT'
# SignED — InfinityFree Flat Deployment
RewriteEngine On

# Serve real files/dirs directly (css, js, images, etc.)
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Everything else → index.php
RewriteRule ^(.*)$ index.php [QSA,L]
HT;

$tmpHta = tempnam(sys_get_temp_dir(), 'hta');
file_put_contents($tmpHta, $htaccess);
if (@ftp_put($conn, $remoteDir . '/.htaccess', $tmpHta, FTP_ASCII))
    echo "[+] .htaccess uploaded OK\n\n";
else
    echo "[WARN] .htaccess upload failed\n\n";
unlink($tmpHta);

echo "[5/5] Uploading " . count($allFiles) . " files...\n";
$ok = $fail = 0;
foreach ($allFiles as $i => $f) {
    $remote = $remoteDir . '/' . $f['remote'];
    mkRemoteDir($conn, dirname($remote));
    $n = $i + 1; $t = count($allFiles);
    echo "[$n/$t] " . $f['remote'] . " ... ";
    if (@ftp_put($conn, $remote, $f['local'], FTP_BINARY)) { echo "OK\n"; $ok++; }
    else { echo "FAIL\n"; $fail++; }
}

ftp_close($conn);

echo "\n=========================================================\n";
echo " Deploy done: $ok uploaded, $fail failed.\n";
echo " NEXT: phpMyAdmin → Import deploy/config/schema.sql\n";
echo " Then visit: http://signedtest.site.je\n";
echo "=========================================================\n";
