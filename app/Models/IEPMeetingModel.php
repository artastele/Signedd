<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-07
// Part of: SPED LMS — IEP Meeting Model

require_once __DIR__ . '/../../config/db.php';

class IEPMeetingModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get recurring availability (weekly schedule) for a user
     * Returns array of day_of_week => is_available
     */
    public function getRecurringAvailability($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT day_of_week, is_available
                FROM user_availability
                WHERE user_id = :user_id AND type = 'recurring'
                ORDER BY day_of_week
            ");
            $stmt->execute(['user_id' => $userId]);
            $results = $stmt->fetchAll();
            
            $availability = [];
            foreach ($results as $row) {
                $availability[$row['day_of_week']] = (bool)$row['is_available'];
            }
            
            return $availability;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->getRecurringAvailability() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save recurring availability (weekly schedule) for a user
     * $days = array of day_of_week (0-6) that user is available
     */
    public function saveRecurringAvailability($userId, $days) {
        try {
            // Delete existing recurring availability
            $stmt = $this->db->prepare("
                DELETE FROM user_availability
                WHERE user_id = :user_id AND type = 'recurring'
            ");
            $stmt->execute(['user_id' => $userId]);
            
            // Insert new recurring availability
            if (!empty($days)) {
                $stmt = $this->db->prepare("
                    INSERT INTO user_availability (user_id, type, day_of_week, is_available)
                    VALUES (:user_id, 'recurring', :day_of_week, TRUE)
                ");
                
                foreach ($days as $day) {
                    $stmt->execute([
                        'user_id' => $userId,
                        'day_of_week' => $day
                    ]);
                }
            }
            
            error_log("Saved recurring availability for user: $userId");
            return true;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->saveRecurringAvailability() FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get exception dates for a user
     * Returns array of date => is_available
     */
    public function getExceptions($userId, $startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT specific_date, is_available
                FROM user_availability
                WHERE user_id = :user_id AND type = 'exception'
            ";
            
            $params = ['user_id' => $userId];
            
            if ($startDate && $endDate) {
                $sql .= " AND specific_date BETWEEN :start_date AND :end_date";
                $params['start_date'] = $startDate;
                $params['end_date'] = $endDate;
            }
            
            $sql .= " ORDER BY specific_date";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            
            $exceptions = [];
            foreach ($results as $row) {
                $exceptions[$row['specific_date']] = (bool)$row['is_available'];
            }
            
            return $exceptions;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->getExceptions() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Toggle exception date for a user
     * If exception exists, delete it. If not, create it with opposite of recurring availability.
     */
    public function toggleException($userId, $date, $isAvailable) {
        try {
            // Check if exception already exists
            $stmt = $this->db->prepare("
                SELECT id FROM user_availability
                WHERE user_id = :user_id AND type = 'exception' AND specific_date = :date
            ");
            $stmt->execute(['user_id' => $userId, 'date' => $date]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Delete existing exception
                $stmt = $this->db->prepare("
                    DELETE FROM user_availability
                    WHERE id = :id
                ");
                $stmt->execute(['id' => $existing['id']]);
                error_log("Deleted exception for user $userId on $date");
            } else {
                // Create new exception
                $stmt = $this->db->prepare("
                    INSERT INTO user_availability (user_id, type, specific_date, is_available)
                    VALUES (:user_id, 'exception', :date, :is_available)
                ");
                $stmt->execute([
                    'user_id' => $userId,
                    'date' => $date,
                    'is_available' => $isAvailable
                ]);
                error_log("Created exception for user $userId on $date (available: $isAvailable)");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->toggleException() FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is available on a specific date
     * Considers both recurring availability and exceptions
     */
    public function isUserAvailable($userId, $date) {
        try {
            // Check for exception first
            $stmt = $this->db->prepare("
                SELECT is_available FROM user_availability
                WHERE user_id = :user_id AND type = 'exception' AND specific_date = :date
            ");
            $stmt->execute(['user_id' => $userId, 'date' => $date]);
            $exception = $stmt->fetch();
            
            if ($exception) {
                // Exception overrides recurring
                return (bool)$exception['is_available'];
            }
            
            // Check recurring availability
            $dayOfWeek = date('w', strtotime($date)); // 0=Sunday, 6=Saturday
            $stmt = $this->db->prepare("
                SELECT is_available FROM user_availability
                WHERE user_id = :user_id AND type = 'recurring' AND day_of_week = :day_of_week
            ");
            $stmt->execute(['user_id' => $userId, 'day_of_week' => $dayOfWeek]);
            $recurring = $stmt->fetch();
            
            if ($recurring) {
                return (bool)$recurring['is_available'];
            }
            
            // Default: not available
            return false;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->isUserAvailable() FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get common available dates for multiple users
     * Returns array of dates when ALL users are available
     */
    public function getCommonAvailableDates($userIds, $startDate, $endDate) {
        try {
            $availableDates = [];
            
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $allAvailable = true;
                
                foreach ($userIds as $userId) {
                    if (!$this->isUserAvailable($userId, $dateStr)) {
                        $allAvailable = false;
                        break;
                    }
                }
                
                if ($allAvailable) {
                    $availableDates[] = $dateStr;
                }
            }
            
            return $availableDates;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->getCommonAvailableDates() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get suggested dates for IEP meeting (next 60 days when all participants available)
     */
    public function getSuggestedDates() {
        try {
            // Get user IDs for SPED Teacher, Guidance, Principal roles
            $stmt = $this->db->prepare("
                SELECT id FROM users
                WHERE role IN ('sped_teacher', 'guidance', 'principal')
                AND status = 'active'
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($users) < 3) {
                return []; // Need at least 3 participants
            }
            
            // Get common available dates for next 60 days
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+60 days'));
            
            return $this->getCommonAvailableDates($users, $startDate, $endDate);
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->getSuggestedDates() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create IEP meeting
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_meetings (
                    student_id, assessment_id, scheduled_by, meeting_date, meeting_time,
                    meeting_location, agenda, status, created_at, updated_at
                ) VALUES (
                    :student_id, :assessment_id, :scheduled_by, :meeting_date, :meeting_time,
                    :meeting_location, :agenda, 'scheduled', NOW(), NOW()
                )
            ");
            
            $result = $stmt->execute([
                'student_id' => $data['student_id'],
                'assessment_id' => $data['assessment_id'] ?? null,
                'scheduled_by' => $data['scheduled_by'],
                'meeting_date' => $data['meeting_date'],
                'meeting_time' => $data['meeting_time'],
                'meeting_location' => $data['meeting_location'] ?? $data['venue'] ?? '',
                'agenda' => $data['agenda_notes'] ?? $data['agenda'] ?? null
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create meeting");
            }
            
            $meetingId = $this->db->lastInsertId();
            error_log("Created IEP meeting ID: $meetingId");
            
            return $meetingId;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all meetings
     */
    public function getAll($filters = []) {
        try {
            $sql = "
                SELECT 
                    m.*,
                    s.student_name,
                    s.lrn,
                    u.name as scheduled_by_name
                FROM iep_meetings m
                JOIN student_records s ON m.student_id = s.id
                JOIN users u ON m.scheduled_by = u.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND m.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['student_id'])) {
                $sql .= " AND m.student_id = :student_id";
                $params['student_id'] = $filters['student_id'];
            }
            
            if (!empty($filters['upcoming'])) {
                $sql .= " AND m.meeting_date >= CURDATE()";
            }
            
            $sql .= " ORDER BY m.meeting_date DESC, m.meeting_time DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->getAll() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find meeting by ID
     */
    public function findById($meetingId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    s.student_name,
                    s.lrn,
                    s.date_of_birth,
                    u.name as scheduled_by_name,
                    u.email as scheduled_by_email
                FROM iep_meetings m
                JOIN student_records s ON m.student_id = s.id
                JOIN users u ON m.scheduled_by = u.id
                WHERE m.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $meetingId]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->findById() FAILED: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update meeting
     */
    public function update($meetingId, $data) {
        try {
            $fields = [];
            $params = ['id' => $meetingId];
            
            $allowedFields = ['meeting_date', 'meeting_time', 'venue', 'online_link', 'agenda_notes', 'status', 'reschedule_reason'];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $fields[] = "$key = :$key";
                    $params[$key] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $sql = "UPDATE iep_meetings SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            error_log("Updated meeting ID: $meetingId");
            
            return $result;
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->update() FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reschedule meeting
     */
    public function reschedule($meetingId, $newDate, $newTime, $reason) {
        try {
            return $this->update($meetingId, [
                'meeting_date' => $newDate,
                'meeting_time' => $newTime,
                'status' => 'rescheduled',
                'reschedule_reason' => $reason
            ]);
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->reschedule() FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save meeting notification
     */
    public function saveNotification($meetingId, $userId, $notifiedVia = 'both') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO meeting_notifications (meeting_id, user_id, notified_via, sent_at)
                VALUES (:meeting_id, :user_id, :notified_via, NOW())
            ");
            
            return $stmt->execute([
                'meeting_id' => $meetingId,
                'user_id' => $userId,
                'notified_via' => $notifiedVia
            ]);
            
        } catch (Exception $e) {
            error_log("IEPMeetingModel->saveNotification() FAILED: " . $e->getMessage());
            return false;
        }
    }
}
