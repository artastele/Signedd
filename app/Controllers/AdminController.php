<?php
// DO NOT ALTER WITHOUT APPROVAL — Admin Features
// Last modified: 2026-05-01
// Part of: SPED LMS — Admin Controller

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/RoleRequestModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
if (file_exists(__DIR__ . '/../Helpers/MailHelper.php')) {
    require_once __DIR__ . '/../Helpers/MailHelper.php';
}

class AdminController {
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
     * Admin dashboard
     */
    public function index() {
        $userName = $_SESSION['user_name'];
        require_once __DIR__ . '/../Views/dashboard/admin.php';
    }

    /**
     * Manage users
     */
    public function users() {
        $users = $this->userModel->getAllUsers();
        require_once __DIR__ . '/../Views/admin/users.php';
    }

    /**
     * Role requests list (Admin - only Principal requests)
     */
    public function roleRequests() {
        // Admin only sees Principal role requests
        $requests = $this->roleRequestModel->getPendingByApprover('admin');
        $allRequests = $this->roleRequestModel->getAll();
        
        // Filter to show only principal requests
        $requests = array_filter($allRequests, function($req) {
            return $req['requested_role'] === 'principal';
        });
        
        require_once __DIR__ . '/../Views/admin/role_requests.php';
    }

    /**
     * Approve role request
     */
    public function approveRole($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/admin/role-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/admin/role-requests');
            exit;
        }

        $reviewNote = trim($_POST['review_note'] ?? '');

        // Update role request status
        $this->roleRequestModel->updateStatus(
            $requestId,
            'approved',
            $_SESSION['user_id'],
            $reviewNote
        );

        // Update user role
        $this->userModel->updateRole($request['user_id'], $request['requested_role']);

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

        $_SESSION['success'] = 'Role request approved successfully!';
        header('Location: ' . $this->basePath . '/admin/role-requests');
        exit;
    }

    /**
     * Reject role request
     */
    public function rejectRole($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/admin/role-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/admin/role-requests');
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

        $_SESSION['success'] = 'Role request rejected.';
        header('Location: ' . $this->basePath . '/admin/role-requests');
        exit;
    }

    /**
     * View login attempt logs
     */
    public function loginLogs() {
        // Get filter parameters
        $result = $_GET['result'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 50);
        $search = trim($_GET['search'] ?? '');

        // Build query
        $sql = "SELECT * FROM login_log WHERE 1=1";
        $params = [];

        if ($result !== 'all') {
            $sql .= " AND result = :result";
            $params['result'] = $result;
        }

        if (!empty($search)) {
            $sql .= " AND email LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY attempted_at DESC LIMIT :limit";

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $logs = $stmt->fetchAll();

        // Get statistics
        $statsStmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN result = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN result = 'failure' THEN 1 ELSE 0 END) as failed
            FROM login_log
            WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stats = $statsStmt->fetch();

        require_once __DIR__ . '/../Views/admin/login_logs.php';
    }

    /**
     * View activity logs
     */
    public function activityLogs() {
        // Get filter parameters
        $userId = $_GET['user_id'] ?? null;
        $actionType = $_GET['action_type'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 50);
        $search = trim($_GET['search'] ?? '');

        // Build query
        $sql = "
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM activity_log al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($userId) {
            $sql .= " AND al.user_id = :user_id";
            $params['user_id'] = $userId;
        }

        if ($actionType !== 'all') {
            $sql .= " AND al.action_type = :action_type";
            $params['action_type'] = $actionType;
        }

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR al.details LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit";

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $logs = $stmt->fetchAll();

        // Get unique action types for filter
        $actionsStmt = $db->query("SELECT DISTINCT action_type FROM activity_log ORDER BY action_type");
        $actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);

        require_once __DIR__ . '/../Views/admin/activity_logs.php';
    }
}
