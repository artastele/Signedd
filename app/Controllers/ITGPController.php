<?php
// Part of: SignED — Process 12 Individual Transition Goal Plan
// Last modified: 2026-06-16
// Part of: SPED LMS — Process 12 Controller

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';

class ITGPController {
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
        RoleMiddleware::check('inclusive_iep.create');

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
        $inclusive = $workflow['inclusive_iep'] ?? null;
        $itgp = $workflow['itgp'] ?? null;
        $readiness = $workflow['readiness'] ?? null;
        $itp = $workflow['itp'] ?? null;
        $progress = $workflow['progress_report'] ?? null;
        require_once __DIR__ . '/../Views/itgp/index.php';
    }

    public function save(string $iepId): void {
        RoleMiddleware::check('inclusive_iep.create');

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        if (empty($workflow['readiness']['id']) || empty($workflow['itp']['id'])) {
            $_SESSION['error'] = 'Transition readiness and ITP are required first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $items = [[
            'goal' => trim($_POST['itgp_goal'] ?? ''),
            'learning_packages' => trim($_POST['learning_packages'] ?? ''),
            'competency_skill' => trim($_POST['competency_skill'] ?? ''),
            'activities' => trim($_POST['activities'] ?? ''),
            'time_frame' => trim($_POST['time_frame'] ?? ''),
            'person_responsible' => trim($_POST['person_responsible'] ?? ''),
            'remarks' => trim($_POST['itgp_remarks'] ?? ''),
            'recommendations' => trim($_POST['itgp_recommendations'] ?? ''),
        ]];

        $this->model->saveInclusiveIepAndItgp(
            (int)$ctx['student_id'],
            $iepId,
            (int)$workflow['readiness']['id'],
            (int)$workflow['itp']['id'],
            $this->userId,
            [
                'generated_summary' => trim($_POST['generated_summary'] ?? ''),
                'progress_remarks' => $workflow['progress_report']['teacher_remarks'] ?? '',
                'cot_recommendations' => $workflow['cot']['recommendations'] ?? '',
                'learner_name' => $ctx['student_name'] ?? '',
                'disability' => $_POST['disability'] ?? '',
                'entry_point' => $_POST['entry_point'] ?? ($workflow['itp']['entry_point'] ?? ''),
                'recommendations' => trim($_POST['itgp_recommendations'] ?? ''),
                'items' => $items,
                'status' => $_POST['status'] ?? 'draft',
            ]
        );

        $_SESSION['success'] = 'Inclusive IEP and ITGP saved.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }
}
