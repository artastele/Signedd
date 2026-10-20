<?php
// Part of: SignED — Stage 2 Section Controller (Principal / Admin)
// Last modified: 2026-08-21

require_once __DIR__ . '/../Models/SectionModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';

class SectionController {
    private $sectionModel;
    private $userModel;
    private $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Allow Principal, Master Teacher, and SPED Teacher (Admin cannot view school section management)
        RoleMiddleware::checkAny(['principal', 'master_teacher', 'sped_teacher']);

        $this->sectionModel = new SectionModel();
        $this->userModel = new UserModel();

        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * List all sections in school
     */
    public function index() {
        $userRole = $_SESSION['role'] ?? 'user';
        $userId = $_SESSION['user_id'] ?? 0;
        $schoolId = $_SESSION['school_id'] ?? null;

        $db = Database::getInstance()->getConnection();

        // Self-heal: link imported learners where section_id is null but section_name matches a section
        try {
            $db->exec("
                UPDATE student_records sr
                JOIN sections s ON (
                    (sr.school_id = s.school_id OR sr.school_id IS NULL OR s.school_id IS NULL)
                    AND LOWER(TRIM(sr.section_name)) = LOWER(TRIM(s.section_name))
                )
                SET sr.section_id = s.id,
                    sr.school_id = COALESCE(sr.school_id, s.school_id)
                WHERE sr.section_id IS NULL OR sr.section_id = 0
            ");
            // Also link section adviser to assigned_teacher_id if unassigned
            $db->exec("
                UPDATE student_records sr
                JOIN sections s ON sr.section_id = s.id
                SET sr.assigned_teacher_id = s.adviser_teacher_id
                WHERE (sr.assigned_teacher_id IS NULL OR sr.assigned_teacher_id = 0)
                  AND s.adviser_teacher_id IS NOT NULL
            ");
            // Sync current_count on sections
            $db->exec("
                UPDATE sections s
                SET current_count = (
                    SELECT COUNT(*) 
                    FROM student_records sr 
                    WHERE (sr.section_id = s.id 
                           OR (sr.section_id IS NULL AND LOWER(TRIM(sr.section_name)) = LOWER(TRIM(s.section_name)))
                          )
                      AND (s.school_id IS NULL OR sr.school_id = s.school_id OR sr.school_id IS NULL)
                )
            ");
        } catch (\Throwable $syncErr) {
            error_log('SectionController sync error: ' . $syncErr->getMessage());
        }

        // Fetch teachers in same school for adviser assignment
        $stmt = $db->prepare("
            SELECT id, name, email 
            FROM users 
            WHERE role IN ('sped_teacher', 'master_teacher', 'teacher') 
              AND status = 'active'
              AND (school_id = :school_id OR :school_id_null IS NULL)
            ORDER BY name ASC
        ");
        $stmt->execute(['school_id' => $schoolId, 'school_id_null' => $schoolId]);
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sections = $this->sectionModel->getSectionsBySchool($schoolId);
        foreach ($sections as &$sec) {
            $sec['students'] = $this->sectionModel->getStudentsBySection((int)$sec['id'], $sec['section_name'] ?? '');
        }
        unset($sec);

        $success = $_SESSION['flash_success'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $basePath = $this->basePath;
        require __DIR__ . '/../Views/sections/index.php';
    }

    /**
     * Create section
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/sections');
            exit;
        }

        $schoolId = $_SESSION['school_id'] ?? 1;
        $sectionName = trim($_POST['section_name'] ?? '');
        $gradeLevel = trim($_POST['grade_level'] ?? 'SPED');
        $roomNumber = trim($_POST['room_number'] ?? '');
        $maxCapacity = max(1, (int)($_POST['max_capacity'] ?? 15));
        $adviserId = !empty($_POST['adviser_teacher_id']) ? (int)$_POST['adviser_teacher_id'] : null;

        if (empty($sectionName)) {
            $_SESSION['flash_error'] = 'Section name is required.';
            header('Location: ' . $this->basePath . '/sections');
            exit;
        }

        $this->sectionModel->createSection([
            'school_id'          => $schoolId,
            'section_name'       => $sectionName,
            'grade_level'        => $gradeLevel,
            'room_number'        => $roomNumber,
            'max_capacity'       => $maxCapacity,
            'adviser_teacher_id' => $adviserId,
            'school_year'        => '2026-2027'
        ]);

        if ($adviserId) {
            try {
                require_once __DIR__ . '/../Models/TeacherAssignmentModel.php';
                $taModel = new TeacherAssignmentModel();
                $taModel->assignTeacher(
                    $schoolId,
                    $adviserId,
                    $gradeLevel,
                    $sectionName,
                    'Main Building',
                    $roomNumber,
                    '',
                    $_SESSION['user_id'] ?? $adviserId
                );
            } catch (\Throwable $taErr) {
                error_log('SectionController sync to teacher_assignments error: ' . $taErr->getMessage());
            }
        }

        $_SESSION['flash_success'] = "Section '{$sectionName}' created successfully!";
        header('Location: ' . $this->basePath . '/sections');
        exit;
    }

    /**
     * Update section
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/sections');
            exit;
        }

        $id = (int)$id;
        $sectionName = trim($_POST['section_name'] ?? '');
        $gradeLevel = trim($_POST['grade_level'] ?? 'SPED');
        $roomNumber = trim($_POST['room_number'] ?? '');
        $maxCapacity = max(1, (int)($_POST['max_capacity'] ?? 15));
        $adviserId = !empty($_POST['adviser_teacher_id']) ? (int)$_POST['adviser_teacher_id'] : null;

        if (empty($sectionName)) {
            $_SESSION['flash_error'] = 'Section name is required.';
            header('Location: ' . $this->basePath . '/sections');
            exit;
        }

        $this->sectionModel->updateSection($id, [
            'section_name'       => $sectionName,
            'grade_level'        => $gradeLevel,
            'room_number'        => $roomNumber,
            'max_capacity'       => $maxCapacity,
            'adviser_teacher_id' => $adviserId,
            'school_year'        => '2026-2027'
        ]);

        if ($adviserId) {
            $schoolId = $_SESSION['school_id'] ?? 1;
            try {
                require_once __DIR__ . '/../Models/TeacherAssignmentModel.php';
                $taModel = new TeacherAssignmentModel();
                $taModel->assignTeacher(
                    $schoolId,
                    $adviserId,
                    $gradeLevel,
                    $sectionName,
                    'Main Building',
                    $roomNumber,
                    '',
                    $_SESSION['user_id'] ?? $adviserId
                );
            } catch (\Throwable $taErr) {
                error_log('SectionController sync to teacher_assignments error: ' . $taErr->getMessage());
            }
        }

        $_SESSION['flash_success'] = "Section updated successfully!";
        header('Location: ' . $this->basePath . '/sections');
        exit;
    }

    /**
     * Delete / archive section
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/sections');
            exit;
        }

        $id = (int)$id;
        $this->sectionModel->deleteSection($id);
        $_SESSION['flash_success'] = "Section archived successfully.";
        header('Location: ' . $this->basePath . '/sections');
        exit;
    }
}
