<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP Meeting Controller

require_once __DIR__ . '/../Models/IEPMeetingModel.php';
require_once __DIR__ . '/../Models/AssessmentModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class IEPMeetingController {
    private $meetingModel;
    private $assessmentModel;
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
        
        $this->meetingModel = new IEPMeetingModel();
        $this->assessmentModel = new AssessmentModel();
    }

    /**
     * List all IEP meetings
     */
    public function index() {
        try {
            // Get all meetings for this user
            $db = Database::getInstance()->getConnection();
            
            if ($this->userRole === 'sped_teacher') {
                $stmt = $db->prepare("
                    SELECT im.*, sr.student_name, sr.lrn
                    FROM iep_meetings im
                    JOIN student_records sr ON im.student_id = sr.id
                    WHERE im.scheduled_by = ?
                    ORDER BY im.meeting_date DESC
                ");
                $stmt->execute([$this->userId]);
            } elseif ($this->userRole === 'parent') {
                // Parent sees meetings for their children
                $stmt = $db->prepare("
                    SELECT im.*, sr.student_name, sr.lrn
                    FROM iep_meetings im
                    JOIN student_records sr ON im.student_id = sr.id
                    JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                    WHERE es.parent_id = ?
                    ORDER BY im.meeting_date DESC
                ");
                $stmt->execute([$this->userId]);
            } else {
                // Guidance or Principal
                $stmt = $db->prepare("
                    SELECT im.*, sr.student_name, sr.lrn
                    FROM iep_meetings im
                    JOIN student_records sr ON im.student_id = sr.id
                    WHERE im.guidance_id = ? OR im.principal_id = ?
                    ORDER BY im.meeting_date DESC
                ");
                $stmt->execute([$this->userId, $this->userId]);
            }
            
            $meetings = $stmt->fetchAll();
            
            $this->logActivity('iep_meeting.list', 'iep_meetings', null, 'Viewed meetings list');
            
            // Use parent view if parent role
            if ($this->userRole === 'parent') {
                require __DIR__ . '/../Views/iep_meeting/parent_list.php';
            } else {
                require __DIR__ . '/../Views/iep_meeting/index.php';
            }
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->index() ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo "Error loading meetings: " . htmlspecialchars($e->getMessage());
        }
    }

    /**
     * Display schedule meeting form
     */
    public function schedule() {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get approved assessments
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT ar.*, sr.student_name, sr.lrn
                FROM assessment_records ar
                JOIN student_records sr ON ar.student_id = sr.id
                WHERE ar.status = 'approved'
                ORDER BY ar.submitted_at DESC
            ");
            $stmt->execute();
            $approvedAssessments = $stmt->fetchAll();
            
            // Get guidance and principal users
            $stmt = $db->prepare("
                SELECT id, name, email FROM users
                WHERE role IN ('guidance', 'principal')
                ORDER BY role, name
            ");
            $stmt->execute();
            $participants = $stmt->fetchAll();
            
            $this->logActivity('iep_meeting.schedule_form', 'iep_meetings', null, 'Opened schedule meeting form');
            
            require __DIR__ . '/../Views/iep_meeting/schedule.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->schedule() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading schedule form";
        }
    }

    /**
     * Get available time slots for a user
     */
    public function getAvailability() {
        try {
            $userId = $_POST['user_id'] ?? null;
            $date = $_POST['date'] ?? null;
            
            if (!$userId || !$date) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                return;
            }
            
            $slots = $this->meetingModel->getAvailableSlots($userId, $date);
            
            echo json_encode([
                'success' => true,
                'slots' => $slots
            ]);
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->getAvailability() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error getting availability']);
        }
    }

    /**
     * Create meeting
     */
    public function createMeeting() {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            // Validate input
            $assessmentId = $_POST['assessment_id'] ?? null;
            $meetingDate = $_POST['meeting_date'] ?? null;
            $meetingTime = $_POST['meeting_time'] ?? null;
            $meetingLocation = $_POST['meeting_location'] ?? '';
            $agenda = $_POST['agenda'] ?? '';
            $guidanceId = $_POST['guidance_id'] ?? null;
            $principalId = $_POST['principal_id'] ?? null;
            
            if (!$assessmentId || !$meetingDate || !$meetingTime) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }
            
            // Get assessment and student
            $assessment = $this->assessmentModel->findById($assessmentId);
            if (!$assessment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                return;
            }
            
            // Combine date and time
            $meetingDateTime = $meetingDate . ' ' . $meetingTime;
            
            // Create meeting
            $meetingId = $this->meetingModel->create(
                $assessment['student_id'],
                $assessmentId,
                [
                    'meeting_date' => $meetingDateTime,
                    'meeting_location' => $meetingLocation,
                    'agenda' => $agenda,
                    'guidance_id' => $guidanceId,
                    'principal_id' => $principalId,
                    'scheduled_by' => $this->userId
                ]
            );
            
            // Send invitations
            $this->sendInvitations($meetingId, $assessment);
            
            $this->logActivity('iep_meeting.created', 'iep_meetings', $meetingId, "Created meeting for student: {$assessment['student_name']}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Meeting scheduled successfully',
                'meeting_id' => $meetingId
            ]);
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->createMeeting() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating meeting']);
        }
    }

    /**
     * View meeting details
     */
    public function show($id) {
        try {
            $meeting = $this->meetingModel->findById($id);
            
            if (!$meeting) {
                http_response_code(404);
                echo "Meeting not found";
                return;
            }
            
            $this->logActivity('iep_meeting.viewed', 'iep_meetings', $id, 'Viewed meeting details');
            
            require __DIR__ . '/../Views/iep_meeting/show.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->show() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading meeting";
        }
    }

    /**
     * Send meeting invitations
     */
    private function sendInvitations($meetingId, $assessment) {
        try {
            $meeting = $this->meetingModel->findById($meetingId);
            
            // Get parent email
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT u.email, u.name FROM users u
                JOIN enrollment_submissions es ON u.id = es.parent_id
                JOIN student_records sr ON es.id = sr.enrollment_id
                WHERE sr.id = :student_id
                LIMIT 1
            ");
            $stmt->execute(['student_id' => $assessment['student_id']]);
            $parent = $stmt->fetch();
            
            $meetingDateFormatted = date('F d, Y H:i', strtotime($meeting['meeting_date']));
            
            // Send to parent
            if ($parent) {
                $subject = "IEP Meeting Scheduled - {$assessment['student_name']}";
                $body = "
                <h2>IEP Meeting Scheduled</h2>
                <p>Dear {$parent['name']},</p>
                <p>An IEP meeting has been scheduled for your child.</p>
                
                <h3>Meeting Details</h3>
                <p><strong>Student:</strong> {$assessment['student_name']}</p>
                <p><strong>Date & Time:</strong> $meetingDateFormatted</p>
                <p><strong>Location:</strong> {$meeting['meeting_location']}</p>
                <p><strong>Agenda:</strong> {$meeting['agenda']}</p>
                
                <p>Please confirm your attendance.</p>
                <p>Best regards,<br>SPED LMS System</p>
                ";
                MailHelper::send($parent['email'], $subject, $body);
            }
            
            // Send to guidance
            if ($meeting['guidance_email']) {
                $subject = "IEP Meeting Invitation - {$assessment['student_name']}";
                $body = "
                <h2>IEP Meeting Invitation</h2>
                <p>Dear {$meeting['guidance_name']},</p>
                <p>You are invited to an IEP meeting.</p>
                
                <h3>Meeting Details</h3>
                <p><strong>Student:</strong> {$assessment['student_name']}</p>
                <p><strong>Date & Time:</strong> $meetingDateFormatted</p>
                <p><strong>Location:</strong> {$meeting['meeting_location']}</p>
                
                <p><a href='" . BASE_PATH . "/iep/meetings/$meetingId' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>View Meeting</a></p>
                
                <p>Best regards,<br>SPED LMS System</p>
                ";
                MailHelper::send($meeting['guidance_email'], $subject, $body);
            }
            
            // Send to principal
            if ($meeting['principal_email']) {
                $subject = "IEP Meeting Invitation - {$assessment['student_name']}";
                $body = "
                <h2>IEP Meeting Invitation</h2>
                <p>Dear {$meeting['principal_name']},</p>
                <p>You are invited to an IEP meeting.</p>
                
                <h3>Meeting Details</h3>
                <p><strong>Student:</strong> {$assessment['student_name']}</p>
                <p><strong>Date & Time:</strong> $meetingDateFormatted</p>
                <p><strong>Location:</strong> {$meeting['meeting_location']}</p>
                
                <p><a href='" . BASE_PATH . "/iep/meetings/$meetingId' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>View Meeting</a></p>
                
                <p>Best regards,<br>SPED LMS System</p>
                ";
                MailHelper::send($meeting['principal_email'], $subject, $body);
            }
            
        } catch (Exception $e) {
            error_log("Failed to send invitations: " . $e->getMessage());
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

    /**
     * Upload calendar availability
     */
    public function uploadCalendar() {
        try {
            // Only Guidance and Principal can upload calendars
            if (!in_array($this->userRole, ['guidance', 'principal'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            // Check if file was uploaded
            if (!isset($_FILES['calendar_file'])) {
                echo json_encode(['success' => false, 'message' => 'No file uploaded']);
                exit;
            }
            
            $file = $_FILES['calendar_file'];
            $validFrom = $_POST['valid_from'] ?? date('Y-m-d');
            $validUntil = $_POST['valid_until'] ?? date('Y-m-d', strtotime('+1 month'));
            
            // Validate file
            $allowedTypes = ['text/calendar', 'application/pdf', 'text/plain'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only ICS, PDF, or text files allowed']);
                exit;
            }
            
            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../public/uploads/calendars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $filename = 'calendar_' . $this->userId . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filePath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
                exit;
            }
            
            // Parse calendar data (basic parsing for ICS files)
            $availabilityData = [];
            if ($file['type'] === 'text/calendar') {
                $availabilityData = $this->parseICSFile($filePath);
            }
            
            // Save to database
            $model = new IEPMeetingModel();
            $calendarId = $model->uploadCalendar(
                $this->userId,
                '/uploads/calendars/' . $filename,
                $availabilityData,
                $validFrom,
                $validUntil
            );
            
            $this->logActivity('calendar.upload', 'iep_meeting_calendars', $calendarId, 'Uploaded calendar availability');
            
            echo json_encode([
                'success' => true,
                'message' => 'Calendar uploaded successfully',
                'calendar_id' => $calendarId
            ]);
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->uploadCalendar() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Parse ICS calendar file
     */
    private function parseICSFile($filePath) {
        $availabilityData = [];
        
        try {
            $content = file_get_contents($filePath);
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                if (strpos($line, 'DTSTART') === 0) {
                    preg_match('/DTSTART[^:]*:(.*)/', $line, $matches);
                    if (isset($matches[1])) {
                        $availabilityData[] = ['start' => trim($matches[1])];
                    }
                }
                if (strpos($line, 'DTEND') === 0) {
                    preg_match('/DTEND[^:]*:(.*)/', $line, $matches);
                    if (isset($matches[1]) && !empty($availabilityData)) {
                        $availabilityData[count($availabilityData) - 1]['end'] = trim($matches[1]);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Failed to parse ICS file: " . $e->getMessage());
        }
        
        return $availabilityData;
    }

    /**
     * Get meetings for current user (Guidance/Principal view)
     */
    public function listMeetings() {
        try {
            if (!in_array($this->userRole, ['guidance', 'principal', 'sped_teacher'])) {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            $model = new IEPMeetingModel();
            
            if ($this->userRole === 'sped_teacher') {
                // SPED Teacher sees all meetings they scheduled
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT im.*, sr.student_name, sr.lrn, 
                           u_guidance.name as guidance_name, u_principal.name as principal_name
                    FROM iep_meetings im
                    JOIN student_records sr ON im.student_id = sr.id
                    LEFT JOIN users u_guidance ON im.guidance_id = u_guidance.id
                    LEFT JOIN users u_principal ON im.principal_id = u_principal.id
                    WHERE im.scheduled_by = ?
                    ORDER BY im.meeting_date DESC
                ");
                $stmt->execute([$this->userId]);
                $meetings = $stmt->fetchAll();
            } else {
                // Guidance or Principal
                $meetings = $model->getMeetingsForUser($this->userId, $this->userRole);
            }
            
            $this->logActivity('iep_meeting.list', 'iep_meetings', null, 'Viewed meetings list');
            
            require __DIR__ . '/../Views/iep_meeting/index.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->listMeetings() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading meetings: " . htmlspecialchars($e->getMessage());
        }
    }
}
