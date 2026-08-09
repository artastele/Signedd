<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — Principal Controller

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/RoleRequestModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
if (file_exists(__DIR__ . '/../Helpers/MailHelper.php')) {
    require_once __DIR__ . '/../Helpers/MailHelper.php';
}

class PrincipalController {
    private $userModel;
    private $roleRequestModel;
    private $notificationModel;
    private $basePath;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->roleRequestModel = new RoleRequestModel();
        $this->notificationModel = new NotificationModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Staff role requests list (Principal approves: SPED Teacher, Guidance, Master Teacher)
     */
    public function staffRequests() {
        $principalUser = $this->userModel->findById($_SESSION['user_id']);
        $schoolId = $principalUser['school_id'] ?? null;

        $allRequests = $this->roleRequestModel->getAll();
        
        // Filter to show only staff requests (not principal) matching principal's school_id
        $requests = array_filter($allRequests, function($req) use ($schoolId) {
            $isStaff = $req['requested_role'] !== 'principal';
            if (!$isStaff) return false;
            if ($schoolId && isset($req['school_id']) && $req['school_id']) {
                return (int)$req['school_id'] === (int)$schoolId;
            }
            return true;
        });
        
        require_once __DIR__ . '/../Views/principal/staff_requests.php';
    }

    /**
     * Approve staff role request
     */
    public function approveStaff($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        // Verify this is a staff request (not principal)
        if ($request['requested_role'] === 'principal') {
            $_SESSION['error'] = 'You cannot approve principal requests.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        $reviewNote = trim($_POST['review_note'] ?? '');
        $principalUser = $this->userModel->findById($_SESSION['user_id']);

        // Update role request status
        $this->roleRequestModel->updateStatus(
            $requestId,
            'approved',
            $_SESSION['user_id'],
            $reviewNote
        );

        // Update user role and associate to principal's school_id if set
        $this->userModel->updateRole($request['user_id'], $request['requested_role']);
        if (!empty($principalUser['school_id'])) {
            $this->userModel->updateSchoolId($request['user_id'], $principalUser['school_id']);
        }

        // Create notification
        $this->notificationModel->create(
            $request['user_id'],
            'role_approved',
            'Application Approved',
            'Your application for ' . ucwords(str_replace('_', ' ', $request['requested_role'])) . ' has been approved!',
            ['role' => $request['requested_role'], 'request_id' => $requestId]
        );

        // Send approval email
        if (class_exists('MailHelper')) {
            MailHelper::sendRoleApprovalNotification(
                $request['user_email'],
                $request['user_name'],
                $request['requested_role']
            );
        }

        $_SESSION['success'] = 'Staff role request approved successfully!';
        header('Location: ' . $this->basePath . '/principal/staff-requests');
        exit;
    }

    /**
     * Reject staff role request
     */
    public function rejectStaff($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        // Verify this is a staff request (not principal)
        if ($request['requested_role'] === 'principal') {
            $_SESSION['error'] = 'You cannot reject principal requests.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        $reviewNote = trim($_POST['review_note'] ?? 'Your application was rejected.');

        // Update role request status
        $this->roleRequestModel->updateStatus(
            $requestId,
            'rejected',
            $_SESSION['user_id'],
            $reviewNote
        );

        // Create notification
        $this->notificationModel->create(
            $request['user_id'],
            'role_rejected',
            'Application Rejected',
            'Your application for ' . ucwords(str_replace('_', ' ', $request['requested_role'])) . ' was not approved.',
            [
                'role' => $request['requested_role'], 
                'request_id' => $requestId,
                'reason' => $reviewNote
            ]
        );

        // Send rejection email
        if (class_exists('MailHelper')) {
            MailHelper::sendRoleRejectionNotification(
                $request['user_email'],
                $request['user_name'],
                $request['requested_role'],
                $reviewNote
            );
        }

        $_SESSION['success'] = 'Staff role request rejected.';
        header('Location: ' . $this->basePath . '/principal/staff-requests');
        exit;
    }

    /**
     * Register a new faculty/staff member directly under the Principal's school
     */
    public function registerStaff() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $principalUser = $this->userModel->findById($_SESSION['user_id']);
        $schoolId = $principalUser['school_id'] ?? null;

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'sped_teacher';
        $employeeNumber = trim($_POST['employee_number'] ?? '');
        $password = $_POST['password'] ?? 'Teacher123!';

        if (empty($name) || empty($email)) {
            $_SESSION['error'] = 'Staff Name and Email are required.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'An account with that email already exists.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user with assigned role and principal's school_id
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO users (name, email, password_hash, role, school_id, email_verified, status)
            VALUES (:name, :email, :password_hash, :role, :school_id, TRUE, 'active')
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'school_id' => $schoolId
        ]);

        $_SESSION['success'] = 'Faculty member "' . htmlspecialchars($name) . '" (' . ucwords(str_replace('_', ' ', $role)) . ') enrolled successfully!';
        header('Location: ' . $this->basePath . '/dashboard');
        exit;
    }

