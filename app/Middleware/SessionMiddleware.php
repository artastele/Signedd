<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 5
// Last modified: 2026-05-01
// Part of: SPED LMS — Session Management & Timeout

class SessionMiddleware {
    private const TIMEOUT_DURATION = 1800; // 30 minutes default
    private const ENROLLMENT_TIMEOUT = 3600; // 60 minutes for enrollment pages
    private const WARNING_TIME = 300; // 5 minutes warning

    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
            session_start();
        }

        self::checkTimeout();
        self::checkRoleUpdate();
        self::checkEmailVerification();
    }

    private static function checkTimeout() {
        if (isset($_SESSION['user_id'])) {
            $currentTime = time();

            // Determine timeout duration based on current page
            $timeoutDuration = self::getTimeoutDuration();

            // Check if last activity timestamp exists
            if (isset($_SESSION['last_activity'])) {
                $elapsed = $currentTime - $_SESSION['last_activity'];

                // Session expired
                if ($elapsed > $timeoutDuration) {
                    self::destroy();
                    
                    // Get base path
                    $basePath = defined('BASE_PATH') ? BASE_PATH : '';
                    header('Location: ' . $basePath . '/login?timeout=1');
                    exit;
                }
            }

            // Update last activity timestamp
            $_SESSION['last_activity'] = $currentTime;
        }
    }

    /**
     * Get timeout duration based on current page
     */
    private static function getTimeoutDuration() {
        // Get current path
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Extended timeout for enrollment pages
        if (strpos($currentPath, '/enrollment') !== false) {
            return self::ENROLLMENT_TIMEOUT; // 60 minutes
        }
        
        return self::TIMEOUT_DURATION; // 30 minutes default
    }

    /**
     * Check if user's role has been updated in database
     */
    private static function checkRoleUpdate() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            // Check every 10 seconds to detect role changes quickly
            if (!isset($_SESSION['last_role_check']) || (time() - $_SESSION['last_role_check']) > 10) {
                try {
                    require_once __DIR__ . '/../../config/db.php';
                    $db = Database::getInstance()->getConnection();
                    
                    $stmt = $db->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
                    $stmt->execute(['id' => $_SESSION['user_id']]);
                    $user = $stmt->fetch();
                    
                    if ($user && $user['role'] !== $_SESSION['role']) {
                        // Role has changed! Update session
                        $oldRole = $_SESSION['role'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['last_role_check'] = time();
                        
                        // Redirect to appropriate dashboard
                        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
                        $_SESSION['success'] = 'Your role has been updated to ' . ucwords(str_replace('_', ' ', $user['role'])) . '!';
                        header('Location: ' . $basePath . '/dashboard');
                        exit;
                    }
                    
                    $_SESSION['last_role_check'] = time();
                } catch (Exception $e) {
                    // Silently fail - don't break the app if DB check fails
                    error_log('Role update check failed: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Check if email verification is required
     */
    private static function checkEmailVerification() {
        // Skip check if not logged in
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        // Get current path
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/') {
            $currentPath = str_replace($basePath, '', $currentPath);
        }
        $currentPath = '/' . trim($currentPath, '/');

        // Exempt routes (allow access without email verification)
        $exemptRoutes = [
            '/auth/verify-email',
            '/auth/resend-otp',
            '/logout',
            '/auth/google/callback'
        ];

        foreach ($exemptRoutes as $route) {
            if (strpos($currentPath, $route) === 0) {
                return;
            }
        }

        // Check if email is verified
        if (isset($_SESSION['email_verified']) && $_SESSION['email_verified'] === false) {
            // Redirect to verification page
            $redirectPath = $basePath . '/auth/verify-email';
            if ($currentPath !== '/auth/verify-email') {
                header('Location: ' . $redirectPath);
                exit;
            }
        }
    }

    public static function destroy() {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function regenerate() {
        session_regenerate_id(true);
    }

    public static function getTimeRemaining() {
        if (isset($_SESSION['last_activity'])) {
            $timeoutDuration = self::getTimeoutDuration();
            $elapsed = time() - $_SESSION['last_activity'];
            $remaining = $timeoutDuration - $elapsed;
            return max(0, $remaining);
        }
        return self::getTimeoutDuration();
    }

    public static function isWarningTime() {
        return self::getTimeRemaining() <= self::WARNING_TIME;
    }
}
