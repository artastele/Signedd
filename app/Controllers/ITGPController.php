<?php
// Part of: SignED — Process 12 Individual Transition Goal Plan
// Last modified: 2026-06-23

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

class ITGPController {
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
        // Enforce RBAC permissions
        if (!RoleMiddleware::hasPermission('itgp.view') && !RoleMiddleware::hasPermission('itgp.manage') && $this->userRole !== 'admin') {
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

        $itp = $workflow['itp'] ?? null;
        if (!$itp || $itp['status'] !== 'finalized') {
            $_SESSION['error'] = 'Individual Transition Plan (Process 11) must be finalized before initiating the Inclusive IEP & ITGP (Process 12).';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $studentId = (int)$ctx['student_id'];
        $assignment = $workflow['assignment'] ?? null;

        // General Teacher must be the assigned general teacher for this student
        if ($this->userRole === 'general_teacher') {
            if (!$assignment || (int)$assignment['general_teacher_id'] !== $this->userId) {
                $_SESSION['error'] = 'You are not assigned as the General Education Teacher for this student.';
                header('Location: ' . $this->basePath . '/iep');
                exit;
            }
        }

        // Fetch reference panel details
        // 1. IEP Details
        $iepSteps = $this->model->getTransitionGoals($iepId);
        
        // 2. PDSP Details
        $pdspId = (int)($ctx['pdsp_id'] ?? 0);
        require_once __DIR__ . '/../Models/PDSPModel.php';
        $pdspModel = new PDSPModel();
        $pdspDomains = $pdspId ? $pdspModel->getDomains($pdspId) : [];

        // 3. ITP Details
        $itpId = (int)$itp['id'];
        $itpNarratives = $this->model->getNarrativeItems($itpId);
        $itpRecommendationsBeginning = $this->model->getRecommendation($itpId, 'beginning_of_sy');
        $itpRecommendationsEnd = $this->model->getRecommendation($itpId, 'end_of_sy');

        // Parse narrative sections
        $itpSections = [
            'strengths' => [],
            'interests' => [],
            'talents' => [],
            'skills' => [],
            'needs' => []
        ];
        foreach ($itpNarratives as $narrative) {
            $sec = $narrative['section'];
            if (isset($itpSections[$sec])) {
                $itpSections[$sec][] = $narrative['item_text'];
            }
        }

        // Fetch comments
        $itgp = $workflow['itgp'] ?? null;
        $comments = [];
        if ($itgp) {
            $comments = $this->model->getItgpComments((int)$itgp['id']);
        }

        // Fetch approved general teachers for SPED/Principal assignment dropdown
        $generalTeachers = [];
        if (in_array($this->userRole, ['sped_teacher', 'master_teacher', 'principal', 'admin'], true)) {
            $generalTeachers = $this->model->getApprovedGeneralTeachers();
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        $iep = $ctx;
        $readiness = $workflow['readiness'] ?? null;
        
        require_once __DIR__ . '/../Views/itgp/index.php';
    }

    public function assignGeneralTeacher(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'principal', 'admin'], true)) {
            http_response_code(403);
            die('403 Forbidden: You do not have permission to assign general teachers.');
        }

