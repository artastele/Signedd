<?php
// Part of: SignED — Process 13 Class Placement
// Last modified: 2026-06-16
// Part of: SPED LMS — Process 13 Controller

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

class ClassPlacementController {
    private TransitionWorkflowModel $model;
    private NotificationModel $notifications;
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
        $this->notifications = new NotificationModel();
    }

    public function index(string $iepId): void {
        RoleMiddleware::check('placement_notice.view');

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
        $placement = $workflow['placement'] ?? null;
        $teachers = $this->model->getTeacherAccounts();
        require_once __DIR__ . '/../Views/class-placement/index.php';
    }

    public function save(string $iepId): void {
        RoleMiddleware::check('placement_notice.create');

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        if (empty($workflow['inclusive_iep']['id']) || empty($workflow['itgp']['id']) || empty($workflow['readiness']['id'])) {
            $_SESSION['error'] = 'Inclusive IEP, ITGP, and readiness are required before placement notice.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/placement-notice');
            exit;
        }

        try {
            $placementId = $this->model->savePlacement(
                (int)$ctx['student_id'],
                (int)$workflow['inclusive_iep']['id'],
                (int)$workflow['itgp']['id'],
                (int)$workflow['readiness']['id'],
                $this->userId,
                [
                    'receiving_teacher_id' => (int) ($_POST['receiving_teacher_id'] ?? 0),
                    'target_grade_section' => trim($_POST['target_grade_section'] ?? ''),
                    'effective_date' => $_POST['effective_date'] ?? null,
                    'support_needed' => trim($_POST['support_needed'] ?? ''),
                    'placement_status' => $_POST['placement_status'] ?? 'Draft',
                    'approval_status' => $_POST['approval_status'] ?? 'draft',
                ]
            );

            $teacherId = (int) ($_POST['receiving_teacher_id'] ?? 0);
            if (in_array(($_POST['placement_status'] ?? ''), ['Notice Sent','Placed'], true)) {
                $this->notifications->create(
                    $teacherId,
                    'placement_notice',
                    'Class Placement Notice',
                    ($ctx['student_name'] ?? 'A learner') . ' has a placement notice assigned to you.',
                    ['iep_id' => $iepId, 'placement_notice_id' => $placementId]
                );
                $this->model->markPlacementNotificationSent($placementId, false);
                $parentId = $this->model->getParentIdForStudent((int)$ctx['student_id']);
                if ($parentId) {
                    $this->notifications->create(
                        $parentId,
                        'placement_notice_parent',
                        'Placement Notice Available',
                        'A placement notice is available for ' . ($ctx['student_name'] ?? 'your learner') . '.',
                        ['iep_id' => $iepId, 'placement_notice_id' => $placementId]
                    );
                    $this->model->markPlacementNotificationSent($placementId, true);
                }
            }

            $_SESSION['success'] = 'Placement notice saved.';
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/placement-notice');
        exit;
    }
}
