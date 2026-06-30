<?php
$file = __DIR__ . '/../app/Views/iep/partials/iep_form_section_5_steps.php';
$lines = file($file);
foreach ($lines as $index => $line) {
    if (stripos($line, '<script') !== false) {
        echo "Line " . ($index + 1) . ": " . trim($line) . "\n";
    }
}
