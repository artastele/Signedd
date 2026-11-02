<?php
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();
foreach (['teacher_assignments', 'sections', 'student_records', 'enrollment_submissions'] as $t) {
    echo "TABLE $t:\n";
    $stmt = $db->query("DESCRIBE $t");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$r['Field']} ({$r['Type']})\n";
    }
}
