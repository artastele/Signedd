<?php
$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config = parse_ini_file($envFile);
$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
if ($conn && ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS'])) {
    @ftp_delete($conn, '/signedtest.site.je/htdocs/sync_sections_live.php');
    @ftp_delete($conn, '/signedtest.site.je/htdocs/sync_deployed.php');
    @ftp_delete($conn, '/signedtest.site.je/htdocs/sync_pwd_live.php');
    @ftp_delete($conn, '/signedtest.site.je/htdocs/set_principal_pwd.php');
    @ftp_delete($conn, '/signedtest.site.je/htdocs/check_live_users.php');
    echo "Deleted sync scripts from live server.\n";
    ftp_close($conn);
}
