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
                require_once __DIR__ . '/../Models/UserModel.php';
                require_once __DIR__ . '/../Models/RoleRequestModel.php';
                require_once __DIR__ . '/../Models/SchoolModel.php';

                $userModelObj = new UserModel();
                $roleReqModelObj = new RoleRequestModel();
                $schoolModelObj = new SchoolModel();

                $userAnalytics = $userModelObj->getUserRoleAnalytics();
                $pendingRoleRequests = $roleReqModelObj->getPendingByApprover('admin');
                $approvedSchoolsCount = count($schoolModelObj->getAllSchools());

                require_once __DIR__ . '/../Views/dashboard/admin.php';
                break;
            case 'parent':
                // Fetch enrollment data and registered schools for parent
                require_once __DIR__ . '/../Models/EnrollmentModel.php';
                require_once __DIR__ . '/../Models/SchoolModel.php';
                require_once __DIR__ . '/../Models/MasterlistModel.php';
                $enrollmentModel = new EnrollmentModel();
                $schoolModelObj  = new SchoolModel();
                $masterlistModel = new MasterlistModel();

                $enrollments    = $enrollmentModel->getEnrollmentsWithStats($userId);
                $stats          = $enrollmentModel->getParentStats($userId);
                $allSchools     = $schoolModelObj->getAllSchools();
                $parentLearners = $masterlistModel->getParentLearnerAccounts($userId);
                
                require_once __DIR__ . '/../Views/dashboard/parent.php';
                break;
            case 'learner':
                // Redirect to the LMS learner dashboard (Process 7)
                header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/learning/dashboard');
                exit;
            case 'sped_teacher':
                // Fetch pending enrollments for SPED teacher (school pool)
                require_once __DIR__ . '/../Models/UserModel.php';
                require_once __DIR__ . '/../Models/EnrollmentModel.php';
                require_once __DIR__ . '/../Models/StudentModel.php';
                require_once __DIR__ . '/../Models/IEPMeetingModel.php';

                $userModel = new UserModel();
                $currentUser = $userModel->findById($userId);
                $schoolId = $currentUser['school_id'] ?? null;

                $enrollmentModel = new EnrollmentModel();
                $pendingEnrollments = $enrollmentModel->getPendingPoolForSchool($schoolId);
                $pendingCount = count($pendingEnrollments);

                $studentModel = new StudentModel();
                $myEnrolledStudents = $studentModel->getByTeacher($userId);
                $verifiedStudentsCount = count($myEnrolledStudents);

                $iepMeetingModel = new IEPMeetingModel();
                $recurringAvailability = $iepMeetingModel->getRecurringAvailability($userId);
                $currentMonthExceptions = $iepMeetingModel->getExceptions($userId, date('Y-m-01'), date('Y-m-t'));

                $assessmentsDoneCount = 0;
                $activeIepsCount = 0;

                try {
                    require_once __DIR__ . '/../../config/db.php';
                    $db = Database::getInstance()->getConnection();
                    if ($verifiedStudentsCount === 0) {
                        $stmt = $db->prepare("SELECT COUNT(*) FROM student_records WHERE assigned_teacher_id = :teacher_id OR verified_by = :teacher_id_check");
                        $stmt->execute(['teacher_id' => $userId, 'teacher_id_check' => $userId]);
                        $verifiedStudentsCount = (int) $stmt->fetchColumn();
                    }

                    $assessmentsDoneCount = (int) $db->query("SELECT COUNT(*) FROM assessment_records WHERE status IN ('finalized', 'approved')")->fetchColumn();
                    $activeIepsCount = (int) $db->query("SELECT COUNT(*) FROM iep_records WHERE status IN ('signed', 'signing')")->fetchColumn();
                } catch (PDOException $e) {
                    error_log('DashboardController: teacher counter query failed - ' . $e->getMessage());
                }
                
                // Fetch learners for progress tracker widget (Process 6/7 tables may not exist yet)
                $learners = [];
                $draftsCount = 0;
                try {
                    require_once __DIR__ . '/../Models/LessonPlanModel.php';
                    $lpModel = new LessonPlanModel();
                    $learners = $lpModel->getLearnersForTeacher($userId);
                    $draftsCount = $lpModel->countDraftForTeacher($userId);
                } catch (PDOException $e) {
                    error_log('DashboardController: LessonPlanModel tables not ready — ' . $e->getMessage());
                } catch (Exception $e) {
                    error_log('DashboardController: LessonPlanModel error — ' . $e->getMessage());
                }
                
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
            case 'general_teacher':
                $assignedIepsCount = 0;
                $activeITGPs = 0;
                $submittedGrades = 0;
                
                try {
                    require_once __DIR__ . '/../../config/db.php';
                    $db = Database::getInstance()->getConnection();
                    
                    // Simple stats for visual feedback
                    $assignedIepsCount = (int) $db->query("SELECT COUNT(*) FROM iep_records WHERE status IN ('signed', 'locked')")->fetchColumn();
                    // We can refine these queries later if specific tracking is needed
                } catch (PDOException $e) {
                    error_log('DashboardController: general_teacher stats query failed - ' . $e->getMessage());
                }

                require_once __DIR__ . '/../Views/dashboard/general_teacher.php';
                break;
            case 'general':
            case 'user':
            default:
                require_once __DIR__ . '/../Models/SystemSettingsModel.php';
                $sysModel = new SystemSettingsModel();
                $enrollmentSettings = $sysModel->getEnrollmentSettings();
                require_once __DIR__ . '/../Views/dashboard/general.php';
                break;
        }
    }

    /**
     * Dismiss LRN notification (AJAX endpoint)
     */
    public function dismissLrnNotification() {
        header('Content-Type: application/json');
        
        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Get enrollment ID from request
        $input = json_decode(file_get_contents('php://input'), true);
        $enrollmentId = $input['enrollment_id'] ?? null;
        
        if (!$enrollmentId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Enrollment ID required']);
            exit;
        }
        
        // Store dismissal in session
        $_SESSION['lrn_dismissed_' . $enrollmentId] = true;
        
        echo json_encode(['success' => true, 'message' => 'Notification dismissed']);
        exit;
    }

    /**
     * Update a child's LMS login credentials (username and/or password) by the parent.
     */
    public function updateChildCredentials() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $basePath = defined('BASE_PATH') ? BASE_PATH : '';

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Verify CSRF
        require_once __DIR__ . '/../Helpers/CSRFHelper.php';
        try {
            CSRFHelper::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Invalid session or token expired. Please try again.';
            header('Location: ' . $basePath . '/dashboard');
            exit;
        }

        $parentId = (int)$_SESSION['user_id'];
        $childRecordId = (int)($_POST['child_record_id'] ?? $_POST['student_record_id'] ?? 0);
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$childRecordId) {
            $_SESSION['error'] = 'Palihug pagpili og bata nga i-update.';
            header('Location: ' . $basePath . '/dashboard');
            exit;
        }

        if (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Ang mga password wala magkatugma (Passwords do not match).';
            header('Location: ' . $basePath . '/dashboard');
            exit;
        }

        require_once __DIR__ . '/../Models/MasterlistModel.php';
        $masterlistModel = new MasterlistModel();
        $result = $masterlistModel->updateChildCredentials($parentId, $childRecordId, $newUsername, $newPassword);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: ' . $basePath . '/dashboard');
        exit;
    }
}

