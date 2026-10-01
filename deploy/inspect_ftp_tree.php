<?php
// Comprehensive FTP tree inspection script

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

function listRecursive($conn, $dir, $depth = 0) {
    if ($depth > 3) return;
    $items = @ftp_nlist($conn, $dir);
    if (!$items) return;
    
    foreach ($items as $item) {
        $basename = basename($item);
        if ($basename == '.' || $basename == '..') continue;
        
        $indent = str_repeat('  ', $depth);
        echo $indent . "- " . $item . "\n";
        
        // Try to list subdirectories
        if (@ftp_chdir($conn, $item)) {
            ftp_chdir($conn, '/');
            listRecursive($conn, $item, $depth + 1);
        }
    }
}

echo "=== Comprehensive FTP Directory Structure ===\n";
listRecursive($conn, '/');

ftp_close($conn);
