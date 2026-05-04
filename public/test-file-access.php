<?php
// Test file access

$testFile = 'uploads/enrollment/psa_birth_cert_1_1777888078_69f86b4e80cc3.pdf';

echo "<h2>File Access Test</h2>";
echo "<pre>";

echo "Test File: $testFile\n\n";

// Test 1: Check if file exists
$fullPath = __DIR__ . '/' . $testFile;
echo "Full Path: $fullPath\n";
echo "File Exists: " . (file_exists($fullPath) ? "YES ✓" : "NO ✗") . "\n\n";

// Test 2: Check file permissions
if (file_exists($fullPath)) {
    echo "File Size: " . filesize($fullPath) . " bytes\n";
    echo "Is Readable: " . (is_readable($fullPath) ? "YES ✓" : "NO ✗") . "\n";
    echo "File Type: " . mime_content_type($fullPath) . "\n\n";
}

// Test 3: Generate correct URLs
$basePath = '/Sign/public';
echo "Base Path: $basePath\n";
echo "Correct URL: $basePath/$testFile\n";
echo "Full URL: http://localhost$basePath/$testFile\n\n";

// Test 4: List all files in uploads/enrollment
echo "Files in uploads/enrollment:\n";
$files = glob(__DIR__ . '/uploads/enrollment/*');
foreach ($files as $file) {
    $filename = basename($file);
    $relPath = 'uploads/enrollment/' . $filename;
    $url = $basePath . '/' . $relPath;
    echo "  - $filename\n";
    echo "    URL: http://localhost$url\n";
}

echo "</pre>";

echo "<h3>Test Links</h3>";
foreach ($files as $file) {
    $filename = basename($file);
    $relPath = 'uploads/enrollment/' . $filename;
    $url = $basePath . '/' . $relPath;
    echo "<p><a href='$url' target='_blank'>$filename</a></p>";
}
?>
