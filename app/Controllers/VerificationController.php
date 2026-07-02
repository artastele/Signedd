<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-06-28
// Part of: SPED LMS — Enrollment Verification Controller

require_once __DIR__ . '/../Models/EnrollmentModel.php';
require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class VerificationController {
    private $enrollmentModel;
    private $studentModel;
    private $notificationModel;
    private $db;
    private $userId;
    private $userRole;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $this->userId = $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? 'user';

        if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
            http_response_code(403);
            echo "Access Denied";
            exit;
        }

        $this->db = Database::getInstance()->getConnection();
        $this->enrollmentModel = new EnrollmentModel();
        $this->studentModel = new StudentModel();
        $this->notificationModel = new NotificationModel();
    }

    public function index() {
        try {
            $enrollments = $this->enrollmentModel->getPending();

            foreach ($enrollments as &$enrollment) {
                $documents = $this->enrollmentModel->getDocuments($enrollment['id']);
                $enrollment['documents'] = $documents;
                $enrollment['total_docs'] = count($documents);
                $enrollment['approved_docs'] = count(array_filter($documents, fn($d) => $d['status'] === 'approved'));
                $enrollment['pending_docs'] = count(array_filter($documents, fn($d) => $d['status'] === 'pending'));
                $enrollment['rejected_docs'] = count(array_filter($documents, fn($d) => $d['status'] === 'rejected'));
            }

            $this->logActivity('verification.list', 'enrollment_submissions', null, 'Viewed verification dashboard');
            require __DIR__ . '/../Views/verification/index.php';

        } catch (Exception $e) {
            error_log("VerificationController->index() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading enrollments";
        }
    }

    public function show($id) {
        try {
            $enrollment = $this->enrollmentModel->findById($id);

            if (!$enrollment) {
                http_response_code(404);
                echo "Enrollment not found";
                return;
            }

            $documents = $this->enrollmentModel->getDocuments($id);
            $allApproved = $this->enrollmentModel->areAllDocumentsApproved($id);

            $this->logActivity('verification.view', 'enrollment_submissions', $id, 'Viewed enrollment detail');
            require __DIR__ . '/../Views/verification/show.php';

        } catch (Exception $e) {
            error_log("VerificationController->show() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading enrollment";
        }
    }

    public function verify($id) {
        header('Content-Type: application/json');

        try {
            $enrollment = $this->enrollmentModel->findById($id);

            if (!$enrollment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
                return;
            }

            if ($enrollment['learner_account_created']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Learner account already created for this enrollment']);
                return;
            }

            if ($enrollment['status'] !== 'verified') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Enrollment must be verified first before creating learner account']);
                return;
            }

            $studentData = $this->studentModel->createStudentRecord($id, $this->userId);

            $enrollmentDataForAccount = $enrollment;
            $enrollmentDataForAccount['enrollment_id'] = $enrollment['id'];

            $accountData = $this->studentModel->createLearnerAccount(
                $studentData['id'],
                $studentData['student_id'],
                $enrollmentDataForAccount
            );

            $this->enrollmentModel->update($id, [
                'learner_account_created' => true,
            ]);

            try {
                $this->notifyParentVerified($enrollment, $studentData['student_id'], $accountData);
            } catch (Throwable $e) {
                error_log("Failed to send parent notification (non-fatal): " . $e->getMessage());
            }

            $this->logActivity(
                'enrollment.verified',
                'enrollment_submissions',
                $id,
                "Created learner account with Student ID: {$studentData['student_id']}"
            );

            echo json_encode([
                'success' => true,
                'message' => 'Learner account created successfully',
                'student_id' => $studentData['student_id'],
                'lrn' => $studentData['lrn'],
                'learner_id' => $accountData['user_id']
            ]);

        } catch (Exception $e) {
            error_log("VerificationController->verify() ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating learner account: ' . $e->getMessage()]);
        }
    }

    private function notifyParentVerified($enrollment, $studentIdCode, $accountData) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = :parent_id");
            $stmt->execute(['parent_id' => $enrollment['parent_id']]);
            $parent = $stmt->fetch();

            if (!$parent) {
                error_log("Parent not found for enrollment: " . $enrollment['id']);
                return;
            }

            $this->notificationModel->create(
                $parent['id'],
                'enrollment_verified',
                'Enrollment Verified - Learner Account Created',
                "Your child's enrollment has been verified. Student ID: $studentIdCode. Login credentials have been sent to your email.",
                [
                    'enrollment_id' => $enrollment['id'],
                    'student_id' => $studentIdCode,
                    'learner_id' => $accountData['user_id'],
                    'temp_password' => $accountData['temp_password']
                ]
            );

            $subject = "Enrollment Verified - Learner Account Created";
            $body = "
            <h2>Enrollment Verified</h2>
            <p>Dear {$enrollment['parent_name']},</p>
            <p>Your child's enrollment has been verified and approved by the SPED Teacher.</p>
            <h3>Learner Information</h3>
            <p><strong>Student ID:</strong> <strong>$studentIdCode</strong></p>
            <p><strong>Status:</strong> Active</p>
            <h3>Next Steps</h3>
            <p>A learner account has been created. You will receive a separate email with login credentials for your child's account.</p>
            <p>Best regards,<br>SPED LMS System</p>
            ";

            @MailHelper::sendNotification($enrollment['parent_email'], $enrollment['parent_name'], $subject, $body);

        } catch (Exception $e) {
            error_log("Failed to send verification notification: " . $e->getMessage());
        }
    }

    private function logActivity($actionType, $table, $recordId, $details) {
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
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}
