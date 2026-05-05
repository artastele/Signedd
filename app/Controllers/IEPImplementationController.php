<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — IEP Implementation Controller

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Models/LearnerIEPModel.php';
require_once __DIR__ . '/../Models/LearningMaterialModel.php';
require_once __DIR__ . '/../Models/ActivityTemplateModel.php';
require_once __DIR__ . '/../Models/LearnerProgressModel.php';
require_once __DIR__ . '/../Helpers/FileEncryptionHelper.php';

class IEPImplementationController {
    private $learnerIEPModel;
    private $materialModel;
    private $activityModel;
    private $progressModel;
    private $basePath;

    public function __construct() {
        $this->learnerIEPModel = new LearnerIEPModel();
        $this->materialModel = new LearningMaterialModel();
        $this->activityModel = new ActivityTemplateModel();
        $this->progressModel = new LearnerProgressModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Dashboard - List all students with assigned IEPs
     */
    public function index() {
        $teacherId = $_SESSION['user_id'];
        $students = $this->learnerIEPModel->getByTeacherId($teacherId);
        
        // Calculate progress for each student
        foreach ($students as &$student) {
            $total = $student['materials_count'];
            $completed = $student['completed_count'];
            $student['progress_percentage'] = $total > 0 ? round(($completed / $total) * 100) : 0;
        }
        
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/iep_implementation/index.php';
    }

    /**
     * Show assign IEP form
     */
    public function showAssign() {
        // Get students ready for IEP assignment
        $students = $this->learnerIEPModel->getStudentsReadyForAssignment();
        
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/iep_implementation/assign.php';
    }

    /**
     * Save IEP assignment
     */
    public function assign() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . '/iep/implementation');
            exit;
        }

        $studentId = $_POST['student_id'] ?? null;
        $iepId = $_POST['iep_id'] ?? null;
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        $teacherId = $_SESSION['user_id'];

        if (!$studentId || !$iepId) {
            $_SESSION['error'] = 'Missing required fields';
            header('Location: ' . BASE_PATH . '/iep/implementation/assign');
            exit;
        }

        $learnerIepId = $this->learnerIEPModel->assignIEP($studentId, $iepId, $teacherId, $startDate, $notes);

        if ($learnerIepId) {
            $_SESSION['success'] = 'IEP assigned successfully!';
            header('Location: ' . BASE_PATH . '/iep/implementation/materials/' . $learnerIepId);
        } else {
            $_SESSION['error'] = 'Failed to assign IEP';
            header('Location: ' . BASE_PATH . '/iep/implementation/assign');
        }
        exit;
    }

    /**
     * Materials page for specific student
     */
    public function materials($learnerIepId) {
        $iep = $this->learnerIEPModel->getById($learnerIepId);
        
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found';
            header('Location: ' . BASE_PATH . '/iep/implementation');
            exit;
        }

        $materials = $this->materialModel->getByLearnerIepId($learnerIepId);
        
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/iep_implementation/materials.php';
    }

    /**
     * Show create activity form
     */
    public function showCreateActivity($learnerIepId = null) {
        $iep = null;
        if ($learnerIepId) {
            $iep = $this->learnerIEPModel->getById($learnerIepId);
        }
        
        // Get all students for this teacher
        $teacherId = $_SESSION['user_id'];
        $students = $this->learnerIEPModel->getByTeacherId($teacherId);
        
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/iep_implementation/create_activity.php';
    }

    /**
     * Upload file material
     */
    public function uploadFile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $learnerIepIds = $_POST['learner_iep_ids'] ?? [];
        $materialName = $_POST['material_name'] ?? '';
        $materialType = $_POST['material_type'] ?? '';
        $description = $_POST['description'] ?? '';
        $isAssignment = isset($_POST['is_assignment']) ? 1 : 0;
        $dueDate = $_POST['due_date'] ?? null;
        $points = $_POST['points'] ?? 0;
        $uploadedBy = $_SESSION['user_id'];

        if (empty($learnerIepIds) || empty($materialName) || !isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Handle file upload
        $file = $_FILES['file'];
        $uploadDir = __DIR__ . '/../../public/uploads/materials/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $fileExt;
        $filePath = 'uploads/materials/' . $fileName;
        $fullPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            // Encrypt file
            $encryptedPath = FileEncryptionHelper::encryptFile($fullPath);
            
            if ($encryptedPath) {
                // Delete original
                unlink($fullPath);
                $filePath = $encryptedPath;
            }

            // Create material for each selected student
            $successCount = 0;
            foreach ($learnerIepIds as $learnerIepId) {
                $materialId = $this->materialModel->create([
                    'learner_iep_id' => $learnerIepId,
                    'material_name' => $materialName,
                    'material_type' => $materialType,
                    'file_path' => $filePath,
                    'description' => $description,
                    'is_assignment' => $isAssignment,
                    'due_date' => $dueDate,
                    'points' => $points,
                    'uploaded_by' => $uploadedBy
                ]);

                if ($materialId) {
                    $successCount++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Material uploaded to $successCount student(s)"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'File upload failed']);
        }
        exit;
    }

    /**
     * Save manual activity
     */
    public function saveActivity() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $learnerIepIds = $_POST['learner_iep_ids'] ?? [];
        $materialName = $_POST['material_name'] ?? '';
        $activityType = $_POST['activity_type'] ?? '';
        $instructions = $_POST['instructions'] ?? '';
        $activityData = $_POST['activity_data'] ?? '';
        $totalPoints = $_POST['total_points'] ?? 0;
        $isAssignment = isset($_POST['is_assignment']) ? 1 : 0;
        $dueDate = $_POST['due_date'] ?? null;
        $uploadedBy = $_SESSION['user_id'];

        if (empty($learnerIepIds) || empty($materialName) || empty($activityType) || empty($activityData)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Decode activity data
        $activityDataArray = json_decode($activityData, true);
        if (!$activityDataArray) {
            echo json_encode(['success' => false, 'message' => 'Invalid activity data']);
            exit;
        }

        // Create material and activity for each selected student
        $successCount = 0;
        foreach ($learnerIepIds as $learnerIepId) {
            // Create material
            $materialId = $this->materialModel->create([
                'learner_iep_id' => $learnerIepId,
                'material_name' => $materialName,
                'material_type' => 'activity',
                'file_path' => null,
                'description' => $instructions,
                'is_assignment' => $isAssignment,
                'due_date' => $dueDate,
                'points' => $totalPoints,
                'uploaded_by' => $uploadedBy
            ]);

            if ($materialId) {
                // Create activity template
                $activityId = $this->activityModel->create(
                    $materialId,
                    $activityType,
                    $activityDataArray,
                    $instructions,
                    $totalPoints
                );

                if ($activityId) {
                    $successCount++;
                }
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Activity created for $successCount student(s)"
        ]);
        exit;
    }

    /**
     * Delete material
     */
    public function deleteMaterial($materialId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $material = $this->materialModel->getById($materialId);
        
        if (!$material) {
            echo json_encode(['success' => false, 'message' => 'Material not found']);
            exit;
        }

        // Delete file if exists
        if ($material['file_path']) {
            $fullPath = __DIR__ . '/../../public/' . $material['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        if ($this->materialModel->delete($materialId)) {
            echo json_encode(['success' => true, 'message' => 'Material deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete material']);
        }
        exit;
    }

    /**
     * Progress page for specific student
     */
    public function progress($learnerIepId) {
        $iep = $this->learnerIEPModel->getById($learnerIepId);
        
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found';
            header('Location: ' . BASE_PATH . '/iep/implementation');
            exit;
        }

        $materials = $this->materialModel->getByLearnerIepId($learnerIepId);
        $progress = $this->progressModel->getByStudentId($iep['student_id']);
        
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/iep_implementation/progress.php';
    }
}