    /**
     * Update Process 4 Enrollment Guidelines & Schedule (Principal capability)
     */
    public function updateEnrollmentSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        require_once __DIR__ . '/../Models/SystemSettingsModel.php';
        $settingsModel = new SystemSettingsModel();

        $sy           = trim($_POST['enrollment_sy'] ?? '2026-2027');
        $status       = trim($_POST['enrollment_status'] ?? 'open');
        $startDate   = trim($_POST['enrollment_start_date'] ?? '');
        $endDate     = trim($_POST['enrollment_end_date'] ?? '');
        
        if (isset($_POST['checklist_items']) && is_array($_POST['checklist_items'])) {
            $cleanItems = [];
            $isOptArray = $_POST['checklist_is_optional'] ?? [];
            foreach ($_POST['checklist_items'] as $idx => $itemVal) {
                $itemVal = trim($itemVal);
                if ($itemVal === '') continue;
                $itemVal = preg_replace('/\s*\((?:Optional|optional)\)$/i', '', $itemVal);
                if (isset($isOptArray[$idx]) && (string)$isOptArray[$idx] === '1') {
                    $itemVal .= ' (Optional)';
                }
                $cleanItems[] = $itemVal;
            }
            $guidelines = implode("\n", $cleanItems);
        } else {
            $guidelines = trim($_POST['enrollment_guidelines'] ?? '');
        }

        $contactEmail  = trim($_POST['contact_email'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $facebookPage  = trim($_POST['facebook_page'] ?? '');

        $settingsModel->updateMultipleSettings([
            'enrollment_sy'                   => $sy,
            'enrollment_status'               => $status,
            'enrollment_start_date'           => $startDate,
            'enrollment_end_date'             => $endDate,
            'enrollment_guidelines'           => $guidelines,
            'enrollment_guidelines_published' => 'true',
        ]);

        $schoolId = $_SESSION['school_id'] ?? null;

        // Fallback: look up school_id from DB if session doesn't have it
        if (!$schoolId && isset($_SESSION['user_id'])) {
            $dbFallback = Database::getInstance()->getConnection();
            $stmtFb = $dbFallback->prepare("SELECT school_id FROM users WHERE id = :id LIMIT 1");
            $stmtFb->execute(['id' => $_SESSION['user_id']]);
            $schoolId = $stmtFb->fetchColumn() ?: null;
            if ($schoolId) {
                $_SESSION['school_id'] = $schoolId; // cache it for future requests
            }
        }

        if ($schoolId) {
            require_once __DIR__ . '/../Models/SchoolModel.php';
            $schoolModel = new SchoolModel();
            $schoolModel->updateGuidelines($schoolId, [
                'enrollment_sy'         => $sy,
                'enrollment_status'     => $status,
                'enrollment_start_date' => $startDate,
                'enrollment_end_date'   => $endDate,
                'enrollment_guidelines' => $guidelines,
                'contact_email'         => $contactEmail,
                'contact_number'        => $contactNumber,
                'facebook_page'         => $facebookPage
            ]);

            // Optional Pubmat Poster Upload
            if (isset($_FILES['pubmat_image']) && $_FILES['pubmat_image']['error'] === UPLOAD_ERR_OK) {
                $pubmatFile = $_FILES['pubmat_image'];
                $pubmatDir = __DIR__ . '/../../public/uploads/pubmats/';
                if (!is_dir($pubmatDir)) {
                    mkdir($pubmatDir, 0755, true);
                }
                $ext = pathinfo($pubmatFile['name'], PATHINFO_EXTENSION);
                $fileName = 'pubmat_' . $schoolId . '_' . time() . '.' . strtolower($ext);
                if (move_uploaded_file($pubmatFile['tmp_name'], $pubmatDir . $fileName)) {
                    $schoolModel->updatePubmat($schoolId, 'uploads/pubmats/' . $fileName);
                }
            }
        }

        $_SESSION['success'] = 'Official Enrollment Guidelines, Pubmat Poster & School Details saved and published successfully!';
        header('Location: ' . $this->basePath . '/dashboard');
        exit;
    }

    /**
     * Upload custom school logo/image
     */
    public function uploadLogo() {
        $this->requirePrincipal();

        $schoolId = $_SESSION['school_id'] ?? null;
        if (!$schoolId) {
            $_SESSION['error'] = 'No school registered under your account.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        if (empty($_FILES['school_logo']['name']) || $_FILES['school_logo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please select a valid image file to upload.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $file = $_FILES['school_logo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'File size exceeds maximum limit of 5MB.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/schools/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'school_' . $schoolId . '_' . time() . '.' . strtolower($ext);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            require_once __DIR__ . '/../Models/SchoolModel.php';
            $schoolModel = new SchoolModel();
            $relativePath = 'uploads/schools/' . $fileName;
            $schoolModel->updateLogo($schoolId, $relativePath);

            $_SESSION['success'] = 'Official School Logo uploaded successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save uploaded image. Please try again.';
        }

        header('Location: ' . $this->basePath . '/dashboard');
        exit;
    }
}
