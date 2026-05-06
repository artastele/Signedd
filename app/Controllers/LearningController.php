<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Learning Controller (Learner Side)

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/LearnerIEPModel.php';
require_once __DIR__ . '/../Models/LearningMaterialModel.php';
require_once __DIR__ . '/../Models/ActivityTemplateModel.php';
require_once __DIR__ . '/../Models/ActivityAttemptModel.php';
require_once __DIR__ . '/../Models/LearnerProgressModel.php';
require_once __DIR__ . '/../Models/AssignmentSubmissionModel.php';
require_once __DIR__ . '/../Models/ModuleAccessLogModel.php';
require_once __DIR__ . '/../Helpers/FileEncryptionHelper.php';

class LearningController {
    private $studentModel;
    private $iepModel;
    private $materialModel;
    private $activityModel;
    private $attemptModel;
    private $progressModel;
    private $submissionModel;
    private $accessLogModel;
    private $basePath;

    public function __construct() {
        $this->studentModel = new StudentModel();
        $this->iepModel = new LearnerIEPModel();
        $this->materialModel = new LearningMaterialModel();
        $this->activityModel = new ActivityTemplateModel();
        $this->attemptModel = new ActivityAttemptModel();
        $this->progressModel = new LearnerProgressModel();
        $this->submissionModel = new AssignmentSubmissionModel();
        $this->accessLogModel = new ModuleAccessLogModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Get student ID from user session
     */
    private function getStudentId() {
        $userId = $_SESSION['user_id'];
        $student = $this->studentModel->getByUserId($userId);
        return $student['id'] ?? null;
    }

    /**
     * Learner dashboard
     */
    public function dashboard() {
        $studentId = $this->getStudentId();
        
        if (!$studentId) {
            $_SESSION['error'] = 'Student record not found';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Get stats
        $modules = $this->materialModel->getModulesByStudentId($studentId);
        $assignments = $this->materialModel->getAssignmentsByStudentId($studentId);
        $totalStars = $this->progressModel->getTotalStars($studentId);
        $progressPercentage = $this->progressModel->calculateProgressPercentage($studentId);

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/dashboard/learner.php';
    }

    /**
     * Modules list
     */
    public function modules() {
        $studentId = $this->getStudentId();
        
        if (!$studentId) {
            $_SESSION['error'] = 'Student record not found';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $modules = $this->materialModel->getModulesByStudentId($studentId);
        
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/learning/modules.php';
    }

    /**
     * Assignments list
     */
    public function assignments() {
        $studentId = $this->getStudentId();
        
        if (!$studentId) {
            $_SESSION['error'] = 'Student record not found';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $assignments = $this->materialModel->getAssignmentsByStudentId($studentId);
        
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/learning/assignments.php';
    }

    /**
     * View module
     */
    public function viewModule($materialId) {
        $studentId = $this->getStudentId();
        
        if (!$studentId) {
            $_SESSION['error'] = 'Student record not found';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $material = $this->materialModel->getById($materialId);
        
        if (!$material) {
            $_SESSION['error'] = 'Module not found';
            header('Location: ' . BASE_PATH . '/learning/modules');
            exit;
        }

        // Get or create progress
        $progress = $this->progressModel->getOrCreate($studentId, $materialId);
        
        // Handle null progress (database error)
        if (!$progress) {
            $progress = [
                'status' => 'not_started',
                'stars_earned' => 0,
                'time_spent_minutes' => 0
            ];
        }
        
        // Update status to in_progress if not started
        if ($progress['status'] === 'not_started') {
            $this->progressModel->updateStatus($studentId, $materialId, 'in_progress');
            $progress['status'] = 'in_progress';
        }

        // Check if it's an activity
        $activity = $this->activityModel->getByMaterialId($materialId);
        
        if ($activity) {
            // Redirect to activity player
            header('Location: ' . BASE_PATH . '/learning/activity/' . $activity['id']);
            exit;
        }

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/learning/view_module.php';
    }

    /**
     * Mark module as complete
     */
    public function completeModule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $studentId = $this->getStudentId();
        $materialId = $_POST['material_id'] ?? null;
        $timeSpent = $_POST['time_spent'] ?? 0;

        if (!$studentId || !$materialId) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Add time spent
        $this->progressModel->addTimeSpent($studentId, $materialId, $timeSpent);
        
        // Mark as complete
        $starsEarned = 1; // Default 1 star for completion
        $this->progressModel->markComplete($studentId, $materialId, $starsEarned);

        // Log access
        $material = $this->materialModel->getById($materialId);
        $this->accessLogModel->logAccess($studentId, $material['material_name'], $timeSpent);

        echo json_encode([
            'success' => true,
            'message' => 'Module completed!',
            'stars_earned' => $starsEarned
        ]);
        exit;
    }

    /**
     * Play interactive activity
     */
    public function playActivity($activityId) {
        $studentId = $this->getStudentId();
        
        if (!$studentId) {
            $_SESSION['error'] = 'Student record not found';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $activity = $this->activityModel->getById($activityId);
        
        if (!$activity) {
            $_SESSION['error'] = 'Activity not found';
            header('Location: ' . BASE_PATH . '/learning/modules');
            exit;
        }

        // Get material
        $material = $this->materialModel->getById($activity['material_id']);
        
        // Get previous attempts
        $attempts = $this->attemptModel->getByStudentAndActivity($studentId, $activityId);
        $bestAttempt = $this->attemptModel->getBestAttempt($studentId, $activityId);

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/learning/play_activity.php';
    }

    /**
     * Submit activity answers
     */
    public function submitActivity() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $studentId = $this->getStudentId();
        $activityId = $_POST['activity_id'] ?? null;
        $answersJson = $_POST['answers'] ?? '[]';
        $timeSpent = $_POST['time_spent'] ?? 0;

        if (!$studentId || !$activityId) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Decode answers from JSON
        $answers = json_decode($answersJson, true);
        if ($answers === null) {
            echo json_encode(['success' => false, 'message' => 'Invalid answers format']);
            exit;
        }

        // Get activity template
        $activity = $this->activityModel->getById($activityId);
        
        if (!$activity) {
            echo json_encode(['success' => false, 'message' => 'Activity not found']);
            exit;
        }

        // Calculate score
        $score = $this->attemptModel->calculateScore($activity, $answers);
        $totalPoints = $activity['total_points'];
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

        // Save attempt
        $attemptId = $this->attemptModel->create($activityId, $studentId, $answers, $score, $totalPoints, $timeSpent);

        if ($attemptId) {
            // Update progress
            $materialId = $activity['material_id'];
            $this->progressModel->addTimeSpent($studentId, $materialId, $timeSpent);
            
            // Award stars based on score
            $starsEarned = 0;
            if ($percentage >= 90) $starsEarned = 3;
            elseif ($percentage >= 70) $starsEarned = 2;
            elseif ($percentage >= 50) $starsEarned = 1;
            
            if ($percentage >= 50) {
                $this->progressModel->markComplete($studentId, $materialId, $starsEarned);
            }

            echo json_encode([
                'success' => true,
                'score' => $score,
                'total_points' => $totalPoints,
                'percentage' => round($percentage, 2),
                'stars_earned' => $starsEarned,
                'message' => $percentage >= 50 ? 'Great job!' : 'Keep trying!'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save attempt']);
        }
        exit;
    }

    /**
     * View assignment
     */
    public function viewAssignment($materialId) {
        $studentId = $this->getStudentId();
        
        if (!$studentId) {
            $_SESSION['error'] = 'Student record not found';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $material = $this->materialModel->getById($materialId);
        
        if (!$material || !$material['is_assignment']) {
            $_SESSION['error'] = 'Assignment not found';
            header('Location: ' . BASE_PATH . '/learning/assignments');
            exit;
        }

        // Get submission if exists
        $submission = $this->submissionModel->getByStudentAndMaterial($studentId, $materialId);

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/learning/view_assignment.php';
    }

    /**
     * Submit assignment
     */
    public function submitAssignment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $studentId = $this->getStudentId();
        $materialId = $_POST['material_id'] ?? null;
        $textAnswer = $_POST['text_answer'] ?? null;

        if (!$studentId || !$materialId) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $filePath = null;
        $submissionType = 'text';

        // Handle file upload if present (simplified - no encryption)
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $uploadDir = __DIR__ . '/../../public/uploads/assignments/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExt;
            $fullPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                $filePath = 'uploads/assignments/' . $fileName;
                $submissionType = $textAnswer ? 'both' : 'file';
            }
        }

        // Create submission
        $submissionId = $this->submissionModel->create($materialId, $studentId, $submissionType, $filePath, $textAnswer);

        if ($submissionId) {
            // Mark as in progress
            $this->progressModel->updateStatus($studentId, $materialId, 'in_progress');

            echo json_encode([
                'success' => true,
                'message' => 'Assignment submitted successfully!'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit assignment']);
        }
        exit;
    }

    /**
     * Progress page
     */
    public function progress() {
        $studentId = $this->getStudentId();
        
        if (!$studentId) {
            $_SESSION['error'] = 'Student record not found';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Get stats
        $totalStars = $this->progressModel->getTotalStars($studentId);
        $progressPercentage = $this->progressModel->calculateProgressPercentage($studentId);
        $completedCount = $this->progressModel->countCompleted($studentId);
        $totalMaterials = $this->materialModel->countByStudent($studentId);
        $submissionsCount = $this->submissionModel->countByStudent($studentId);
        
        // Get recent progress
        $recentProgress = $this->progressModel->getByStudentId($studentId);
        
        // Get recent attempts
        $recentAttempts = $this->attemptModel->getByStudentId($studentId);

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/learning/progress.php';
    }

    /**
     * Log activity (AJAX)
     */
    public function logActivity() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }

        $studentId = $this->getStudentId();
        $materialId = $_POST['material_id'] ?? null;
        $timeSpent = $_POST['time_spent'] ?? 0;

        if ($studentId && $materialId && $timeSpent > 0) {
            $this->progressModel->addTimeSpent($studentId, $materialId, $timeSpent);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}
