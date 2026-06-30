<?php
$files = [
    __DIR__ . '/../app/Controllers/IEPImplementationController.php',
    __DIR__ . '/../app/Controllers/LearningController.php',
    __DIR__ . '/../routes/web.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "=== FILE: " . basename($file) . " ===\n";
        $lines = file($file);
        foreach ($lines as $index => $line) {
            if (stripos($line, 'create_activity') !== false || stripos($line, 'addActivity') !== false) {
                echo "  Line " . ($index + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}
