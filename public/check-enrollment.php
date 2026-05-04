<?php
require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->query("SELECT id, parent_id, first_name, last_name, is_draft, status, submitted_at FROM enrollment_submissions ORDER BY id DESC LIMIT 5");
$enrollments = $stmt->fetchAll();

echo "<h2>Recent Enrollments:</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Parent ID</th><th>Name</th><th>Is Draft</th><th>Status</th><th>Submitted At</th></tr>";

foreach ($enrollments as $e) {
    echo "<tr>";
    echo "<td>{$e['id']}</td>";
    echo "<td>{$e['parent_id']}</td>";
    echo "<td>{$e['first_name']} {$e['last_name']}</td>";
    echo "<td>" . ($e['is_draft'] ? 'YES' : 'NO') . "</td>";
    echo "<td>{$e['status']}</td>";
    echo "<td>{$e['submitted_at']}</td>";
    echo "</tr>";
}

echo "</table>";
?>
