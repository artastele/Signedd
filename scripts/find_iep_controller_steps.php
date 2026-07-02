<?php
$file = __DIR__ . '/../app/Controllers/IEPController.php';
$lines = file($file);
foreach ($lines as $index => $line) {
    if (stripos($line, 'function') !== false && (stripos($line, 'step') !== false || stripos($line, 'save') !== false)) {
        echo "Line " . ($index + 1) . ": " . trim($line) . "\n";
    }
}
