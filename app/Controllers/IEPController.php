<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5 (SIMPLIFIED)
// Last modified: 2026-05-12
// Part of: SPED LMS — IEP Controller (Individualized Education Plan) - Upload Only System

require_once __DIR__ . '/../Models/IEPModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class IEPController {
    private $iepModel;
    private $notifModel;
    private $userId;
    private $userRole;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
        $this->userId    = $_SESSION['user_id'];
        $this->userRole  = $_SESSION['role'] ?? 'user';
        $this->iepModel  = new IEPModel();
        $this->notifModel = new NotificationModel();
    }

    // ============================================================
    // INDEX — IEP Repository (list all IEPs per role)
    // ============================================================
    public function index() {
        $role     = $this->userRole;
        $basePath = BASE_PATH;

        if (!in_array($role, ['sped_teacher','guidance','principal','parent','admin'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Filter params
        $filterYear   = $_GET['school_year'] ?? '';
        $filterStatus = $_GET['status'] ?? '';

        if ($role === 'sped_teacher' || $role === 'admin') {
            $ieps = $this->iepModel->getByTeacher($this->userId);
        } elseif ($role === 'parent') {
            $ieps = $this->iepModel->getSignedForRole('parent', $this->userId);
        } else {
            $ieps = $this->iepModel->getSignedForRole($role, $this->userId);
        }

        // Apply filters
        if ($filterYear) {
            $ieps = array_filter($ieps, fn($i) => $i['school_year'] === $filterYear);
        }
        if ($filterStatus) {
            $ieps = array_filter($ieps, fn($i) => $i['status'] === $filterStatus);
        }

        // Eligible students for new IEP (sped_teacher only)
        $eligibleStudents = [];
        if ($role === 'sped_teacher' || $role === 'admin') {
            $eligibleStudents = $this->iepModel->getEligibleStudents($this->userId);
        }

        require __DIR__ . '/../Views/iep/index.php';
    }

    // ============================================================
    // CREATE — Start new IEP draft for a student
    // ============================================================
    public function create() {
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            $_SESSION['error'] = 'Only SPED Teachers can draft an IEP.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $studentId = $_GET['student_id'] ?? null;
        if (!$studentId) {
            $_SESSION['error'] = 'No student selected.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Verify signed PDSP exists
        $pdsp = $this->iepModel->getSignedPDSP($studentId);
        if (!$pdsp) {
            $_SESSION['error'] = 'This student does not have a signed PDSP yet. Complete Process 4 first.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Check if a draft already exists for this student + school year
        $existing = $this->iepModel->getLatestByStudent($studentId);
        if ($existing && in_array($existing['status'], ['draft'])) {
            // Resume existing draft
            header('Location: ' . BASE_PATH . '/iep/form/' . $existing['id']);
            exit;
        }

        // Create new draft
        $schoolYear = date('Y') . '-' . (date('Y') + 1);
        $iepId = $this->iepModel->create($studentId, $pdsp['id'], $this->userId, $schoolYear);

        $this->logActivity('iep.created', $iepId, "Created IEP draft for student: $studentId");

        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    // ============================================================
    // FORM — Show simplified IEP upload form
    // ============================================================
    public function form($iepId) {
        $iep = $this->iepModel->findById($iepId);
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // SPED Teacher: must be drafter; others: read-only if signed/locked
        $role     = $this->userRole;
        $readOnly = false;

        if ($role === 'sped_teacher' || $role === 'admin') {
            if ($iep['drafted_by'] != $this->userId && $role !== 'admin') {
                $_SESSION['error'] = 'You can only edit IEPs you drafted.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
            if (in_array($iep['status'], ['signed','locked'])) {
                $readOnly = true;
            }
        } elseif (in_array($role, ['guidance','principal','parent'])) {
            // Can view if a copy was sent to them OR status is signed/locked
            $db2 = Database::getInstance()->getConnection();
            $stmt2 = $db2->prepare("SELECT id FROM iep_copies WHERE iep_id = :iep_id AND sent_to = :user_id LIMIT 1");
            $stmt2->execute(['iep_id' => $iepId, 'user_id' => $this->userId]);
            $hasCopy = $stmt2->fetch();
            if (!$hasCopy && !in_array($iep['status'], ['signed','locked'])) {
                $_SESSION['error'] = 'This IEP has not been shared with you yet.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
            $readOnly = in_array($iep['status'], ['signed','locked']);
        } else {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Load related data
        $signatories    = $this->iepModel->getSignatories($iepId);
        $studentData    = $this->iepModel->getStudentAutoFill($iep['student_id']);
        $linkedParent   = $this->iepModel->getLinkedParent($iep['student_id']);
        $studentIEPs    = $this->iepModel->getByStudent($iep['student_id']);
        $userRole       = $role;

        // Check if re-evaluation date has passed
        $canStartNewCycle = false;
        if ($iep['status'] === 'locked' && $iep['re_evaluation_date']) {
            $canStartNewCycle = (strtotime($iep['re_evaluation_date']) < time());
        }

        // Mark copy as viewed for non-teacher roles
        if (in_array($role, ['guidance','principal','parent'])) {
            $this->iepModel->markCopyViewed($iepId, $this->userId);
        }

        $this->logActivity('iep.viewed', $iepId, "Viewed IEP form");

        $basePath = BASE_PATH;
        require __DIR__ . '/../Views/iep/form_simplified.php';
    }

    // ============================================================
    // UPLOAD IEP DOCUMENT — AJAX (simplified upload)
    // ============================================================
    public function upload() {
        header('Content-Type: application/json');

        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        if (!$iepId || !isset($_FILES['iep_document'])) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        $iep = $this->iepModel->findById($iepId);
        if (!$iep || in_array($iep['status'], ['signed','locked'])) {
            echo json_encode(['success' => false, 'message' => 'IEP is locked or not found']);
            exit;
        }

        $file = $_FILES['iep_document'];
        $allowedTypes = ['image/jpeg','image/png','application/pdf'];
        $allowedExts  = ['jpg','jpeg','png','pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file['type'], $allowedTypes) || !in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'message' => 'Only jpg, png, pdf allowed']);
            exit;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
            exit;
        }

        // Create student-specific directory
        $uploadDir = __DIR__ . '/../../public/uploads/iep/' . $iep['student_id'] . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'iep_' . $iepId . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
            exit;
        }

        $path = 'uploads/iep/' . $iep['student_id'] . '/' . $filename;
        $this->iepModel->update($iepId, ['signed_document_path' => $path]);
        
        $this->logActivity('iep.document_uploaded', $iepId, 'IEP document uploaded: ' . $file['name']);

        echo json_encode([
            'success'  => true,
            'message'  => 'Document uploaded successfully',
            'filename' => $file['name'],
            'path'     => $path,
            'fileType' => $ext,
            'fileSize' => round($file['size'] / 1024, 1) . ' KB'
        ]);
        exit;
    }

    // ============================================================
    // SUBMIT IEP — Final submission with validation
    // ============================================================
    public function submitIEP() {
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        $iep   = $this->iepModel->findById($iepId);

        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        if ($iep['status'] !== 'draft') {
            $_SESSION['error'] = 'Only draft IEPs can be submitted.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Validation
        $errors = [];

        // Document uploaded
        if (empty($iep['signed_document_path'])) {
            $errors[] = 'IEP document upload is required.';
        }

        // Re-evaluation date
        $reEvalDate = $_POST['re_evaluation_date'] ?? '';
        if (empty($reEvalDate)) {
            $errors[] = 'Re-evaluation date is required.';
        } elseif (strtotime($reEvalDate) <= time()) {
            $errors[] = 'Re-evaluation date must be in the future.';
        }

        // At least one signatory
        $signatoryRoles = ['parent_guardian', 'guidance_counselor', 'teacher', 'sned_teacher', 'school_head', 'ilrc_supervisor'];
        $hasSignatory = false;
        foreach ($signatoryRoles as $role) {
            if (!empty($_POST['signatory_' . $role]) && !empty($_POST['signatory_name_' . $role])) {
                $hasSignatory = true;
                break;
            }
        }
        if (!$hasSignatory) {
            $errors[] = 'At least one signatory is required.';
        }

        if (!empty($errors)) {
            $_SESSION['iep_errors'] = $errors;
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Save re-evaluation date
        $this->iepModel->update($iepId, ['re_evaluation_date' => $reEvalDate]);

        // Save signatories
        $signatories = [];
        foreach ($signatoryRoles as $role) {
            if (!empty($_POST['signatory_' . $role]) && !empty($_POST['signatory_name_' . $role])) {
                $signatories[] = [
                    'role' => $role,
                    'name' => trim($_POST['signatory_name_' . $role])
                ];
            }
        }
        $this->iepModel->saveSignatories($iepId, $signatories);

        // Mark as signed and locked
        $this->iepModel->markSigned($iepId);

        // Send copies to Guidance, Principal, Parent
        $this->sendSignedCopies($iepId, $iep);

        // Notify that Process 6 is unlocked
        $this->notifModel->create(
            $this->userId,
            'process6_unlocked',
            'IEP Signed — Process 6 Unlocked',
            "The IEP for {$iep['student_name']} has been signed. You can now implement the IEP (Process 6).",
            json_encode(['iep_id' => $iepId, 'student_id' => $iep['student_id']])
        );

        $this->logActivity('iep.signed', $iepId, "IEP submitted and locked");

        $_SESSION['success'] = 'IEP submitted and locked successfully. Copies sent to all parties.';
        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    // ============================================================
    // NEW CYCLE — preserve old IEP, create new draft
    // ============================================================
    public function newCycle() {
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        $iep   = $this->iepModel->findById($iepId);

        if (!$iep || !in_array($iep['status'], ['signed','locked'])) {
            $_SESSION['error'] = 'Only signed/locked IEPs can start a new cycle.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Check if re-evaluation date has passed
        if ($iep['re_evaluation_date'] && strtotime($iep['re_evaluation_date']) > time()) {
            $_SESSION['error'] = 'Cannot start new cycle until re-evaluation date has passed.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Lock the old one explicitly
        $this->iepModel->update($iepId, ['status' => 'locked']);

        // Get signed PDSP (may be same or new)
        $pdsp = $this->iepModel->getSignedPDSP($iep['student_id']);
        if (!$pdsp) {
            $_SESSION['error'] = 'No signed PDSP found. Complete a new IEP meeting first.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $schoolYear = date('Y') . '-' . (date('Y') + 1);
        $newIepId   = $this->iepModel->create($iep['student_id'], $pdsp['id'], $this->userId, $schoolYear);

        $this->logActivity('iep.new_cycle', $newIepId, "New IEP cycle started from IEP: $iepId");

        $_SESSION['success'] = 'New IEP cycle started. Previous IEP preserved.';
        header('Location: ' . BASE_PATH . '/iep/form/' . $newIepId);
        exit;
    }

    // ============================================================
    // DOWNLOAD DOCUMENT — Secure file serving
    // ============================================================
    public function downloadDocument($iepId) {
        $iep = $this->iepModel->findById($iepId);
        if (!$iep || empty($iep['signed_document_path'])) {
            $_SESSION['error'] = 'Document not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Check access permissions
        $role = $this->userRole;
        $hasAccess = false;

        if (in_array($role, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
            $hasAccess = true;
        } elseif ($role === 'parent') {
            // Parent can only view, not download
            $linkedParent = $this->iepModel->getLinkedParent($iep['student_id']);
            $hasAccess = ($linkedParent && $linkedParent['id'] == $this->userId);
        }

        if (!$hasAccess) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $filePath = __DIR__ . '/../../public/' . $iep['signed_document_path'];
        if (!file_exists($filePath)) {
            $_SESSION['error'] = 'File not found on server.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Serve file
        $filename = basename($filePath);
        $mimeType = mime_content_type($filePath);
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================
    private function sendSignedCopies($iepId, $iep) {
        $db = Database::getInstance()->getConnection();
        $recipients = [];

        // Parent
        $parent = $this->iepModel->getLinkedParent($iep['student_id']);
        if ($parent) $recipients[] = $parent;

        // Guidance + Principal
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE role IN ('guidance','principal') AND status = 'active'");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $s) $recipients[] = $s;

        $link = getenv('APP_URL') . BASE_PATH . '/iep/form/' . $iepId;

        foreach ($recipients as $user) {
            // Record copy
            $this->iepModel->recordCopy($iepId, $user['id']);

            // In-system notification
            $this->notifModel->create($user['id'], 'iep_signed',
                'IEP Signed — ' . $iep['student_name'],
                "The IEP for {$iep['student_name']} has been signed and is now available for viewing.",
                json_encode(['iep_id' => $iepId])
            );

            // Email (Guidance and Principal only, not Parent)
            if (!empty($user['email']) && in_array($user['role'] ?? '', ['guidance','principal'])) {
                MailHelper::sendNotification($user['email'], $user['name'],
                    'IEP Signed — ' . $iep['student_name'] . ' — SPED LMS',
                    "<h2 style='color:#1e4072;'>IEP Signed</h2>
                     <p>Dear {$user['name']},</p>
                     <p>The IEP for <strong>{$iep['student_name']}</strong> has been signed and is now available for viewing.</p>
                     <p><a href='{$link}' style='background:#a01422;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>View IEP</a></p>
                     <p>Best regards,<br>SPED LMS</p>"
                );
            }
        }
    }

    private function logActivity($action, $recordId, $details) {
        try {
            $stmt = Database::getInstance()->getConnection()->prepare("
                INSERT INTO activity_log (user_id, action_type, affected_table, affected_record_id, details, ip_address, created_at)
                VALUES (:user_id, :action, 'iep_records', :record_id, :details, :ip, NOW())
            ");
            $stmt->execute([
                'user_id'   => $this->userId,
                'action'    => $action,
                'record_id' => $recordId,
                'details'   => $details,
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Activity log failed: " . $e->getMessage());
        }
    }
}