        $iepId = (int)$iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $generalTeacherId = (int)($_POST['general_teacher_id'] ?? 0);
        if (!$generalTeacherId) {
            $_SESSION['error'] = 'Please select a General Education Teacher.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $success = $this->model->assignGeneralTeacher((int)$ctx['student_id'], $generalTeacherId, $this->userId);
        if ($success) {
            // Notify the General Teacher
            $this->notifications->create(
                $generalTeacherId,
                'itgp_assigned',
                'Assigned to ITGP',
                'You have been assigned to co-manage the Inclusive IEP & ITGP for ' . ($ctx['student_name'] ?? 'a learner') . '.',
                ['iep_id' => $iepId]
            );
            $_SESSION['success'] = 'General Education Teacher assigned successfully.';
        } else {
            $_SESSION['error'] = 'Failed to assign General Education Teacher.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }

    public function save(string $iepId): void {
        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itp = $workflow['itp'] ?? null;
        if (!$itp || $itp['status'] !== 'finalized') {
            $_SESSION['error'] = 'ITP must be finalized first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $assignment = $workflow['assignment'] ?? null;
        if (!$assignment || (int)$assignment['general_teacher_id'] !== $this->userId) {
            $_SESSION['error'] = 'Only the assigned General Education Teacher can modify this ITGP.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $itgp = $workflow['itgp'] ?? null;
        if ($itgp && $itgp['status'] === 'finalized') {
            $_SESSION['error'] = 'This ITGP has already been finalized and is read-only.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $goal = trim($_POST['itgp_goal'] ?? '');
        $entryPoint = trim($_POST['entry_point'] ?? '');
        $learningPackages = trim($_POST['learning_packages'] ?? '');
        $recommendations = trim($_POST['itgp_recommendations'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        // Parse activities
        $activities = [];
        if (!empty($_POST['activities']) && is_array($_POST['activities'])) {
            foreach ($_POST['activities'] as $act) {
                $comp = trim($act['competency_skill'] ?? '');
                $activityText = trim($act['activities'] ?? '');
                $time = trim($act['time_frame'] ?? '');
                $person = trim($act['person_responsible'] ?? '');
                $rem = trim($act['remarks'] ?? '');

                // Only skip completely empty rows
                if ($comp === '' && $activityText === '' && $time === '' && $person === '' && $rem === '') {
                    continue;
                }

                $activities[] = [
                    'competency_skill' => $comp,
                    'activities' => $activityText,
                    'time_frame' => $time,
                    'person_responsible' => $person,
                    'remarks' => $rem
                ];
            }
        }

        // Validation for finalization
        if ($status === 'finalized') {
            if ($goal === '') {
                $_SESSION['error'] = 'Transition Goal / Target Objective is required to finalize.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
            if ($entryPoint === '') {
                $_SESSION['error'] = 'Point of Entry is required to finalize.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
            if ($recommendations === '') {
                $_SESSION['error'] = 'Educational Recommendations are required to finalize.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
            
            // At least one activity row complete (Competency/Skill + Activities + Time Frame + Person Responsible all filled)
            $hasCompleteActivity = false;
            foreach ($activities as $act) {
                if ($act['competency_skill'] !== '' && 
                    $act['activities'] !== '' && 
                    $act['time_frame'] !== '' && 
                    $act['person_responsible'] !== '') {
                    $hasCompleteActivity = true;
                    break;
                }
            }

            if (!$hasCompleteActivity) {
                $_SESSION['error'] = 'At least one activity row must be complete (Competency/Skill, Activities, Time Frame, and Person Responsible must all be filled) to finalize.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
        }

        // Save ITGP
        $itgpId = $this->model->saveItgp(
            (int)$ctx['student_id'],
            (int)$itp['id'],
            $this->userId,
            [
                'goal' => $goal,
                'entry_point' => $entryPoint,
                'learning_packages' => $learningPackages,
                'recommendations' => $recommendations,
                'status' => $status,
                'activities' => $activities
            ]
        );

        if ($status === 'finalized') {
            // Notify SPED Teacher
            $spedTeacherId = (int)$ctx['drafted_by'];
            $this->notifications->create(
                $spedTeacherId,
                'itgp_finalized',
                'ITGP Finalized',
                'The ITGP for ' . ($ctx['student_name'] ?? 'a learner') . ' has been finalized by General Teacher.',
                ['iep_id' => $iepId]
            );

            // Notify Principals
            $principals = $this->model->getActiveUsersByRoles(['principal']);
            foreach ($principals as $p) {
                $this->notifications->create(
                    (int)$p['id'],
                    'itgp_finalized',
                    'ITGP Finalized',
                    'The ITGP for ' . ($ctx['student_name'] ?? 'a learner') . ' has been finalized by General Teacher.',
                    ['iep_id' => $iepId]
                );
            }

            $_SESSION['success'] = 'Inclusive IEP and ITGP finalized and signed.';
        } else {
            $_SESSION['success'] = 'Inclusive IEP and ITGP draft saved.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }

    public function addComment(string $iepId): void {
        if (!RoleMiddleware::hasPermission('itgp.comment') && $this->userRole !== 'admin') {
            http_response_code(403);
            die('403 Forbidden: You do not have permission to post comments.');
        }

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $commentText = trim($_POST['comment_text'] ?? '');
        if ($commentText === '') {
            $_SESSION['error'] = 'Comment text cannot be empty.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itp = $workflow['itp'] ?? null;
        $assignment = $workflow['assignment'] ?? null;
        if (!$itp) {
            $_SESSION['error'] = 'Individual Transition Plan must exist first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $itgp = $workflow['itgp'] ?? null;
        if (!$itgp) {
            if (!$assignment) {
                $_SESSION['error'] = 'A General Teacher must be assigned before commenting.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
            // Auto-create draft ITGP record to link comment
            $itgpId = $this->model->saveItgp(
                (int)$ctx['student_id'],
                (int)$itp['id'],
                (int)$assignment['general_teacher_id'],
                [
                    'goal' => '',
                    'entry_point' => '',
                    'learning_packages' => '',
                    'recommendations' => '',
                    'status' => 'draft',
                    'activities' => []
                ]
            );
        } else {
            $itgpId = (int)$itgp['id'];
        }

        $success = $this->model->addItgpComment($itgpId, $this->userId, $commentText);
        if ($success) {
            // Notify other party
            $assignedTeacherId = $assignment ? (int)$assignment['general_teacher_id'] : 0;
            $spedTeacherId = (int)$ctx['drafted_by'];
            
            $recipientId = null;
            if ($this->userId === $assignedTeacherId) {
                $recipientId = $spedTeacherId;
            } else {
                $recipientId = $assignedTeacherId;
            }

            if ($recipientId) {
                $this->notifications->create(
                    $recipientId,
                    'itgp_comment',
                    'New ITGP Comment',
                    $_SESSION['user_name'] . ' commented on the ITGP for ' . ($ctx['student_name'] ?? 'a learner') . '.',
                    ['iep_id' => $iepId]
                );
            }
            $_SESSION['success'] = 'Comment posted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to post comment.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }
}
