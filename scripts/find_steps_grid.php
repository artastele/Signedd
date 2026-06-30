<?php
$file = __DIR__ . '/../app/Views/iep/partials/iep_form_section_5_steps.php';
$lines = file($file);
foreach ($lines as $index => $line) {
    if (stripos($line, '<input') !== false || stripos($line, '<select') !== false || stripos($line, 'strategies') !== false || stripos($line, 'objective') !== false) {
        echo "Line " . ($index + 1) . ": " . trim($line) . "\n";
    }
}
