<?php
// Part of: SignED — Process 9 Classroom Observation Controller
// Last modified: 2026-06-17

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/ClassroomObservationModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

class ClassroomObservationController {
    private ClassroomObservationModel $model;
    private NotificationModel $notificationModel;
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
        $this->model = new ClassroomObservationModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * View and manage indicators for a school year
     */
    public function manageIndicators(): void {
        RoleMiddleware::check('observation.manage_indicators');

        $schoolYear = $_GET['school_year'] ?? 'SY 2025-2026';
        $indicators = $this->model->getIndicatorSet($schoolYear);
        $schoolYears = $this->model->getIndicatorSchoolYears();

        if (!in_array('SY 2025-2026', $schoolYears)) {
            $schoolYears[] = 'SY 2025-2026';
            sort($schoolYears);
            $schoolYears = array_reverse($schoolYears);
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/cot/indicators.php';
    }

    /**
     * Save indicators manually submitted from form
     */
    public function saveIndicators(): void {
        RoleMiddleware::check('observation.manage_indicators');

        $schoolYear = trim($_POST['school_year'] ?? '');
        if (empty($schoolYear)) {
            $_SESSION['error'] = 'School Year is required.';
            header('Location: ' . $this->basePath . '/cot/indicators');
            exit;
        }

        $inputTexts = $_POST['indicator_text'] ?? [];
        $inputCodes = $_POST['competency_code'] ?? [];

        $indicators = [];
        for ($i = 0; $i < count($inputTexts); $i++) {
            if (!empty(trim($inputTexts[$i])) && !empty(trim($inputCodes[$i]))) {
                $indicators[] = [
                    'indicator_text' => $inputTexts[$i],
                    'competency_code' => $inputCodes[$i]
                ];
            }
        }

        if (empty($indicators)) {
            $_SESSION['error'] = 'At least one indicator with text and competency code is required.';
            header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($schoolYear));
            exit;
        }

        if ($this->model->saveIndicatorSet($schoolYear, $indicators, $this->userId)) {
            $_SESSION['success'] = 'Indicator set saved successfully.';
        } else {
            $_SESSION['error'] = 'Failed to save indicator set.';
        }

        header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($schoolYear));
        exit;
    }

    /**
     * Auto load default indicators for SY 2025-2026 as per PMES reference doc
     */
    public function loadDefaultIndicators(): void {
        RoleMiddleware::check('observation.manage_indicators');

        $schoolYear = 'SY 2025-2026';
        
        // Exact indicators extracted from the PMES Rating Sheet for Teacher I-III (SY 2025-2026)
        $defaultIndicators = [
            [
                'indicator_text' => 'Apply knowledge of content within and across curriculum teaching areas',
                'competency_code' => '1.1.2'
            ],
            [
                'indicator_text' => 'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills',
                'competency_code' => '1.4.2'
            ],
            [
                'indicator_text' => 'Apply a range of teaching strategies to develop critical and creative thinking, as well as other higher-order thinking skills',
                'competency_code' => '1.5.2'
            ],
            [
                'indicator_text' => 'Manage classroom structure to engage learners, individually or in groups, in meaningful exploration, discovery and hands-on activities within a range of physical learning environments',
                'competency_code' => '2.3.2'
            ],
            [
                'indicator_text' => 'Manage learner behavior constructively by applying positive and non-violent discipline to ensure learning-focused environments',
                'competency_code' => '2.6.2'
            ],
            [
                'indicator_text' => 'Use differentiated, developmentally appropriate learning experiences to address learners\' gender, needs, strengths, interests and experiences',
                'competency_code' => '3.1.2'
            ],
            [
                'indicator_text' => 'Plan, manage and implement developmentally sequenced teaching and learning process to meet curriculum requirements and varied teaching contexts',
                'competency_code' => '4.1.2'
            ],
            [
                'indicator_text' => 'Select, develop, organize and use appropriate teaching and learning resources, including ICT, to address learning goals',
                'competency_code' => '4.5.2'
            ],
            [
                'indicator_text' => 'Design, select, organize and use diagnostic, formative and summative assessment strategies consistent with curriculum requirements',
                'competency_code' => '5.1.2'
            ]
        ];

        if ($this->model->saveIndicatorSet($schoolYear, $defaultIndicators, $this->userId)) {
            $_SESSION['success'] = 'Successfully loaded default PMES indicators for SY 2025-2026!';
        } else {
            $_SESSION['error'] = 'Failed to load default indicators.';
        }

        header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($schoolYear));
        exit;
    }

    /**
     * Show scheduled, in progress, and finalized observations
     */
    public function history(): void {
        // Master Teacher or Admin
        if ($this->userRole !== 'admin') {
            RoleMiddleware::check('observation.schedule');
        }

        $filters = [
            'school_year' => $_GET['school_year'] ?? '',
            'quarter' => $_GET['quarter'] ?? '',
            'observed_teacher_id' => $_GET['observed_teacher_id'] ?? ''
        ];

        $observations = $this->model->getObservationHistory($this->userId, $this->userRole, $filters);
        $teachers = $this->model->getActiveSpedTeachers();
        $schoolYears = $this->model->getIndicatorSchoolYears();

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/cot/history.php';
    }

    /**
     * Show schedule observation form
     */
    public function showScheduleForm(): void {
        RoleMiddleware::check('observation.schedule');

        $teachers = $this->model->getActiveSpedTeachers();
        $schoolYears = $this->model->getIndicatorSchoolYears();

        if (empty($schoolYears)) {
            $schoolYears = ['SY 2025-2026'];
        }

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/cot/schedule.php';
    }

    /**
     * Process scheduling form and send notification
     */
    public function schedule(): void {
        RoleMiddleware::check('observation.schedule');

        $observedTeacherId = (int) ($_POST['observed_teacher_id'] ?? 0);
        $subjectGrade = trim($_POST['subject_grade_level'] ?? '');
        $schoolYear = trim($_POST['school_year'] ?? '');
        $quarter = trim($_POST['quarter'] ?? '');
        $observationNumber = (int) ($_POST['observation_number'] ?? 1);
        $scheduledAt = trim($_POST['scheduled_at'] ?? '');

        if (!$observedTeacherId || empty($subjectGrade) || empty($schoolYear) || empty($quarter) || empty($scheduledAt)) {
            $_SESSION['error'] = 'All fields are required to schedule an observation.';
            header('Location: ' . $this->basePath . '/cot/observations/schedule');
            exit;
        }

        // Schedule in database
        $observationId = $this->model->scheduleObservation([
            'observer_id' => $this->userId,
            'observed_teacher_id' => $observedTeacherId,
            'school_year' => $schoolYear,
            'quarter' => $quarter,
            'observation_number' => $observationNumber,
            'subject_grade_level' => $subjectGrade,
            'scheduled_at' => $scheduledAt
        ]);

        if ($observationId) {
            // Send in-system notification to the observed SPED teacher
            $observerName = $_SESSION['user_name'] ?? 'Master Teacher';
            $formattedDate = date('Y-m-d H:i', strtotime($scheduledAt));

            $this->notificationModel->create(
                $observedTeacherId,
                'observation_schedule',
                'New Observation Scheduled',
                "You have a classroom observation scheduled on {$formattedDate} by {$observerName}",
                ['observation_id' => $observationId]
            );

            $_SESSION['success'] = 'Observation scheduled successfully and notification sent to the teacher.';
            header('Location: ' . $this->basePath . '/cot/observations');
        } else {
            $_SESSION['error'] = 'Failed to schedule observation.';
            header('Location: ' . $this->basePath . '/cot/observations/schedule');
        }
        exit;
    }

    /**
     * Live rating view
     */
    public function rateLive(string $id): void {
        RoleMiddleware::check('observation.rate');
        $id = (int)$id;

        $observation = $this->model->getObservationById($id);
        if (!$observation) {
            $_SESSION['error'] = 'Observation record not found.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        // Ensure observer is the logged in user
        if ($observation['observer_id'] !== $this->userId) {
            $_SESSION['error'] = 'You can only rate observations that you scheduled.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        // Check if already finalized
        if ($observation['status'] === 'finalized') {
            header('Location: ' . $this->basePath . '/cot/observations/' . $id . '/view');
            exit;
        }

        // Pull indicator set
        $indicators = $this->model->getIndicatorSet($observation['school_year']);
        
        // If empty, warn MT that they need to define the indicator set for this SY
        if (empty($indicators)) {
            $_SESSION['error'] = "No indicators defined for {$observation['school_year']}. Please create the indicator set first.";
            header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($observation['school_year']));
            exit;
        }

        // Get current ratings
        $ratingsRaw = $this->model->getObservationRatings($id);
        $ratings = [];
        foreach ($ratingsRaw as $r) {
            $ratings[$r['indicator_id']] = $r['rating'];
        }

        // Check if observation status should be updated to 'in_progress'
        if ($observation['status'] === 'scheduled') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE classroom_observations SET status = 'in_progress' WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $observation['status'] = 'in_progress';
        }

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/cot/rate.php';
    }

    /**
     * AJAX action to save individual rating tap
     */
    public function saveRating(string $id): void {
        header('Content-Type: application/json');
        
        if (!$this->hasPermission('observation.rate')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }

        $observationId = (int)$id;
        $indicatorId = (int)($_POST['indicator_id'] ?? 0);
        $rating = trim($_POST['rating'] ?? '');

        if (!$observationId || !$indicatorId || !in_array($rating, ['2', '3', '4', '5', '6', 'NO'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        // Verify ownership and status
        $observation = $this->model->getObservationById($observationId);
        if (!$observation || $observation['observer_id'] !== $this->userId || $observation['status'] === 'finalized') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized or finalized']);
            exit;
        }

        $success = $this->model->saveRating($observationId, $indicatorId, $rating);
        
        // Return progress info
        $ratingsRaw = $this->model->getObservationRatings($observationId);
        $indicators = $this->model->getIndicatorSet($observation['school_year']);
        $totalIndicators = count($indicators);
        $ratedCount = count($ratingsRaw);

        echo json_encode([
            'success' => $success,
            'rated_count' => $ratedCount,
            'total_indicators' => $totalIndicators
        ]);
        exit;
    }

    /**
     * AJAX action to save comments on blur
     */
    public function saveComments(string $id): void {
        header('Content-Type: application/json');

        if (!$this->hasPermission('observation.rate')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }

        $observationId = (int)$id;
        $comments = $_POST['other_comments'] ?? '';

        // Verify ownership and status
        $observation = $this->model->getObservationById($observationId);
        if (!$observation || $observation['observer_id'] !== $this->userId || $observation['status'] === 'finalized') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized or finalized']);
            exit;
        }

        $success = $this->model->saveComments($observationId, $comments);
        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * Finalize the observation
     */
    public function finalize(string $id): void {
        RoleMiddleware::check('observation.finalize');
        $observationId = (int)$id;

        $observation = $this->model->getObservationById($observationId);
        if (!$observation) {
            $_SESSION['error'] = 'Observation record not found.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        if ($observation['observer_id'] !== $this->userId) {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        if ($observation['status'] === 'finalized') {
            $_SESSION['error'] = 'Observation is already finalized.';
            header('Location: ' . $this->basePath . '/cot/observations/' . $observationId . '/view');
            exit;
        }

        $ratingsRaw = $this->model->getObservationRatings($observationId);
        $indicators = $this->model->getIndicatorSet($observation['school_year']);
        $totalIndicators = count($indicators);
        $ratedCount = count($ratingsRaw);

        // Compute average score. NO = 2, unrated are ignored unless there are none.
        $totalScore = 0;
        $count = 0;
        foreach ($ratingsRaw as $ratingRow) {
            $r = $ratingRow['rating'];
            if ($r === 'NO') {
                $totalScore += 2;
                $count++;
            } elseif (in_array($r, ['2', '3', '4', '5', '6'])) {
                $totalScore += (int)$r;
                $count++;
            }
        }

        $averageScore = $count > 0 ? round($totalScore / $count, 2) : 2.0;

        if ($this->model->finalizeObservation($observationId, $averageScore)) {
            $_SESSION['success'] = 'Observation finalized successfully. Average score: ' . $averageScore;
        } else {
            $_SESSION['error'] = 'Failed to finalize observation.';
        }

        header('Location: ' . $this->basePath . '/cot/observations/' . $observationId . '/view');
        exit;
    }

    /**
     * View finalized observation
     */
    public function view(string $id): void {
        $id = (int)$id;
        $observation = $this->model->getObservationById($id);
        if (!$observation) {
            $_SESSION['error'] = 'Observation record not found.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        // Results viewable only by observer (Master Teacher) or Admin
        if ($this->userRole !== 'admin' && $observation['observer_id'] !== $this->userId) {
            $_SESSION['error'] = 'Unauthorized. You do not have permission to view this observation.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $indicators = $this->model->getIndicatorSet($observation['school_year']);
        $ratingsRaw = $this->model->getObservationRatings($id);
        
        $ratings = [];
        foreach ($ratingsRaw as $r) {
            $ratings[$r['indicator_id']] = $r['rating'];
        }

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/cot/view.php';
    }

    /**
     * Check permission inline helper
     */
    private function hasPermission(string $permission): bool {
        return RoleMiddleware::hasPermission($permission);
    }
}
