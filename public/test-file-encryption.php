<?php
// Test File Encryption System
session_start();

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!empty($name)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Helpers/FileEncryptionHelper.php';

echo "<h2>File Encryption System Test</h2>";
echo "<hr>";

// Check encryption key
echo "<h3>1. Encryption Key Check</h3>";
$key = getenv('ENCRYPTION_KEY');
if ($key) {
    echo "<p style='color: #3b6d11;'>✅ Encryption key loaded: " . substr($key, 0, 10) . "..." . substr($key, -10) . "</p>";
} else {
    echo "<p style='color: #a01422;'>❌ Encryption key NOT found!</p>";
}

// Check encrypted files
echo "<h3>2. Encrypted Files Check</h3>";
$encryptedDir = __DIR__ . '/uploads/encrypted/';
if (is_dir($encryptedDir)) {
    $files = scandir($encryptedDir);
    $files = array_diff($files, ['.', '..']);
    echo "<p style='color: #3b6d11;'>✅ Encrypted directory exists</p>";
    echo "<p>Files found: <strong>" . count($files) . "</strong></p>";
    echo "<ul>";
    foreach ($files as $file) {
        $size = filesize($encryptedDir . $file);
        echo "<li><code>{$file}</code> - " . number_format($size) . " bytes</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: #a01422;'>❌ Encrypted directory NOT found!</p>";
}

// Check database records
echo "<h3>3. Database Records Check</h3>";
$db = Database::getInstance()->getConnection();

// Enrollment documents
$stmt = $db->query("SELECT id, enrollment_id, document_type, file_path FROM enrollment_documents");
$enrollmentDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p><strong>Enrollment Documents:</strong> " . count($enrollmentDocs) . "</p>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Enrollment ID</th><th>Type</th><th>File Path</th><th>Encrypted?</th></tr>";
foreach ($enrollmentDocs as $doc) {
    $isEncrypted = strpos($doc['file_path'], '/encrypted/') !== false ? '✅ Yes' : '❌ No';
    echo "<tr>";
    echo "<td>{$doc['id']}</td>";
    echo "<td>{$doc['enrollment_id']}</td>";
    echo "<td>{$doc['document_type']}</td>";
    echo "<td><code>" . basename($doc['file_path']) . "</code></td>";
    echo "<td>{$isEncrypted}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br>";

// Role documents
$stmt = $db->query("SELECT id, role_request_id, file_type, file_path FROM role_documents");
$roleDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p><strong>Role Documents:</strong> " . count($roleDocs) . "</p>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Request ID</th><th>Type</th><th>File Path</th><th>Encrypted?</th></tr>";
foreach ($roleDocs as $doc) {
    $isEncrypted = strpos($doc['file_path'], '/encrypted/') !== false ? '✅ Yes' : '❌ No';
    echo "<tr>";
    echo "<td>{$doc['id']}</td>";
    echo "<td>{$doc['role_request_id']}</td>";
    echo "<td>{$doc['file_type']}</td>";
    echo "<td><code>" . basename($doc['file_path']) . "</code></td>";
    echo "<td>{$isEncrypted}</td>";
    echo "</tr>";
}
echo "</table>";

// Test decryption
echo "<h3>4. Decryption Test</h3>";
if (!empty($enrollmentDocs)) {
    $testDoc = $enrollmentDocs[0];
    $filePath = $testDoc['file_path'];
    
    echo "<p>Testing decryption of: <code>{$filePath}</code></p>";
    
    try {
        $decrypted = FileEncryptionHelper::getDecryptedContents($filePath);
        if ($decrypted !== false) {
            $size = strlen($decrypted);
            echo "<p style='color: #3b6d11;'>✅ Decryption successful! Decrypted size: " . number_format($size) . " bytes</p>";
            
            // Check if it's a valid file (PDF or image)
            $header = substr($decrypted, 0, 10);
            if (strpos($header, '%PDF') !== false) {
                echo "<p style='color: #3b6d11;'>✅ Valid PDF file detected</p>";
            } elseif (strpos($header, "\xFF\xD8\xFF") !== false) {
                echo "<p style='color: #3b6d11;'>✅ Valid JPEG image detected</p>";
            } elseif (strpos($header, "\x89PNG") !== false) {
                echo "<p style='color: #3b6d11;'>✅ Valid PNG image detected</p>";
            }
        } else {
            echo "<p style='color: #a01422;'>❌ Decryption failed!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: #a01422;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

// Summary
echo "<hr>";
echo "<h3>Summary</h3>";
echo "<ul>";
echo "<li>Encryption key: " . ($key ? '✅ Loaded' : '❌ Missing') . "</li>";
echo "<li>Encrypted files: " . (isset($files) ? count($files) : 0) . "</li>";
echo "<li>Enrollment documents: " . count($enrollmentDocs) . " (all encrypted: " . (count(array_filter($enrollmentDocs, function($d) { return strpos($d['file_path'], '/encrypted/') !== false; })) == count($enrollmentDocs) ? '✅' : '❌') . ")</li>";
echo "<li>Role documents: " . count($roleDocs) . " (all encrypted: " . (count(array_filter($roleDocs, function($d) { return strpos($d['file_path'], '/encrypted/') !== false; })) == count($roleDocs) ? '✅' : '❌') . ")</li>";
echo "<li>Decryption test: " . (isset($decrypted) && $decrypted !== false ? '✅ Pass' : '❌ Fail') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>File Encryption System Status:</strong> ";
if ($key && isset($files) && count($files) > 0 && isset($decrypted) && $decrypted !== false) {
    echo "<span style='color: #3b6d11; font-size: 1.2em;'>✅ FULLY OPERATIONAL</span>";
} else {
    echo "<span style='color: #a01422; font-size: 1.2em;'>❌ ISSUES DETECTED</span>";
}
echo "</p>";

echo "<p><a href='/'>← Back to Home</a></p>";
