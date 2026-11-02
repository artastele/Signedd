<?php
// deploy/inspect_remote.php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
if (!file_exists($envFile)) {
    die("Config file .env.infinityfree not found!\n");
}
$config = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 20);
if (!$conn) {
    die("FTP connection failed.\n");
}
if (!ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    die("FTP login failed.\n");
}
ftp_pasv($conn, true);

echo "Connected successfully to " . $config['FTP_HOST'] . "\n";
echo "Current dir: " . ftp_pwd($conn) . "\n\n";

$baseRemote = '/signedtest.site.je/htdocs';
$files = [
    'config/db.php',
    'app/Models/MasterlistModel.php',
    'app/Models/ObjectiveMetricsModel.php',
    'app/Views/dashboard/admin.php',
    'app/Views/dashboard/principal.php',
    'app/Views/masterlist/index.php',
    'app/Models/UserModel.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/AdminController.php'
];

foreach ($files as $file) {
    $remotePath = $baseRemote . '/' . $file;
    $size = ftp_size($conn, $remotePath);
    $mtime = ftp_mdtm($conn, $remotePath);
    echo str_pad($file, 35) . " => Size: " . str_pad($size, 8) . " Mtime: " . ($mtime > 0 ? date('Y-m-d H:i:s', $mtime) : 'N/A') . "\n";
}

ftp_close($conn);
