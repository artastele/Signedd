<?php
// Part of: SignED — Process 12 Individual Transition Goal Plan (Three-Way Workflow)
// Last modified: 2026-06-30

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
        $this->userId   = (int) $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model    = new TransitionWorkflowModel();
        $this->notifications = new NotificationModel();
    }

    // ─────────────────────────────────────────────
    // VIEW
    // ─────────────────────────────────────────────
    public function index(string $iepId = ''): void {
        $allowed = ['sped_teacher','general_teacher','master_teacher','principal','admin'];
        if (!in_array($this->userRole, $allowed, true)) {
            http_response_code(403);
            die('403 Forbidden: You do not have permission to view this page.');
        }

        $iepId = (int) $iepId;
        $ctx   = $this->model->getIepContext($iepId);
        $workflow = $this->model->getWorkflow($iepId);

        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $itp = $workflow['itp'] ?? null;
        if (!$itp || $itp['status'] !== 'finalized') {
            $_SESSION['error'] = 'Individual Transition Plan (Part 5) must be finalized before initiating the Inclusive IEP & ITGP (Part 6).';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $studentId  = (int)$ctx['student_id'];
        $assignment = $workflow['assignment'] ?? null;

        // General Teacher: must be assigned
        if ($this->userRole === 'general_teacher') {
            if (!$assignment || (int)$assignment['general_teacher_id'] !== $this->userId) {
                $_SESSION['error'] = 'You are not assigned as the General Education Teacher for this student.';
                header('Location: ' . $this->basePath . '/iep');
                exit;
            }
        }

        // Fetch reference panel details
        $iepSteps = $this->model->getTransitionGoals($iepId);

        $pdspId = (int)($ctx['pdsp_id'] ?? 0);
        require_once __DIR__ . '/../Models/PDSPModel.php';
        $pdspModel   = new PDSPModel();
        $pdspRecord  = $pdspId ? $pdspModel->findById($pdspId) : null;
        $pdspDomains = $pdspId ? $pdspModel->getDomains($pdspId) : [];
        $pdspSignedDocPath = $pdspRecord['signed_document_path'] ?? null;

        $progressReport = $workflow['progress_report'] ?? null;

        $itpId                       = (int)$itp['id'];
        $itpNarratives               = $this->model->getNarrativeItems($itpId);
        $itpRecommendationsBeginning = $this->model->getRecommendation($itpId, 'beginning_of_sy');
        $itpRecommendationsEnd       = $this->model->getRecommendation($itpId, 'end_of_sy');

        $itpSections = ['strengths' => [], 'interests' => [], 'talents' => [], 'skills' => [], 'needs' => []];
        foreach ($itpNarratives as $narrative) {
            $sec = $narrative['section'];
            if (isset($itpSections[$sec])) {
                $itpSections[$sec][] = $narrative['item_text'];
            }
        }

        $itgp     = $workflow['itgp'] ?? null;
        $comments = [];
        if ($itgp) {
            $comments = $this->model->getItgpComments((int)$itgp['id']);
        }

        $generalTeachers = [];
        if (in_array($this->userRole, ['sped_teacher', 'master_teacher', 'principal', 'admin'], true)) {
            $generalTeachers = $this->model->getApprovedGeneralTeachers();
        }

        $success = $_SESSION['success'] ?? null;
        $error   = $_SESSION['error']   ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath  = $this->basePath;
        $role      = $this->userRole;
        $iep       = $ctx;
        $readiness = $workflow['readiness'] ?? null;

        require_once __DIR__ . '/../Views/itgp/index.php';
    }

    public function inspectionQueue(): void {
        if (!in_array($this->userRole, ['master_teacher', 'admin'], true)) {
            http_response_code(403);
            die('403 Forbidden: You do not have permission to view this page.');
        }

        $itgps = $this->model->getItgpInspectionQueue();

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        require_once __DIR__ . '/../Views/itgp/inspection_queue.php';
    }

    // ─────────────────────────────────────────────
    // STEP 1: General Teacher assigns / assigns self
    // ─────────────────────────────────────────────
    public function assignGeneralTeacher(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'principal', 'admin'], true)) {
            http_response_code(403);
            die('403 Forbidden');
        }

        $iepId = (int)$iepId;
        $ctx   = $this->model->getIepContext($iepId);
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

    // ─────────────────────────────────────────────
    // STEP 1: General Teacher saves/submits draft
    // ─────────────────────────────────────────────
    public function save(string $iepId): void {
        $iepId = (int) $iepId;
        $ctx   = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow   = $this->model->getWorkflow($iepId);
        $itp        = $workflow['itp'] ?? null;
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
        if ($itgp && !in_array($itgp['status'], ['draft'], true)) {
            $_SESSION['error'] = 'This ITGP has already been submitted for review and can no longer be edited.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $goal            = trim($_POST['itgp_goal']     ?? '');
        $entryPoint      = trim($_POST['entry_point']   ?? '');
        $learningPackages = trim($_POST['learning_packages'] ?? '');
        $recommendations  = trim($_POST['itgp_recommendations'] ?? '');
        $submitForReview  = isset($_POST['submit_for_review']);

        // Parse activities
        $activities = [];
        if (!empty($_POST['activities']) && is_array($_POST['activities'])) {
            foreach ($_POST['activities'] as $act) {
                $comp        = trim($act['competency_skill'] ?? '');
                $activityText = trim($act['activities']      ?? '');
                $time        = trim($act['time_frame']       ?? '');
                $person      = trim($act['person_responsible'] ?? '');
                $rem         = trim($act['remarks']          ?? '');
                if ($comp === '' && $activityText === '' && $time === '' && $person === '' && $rem === '') {
                    continue;
                }
                $activities[] = [
                    'competency_skill'    => $comp,
                    'activities'          => $activityText,
                    'time_frame'          => $time,
                    'person_responsible'  => $person,
                    'remarks'             => $rem,
                ];
            }
        }

        $status = $submitForReview ? 'pending_sned_review' : 'draft';

        // Validation before submitting for review
        if ($submitForReview) {
            if ($goal === '') {
                $_SESSION['error'] = 'Transition Goal is required before submitting for SPED Teacher review.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
            if ($entryPoint === '') {
                $_SESSION['error'] = 'Point of Entry is required before submitting for SPED Teacher review.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
            $hasCompleteActivity = false;
            foreach ($activities as $act) {
                if ($act['competency_skill'] !== '' && $act['activities'] !== '' && $act['time_frame'] !== '' && $act['person_responsible'] !== '') {
                    $hasCompleteActivity = true;
                    break;
                }
            }
            if (!$hasCompleteActivity) {
                $_SESSION['error'] = 'At least one complete activity row is required before submitting for SPED Teacher review.';
                header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
                exit;
            }
        }

        $itgpId = $this->model->saveItgp(
            (int)$ctx['student_id'],
            (int)$itp['id'],
            $this->userId,
            [
                'goal'             => $goal,
                'entry_point'      => $entryPoint,
                'learning_packages'=> $learningPackages,
                'recommendations'  => $recommendations,
                'status'           => $status,
                'activities'       => $activities,
            ]
        );

        if ($submitForReview) {
            // Notify SPED Teacher
            $spedTeacherId = (int)$ctx['drafted_by'];
            $this->notifications->create(
                $spedTeacherId,
                'itgp_pending_sned',
                'ITGP Draft Ready for Review',
                'The ITGP for ' . ($ctx['student_name'] ?? 'a learner') . ' has been submitted by the General Teacher and needs your consult remarks.',
                ['iep_id' => $iepId]
            );
            $_SESSION['success'] = 'ITGP draft submitted for SPED Teacher review.';
        } else {
            $_SESSION['success'] = 'ITGP draft saved.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }

    // ─────────────────────────────────────────────
    // STEP 2: SPED Teacher saves remarks → ready for inspection
    // ─────────────────────────────────────────────
    public function saveSnedRemarks(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            http_response_code(403);
            die('403 Forbidden');
        }

        $iepId = (int)$iepId;
        $ctx   = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itgp     = $workflow['itgp'] ?? null;

        if (!$itgp || $itgp['status'] !== 'pending_sned_review') {
            $_SESSION['error'] = 'ITGP is not in a state that allows SPED Teacher remarks.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $remarks = trim($_POST['sned_remarks'] ?? '');

        $ok = $this->model->addItgpSnedRemarks((int)$itgp['id'], $this->userId, $remarks);
        if ($ok) {
            // Notify Master Teacher(s)
            $masterTeachers = $this->model->getActiveUsersByRoles(['master_teacher']);
            foreach ($masterTeachers as $mt) {
                $this->notifications->create(
                    (int)$mt['id'],
                    'itgp_ready_inspection',
                    'ITGP Ready for Inspection',
                    'The ITGP for ' . ($ctx['student_name'] ?? 'a learner') . ' is ready for your inspection and sign-off.',
                    ['iep_id' => $iepId]
                );
            }
            $_SESSION['success'] = 'Consult remarks saved. ITGP is now ready for Master Teacher inspection.';
        } else {
            $_SESSION['error'] = 'Failed to save remarks.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }

    // ─────────────────────────────────────────────
    // STEP 2 (ALT): SPED Teacher sends remarks BACK to General Teacher for revision
    // ─────────────────────────────────────────────
    public function sendBackToGenTeacher(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            http_response_code(403);
            die('403 Forbidden');
        }

        $iepId = (int)$iepId;
        $ctx   = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itgp     = $workflow['itgp'] ?? null;

        if (!$itgp || $itgp['status'] !== 'pending_sned_review') {
            $_SESSION['error'] = 'ITGP must be in pending review state to send back.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $remarks = trim($_POST['sned_remarks'] ?? '');
        if ($remarks === '') {
            $_SESSION['error'] = 'Please write your remarks before sending back to the General Teacher.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        // Save remarks + set status back to draft so Gen Teacher can revise
        $db = $this->model->getDb();
        $stmt = $db->prepare("
            UPDATE itgp_records
            SET sned_remarks = :remarks,
                sned_reviewed_by = :user_id,
                sned_reviewed_at = NOW(),
                gen_teacher_revised = 0,
                status = 'draft'
            WHERE id = :id
        ");
        $ok = $stmt->execute(['remarks' => $remarks, 'user_id' => $this->userId, 'id' => (int)$itgp['id']]);

        if ($ok) {
            // Notify General Teacher
            $assignment = $workflow['assignment'] ?? null;
            if ($assignment) {
                $genTeacherId = (int)$assignment['general_teacher_id'];
                $this->notifications->create(
                    $genTeacherId,
                    'itgp_revision_requested',
                    'ITGP Revision Requested',
                    'The SPED Teacher has reviewed your ITGP draft for ' . ($ctx['student_name'] ?? 'a learner') . ' and has sent it back with remarks. Please revise and resubmit.',
                    ['iep_id' => $iepId]
                );
            }
            $_SESSION['success'] = 'Consult remarks sent to the General Teacher for revision.';
        } else {
            $_SESSION['error'] = 'Failed to send back remarks.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }

    // ─────────────────────────────────────────────
    // STEP 3: Master Teacher inspects & signs
    // ─────────────────────────────────────────────
    public function inspect(string $iepId): void {
        if (!in_array($this->userRole, ['master_teacher', 'admin'], true)) {
            http_response_code(403);
            die('403 Forbidden');
        }

        $iepId = (int)$iepId;
        $ctx   = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itgp     = $workflow['itgp'] ?? null;

        if (!$itgp || $itgp['status'] !== 'ready_for_inspection') {
            $_SESSION['error'] = 'ITGP is not ready for inspection.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $recommendations = trim($_POST['master_recommendations'] ?? '');
        $signatureData   = trim($_POST['master_signature']       ?? '');

        if ($recommendations === '') {
            $_SESSION['error'] = 'Recommendations (Beginning of School Year) are required.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }
        if (empty($signatureData) || !str_starts_with($signatureData, 'data:image/')) {
            $_SESSION['error'] = 'A digital signature is required to inspect.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $ok = $this->model->inspectItgp((int)$itgp['id'], $this->userId, $recommendations, $signatureData);
        if ($ok) {
            // Notify SPED Teacher to finalize
            $spedTeacherId = (int)$ctx['drafted_by'];
            $this->notifications->create(
                $spedTeacherId,
                'itgp_inspected',
                'ITGP Inspected by Master Teacher',
                'The ITGP for ' . ($ctx['student_name'] ?? 'a learner') . ' has been signed by the Master Teacher. Please finalize and lock the record.',
                ['iep_id' => $iepId]
            );
            $_SESSION['success'] = 'ITGP successfully inspected and digitally signed. SPED Teacher has been notified to finalize.';
        } else {
            $_SESSION['error'] = 'Failed to save inspection.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }

    // ─────────────────────────────────────────────
    // STEP 4: SPED Teacher finalizes/locks
    // ─────────────────────────────────────────────
    public function finalize(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            http_response_code(403);
            die('403 Forbidden');
        }

        $iepId = (int)$iepId;
        $ctx   = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itgp     = $workflow['itgp'] ?? null;

        if (!$itgp || $itgp['status'] !== 'inspected') {
            $_SESSION['error'] = 'ITGP must be inspected by the Master Teacher before finalization.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
            exit;
        }

        $ok = $this->model->finalizeItgp((int)$itgp['id'], $this->userId);
        if ($ok) {
            // Notify General Teacher
            $assignment = $workflow['assignment'] ?? null;
            if ($assignment) {
                $genTeacherId = (int)$assignment['general_teacher_id'];
                $this->notifications->create(
                    $genTeacherId,
                    'itgp_finalized',
                    'ITGP Finalized',
                    'The ITGP for ' . ($ctx['student_name'] ?? 'a learner') . ' has been finalized. Class Placement Notice will follow.',
                    ['iep_id' => $iepId]
                );
            }
            // Notify Parent(s) — look up parent linked to student
            $parents = $this->model->getActiveUsersByRoles(['parent']);
            foreach ($parents as $p) {
                $this->notifications->create(
                    (int)$p['id'],
                    'itgp_finalized_parent',
                    'Transition Goal Plan Finalized',
                    'The Individualized Transition Goal Plan (ITGP) for ' . ($ctx['student_name'] ?? 'your child') . ' has been finalized. A Class Placement Notice will be issued.',
                    ['iep_id' => $iepId]
                );
            }
            $_SESSION['success'] = 'ITGP finalized and locked. General Teacher and parents have been notified.';
        } else {
            $_SESSION['error'] = 'Failed to finalize ITGP.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/inclusive-iep-itgp');
        exit;
    }

    // ─────────────────────────────────────────────
    // Discussion comments (optional, remains active)
    // ─────────────────────────────────────────────
    public function addComment(string $iepId): void {
        if (!RoleMiddleware::hasPermission('itgp.comment') && $this->userRole !== 'admin') {
            http_response_code(403);
            die('403 Forbidden');
        }

        $iepId = (int) $iepId;
        $ctx   = $this->model->getIepContext($iepId);
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

        $workflow   = $this->model->getWorkflow($iepId);
        $itp        = $workflow['itp'] ?? null;
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
            $itgpId = $this->model->saveItgp(
                (int)$ctx['student_id'],
                (int)$itp['id'],
                (int)$assignment['general_teacher_id'],
                ['goal' => '', 'entry_point' => '', 'learning_packages' => '', 'recommendations' => '', 'status' => 'draft', 'activities' => []]
            );
        } else {
            $itgpId = (int)$itgp['id'];
        }

        $success = $this->model->addItgpComment($itgpId, $this->userId, $commentText);
        if ($success) {
            $assignedTeacherId = $assignment ? (int)$assignment['general_teacher_id'] : 0;
            $spedTeacherId     = (int)$ctx['drafted_by'];
            $recipientId = ($this->userId === $assignedTeacherId) ? $spedTeacherId : $assignedTeacherId;
            if ($recipientId) {
                $this->notifications->create(
                    $recipientId,
                    'itgp_comment',
                    'New ITGP Comment',
                    ($_SESSION['user_name'] ?? 'Someone') . ' commented on the ITGP for ' . ($ctx['student_name'] ?? 'a learner') . '.',
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
