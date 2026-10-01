<?php
// Part of: SignED — Parent QR Claim & Activation Controller
// Last modified: 2026-09-22

require_once __DIR__ . '/../Models/MasterlistModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/CSRFHelper.php';

class ParentClaimController {
    private MasterlistModel $masterlistModel;
    private UserModel $userModel;
    private string $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->masterlistModel = new MasterlistModel();
        $this->userModel = new UserModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Display the QR Claim / Invite Landing Page
     */
    public function claim(string $token = ''): void {
        $token = trim($token);
        if (empty($token)) {
            $_SESSION['error'] = 'Invalid invitation link.';
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $learner = $this->masterlistModel->getLearnerByClaimToken($token);
        if (!$learner) {
            $pageTitle = 'Invalid Invitation — SignED';
            $basePath = $this->basePath;
            require_once __DIR__ . '/../Views/auth/claim_invalid.php';
            exit;
        }

        // Check if learner already has a registered parent linked
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUserRole = $_SESSION['role'] ?? '';
        $existingParentId = (int)($learner['parent_id'] ?? 0);

        if ($existingParentId > 0 && $existingParentId !== $currentUserId) {
            $alreadyClaimed = true;
        } else {
            $alreadyClaimed = false;
        }

        $basePath = $this->basePath;
        $pageTitle = 'Connect with ' . htmlspecialchars($learner['student_name']) . ' — SignED';
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/auth/claim_parent.php';
    }

    /**
     * Process Parent Account Activation & Linking
     */
    public function activate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $token = trim($_POST['token'] ?? '');
        $learner = $this->masterlistModel->getLearnerByClaimToken($token);
        if (!$learner) {
            $_SESSION['error'] = 'Invalid or expired invitation token.';
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $studentId = (int)$learner['student_record_id'];
        $schoolId = (int)$learner['school_id'];
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUserRole = $_SESSION['role'] ?? '';

        // If parent is ALREADY logged in, directly link
        if ($currentUserId > 0 && $currentUserRole === 'parent') {
            $claimInfo = $this->masterlistModel->linkParentAccountToLearner($studentId, $currentUserId);
            if ($claimInfo) {
                $_SESSION['generated_learner_credentials'] = [
                    'student_name' => $claimInfo['student_name'],
                    'username'     => $claimInfo['username'],
                    'password'     => $claimInfo['password'],
                    'login_url'    => $this->basePath . '/login'
                ];
            }
            $_SESSION['success'] = "Successfully connected to {$learner['student_name']}!";
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        // Otherwise, register new parent account
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];
        if (empty($name)) $errors[] = 'Your full name is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
        if (empty($password)) $errors[] = 'Password is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'An account with this email already exists. Please log in first, then scan the QR code to link.';
            header('Location: ' . $this->basePath . '/login?return_to=' . urlencode('/invite/claim/' . $token));
            exit;
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: ' . $this->basePath . '/invite/claim/' . $token);
            exit;
        }

        // Create user
        $db = Database::getInstance()->getConnection();
        $passHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("
            INSERT INTO users (
                name, email, password_hash, role, status, email_verified,
                auth_provider, school_id, created_at, updated_at
            ) VALUES (
                :name, :email, :pass, 'parent', 'active', 1,
                'local', :school_id, NOW(), NOW()
            )
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'pass' => $passHash,
            'school_id' => $schoolId > 0 ? $schoolId : null
        ]);
        $newUserId = (int)$db->lastInsertId();

        // Link parent to learner & generate/link learner LMS credentials
        $claimInfo = $this->masterlistModel->linkParentAccountToLearner($studentId, $newUserId);

        // Sign in user
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['role'] = 'parent';
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        if ($schoolId > 0) {
            $_SESSION['school_id'] = $schoolId;
        }

        if ($claimInfo) {
            $_SESSION['generated_learner_credentials'] = [
                'student_name' => $claimInfo['student_name'],
                'username'     => $claimInfo['username'],
                'password'     => $claimInfo['password'],
                'login_url'    => $this->basePath . '/login'
            ];
        }

        $_SESSION['success'] = "Welcome to SignED! Your parent account has been activated and linked to {$learner['student_name']}.";
        header('Location: ' . $this->basePath . '/dashboard');
        exit;
    }
}
