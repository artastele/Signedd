<?php
// Test script to debug parent dashboard data

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Not logged in. Please login first.');
}

echo "<h2>Parent Dashboard Debug</h2>";
echo "<p><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";
echo "<p><strong>Name:</strong> " . $_SESSION['user_name'] . "</p>";

// Load database
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/EnrollmentModel.php';

$enrollmentModel = new EnrollmentModel();
$userId = $_SESSION['user_id'];

echo "<hr>";
echo "<h3>Testing getEnrollmentsWithStats()</h3>";
$enrollments = $enrollmentModel->getEnrollmentsWithStats($userId);
echo "<p><strong>Number of enrollments found:</strong> " . count($enrollments) . "</p>";

if (!empty($enrollments)) {
    echo "<pre>";
    print_r($enrollments);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>No enrollments found!</p>";
    
    // Check if there are ANY enrollments in the database for this user
    echo "<h4>Checking database directly...</h4>";
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE parent_id = :parent_id");
    $stmt->execute(['parent_id' => $userId]);
    $allEnrollments = $stmt->fetchAll();
    
    echo "<p><strong>Total enrollments (including drafts):</strong> " . count($allEnrollments) . "</p>";
    
    if (!empty($allEnrollments)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Status</th><th>Is Draft</th><th>Submitted At</th></tr>";
        foreach ($allEnrollments as $e) {
            echo "<tr>";
            echo "<td>" . $e['id'] . "</td>";
            echo "<td>" . $e['first_name'] . " " . $e['last_name'] . "</td>";
            echo "<td>" . $e['status'] . "</td>";
            echo "<td>" . ($e['is_draft'] ? 'YES' : 'NO') . "</td>";
            echo "<td>" . ($e['submitted_at'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color: orange;'><strong>Issue:</strong> You have enrollments but they might be drafts or not submitted yet.</p>";
    } else {
        echo "<p style='color: red;'>No enrollments found in database at all for this user!</p>";
    }
}

echo "<hr>";
echo "<h3>Testing getParentStats()</h3>";
$stats = $enrollmentModel->getParentStats($userId);
echo "<pre>";
print_r($stats);
echo "</pre>";

echo "<hr>";
echo "<p><a href='../dashboard'>Back to Dashboard</a></p>";
?>
