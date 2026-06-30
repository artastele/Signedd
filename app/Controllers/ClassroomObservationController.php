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

        $schoolYear = trim($_GET['school_year'] ?? '');
        $schoolYears = $this->model->getIndicatorSchoolYears();

        if ($schoolYear === '' && !empty($schoolYears)) {
            $schoolYear = $schoolYears[0];
        }
        if ($schoolYear === '') {
            $schoolYear = 'SY 2025-2026';
        }

        $indicators = $this->model->getIndicatorSet($schoolYear);

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/cot/indicators.php';
    }

    /**
     * Save indicators manually submitted from form
     */
    public function saveIndicators(): void {
        RoleMiddleware::check('observation.manage_indicators');

        $schoolYear = trim($_POST['school_year'] ?? '');
        $codes = $_POST['competency_code'] ?? [];
        $texts = $_POST['indicator_text'] ?? [];

        $indicators = [];
        for ($i = 0; $i < max(count($codes), count($texts)); $i++) {
            $code = trim($codes[$i] ?? '');
            $text = trim($texts[$i] ?? '');
            if ($code === '' || $text === '') {
                continue;
            }
            $indicators[] = [
                'competency_code' => $code,
                'indicator_text' => $text,
            ];
        }

        if ($schoolYear === '' || empty($indicators)) {
            $_SESSION['error'] = 'Please provide a valid school year and at least one indicator.';
            header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($schoolYear));
            exit;
        }

        if ($this->model->saveIndicatorSet($schoolYear, $indicators, $this->userId)) {
            $_SESSION['success'] = 'Indicator set saved successfully.';
        } else {
            $_SESSION['error'] = 'Failed to save the indicator set. Please try again.';
        }

        header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($schoolYear));
        exit;
    }

    /**
     * Auto load default indicators
     */
    public function loadDefaultIndicators(): void {
        RoleMiddleware::check('observation.manage_indicators');

        $schoolYear = trim($_POST['school_year'] ?? '');
        if ($schoolYear === '') {
            $_SESSION['error'] = 'Please select a school year before loading defaults.';
            header('Location: ' . $this->basePath . '/cot/indicators');
            exit;
        }

        $defaultIndicators = $this->model->getDefaultIndicatorSet($schoolYear);
        if (empty($defaultIndicators)) {
            $_SESSION['error'] = 'No default indicator set is available for ' . htmlspecialchars($schoolYear) . '.';
            header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($schoolYear));
            exit;
        }

        if ($this->model->saveIndicatorSet($schoolYear, $defaultIndicators, $this->userId)) {
            $_SESSION['success'] = 'Standard indicator set loaded successfully.';
        } else {
            $_SESSION['error'] = 'Failed to load default indicators. Please try again.';
        }

        header('Location: ' . $this->basePath . '/cot/indicators?school_year=' . urlencode($schoolYear));
        exit;
    }

    /**
     * Show scheduled, in progress, and finalized observations
     */
    public function history(): void {
        // SPED Teacher, Master Teacher or Admin
        if ($this->userRole !== 'admin' && $this->userRole !== 'sped_teacher') {
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
        $userId = $this->userId;

        if ($this->userRole === 'sped_teacher') {
            $scheduledObservations = array_values(array_filter($observations, fn($o) => in_array($o['status'], ['scheduled', 'in_progress'], true)));
            $pendingSignoff = array_values(array_filter($observations, fn($o) => $o['status'] === 'pending_signoff'));
            $finalizedObservations = array_values(array_filter($observations, fn($o) => $o['status'] === 'finalized'));
            require_once __DIR__ . '/../Views/cot/teacher_dashboard.php';
            return;
        }

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
        $subjectTaught = trim($_POST['subject_taught'] ?? '');
        $gradeLevelTaught = trim($_POST['grade_level_taught'] ?? '');
        $subjectGrade = ($subjectTaught !== '' && $gradeLevelTaught !== '')
            ? $subjectTaught . ' - ' . $gradeLevelTaught
            : '';
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

        // Check if already finalized or pending signoff
        if ($observation['status'] === 'finalized' || $observation['status'] === 'pending_signoff') {
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
            $ratings[$r['indicator_id']] = ($r['rating'] === null) ? 'N/A' : $r['rating'];
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

        if (!$observationId || !$indicatorId || !in_array($rating, ['2', '3', '4', '5', '6', 'NO', 'N/A'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        // Verify ownership and status
        $observation = $this->model->getObservationById($observationId);
        if (!$observation || $observation['observer_id'] !== $this->userId || $observation['status'] === 'finalized' || $observation['status'] === 'pending_signoff') {
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
        if (!$observation || $observation['observer_id'] !== $this->userId || $observation['status'] === 'finalized' || $observation['status'] === 'pending_signoff') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized or finalized']);
            exit;
        }

        $success = $this->model->saveComments($observationId, $comments);
        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * Finalize the observation (Observer finalizes, status -> pending_signoff)
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

        if ($observation['status'] === 'finalized' || $observation['status'] === 'pending_signoff') {
            $_SESSION['error'] = 'Observation is already finalized or pending review.';
            header('Location: ' . $this->basePath . '/cot/observations/' . $observationId . '/view');
            exit;
        }

        if ($this->model->setPendingSignoff($observationId)) {
            // Send in-system notification to the observed SPED teacher
            $this->notificationModel->create(
                (int)$observation['observed_teacher_id'],
                'observation_ready_for_review',
                'Observation Results Ready for Review',
                'Your classroom observation results are ready for your review.',
                ['observation_id' => $observationId]
            );

            $_SESSION['success'] = 'Observation submitted for teacher sign-off successfully!';
        } else {
            $_SESSION['error'] = 'Failed to submit observation for sign-off.';
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

        // Results viewable only by observer (Master Teacher), Admin, or the observed SPED Teacher themselves
        if ($this->userRole !== 'admin' && $observation['observer_id'] !== $this->userId && $observation['observed_teacher_id'] !== $this->userId) {
            $_SESSION['error'] = 'Unauthorized. You do not have permission to view this observation.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $indicators = $this->model->getIndicatorSet($observation['school_year']);
        $ratingsRaw = $this->model->getObservationRatings($id);
        
        $ratings = [];
        foreach ($ratingsRaw as $r) {
            $ratings[$r['indicator_id']] = ($r['rating'] === null) ? 'N/A' : $r['rating'];
        }

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/cot/view.php';
    }

    /**
     * Show SPED Teacher sign-off page
     */
    public function showSignOff(string $id): void {
        $id = (int)$id;
        $observation = $this->model->getObservationById($id);
        if (!$observation) {
            $_SESSION['error'] = 'Observation record not found.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        // Only the observed teacher themselves can access the sign-off page
        if ($observation['observed_teacher_id'] !== $this->userId) {
            $_SESSION['error'] = 'Unauthorized. You can only sign off on your own observations.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        // If already finalized, redirect to view details page
        if ($observation['status'] === 'finalized') {
            header('Location: ' . $this->basePath . '/cot/observations/' . $id . '/view');
            exit;
        }

        // If not in pending_signoff status, redirect to history with error
        if ($observation['status'] !== 'pending_signoff') {
            $_SESSION['error'] = 'This observation is not ready for sign-off.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        $indicators = $this->model->getIndicatorSet($observation['school_year']);
        $ratingsRaw = $this->model->getObservationRatings($id);
        
        $ratings = [];
        foreach ($ratingsRaw as $r) {
            $ratings[$r['indicator_id']] = ($r['rating'] === null) ? 'N/A' : $r['rating'];
        }

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/cot/sign_off.php';
    }

    /**
     * Process SPED Teacher sign-off submission
     */
    public function signOff(string $id): void {
        $id = (int)$id;
        $observation = $this->model->getObservationById($id);
        if (!$observation) {
            $_SESSION['error'] = 'Observation record not found.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        if ($observation['observed_teacher_id'] !== $this->userId) {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        if ($observation['status'] !== 'pending_signoff') {
            $_SESSION['error'] = 'This observation is not ready for sign-off.';
            header('Location: ' . $this->basePath . '/cot/observations');
            exit;
        }

        $ratingsRaw = $this->model->getObservationRatings($id);
        
        // Compute average score. NO = 2, N/A is excluded.
        $totalScore = 0;
        $count = 0;
        foreach ($ratingsRaw as $ratingRow) {
            $r = $ratingRow['rating'];
            if ($r === 'NO') {
                $totalScore += 2;
                $count++;
            } elseif ($r !== null && in_array($r, ['2', '3', '4', '5', '6'])) {
                $totalScore += (int)$r;
                $count++;
            }
        }

        $averageScore = $count > 0 ? round($totalScore / $count, 2) : 2.0;

        $signatureB64 = trim($_POST['signature_data'] ?? '');
        if ($signatureB64 === '') {
            $_SESSION['error'] = 'Please draw your signature before submitting.';
            header('Location: ' . $this->basePath . '/cot/observations/' . $id . '/sign-off');
            exit;
        }

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureB64));
        if ($imageData === false || $imageData === '') {
            $_SESSION['error'] = 'Invalid signature image. Please try again.';
            header('Location: ' . $this->basePath . '/cot/observations/' . $id . '/sign-off');
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/signatures/cot/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'observation_' . $id . '_' . time() . '.png';
        $fullPath = $uploadDir . $filename;
        if (file_put_contents($fullPath, $imageData) === false) {
            $_SESSION['error'] = 'Could not save signature image.';
            header('Location: ' . $this->basePath . '/cot/observations/' . $id . '/sign-off');
            exit;
        }

        $signaturePath = 'uploads/signatures/cot/' . $filename;

        try {
            if ($this->model->finalizeObservation($id, $averageScore, $signaturePath)) {
                $_SESSION['success'] = 'Observation successfully signed and finalized. Average score: ' . $averageScore;
            } else {
                $_SESSION['error'] = 'Failed to sign and finalize observation.';
            }
        } catch (\Throwable $e) {
            error_log('ClassroomObservationController->signOff() ERROR: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to save signature. Please try again or contact support.';
        }

        header('Location: ' . $this->basePath . '/cot/observations/' . $id . '/view');
        exit;
    }

    /**
     * Check permission inline helper
     */
    private function hasPermission(string $permission): bool {
        return RoleMiddleware::hasPermission($permission);
    }
}
