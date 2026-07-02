<?php
require_once __DIR__ . '/../config/env.php';
$host = env('DB_HOST','localhost');
$dbname = env('DB_NAME','sped_lms');
$user = env('DB_USER','root');
$pass = env('DB_PASS','');
$mysqli = new mysqli($host,$user,$pass,$dbname);
if ($mysqli->connect_errno) { echo "DB connect failed: " . $mysqli->connect_error . "\n"; exit(1);} 
$tables = ['db_version','users','role_documents','transition_readiness','attendance_records'];
foreach ($tables as $t) {
    $res = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($t) . "'");
    echo $t . ': ' . ($res && $res->num_rows ? 'FOUND' : 'MISSING') . "\n";
}
$mysqli->close();
