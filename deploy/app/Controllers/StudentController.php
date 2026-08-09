<?php
// DO NOT ALTER WITHOUT APPROVAL — Student Records Management
// Last modified: 2026-06-28
// Part of: SPED LMS — Student Records

require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/EnrollmentModel.php';
require_once __DIR__ . '/../Helpers/CSRFHelper.php';

class StudentController {
    private $studentModel;
    private $enrollmentModel;
    private $basePath;

    public function __construct() {
        $this->studentModel = new StudentModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    private function requireStaff(): void {
        if (!isset($_SESSION['user_id']) || in_array($_SESSION['role'], ['parent', 'user', 'learner'])) {
            $_SESSION['error'] = 'Access denied. Staff only.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }
    }

    public function index() {
        $this->requireStaff();
        $students = $this->studentModel->getAllStudents();
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/index.php';
    }

    public function view($studentId) {
        $this->requireStaff();

        $student = $this->studentModel->findById($studentId);

        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: ' . $this->basePath . '/students');
            exit;
        }

        $enrollments = $this->studentModel->getEnrollmentsByStudentRecordId($studentId);

        $allDocuments = [];
        foreach ($enrollments as $enrollment) {
            $docs = $this->enrollmentModel->getDocuments($enrollment['id']);
            foreach ($docs as $doc) {
                $doc['enrollment_year'] = $enrollment['school_year'];
                $doc['enrollment_type'] = $enrollment['enrollment_type'];
                $allDocuments[] = $doc;
            }
        }

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/view.php';
    }

    public function edit($studentId) {
        $this->requireStaff();

        $student = $this->studentModel->findById($studentId);

        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: ' . $this->basePath . '/students');
            exit;
        }

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/edit.php';
    }

    public function update($studentId) {
        $this->requireStaff();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/students/edit/' . (int)$studentId);
            exit;
        }

        try {
            CSRFHelper::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Security validation failed.';
            header('Location: ' . $this->basePath . '/students/edit/' . (int)$studentId);
            exit;
        }

        $student = $this->studentModel->findById($studentId);
        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: ' . $this->basePath . '/students');
            exit;
        }

        $lrn = trim($_POST['lrn'] ?? '');
        $lrn = $lrn !== '' ? $lrn : null;

        try {
            $this->studentModel->update($studentId, ['lrn' => $lrn]);
            $_SESSION['success'] = 'Student profile updated.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to update student profile.';
        }

        header('Location: ' . $this->basePath . '/students/view/' . (int)$studentId);
        exit;
    }
}
