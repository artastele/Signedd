<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 15);
if (!$conn) { echo "FTP connect failed\n"; exit; }
if (!ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) { echo "FTP login failed\n"; exit; }
ftp_pasv($conn, true);

echo "Listing /signedtest.site.je/htdocs/app/Controllers:\n";
$files = ftp_nlist($conn, '/signedtest.site.je/htdocs/app/Controllers');
if ($files) {
    foreach ($files as $f) {
        echo "  " . basename($f) . "\n";
    }
} else {
    echo "  (empty or error)\n";
}

echo "\nChecking /signedtest.site.je/htdocs/app/Views/layouts/sidebar.php:\n";
$temp = fopen('php://temp', 'r+');
if (@ftp_fget($conn, $temp, '/signedtest.site.je/htdocs/app/Views/layouts/sidebar.php', FTP_BINARY)) {
    rewind($temp);
    $sidebar = stream_get_contents($temp);
    if (strpos($sidebar, 'masterlist') !== false) {
        echo "  [FOUND] /masterlist and 'Learner Masterlist & Registry' IS PRESENT on live site!\n";
    } else {
        echo "  [NOT FOUND] /masterlist is NOT yet present in live sidebar.\n";
    }
} else {
    echo "  Could not fetch sidebar.php from live site.\n";
}
fclose($temp);
ftp_close($conn);
