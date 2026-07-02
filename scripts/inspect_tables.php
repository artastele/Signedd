<?php
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

$tables = [
    'enrollment_submissions',
    'student_records',
    'iep_records',
    'iep_steps',
    'learner_iep',
    'learner_progress',
    'learning_materials',
    'lesson_plans',
    'lesson_assignments',
    'lms_activities',
    'lms_submissions',
    'lms_grades',
    'attendance_records',
    'grade_entries'
];

foreach ($tables as $t) {
    try {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "Table: $t | Rows: $count\n";
    } catch (Exception $e) {
        echo "Table: $t | Error: " . $e->getMessage() . "\n";
    }
}
