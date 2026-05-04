<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment Controller

require_once __DIR__ . '/../Models/AssessmentModel.php';
require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/EnrollmentModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class AssessmentController {
    private $assessmentModel;
    private $studentModel;
    private $enrollmentModel;
    private $userId;
    private $userRole;

    public function __construct() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $this->userId = $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? 'user';
        
        $this->assessmentModel = new AssessmentModel();
        $this->studentModel = new StudentModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * Assessment dashboard - SPED Teacher reviews pending assessments
     */
    public function index() {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get pending assessments
            $pendingAssessments = $this->assessmentModel->getPendingForReview();
            
            // Log activity
            $this->logActivity('assessment.list', 'assessment_records', null, 'Viewed assessment dashboard');
            
            // Load view
            require __DIR__ . '/../Views/assessment/index.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->index() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading assessments";
        }
    }

    /**
     * Assessment form - Parent fills education history and assessment info
     */
    public function conduct($studentId) {
        try {
            // Get student with enrollment data
            $student = $this->studentModel->getWithDetails($studentId);
            
            if (!$student) {
                http_response_code(404);
                echo "Student not found";
                return;
            }
            
            // Check if parent owns this student (from enrollment)
            if ($this->userRole === 'parent' && $student['parent_id'] !== $this->userId) {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get existing assessment if any
            $existingAssessment = $this->assessmentModel->getLatest($studentId);
            
            // Log activity
            $this->logActivity('assessment.form', 'assessment_records', null, "Opened assessment form for student: $studentId");
            
            // Load view
            require __DIR__ . '/../Views/assessment/conduct.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->conduct() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading assessment form";
        }
    }

    /**
     * Submit assessment - Parent submits education history and assessment info
     */
    public function submit() {
        try {
            // Check permission
            if ($this->userRole !== 'parent' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            // Get POST data
            $studentId = $_POST['student_id'] ?? null;
            $educationHistory = [
                'previous_school' => $_POST['previous_school'] ?? '',
                'grade_level' => $_POST['grade_level'] ?? '',
                'with_iep' => $_POST['with_iep'] ?? 'no',
                'with_support_services' => $_POST['with_support_services'] ?? 'no',
                'support_services' => isset($_POST['support_services']) ? $_POST['support_services'] : []
            ];
            
            // Assessment info - dynamic table rows
            $assessmentInfo = [];
            if (isset($_POST['assessment_service'])) {
                $services = $_POST['assessment_service'];
                $mdtMembers = $_POST['mdt_members'] ?? [];
                $assessmentDates = $_POST['assessment_dates'] ?? [];
                $supportingDocs = $_POST['supporting_docs'] ?? [];
                
                foreach ($services as $index => $service) {
                    if (!empty($service)) {
                        $assessmentInfo[] = [
                            'service' => $service,
                            'mdt_members' => $mdtMembers[$index] ?? '',
                            'assessment_date' => $assessmentDates[$index] ?? '',
                            'supporting_documents' => $supportingDocs[$index] ?? ''
                        ];
                    }
                }
            }
            
            // Validate required fields
            if (!$studentId || empty($educationHistory['previous_school']) || empty($assessmentInfo)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }
            
            // Create or update assessment
            $assessmentId = $this->assessmentModel->create(
                $studentId,
                $educationHistory,
                $assessmentInfo,
                $this->userId
            );
            
            // Log activity
            $this->logActivity('assessment.submit', 'assessment_records', $assessmentId, "Parent submitted assessment for student: $studentId");
            
            // Send notification to SPED teacher
            $this->notifySpedTeacherNewAssessment($studentId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Assessment submitted successfully',
                'assessment_id' => $assessmentId
            ]);
            
        } catch (Exception $e) {
            error_log("AssessmentController->submit() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error submitting assessment']);
        }
    }

    /**
     * View assessment - SPED Teacher reviews submitted assessment
     */
    public function view($assessmentId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get assessment
            $assessment = $this->assessmentModel->findById($assessmentId);
            
            if (!$assessment) {
                http_response_code(404);
                echo "Assessment not found";
                return;
            }
            
            // Log activity
            $this->logActivity('assessment.view', 'assessment_records', $assessmentId, 'Viewed assessment for review');
            
            // Load view
            require __DIR__ . '/../Views/assessment/view.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->view() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading assessment";
        }
    }

    /**
     * Approve assessment
     */
    public function approve($assessmentId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            // Get assessment
            $assessment = $this->assessmentModel->findById($assessmentId);
            
            if (!$assessment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                return;
            }
            
            // Approve assessment
            $this->assessmentModel->approve($assessmentId, $this->userId);
            
            // Log activity
            $this->logActivity('assessment.approve', 'assessment_records', $assessmentId, 'Approved assessment');
            
            // Send notification to parent
            $this->notifyParentApproved($assessment);
            
            echo json_encode([
                'success' => true,
                'message' => 'Assessment approved successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("AssessmentController->approve() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error approving assessment']);
        }
    }

    /**
     * Reject assessment
     */
    public function reject($assessmentId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            // Get rejection reason
            $reason = $_POST['reason'] ?? 'No reason provided';
            
            // Get assessment
            $assessment = $this->assessmentModel->findById($assessmentId);
            
            if (!$assessment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                return;
            }
            
            // Reject assessment
            $this->assessmentModel->reject($assessmentId, $this->userId, $reason);
            
            // Log activity
            $this->logActivity('assessment.reject', 'assessment_records', $assessmentId, "Rejected assessment: $reason");
            
            // Send notification to parent
            $this->notifyParentRejected($assessment, $reason);
            
            echo json_encode([
                'success' => true,
                'message' => 'Assessment rejected successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("AssessmentController->reject() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error rejecting assessment']);
        }
    }

    /**
     * View assessment history
     */
    public function history($studentId) {
        try {
            // Get student
            $student = $this->studentModel->getWithDetails($studentId);
            
            if (!$student) {
                http_response_code(404);
                echo "Student not found";
                return;
            }
            
            // Check permission
            if ($this->userRole === 'parent' && $student['parent_id'] !== $this->userId) {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get assessment history
            $history = $this->assessmentModel->getHistory($studentId);
            
            // Log activity
            $this->logActivity('assessment.history', 'assessment_records', null, "Viewed assessment history for student: $studentId");
            
            // Load view
            require __DIR__ . '/../Views/assessment/history.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->history() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading assessment history";
        }
    }

    /**
     * Notify SPED teacher of new assessment submission
     */
    private function notifySpedTeacherNewAssessment($studentId) {
        try {
            $student = $this->studentModel->getWithDetails($studentId);
            
            // Get SPED teachers (for now, send to admin)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT id, email, name FROM users
                WHERE role IN ('sped_teacher', 'admin')
                LIMIT 5
            ");
            $stmt->execute();
            $teachers = $stmt->fetchAll();
            
            foreach ($teachers as $teacher) {
                $subject = "New Assessment Submission - {$student['student_name']}";
                
                $body = "
                <h2>New Assessment Submission</h2>
                <p>A new assessment has been submitted for review.</p>
                
                <h3>Student Information</h3>
                <p><strong>Name:</strong> {$student['student_name']}</p>
                <p><strong>LRN:</strong> {$student['lrn']}</p>
                
                <p><a href='" . BASE_PATH . "/assessment/view/{$student['id']}' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Review Assessment</a></p>
                
                <p>Best regards,<br>SPED LMS System</p>
                ";
                
                MailHelper::send($teacher['email'], $subject, $body);
            }
            
        } catch (Exception $e) {
            error_log("Failed to notify SPED teacher: " . $e->getMessage());
        }
    }

    /**
     * Notify parent of assessment approval
     */
    private function notifyParentApproved($assessment) {
        try {
            $subject = "Assessment Approved - {$assessment['student_name']}";
            
            $body = "
            <h2>Assessment Approved</h2>
            <p>Dear {$assessment['parent_name']},</p>
            <p>Your child's educational assessment has been reviewed and approved by the SPED Teacher.</p>
            
            <h3>Student Information</h3>
            <p><strong>Name:</strong> {$assessment['student_name']}</p>
            <p><strong>LRN:</strong> {$assessment['lrn']}</p>
            <p><strong>Quarter:</strong> {$assessment['quarter']}</p>
            
            <p>The next step in the process will be scheduled soon. You will receive further notifications.</p>
            
            <p>Best regards,<br>SPED LMS System</p>
            ";
            
            MailHelper::send($assessment['parent_email'], $subject, $body);
            
        } catch (Exception $e) {
            error_log("Failed to send approval notification: " . $e->getMessage());
        }
    }

    /**
     * Notify parent of assessment rejection
     */
    private function notifyParentRejected($assessment, $reason) {
        try {
            $subject = "Assessment Needs Revision - {$assessment['student_name']}";
            
            $body = "
            <h2>Assessment Needs Revision</h2>
            <p>Dear {$assessment['parent_name']},</p>
            <p>Your child's educational assessment has been reviewed and requires revision.</p>
            
            <h3>Student Information</h3>
            <p><strong>Name:</strong> {$assessment['student_name']}</p>
            <p><strong>LRN:</strong> {$assessment['lrn']}</p>
            
            <h3>Reason for Revision</h3>
            <p>$reason</p>
            
            <p>Please review the assessment and resubmit with the necessary corrections.</p>
            
            <p><a href='" . BASE_PATH . "/assessment/conduct/{$assessment['student_id']}' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Resubmit Assessment</a></p>
            
            <p>Best regards,<br>SPED LMS System</p>
            ";
            
            MailHelper::send($assessment['parent_email'], $subject, $body);
            
        } catch (Exception $e) {
            error_log("Failed to send rejection notification: " . $e->getMessage());
        }
    }

    /**
     * Log activity
     */
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
