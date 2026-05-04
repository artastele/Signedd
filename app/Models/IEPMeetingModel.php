<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP Meeting Model

require_once __DIR__ . '/../../config/db.php';

class IEPMeetingModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create IEP meeting
     */
    public function create($studentId, $assessmentId, $meetingData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_meetings (
                    student_id, assessment_id, meeting_date, meeting_location,
                    agenda, guidance_id, principal_id, scheduled_by, status
                ) VALUES (
                    :student_id, :assessment_id, :meeting_date, :meeting_location,
                    :agenda, :guidance_id, :principal_id, :scheduled_by, 'scheduled'
                )
            ");
            
            $result = $stmt->execute([
                'student_id' => $studentId,
                'assessment_id' => $assessmentId,
                'meeting_date' => $meetingData['meeting_date'],
                'meeting_location' => $meetingData['meeting_location'] ?? '',
                'agenda' => $meetingData['agenda'] ?? '',
                'guidance_id' => $meetingData['guidance_id'] ?? null,
                'principal_id' => $meetingData['principal_id'] ?? null,
                'scheduled_by' => $meetingData['scheduled_by']
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create meeting");
            }
            
            $meetingId = $this->db->lastInsertId();
            error_log("Created IEP meeting ID: $meetingId for student: $studentId");
            
            return $meetingId;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get meeting by ID
     */
    public function findById($meetingId) {
        $stmt = $this->db->prepare("
            SELECT im.*, sr.student_name, sr.lrn,
                   u_guidance.name as guidance_name, u_guidance.email as guidance_email,
                   u_principal.name as principal_name, u_principal.email as principal_email,
                   u_sped.name as sped_teacher_name
            FROM iep_meetings im
            JOIN student_records sr ON im.student_id = sr.id
            LEFT JOIN users u_guidance ON im.guidance_id = u_guidance.id
            LEFT JOIN users u_principal ON im.principal_id = u_principal.id
            LEFT JOIN users u_sped ON im.scheduled_by = u_sped.id
            WHERE im.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $meetingId]);
        return $stmt->fetch();
    }

    /**
     * Get all meetings for a student
     */
    public function getByStudentId($studentId) {
        $stmt = $this->db->prepare("
            SELECT im.*, sr.student_name, sr.lrn
            FROM iep_meetings im
            JOIN student_records sr ON im.student_id = sr.id
            WHERE im.student_id = :student_id
            ORDER BY im.meeting_date DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get scheduled meetings
     */
    public function getScheduled() {
        $stmt = $this->db->prepare("
            SELECT im.*, sr.student_name, sr.lrn
            FROM iep_meetings im
            JOIN student_records sr ON im.student_id = sr.id
            WHERE im.status = 'scheduled'
            ORDER BY im.meeting_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get available time slots for a user on a specific date
     */
    public function getAvailableSlots($userId, $date) {
        // Get all meetings for this user on this date
        $stmt = $this->db->prepare("
            SELECT meeting_date FROM iep_meetings
            WHERE (scheduled_by = :user_id OR guidance_id = :user_id OR principal_id = :user_id)
            AND DATE(meeting_date) = :date
            AND status != 'cancelled'
        ");
        $stmt->execute([
            'user_id' => $userId,
            'date' => $date
        ]);
        $bookedSlots = $stmt->fetchAll();
        
        // Generate available slots (9 AM to 5 PM, 1-hour slots)
        $availableSlots = [];
        $bookedTimes = array_map(fn($slot) => date('H:i', strtotime($slot['meeting_date'])), $bookedSlots);
        
        for ($hour = 9; $hour < 17; $hour++) {
            $time = sprintf("%02d:00", $hour);
            if (!in_array($time, $bookedTimes)) {
                $availableSlots[] = $time;
            }
        }
        
        return $availableSlots;
    }

    /**
     * Update meeting status
     */
    public function updateStatus($meetingId, $status) {
        try {
            $column = match($status) {
                'completed' => 'completed_at',
                'cancelled' => 'cancelled_at',
                default => null
            };
            
            if (!$column) {
                throw new Exception("Invalid status: $status");
            }
            
            $stmt = $this->db->prepare("
                UPDATE iep_meetings
                SET status = :status, $column = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'status' => $status,
                'id' => $meetingId
            ]);
            
            if (!$result) {
                throw new Exception("Failed to update meeting status");
            }
            
            error_log("Updated meeting ID: $meetingId status to: $status");
            return true;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->updateStatus() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel meeting
     */
    public function cancel($meetingId, $reason) {
        try {
            $stmt = $this->db->prepare("
                UPDATE iep_meetings
                SET status = 'cancelled', cancelled_at = NOW(), cancellation_reason = :reason
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $meetingId,
                'reason' => $reason
            ]);
            
            if (!$result) {
                throw new Exception("Failed to cancel meeting");
            }
            
            error_log("Cancelled meeting ID: $meetingId");
            return true;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->cancel() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload calendar availability
     */
    public function uploadCalendar($userId, $filePath, $availabilityData, $validFrom, $validUntil) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_meeting_calendars (user_id, calendar_file_path, availability_data, valid_from, valid_until)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $userId,
                $filePath,
                json_encode($availabilityData),
                $validFrom,
                $validUntil
            ]);
            
            if (!$result) {
                throw new Exception("Failed to upload calendar");
            }
            
            $calendarId = $this->db->lastInsertId();
            error_log("Uploaded calendar ID: $calendarId for user: $userId");
            
            return $calendarId;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->uploadCalendar() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get latest calendar for user
     */
    public function getLatestCalendar($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM iep_meeting_calendars
            WHERE user_id = ?
            ORDER BY uploaded_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Get meetings for user (Guidance or Principal)
     */
    public function getMeetingsForUser($userId, $role) {
        $column = ($role === 'guidance') ? 'guidance_id' : 'principal_id';
        
        $stmt = $this->db->prepare("
            SELECT im.*, sr.student_name, sr.lrn, ar.assessment_type
            FROM iep_meetings im
            JOIN student_records sr ON im.student_id = sr.id
            LEFT JOIN assessment_records ar ON im.assessment_id = ar.id
            WHERE im.$column = ?
            ORDER BY im.meeting_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
