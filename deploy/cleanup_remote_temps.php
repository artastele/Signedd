<?php
$env = parse_ini_file(__DIR__ . '/../.env.infinityfree');
$conn = ftp_connect($env['FTP_HOST'], intval($env['FTP_PORT']), 30);
ftp_login($conn, $env['FTP_USER'], $env['FTP_PASS']);
ftp_pasv($conn, true);
@ftp_delete($conn, '/signedtest.site.je/htdocs/run_v65_migration.php');
@ftp_delete($conn, '/signedtest.site.je/htdocs/seed_live_admin.php');
ftp_close($conn);
echo "Cleaned up remote temporary files\n";
