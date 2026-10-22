<?php
// deploy/upload_password_rule_fix.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$baseLocal = dirname(__DIR__);
$baseRemote = '/signedtest.site.je/htdocs';

$files = [
    'app/Views/auth/register.php',
    'app/Controllers/AuthController.php',
    'app/Views/auth/claim_parent.php',
    'app/Views/auth/claim_universal.php',
    'app/Controllers/ParentClaimController.php',
];

echo "=== Uploading password requirement fixes to InfinityFree ===\n";
foreach ($files as $rel) {
    $local = $baseLocal . '/' . $rel;
    $remote = $baseRemote . '/' . $rel;
    if (ftp_put($conn, $remote, $local, FTP_BINARY)) {
        echo "[OK] Uploaded $rel\n";
    } else {
        echo "[FAIL] Failed $rel\n";
    }
}

ftp_close($conn);
echo "=== Done ===\n";
