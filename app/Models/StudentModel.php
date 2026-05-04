<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-05-04
// Part of: SPED LMS — Student Record Model

require_once __DIR__ . '/../../config/db.php';

class StudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Generate unique 12-digit LRN
     * Format: YYYYMMDDNNNN (Year + Month + Day + 4-digit sequence)
     * Example: 202605040001
     */
    public function generateLRN() {
        $datePrefix = date('Ymd'); // YYYYMMDD
        
        // Find the next sequence number for today
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM student_records
            WHERE lrn LIKE :date_prefix
        ");
        $stmt->execute(['date_prefix' => $datePrefix . '%']);
        $result = $stmt->fetch();
        
        $sequence = ($result['count'] + 1);
        $sequenceStr = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        $lrn = $datePrefix . $sequenceStr;
        
        error_log("Generated LRN: $lrn");
        return $lrn;
    }

    /**
     * Create student record from enrollment
     */
    public function createStudentRecord($enrollmentId, $verifiedBy) {
        try {
            // Get enrollment data
            $stmt = $this->db->prepare("
                SELECT * FROM enrollment_submissions
                WHERE id = :id
            ");
            $stmt->execute(['id' => $enrollmentId]);
            $enrollment = $stmt->fetch();
            
            if (!$enrollment) {
                throw new Exception("Enrollment not found: $enrollmentId");
            }
            
            // Generate LRN
            $lrn = $this->generateLRN();
            
            // Create student record
            $stmt = $this->db->prepare("
                INSERT INTO student_records (
                    enrollment_id, lrn, student_name, date_of_birth,
                    disability_type, psa_number, pwd_id_number, verified_by
                ) VALUES (
                    :enrollment_id, :lrn, :student_name, :date_of_birth,
                    :disability_type, :psa_number, :pwd_id_number, :verified_by
                )
            ");
            
            // Determine disability type from enrollment
            $disabilities = [];
            if ($enrollment['disability_visual']) $disabilities[] = 'Visual';
            if ($enrollment['disability_hearing']) $disabilities[] = 'Hearing';
            if ($enrollment['disability_learning']) $disabilities[] = 'Learning';
            if ($enrollment['disability_speech']) $disabilities[] = 'Speech';
            if ($enrollment['disability_intellectual']) $disabilities[] = 'Intellectual';
            if ($enrollment['disability_physical']) $disabilities[] = 'Physical';
            if ($enrollment['disability_emotional']) $disabilities[] = 'Emotional';
            if ($enrollment['disability_chronic_illness']) $disabilities[] = 'Chronic Illness';
            if ($enrollment['disability_others']) $disabilities[] = $enrollment['disability_others_specify'];
            
            $disabilityType = !empty($disabilities) ? implode(', ', $disabilities) : 'Not specified';
            
            $result = $stmt->execute([
                'enrollment_id' => $enrollmentId,
                'lrn' => $lrn,
                'student_name' => $enrollment['first_name'] . ' ' . $enrollment['last_name'],
                'date_of_birth' => $enrollment['birth_date'],
                'disability_type' => $disabilityType,
                'psa_number' => $enrollment['lrn'] ?? null,
                'pwd_id_number' => null,
                'verified_by' => $verifiedBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create student record");
            }
            
            $studentId = $this->db->lastInsertId();
            error_log("Created student record ID: $studentId with LRN: $lrn");
            
            return [
                'id' => $studentId,
                'lrn' => $lrn,
                'name' => $enrollment['first_name'] . ' ' . $enrollment['last_name']
            ];
            
        } catch (Exception $e) {
            error_log("StudentModel->createStudentRecord() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create learner account with LRN as credentials
     * Returns: ['user_id' => id, 'lrn' => lrn, 'temp_password' => password]
     */
    public function createLearnerAccount($studentId, $lrn, $enrollmentData) {
        try {
            // Generate temporary password (8 characters)
            $tempPassword = bin2hex(random_bytes(4)); // 8 hex chars
            $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);
            
            // Get parent email from enrollment
            $stmt = $this->db->prepare("
                SELECT u.id, u.email, u.first_name, u.last_name
                FROM enrollment_submissions es
                JOIN users u ON es.parent_id = u.id
                WHERE es.id = :enrollment_id
            ");
            $stmt->execute(['enrollment_id' => $enrollmentData['enrollment_id']]);
            $parent = $stmt->fetch();
            
            if (!$parent) {
                throw new Exception("Parent not found for enrollment");
            }
            
            // Create learner user account
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    name, first_name, last_name, email, password_hash,
                    role, status, email_verified, auth_provider
                ) VALUES (
                    :name, :first_name, :last_name, :email, :password_hash,
                    'learner', 'active', TRUE, 'local'
                )
            ");
            
            $learnerName = $enrollmentData['first_name'] . ' ' . $enrollmentData['last_name'];
            $learnerEmail = 'learner_' . $lrn . '@spedlms.local'; // System email
            
            $result = $stmt->execute([
                'name' => $learnerName,
                'first_name' => $enrollmentData['first_name'],
                'last_name' => $enrollmentData['last_name'],
                'email' => $learnerEmail,
                'password_hash' => $passwordHash
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create learner account");
            }
            
            $userId = $this->db->lastInsertId();
            error_log("Created learner account ID: $userId with LRN: $lrn");
            
            // Send credentials email to parent
            $this->sendLRNCredentialsEmail($parent['email'], $learnerName, $lrn, $tempPassword);
            
            // Create in-app notification for parent
            $this->createLRNNotification($parent['id'], $learnerName, $lrn, $tempPassword);
            
            return [
                'user_id' => $userId,
                'lrn' => $lrn,
                'temp_password' => $tempPassword,
                'parent_email' => $parent['email']
            ];
            
        } catch (Exception $e) {
            error_log("StudentModel->createLearnerAccount() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send LRN credentials email to parent
     */
    private function sendLRNCredentialsEmail($parentEmail, $learnerName, $lrn, $tempPassword) {
        try {
            require_once __DIR__ . '/../Helpers/MailHelper.php';
            
            $subject = "Learner Account Created - LRN: $lrn";
            
            $body = "
            <h2>Learner Account Created</h2>
            <p>Dear Parent/Guardian,</p>
            <p>Your child's enrollment has been verified and a learner account has been created.</p>
            
            <h3>Learner Information</h3>
            <p><strong>Name:</strong> $learnerName</p>
            <p><strong>LRN (Learner Reference Number):</strong> <strong>$lrn</strong></p>
            
            <h3>Login Credentials</h3>
            <p><strong>LRN:</strong> $lrn</p>
            <p><strong>Temporary Password:</strong> $tempPassword</p>
            
            <p style='color: #a01422; font-weight: bold;'>
                Please change your password on first login for security.
            </p>
            
            <p>If you have any questions, please contact the school.</p>
            <p>Best regards,<br>SPED LMS System</p>
            ";
            
            MailHelper::send($parentEmail, $subject, $body);
            error_log("LRN credentials email sent to: $parentEmail");
            
        } catch (Exception $e) {
            error_log("Failed to send LRN credentials email: " . $e->getMessage());
            // Don't throw - email failure shouldn't block account creation
        }
    }

    /**
     * Create in-app notification for LRN credentials
     */
    private function createLRNNotification($parentId, $learnerName, $lrn, $tempPassword) {
        try {
            require_once __DIR__ . '/../Models/NotificationModel.php';
            
            $notificationModel = new NotificationModel();
            $notificationModel->create(
                $parentId,
                'learner_account_created',
                'Learner Account Created - LRN Credentials',
                "Your child $learnerName's learner account has been created. LRN: $lrn. Check your email for login credentials.",
                [
                    'learner_name' => $learnerName,
                    'lrn' => $lrn,
                    'temp_password' => $tempPassword
                ]
            );
            
            error_log("LRN notification created for parent ID: $parentId");
            
        } catch (Exception $e) {
            error_log("Failed to create LRN notification: " . $e->getMessage());
            // Don't throw - notification failure shouldn't block account creation
        }
    }

    /**
     * Find student by LRN
     */
    public function findByLRN($lrn) {
        $stmt = $this->db->prepare("
            SELECT * FROM student_records
            WHERE lrn = :lrn
            LIMIT 1
        ");
        $stmt->execute(['lrn' => $lrn]);
        return $stmt->fetch();
    }

    /**
     * Find student by enrollment ID
     */
    public function findByEnrollmentId($enrollmentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM student_records
            WHERE enrollment_id = :enrollment_id
            LIMIT 1
        ");
        $stmt->execute(['enrollment_id' => $enrollmentId]);
        return $stmt->fetch();
    }

    /**
     * Get student with enrollment and parent info
     */
    public function getWithDetails($studentId) {
        $stmt = $this->db->prepare("
            SELECT sr.*, es.*, u.name as parent_name, u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            JOIN users u ON es.parent_id = u.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * Get all students (for admin/teacher)
     */
    public function getAll($limit = 100) {
        $stmt = $this->db->prepare("
            SELECT sr.*, es.*, u.name as parent_name, u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            JOIN users u ON es.parent_id = u.id
            ORDER BY sr.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update student record
     */
    public function update($studentId, $data) {
        $fields = [];
        $params = ['id' => $studentId];
        
        $validColumns = ['student_name', 'date_of_birth', 'disability_type', 'psa_number', 'pwd_id_number'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $validColumns)) {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE student_records SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("StudentModel->update() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete student record
     */
    public function delete($studentId) {
        $stmt = $this->db->prepare("
            DELETE FROM student_records
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $studentId]);
    }
}
