<?php
session_start();
require_once __DIR__ . '/../config/db.php';

echo "<h2>Final Enrollment Test</h2>";

$db = Database::getInstance()->getConnection();

// Check current session
echo "<h3>Current Session:</h3>";
echo "<p>User ID: <strong>" . ($_SESSION['user_id'] ?? 'NOT SET') . "</strong></p>";
echo "<p>Role: <strong>" . ($_SESSION['role'] ?? 'NOT SET') . "</strong></p>";

// Show ALL enrollments (not filtered)
echo "<h3>ALL Enrollments in Database:</h3>";
$stmt = $db->query("SELECT id, parent_id, first_name, last_name, is_draft, status, submitted_at, created_at FROM enrollment_submissions ORDER BY id DESC");
$all = $stmt->fetchAll();

if (count($all) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #1e4072; color: white;'>";
    echo "<th>ID</th><th>Parent ID</th><th>Name</th><th>Is Draft</th><th>Status</th><th>Submitted At</th><th>Created At</th>";
    echo "</tr>";
    foreach ($all as $row) {
        $isDraft = $row['is_draft'] ? 'YES' : 'NO';
        $bgColor = $row['is_draft'] ? '#fff3cd' : '#d1ecf1';
        echo "<tr style='background: $bgColor;'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['parent_id']}</td>";
        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
        echo "<td>$isDraft</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['submitted_at']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total: " . count($all) . " enrollment(s)</strong></p>";
} else {
    echo "<p style='color: red;'>No enrollments found in database.</p>";
}

// Show enrollments for current user
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "<h3>Enrollments for Current User (ID: $userId):</h3>";
    
    $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE parent_id = ? AND is_draft = 0 ORDER BY id DESC");
    $stmt->execute([$userId]);
    $userEnrollments = $stmt->fetchAll();
    
    if (count($userEnrollments) > 0) {
        echo "<p style='color: green;'>✓ Found " . count($userEnrollments) . " enrollment(s) for this user</p>";
        foreach ($userEnrollments as $e) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h4>{$e['first_name']} {$e['last_name']}</h4>";
            echo "<p>Status: <strong>{$e['status']}</strong></p>";
            echo "<p>Submitted: {$e['submitted_at']}</p>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ No enrollments found for user ID $userId</p>";
        echo "<p>This means either:</p>";
        echo "<ul>";
        echo "<li>You haven't submitted any enrollments yet</li>";
        echo "<li>OR your enrollments are saved with a different parent_id</li>";
        echo "</ul>";
    }
}

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<p><a href='../enrollment'>Go to Enrollment Form</a></p>";
echo "<p><a href='../enrollment/status'>View Enrollment Status</a></p>";
echo "<p><a href='diagnose-db.php'>Database Diagnostic</a></p>";
?>
