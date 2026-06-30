<?php
$file = __DIR__ . '/../config/schema.sql';
$lines = file($file);
$found = false;
foreach ($lines as $index => $line) {
    if (stripos($line, 'CREATE TABLE users') !== false || (stripos($line, 'CREATE TABLE IF NOT EXISTS users') !== false)) {
        $found = true;
        echo "Found at line " . ($index + 1) . "\n";
        for ($i = $index; $i < $index + 20 && $i < count($lines); $i++) {
            echo ($i + 1) . ": " . $lines[$i];
        }
        break;
    }
}
