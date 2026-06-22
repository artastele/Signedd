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
        RoleMiddleware::check('transition_readiness.view');

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

        if (!$readiness) {
            $evidence = $this->model->getEvidence((int)$ctx['student_id'], $iepId);
            $suggestedReadinessResult = $this->model->suggestReadiness($evidence);

            $readinessData = [
                'readiness_result' => $suggestedReadinessResult,
                'evidence_summary' => '',
                'teacher_recommendation' => '',
                'status' => 'draft',
                'overall_status' => 'partial',
                'overall_status_overridden' => 0,
                'overall_remarks' => '',
            ];
            
            $readinessId = $this->model->saveReadiness(
                $iepId,
                (int) $ctx['student_id'],
                $this->userId,
                $readinessData,
                (int)$workflow['progress_report']['id'], 
                !empty($workflow['cot']['id']) ? (int)$workflow['cot']['id'] : null
            );

            $defaultGoals = $this->model->getTransitionGoals($iepId, null);
            $goalInputs = [];
            foreach ($defaultGoals as $goal) {
                $goalInputs[] = [
                    'iep_step_id' => $goal['step_id'],
                    'goal_text' => $goal['goal_text'] ?? '',
                    'pdsp_domain' => $goal['pdsp_domain'] ?? '',
                    'suggested_status' => $goal['suggested_status'] ?? 'partial',
                    'final_status' => $goal['final_status'] ?? 'partial',
                    'status_overridden' => 0,
                    'remarks' => '',
                ];
            }

            if ($readinessId && count($goalInputs) > 0) {
                $this->model->saveReadinessGoals($readinessId, $goalInputs);
            }

            $workflow = $this->model->getWorkflow($iepId);
            $readiness = $workflow['readiness'] ?? null;
        }

        $progressReport = $workflow['progress_report'] ?? null;
        $cot = $workflow['cot'] ?? null;
        $progressSnapshot = $this->model->getProgressSnapshot((int)$ctx['student_id']);
        $readinessGoals = $this->model->getTransitionGoals($iepId, $readiness['id'] ?? null);
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

        $readinessData = [
            'readiness_result' => $_POST['readiness_result'] ?? 'For Re-evaluation',
            'evidence_summary' => trim($_POST['evidence_summary'] ?? ''),
            'teacher_recommendation' => trim($_POST['teacher_recommendation'] ?? ''),
            'status' => $_POST['status'] ?? 'draft',
            'overall_status' => $_POST['overall_status'] ?? 'partial',
            'overall_status_overridden' => !empty($_POST['overall_status_overridden']),
            'overall_remarks' => trim($_POST['overall_remarks'] ?? ''),
        ];

        $readinessId = $this->model->saveReadiness(
            $iepId,
            (int) $ctx['student_id'],
            $this->userId,
            $readinessData,
            (int)$workflow['progress_report']['id'], 
            !empty($workflow['cot']['id']) ? (int)$workflow['cot']['id'] : null
        );

        $goalInputs = [];
        foreach ($_POST['goals'] ?? [] as $stepId => $goalData) {
            $goalInputs[] = [
                'iep_step_id' => $stepId,
                'goal_text' => $goalData['goal_text'] ?? '',
                'pdsp_domain' => $goalData['pdsp_domain'] ?? '',
                'suggested_status' => $goalData['suggested_status'] ?? 'partial',
                'final_status' => $goalData['final_status'] ?? 'partial',
                'status_overridden' => !empty($goalData['status_overridden']),
                'remarks' => $goalData['remarks'] ?? '',
            ];
        }

        if ($readinessId && count($goalInputs) > 0) {
            $this->model->saveReadinessGoals($readinessId, $goalInputs);
        }

        if ($readinessId && ($readinessData['status'] === 'finalized')) {
            require_once __DIR__ . '/../Models/NotificationModel.php';
            $notifModel = new NotificationModel();
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT id FROM users
                WHERE role IN ('guidance', 'principal') AND status = 'active'
            ");
            $stmt->execute();
            $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($staff as $user) {
                $notifModel->create(
                    (int)$user['id'],
                    'transition_readiness',
                    'Transition Readiness Finalized',
                    "Transition readiness for student " . ($ctx['student_name'] ?? 'learner') . " has been finalized.",
                    ['iep_id' => $iepId, 'readiness_id' => $readinessId]
                );
            }
        }

        $_SESSION['success'] = 'Transition readiness saved.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/transition-readiness');
        exit;
    }
}
