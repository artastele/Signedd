<?php
// Debug OCR Extraction
// This file helps diagnose OCR extraction issues

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>OCR Debug Tool</h1>";
echo "<hr>";

// Step 1: Check Tesseract config
echo "<h2>Step 1: Tesseract Configuration</h2>";
require_once __DIR__ . '/../config/tesseract.php';

echo "<p><strong>TESSERACT_PATH:</strong> " . TESSERACT_PATH . "</p>";
echo "<p><strong>TESSERACT_ENABLED:</strong> " . (TESSERACT_ENABLED ? 'YES ✅' : 'NO ❌') . "</p>";
echo "<p><strong>TESSERACT_LANG:</strong> " . TESSERACT_LANG . "</p>";

// Step 2: Test Tesseract command
echo "<h2>Step 2: Test Tesseract Command</h2>";
$command = '"' . TESSERACT_PATH . '" --version 2>&1';
echo "<p><strong>Command:</strong> <code>$command</code></p>";

$output = [];
$returnCode = 0;
exec($command, $output, $returnCode);

echo "<p><strong>Return Code:</strong> $returnCode</p>";
echo "<p><strong>Output:</strong></p>";
echo "<pre>" . implode("\n", $output) . "</pre>";

// Step 3: Skip test image (GD library not needed for actual OCR)
echo "<h2>Step 3: Test Image Extraction</h2>";
echo "<p>⚠️ Skipped (GD library not enabled - not required for OCR to work)</p>";

require_once __DIR__ . '/../app/Helpers/TesseractHelper.php';

// Step 4: Check uploaded PDSP documents
echo "<h2>Step 4: Check Uploaded PDSP Documents</h2>";

$uploadDir = __DIR__ . '/uploads/pdsp_signed/';
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    $files = array_diff($files, ['.', '..']);
    
    if (empty($files)) {
        echo "<p>⚠️ No uploaded documents found in: $uploadDir</p>";
    } else {
        echo "<p>✅ Found " . count($files) . " uploaded document(s):</p>";
        echo "<ul>";
        foreach ($files as $file) {
            $filepath = $uploadDir . $file;
            $filesize = filesize($filepath);
            $filetype = mime_content_type($filepath);
            echo "<li><strong>$file</strong> - " . number_format($filesize) . " bytes - $filetype</li>";
        }
        echo "</ul>";
        
        // Try to extract from the first file
        $firstFile = $uploadDir . reset($files);
        echo "<h3>Testing OCR on: " . basename($firstFile) . "</h3>";
        
        $result = TesseractHelper::extractText($firstFile);
        echo "<p><strong>Success:</strong> " . ($result['success'] ? 'YES ✅' : 'NO ❌') . "</p>";
        
        if ($result['success']) {
            echo "<p><strong>Extracted Text (first 500 chars):</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($result['text'], 0, 500)) . "</pre>";
            
            // Try parsing
            $domains = TesseractHelper::parsePDSPText($result['text']);
            echo "<p><strong>Parsed Domains:</strong> " . count($domains) . "</p>";
            echo "<pre>" . print_r($domains, true) . "</pre>";
        } else {
            echo "<p><strong>Error:</strong></p>";
            echo "<pre style='color: red;'>" . htmlspecialchars($result['error']) . "</pre>";
        }
    }
} else {
    echo "<p>❌ Upload directory not found: $uploadDir</p>";
}

echo "<hr>";
echo "<p><a href='test_ocr.php'>← Back to OCR Test</a></p>";
?>
