<?php
$file = __DIR__ . '/../app/Controllers/IEPMeetingController.php';
$lines = file($file);
foreach ($lines as $index => $line) {
    if (stripos($line, 'function ') !== false || stripos($line, 'status') !== false || stripos($line, 'sign') !== false) {
        if (stripos($line, 'pdsp') !== false || stripos($line, 'sign') !== false || stripos($line, 'function ') !== false) {
            echo "Line " . ($index + 1) . ": " . trim($line) . "\n";
        }
    }
}
