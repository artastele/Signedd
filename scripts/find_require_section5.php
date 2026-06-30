<?php
$files = [
    __DIR__ . '/../app/Views/iep/form.php',
    __DIR__ . '/../app/Views/iep/form_simplified.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "=== File: " . basename($file) . " ===\n";
        $lines = file($file);
        foreach ($lines as $index => $line) {
            if (stripos($line, 'section_5') !== false || stripos($line, 'steps') !== false) {
                echo "  Line " . ($index + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}
