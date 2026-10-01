<?php
$env = parse_ini_file(__DIR__ . '/../.env.infinityfree');
$conn = ftp_connect($env['FTP_HOST'], intval($env['FTP_PORT']), 30);
ftp_login($conn, $env['FTP_USER'], $env['FTP_PASS']);
ftp_pasv($conn, true);

if (ftp_put($conn, '/signedtest.site.je/htdocs/app/Models/MasterlistModel.php', __DIR__ . '/../app/Models/MasterlistModel.php', FTP_BINARY)) {
    echo "Successfully uploaded fixed MasterlistModel.php to live server\n";
} else {
    echo "Failed to upload MasterlistModel.php\n";
}
ftp_close($conn);
