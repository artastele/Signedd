<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-06-28
// Part of: SPED LMS — Student Record Model

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Helpers/StudentDisplayHelper.php';

class StudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Generate unique internal Student ID.
     * Format: YYYYNNNN (year + 4-digit sequence, no hyphen).
     * Example: 20250001
     */
    public function generateStudentId() {
        $year = date('Y');

        $stmt = $this->db->prepare("
            SELECT student_id
            FROM student_records
            WHERE student_id LIKE :year_prefix
            ORDER BY student_id DESC
            LIMIT 1
        ");
        $stmt->execute(['year_prefix' => $year . '%']);
        $result = $stmt->fetch();

        $sequence = 1;
        if ($result && preg_match('/^' . preg_quote($year, '/') . '(\d{4})$/', $result['student_id'], $m)) {
            $sequence = (int)$m[1] + 1;
        }

        $studentId = $year . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
        error_log("Generated Student ID: $studentId");
        return $studentId;
    }

    /**
     * Create student record from enrollment.
     * Auto-generates internal student_id; LRN is optional from enrollment only.
     */
    public function createStudentRecord($enrollmentId, $verifiedBy) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM enrollment_submissions
                WHERE id = :id
            ");
            $stmt->execute(['id' => $enrollmentId]);
            $enrollment = $stmt->fetch();

            if (!$enrollment) {
                throw new Exception("Enrollment not found: $enrollmentId");
            }

            $studentIdCode = $this->generateStudentId();

            $lrn = null;
            if (!empty($enrollment['lrn'])) {
                $lrn = trim($enrollment['lrn']);
                if ($lrn === '') {
                    $lrn = null;
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO student_records (
                    enrollment_id, student_id, lrn, student_name, date_of_birth,
                    disability_type, psa_number, pwd_id_number, verified_by
                ) VALUES (
                    :enrollment_id, :student_id, :lrn, :student_name, :date_of_birth,
                    :disability_type, :psa_number, :pwd_id_number, :verified_by
                )
            ");

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
                'student_id' => $studentIdCode,
                'lrn' => $lrn,
                'student_name' => $enrollment['first_name'] . ' ' . $enrollment['last_name'],
                'date_of_birth' => $enrollment['birth_date'],
                'disability_type' => $disabilityType,
                'psa_number' => null,
                'pwd_id_number' => null,
                'verified_by' => $verifiedBy
            ]);

            if (!$result) {
                throw new Exception("Failed to create student record");
            }

            $recordId = $this->db->lastInsertId();
            error_log("Created student record ID: $recordId with Student ID: $studentIdCode");

            return [
                'id' => $recordId,
                'student_id' => $studentIdCode,
                'lrn' => $lrn,
                'name' => $enrollment['first_name'] . ' ' . $enrollment['last_name']
            ];

        } catch (Exception $e) {
            error_log("StudentModel->createStudentRecord() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create learner account using internal Student ID as login identifier.
     */
    public function createLearnerAccount($recordId, $studentIdCode, $enrollmentData) {
        try {
            $tempPassword = bin2hex(random_bytes(4));
            $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

            $enrollmentId = $enrollmentData['enrollment_id'] ?? $enrollmentData['id'] ?? null;
            $stmt = $this->db->prepare("
                SELECT u.id, u.email, u.first_name, u.last_name
                FROM enrollment_submissions es
                JOIN users u ON es.parent_id = u.id
                WHERE es.id = :enrollment_id
            ");
            $stmt->execute(['enrollment_id' => $enrollmentId]);
            $parent = $stmt->fetch();

            if (!$parent) {
                throw new Exception("Parent not found for enrollment");
            }

            $learnerName = $enrollmentData['first_name'] . ' ' . $enrollmentData['last_name'];
            $learnerEmail = 'learner_' . $studentIdCode . '@spedlms.local';

            $stmt = $this->db->prepare("
                SELECT id, status FROM users WHERE email = :email
            ");
            $stmt->execute(['email' => $learnerEmail]);
            $existingUser = $stmt->fetch();

            $userId = null;
            $isExisting = false;

            if ($existingUser) {
                $userId = $existingUser['id'];
                $isExisting = true;

                $stmt = $this->db->prepare("
                    UPDATE users
                    SET password_hash = :password_hash,
                        status = 'active',
                        name = :name,
                        first_name = :first_name,
                        last_name = :last_name
                    WHERE id = :id
                ");

                $result = $stmt->execute([
                    'password_hash' => $passwordHash,
                    'name' => $learnerName,
                    'first_name' => $enrollmentData['first_name'],
                    'last_name' => $enrollmentData['last_name'],
                    'id' => $userId
                ]);

                if (!$result) {
                    throw new Exception("Failed to reset learner account password");
                }
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO users (
                        name, first_name, last_name, email, password_hash,
                        role, status, email_verified, auth_provider
                    ) VALUES (
                        :name, :first_name, :last_name, :email, :password_hash,
                        'learner', 'active', TRUE, 'local'
                    )
                ");

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
            }

            $parentName = $parent['first_name'] . ' ' . $parent['last_name'];
            $this->sendLearnerCredentialsEmail($parent['email'], $learnerName, $studentIdCode, $tempPassword, $parentName, $isExisting);
            $this->createLearnerAccountNotification($parent['id'], $learnerName, $studentIdCode, $tempPassword, $isExisting);

            return [
                'user_id' => $userId,
                'student_id' => $studentIdCode,
                'temp_password' => $tempPassword,
                'parent_email' => $parent['email'],
                'is_existing' => $isExisting
            ];

        } catch (Exception $e) {
            error_log("StudentModel->createLearnerAccount() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    private function sendLearnerCredentialsEmail($parentEmail, $learnerName, $studentIdCode, $tempPassword, $parentName = 'Parent/Guardian', $isExisting = false) {
        try {
            if (!class_exists('MailHelper')) {
                require_once __DIR__ . '/../Helpers/MailHelper.php';
            }

            if ($isExisting) {
                $subject = "Learner Account Password Reset - Student ID: $studentIdCode";
                $body = "
                <h2>Learner Account Password Reset</h2>
                <p>Dear $parentName,</p>
                <p>Your child's enrollment has been re-verified and the learner account password has been reset.</p>
                <h3>Learner Information</h3>
                <p><strong>Name:</strong> $learnerName</p>
                <p><strong>Student ID:</strong> <strong>$studentIdCode</strong></p>
                <h3>New Login Credentials</h3>
                <p><strong>Username (Student ID):</strong> $studentIdCode</p>
                <p><strong>New Temporary Password:</strong> $tempPassword</p>
                <p style='color: #a01422; font-weight: bold;'>Please change your password on first login.</p>
                <p>Best regards,<br>SPED LMS System</p>
                ";
            } else {
                $subject = "Learner Account Created - Student ID: $studentIdCode";
                $body = "
                <h2>Learner Account Created</h2>
                <p>Dear $parentName,</p>
                <p>Your child's enrollment has been verified and a learner account has been created.</p>
                <h3>Learner Information</h3>
                <p><strong>Name:</strong> $learnerName</p>
                <p><strong>Student ID:</strong> <strong>$studentIdCode</strong></p>
                <h3>Login Credentials</h3>
                <p><strong>Username (Student ID):</strong> $studentIdCode</p>
                <p><strong>Temporary Password:</strong> $tempPassword</p>
                <p style='color: #a01422; font-weight: bold;'>Please change your password on first login for security.</p>
                <p>Best regards,<br>SPED LMS System</p>
                ";
            }

            @MailHelper::sendNotification($parentEmail, $parentName, $subject, $body);

        } catch (Throwable $e) {
            error_log("Failed to send learner credentials email: " . $e->getMessage());
        }
    }

    private function createLearnerAccountNotification($parentId, $learnerName, $studentIdCode, $tempPassword, $isExisting = false) {
        try {
            require_once __DIR__ . '/../Models/NotificationModel.php';
            $notificationModel = new NotificationModel();

            if ($isExisting) {
                $notificationModel->create(
                    $parentId,
                    'learner_password_reset',
                    'Learner Account Password Reset',
                    "Your child $learnerName's learner account password has been reset. Student ID: $studentIdCode. Check your email for new login credentials.",
                    [
                        'learner_name' => $learnerName,
                        'student_id' => $studentIdCode,
                        'temp_password' => $tempPassword,
                        'is_existing' => true
                    ]
                );
            } else {
                $notificationModel->create(
                    $parentId,
                    'learner_account_created',
                    'Learner Account Created',
                    "Your child $learnerName's learner account has been created. Student ID: $studentIdCode. Check your email for login credentials.",
                    [
                        'learner_name' => $learnerName,
                        'student_id' => $studentIdCode,
                        'temp_password' => $tempPassword,
                        'is_existing' => false
                    ]
                );
            }
        } catch (Exception $e) {
            error_log("Failed to create learner account notification: " . $e->getMessage());
        }
    }

    public function findByStudentIdCode($studentIdCode) {
        $stmt = $this->db->prepare("
            SELECT * FROM student_records
            WHERE student_id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentIdCode]);
        return $stmt->fetch();
    }

    /** @deprecated Use findByStudentIdCode() */
    public function findByLRN($lrn) {
        $stmt = $this->db->prepare("
            SELECT * FROM student_records
            WHERE lrn = :lrn
            LIMIT 1
        ");
        $stmt->execute(['lrn' => $lrn]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        try {
            $stmt = $this->db->prepare("SELECT email FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return null;
            }

            if (preg_match('/learner_(\d{8})@/', $user['email'], $matches)) {
                return $this->findByStudentIdCode($matches[1]);
            }

            if (preg_match('/learner_(\d{12})@/', $user['email'], $matches)) {
                return $this->findByLRN($matches[1]);
            }

            return null;
        } catch (PDOException $e) {
            error_log('Failed to get student by user ID: ' . $e->getMessage());
            return null;
        }
    }

    public function findByEnrollmentId($enrollmentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM student_records
            WHERE enrollment_id = :enrollment_id
            LIMIT 1
        ");
        $stmt->execute(['enrollment_id' => $enrollmentId]);
        return $stmt->fetch();
    }

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

    public function update($studentId, $data) {
        $fields = [];
        $params = ['id' => $studentId];

        $validColumns = ['student_name', 'date_of_birth', 'disability_type', 'psa_number', 'pwd_id_number', 'lrn'];

        foreach ($data as $key => $value) {
            if (in_array($key, $validColumns)) {
                $fields[] = "$key = :$key";
                $params[$key] = ($key === 'lrn' && ($value === '' || $value === null)) ? null : $value;
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

    public function delete($studentId) {
        $stmt = $this->db->prepare("
            DELETE FROM student_records
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $studentId]);
    }

    public function getAllStudents() {
        $stmt = $this->db->query("
            SELECT 
                sr.*,
                es.school_year as latest_school_year,
                es.grade_level_to_enroll as current_grade_level,
                es.status as enrollment_status,
                es.parent_id,
                u.name as parent_name,
                u.email as parent_email
            FROM student_records sr
            LEFT JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN users u ON es.parent_id = u.id
            ORDER BY sr.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getEnrollmentsByStudentRecordId($recordId) {
        $stmt = $this->db->prepare("
            SELECT 
                es.*,
                u.name as parent_name,
                u.email as parent_email,
                verifier.name as verifier_name
            FROM enrollment_submissions es
            JOIN users u ON es.parent_id = u.id
            LEFT JOIN users verifier ON es.verified_by = verifier.id
            JOIN student_records sr ON sr.enrollment_id = es.id
            WHERE sr.id = :record_id AND es.is_draft = FALSE
            ORDER BY es.created_at DESC
        ");
        $stmt->execute(['record_id' => $recordId]);
        return $stmt->fetchAll();
    }

    /** @deprecated Use getEnrollmentsByStudentRecordId() */
    public function getEnrollmentsByLRN($lrn) {
        $stmt = $this->db->prepare("
            SELECT 
                es.*,
                u.name as parent_name,
                u.email as parent_email,
                verifier.name as verifier_name
            FROM enrollment_submissions es
            JOIN users u ON es.parent_id = u.id
            LEFT JOIN users verifier ON es.verified_by = verifier.id
            JOIN student_records sr ON sr.enrollment_id = es.id
            WHERE sr.lrn = :lrn AND es.is_draft = FALSE
            ORDER BY es.created_at DESC
        ");
        $stmt->execute(['lrn' => $lrn]);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                sr.*,
                es.parent_id,
                es.grade_level_to_enroll as current_grade_level,
                es.school_year as latest_school_year,
                es.status as enrollment_status,
                u.name as parent_name,
                u.email as parent_email,
                u.contact_number
            FROM student_records sr
            LEFT JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN users u ON es.parent_id = u.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getVerifiedStudents() {
        $stmt = $this->db->query("
            SELECT DISTINCT
                sr.id,
                sr.student_id,
                sr.lrn,
                sr.student_name,
                sr.date_of_birth,
                sr.disability_type,
                es.school_year,
                es.grade_level_to_enroll
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE es.status = 'verified'
            ORDER BY sr.student_name ASC
        ");
        return $stmt->fetchAll();
    }

    public function getFullDetails($studentId) {
        $stmt = $this->db->prepare("
            SELECT 
                sr.*,
                es.last_name,
                es.first_name,
                es.middle_name,
                es.extension_name,
                es.birth_date,
                es.sex,
                es.birth_place,
                es.mother_tongue,
                es.is_indigenous_people,
                es.indigenous_group,
                es.is_4ps_beneficiary,
                es.fourps_household_id,
                es.current_house_no,
                es.current_barangay,
                es.current_city,
                es.current_province,
                es.current_zip_code,
                es.father_last_name,
                es.father_first_name,
                es.father_middle_name,
                es.father_contact_number,
                es.mother_maiden_last_name,
                es.mother_first_name,
                es.mother_middle_name,
                es.mother_contact_number,
                es.guardian_last_name,
                es.guardian_first_name,
                es.guardian_middle_name,
                es.guardian_contact_number,
                es.previous_school_name,
                es.previous_grade_level,
                es.previous_school_year,
                es.grade_level_to_enroll,
                es.school_year,
                es.enrollment_type,
                u.name as parent_name,
                u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN users u ON es.parent_id = u.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $studentId]);
        $result = $stmt->fetch();

        if ($result) {
            $birthDate = new DateTime($result['birth_date']);
            $today = new DateTime();
            $result['age'] = $today->diff($birthDate)->y;

            $result['full_address'] = trim(
                ($result['current_house_no'] ? $result['current_house_no'] . ', ' : '') .
                ($result['current_barangay'] ? $result['current_barangay'] . ', ' : '') .
                ($result['current_city'] ? $result['current_city'] . ', ' : '') .
                ($result['current_province'] ? $result['current_province'] . ' ' : '') .
                ($result['current_zip_code'] ? $result['current_zip_code'] : '')
            );

            $result['father_full_name'] = trim(
                ($result['father_first_name'] ?? '') . ' ' .
                ($result['father_middle_name'] ?? '') . ' ' .
                ($result['father_last_name'] ?? '')
            );

            $result['mother_full_name'] = trim(
                ($result['mother_first_name'] ?? '') . ' ' .
                ($result['mother_middle_name'] ?? '') . ' ' .
                ($result['mother_maiden_last_name'] ?? '')
            );

            $result['guardian_full_name'] = trim(
                ($result['guardian_first_name'] ?? '') . ' ' .
                ($result['guardian_middle_name'] ?? '') . ' ' .
                ($result['guardian_last_name'] ?? '')
            );
        }

        return $result;
    }
}
