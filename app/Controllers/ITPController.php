<?php
// Part of: SignED — Process 11 Individual Transition Plan
// Last modified: 2026-06-16
// Part of: SPED LMS — Process 11 Controller

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/TransitionWorkflowModel.php';

class ITPController {
    private TransitionWorkflowModel $model;
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
        $this->userId = (int) $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model = new TransitionWorkflowModel();
    }

    public function index(string $iepId): void {
        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        $workflow = $this->model->getWorkflow($iepId);

        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $readiness = $workflow['readiness'] ?? null;
        if (!$readiness || $readiness['status'] !== 'finalized') {
            $_SESSION['error'] = 'Transition Readiness must be finalized before initiating the Individual Transition Plan (Process 11).';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $itp = $workflow['itp'] ?? null;

        // RBAC check
        if ($itp && $itp['status'] === 'finalized') {
            RoleMiddleware::check('itp.view');
        } else {
            if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'admin'], true)) {
                $_SESSION['error'] = 'You do not have permission to view or edit this ITP draft.';
                header('Location: ' . $this->basePath . '/iep');
                exit;
            }
        }

        $parentSignature = null;
        $canSignAsParent = false;
        $incompleteMembers = [];

        if ($itp) {
            $personalInfo = $itp['learner_information'] ?? [];
            $teamMembers = $this->model->getTeamMembers((int)$itp['id']);
            $parentSignature = $this->model->getParentSignature((int)$itp['id']);
            
            // Check if current user is parent and can sign
            if ($this->userRole === 'parent') {
                $parentMember = null;
                foreach ($teamMembers as $m) {
                    if ($m['role'] === 'parent_guardian') {
                        $parentMember = $m;
                        break;
                    }
                }
                $isAssignedParent = ($parentMember && (int)$parentMember['assigned_user_id'] === $this->userId);
                $studentParentId = $this->model->getParentIdForStudent((int)$ctx['student_id']);
                $isLinkedParent = ($studentParentId !== null && (int)$studentParentId === $this->userId);
                $canSignAsParent = ($isAssignedParent || $isLinkedParent);
            }

            // Find incomplete transition team members
            foreach ($teamMembers as $m) {
                if ($m['status'] === 'pending') {
                    $incompleteMembers[] = $m;
                }
            }
        } else {
            $personalInfo = $this->model->getStudentPersonalInfoForItp((int)$ctx['student_id']);
            $teamMembers = [];
        }

        $suggestedPointOfEntry = '';
        if (!$itp) {
            $suggestedPointOfEntry = $this->suggestPointOfEntry($readiness['readiness_result'] ?? '');
        } else {
            $suggestedPointOfEntry = $itp['point_of_entry'] ?? '';
        }

        // Fetch eligible users for roles dropdowns
        $coordinators = $this->model->getActiveUsersByRoles(['sped_teacher', 'master_teacher']);
        $schoolHeads = $this->model->getActiveUsersByRoles(['principal']);
        $guidanceTeachers = $this->model->getActiveUsersByRoles(['guidance']);
        $parents = $this->model->getActiveUsersByRoles(['parent']);
        $learners = $this->model->getActiveUsersByRoles(['learner']);
        $linkagesUsers = $this->model->getActiveUsersByRoles(['sped_teacher', 'master_teacher', 'guidance', 'principal', 'user', 'admin']);

        // Load narrative items and recommendations
        $narrativeItems = [];
        $recBeginning = '';
        $programMatrix = [];
        $recEnd = '';
        if ($itp) {
            $rawNarratives = $this->model->getNarrativeItems((int)$itp['id']);
            // Group by section for convenience in views
            foreach ($rawNarratives as $narrative) {
                $narrativeItems[$narrative['section']][] = $narrative['item_text'];
            }
            $recBeginning = $this->model->getRecommendation((int)$itp['id'], 'beginning_of_sy') ?? '';
            $programMatrix = $this->model->getProgramMatrix((int)$itp['id']);
            $recEnd = $this->model->getRecommendation((int)$itp['id'], 'end_of_sy') ?? '';
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        $iep = $ctx;

        require_once __DIR__ . '/../Views/itp/index.php';
    }

    private function suggestPointOfEntry(string $readinessResult): string {
        switch ($readinessResult) {
            case 'Ready for Inclusion':
                return 'Transition from SPED Center/SPED Classes to Inclusion Classes';
            case 'Needs More Support':
                return 'Transition level from one class to another in the same grade level';
            case 'Not Yet Ready':
                return 'Transition from home to school';
            case 'For Re-evaluation':
            default:
                return 'Transition from school to functional life';
        }
    }

    public function save(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $readiness = $workflow['readiness'] ?? null;
        if (!$readiness || $readiness['status'] !== 'finalized') {
            $_SESSION['error'] = 'Transition Readiness must be finalized first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        $itp = $workflow['itp'] ?? null;
        if ($itp && $itp['status'] === 'finalized') {
            $_SESSION['error'] = 'This ITP is finalized and locked.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        $schoolYear = $_POST['school_year'] ?? $ctx['school_year'] ?? '';
        $pointOfEntry = $_POST['point_of_entry'] ?? '';

        $learnerInfo = [
            'student_name' => trim($_POST['student_name'] ?? ''),
            'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
            'lrn' => trim($_POST['lrn'] ?? ''),
            'father_name' => trim($_POST['father_name'] ?? ''),
            'mother_name' => trim($_POST['mother_name'] ?? ''),
            'level_of_education' => trim($_POST['level_of_education'] ?? ''),
            'previous_school' => trim($_POST['previous_school'] ?? ''),
            'religion' => trim($_POST['religion'] ?? ''),
            'years_in_school' => trim($_POST['years_in_school'] ?? ''),
            'gender' => trim($_POST['gender'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'contact_no' => trim($_POST['contact_no'] ?? ''),
            'exceptionality_type' => $_POST['exceptionality_type'] ?? 'Without Assessment',
            'exceptionality_assessment' => trim($_POST['exceptionality_assessment'] ?? ''),
        ];

        $this->model->saveItpPartI(
            (int)$ctx['student_id'],
            (int)$readiness['id'],
            $this->userId,
            [
                'school_year' => $schoolYear,
                'point_of_entry' => $pointOfEntry,
                'learner_information' => $learnerInfo,
                'status' => 'in_progress'
            ]
        );

        $_SESSION['success'] = 'Individual Transition Plan saved successfully.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
        exit;
    }

    public function assignTeam(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itp = $workflow['itp'] ?? null;
        if (!$itp) {
            $_SESSION['error'] = 'Individual Transition Plan must be created first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        if ($itp['status'] === 'finalized') {
            $_SESSION['error'] = 'This ITP is finalized and locked.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        $role = $_POST['role'] ?? '';
        $assignedUserId = !empty($_POST['assigned_user_id']) ? (int)$_POST['assigned_user_id'] : null;
        $notApplicable = !empty($_POST['not_applicable']) ? '1' : null;

        if (empty($role)) {
            $_SESSION['error'] = 'Role must be specified.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        $this->model->assignTeamMember((int)$itp['id'], $role, $assignedUserId, $notApplicable);

        // If a user was assigned and NOT marked as not_applicable, send notification
        if ($assignedUserId !== null && $notApplicable === null) {
            // Find the team member row ID for the email/notification link
            $members = $this->model->getTeamMembers((int)$itp['id']);
            $memberId = null;
            foreach ($members as $m) {
                if ($m['role'] === $role) {
                    $memberId = (int)$m['id'];
                    break;
                }
            }

            if ($memberId) {
                $user = $this->model->getUser($assignedUserId);
                if ($user) {
                    // Create system notification
                    require_once __DIR__ . '/../Models/NotificationModel.php';
                    $notifModel = new NotificationModel();
                    $notifTitle = 'Transition Team Assignment';
                    $notifMsg = "You have been added to {$ctx['student_name']}'s Transition Team as " . ucwords(str_replace('_', ' ', $role)) . ". Please fill in your information.";
                    $notifModel->create($assignedUserId, 'iep', $notifTitle, $notifMsg, ['itp_member_id' => $memberId]);

                    // Send email
                    require_once __DIR__ . '/../Helpers/MailHelper.php';
                    $appUrl = getenv('APP_URL') ?: 'http://localhost';
                    $link = $appUrl . $this->basePath . '/itp-team/edit/' . $memberId;
                    $subject = 'Transition Team Invitation - SPED LMS';
                    $htmlBody = "
                        <h2>Transition Team Invitation</h2>
                        <p>Hello <strong>{$user['name']}</strong>,</p>
                        <p>You have been assigned to the role of <strong>" . ucwords(str_replace('_', ' ', $role)) . "</strong> on the Transition Team for student <strong>{$ctx['student_name']}</strong>.</p>
                        <p>Please log in and update your name, contact details, and status for this Individual Transition Plan (ITP) record using the link below:</p>
                        <p><a href='{$link}' style='background:#a01422;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>Fill Team Member Information</a></p>
                        <p>If the button doesn't work, copy and paste this URL into your browser:</p>
                        <p>{$link}</p>
                    ";
                    MailHelper::sendNotification($user['email'], $user['name'], $subject, $htmlBody);
                }
            }
        }

        $_SESSION['success'] = 'Transition Team member updated successfully.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
        exit;
    }

    public function remindTeamMember(string $id): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $memberId = (int)$id;
        $member = $this->model->getTeamMemberById($memberId);
        if (!$member) {
            $_SESSION['error'] = 'Team member not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $itp = $this->model->getItpById((int)$member['itp_id']);
        if (!$itp) {
            $_SESSION['error'] = 'ITP not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $ctx = $this->model->getIepContext((int)$itp['iep_record_id']);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        if ($member['assigned_user_id']) {
            $user = $this->model->getUser((int)$member['assigned_user_id']);
            if ($user) {
                // Create system notification
                require_once __DIR__ . '/../Models/NotificationModel.php';
                $notifModel = new NotificationModel();
                $notifTitle = 'Transition Team Reminder';
                $notifMsg = "Reminder: Please fill in your information for {$ctx['student_name']}'s Transition Team.";
                $notifModel->create($member['assigned_user_id'], 'iep', $notifTitle, $notifMsg, ['itp_member_id' => $memberId]);

                // Send email
                require_once __DIR__ . '/../Helpers/MailHelper.php';
                $appUrl = getenv('APP_URL') ?: 'http://localhost';
                $link = $appUrl . $this->basePath . '/itp-team/edit/' . $memberId;
                $subject = 'Reminder: Transition Team Invitation - SPED LMS';
                $htmlBody = "
                    <h2>Transition Team Reminder</h2>
                    <p>Hello <strong>{$user['name']}</strong>,</p>
                    <p>This is a reminder that you have a pending action to fill in your details as the <strong>" . ucwords(str_replace('_', ' ', $member['role'])) . "</strong> on the Transition Team for <strong>{$ctx['student_name']}</strong>.</p>
                    <p>Please log in and update your details using the link below:</p>
                    <p><a href='{$link}' style='background:#a01422;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>Fill Team Member Information</a></p>
                    <p>If the button doesn't work, copy and paste this URL into your browser:</p>
                    <p>{$link}</p>
                ";
                MailHelper::sendNotification($user['email'], $user['name'], $subject, $htmlBody);
                $_SESSION['success'] = 'Reminder sent successfully.';
            } else {
                $_SESSION['error'] = 'Assigned user details not found.';
            }
        } else {
            $_SESSION['error'] = 'No user assigned to this role.';
        }

        header('Location: ' . $this->basePath . '/iep/' . $itp['iep_record_id'] . '/individual-transition-plan');
        exit;
    }

    public function editTeamMember(string $id): void {
        RoleMiddleware::check('itp.fill_own_row');

        $memberId = (int)$id;
        $member = $this->model->getTeamMemberById($memberId);
        if (!$member) {
            $_SESSION['error'] = 'Team member row not found.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        // Gate access
        if ((int)$member['assigned_user_id'] !== $this->userId) {
            $_SESSION['error'] = 'You do not have permission to edit this team member row.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $itp = $this->model->getItpById((int)$member['itp_id']);
        if (!$itp) {
            $_SESSION['error'] = 'ITP record not found.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $iepId = (int)$itp['iep_record_id'];
        $iep = $this->model->getIepContext($iepId);

        // Prefill name/email if empty
        if (empty($member['name'])) {
            $member['name'] = $member['user_name'] ?? '';
        }
        if (empty($member['contact_details'])) {
            $member['contact_details'] = $member['user_email'] ?? '';
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;

        require_once __DIR__ . '/../Views/itp/edit_member.php';
    }

    public function saveTeamMember(string $id): void {
        RoleMiddleware::check('itp.fill_own_row');

        $memberId = (int)$id;
        $member = $this->model->getTeamMemberById($memberId);
        if (!$member) {
            $_SESSION['error'] = 'Team member row not found.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        // Gate access
        if ((int)$member['assigned_user_id'] !== $this->userId) {
            $_SESSION['error'] = 'You do not have permission to update this team member row.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $itp = $this->model->getItpById((int)$member['itp_id']);
        if (!$itp) {
            $_SESSION['error'] = 'ITP record not found.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $contactDetails = trim($_POST['contact_details'] ?? '');
        $dateStarted = trim($_POST['date_started'] ?? '');

        if (empty($name) || empty($contactDetails)) {
            $_SESSION['error'] = 'Name and contact details are required.';
            header('Location: ' . $this->basePath . '/itp-team/edit/' . $memberId);
            exit;
        }

        $this->model->updateTeamMemberDetails($memberId, $name, $contactDetails, $dateStarted);

        $_SESSION['success'] = 'Your Transition Team details have been updated.';
        header('Location: ' . $this->basePath . '/iep/' . $itp['iep_record_id'] . '/individual-transition-plan');
        exit;
    }

    public function saveNarrative(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itp = $workflow['itp'] ?? null;
        if (!$itp) {
            $_SESSION['error'] = 'Individual Transition Plan must be created first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        if ($itp['status'] === 'finalized') {
            $_SESSION['error'] = 'This ITP is finalized and locked.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        $itpId = (int)$itp['id'];

        // Save narratives for Strengths, Interests, Talents, Skills, Needs
        $sections = ['strengths', 'interests', 'talents', 'skills', 'needs'];
        foreach ($sections as $section) {
            $items = $_POST[$section] ?? [];
            if (!is_array($items)) {
                $items = [];
            }
            // Filter out empty items
            $filteredItems = array_filter(array_map('trim', $items), function($v) {
                return $v !== '';
            });
            $this->model->saveNarrativeItems($itpId, $section, $filteredItems);
        }

        // Save beginning of SY recommendation
        $recText = $_POST['recommendation_beginning'] ?? '';
        $this->model->saveRecommendation($itpId, 'beginning_of_sy', $recText);

        $_SESSION['success'] = 'Narrative assessment and recommendations saved successfully.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
        exit;
    }

    public function saveMatrix(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $iepId = (int) $iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itp = $workflow['itp'] ?? null;
        if (!$itp) {
            $_SESSION['error'] = 'Individual Transition Plan must be created first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        if ($itp['status'] === 'finalized') {
            $_SESSION['error'] = 'This ITP is finalized and locked.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        $itpId = (int)$itp['id'];

        // Parse and save program matrix
        $checkedCells = [];
        if (!empty($_POST['matrix']) && is_array($_POST['matrix'])) {
            foreach ($_POST['matrix'] as $cellStr) {
                $parts = explode('-', $cellStr);
                if (count($parts) === 2) {
                    $checkedCells[] = [
                        'row' => (int)$parts[0],
                        'col' => (int)$parts[1]
                    ];
                }
            }
        }
        $this->model->saveProgramMatrix($itpId, $checkedCells);

        // Save end of SY recommendation
        $recEndText = $_POST['recommendation_end'] ?? '';
        $this->model->saveRecommendation($itpId, 'end_of_sy', $recEndText);

        $_SESSION['success'] = 'Transition program matrix and recommendations saved successfully.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
        exit;
    }

    public function saveParentSignature(string $iepId): void {
        $iepId = (int)$iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'IEP record not found.']);
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itp = $workflow['itp'] ?? null;
        if (!$itp) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Individual Transition Plan must be created first.']);
            exit;
        }

        if ($itp['status'] === 'finalized') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'This ITP is finalized and locked.']);
            exit;
        }

        // Validate current user is linked parent or assigned parent team member
        $teamMembers = $this->model->getTeamMembers((int)$itp['id']);
        $parentMember = null;
        foreach ($teamMembers as $m) {
            if ($m['role'] === 'parent_guardian') {
                $parentMember = $m;
                break;
            }
        }
        $isAssignedParent = ($parentMember && (int)$parentMember['assigned_user_id'] === $this->userId);
        $studentParentId = $this->model->getParentIdForStudent((int)$ctx['student_id']);
        $isLinkedParent = ($studentParentId !== null && $studentParentId === $this->userId);

        if (!$isAssignedParent && !$isLinkedParent && $this->userRole !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You are not authorized to sign as the parent/guardian for this student.']);
            exit;
        }

        $signatureB64 = $_POST['signature_data'] ?? '';
        if (empty($signatureB64)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No signature data provided.']);
            exit;
        }

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureB64));
        if ($imageData === false || $imageData === '') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid signature image data.']);
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/signatures/itp/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'sig_itp_' . $itp['id'] . '_' . time() . '.png';
        if (file_put_contents($uploadDir . $filename, $imageData) === false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Could not save signature image.']);
            exit;
        }

        $path = 'uploads/signatures/itp/' . $filename;
        $this->model->saveParentSignature((int)$itp['id'], $path);

        // Update team member status if assigned parent signed
        if ($parentMember) {
            $currentUser = $this->model->getUser($this->userId);
            $parentName = $parentMember['name'] ?: ($currentUser['name'] ?? 'Parent / Guardian');
            $parentContact = $parentMember['contact_details'] ?: ($currentUser['email'] ?? 'N/A');
            
            // If parent role is not assigned to this user ID yet, assign them
            if (empty($parentMember['assigned_user_id']) && $this->userRole === 'parent') {
                $this->model->assignTeamMember((int)$itp['id'], 'parent_guardian', $this->userId);
            }
            
            $this->model->updateTeamMemberDetails((int)$parentMember['id'], $parentName, $parentContact, date('Y-m-d'));
        }

        $this->logActivity('itp.signature_saved', 'itp_records', (int)$itp['id'], 'Parent signature saved for ITP ID ' . $itp['id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Signature saved successfully', 'path' => $path]);
        exit;
    }

    public function finalizeItp(string $iepId): void {
        if (!in_array($this->userRole, ['sped_teacher', 'master_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $iepId = (int)$iepId;
        $ctx = $this->model->getIepContext($iepId);
        if (!$ctx) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $workflow = $this->model->getWorkflow($iepId);
        $itp = $workflow['itp'] ?? null;
        if (!$itp) {
            $_SESSION['error'] = 'Individual Transition Plan must be created first.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        if ($itp['status'] === 'finalized') {
            $_SESSION['success'] = 'This ITP is already finalized and locked.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        // Hard gate: check parent signature
        $parentSignature = $this->model->getParentSignature((int)$itp['id']);
        if (!$parentSignature) {
            $_SESSION['error'] = 'Parent/Guardian signature is required before finalization.';
            header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
            exit;
        }

        // Finalize
        $this->model->finalizeItp((int)$itp['id']);

        // Notify active transition team members
        $teamMembers = $this->model->getTeamMembers((int)$itp['id']);
        require_once __DIR__ . '/../Models/NotificationModel.php';
        $notifModel = new NotificationModel();

        foreach ($teamMembers as $m) {
            if ($m['assigned_user_id'] && (int)$m['assigned_user_id'] !== $this->userId) {
                $notifTitle = 'ITP Finalized';
                $notifMsg = "The Individual Transition Plan for {$ctx['student_name']} has been finalized and locked.";
                $notifModel->create((int)$m['assigned_user_id'], 'iep', $notifTitle, $notifMsg, ['itp_id' => $itp['id']]);
            }
        }

        $this->logActivity('itp.finalized', 'itp_records', (int)$itp['id'], 'Finalized and locked ITP for student ID ' . $ctx['student_id']);

        $_SESSION['success'] = 'Individual Transition Plan has been finalized and locked successfully.';
        header('Location: ' . $this->basePath . '/iep/' . $iepId . '/individual-transition-plan');
        exit;
    }

    private function logActivity(string $actionType, string $table, ?int $recordId, string $details): void {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO activity_log (user_id, action_type, affected_table, affected_record_id, details, ip_address)
                VALUES (:user_id, :action_type, :affected_table, :affected_record_id, :details, :ip_address)
            ");
            $stmt->execute([
                'user_id' => $this->userId,
                'action_type' => $actionType,
                'affected_table' => $table,
                'affected_record_id' => $recordId,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (Throwable $e) {
            error_log("Failed to log activity in ITPController: " . $e->getMessage());
        }
    }
}
