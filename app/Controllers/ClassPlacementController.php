<?php
// Part of: SignED — Process 13 Class Placement
// Last modified: 2026-06-23

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

    public function index(string $iepId = ''): void {
        // Enforce RBAC permissions
        if (!RoleMiddleware::hasPermission('class_placement.review') && !RoleMiddleware::hasPermission('class_placement.view') && $this->userRole !== 'admin') {
            http_response_code(403);
            die('403 Forbidden: You do not have permission to view this page.');
        }

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        $workflow = $this->model->getWorkflow($iepId);

        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $itgp = $workflow['itgp'] ?? null;
        if (!$itgp || $itgp['status'] !== 'finalized') {
            $_SESSION['error'] = 'Inclusive IEP & ITGP (Process 12) must be finalized before initiating Class Placement Notice (Process 13).';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $studentId = (int)$ctx['student_id'];
        $assignment = $workflow['assignment'] ?? null;

        // General Teacher must be the assigned general teacher for this student to perform review
        if ($this->userRole === 'general_teacher') {
            if (!$assignment || (int)$assignment['general_teacher_id'] !== $this->userId) {
                $_SESSION['error'] = 'You are not assigned as the General Education Teacher for this student.';
                header('Location: ' . $this->basePath . '/iep');
                exit;
            }
        }

        // Parent must be the parent of this student to view
        if ($this->userRole === 'parent') {
            if ((int)($ctx['parent_id'] ?? 0) !== $this->userId && $this->model->getParentIdForStudent($studentId) !== $this->userId) {
                http_response_code(403);
                die('403 Forbidden: You can only view class placement for your own child.');
            }
        }

        // Fetch transition history log
        $placementHistory = $this->model->getPlacementHistory($studentId);

        // Fetch IEP details for summary snapshot
        $iepSteps = $this->model->getTransitionGoals($iepId);

        // Fetch PDSP Details
        $pdspId = (int)($ctx['pdsp_id'] ?? 0);
        require_once __DIR__ . '/../Models/PDSPModel.php';
        $pdspModel = new PDSPModel();
        $pdspDomains = $pdspId ? $pdspModel->getDomains($pdspId) : [];

        // Fetch ITP Details for summary snapshot
        $itp = $workflow['itp'] ?? null;
        $itpRecommendationsBeginning = $itp ? $this->model->getRecommendation((int)$itp['id'], 'beginning_of_sy') : null;
        $itpRecommendationsEnd = $itp ? $this->model->getRecommendation((int)$itp['id'], 'end_of_sy') : null;

        // Fetch ITGP Details for summary snapshot
        $itgpActivities = $itgp['activities'] ?? [];

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        $iep = $ctx;
        $readiness = $workflow['readiness'] ?? null;
        $placement = $workflow['placement'] ?? null;

        require_once __DIR__ . '/../Views/class-placement/index.php';
    }

    public function save(string $iepId): void {
        if (!RoleMiddleware::hasPermission('class_placement.review') && !RoleMiddleware::hasPermission('class_placement.confirm') && $this->userRole !== 'admin') {
            http_response_code(403);
            die('403 Forbidden: You do not have permission to perform this action.');
        }

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itgp = $workflow['itgp'] ?? null;
        if (!$itgp || $itgp['status'] !== 'finalized') {
            $_SESSION['error'] = 'Inclusive IEP & ITGP (Process 12) must be finalized first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/placement-notice');
            exit;
        }

        $assignment = $workflow['assignment'] ?? null;
        if ($this->userRole === 'general_teacher') {
            if (!$assignment || (int)$assignment['general_teacher_id'] !== $this->userId) {
                $_SESSION['error'] = 'Only the assigned General Education Teacher can make placement decisions for this student.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/placement-notice');
                exit;
            }
        }

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['confirmed', 'on_hold'], true)) {
            $_SESSION['error'] = 'Invalid placement decision status.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/placement-notice');
            exit;
        }

        $remarks = trim($_POST['remarks'] ?? '');
        $holdReason = ($status === 'confirmed') ? ($remarks !== '' ? $remarks : null) : trim($_POST['hold_reason'] ?? '');
        if ($status === 'on_hold' && $holdReason === '') {
            $_SESSION['error'] = 'A reason is required to place the student on hold.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/placement-notice');
            exit;
        }

        try {
            $success = $this->model->saveClassPlacement(
                (int)$ctx['student_id'],
                (int)$itgp['id'],
                $this->userId,
                $status,
                $holdReason
            );

            if ($success) {
                $studentName = $ctx['student_name'] ?? 'learner';
                $parentId = $this->model->getParentIdForStudent((int)$ctx['student_id']);
                $spedTeacherId = (int)$ctx['drafted_by'];

                if ($status === 'confirmed') {
                    // Send Parent Notifications
                    if ($parentId) {
                        // In-system
                        $notifMsg = 'Your child ' . $studentName . ' has been recommended for regular class placement.';
                        if ($remarks !== '') {
                            $notifMsg .= ' Remarks: ' . $remarks;
                        }
                        $this->notifications->create(
                            $parentId,
                            'placement_confirmed',
                            'Placement Confirmed',
                            $notifMsg,
                            ['iep_id' => $iepId]
                        );

                        // PHPMailer
                        $parentUser = $this->model->getUser($parentId);
                        if ($parentUser && !empty($parentUser['email'])) {
                            require_once __DIR__ . '/../Helpers/MailHelper.php';
                            $subject = 'Class Placement Recommendation - SignED';
                            $htmlBody = "
                                <h2>Class Placement Notice</h2>
                                <p>Hello " . htmlspecialchars($parentUser['name']) . ",</p>
                                <p>We are pleased to inform you that your child, <strong>" . htmlspecialchars($studentName) . "</strong>, has been recommended for regular class placement (mainstreamed).</p>
                            ";
                            if ($remarks !== '') {
                                $htmlBody .= "<p><strong>Remarks from Teacher:</strong><br>" . nl2br(htmlspecialchars($remarks)) . "</p>";
                            }
                            $htmlBody .= "
                                <p>If you have any questions, please contact the SPED teacher or school administration.</p>
                                <p>Best regards,<br>SignED Team</p>
                            ";
                            MailHelper::sendNotification($parentUser['email'], $parentUser['name'], $subject, $htmlBody);
                        }
                    }

                    // Notify SPED Teacher
                    $this->notifications->create(
                        $spedTeacherId,
                        'placement_confirmed',
                        'Placement Confirmed',
                        'Placement confirmed for ' . $studentName . '.',
                        ['iep_id' => $iepId]
                    );

                    // Notify Principals
                    $principals = $this->model->getActiveUsersByRoles(['principal']);
                    foreach ($principals as $p) {
                        $this->notifications->create(
                            (int)$p['id'],
                            'placement_confirmed',
                            'Placement Confirmed',
                            'Placement confirmed for ' . $studentName . '.',
                            ['iep_id' => $iepId]
                        );
                    }

                    $_SESSION['success'] = 'Regular class placement confirmed and student mainstreamed.';
                } else {
                    // On Hold
                    // Notify SPED Teacher
                    $this->notifications->create(
                        $spedTeacherId,
                        'placement_hold',
                        'Placement Put On Hold',
                        'The placement for ' . $studentName . ' was put on hold. Reason: ' . $holdReason,
                        ['iep_id' => $iepId]
                    );

                    // Notify Principals
                    $principals = $this->model->getActiveUsersByRoles(['principal']);
                    foreach ($principals as $p) {
                        $this->notifications->create(
                            (int)$p['id'],
                            'placement_hold',
                            'Placement Put On Hold',
                            'The placement for ' . $studentName . ' was put on hold. Reason: ' . $holdReason,
                            ['iep_id' => $iepId]
                        );
                    }

                    $_SESSION['success'] = 'Placement decision set to On Hold.';
                }
            } else {
                $_SESSION['error'] = 'Failed to save class placement decision.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/placement-notice');
        exit;
    }
}
