<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — Role Selection Controller

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/RoleRequestModel.php';
require_once __DIR__ . '/../Models/SchoolModel.php';
if (file_exists(__DIR__ . '/../Helpers/MailHelper.php')) {
    require_once __DIR__ . '/../Helpers/MailHelper.php';
}

class RoleController {
    private $userModel;
    private $roleRequestModel;
    private $schoolModel;
    private $basePath;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->roleRequestModel = new RoleRequestModel();
        $this->schoolModel = new SchoolModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Show role selection page
     */
    public function showSelection() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $type = $_GET['type'] ?? null;
        $userId = $_SESSION['user_id'];
        $currentUser = $this->userModel->findById($userId);
        if (!$currentUser) {
            session_destroy();
            session_start();
            $_SESSION['error'] = 'Session expired or user account not found. Please log in again.';
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userName = $_SESSION['user_name'] ?? $currentUser['name'];
        $userEmail = $_SESSION['user_email'] ?? $currentUser['email'];

        // Check if user already has a pending request
        $pendingRequest = $this->roleRequestModel->getPendingByUserId($userId);
        
        // Fetch all registered schools for selector
        $schools = $this->schoolModel->getAllSchools();

        require_once __DIR__ . '/../Views/auth/role_select.php';
    }

    /**
     * Select parent role (instant)
     */
    public function selectParent() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $schoolId = !empty($_POST['school_id']) ? (int)$_POST['school_id'] : null;

        if (!$schoolId) {
            $_SESSION['error'] = 'Please select your target SPED Center / School before proceeding as Parent.';
            header('Location: ' . $this->basePath . '/role/select?type=parent');
            exit;
        }

        // Update user role to parent and assign target school_id
        $this->userModel->updateRole($userId, 'parent');
        $this->userModel->updateSchoolId($userId, $schoolId);

        // Update session
        $_SESSION['role'] = 'parent';
        $_SESSION['school_id'] = $schoolId;
        $_SESSION['success'] = 'Welcome! Your target SPED Center has been set. You can now enroll your child.';

        header('Location: ' . $this->basePath . '/enrollment?school_id=' . $schoolId);
        exit;
    }

    /**
     * Submit staff role application
     */
    public function submitStaffApplication() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/role/select');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $currentUser = $this->userModel->findById($userId);
        if (!$currentUser) {
            session_destroy();
            session_start();
            $_SESSION['error'] = 'Session expired or user account not found. Please log in again.';
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $requestedRole = $_POST['requested_role'] ?? '';
        $employeeNumber = trim($_POST['employee_number'] ?? '');
        $schoolMode = $_POST['school_mode'] ?? 'existing';
        $schoolId = $_POST['school_id'] ?? null;

        // Validation
        $errors = [];
        $validRoles = ['sped_teacher', 'guidance', 'principal', 'master_teacher', 'general_teacher'];

        if (!in_array($requestedRole, $validRoles)) {
            $errors[] = 'Invalid role selected.';
        }

        // Process school selection / registration
        if ($schoolMode === 'new' || ($requestedRole === 'principal' && !empty($_POST['new_school_name']))) {
            $newCode = trim($_POST['new_school_code'] ?? '');
            $newName = trim($_POST['new_school_name'] ?? '');
            $newDiv  = trim($_POST['new_school_division'] ?? '');
            $newReg  = trim($_POST['new_school_region'] ?? '');
            $newAddr = trim($_POST['new_school_address'] ?? '');

            if (empty($newCode) || empty($newName)) {
                $errors[] = 'DepEd School ID Code and School Name are required to register a school.';
            } else {
                $existingSchool = $this->schoolModel->findBySchoolCode($newCode);
                if ($existingSchool) {
                    $schoolId = $existingSchool['id'];
                } else {
                    // School logo validation
                    if (empty($_FILES['school_logo']['name']) || $_FILES['school_logo']['error'] !== UPLOAD_ERR_OK) {
                        $errors[] = 'Official School Logo image is required when registering a new school.';
                    } else {
                        $schoolId = $this->schoolModel->createSchool($newCode, $newName, $newDiv, $newReg, $newAddr);
                        if ($schoolId) {
                            $logoFile = $_FILES['school_logo'];
                            $logoDir = __DIR__ . '/../../public/uploads/schools/';
                            if (!is_dir($logoDir)) {
                                mkdir($logoDir, 0755, true);
                            }
                            $ext = pathinfo($logoFile['name'], PATHINFO_EXTENSION);
                            $fileName = 'school_' . $schoolId . '_' . time() . '.' . strtolower($ext);
                            if (move_uploaded_file($logoFile['tmp_name'], $logoDir . $fileName)) {
                                $this->schoolModel->updateLogo($schoolId, 'uploads/schools/' . $fileName);
                            }
                        }
                    }
                }
            }
        }

        if ($schoolId) {
            $this->userModel->updateSchoolId($userId, $schoolId);
        }

        // Check if user already has pending request
        if ($this->roleRequestModel->hasPendingRequest($userId)) {
            $_SESSION['error'] = 'You already have a pending role request.';
            header('Location: ' . $this->basePath . '/role/select');
            exit;
        }

        // File uploads
        $uploadedFiles = [];
        $uploadDir = __DIR__ . '/../../public/uploads/role_verification/';

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Process government ID
        // Process optional government / principal ID
        if (isset($_FILES['government_id']) && $_FILES['government_id']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['government_id'];
            $result = $this->uploadFile($file, $uploadDir, 'gov_id_' . $userId . '_');
            if ($result['success']) {
                $uploadedFiles['government_id'] = $result['path'];
            }
        }

        // Process optional proof of designation
        if (isset($_FILES['proof_designation']) && $_FILES['proof_designation']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['proof_designation'];
            $result = $this->uploadFile($file, $uploadDir, 'proof_' . $userId . '_');
            if ($result['success']) {
                $uploadedFiles['proof_designation'] = $result['path'];
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_role'] = $requestedRole;
            $_SESSION['old_employee_number'] = $employeeNumber;
            header('Location: ' . $this->basePath . '/role/select?type=staff');
            exit;
        }

        // Create role request
        $principalRank = trim($_POST['principal_rank'] ?? '');
        $submittedDocs = [
            'employee_number' => $employeeNumber,
            'principal_rank' => $principalRank,
            'school_id' => $schoolId,
            'files' => $uploadedFiles
        ];

        $requestId = $this->roleRequestModel->create($userId, $requestedRole, $submittedDocs);

        // Save document records
        foreach ($uploadedFiles as $type => $path) {
            $this->roleRequestModel->saveDocument($requestId, $path, $type);
        }

        // Send email notification to correct approver
        if (class_exists('MailHelper')) {
            $approverRole = ($requestedRole === 'principal') ? 'admin' : 'principal';
            $allUsers = $this->userModel->getAllUsers();
            
            foreach ($allUsers as $user) {
                if ($user['role'] === $approverRole) {
                    MailHelper::sendRoleVerificationNotification(
                        $user['email'],
                        $_SESSION['user_name'],
                        $requestedRole
                    );
                }
            }
        }

        $_SESSION['success'] = 'Your application has been submitted! You will be notified once it is reviewed.';
        header('Location: ' . $this->basePath . '/dashboard');
        exit;
    }

    /**
     * Upload file helper (simplified - no encryption)
     */
    private function uploadFile($file, $uploadDir, $prefix) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, and PDF are allowed.'];
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File size exceeds 5MB limit.'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true, 
                'path' => 'uploads/role_verification/' . $filename,
                'original_name' => $file['name']
            ];
        } else {
            return ['success' => false, 'error' => 'Failed to upload file.'];
        }
    }
}
