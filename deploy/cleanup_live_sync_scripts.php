<?php
// deploy/cleanup_live_sync_scripts.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$baseRemote = '/signedtest.site.je/htdocs';
$toDelete = [
    $baseRemote . '/sync_sections_live.php',
    $baseRemote . '/inspect_students_live.php',
    $baseRemote . '/run_live_fix_sync.php'
];

foreach ($toDelete as $f) {
    if (@ftp_delete($conn, $f)) {
        echo "[CLEANUP] Deleted $f\n";
    } else {
        echo "[INFO] Could not delete $f\n";
    }
}

ftp_close($conn);
echo "Cleanup completed.\n";
