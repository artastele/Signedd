<?php
$file = __DIR__ . '/../config/schema.sql';
$lines = file($file);
$found = false;
foreach ($lines as $index => $line) {
    if (stripos($line, 'pdsp_domains') !== false) {
        $found = true;
        echo "Found at line " . ($index + 1) . ": " . trim($line) . "\n";
    }
}
if (!$found) {
    echo "pdsp_domains not found in schema.sql\n";
}
