<?php
// Part of: SignED — Process 9 Classroom Observation
// Last modified: 2026-06-16
// Part of: SPED LMS — Process 9 Controller

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';

class ObservationController {
    private TransitionWorkflowModel $model;
    private int $userId;
    private string $userRole;
    private string $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
        $this->userId = (int) $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model = new TransitionWorkflowModel();
    }

    public function index(string $iepId): void {
        RoleMiddleware::check('observation.conduct');

        $iepId = (int) $iepId;
        $workflow = $this->model->getWorkflow($iepId);
        $ctx = $this->model->getIepContext($iepId);

        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        $iep = $ctx;
        $cot = $workflow['cot'] ?? null;
        $teachers = $this->model->getTeacherAccounts();
        require_once __DIR__ . '/../Views/observations/index.php';
    }

    public function save(string $iepId): void {
        RoleMiddleware::check('cot.create');

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $studentId = (int) $ctx['student_id'];
        $ratings = [
            'planning' => $_POST['rating_planning'] ?? '',
            'environment' => $_POST['rating_environment'] ?? '',
            'instruction' => $_POST['rating_instruction'] ?? '',
            'assessment' => $_POST['rating_assessment'] ?? '',
        ];

        $this->model->upsertCot($iepId, $studentId, $this->userId, [
            'observed_teacher_id' => (int) ($_POST['observed_teacher_id'] ?? 0),
            'school_year' => $_POST['school_year'] ?? ($ctx['school_year'] ?? ''),
            'quarter' => $_POST['quarter'] ?? '',
            'observation_date' => $_POST['observation_date'] ?? null,
            'ratings' => $ratings,
            'strengths' => trim($_POST['strengths'] ?? ''),
            'recommendations' => trim($_POST['recommendations'] ?? ''),
            'status' => $_POST['status'] ?? 'draft',
        ]);

        $_SESSION['success'] = 'COT observation saved.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/observation-management/cot-observations');
        exit;
    }
}
