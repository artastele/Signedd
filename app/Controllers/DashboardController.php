<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — Dashboard Controller

class DashboardController {
    public function index() {
        // User must be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $role = $_SESSION['role'];
        $userName = $_SESSION['user_name'];
        $userEmail = $_SESSION['user_email'];
        $userId = $_SESSION['user_id'];

        // Check for pending and rejected role requests
        require_once __DIR__ . '/../Models/RoleRequestModel.php';
        $roleRequestModel = new RoleRequestModel();
        $pendingRequest = $roleRequestModel->getPendingByUserId($userId);
        $rejectedRequest = $roleRequestModel->getLatestRejectedByUserId($userId);
        $applicationHistory = $roleRequestModel->getAllByUserId($userId);

        // Route to appropriate dashboard based on role
        switch ($role) {
            case 'admin':
                require_once __DIR__ . '/../Views/dashboard/admin.php';
                break;
            case 'parent':
                // Fetch enrollment data for parent
                require_once __DIR__ . '/../Models/EnrollmentModel.php';
                $enrollmentModel = new EnrollmentModel();
                $enrollments = $enrollmentModel->getEnrollmentsWithStats($userId);
                $stats = $enrollmentModel->getParentStats($userId);
                
                require_once __DIR__ . '/../Views/dashboard/parent.php';
                break;
            case 'sped_teacher':
                require_once __DIR__ . '/../Views/dashboard/teacher.php';
                break;
            case 'guidance':
                require_once __DIR__ . '/../Views/dashboard/guidance.php';
                break;
            case 'principal':
                require_once __DIR__ . '/../Views/dashboard/principal.php';
                break;
            case 'master_teacher':
                require_once __DIR__ . '/../Views/dashboard/master_teacher.php';
                break;
            case 'general':
            case 'user':
            default:
                require_once __DIR__ . '/../Views/dashboard/general.php';
                break;
        }
    }
}
