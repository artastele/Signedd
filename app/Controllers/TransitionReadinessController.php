<?php
// Part of: SignED — Process 10 Transition Readiness
// Last modified: 2026-06-16
// Part of: SPED LMS — Process 10 Controller

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';

class TransitionReadinessController {
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
        RoleMiddleware::check('transition_readiness.create');

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        $workflow = $this->model->getWorkflow($iepId);

        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        if (empty($workflow['progress_report']['id']) || ($workflow['progress_report']['status'] ?? '') !== 'finalized') {
            $_SESSION['error'] = 'Transition Readiness cannot be accessed because a finalized Progress Report does not exist for this student.';
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
        $progressReport = $workflow['progress_report'] ?? null;
        $cot = $workflow['cot'] ?? null;
        $progressSnapshot = $this->model->getProgressSnapshot((int)$ctx['student_id']);
        require_once __DIR__ . '/../Views/transition-readiness/index.php';
    }

    public function save(string $iepId): void {
        RoleMiddleware::check('transition_readiness.create');

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        if (empty($workflow['progress_report']['id']) || ($workflow['progress_report']['status'] ?? '') !== 'finalized') {
            $_SESSION['error'] = 'Transition Readiness cannot be created or saved because a finalized Progress Report does not exist for this student.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/transition-readiness');
            exit;
        }

        $this->model->saveReadiness(
            $iepId,
            (int) $ctx['student_id'],
            $this->userId,
            [
                'readiness_result' => $_POST['readiness_result'] ?? 'For Re-evaluation',
                'evidence_summary' => trim($_POST['evidence_summary'] ?? ''),
                'teacher_recommendation' => trim($_POST['teacher_recommendation'] ?? ''),
                'status' => $_POST['status'] ?? 'draft',
            ],
            (int)$workflow['progress_report']['id'], 
            !empty($workflow['cot']['id']) ? (int)$workflow['cot']['id'] : null
        );

        $_SESSION['success'] = 'Transition readiness saved.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/transition-readiness');
        exit;
    }
}
