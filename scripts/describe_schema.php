<?php
require_once __DIR__ . '/../config/env.php';
$host = env('DB_HOST','localhost');
$dbname = env('DB_NAME','sped_lms');
$user = env('DB_USER','root');
$pass = env('DB_PASS','');
$mysqli = new mysqli($host,$user,$pass,$dbname);
if ($mysqli->connect_errno) { echo "DB connect failed: " . $mysqli->connect_error . "\n"; exit(1);} 

echo "Connected to DB: $dbname@ $host\n\n";

// List tables
$res = $mysqli->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_row()) { $tables[] = $row[0]; }
echo "Tables (" . count($tables) . "):\n";
foreach ($tables as $t) { echo " - $t\n"; }

echo "\nDescribing key tables...\n";
$keys = ['transition_readiness','transition_readiness_goals','users','db_version'];
foreach ($keys as $k) {
    echo "\n=== $k ===\n";
    if (in_array($k,$tables)) {
        $r = $mysqli->query("SHOW CREATE TABLE `$k`");
        if ($r && $row = $r->fetch_assoc()) {
            echo $row['Create Table'] . "\n";
        } else {
            echo "Failed to get create table for $k\n";
        }
    } else {
        echo "$k: MISSING\n";
    }
}

$mysqli->close();
