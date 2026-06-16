<?php
// Part of: SignED — Process 11 Individual Transition Plan
// Last modified: 2026-06-16
// Part of: SPED LMS — Process 11 Controller

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';

class ITPController {
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
        RoleMiddleware::check('itp.view');

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        $workflow = $this->model->getWorkflow($iepId);

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
        $readiness = $workflow['readiness'] ?? null;
        $itp = $workflow['itp'] ?? null;
        $progressSummary = $workflow['progress_report'] ?? [];
        require_once __DIR__ . '/../Views/itp/index.php';
    }

    public function save(string $iepId): void {
        RoleMiddleware::check('itp.create');

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        if (empty($workflow['readiness']['id'])) {
            $_SESSION['error'] = 'Create transition readiness before ITP.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        $learnerInfo = [
            'learner_name' => $ctx['student_name'] ?? '',
            'lrn' => $ctx['lrn'] ?? '',
            'guardian_name' => $ctx['guardian_name'] ?? '',
            'grade_level' => $ctx['grade_level_to_enroll'] ?? '',
            'school_year' => $ctx['school_year'] ?? '',
        ];

        $this->model->saveItp(
            $iepId,
            (int)$ctx['student_id'],
            $this->userId,
            (int)$workflow['readiness']['id'],
            [
                'entry_point' => trim($_POST['entry_point'] ?? ''),
                'learner_information' => $learnerInfo,
                'transition_services' => trim($_POST['transition_services'] ?? ''),
                'support_needed' => trim($_POST['support_needed'] ?? ''),
                'team_responsibilities' => trim($_POST['team_responsibilities'] ?? ''),
                'status' => $_POST['status'] ?? 'draft',
            ]
        );

        $_SESSION['success'] = 'Individual Transition Plan saved.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
        exit;
    }
}
