<?php
// DO NOT ALTER WITHOUT APPROVAL — Route Definitions
// Last modified: 2026-05-01
// Part of: SPED LMS — Routing

// Simple routing function
function route($method, $pattern, $controller, $action, $permission = null) {
    global $path, $requestMethod;
    
    if ($requestMethod !== $method) {
        return false;
    }
    
    // Convert pattern to regex
    $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $pattern);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $path, $matches)) {
        array_shift($matches); // Remove full match
        
        // Check permission if required
        if ($permission !== null) {
            RoleMiddleware::check($permission);
        }
        
        // Load controller
        require_once __DIR__ . '/../app/Controllers/' . $controller . '.php';
        
        // Instantiate and call action
        $controllerInstance = new $controller();
        call_user_func_array([$controllerInstance, $action], $matches);
        exit;
    }
    
    return false;
}

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================

// Home - redirect to login or dashboard
route('GET', '/', 'AuthController', 'index');

// Authentication
route('GET', '/login', 'AuthController', 'showLogin');
route('POST', '/login', 'AuthController', 'login');
route('GET', '/register', 'AuthController', 'showRegister');
route('POST', '/register', 'AuthController', 'register');
route('GET', '/logout', 'AuthController', 'logout');

// File serving (encrypted files)
route('GET', '/file/serve/{path}', 'FileController', 'serve', '*');
route('GET', '/file/download/{path}', 'FileController', 'download', '*');
route('GET', '/file/thumbnail/{path}', 'FileController', 'thumbnail', '*');

// Email Verification
route('GET', '/auth/verify-email', 'AuthController', 'showVerifyEmail');
route('POST', '/auth/verify-email', 'AuthController', 'verifyEmail');
route('POST', '/auth/resend-otp', 'AuthController', 'resendOTP');

// Google OAuth
route('GET', '/auth/google', 'AuthController', 'googleLogin');
route('GET', '/auth/google/callback', 'AuthController', 'googleCallback');

// ============================================
// AUTHENTICATED ROUTES
// ============================================

// Dashboard
route('GET', '/dashboard', 'DashboardController', 'index');

// Notifications (AJAX endpoints)
route('GET', '/notifications/get', 'NotificationController', 'getNotifications');
route('POST', '/notifications/{id}/read', 'NotificationController', 'markAsRead');
route('POST', '/notifications/read-all', 'NotificationController', 'markAllAsRead');
route('POST', '/notifications/{id}/delete', 'NotificationController', 'delete');

// Location API (AJAX endpoints for enrollment form)
route('GET', '/api/locations/provinces', 'LocationController', 'getProvinces');
route('GET', '/api/locations/cities/{province}', 'LocationController', 'getCities');
route('GET', '/api/locations/barangays/{province}/{city}', 'LocationController', 'getBarangays');

// Services (General Information)
route('GET', '/services', 'ServicesController', 'index');

// Role Selection
route('GET', '/role/select', 'RoleController', 'showSelection', 'role.select');
route('POST', '/role/select-parent', 'RoleController', 'selectParent', 'role.select');
route('POST', '/role/submit-staff', 'RoleController', 'submitStaffApplication', 'role.select');

// ============================================
// PARENT ROUTES
// ============================================

// Parent Enrollment Routes
route('GET', '/enrollment', 'EnrollmentController', 'index', 'enrollment.submit');
route('GET', '/enrollment/create', 'EnrollmentController', 'create', 'enrollment.submit');
route('POST', '/enrollment/save-draft', 'EnrollmentController', 'saveDraft', 'enrollment.submit');
route('POST', '/enrollment/submit', 'EnrollmentController', 'submit', 'enrollment.submit');
route('POST', '/enrollment/discard-draft', 'EnrollmentController', 'discardDraft', 'enrollment.submit');
route('POST', '/enrollment/keepalive', 'EnrollmentController', 'keepalive', 'enrollment.submit');
route('GET', '/enrollment/status', 'EnrollmentController', 'status', 'enrollment.track');
route('GET', '/enrollment/view/{id}', 'EnrollmentController', 'view', 'enrollment.view');

// ============================================
// SPED TEACHER ROUTES
// ============================================

// Verification (Process 2)
route('GET', '/verification', 'VerificationController', 'index', 'enrollment.verify');
route('GET', '/verification/{id}', 'VerificationController', 'show', 'enrollment.verify');
route('POST', '/verification/{id}/verify', 'VerificationController', 'verify', 'enrollment.verify');

// Enrollment Review (Process 1 - SPED Teacher Side)
route('GET', '/enrollment/review', 'EnrollmentController', 'review', 'enrollment.verify');
route('GET', '/enrollment/review/{id}', 'EnrollmentController', 'reviewDetail', 'enrollment.verify');
route('POST', '/enrollment/document/approve/{id}', 'EnrollmentController', 'approveDocument', 'enrollment.verify');
route('POST', '/enrollment/document/reject/{id}', 'EnrollmentController', 'rejectDocument', 'enrollment.verify');

// Assessment (Process 3)
route('GET', '/assessment', 'AssessmentController', 'index', 'assessment.manage');
route('GET', '/assessment/conduct/{id}', 'AssessmentController', 'conduct', 'assessment.conduct');
route('POST', '/assessment/submit', 'AssessmentController', 'submit', 'assessment.conduct');
route('GET', '/assessment/view/{id}', 'AssessmentController', 'view', 'assessment.view');
route('POST', '/assessment/{id}/approve', 'AssessmentController', 'approve', 'assessment.manage');
route('POST', '/assessment/{id}/reject', 'AssessmentController', 'reject', 'assessment.manage');
route('GET', '/assessment/{id}/history', 'AssessmentController', 'history', 'assessment.view');

