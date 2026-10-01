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

        // Allow Principal, Admin, and Master Teacher
        RoleMiddleware::checkAny(['principal', 'admin', 'master_teacher', 'sped_teacher']);

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

        // Fetch teachers in same school for adviser assignment
        $db = Database::getInstance()->getConnection();
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
