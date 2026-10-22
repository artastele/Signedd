<?php
// Part of: SignED — Parent QR Claim & Universal GC Activation Controller
// Last modified: 2026-10-16

require_once __DIR__ . '/../Models/MasterlistModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/CSRFHelper.php';
require_once __DIR__ . '/../Services/SmsService.php';

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
     * Display Universal GC Claim Page (No token required — Choose child by Name / Alias)
     */
    public function universalClaim(): void {
        $learners = $this->masterlistModel->getClaimableLearners();
        $basePath = $this->basePath;
        $pageTitle = 'Connect with Learner — SignED Parent Portal';
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUserRole = $_SESSION['role'] ?? '';

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        require_once __DIR__ . '/../Views/auth/claim_universal.php';
    }

    /**
     * Display the QR Claim / Direct Invite Landing Page (Via specific token)
     */
    public function claim(string $token = ''): void {
        $token = trim($token);
        if (empty($token)) {
            $this->universalClaim();
            return;
        }

        $learner = $this->masterlistModel->getLearnerByClaimToken($token);
        if (!$learner) {
            $pageTitle = 'Invalid Invitation — SignED';
            $basePath = $this->basePath;
            require_once __DIR__ . '/../Views/auth/claim_invalid.php';
            exit;
        }

        // Check if learner already has a registered parent linked or active learner account
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUserRole = $_SESSION['role'] ?? '';
        $existingParentId = (int)($learner['parent_id'] ?? 0);
        $hasAccount = !empty($learner['learner_user_id']) || ($learner['learner_account_created'] ?? 0) == 1 || ($learner['lms_invite_status'] ?? '') === 'accepted';

        if (($existingParentId > 0 && $existingParentId !== $currentUserId) || ($hasAccount && $existingParentId > 0 && $existingParentId !== $currentUserId)) {
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
     * Process Parent Account Activation & Linking via Universal Alias Selection
     */
    public function claimByAlias(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/invite/claim');
            exit;
        }

        $studentId = (int)($_POST['student_record_id'] ?? 0);
        if ($studentId <= 0) {
            $_SESSION['error'] = 'Palihug pagpili og ngalan o alias sa imong anak.';
            header('Location: ' . $this->basePath . '/invite/claim');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT sr.id AS student_record_id, sr.student_name, sr.school_id, sr.learner_user_id, sr.lms_invite_status,
                   es.parent_id, es.id AS enrollment_id, es.learner_account_created
            FROM student_records sr
            LEFT JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $studentId]);
        $learner = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$learner) {
            $_SESSION['error'] = 'Wala makit-i ang rekord sa maong estudyante.';
            header('Location: ' . $this->basePath . '/invite/claim');
            exit;
        }

        $schoolId = (int)($learner['school_id'] ?? 0);
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUserRole = $_SESSION['role'] ?? '';
        $existingParentId = (int)($learner['parent_id'] ?? 0);
        $hasAccount = !empty($learner['learner_user_id']) || ($learner['learner_account_created'] ?? 0) == 1 || ($learner['lms_invite_status'] ?? '') === 'accepted';

        // Check if learner is already claimed / has an account
        if ($existingParentId > 0 && $existingParentId !== $currentUserId) {
            $_SESSION['error'] = 'Kini nga estudyante aduna na\'y konektado nga account sa ginikanan. Palihug pakig-alayon sa imong SPED teacher.';
            header('Location: ' . $this->basePath . '/invite/claim');
            exit;
        }

        if ($currentUserId === 0 && ($existingParentId > 0 || $hasAccount)) {
            $_SESSION['error'] = 'Kini nga estudyante aduna na\'y aktibong rehistradong account. Palihug pag-log in sa imong ginikanan nga account.';
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        // Case 1: If parent is ALREADY logged in
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

            // Get user phone for confirmation SMS
            $stmtU = $db->prepare("SELECT contact_number, name FROM users WHERE id = :uid LIMIT 1");
            $stmtU->execute(['uid' => $currentUserId]);
            $uRow = $stmtU->fetch(PDO::FETCH_ASSOC);
            if (!empty($uRow['contact_number'])) {
                SmsService::getInstance()->send(
                    $uRow['contact_number'],
                    "SignED: Maayong adlaw {$uRow['name']}! Malampusong na-link ang imong account sa imong anak ({$learner['student_name']}). Makadawat ka na karon og instant text alerts sa bag-ong activities.",
                    $uRow['name'],
                    'parent_welcome'
                );
            }

            $_SESSION['success'] = "Malampusong na-link ang imong account kang {$learner['student_name']}!";
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        // Case 2: New Parent Registration
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            $errors[] = 'Palihug ibutang ang imong password.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Wala magkatugma ang mga password.';
        }
        if (empty($_POST['privacy_consent'])) $errors[] = 'Palihug tsekki ang Data Privacy Act (RA 10173) consent aron makapadayon.';

        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'Aduna nay narehistro nga account gamit kining maong email. Palihug pag-log in una.';
            header('Location: ' . $this->basePath . '/login?return_to=' . urlencode('/invite/claim'));
            exit;
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: ' . $this->basePath . '/invite/claim');
            exit;
        }

        $formattedContact = SmsService::getInstance()->formatPhoneNumber($contact) ?: $contact;
        $passHash = password_hash($password, PASSWORD_BCRYPT);

        $stmtIns = $db->prepare("
            INSERT INTO users (
                name, email, contact_number, password_hash, role, status, email_verified,
                auth_provider, school_id, created_at, updated_at
            ) VALUES (
                :name, :email, :contact, :pass, 'parent', 'active', 1,
                'local', :school_id, NOW(), NOW()
            )
        ");
        $stmtIns->execute([
            'name' => $name,
            'email' => $email,
            'contact' => $formattedContact,
            'pass' => $passHash,
            'school_id' => $schoolId > 0 ? $schoolId : null
        ]);
        $newUserId = (int)$db->lastInsertId();

        // Update enrollment guardian phone if available
        if (!empty($learner['enrollment_id'])) {
            $stmtUpd = $db->prepare("UPDATE enrollment_submissions SET guardian_contact_number = :c WHERE id = :eid AND (guardian_contact_number IS NULL OR guardian_contact_number = '')");
            $stmtUpd->execute(['c' => $formattedContact, 'eid' => $learner['enrollment_id']]);
        }

        // Link parent to learner
        $claimInfo = $this->masterlistModel->linkParentAccountToLearner($studentId, $newUserId);

        // Send welcome SMS
        if (!empty($formattedContact)) {
            SmsService::getInstance()->send(
                $formattedContact,
                "SignED: Maayong adlaw {$name}! Malampusong na-link ang imong account sa imong anak ({$learner['student_name']}). Makadawat ka na karon og instant text alerts sa bag-ong activities ug leksyon.",
                $name,
                'parent_welcome'
            );
        }

        // Sign in user
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['role'] = 'parent';
        $_SESSION['name'] = $name;
        $_SESSION['user_name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['user_email'] = $email;
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

        $_SESSION['success'] = "Malipayong pag-abot sa SignED! Naka-link na ang imong parent account kang {$learner['student_name']}.";
        header('Location: ' . $this->basePath . '/dashboard');
        exit;
    }

    /**
     * Process Parent Account Activation & Linking via Specific Token (QR Scan)
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
        if (empty($contact)) $errors[] = 'Your mobile contact number is required for SMS notifications.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        if (empty($_POST['privacy_consent'])) $errors[] = 'You must agree to the Data Privacy Act (RA 10173) statement to proceed.';

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

        $formattedContact = SmsService::getInstance()->formatPhoneNumber($contact) ?: $contact;

        // Create user with contact_number
        $db = Database::getInstance()->getConnection();
        $passHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("
            INSERT INTO users (
                name, email, contact_number, password_hash, role, status, email_verified,
                auth_provider, school_id, created_at, updated_at
            ) VALUES (
                :name, :email, :contact, :pass, 'parent', 'active', 1,
                'local', :school_id, NOW(), NOW()
            )
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'contact' => $formattedContact,
            'pass' => $passHash,
            'school_id' => $schoolId > 0 ? $schoolId : null
        ]);
        $newUserId = (int)$db->lastInsertId();

        // Link parent to learner & generate/link learner LMS credentials
        $claimInfo = $this->masterlistModel->linkParentAccountToLearner($studentId, $newUserId);

        // Send welcome SMS
        if (!empty($formattedContact)) {
            SmsService::getInstance()->send(
                $formattedContact,
                "SignED: Maayong adlaw {$name}! Malampusong na-link ang imong account sa imong anak ({$learner['student_name']}). Makadawat ka na karon og instant text alerts sa bag-ong activities ug leksyon.",
                $name,
                'parent_welcome'
            );
        }

        // Sign in user
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['role'] = 'parent';
        $_SESSION['name'] = $name;
        $_SESSION['user_name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['user_email'] = $email;
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
