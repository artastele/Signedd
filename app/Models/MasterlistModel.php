<?php
// Part of: SignED — Stage 2 Masterlist & LIS Sync (SF1/SF2)
// Last modified: 2026-08-21
// Part of: SPED LMS — Masterlist Model

require_once __DIR__ . '/../../config/db.php';

class MasterlistModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all learners in the masterlist for a teacher, principal, or admin
     */
    public function getMasterlist(array $filters = []): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['teacher_id'])) {
            $where[] = "(sr.assigned_teacher_id = :teacher_id OR es.assigned_teacher_id = :teacher_id2)";
            $params['teacher_id'] = $filters['teacher_id'];
            $params['teacher_id2'] = $filters['teacher_id'];
        }

        if (!empty($filters['school_id'])) {
            $where[] = "sr.school_id = :school_id";
            $params['school_id'] = $filters['school_id'];
        }

        if (!empty($filters['disability_type'])) {
            $where[] = "sr.disability_type LIKE :disability_type";
            $params['disability_type'] = '%' . $filters['disability_type'] . '%';
        }

        if (!empty($filters['track'])) {
            $lmsCondition = "(sr.learning_track = 'lms' OR sr.learner_user_id IS NOT NULL OR es.lms_track = 'signed' OR es.lms_track = '1' OR es.lms_track = 1 OR es.learning_track = 'lms' OR (es.survey_has_internet = 1 AND (es.survey_willing_online = 1 OR es.willing_digital = 1)) OR (es.has_device = 1 AND es.willing_digital = 1) OR es.modality_online = 1 OR es.modality_modular_digital = 1)";
            if ($filters['track'] === 'lms') {
                $where[] = $lmsCondition;
            } elseif ($filters['track'] === 'traditional') {
                $where[] = "NOT " . $lmsCondition;
            }
        }

        if (!empty($filters['search'])) {
            $where[] = "(sr.student_name LIKE :search OR sr.lrn LIKE :search OR sr.student_id LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                sr.id AS student_record_id,
                sr.student_id,
                sr.lrn,
                sr.student_name,
                sr.date_of_birth,
                sr.disability_type,
                sr.section_name,
                sr.track_strand,
                sr.learning_track,
                sr.learner_user_id,
                sr.lis_status,
                sr.lis_synced_at,
                sr.claim_token,
                es.id AS enrollment_id,
                es.learning_track AS es_learning_track,
                es.lms_track,
                es.survey_has_internet,
                es.survey_willing_online,
                es.has_device,
                es.willing_digital,
                es.modality_online,
                es.modality_modular_digital,
                es.sex,
                es.age,
                es.grade_level_to_enroll,
                es.school_year,
                es.enrollment_type,
                es.is_indigenous_people,
                es.indigenous_group,
                es.is_4ps_beneficiary,
                es.fourps_household_id,
                es.current_house_no,
                es.current_barangay,
                es.current_city,
                es.current_province,
                es.father_last_name,
                es.father_first_name,
                es.father_contact_number,
                es.mother_maiden_last_name,
                es.mother_first_name,
                es.mother_contact_number,
                es.guardian_last_name,
                es.guardian_first_name,
                es.guardian_contact_number,
                es.last_name AS learner_last_name,
                es.first_name AS learner_first_name,
                es.middle_name AS learner_middle_name,
                es.extension_name AS learner_extension_name,
                es.status AS enrollment_status,
                es.birth_place,
                es.mother_tongue,
                es.father_middle_name,
                es.mother_middle_name,
                es.guardian_middle_name,
                sch.school_name,
                sch.school_id AS deped_school_id,
                sch.division,
                sch.region,
                u_parent.email AS parent_email,
                u_parent.name AS parent_name
            FROM student_records sr
            LEFT JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN schools sch ON sr.school_id = sch.id
            LEFT JOIN users u_parent ON es.parent_id = u_parent.id
            WHERE $whereClause
            ORDER BY es.grade_level_to_enroll ASC, sr.student_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single learner details for SF1 / Masterlist preview
     */
    public function getLearnerDetail(int $studentId): ?array {
        $list = $this->getMasterlist(['search' => (string)$studentId]);
        foreach ($list as $row) {
            if ((int)$row['student_record_id'] === $studentId) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Update section or LIS status for a learner
     */
    public function updateLisInfo(int $studentId, string $sectionName, string $lisStatus): bool {
        $stmt = $this->db->prepare("
            UPDATE student_records 
            SET section_name = :section,
                lis_status = :status,
                lis_synced_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'section' => $sectionName,
            'status' => $lisStatus,
            'id' => $studentId
        ]);
    }

    /**
     * Log LIS Sync actions (SF1 Export, SF2 Export, SF2 Import)
     */
    public function logSyncAction(int $userId, string $syncType, string $filename, int $recordsCount): bool {
        $stmt = $this->db->prepare("
            INSERT INTO lis_sync_logs (user_id, sync_type, filename, records_count)
            VALUES (:user_id, :sync_type, :filename, :records_count)
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'sync_type' => $syncType,
            'filename' => $filename,
            'records_count' => $recordsCount
        ]);
    }

    /**
     * Update LRN for 2-way LIS sync
     */
    public function updateLrn(string $identifier, string $lrn): bool {
        $lrn = trim($lrn);
        $identifier = trim($identifier);
        if (empty($lrn) || empty($identifier)) return false;

        $stmt = $this->db->prepare("
            UPDATE student_records 
            SET lrn = :lrn,
                lis_status = 'synced',
                lis_synced_at = NOW()
            WHERE student_id = :id_match 
               OR id = :rec_id_match
               OR student_name LIKE :name_match
        ");
        return $stmt->execute([
            'lrn'          => $lrn,
            'id_match'     => $identifier,
            'rec_id_match' => is_numeric($identifier) ? (int)$identifier : 0,
            'name_match'   => '%' . $identifier . '%'
        ]);
    }

    /**
     * Get recent LIS sync logs
     */
    public function getSyncLogs(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT lsl.*, u.name AS performed_by_name
            FROM lis_sync_logs lsl
            JOIN users u ON lsl.user_id = u.id
            ORDER BY lsl.performed_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get or generate a secure claim token for QR parent activation
     */
    public function generateClaimTokenForStudent(int $studentId): string {
        $stmt = $this->db->prepare("SELECT claim_token FROM student_records WHERE id = :id");
        $stmt->execute(['id' => $studentId]);
        $token = $stmt->fetchColumn();

        if (!empty($token)) {
            return $token;
        }

        $newToken = bin2hex(random_bytes(16));
        $updateStmt = $this->db->prepare("UPDATE student_records SET claim_token = :token WHERE id = :id");
        $updateStmt->execute(['token' => $newToken, 'id' => $studentId]);
        return $newToken;
    }

    /**
     * Look up learner details using a QR claim token
     */
    public function getLearnerByClaimToken(string $token): ?array {
        $stmt = $this->db->prepare("
            SELECT sr.id AS student_record_id, sr.student_id, sr.lrn, sr.student_name,
                   sr.date_of_birth, sr.disability_type, sr.learning_track, sr.school_id,
                   sch.school_name, sch.address AS school_address,
                   es.id AS enrollment_id, es.parent_id, es.first_name, es.last_name,
                   es.grade_level_to_enroll, es.guardian_first_name, es.guardian_last_name,
                   es.guardian_contact_number
            FROM student_records sr
            LEFT JOIN schools sch ON sr.school_id = sch.id
            LEFT JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE sr.claim_token = :token
            LIMIT 1
        ");
        $stmt->execute(['token' => trim($token)]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Link parent user account to the claimed learner
     */
    public function linkParentAccountToLearner(int $studentRecordId, int $parentId): ?array {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT id, student_name, student_id, lrn, enrollment_id, school_id, learner_user_id, learning_track 
                FROM student_records 
                WHERE id = :id
            ");
            $stmt->execute(['id' => $studentRecordId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->db->rollBack();
                return null;
            }

            $enrollmentId = (int)$row['enrollment_id'];
            $schoolId = (int)$row['school_id'];
            $studentIdCode = trim($row['student_id'] ?? '');
            $lrn = trim($row['lrn'] ?? '');

            // Determine login identifier & system email for learner
            // Matches AuthController.php regex:
            // 8-digit Student ID -> learner_{YYYYNNNN}@spedlms.local
            // 12-digit LRN        -> learner_{LRN}@spedlms.local
            $loginUsername = !empty($lrn) && strlen($lrn) === 12 ? $lrn : $studentIdCode;
            $loginEmail = 'learner_' . $loginUsername . '@spedlms.local';
            $defaultPassword = 'Learner@' . date('Y');

            // Check if user already exists
            $stmtUserCheck = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmtUserCheck->execute(['email' => $loginEmail]);
            $existingLearnerId = $stmtUserCheck->fetchColumn();

            $learnerUserId = null;
            if ($existingLearnerId) {
                $learnerUserId = (int)$existingLearnerId;
            } else {
                $passHash = password_hash($defaultPassword, PASSWORD_BCRYPT);
                $stmtIns = $this->db->prepare("
                    INSERT INTO users (
                        name, email, password_hash, role, status, email_verified,
                        auth_provider, school_id, created_at, updated_at
                    ) VALUES (
                        :name, :email, :pass, 'learner', 'active', 1,
                        'local', :school_id, NOW(), NOW()
                    )
                ");
                $stmtIns->execute([
                    'name' => $row['student_name'],
                    'email' => $loginEmail,
                    'pass' => $passHash,
                    'school_id' => $schoolId > 0 ? $schoolId : null
                ]);
                $learnerUserId = (int)$this->db->lastInsertId();
            }

            // Update enrollment_submissions
            if ($enrollmentId > 0) {
                $stmtEs = $this->db->prepare("
                    UPDATE enrollment_submissions 
                    SET parent_id = :pid, 
                        learner_user_id = :luid, 
                        learner_account_created = 1, 
                        lms_invite_status = 'accepted',
                        lms_invite_accepted_at = NOW(),
                        updated_at = NOW() 
                    WHERE id = :eid
                ");
                $stmtEs->execute([
                    'pid' => $parentId, 
                    'luid' => $learnerUserId, 
                    'eid' => $enrollmentId
                ]);
            }

            // Update student_records
            $stmtSr = $this->db->prepare("
                UPDATE student_records 
                SET lms_invite_status = 'accepted',
                    lms_accepted_at = NOW(),
                    learning_track = 'lms',
                    learner_user_id = :luid,
                    updated_at = NOW() 
                WHERE id = :id
            ");
            $stmtSr->execute([
                'luid' => $learnerUserId, 
                'id' => $studentRecordId
            ]);

            // Update parent's school_id if not already assigned
            if ($schoolId > 0) {
                $stmtUser = $this->db->prepare("UPDATE users SET school_id = :sid WHERE id = :uid AND (school_id IS NULL OR school_id = 0)");
                $stmtUser->execute(['sid' => $schoolId, 'uid' => $parentId]);
            }

            $this->db->commit();

            return [
                'student_name' => $row['student_name'],
                'student_id'   => $studentIdCode,
                'lrn'          => $lrn,
                'username'     => $loginUsername,
                'email'        => $loginEmail,
                'password'     => $defaultPassword,
                'learner_user_id' => $learnerUserId
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("linkParentAccountToLearner error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Bulk import enrolled learners from client CSV
     */
    public function bulkImportLearners(array $rows, int $schoolId, int $teacherId): array {
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $year = date('Y');
        $stmtSeq = $this->db->prepare("
            SELECT student_id FROM student_records 
            WHERE student_id LIKE :pfx ORDER BY student_id DESC LIMIT 1
        ");
        $stmtSeq->execute(['pfx' => $year . '%']);
        $lastRow = $stmtSeq->fetch(PDO::FETCH_ASSOC);
        $nextSeq = 1;
        if ($lastRow && preg_match('/^' . preg_quote($year, '/') . '(\d{4})$/', $lastRow['student_id'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }

        foreach ($rows as $idx => $r) {
            $line = $idx + 2; // header is row 1
            $name = trim($r['student_name'] ?? '');
            if (empty($name)) {
                $skipped++;
                continue;
            }

            $lrn = !empty($r['lrn']) && preg_match('/^\d{12}$/', trim($r['lrn'])) ? trim($r['lrn']) : null;
            $dob = !empty($r['birth_date']) ? date('Y-m-d', strtotime($r['birth_date'])) : null;
            if ($dob === '1970-01-01' || empty($dob)) {
                $dob = '2018-01-01'; // sensible default if unspecified
            }

            $sex = !empty($r['sex']) ? (stripos($r['sex'], 'f') === 0 ? 'Female' : 'Male') : 'Male';
            $grade = !empty($r['grade_level']) ? trim($r['grade_level']) : 'Kinder';
            $section = !empty($r['section_name']) ? trim($r['section_name']) : 'SPED Section A';
            $disability = !empty($r['disability_type']) ? trim($r['disability_type']) : 'General SPED';
            $parentName = !empty($r['parent_name']) ? trim($r['parent_name']) : 'Parent / Guardian';
            $parentContact = !empty($r['parent_contact']) ? trim($r['parent_contact']) : '';

            // Check if learner already exists by LRN or by Name + DOB
            $existing = null;
            if (!empty($lrn)) {
                $stmtCheck = $this->db->prepare("SELECT id, enrollment_id FROM student_records WHERE lrn = :lrn LIMIT 1");
                $stmtCheck->execute(['lrn' => $lrn]);
                $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            }
            if (!$existing) {
                $stmtCheck = $this->db->prepare("SELECT id, enrollment_id FROM student_records WHERE student_name = :sname AND date_of_birth = :dob LIMIT 1");
                $stmtCheck->execute(['sname' => $name, 'dob' => $dob]);
                $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            }

            if ($existing) {
                // Update existing record
                $upStmt = $this->db->prepare("
                    UPDATE student_records 
                    SET lrn = COALESCE(:lrn, lrn),
                        section_name = :section,
                        disability_type = :disability,
                        lis_status = 'synced',
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $upStmt->execute([
                    'lrn' => $lrn,
                    'section' => $section,
                    'disability' => $disability,
                    'id' => $existing['id']
                ]);
                $updated++;
                continue;
            }

            $track = !empty($row['learning_track']) ? strtolower(trim($row['learning_track'])) : 'traditional';
            if (!in_array($track, ['traditional', 'lms', 'candidate'])) {
                $track = 'traditional';
            }

            // Create new record
            try {
                $this->db->beginTransaction();

                $studentIdCode = $year . str_pad((string)$nextSeq++, 4, '0', STR_PAD_LEFT);
                $claimToken = bin2hex(random_bytes(16));

                // Insert into enrollment_submissions
                $stmtEs = $this->db->prepare("
                    INSERT INTO enrollment_submissions (
                        target_school_id, assigned_teacher_id, status, is_draft, enrollment_type,
                        school_year, lrn, first_name, last_name, birth_date, sex,
                        grade_level_to_enroll, guardian_first_name, guardian_contact_number,
                        learning_track, submitted_at, verified_at, verified_by, created_at, updated_at
                    ) VALUES (
                        :school_id, :teacher_id, 'verified', 0, 'new',
                        :sy, :lrn, :fname, :lname, :dob, :sex,
                        :grade, :pname, :pcontact,
                        :track, NOW(), NOW(), :verified_by, NOW(), NOW()
                    )
                ");

                $parts = explode(' ', $name);
                $lname = count($parts) > 1 ? array_pop($parts) : $name;
                $fname = !empty($parts) ? implode(' ', $parts) : $name;

                $stmtEs->execute([
                    'school_id' => $schoolId,
                    'teacher_id' => $teacherId,
                    'sy' => date('Y') . '-' . (date('Y') + 1),
                    'lrn' => $lrn,
                    'fname' => $fname,
                    'lname' => $lname,
                    'dob' => $dob,
                    'sex' => $sex,
                    'grade' => $grade,
                    'pname' => $parentName,
                    'pcontact' => $parentContact,
                    'track' => $track,
                    'verified_by' => $teacherId
                ]);
                $enrollmentId = (int)$this->db->lastInsertId();

                // Insert into student_records
                $stmtSr = $this->db->prepare("
                    INSERT INTO student_records (
                        enrollment_id, school_id, assigned_teacher_id, student_id, lrn,
                        student_name, date_of_birth, disability_type, section_name,
                        learning_track, lms_invite_status, lis_status, claim_token,
                        verified_by, created_at, updated_at
                    ) VALUES (
                        :eid, :school_id, :teacher_id, :sid_code, :lrn,
                        :sname, :dob, :disability, :section,
                        :track, 'none', :lis_status, :claim_token,
                        :verified_by, NOW(), NOW()
                    )
                ");
                $stmtSr->execute([
                    'eid' => $enrollmentId,
                    'school_id' => $schoolId,
                    'teacher_id' => $teacherId,
                    'sid_code' => $studentIdCode,
                    'lrn' => $lrn,
                    'sname' => $name,
                    'dob' => $dob,
                    'disability' => $disability,
                    'section' => $section,
                    'track' => $track,
                    'lis_status' => (!empty($lrn) && strlen($lrn) === 12) ? 'synced' : 'pending',
                    'claim_token' => $claimToken,
                    'verified_by' => $teacherId
                ]);

                $this->db->commit();
                $imported++;
            } catch (Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $errors[] = "Row {$line} ({$name}): " . $e->getMessage();
                $skipped++;
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Get all learner LMS accounts belonging to a parent.
     * Ensures an active user account exists for each child.
     */
    public function getParentLearnerAccounts(int $parentId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sr.id as student_record_id,
                    COALESCE(sr.student_name, CONCAT(es.first_name, ' ', es.last_name)) as student_name,
                    sr.student_id,
                    COALESCE(sr.lrn, es.lrn) as lrn,
                    sr.section_name,
                    COALESCE(sr.learning_track, es.learning_track, 'traditional') as learning_track,
                    COALESCE(sr.disability_type, 'SPED') as disability_type,
                    COALESCE(sr.learner_user_id, es.learner_user_id) as learner_user_id,
                    sr.school_id,
                    es.id as enrollment_id,
                    es.status as enrollment_status,
                    es.grade_level_to_enroll,
                    u.id as user_id,
                    u.name as user_name,
                    u.email as user_email,
                    u.status as user_status
                FROM enrollment_submissions es
                LEFT JOIN student_records sr ON sr.enrollment_id = es.id
                LEFT JOIN users u ON u.id = COALESCE(sr.learner_user_id, es.learner_user_id)
                WHERE es.parent_id = :parent_id 
                  AND es.is_draft = 0
                  AND es.status != 'rejected'
                ORDER BY es.id DESC
            ");
            $stmt->execute(['parent_id' => $parentId]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $learners = [];
            foreach ($records as $row) {
                $studentRecordId = !empty($row['student_record_id']) ? (int)$row['student_record_id'] : null;
                $enrollmentId = (int)$row['enrollment_id'];
                $studentIdCode = trim($row['student_id'] ?? '');
                $lrn = trim($row['lrn'] ?? '');
                $studentName = trim($row['student_name'] ?? 'Learner');
                $learnerUserId = !empty($row['user_id']) ? (int)$row['user_id'] : null;
                $userEmail = trim($row['user_email'] ?? '');
                $userStatus = trim($row['user_status'] ?? 'active');

                // If user account is missing for this verified child, auto-create it now
                if (!$learnerUserId || empty($userEmail)) {
                    $defaultUsername = !empty($lrn) && strlen($lrn) === 12 ? $lrn : (!empty($studentIdCode) ? $studentIdCode : 'std' . $enrollmentId);
                    $defaultEmail = 'learner_' . $defaultUsername . '@spedlms.local';
                    $defaultPassword = 'Learner@' . date('Y');

                    // Check if already in users table
                    $stmtCheck = $this->db->prepare("SELECT id, email, status FROM users WHERE email = :email LIMIT 1");
                    $stmtCheck->execute(['email' => $defaultEmail]);
                    $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if ($existingUser) {
                        $learnerUserId = (int)$existingUser['id'];
                        $userEmail = $existingUser['email'];
                        $userStatus = $existingUser['status'];
                    } else {
                        $passHash = password_hash($defaultPassword, PASSWORD_BCRYPT);
                        $stmtIns = $this->db->prepare("
                            INSERT INTO users (
                                name, email, password_hash, role, status, email_verified,
                                auth_provider, school_id, created_at, updated_at
                            ) VALUES (
                                :name, :email, :pass, 'learner', 'active', 1,
                                'local', :school_id, NOW(), NOW()
                            )
                        ");
                        $stmtIns->execute([
                            'name' => $studentName,
                            'email' => $defaultEmail,
                            'pass' => $passHash,
                            'school_id' => !empty($row['school_id']) ? (int)$row['school_id'] : null
                        ]);
                        $learnerUserId = (int)$this->db->lastInsertId();
                        $userEmail = $defaultEmail;
                        $userStatus = 'active';
                    }

                    // Update enrollment_submissions and student_records
                    $this->db->prepare("UPDATE enrollment_submissions SET learner_user_id = :uid, learner_account_created = 1 WHERE id = :eid")
                        ->execute(['uid' => $learnerUserId, 'eid' => $enrollmentId]);
                    if ($studentRecordId) {
                        $this->db->prepare("UPDATE student_records SET learner_user_id = :uid WHERE id = :srid")
                            ->execute(['uid' => $learnerUserId, 'srid' => $studentRecordId]);
                    }
                }

                // Determine clean display username
                $isCustomUsername = false;
                $displayUsername = $userEmail;
                if (preg_match('/^learner_([a-zA-Z0-9_-]+)@spedlms\.local$/', $userEmail, $m)) {
                    $displayUsername = $m[1];
                    $isCustomUsername = false;
                } else {
                    $isCustomUsername = true;
                }

                $learners[] = [
                    'student_record_id'   => $studentRecordId,
                    'enrollment_id'       => $enrollmentId,
                    'student_name'        => $studentName,
                    'student_id'          => $studentIdCode,
                    'lrn'                 => $lrn,
                    'grade_level'         => $row['grade_level_to_enroll'] ?? '',
                    'section_name'        => $row['section_name'] ?? '',
                    'learning_track'      => $row['learning_track'] ?? 'traditional',
                    'learner_user_id'     => $learnerUserId,
                    'display_username'    => $displayUsername,
                    'system_email'        => $userEmail,
                    'is_custom_username'  => $isCustomUsername,
                    'user_status'         => $userStatus
                ];
            }

            return $learners;
        } catch (Throwable $e) {
            error_log("getParentLearnerAccounts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update child's LMS credentials (custom username and/or password) by the parent.
     */
    public function updateChildCredentials(int $parentId, int $enrollmentOrStudentRecordId, ?string $newUsername, ?string $newPassword): array {
        try {
            // Find child and verify ownership by this parent
            $stmt = $this->db->prepare("
                SELECT 
                    es.id as enrollment_id,
                    es.parent_id,
                    COALESCE(sr.id, 0) as student_record_id,
                    COALESCE(sr.student_name, CONCAT(es.first_name, ' ', es.last_name)) as student_name,
                    COALESCE(sr.student_id, '') as student_id,
                    COALESCE(sr.lrn, es.lrn, '') as lrn,
                    COALESCE(sr.learner_user_id, es.learner_user_id) as learner_user_id,
                    es.target_school_id as school_id
                FROM enrollment_submissions es
                LEFT JOIN student_records sr ON sr.enrollment_id = es.id
                WHERE es.parent_id = :parent_id 
                  AND (es.id = :id1 OR sr.id = :id2)
                LIMIT 1
            ");
            $stmt->execute([
                'parent_id' => $parentId,
                'id1' => $enrollmentOrStudentRecordId,
                'id2' => $enrollmentOrStudentRecordId
            ]);
            $child = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$child) {
                return [
                    'success' => false,
                    'message' => 'Wala makit-i ang rekord sa bata o wala kay access niini.'
                ];
            }

            $learnerUserId = !empty($child['learner_user_id']) ? (int)$child['learner_user_id'] : 0;
            $studentName = $child['student_name'];

            // If learner user record doesn't exist yet, create it
            if (!$learnerUserId) {
                $defaultId = !empty($child['student_id']) ? $child['student_id'] : (!empty($child['lrn']) ? $child['lrn'] : 'std' . $child['enrollment_id']);
                $defaultEmail = 'learner_' . $defaultId . '@spedlms.local';
                $defaultPassHash = password_hash('Learner@' . date('Y'), PASSWORD_BCRYPT);

                $stmtIns = $this->db->prepare("
                    INSERT INTO users (
                        name, email, password_hash, role, status, email_verified,
                        auth_provider, school_id, created_at, updated_at
                    ) VALUES (
                        :name, :email, :pass, 'learner', 'active', 1,
                        'local', :school_id, NOW(), NOW()
                    )
                ");
                $stmtIns->execute([
                    'name' => $studentName,
                    'email' => $defaultEmail,
                    'pass' => $defaultPassHash,
                    'school_id' => !empty($child['school_id']) ? (int)$child['school_id'] : null
                ]);
                $learnerUserId = (int)$this->db->lastInsertId();

                $this->db->prepare("UPDATE enrollment_submissions SET learner_user_id = :uid, learner_account_created = 1 WHERE id = :eid")
                    ->execute(['uid' => $learnerUserId, 'eid' => $child['enrollment_id']]);
                if (!empty($child['student_record_id'])) {
                    $this->db->prepare("UPDATE student_records SET learner_user_id = :uid WHERE id = :srid")
                        ->execute(['uid' => $learnerUserId, 'srid' => $child['student_record_id']]);
                }
            }

            $cleanUsername = trim($newUsername ?? '');
            $cleanPassword = trim($newPassword ?? '');

            if (empty($cleanUsername) && empty($cleanPassword)) {
                return [
                    'success' => false,
                    'message' => 'Walay kausaban nga gibuhat. Palihug pagsulod og bag-ong username o password.'
                ];
            }

            // Username validation
            if (!empty($cleanUsername)) {
                if (strlen($cleanUsername) < 3 || strlen($cleanUsername) > 50) {
                    return [
                        'success' => false,
                        'message' => 'Ang username kinahanglan adunay 3 hangtod 50 ka mga karakter.'
                    ];
                }

                // Check uniqueness in users table
                $stmtDup = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :uid LIMIT 1");
                $stmtDup->execute(['email' => $cleanUsername, 'uid' => $learnerUserId]);
                if ($stmtDup->fetchColumn()) {
                    return [
                        'success' => false,
                        'message' => 'Ang username o email nga "' . htmlspecialchars($cleanUsername) . '" gigamit na sa laing account. Palihug pagpili og lain.'
                    ];
                }
            }

            // Password validation
            if (!empty($cleanPassword)) {
                if (strlen($cleanPassword) < 6) {
                    return [
                        'success' => false,
                        'message' => 'Ang password kinahanglan labing menus 6 ka mga karakter.'
                    ];
                }
            }

            // Execute update
            $fields = [];
            $params = ['uid' => $learnerUserId];

            if (!empty($cleanUsername)) {
                $fields[] = 'email = :email';
                $params['email'] = $cleanUsername;
            }

            if (!empty($cleanPassword)) {
                $fields[] = 'password_hash = :password_hash';
                $params['password_hash'] = password_hash($cleanPassword, PASSWORD_BCRYPT);
            }

            $fields[] = 'updated_at = NOW()';
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :uid";
            $this->db->prepare($sql)->execute($params);

            return [
                'success' => true,
                'message' => 'Malampusong nai-update ang login credentials ni ' . $studentName . '! Mahimo na kini gamiton sa pag-log in.'
            ];
        } catch (Throwable $e) {
            error_log("updateChildCredentials error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Adunay wala damhang sayop: ' . $e->getMessage()
            ];
        }
    }
}

