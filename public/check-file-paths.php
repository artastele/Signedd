<?php
// Check file paths in database

require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Enrollment Documents - File Paths</h2>";
echo "<pre>";

$stmt = $db->query("SELECT id, enrollment_id, document_type, file_path FROM enrollment_documents ORDER BY id DESC LIMIT 10");
$docs = $stmt->fetchAll();

echo "Total documents: " . count($docs) . "\n\n";

foreach ($docs as $doc) {
    echo "ID: {$doc['id']}\n";
    echo "Enrollment: {$doc['enrollment_id']}\n";
    echo "Type: {$doc['document_type']}\n";
    echo "File Path: {$doc['file_path']}\n";
    
    // Check if file exists
    $fullPath = __DIR__ . '/' . ltrim($doc['file_path'], '/');
    $exists = file_exists($fullPath);
    echo "Full Path: $fullPath\n";
    echo "Exists: " . ($exists ? "YES ✓" : "NO ✗") . "\n";
    
    // Check with different path
    $altPath = __DIR__ . '/../' . ltrim($doc['file_path'], '/');
    $altExists = file_exists($altPath);
    echo "Alt Path: $altPath\n";
    echo "Alt Exists: " . ($altExists ? "YES ✓" : "NO ✗") . "\n";
    
    echo "\n---\n\n";
}

echo "</pre>";

echo "<h3>Correct URL Format</h3>";
echo "<p>BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'not defined') . "</p>";
echo "<p>Example correct URL: http://localhost/Sign/public/uploads/enrollment/file.pdf</p>";
?>
