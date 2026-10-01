<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 1
// Last modified: 2026-05-04
// Part of: SPED LMS — Authentication Controller

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/RateLimitHelper.php';
require_once __DIR__ . '/../Helpers/CSRFHelper.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Index - redirect to login or dashboard
     */
    public function index() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/dashboard');
        } else {
            header('Location: ' . $basePath . '/login');
        }
        exit;
    }

    /**
     * Show login form
     */
    public function showLogin() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/dashboard');
            exit;
        }

        require_once __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Process login
     */
    public function login() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Verify CSRF token
        try {
            CSRFHelper::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Security validation failed. Please try again.';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        $emailOrLrn = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Validation
        if (empty($emailOrLrn) || empty($password)) {
            $_SESSION['error'] = 'Email/LRN and password are required.';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Check if input is Student ID (YYYYNNNN) or legacy LRN (12 digits)
        $email = $emailOrLrn;
        if (preg_match('/^\d{8}$/', $emailOrLrn)) {
            $email = 'learner_' . $emailOrLrn . '@spedlms.local';
            error_log("Student ID login detected: $emailOrLrn -> $email");
        } elseif (preg_match('/^\d{12}$/', $emailOrLrn)) {
            $email = 'learner_' . $emailOrLrn . '@spedlms.local';
            error_log("Legacy LRN login detected: $emailOrLrn -> $email");
        }

        // Check rate limiting
        $rateLimit = RateLimitHelper::checkLoginAttempts($email, $ipAddress);
        if (!$rateLimit['allowed']) {
            $_SESSION['error'] = $rateLimit['message'];
            RateLimitHelper::recordLoginAttempt($email, false, $ipAddress);
            $this->userModel->logLoginAttempt($email, 'failure', $ipAddress);
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Find user by processed email
        $user = $this->userModel->findByEmail($email);

        // If not found and input was altered (e.g. learner_xxx was attempted), try raw input directly
        if (!$user && $email !== $emailOrLrn) {
            $user = $this->userModel->findByEmail($emailOrLrn);
        }

        // If still not found, check if it matches a Student ID or LRN in student records
        if (!$user) {
            $user = $this->userModel->findByStudentIdentifier($emailOrLrn);
            if ($user && !empty($user['email'])) {
                $email = $user['email'];
            }
        }

        if (!$user) {
            RateLimitHelper::recordLoginAttempt($email, false, $ipAddress);
            $this->userModel->logLoginAttempt($email, 'failure', $ipAddress);
            $_SESSION['error'] = 'Invalid email or password.';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Verify password
        if (!$this->userModel->verifyPassword($user, $password)) {
            RateLimitHelper::recordLoginAttempt($email, false, $ipAddress);
            $this->userModel->logLoginAttempt($email, 'failure', $ipAddress);
            $_SESSION['error'] = 'Invalid email or password.';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            RateLimitHelper::recordLoginAttempt($email, false, $ipAddress);
            $this->userModel->logLoginAttempt($email, 'failure', $ipAddress);
            $_SESSION['error'] = 'Your account is not active. Please contact the administrator.';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Login successful
        RateLimitHelper::recordLoginAttempt($email, true, $ipAddress);
        RateLimitHelper::clearLoginAttempts($email);
        $this->userModel->logLoginAttempt($email, 'success', $ipAddress);

        // Regenerate session ID for security
        SessionMiddleware::regenerate();

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['name'] = $user['name'];  // Add for compatibility
        $_SESSION['email'] = $user['email'];  // Add for compatibility
        $_SESSION['role'] = $user['role'];
        $_SESSION['email_verified'] = $this->userModel->isEmailVerified($user['id']);
        $_SESSION['last_activity'] = time();

        // Check if email is verified
        if (!$_SESSION['email_verified']) {
            // Generate new OTP
            $otp = $this->userModel->generateOTP($user['id']);
            $_SESSION['dev_otp_code'] = $otp;
            
            if (class_exists('MailHelper')) {
                try {
                    MailHelper::sendOTPEmail($email, $user['name'], $otp);
                } catch (\Throwable $e) {
                    error_log("Failed to send OTP email: " . $e->getMessage());
                }
            }
            
            $_SESSION['success'] = 'Please verify your email to continue. A verification code has been generated.';
            header('Location: ' . $basePath . '/auth/verify-email');
            exit;
        }

        // Redirect to dashboard
        header('Location: ' . $basePath . '/dashboard');
        exit;
    }

    /**
     * Show registration form
     */
    public function showRegister() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/dashboard');
            exit;
        }

        require_once __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Process registration
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/register');
            exit;
        }

        // Verify CSRF token
        try {
            CSRFHelper::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Security validation failed. Please try again.';
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/register');
            exit;
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Check registration rate limiting
        $rateLimit = RateLimitHelper::checkRegistrationAttempts($email, $ipAddress);
        if (!$rateLimit['allowed']) {
            $_SESSION['error'] = $rateLimit['message'];
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/register');
            exit;
        }

        // Validation
        $errors = [];

        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } elseif ($this->userModel->emailExists($email)) {
            $errors[] = 'Email is already registered.';
        }

        if (empty($contactNumber)) {
            $errors[] = 'Contact number is required.';
        } elseif (!preg_match('/^[0-9]{11}$/', $contactNumber)) {
            $errors[] = 'Contact number must be 11 digits.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (!$this->validatePassword($password)) {
            $errors[] = 'Password must be at least 8 characters with 1 uppercase, 1 number, and 1 special character.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_first_name'] = $firstName;
            $_SESSION['old_middle_name'] = $middleName;
            $_SESSION['old_last_name'] = $lastName;
            $_SESSION['old_suffix'] = $suffix;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_contact'] = $contactNumber;
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/register');
            exit;
        }

        // Create full name
        $fullName = $firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName . ($suffix ? ' ' . $suffix : '');

        // Create user
        $userId = $this->userModel->create($fullName, $firstName, $middleName, $lastName, $suffix, $email, $contactNumber, $password);

        if ($userId) {
            // Record registration attempt
            RateLimitHelper::recordRegistrationAttempt($email, $ipAddress);

            // Generate and send OTP
            $otp = $this->userModel->generateOTP($userId);
            $_SESSION['dev_otp_code'] = $otp;
            
            if (class_exists('MailHelper')) {
                try {
                    MailHelper::sendOTPEmail($email, $fullName, $otp);
                } catch (\Throwable $e) {
                    error_log("Failed to send OTP email: " . $e->getMessage());
                }
            }
            
            // Set session for verification
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = 'user';
            $_SESSION['email_verified'] = false;
            $_SESSION['last_activity'] = time();
            
            $_SESSION['success'] = 'Registration successful! Verification code generated.';
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/auth/verify-email');
        } else {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/register');
        }
        exit;
    }

    /**
     * Logout
     */
    public function logout() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        SessionMiddleware::destroy();
        header('Location: ' . $basePath . '/login');
        exit;
    }

    /**
     * Show email verification page
     */
    public function showVerifyEmail() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        
        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        // If already verified, redirect to dashboard
        if ($this->userModel->isEmailVerified($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/dashboard');
            exit;
        }

        // Check if user has an unexpired verification token
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT email_verification_token, email_verification_expires FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            $needsNewCode = empty($tokenData['email_verification_token']) 
                || empty($tokenData['email_verification_expires']) 
                || (strtotime($tokenData['email_verification_expires']) < time());

            if ($needsNewCode) {
                $otp = $this->userModel->generateOTP($_SESSION['user_id']);
                if (class_exists('MailHelper')) {
                    try {
                        MailHelper::sendOTPEmail($_SESSION['user_email'], $_SESSION['user_name'] ?? 'User', $otp);
                    } catch (\Throwable $e) {
                        error_log("Failed to send OTP email: " . $e->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("Verification check error: " . $e->getMessage());
        }
        unset($_SESSION['dev_otp_code']);
        
        require_once __DIR__ . '/../Views/auth/verify_email.php';
    }

    /**
     * Process email verification
     */
    public function verifyEmail() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $basePath . '/auth/verify-email');
            exit;
        }
        
        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        // Get OTP from form
        $otp = '';
        for ($i = 1; $i <= 6; $i++) {
            $otp .= $_POST['otp' . $i] ?? '';
        }
        
        if (strlen($otp) !== 6) {
            $_SESSION['error'] = 'Please enter all 6 digits.';
            header('Location: ' . $basePath . '/auth/verify-email');
            exit;
        }
        
        // Verify OTP
        $result = $this->userModel->verifyOTP($_SESSION['user_id'], $otp);
        
        if ($result['success']) {
            // Update session
            $_SESSION['email_verified'] = true;
            unset($_SESSION['dev_otp_code']);
            
            // Send welcome email (non-blocking / error-safe)
            if (class_exists('MailHelper')) {
                try {
                    MailHelper::sendWelcomeEmail($_SESSION['user_email'], $_SESSION['user_name']);
                } catch (\Throwable $e) {
                    error_log("Failed to send welcome email: " . $e->getMessage());
                }
            }
            
            // Create notification
            if (class_exists('NotificationModel')) {
                require_once __DIR__ . '/../Models/NotificationModel.php';
                $notificationModel = new NotificationModel();
                $notificationModel->create(
                    $_SESSION['user_id'],
                    'email_verified',
                    'Email Verified',
                    'Your email has been successfully verified. Welcome to SPED LMS!',
                    null
                );
            }
            
            $_SESSION['success'] = 'Email verified successfully! Welcome to SPED LMS.';
            header('Location: ' . $basePath . '/dashboard');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: ' . $basePath . '/auth/verify-email');
        }
        exit;
    }

    /**
     * Resend OTP
     */
    public function resendOTP() {
        header('Content-Type: application/json');
        
        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Generate new OTP
        $otp = $this->userModel->generateOTP($_SESSION['user_id']);
        unset($_SESSION['dev_otp_code']);
        
        // Send email
        $sent = false;
        if (class_exists('MailHelper')) {
            try {
                $sent = MailHelper::sendOTPEmail($_SESSION['user_email'], $_SESSION['user_name'] ?? 'User', $otp);
            } catch (\Throwable $e) {
                error_log("Failed to resend OTP email: " . $e->getMessage());
            }
        }
        echo json_encode([
            'success' => true,
            'message' => 'A new 6-digit verification code has been sent to your email.'
        ]);
        exit;
    }

    /**
     * Google OAuth login
     */
    public function googleLogin() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        
        // Check if Google client library is available
        if (!class_exists('Google_Client')) {
            $_SESSION['error'] = 'Google Sign-In is not configured. Please use email/password registration.';
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        // Check if credentials are configured
        if (empty(env('GOOGLE_CLIENT_ID')) || empty(env('GOOGLE_CLIENT_SECRET'))) {
            $_SESSION['error'] = 'Google Sign-In is not configured. Please use email/password registration.';
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        try {
            $client = new Google_Client();
            if (class_exists('\GuzzleHttp\Client')) {
                $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            
            $redirectUri = $this->getGoogleRedirectUri();
            $client->setRedirectUri($redirectUri);

            $client->addScope('email');
            $client->addScope('profile');
            
            // Generate state token for CSRF protection
            $_SESSION['google_state'] = bin2hex(random_bytes(16));
            $client->setState($_SESSION['google_state']);
            
            // Redirect to Google
            $authUrl = $client->createAuthUrl();
            header('Location: ' . $authUrl);
            exit;
        } catch (Exception $e) {
            error_log('Google OAuth error: ' . $e->getMessage());
            $_SESSION['error'] = 'Google Sign-In failed: ' . htmlspecialchars($e->getMessage());
            header('Location: ' . $basePath . '/login');
            exit;
        }
    }

    /**
     * Google OAuth callback
     */
    public function googleCallback() {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        
        // Verify state token
        if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['google_state'] ?? '')) {
            $_SESSION['error'] = 'Invalid state parameter. Please try again.';
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        // Check for error
        if (isset($_GET['error'])) {
            $_SESSION['error'] = 'Google Sign-In was cancelled.';
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        // Get authorization code
        if (!isset($_GET['code'])) {
            $_SESSION['error'] = 'Authorization code not received.';
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        try {
            $client = new Google_Client();
            if (class_exists('\GuzzleHttp\Client')) {
                $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

            $redirectUri = $this->getGoogleRedirectUri();
            $client->setRedirectUri($redirectUri);

            
            // Exchange code for access token
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            
            if (isset($token['error'])) {
                throw new Exception($token['error_description'] ?? $token['error']);
            }
            
            $client->setAccessToken($token);
            
            // Get user info
            $oauth = new Google_Service_Oauth2($client);
            $userInfo = $oauth->userinfo->get();
            
            $googleId = $userInfo->id;
            $email = $userInfo->email;
            $name = $userInfo->name;
            $firstName = $userInfo->givenName ?? '';
            $lastName = $userInfo->familyName ?? '';
            $profilePicture = $userInfo->picture ?? '';
            
            // Check if user exists by Google ID
            $user = $this->userModel->findByGoogleId($googleId);
            
            if ($user) {
                // User exists - log them in
                $this->loginUser($user);
                header('Location: ' . $basePath . '/dashboard');
                exit;
            }
            
            // Check if user exists by email
            $user = $this->userModel->findByEmail($email);
            
            if ($user) {
                // Link Google account to existing user
                $this->userModel->linkGoogleAccount($user['id'], $googleId, $profilePicture);
                $this->loginUser($user);
                $_SESSION['success'] = 'Google account linked successfully!';
                header('Location: ' . $basePath . '/dashboard');
                exit;
            }
            
            // Create new user from Google
            $userId = $this->userModel->createFromGoogle($googleId, $email, $name, $firstName, $lastName, $profilePicture);
            
            if ($userId) {
                // Send welcome email
                if (class_exists('MailHelper')) {
                    MailHelper::sendWelcomeEmail($email, $name);
                }
                
                // Log in the new user
                $newUser = $this->userModel->findById($userId);
                $this->loginUser($newUser);
                
                $_SESSION['success'] = 'Welcome to SPED LMS! Please select your role to continue.';
                header('Location: ' . $basePath . '/dashboard');
            } else {
                $_SESSION['error'] = 'Failed to create account. Please try again.';
                header('Location: ' . $basePath . '/register');
            }
            
        } catch (Exception $e) {
            error_log('Google OAuth error: ' . $e->getMessage());
            $_SESSION['error'] = 'Google Sign-In failed: ' . htmlspecialchars($e->getMessage());
            header('Location: ' . $basePath . '/login');
        }
        
        exit;
    }

    /**
     * Helper method to log in a user
     */
    private function loginUser($user) {
        SessionMiddleware::regenerate();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['name'] = $user['name'];  // Add for compatibility
        $_SESSION['email'] = $user['email'];  // Add for compatibility
        $_SESSION['role'] = $user['role'];
        $_SESSION['email_verified'] = (bool)($user['email_verified'] ?? false);
        $_SESSION['last_activity'] = time();
        
        // Log successful login
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->userModel->logLoginAttempt($user['email'], 'success', $ipAddress);
    }

    /**
     * Validate password strength
     */
    private function validatePassword($password) {
        // Min 8 chars, 1 uppercase, 1 number, 1 special character
        if (strlen($password) < 8) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        return true;
    }

    /**
     * Helper to build exact Google OAuth Redirect URI
     */
    private function getGoogleRedirectUri() {
        $redirectUri = env('GOOGLE_REDIRECT_URI');
        if (!empty($redirectUri)) {
            return $redirectUri;
        }
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        return $scheme . '://' . $host . $basePath . '/auth/google/callback';
    }
}

