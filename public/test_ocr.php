<?php
// Test if Tesseract is accessible from PHP

echo "<h2>Tesseract OCR Test</h2>";

// Test 1: Check if tesseract command works
echo "<h3>Test 1: Command Line Test</h3>";
$output = [];
$return = 0;
exec('tesseract --version 2>&1', $output, $return);

echo "<strong>Return Code:</strong> " . $return . "<br>";
echo "<strong>Output:</strong><br><pre>" . implode("\n", $output) . "</pre>";

if ($return === 0) {
    echo "<p style='color: green;'><strong>✅ SUCCESS!</strong> Tesseract is accessible via PATH</p>";
} else {
    echo "<p style='color: red;'><strong>❌ FAILED!</strong> Tesseract not found in PATH</p>";
}

// Test 2: Check common installation paths
echo "<h3>Test 2: Check Installation Paths</h3>";
$paths = [
    'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
    'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✅ Found: <code>$path</code></p>";
        
        // Test this path
        $output2 = [];
        $return2 = 0;
        exec("\"$path\" --version 2>&1", $output2, $return2);
        
        if ($return2 === 0) {
            echo "<p style='color: green;'><strong>✅ This path WORKS!</strong></p>";
            echo "<p><strong>Use this in config/tesseract.php:</strong><br>";
            echo "<code>define('TESSERACT_PATH', '$path');</code></p>";
        }
    } else {
        echo "<p style='color: gray;'>❌ Not found: <code>$path</code></p>";
    }
}

// Test 3: Check config
echo "<h3>Test 3: Check SPED LMS Config</h3>";
require_once __DIR__ . '/../config/tesseract.php';

echo "<strong>TESSERACT_ENABLED:</strong> " . (TESSERACT_ENABLED ? 'true ✅' : 'false ❌') . "<br>";
echo "<strong>TESSERACT_PATH:</strong> <code>" . TESSERACT_PATH . "</code><br>";

if (TESSERACT_ENABLED) {
    echo "<p style='color: green;'><strong>✅ OCR Auto-Fill is READY!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ OCR Auto-Fill is NOT available</strong></p>";
    echo "<p><strong>Solution:</strong> Add Tesseract to PATH or update config/tesseract.php with full path</p>";
}

echo "<hr>";
echo "<p><a href='../'>← Back to SPED LMS</a></p>";
?>
