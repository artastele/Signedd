<?php
// Part of: SignED - Unified Transition + IEP Workflow

require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

class TransitionWorkflowController {
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
        $this->userId = (int)$_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model = new TransitionWorkflowModel();
        $this->notifications = new NotificationModel();
    }

    public function workflow(string $iepId): void {
        $this->redirect((int)$iepId, '');
    }

    public function grades(string $iepId): void {
        $this->redirect((int)$iepId, 'progress-report');
    }

    public function attendance(string $iepId): void {
        $this->redirect((int)$iepId, 'progress-report');
    }

    public function progressReports(string $iepId): void {
        $this->redirect((int)$iepId, 'progress-report');
    }

    public function cotObservations(string $iepId): void {
        $this->redirect((int)$iepId, 'cot-observation');
    }

    public function readinessModule(string $iepId): void {
        $this->redirect((int)$iepId, 'transition-readiness');
    }

    public function itpModule(string $iepId): void {
        $this->redirect((int)$iepId, 'individual-transition-plan');
    }

    public function iegpModule(string $iepId): void {
        $this->redirect((int)$iepId, 'inclusive-iep-itgp');
    }

    public function placementRecommendation(string $iepId): void {
        $this->redirect((int)$iepId, 'inclusive-iep-itgp');
    }

    public function placementNotices(string $iepId): void {
        $this->redirect((int)$iepId, 'placement-notice');
    }

    public function progressReport(string $iepId): void {
        $this->redirect((int)$iepId, 'progress-report');
    }

    public function cot(string $iepId): void {
        $this->redirect((int)$iepId, 'cot-observation');
    }

    public function readiness(string $iepId): void {
        $this->redirect((int)$iepId, 'transition-readiness');
    }

    public function itp(string $iepId): void {
        $this->redirect((int)$iepId, 'individual-transition-plan');
    }

    public function inclusiveIepItgp(string $iepId): void {
        $this->redirect((int)$iepId, 'inclusive-iep-itgp');
    }

    public function placementNotice(string $iepId): void {
        $this->redirect((int)$iepId, 'placement-notice');
    }

    private function show(int $iepId, string $section): void {
        $this->redirect($iepId, $section);
    }

    public function saveProgressReport(string $iepId): void {
        $ctx = $this->loadContextOrRedirect((int)$iepId);
        $this->requireRole(['sped_teacher','master_teacher','admin']);
        $ratings = [
            'academic' => $_POST['rating_academic'] ?? '',
            'behavior' => $_POST['rating_behavior'] ?? '',
            'communication' => $_POST['rating_communication'] ?? '',
            'social' => $_POST['rating_social'] ?? '',
        ];

        $snapshot = $this->model->getProgressSnapshot((int)$ctx['student_id']);
        $autoSummary = sprintf(
            "Learner submissions: %d | Activities attempted: %d | Completed submissions: %d | Average auto score: %.1f%%",
            (int)($snapshot['submissions'] ?? 0),
            (int)($snapshot['activities_attempted'] ?? 0),
            (int)($snapshot['completed_submissions'] ?? 0),
            (float)($snapshot['avg_auto_score'] ?? 0)
        );

        $manualSummary = trim((string)($_POST['progress_summary'] ?? ''));
        $progressSummary = $manualSummary !== '' ? $manualSummary . "\n\n" . $autoSummary : $autoSummary;

        $this->model->upsertProgressReport((int)$iepId, (int)$ctx['student_id'], $this->userId, [
            'school_year' => $_POST['school_year'] ?? ($ctx['school_year'] ?? ''),
            'quarter' => $_POST['quarter'] ?? '',
            'attendance_summary' => trim($_POST['attendance_summary'] ?? ''),
            'progress_summary' => $progressSummary,
            'teacher_remarks' => trim($_POST['teacher_remarks'] ?? ''),
            'ratings' => $ratings,
            'status' => $_POST['status'] ?? 'draft',
        ]);
        $_SESSION['success'] = 'Progress report saved and linked to the IEP.';
        $this->redirect((int)$iepId, 'progress-report');
    }

    public function saveCot(string $iepId): void {
        $ctx = $this->loadContextOrRedirect((int)$iepId);
        $this->requireRole(['master_teacher','admin']);
        $observedTeacherId = (int)($_POST['observed_teacher_id'] ?? 0);
        if ($observedTeacherId <= 0) {
            $_SESSION['error'] = 'Select the observed teacher.';
            $this->redirect((int)$iepId, 'cot');
        }
        $cotId = $this->model->upsertCot((int)$iepId, (int)$ctx['student_id'], $this->userId, [
            'observed_teacher_id' => $observedTeacherId,
            'school_year' => $_POST['school_year'] ?? ($ctx['school_year'] ?? ''),
            'quarter' => $_POST['quarter'] ?? '',
            'observation_date' => $_POST['observation_date'] ?? null,
            'ratings' => [
                'planning' => $_POST['rating_planning'] ?? '',
                'environment' => $_POST['rating_environment'] ?? '',
                'instruction' => $_POST['rating_instruction'] ?? '',
                'assessment' => $_POST['rating_assessment'] ?? '',
            ],
            'strengths' => trim($_POST['strengths'] ?? ''),
            'recommendations' => trim($_POST['recommendations'] ?? ''),
            'status' => $_POST['status'] ?? 'draft',
        ]);
        if (($_POST['status'] ?? '') === 'finalized') {
            $this->notifications->create(
                $observedTeacherId,
                'cot_observation',
                'COT Observation Completed',
                'A COT observation has been recorded for ' . ($ctx['student_name'] ?? 'a learner') . '.',
                ['iep_id' => (int)$iepId, 'cot_id' => $cotId]
            );
            $this->model->markCotNotificationSent($cotId);
        }
        $_SESSION['success'] = 'COT observation saved and linked to the IEP.';
        $this->redirect((int)$iepId, 'cot');
    }

    public function saveReadiness(string $iepId): void {
        $ctx = $this->loadContextOrRedirect((int)$iepId);
        $this->requireRole(['sped_teacher','master_teacher','admin']);
        $workflow = $this->model->getWorkflow((int)$iepId);

        if (empty($workflow['progress_report']['id'])) {
            $_SESSION['error'] = 'Create and save a progress report before transition readiness.';
            $this->redirect((int)$iepId, 'transition-readiness');
        }

        $status = $_POST['status'] ?? 'draft';
        if ($status === 'finalized' && (($workflow['progress_report']['status'] ?? 'draft') !== 'finalized')) {
            $_SESSION['error'] = 'Finalize the progress report before marking readiness as finalized.';
            $this->redirect((int)$iepId, 'transition-readiness');
        }

        $readinessId = $this->model->saveReadiness((int)$iepId, (int)$ctx['student_id'], $this->userId, [
            'readiness_result' => $_POST['readiness_result'] ?? 'For Re-evaluation',
            'evidence_summary' => trim($_POST['evidence_summary'] ?? ''),
            'teacher_recommendation' => trim($_POST['teacher_recommendation'] ?? ''),
            'status' => $status,
        ], !empty($workflow['progress_report']['id']) ? (int)$workflow['progress_report']['id'] : null,
           !empty($workflow['cot']['id']) ? (int)$workflow['cot']['id'] : null);

        if ($readinessId && $status === 'finalized') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT id FROM users
                WHERE role IN ('guidance', 'principal') AND status = 'active'
            ");
            $stmt->execute();
            $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($staff as $user) {
                $this->notifications->create(
                    (int)$user['id'],
                    'transition_readiness',
                    'Transition Readiness Finalized',
                    "Transition readiness for student " . ($ctx['student_name'] ?? 'learner') . " has been finalized.",
                    ['iep_id' => (int)$iepId, 'readiness_id' => $readinessId]
                );
            }
        }

        $_SESSION['success'] = 'Transition readiness saved.';
        $this->redirect((int)$iepId, 'transition-readiness');
        exit;
    }

    public function saveItp(string $iepId): void {
        $ctx = $this->loadContextOrRedirect((int)$iepId);
        $this->requireRole(['sped_teacher','master_teacher','admin']);
        $workflow = $this->model->getWorkflow((int)$iepId);
        if (empty($workflow['readiness']['id'])) {
            $_SESSION['error'] = 'Create transition readiness before ITP.';
            $this->redirect((int)$iepId, 'itp');
        }
        $learnerInfo = [
            'learner_name' => $ctx['student_name'] ?? '',
            'lrn' => $ctx['lrn'] ?? '',
            'guardian_name' => $ctx['guardian_name'] ?? '',
            'grade_level' => $ctx['grade_level_to_enroll'] ?? '',
            'school_year' => $ctx['school_year'] ?? '',
        ];
        $this->model->saveItp((int)$iepId, (int)$ctx['student_id'], $this->userId, (int)$workflow['readiness']['id'], [
            'entry_point' => trim($_POST['entry_point'] ?? ''),
            'learner_information' => $learnerInfo,
            'transition_services' => trim($_POST['transition_services'] ?? ''),
            'support_needed' => trim($_POST['support_needed'] ?? ''),
            'team_responsibilities' => trim($_POST['team_responsibilities'] ?? ''),
            'status' => $_POST['status'] ?? 'draft',
        ]);
        $_SESSION['success'] = 'Individual Transition Plan saved.';
        $this->redirect((int)$iepId, 'itp');
    }

    public function saveInclusiveIepItgp(string $iepId): void {
        $ctx = $this->loadContextOrRedirect((int)$iepId);
        $this->requireRole(['sped_teacher','master_teacher','admin']);
        $workflow = $this->model->getWorkflow((int)$iepId);
        if (empty($workflow['readiness']['id']) || empty($workflow['itp']['id'])) {
            $_SESSION['error'] = 'Transition readiness and ITP are required first.';
            $this->redirect((int)$iepId, 'inclusive-iep-itgp');
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
        $this->model->saveInclusiveIepAndItgp((int)$ctx['student_id'], (int)$iepId, (int)$workflow['readiness']['id'], (int)$workflow['itp']['id'], $this->userId, [
            'generated_summary' => trim($_POST['generated_summary'] ?? ''),
            'progress_remarks' => $workflow['progress_report']['teacher_remarks'] ?? '',
            'cot_recommendations' => $workflow['cot']['recommendations'] ?? '',
            'learner_name' => $ctx['student_name'] ?? '',
            'disability' => $_POST['disability'] ?? '',
            'entry_point' => $_POST['entry_point'] ?? ($workflow['itp']['entry_point'] ?? ''),
            'recommendations' => trim($_POST['itgp_recommendations'] ?? ''),
            'items' => $items,
            'status' => $_POST['status'] ?? 'draft',
        ]);
        $_SESSION['success'] = 'Inclusive IEP and ITGP saved from existing IEP data.';
        $this->redirect((int)$iepId, 'inclusive-iep-itgp');
    }

    public function savePlacementNotice(string $iepId): void {
        $ctx = $this->loadContextOrRedirect((int)$iepId);
        $this->requireRole(['master_teacher','admin']);
        $workflow = $this->model->getWorkflow((int)$iepId);
        if (empty($workflow['inclusive_iep']['id']) || empty($workflow['itgp']['id']) || empty($workflow['readiness']['id'])) {
            $_SESSION['error'] = 'Inclusive IEP, ITGP, and readiness are required before placement notice.';
            $this->redirect((int)$iepId, 'placement-notice');
        }
        try {
            $placementId = $this->model->savePlacement(
                (int)$ctx['student_id'],
                (int)$workflow['inclusive_iep']['id'],
                (int)$workflow['itgp']['id'],
                (int)$workflow['readiness']['id'],
                $this->userId,
                [
                    'receiving_teacher_id' => (int)($_POST['receiving_teacher_id'] ?? 0),
                    'target_grade_section' => trim($_POST['target_grade_section'] ?? ''),
                    'effective_date' => $_POST['effective_date'] ?? null,
                    'support_needed' => trim($_POST['support_needed'] ?? ''),
                    'placement_status' => $_POST['placement_status'] ?? 'Draft',
                    'approval_status' => $_POST['approval_status'] ?? 'draft',
                ]
            );
            $teacherId = (int)($_POST['receiving_teacher_id'] ?? 0);
            if (in_array(($_POST['placement_status'] ?? ''), ['Notice Sent','Placed'], true)) {
                $this->notifications->create(
                    $teacherId,
                    'placement_notice',
                    'Regular Class Placement Notice',
                    ($ctx['student_name'] ?? 'A learner') . ' has a placement notice assigned to you.',
                    ['iep_id' => (int)$iepId, 'placement_notice_id' => $placementId]
                );
                $this->model->markPlacementNotificationSent($placementId, false);
                $parentId = $this->model->getParentIdForStudent((int)$ctx['student_id']);
                if ($parentId) {
                    $this->notifications->create(
                        $parentId,
                        'placement_notice_parent',
                        'Placement Notice Available',
                        'A placement notice is available for ' . ($ctx['student_name'] ?? 'your learner') . '.',
                        ['iep_id' => (int)$iepId, 'placement_notice_id' => $placementId]
                    );
                    $this->model->markPlacementNotificationSent($placementId, true);
                }
            }
            $_SESSION['success'] = 'Placement notice saved. Notification targets the selected receiving teacher only.';
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect((int)$iepId, 'placement-notice');
    }

    private function loadContextOrRedirect(int $iepId): array {
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }
        return $ctx;
    }

    private function requireRole(array $roles): void {
        if (!in_array($this->userRole, $roles, true)) {
            $_SESSION['error'] = 'You do not have access to perform this action.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }
    }

    private function redirect(int $iepId, string $section = ''): void {
        $ctx = $this->model->getIepContext($iepId);
        $studentId = $ctx['student_id'] ?? null;
        $base = $this->basePath;

        switch ($section) {
            case 'progress-report':
                if ($studentId) {
                    header('Location: ' . $base . '/progress-reports/' . intval($studentId));
                } else {
                    header('Location: ' . $base . '/progress-reports');
                }
                break;
            case 'cot':
            case 'cot-observation':
                header('Location: ' . $base . '/cot/observations');
                break;
            case 'readiness':
                header('Location: ' . $base . '/iep/' . $iepId . '/transition-readiness');
                break;
            case 'itp':
                header('Location: ' . $base . '/iep/' . $iepId . '/individual-transition-plan');
                break;
            case 'inclusive':
                header('Location: ' . $base . '/iep/' . $iepId . '/inclusive-iep-itgp');
                break;
            case 'placement':
                header('Location: ' . $base . '/iep/' . $iepId . '/placement-notice');
                break;
            default:
                header('Location: ' . $base . '/iep');
        }
        exit;
    }
}
