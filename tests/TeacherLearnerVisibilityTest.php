<?php
// tests/TeacherLearnerVisibilityTest.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/IEPModel.php';

$db = Database::getInstance()->getConnection();
$iepModel = new IEPModel();

echo "Running Teacher Learner Visibility & Authorization Tests...\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $desc, bool $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASS] $desc\n";
        $passCount++;
    } else {
        echo "[FAIL] $desc\n";
        $failCount++;
    }
}

// 1. Teacher #6 is assigned as adviser to Section 3 (Maligaya) and in teacher_assignments to Masipag.
// Learners in Section 3: Student 4, 5, 7, 8, 13, 14, 15, 16, 17 have assigned_teacher_id = 6 or are in Section 3.
$teacher6Students = $iepModel->getEligibleStudents(6, 'sped_teacher');
$t6Ids = array_map(fn($s) => (int)$s['id'], $teacher6Students);
assertTest("Teacher #6 can see assigned section learners", count($t6Ids) > 0);

// Teacher #6 authorization checks
assertTest("Teacher #6 is authorized for student in their section (Student 4)", $iepModel->isStudentAssignedToTeacher(4, 6) === true);
assertTest("Teacher #6 is authorized for student in their section (Student 5)", $iepModel->isStudentAssignedToTeacher(5, 6) === true);

// 2. Teacher with no assigned sections (e.g., ID 99999)
$unassignedStudents = $iepModel->getEligibleStudents(99999, 'sped_teacher');
assertTest("Teacher with no assigned section sees 0 eligible learners", count($unassignedStudents) === 0);
assertTest("Unassigned teacher is denied authorization for Student 4", $iepModel->isStudentAssignedToTeacher(4, 99999) === false);

// 3. Admin role access
$adminStudents = $iepModel->getEligibleStudents(2, 'admin');
assertTest("Admin can see all eligible learners across schools", count($adminStudents) >= count($t6Ids));

// 4. Cross-section isolation:
// Create a temporary mock scenario or test learner if needed, or verify against non-existent student ID
assertTest("Teacher #6 is denied authorization for non-existent student (ID 999999)", $iepModel->isStudentAssignedToTeacher(999999, 6) === false);

echo "\nSummary: $passCount passed, $failCount failed.\n";
if ($failCount > 0) exit(1);
exit(0);
