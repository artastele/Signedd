<?php
// Test enrollment submission

session_start();

echo "<h2>Enrollment Submission Test</h2>";

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    die('<p style="color: red;">Not logged in! <a href="../login">Login first</a></p>');
}

echo "<p><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";

if ($_SESSION['role'] !== 'parent') {
    die('<p style="color: red;">You must be logged in as a parent!</p>');
}

// Check database
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

echo "<hr><h3>Current Enrollments in Database</h3>";

$stmt = $db->prepare("SELECT id, first_name, last_name, status, is_draft, submitted_at, created_at FROM enrollment_submissions WHERE parent_id = :parent_id ORDER BY created_at DESC");
$stmt->execute(['parent_id' => $_SESSION['user_id']]);
$enrollments = $stmt->fetchAll();

if (empty($enrollments)) {
    echo "<p style='color: orange;'>No enrollments found for your account.</p>";
    echo "<p><strong>Possible reasons:</strong></p>";
    echo "<ul>";
    echo "<li>You haven't completed the enrollment form yet</li>";
    echo "<li>The form submission failed (check for errors)</li>";
    echo "<li>JavaScript errors prevented submission</li>";
    echo "</ul>";
} else {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Child Name</th><th>Status</th><th>Is Draft?</th><th>Submitted At</th><th>Created At</th>";
    echo "</tr>";
    
    foreach ($enrollments as $e) {
        $isDraft = $e['is_draft'] ? 'YES' : 'NO';
        $rowColor = $e['is_draft'] ? '#fff3cd' : '#d4edda';
        
        echo "<tr style='background: {$rowColor};'>";
        echo "<td>" . $e['id'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) . "</strong></td>";
        echo "<td>" . $e['status'] . "</td>";
        echo "<td>" . $isDraft . "</td>";
        echo "<td>" . ($e['submitted_at'] ?? '<em>Not submitted</em>') . "</td>";
        echo "<td>" . $e['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for drafts
    $drafts = array_filter($enrollments, function($e) { return $e['is_draft']; });
    if (!empty($drafts)) {
        echo "<p style='color: orange; margin-top: 20px;'><strong>⚠️ You have draft enrollments!</strong></p>";
        echo "<p>Drafts are saved but not submitted. You need to complete the form and click 'Submit Enrollment' button.</p>";
    }
    
    // Check for submitted
    $submitted = array_filter($enrollments, function($e) { return !$e['is_draft']; });
    if (!empty($submitted)) {
        echo "<p style='color: green; margin-top: 20px;'><strong>✓ You have submitted enrollments!</strong></p>";
        echo "<p>These should appear on your dashboard.</p>";
    }
}

echo "<hr>";
echo "<h3>Check Documents</h3>";

$stmt = $db->prepare("
    SELECT ed.*, es.first_name, es.last_name 
    FROM enrollment_documents ed
    JOIN enrollment_submissions es ON ed.enrollment_id = es.id
    WHERE es.parent_id = :parent_id
    ORDER BY ed.uploaded_at DESC
");
$stmt->execute(['parent_id' => $_SESSION['user_id']]);
$documents = $stmt->fetchAll();

if (empty($documents)) {
    echo "<p>No documents uploaded yet.</p>";
} else {
    echo "<p><strong>Total documents:</strong> " . count($documents) . "</p>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Enrollment</th><th>Document Type</th><th>Status</th><th>Uploaded</th></tr>";
    foreach ($documents as $doc) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) . "</td>";
        echo "<td>" . $doc['document_type'] . "</td>";
        echo "<td>" . $doc['status'] . "</td>";
        echo "<td>" . $doc['uploaded_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='../dashboard'>← Back to Dashboard</a> | <a href='../enrollment'>Go to Enrollment Form</a></p>";
?>
