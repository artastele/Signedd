<?php
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();
echo "=== TEACHERS ===\n";
foreach ($db->query("SELECT id, name, email, role, school_id FROM users WHERE role IN ('sped_teacher', 'admin')") as $u) {
    echo "User #{$u['id']}: {$u['name']} ({$u['role']}), School: {$u['school_id']}\n";
}
echo "\n=== SECTIONS ===\n";
foreach ($db->query("SELECT id, school_id, section_name, grade_level, adviser_teacher_id, assigned_teacher_id FROM sections") as $s) {
    echo "Section #{$s['id']}: {$s['section_name']} ({$s['grade_level']}), Adviser: {$s['adviser_teacher_id']}, Assigned: {$s['assigned_teacher_id']}, School: {$s['school_id']}\n";
}
echo "\n=== TEACHER ASSIGNMENTS ===\n";
foreach ($db->query("SELECT * FROM teacher_assignments") as $ta) {
    echo "Assignment: Teacher #{$ta['teacher_id']}, Section: {$ta['section_name']}, School: {$ta['school_id']}\n";
}
echo "\n=== STUDENT RECORDS ===\n";
foreach ($db->query("SELECT id, student_name, student_id, section_id, section_name, assigned_teacher_id, school_id FROM student_records") as $sr) {
    echo "Student #{$sr['id']}: {$sr['student_name']} (SID: {$sr['student_id']}), SectionID: {$sr['section_id']}, SectionName: {$sr['section_name']}, AssignedTeacher: {$sr['assigned_teacher_id']}, School: {$sr['school_id']}\n";
}