// IEP Implementation (Process 6)
route('GET', '/iep/implementation', 'IEPImplementationController', 'index', 'iep.implement');
route('GET', '/iep/implementation/{id}', 'IEPImplementationController', 'show', 'iep.implement');
route('POST', '/iep/implementation/{id}/materials', 'IEPImplementationController', 'addMaterial', 'learning.materials');

// Learning Activities (Process 7)
route('GET', '/activities', 'LearningActivityController', 'index', 'activity.logs');
route('POST', '/activities/record', 'LearningActivityController', 'record', 'activity.record');

// ============================================
// GUIDANCE ROUTES
// ============================================

// IEP Meeting (Process 4)
route('GET', '/iep/meetings', 'IEPMeetingController', 'index', 'iep.meeting');
route('GET', '/iep/meetings/schedule', 'IEPMeetingController', 'schedule', 'iep.meeting');
route('POST', '/iep/meetings/schedule', 'IEPMeetingController', 'createMeeting', 'iep.meeting');
route('POST', '/iep/meetings/availability', 'IEPMeetingController', 'getAvailability', 'iep.meeting');
route('GET', '/iep/meetings/{id}', 'IEPMeetingController', 'show', 'iep.meeting');
route('POST', '/iep/meetings/upload-calendar', 'IEPMeetingController', 'uploadCalendar', 'iep.meeting');

// IEP P2 Documents (Process 4)
route('GET', '/iep/p2/review', 'IEPDocumentController', 'listP2ForReview', 'iep.sign');
route('GET', '/iep/p2/create/{id}', 'IEPDocumentController', 'createP2', 'iep.create');
route('POST', '/iep/p2/submit', 'IEPDocumentController', 'submitP2', 'iep.create');
route('POST', '/iep/p2/upload', 'IEPDocumentController', 'uploadP2', 'iep.create');
route('POST', '/iep/p2/send-review', 'IEPDocumentController', 'sendP2ForReview', 'iep.create');
route('GET', '/iep/p2/{id}/review', 'IEPDocumentController', 'reviewP2', 'iep.sign');
route('POST', '/iep/p2/review-submit', 'IEPDocumentController', 'submitP2Review', 'iep.sign');

// IEP P3 Documents (Process 5)
route('GET', '/iep/p3/sign', 'IEPDocumentController', 'listP3ForSignature', 'iep.sign');
route('GET', '/iep/p3/create/{id}', 'IEPDocumentController', 'createP3', 'iep.create');
route('POST', '/iep/p3/submit', 'IEPDocumentController', 'submitP3', 'iep.create');
route('POST', '/iep/p3/upload', 'IEPDocumentController', 'uploadP3', 'iep.create');
route('POST', '/iep/p3/send-signature', 'IEPDocumentController', 'sendP3ForSignature', 'iep.create');
route('GET', '/iep/p3/{id}/sign', 'IEPDocumentController', 'signP3', 'iep.sign');
route('POST', '/iep/p3/sign-submit', 'IEPDocumentController', 'submitP3Signature', 'iep.sign');

// ============================================
// PRINCIPAL ROUTES
// ============================================

// IEP Approval (Process 5)
route('GET', '/iep/approval', 'IEPDocumentController', 'approvalQueue', 'iep.approve');
route('POST', '/iep/documents/{id}/approve', 'IEPDocumentController', 'approve', 'iep.approve');

// Staff Role Requests (Principal approves staff)
route('GET', '/principal/staff-requests', 'PrincipalController', 'staffRequests', 'staff.approve');
route('POST', '/principal/staff-requests/{id}/approve', 'PrincipalController', 'approveStaff', 'staff.approve');
route('POST', '/principal/staff-requests/{id}/reject', 'PrincipalController', 'rejectStaff', 'staff.approve');

// ============================================
// ADMIN ROUTES
// ============================================

route('GET', '/admin', 'AdminController', 'index', '*');
route('GET', '/admin/users', 'AdminController', 'users', '*');
route('GET', '/admin/role-requests', 'AdminController', 'roleRequests', '*');
route('POST', '/admin/role-requests/{id}/approve', 'AdminController', 'approveRole', '*');
route('POST', '/admin/role-requests/{id}/reject', 'AdminController', 'rejectRole', '*');
route('GET', '/admin/login-logs', 'AdminController', 'loginLogs', '*');
route('GET', '/admin/activity-logs', 'AdminController', 'activityLogs', '*');

// System Settings
route('GET', '/admin/settings', 'AdminController', 'settings', '*');
route('POST', '/admin/settings/update', 'AdminController', 'updateSettings', '*');

// User Management
route('GET', '/admin/manage-users', 'AdminController', 'manageUsers', '*');
route('POST', '/admin/user/change-role', 'AdminController', 'changeUserRole', '*');
route('POST', '/admin/user/toggle-status', 'AdminController', 'toggleUserStatus', '*');
route('POST', '/admin/user/delete', 'AdminController', 'deleteUser', '*');
route('GET', '/admin/user/details/{id}', 'AdminController', 'getUserDetails', '*');

// Activity Logs Export
route('GET', '/admin/activity-logs/export', 'AdminController', 'exportActivityLogs', '*');

// ============================================
// 404 NOT FOUND
// ============================================

http_response_code(404);
echo "404 - Page Not Found";
