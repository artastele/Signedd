<?php
// Part of: SignED — Universal Profile & Account Settings Controller
// Last modified: 2026-08-21

require_once __DIR__ . '/../Models/UserModel.php';

class ProfileController {
    private UserModel $userModel;
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
        $this->userRole = $_SESSION['role'] ?? 'user';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $this->userModel = new UserModel();
    }

    /**
     * Profile & Account Settings Page
     */
    public function index(): void {
        $db = Database::getInstance()->getConnection();
        
        // Fetch fresh user data
        $stmt = $db->prepare("
            SELECT u.*, s.school_name 
            FROM users u
            LEFT JOIN schools s ON u.school_id = s.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $this->userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: ' . $this->basePath . '/logout');
            exit;
        }

        // If learner, fetch linked section / student record
        $studentRecord = null;
        if ($this->userRole === 'learner') {
            $stmtSr = $db->prepare("
                SELECT sr.*, sec.section_name as assigned_section, sec.grade_level as sec_grade
                FROM student_records sr
                LEFT JOIN sections sec ON sr.section_id = sec.id
                WHERE sr.learner_user_id = :uid OR sr.lrn = :email_lrn
                LIMIT 1
            ");
            $stmtSr->execute(['uid' => $this->userId, 'email_lrn' => str_replace('@signed.lms', '', $user['email'])]);
            $studentRecord = $stmtSr->fetch(PDO::FETCH_ASSOC);
        }

        $success = $_SESSION['profile_success'] ?? null;
        $error = $_SESSION['profile_error'] ?? null;
        unset($_SESSION['profile_success'], $_SESSION['profile_error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        require __DIR__ . '/../Views/profile/index.php';
    }

    /**
     * Update Basic Profile Info (Name, Phone, Bio)
     */
    public function updateProfile(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $highContrast = !empty($_POST['high_contrast']) ? 1 : 0;
        $fslPopups = isset($_POST['fsl_popups_enabled']) ? (int)$_POST['fsl_popups_enabled'] : 1;

        if (empty($name)) {
            $_SESSION['profile_error'] = 'Display name is required.';
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE users 
            SET name = :name,
                phone_number = :phone,
                bio = :bio,
                high_contrast = :hc,
                fsl_popups_enabled = :fsl,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'name'  => $name,
            'phone' => $phone,
            'bio'   => $bio,
            'hc'    => $highContrast,
            'fsl'   => $fslPopups,
            'id'    => $this->userId
        ]);

        $_SESSION['user_name'] = $name;
        $_SESSION['high_contrast'] = $highContrast;
        $_SESSION['fsl_popups_enabled'] = $fslPopups;
        $_SESSION['profile_success'] = 'Profile and accessibility preferences updated successfully!';
        header('Location: ' . $this->basePath . '/profile');
        exit;
    }

    /**
     * Upload / Update Avatar Photo
     */
    public function uploadAvatar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['avatar']['tmp_name'])) {
            $_SESSION['profile_error'] = 'Please choose an image to upload.';
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        $file = $_FILES['avatar'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $_SESSION['profile_error'] = 'Only JPG, PNG, WEBP, and GIF images are allowed.';
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['profile_error'] = 'Image size must not exceed 5MB.';
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        $uploadDir = function_exists('public_path') ? public_path('uploads/avatars/') : (__DIR__ . '/../../public/uploads/avatars/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'avatar_' . $this->userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;
        $relPath = 'uploads/avatars/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET profile_photo = :photo WHERE id = :id");
            $stmt->execute(['photo' => $relPath, 'id' => $this->userId]);

            $_SESSION['profile_photo'] = $relPath;
            $_SESSION['profile_success'] = 'Profile picture updated successfully!';
        } else {
            $_SESSION['profile_error'] = 'Failed to upload profile picture.';
        }

        header('Location: ' . $this->basePath . '/profile');
        exit;
    }

    /**
     * Change Password
     */
    public function changePassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || strlen($newPassword) < 6) {
            $_SESSION['profile_error'] = 'New password must be at least 6 characters long.';
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['profile_error'] = 'New password and confirmation password do not match.';
            header('Location: ' . $this->basePath . '/profile');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $this->userId]);
        $hash = $stmt->fetchColumn();

        // For learners who just got generated credentials, verify if current password is provided
        if (!empty($hash) && !empty($currentPassword)) {
            if (!password_verify($currentPassword, $hash)) {
                $_SESSION['profile_error'] = 'Current password is incorrect.';
                header('Location: ' . $this->basePath . '/profile');
                exit;
            }
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmtUpdate = $db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $stmtUpdate->execute(['hash' => $newHash, 'id' => $this->userId]);

        $_SESSION['profile_success'] = 'Password changed successfully! Please use your new password next time you login.';
        header('Location: ' . $this->basePath . '/profile');
        exit;
    }
}
