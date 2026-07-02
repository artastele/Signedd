<?php
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

$tables = [
    'student_records',
    'enrollment_submissions',
    'iep_records',
    'lesson_plans',
    'lesson_assignments',
    'lms_activities',
    'lms_submissions',
    'lms_grades'
];

foreach ($tables as $t) {
    echo "=== TABLE: $t ===\n";
    try {
        $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  Field: {$c['Field']} | Type: {$c['Type']} | Null: {$c['Null']}\n";
        }
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
