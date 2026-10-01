<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-14
// Part of: SPED LMS — IEP Model (domains, core, header overrides, repository)

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/StudentModel.php';

class IEPModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensurePartOneSaveSchema();
    }

    /**
     * v46 migration may record db_version without adding step_domain (e.g. MySQL without
     * ADD COLUMN IF NOT EXISTS). Ensure column exists before INSERT/UPDATE that reference it.
     */
    private function ensureStepDomainColumnExists(): void {
        static $ok = false;
        if ($ok) {
            return;
        }
        try {
            $q = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_steps'
                  AND COLUMN_NAME = 'step_domain'
            ");
            if ((int) $q->fetchColumn() > 0) {
                $ok = true;
                return;
            }
            $this->db->exec('ALTER TABLE iep_steps ADD COLUMN step_domain VARCHAR(191) NULL AFTER step_number');
            $ok = true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate column name') !== false) {
                $ok = true;
                return;
            }
            error_log('IEPModel::ensureStepDomainColumnExists: ' . $msg);
        }
    }

    /**
     * v45 migration uses ADD COLUMN IF NOT EXISTS (not supported on older MySQL).
     * Ensures header snapshot columns exist so IEP save-part1 does not fail.
     */
    private function ensureIepRecordsHeaderColumns(): void {
        static $ok = false;
        if ($ok) {
            return;
        }
        $cols = [
            'header_learner_name'   => 'VARCHAR(255) NULL',
            'header_learner_age'    => 'VARCHAR(50) NULL',
            'header_student_id'     => 'VARCHAR(20) NULL',
            'header_lrn'            => 'VARCHAR(32) NULL',
            'header_section'        => 'VARCHAR(120) NULL',
            'header_teacher_name'   => 'VARCHAR(255) NULL',
            'header_school_name'    => 'VARCHAR(255) NULL',
            'header_grade_level'    => 'VARCHAR(100) NULL',
        ];
        try {
            foreach ($cols as $name => $ddl) {
                $chk = $this->db->prepare('
                    SELECT COUNT(*) FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = \'iep_records\'
                      AND COLUMN_NAME = ?
                ');
                $chk->execute([$name]);
                if ((int) $chk->fetchColumn() > 0) {
                    continue;
                }
                $this->db->exec('ALTER TABLE iep_records ADD COLUMN `' . $name . '` ' . $ddl);
            }
            $ok = true;
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                $ok = true;
                return;
            }
            error_log('IEPModel::ensureIepRecordsHeaderColumns: ' . $e->getMessage());
        }
    }

    /**
     * Ensure Part I save dependencies exist (domains/core tables + header columns).
     */
    public function ensurePartOneSaveSchema(): void {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS iep_domains (
                id INT AUTO_INCREMENT PRIMARY KEY,
                iep_id INT NOT NULL,
                domain_name VARCHAR(200) NOT NULL,
                display_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                INDEX idx_iep_id (iep_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->db->exec("CREATE TABLE IF NOT EXISTS iep_core (
                id INT AUTO_INCREMENT PRIMARY KEY,
                iep_id INT NOT NULL,
                developmental_domain TEXT NULL,
                priority_needs TEXT NULL,
                terminal_objectives TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                UNIQUE KEY unique_iep_core (iep_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log('IEPModel::ensurePartOneSaveSchema tables: ' . $e->getMessage());
        }
        $this->ensureIepRecordsHeaderColumns();
    }

    // ============================================================
    // IEP RECORDS
    // ============================================================

    /**
     * Create a new IEP draft
     */
    public function create($studentId, $pdspId, $draftedBy, $schoolYear) {
        $stmt = $this->db->prepare("
            INSERT INTO iep_records (student_id, pdsp_id, drafted_by, school_year, status, created_at, updated_at)
            VALUES (:student_id, :pdsp_id, :drafted_by, :school_year, 'draft', NOW(), NOW())
        ");
        $stmt->execute([
            'student_id'  => $studentId,
            'pdsp_id'     => $pdspId,
            'drafted_by'  => $draftedBy,
            'school_year' => $schoolYear,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Find IEP by ID — joins student, pdsp, drafter
     */
    public function findById($iepId) {
        $stmt = $this->db->prepare("
            SELECT ir.*,
                   sr.student_name, sr.lrn, sr.enrollment_id,
                   u.name AS drafted_by_name,
                   pr.status AS pdsp_status, pr.meeting_id,
                   pr.signed_document_path AS pdsp_signed_document_path
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            JOIN users u            ON ir.drafted_by  = u.id
            JOIN pdsp_records pr    ON ir.pdsp_id     = pr.id
            WHERE ir.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $iepId]);
        return $stmt->fetch();
    }

    /**
     * Get all IEPs for a student (repository — all versions)
     */
    public function getByStudent($studentId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN users u ON ir.drafted_by = u.id
            WHERE ir.student_id = :student_id
            ORDER BY ir.created_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get latest IEP for a student (most recent row)
     */
    public function getLatestByStudent($studentId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN users u ON ir.drafted_by = u.id
            WHERE ir.student_id = :student_id
            ORDER BY ir.created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * Get all IEPs drafted by a specific teacher
     */
    public function getByTeacher($userId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            JOIN users u ON ir.drafted_by = u.id
            WHERE ir.drafted_by = :user_id
            ORDER BY ir.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Permanently remove a draft IEP (and cascaded child rows). Returns false if not a draft or not owned.
     */
    public function deleteDraftIep(int $iepId, int $draftedByUserId, bool $allowAdmin = false): bool {
        $iep = $this->findById($iepId);
        if (!$iep || ($iep['status'] ?? '') !== 'draft') {
            return false;
        }
        if (!$allowAdmin && (int) ($iep['drafted_by'] ?? 0) !== $draftedByUserId) {
            return false;
        }
        $stmt = $this->db->prepare('DELETE FROM iep_records WHERE id = :id AND status = :st');
        return $stmt->execute(['id' => $iepId, 'st' => 'draft']) && $stmt->rowCount() > 0;
    }

    /**
     * Get IEPs visible to guidance / principal / parent (completed or in signing)
     */
    public function getSignedForRole($role, $userId) {
        if ($role === 'parent') {
            $stmt = $this->db->prepare("
                SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
                FROM iep_records ir
                JOIN student_records sr ON ir.student_id = sr.id
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                JOIN users u ON ir.drafted_by = u.id
                WHERE ir.status IN ('signed','signing','locked')
                AND es.parent_id = :user_id
                ORDER BY ir.created_at DESC
            ");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
                FROM iep_records ir
                JOIN student_records sr ON ir.student_id = sr.id
                JOIN users u ON ir.drafted_by = u.id
                WHERE ir.status IN ('signed','signing','locked')
                ORDER BY ir.created_at DESC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Update IEP header fields + core data
     */
    public function update($iepId, $data) {
        $allowed = [
            'school_year', 'status', 'signed_document_path', 're_evaluation_date', 'signing_method',
            'header_learner_name', 'header_learner_age', 'header_student_id', 'header_lrn', 'header_section',
            'header_teacher_name', 'header_school_name', 'header_grade_level',
        ];
        $sets = [];
        $params = ['id' => $iepId];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($sets)) return false;
        $sets[] = "updated_at = NOW()";
        $stmt = $this->db->prepare("UPDATE iep_records SET " . implode(', ', $sets) . " WHERE id = :id");
        return $stmt->execute($params);
    }

    /**
     * Mark IEP as signed (living document — no lock timestamp).
     *
     * @param string $signingMethod print_upload (meeting attestation / scanned doc path) or digital
     */
    public function markSigned($iepId, string $signingMethod = 'digital') {
        try {
            $stmt = $this->db->prepare("
                UPDATE iep_records
                SET status = 'signed',
                    signing_method = :sm,
                    updated_at = NOW()
                WHERE id = :id
            ");
            return $stmt->execute(['id' => $iepId, 'sm' => $signingMethod]);
        } catch (\Throwable $e) {
            $stmt = $this->db->prepare("
                UPDATE iep_records SET status = 'signed', updated_at = NOW() WHERE id = :id
            ");
            return $stmt->execute(['id' => $iepId]);
        }
    }

    /**
     * Lesson plans linked from Process 5 IEP steps (junction iep_step_lesson_plans).
     *
     * @return int[]
     */
    public function getLessonPlanIdsLinkedToIep(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT j.lesson_plan_id
            FROM iep_step_lesson_plans j
            INNER JOIN iep_steps s ON s.id = j.iep_step_id
            WHERE s.iep_id = :iep_id
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    public function insertIepEditLog(int $iepId, int $userId, string $fieldName, ?string $oldValue, ?string $newValue): void {
        $stmt = $this->db->prepare("
            INSERT INTO iep_edit_logs (iep_id, edited_by, field_name, old_value, new_value)
            VALUES (:iep_id, :uid, :fn, :ov, :nv)
        ");
        $stmt->execute([
            'iep_id' => $iepId,
            'uid'    => $userId,
            'fn'     => $fieldName,
            'ov'     => $oldValue,
            'nv'     => $newValue,
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getIepEditLogs(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT l.field_name, l.old_value, l.new_value, l.edited_at, u.name AS edited_by_name
            FROM iep_edit_logs l
            JOIN users u ON u.id = l.edited_by
            WHERE l.iep_id = :iep_id
            ORDER BY l.edited_at DESC, l.id DESC
            LIMIT 500
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get signed PDSP for a student (to link when creating IEP)
     */
    public function getSignedPDSP($studentId) {
        $stmt = $this->db->prepare("
            SELECT pr.*
            FROM pdsp_records pr
            JOIN iep_meetings im ON pr.meeting_id = im.id
            WHERE im.student_id = :student_id
            AND pr.status = 'signed'
            ORDER BY pr.created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    // ============================================================
    // SIGNATORIES (SIMPLIFIED)
    // ============================================================

    private static ?bool $iepSignatoriesHasSendStatus = null;

    private function iepSignatoriesHasSendStatusColumn(): bool {
        if (self::$iepSignatoriesHasSendStatus !== null) {
            return self::$iepSignatoriesHasSendStatus;
        }
        try {
            $q = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_signatories'
                  AND COLUMN_NAME = 'send_status'
            ");
            self::$iepSignatoriesHasSendStatus = ((int) $q->fetchColumn() > 0);
        } catch (\Throwable $e) {
            self::$iepSignatoriesHasSendStatus = false;
        }
        return self::$iepSignatoriesHasSendStatus;
    }

    /**
     * v44 migration may be missing on some DBs. Digital signing UI requires send_status + sent_at.
     */
    public function ensureIepSignatoriesDigitalColumns(): void {
        try {
            $q = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_signatories'
                  AND COLUMN_NAME = 'send_status'
            ");
            if ((int) $q->fetchColumn() === 0) {
                $this->db->exec("
                    ALTER TABLE iep_signatories
                    ADD COLUMN send_status ENUM('not_sent','pending','signed') NOT NULL DEFAULT 'not_sent' AFTER signatory_name
                ");
            }
            $q2 = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_signatories'
                  AND COLUMN_NAME = 'signature_request_sent_at'
            ");
            if ((int) $q2->fetchColumn() === 0) {
                $this->db->exec("
                    ALTER TABLE iep_signatories
                    ADD COLUMN signature_request_sent_at TIMESTAMP NULL AFTER send_status
                ");
            }
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'Duplicate column') === false) {
                error_log('IEPModel::ensureIepSignatoriesDigitalColumns: ' . $e->getMessage());
            }
        }
        self::$iepSignatoriesHasSendStatus = null;
    }

    /**
     * Replace all signatory rows (meeting-record or digital-collect flow).
     *
     * Each row: role, name, send_status ('signed'|'pending'|'not_sent'),
     * optional signature_image_path, optional signed_at (Y-m-d H:i:s or null).
     */
    public function replaceSignatoryRows(int $iepId, array $rows): void {
        $this->db->prepare('DELETE FROM iep_signatories WHERE iep_id = ?')->execute([$iepId]);
        if (empty($rows)) {
            return;
        }
        $extended = $this->iepSignatoriesHasSendStatusColumn();
        if ($extended) {
            $stmt = $this->db->prepare('
                INSERT INTO iep_signatories
                    (iep_id, signatory_role, signatory_name, send_status, signature_image_path, signed_at, signature_request_sent_at)
                VALUES
                    (:iep_id, :role, :name, :send_status, :sig_path, :signed_at, :sent_at)
            ');
        } else {
            $stmt = $this->db->prepare('
                INSERT INTO iep_signatories
                    (iep_id, signatory_role, signatory_name, signed_at, signature_image_path)
                VALUES
                    (:iep_id, :role, :name, :signed_at, :sig_path)
            ');
        }
        foreach ($rows as $r) {
            $signedAt = $r['signed_at'] ?? null;
            $sigPath  = $r['signature_image_path'] ?? null;
            if ($extended) {
                $stmt->execute([
                    'iep_id'      => $iepId,
                    'role'        => $r['role'],
                    'name'        => $r['name'],
                    'send_status' => $r['send_status'] ?? 'signed',
                    'sig_path'    => $sigPath,
                    'signed_at'   => $signedAt,
                    'sent_at'     => $r['signature_request_sent_at'] ?? null,
                ]);
            } else {
                $stmt->execute([
                    'iep_id'    => $iepId,
                    'role'      => $r['role'],
                    'name'      => $r['name'],
                    'signed_at' => $signedAt,
                    'sig_path'  => $sigPath,
                ]);
            }
        }
    }

    /**
     * Save signatories (replaces existing) — meeting record: all attested now.
     */
    public function saveSignatories($iepId, array $signatories) {
        $rows = [];
        $now = date('Y-m-d H:i:s');
        foreach ($signatories as $sig) {
            $rows[] = [
                'role'                 => $sig['role'],
                'name'                 => $sig['name'],
                'send_status'          => 'signed',
                'signature_image_path' => null,
                'signed_at'            => $now,
            ];
        }
        $this->replaceSignatoryRows((int) $iepId, $rows);
        return true;
    }

    public function getSignatoryById(int $signatoryId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM iep_signatories WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $signatoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Persist canvas signature (relative path under public/).
     */
    public function saveSignatureImage(int $signatoryId, string $relativePath): bool {
        if ($this->iepSignatoriesHasSendStatusColumn()) {
            $stmt = $this->db->prepare("
                UPDATE iep_signatories
                SET signature_image_path = :path,
                    signed_at = COALESCE(signed_at, NOW()),
                    send_status = 'signed'
                WHERE id = :id
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE iep_signatories
                SET signature_image_path = :path,
                    signed_at = COALESCE(signed_at, NOW())
                WHERE id = :id
            ");
        }
        return $stmt->execute(['path' => $relativePath, 'id' => $signatoryId]);
    }

    /**
     * True when every signatory row has a captured path (image or f2f marker).
     */
    public function allSignatoriesSignatureComplete(int $iepId): bool {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM iep_signatories
            WHERE iep_id = :iep_id AND (signature_image_path IS NULL OR TRIM(signature_image_path) = "")
        ');
        $stmt->execute(['iep_id' => $iepId]);
        return (int) $stmt->fetchColumn() === 0;
    }

    public function markSignatoryRequestSent(int $signatoryId): void {
        if (!$this->iepSignatoriesHasSendStatusColumn()) {
            return;
        }
        $stmt = $this->db->prepare('
            UPDATE iep_signatories SET signature_request_sent_at = NOW() WHERE id = :id
        ');
        $stmt->execute(['id' => $signatoryId]);
    }

    /**
     * Get signatories for an IEP
     */
    public function getSignatories($iepId) {
        $stmt = $this->db->prepare("
            SELECT * FROM iep_signatories WHERE iep_id = :iep_id ORDER BY id ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // STUDENT DATA & ELIGIBILITY
    // ============================================================

    /**
     * Get students eligible for new IEP (have signed PDSP or enrolled/invited without active draft)
     */
    public function getEligibleStudents($teacherId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT sr.id, sr.student_name, sr.lrn,
                   COALESCE(pr.created_at, sr.created_at) as pdsp_signed_at
            FROM student_records sr
            LEFT JOIN iep_meetings im ON sr.id = im.student_id
            LEFT JOIN pdsp_records pr ON im.id = pr.meeting_id AND pr.status = 'signed'
            WHERE sr.id NOT IN (
                SELECT student_id FROM iep_records 
                WHERE status IN ('draft') 
                AND school_year = CONCAT(YEAR(NOW()), '-', YEAR(NOW()) + 1)
            )
            ORDER BY sr.student_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ensure baseline meeting and PDSP record exists for an enrolled/invited learner
     */
    public function ensureBaselinePdspForStudent(int $studentId, int $userId): array {
        // 1. Check if meeting already exists
        $stmtM = $this->db->prepare("SELECT id FROM iep_meetings WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
        $stmtM->execute(['sid' => $studentId]);
        $meetingId = $stmtM->fetchColumn();

        if (!$meetingId) {
            $assessmentId = null;
            try {
                $stmtA = $this->db->prepare("SELECT id FROM assessment_records WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
                $stmtA->execute(['sid' => $studentId]);
                $assessmentId = $stmtA->fetchColumn() ?: null;

                if (!$assessmentId) {
                    $insA = $this->db->prepare("
                        INSERT INTO assessment_records (
                            student_id, assessed_by, conducted_by, status, version,
                            section_a_data, services_checked, screening_types,
                            created_at, updated_at
                        ) VALUES (
                            :sid, :uid, :uid, 'finalized', 1,
                            '{}', '[]', '[]',
                            NOW(), NOW()
                        )
                    ");
                    $insA->execute(['sid' => $studentId, 'uid' => $userId]);
                    $assessmentId = (int)$this->db->lastInsertId();
                }
            } catch (\Throwable $e) {
                error_log("Baseline assessment notice: " . $e->getMessage());
            }

            // Absolute fallback to guarantee foreign key iep_meetings_ibfk_2 is satisfied
            if (!$assessmentId) {
                $assessmentId = (int)$this->db->query("SELECT id FROM assessment_records ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;
            }

            $stmtInsM = $this->db->prepare("
                INSERT INTO iep_meetings (
                    student_id, assessment_id, scheduled_by, meeting_date,
                    status, agenda, created_at, updated_at
                ) VALUES (
                    :sid, :aid, :uid, NOW(),
                    'completed', 'Baseline IEP & assessment record initialized for enrolled/invited learner', NOW(), NOW()
                )
            ");
            $stmtInsM->execute([
                'sid' => $studentId,
                'aid' => $assessmentId,
                'uid' => $userId
            ]);
            $meetingId = (int)$this->db->lastInsertId();
        }

        // 2. Check if PDSP record exists
        $stmtP = $this->db->prepare("SELECT id, status FROM pdsp_records WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
        $stmtP->execute(['sid' => $studentId]);
        $pdsp = $stmtP->fetch(PDO::FETCH_ASSOC);

        if (!$pdsp) {
            $stmtInsP = $this->db->prepare("
                INSERT INTO pdsp_records (meeting_id, student_id, filled_by, status, created_at, updated_at)
                VALUES (:mid, :sid, :uid, 'signed', NOW(), NOW())
            ");
            $stmtInsP->execute([
                'mid' => $meetingId,
                'sid' => $studentId,
                'uid' => $userId
            ]);
            $pdspId = (int)$this->db->lastInsertId();
            $pdsp = ['id' => $pdspId, 'status' => 'signed'];
        } else {
            if ($pdsp['status'] !== 'signed') {
                $this->db->prepare("UPDATE pdsp_records SET status = 'signed' WHERE id = :id")->execute(['id' => $pdsp['id']]);
                $pdsp['status'] = 'signed';
            }
        }

        // 3. Ensure baseline domains exist in pdsp_domains using DepEd Baseline Template
        $stmtDom = $this->db->prepare("SELECT COUNT(*) FROM pdsp_domains WHERE pdsp_id = :pid");
        $stmtDom->execute(['pid' => $pdsp['id']]);
        if ((int)$stmtDom->fetchColumn() === 0) {
            $student = (new StudentModel())->findById($studentId);
            $tplKey = self::resolveTemplateKeyFromDisability($student['disability_type'] ?? '');
            $templates = self::getDepEdBaselineTemplates();
            $tpl = $templates[$tplKey] ?? $templates['general_sped'];

            $stmtInsDom = $this->db->prepare("
                INSERT INTO pdsp_domains (pdsp_id, domain_name, skills_description, educational_recommendation, mastered, q1_level, created_at)
                VALUES (:pid, :name, :desc, :rec, 0, 'Developing', NOW())
            ");
            foreach ($tpl['domains'] as $d) {
                $stmtInsDom->execute([
                    'pid'  => $pdsp['id'],
                    'name' => $d['name'],
                    'desc' => $d['desc'],
                    'rec'  => $d['recommendation'] ?? null,
                ]);
            }
        }

        return $pdsp;
    }

    /**
     * DepEd SPED Baseline Templates for Quick-Fill & Mid-Year Record Ingestion
     */
    public static function getDepEdBaselineTemplates(): array {
        return [
            'hearing_impairment' => [
                'name' => 'Deaf / Hard of Hearing (FSL & Visual Literacy Focus)',
                'category' => 'hearing_impairment',
                'domains' => [
                    [
                        'name' => 'Communication & Language',
                        'desc' => 'Demonstrates receptive and expressive mastery of 50+ basic Filipino Sign Language (FSL) survival signs, fingerspelling alphabet, and conversational visual turn-taking.',
                        'recommendation' => 'Provide structured FSL video modules, real-time visual prompts, and ensure well-lit direct line-of-sight during instruction.',
                    ],
                    [
                        'name' => 'Cognitive / Academics',
                        'desc' => 'Matches printed vocabulary words with corresponding FSL sign graphics and real-world objects; performs functional single-digit addition with visual counters.',
                        'recommendation' => 'Utilize visual flashcards, illustrated graphic organizers, and bilingual-bicultural DepEd learning sheets.',
                    ],
                    [
                        'name' => 'Socio-Emotional & Behavioral',
                        'desc' => 'Actively engages in collaborative peer activities, demonstrates positive Deaf identity, and uses appropriate visual attention-getting techniques (gentle wave/tap).',
                        'recommendation' => 'Incorporate social stories on deaf culture, inclusive cooperative games, and positive reinforcement.',
                    ],
                    [
                        'name' => 'Motor & Physical Development',
                        'desc' => 'Exhibits fine-motor finger dexterity and hand coordination necessary for precise fingerspelling handshapes and spatial signing clarity.',
                        'recommendation' => 'Conduct finger-aerobics, clay sculpting, and bilateral hand dexterity drills.',
                    ],
                    [
                        'name' => 'Daily Living & Adaptive Skills',
                        'desc' => 'Recognizes visual safety cues (flashing alarms, exit signs, visual schedules) and independently organizes personal learning tablet and materials.',
                        'recommendation' => 'Maintain visual color-coded labels in the classroom and emergency visual cue cards.',
                    ],
                ],
                'steps' => [
                    'Master everyday FSL conversational greetings and classroom survival vocabulary.',
                    'Identify and associate 20 core sight words with FSL sign illustrations.',
                    'Demonstrate visual numeracy: count and match numbers 1–20 using sign language support.',
                ],
                'starter_plans' => [
                    [
                        'title' => 'FSL Basics: Everyday Greetings and Classroom Signs',
                        'domain' => 'communication_language',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Visual Sight Words & Word-Sign Association',
                        'domain' => 'perceptuo_cognitive',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Functional Math: Counting and Money with Sign Support',
                        'domain' => 'perceptuo_cognitive',
                        'assignment_type' => 'all',
                    ],
                ],
            ],
            'autism_spectrum' => [
                'name' => 'Autism Spectrum Disorder (Structured Routines & Sensory Focus)',
                'category' => 'autism_spectrum',
                'domains' => [
                    [
                        'name' => 'Communication & Language',
                        'desc' => 'Uses Picture Exchange Communication (PECS) or functional 2-word phrase prompts to express basic requests ("I want ___") and respond to greetings.',
                        'recommendation' => 'Employ high-contrast PECS cards, predictable verbal cues, and wait-time for processing.',
                    ],
                    [
                        'name' => 'Cognitive / Academics',
                        'desc' => 'Independently completes 3-step structured task-box activities; sorts objects by color, shape, and size with 80% accuracy.',
                        'recommendation' => 'Break instructions into discrete trials with visual step-by-step strip schedules.',
                    ],
                    [
                        'name' => 'Socio-Emotional & Behavioral',
                        'desc' => 'Tolerates activity transitions using visual countdown timers; accesses calm-down sensory corner independently when experiencing sensory overload.',
                        'recommendation' => 'Provide predictable schedules, sensory breaks, and avoid sudden auditory overstimulation.',
                    ],
                    [
                        'name' => 'Motor & Physical Development',
                        'desc' => 'Engages in sensory-motor integration activities; demonstrates functional pincer grasp during tracing and sorting tasks.',
                        'recommendation' => 'Utilize weighted blankets/vests as prescribed, textured sensory manipulatives, and gross-motor obstacle courses.',
                    ],
                    [
                        'name' => 'Daily Living & Adaptive Skills',
                        'desc' => 'Follows 4-step visual hygiene routine (handwashing, wiping table); packs and unpacks personal bag independently.',
                        'recommendation' => 'Affix visual sequence strips near handwashing stations and cubbies.',
                    ],
                ],
                'steps' => [
                    'Follow daily classroom transitions using individualized visual schedule strips.',
                    'Express requests and choices using functional PECS cards or 2-word phrases.',
                    'Complete table-top sorting and matching tasks for 15 consecutive minutes.',
                ],
                'starter_plans' => [
                    [
                        'title' => 'Visual Schedule Navigation and Daily Routine Mastery',
                        'domain' => 'daily_living_skills',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Functional Object Sorting and Categorization',
                        'domain' => 'perceptuo_cognitive',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Self-Regulation and Calm-Down Strategies',
                        'domain' => 'socio_emotional',
                        'assignment_type' => 'all',
                    ],
                ],
            ],
            'intellectual_disability' => [
                'name' => 'Intellectual Disability / Developmental Delay (Functional Life Skills Focus)',
                'category' => 'intellectual_disability',
                'domains' => [
                    [
                        'name' => 'Communication & Language',
                        'desc' => 'States full name, age, and basic personal info; follows 2-step verbal directions in classroom activities.',
                        'recommendation' => 'Use repetition, simplified language, visual modeling, and immediate positive reinforcement.',
                    ],
                    [
                        'name' => 'Cognitive / Academics',
                        'desc' => 'Recognizes community helpers, common survival signs (STOP, EXIT), and identifies basic numbers 1–10.',
                        'recommendation' => 'Focus on concrete, functional life academics rather than abstract concepts.',
                    ],
                    [
                        'name' => 'Socio-Emotional & Behavioral',
                        'desc' => 'Interacts cooperatively during circle time; shares learning toys and practices polite social greetings.',
                        'recommendation' => 'Implement peer buddy system and structured turn-taking activities.',
                    ],
                    [
                        'name' => 'Motor & Physical Development',
                        'desc' => 'Coordinates hand-eye movement for safety scissor cutting and threading large beads; maintains body balance.',
                        'recommendation' => 'Incorporate adaptive scissors, playdough modeling, and balance beam games.',
                    ],
                    [
                        'name' => 'Daily Living & Adaptive Skills',
                        'desc' => 'Practices independent self-care (buttoning, feeding, hand sanitizing) and recognizes personal belongings.',
                        'recommendation' => 'Step-by-step task analysis with prompt fading technique.',
                    ],
                ],
                'steps' => [
                    'State full personal identity details and identify school authority figures.',
                    'Recognize and respond to critical community safety signs (STOP, EXIT, CR).',
                    'Demonstrate independent personal grooming and classroom bag organization.',
                ],
                'starter_plans' => [
                    [
                        'title' => 'All About Me: Personal Identity and Emergency Information',
                        'domain' => 'communication_language',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Functional Community Signs and Safety Awareness',
                        'domain' => 'perceptuo_cognitive',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Self-Care Routine and Hygiene Independence',
                        'domain' => 'daily_living_skills',
                        'assignment_type' => 'all',
                    ],
                ],
            ],
            'learning_disability' => [
                'name' => 'Learning Disability / ADHD (Academic Strategies & Focus)',
                'category' => 'learning_disability',
                'domains' => [
                    [
                        'name' => 'Communication & Language',
                        'desc' => 'Retells grade-level short stories in proper sequence; explains comprehension answers clearly in oral discussion.',
                        'recommendation' => 'Provide graphic organizers, audio-supported reading, and sentence starter frames.',
                    ],
                    [
                        'name' => 'Cognitive / Academics',
                        'desc' => 'Applies phonetic decoding strategies to unfamiliar words; solves two-digit addition and subtraction using visual counters.',
                        'recommendation' => 'Use multisensory (VAKT) reading methods and chunked math problem sets.',
                    ],
                    [
                        'name' => 'Socio-Emotional & Behavioral',
                        'desc' => 'Sustains attention on individual seatwork for 15-20 minutes; utilizes personal self-monitoring checklist.',
                        'recommendation' => 'Incorporate active movement breaks, timer-based focus blocks, and desk checklists.',
                    ],
                    [
                        'name' => 'Motor & Physical Development',
                        'desc' => 'Maintains ergonomic posture and comfortable pencil grip during extended handwriting tasks.',
                        'recommendation' => 'Provide pencil grips, slant boards, and handwriting warm-up stretches.',
                    ],
                    [
                        'name' => 'Daily Living & Adaptive Skills',
                        'desc' => 'Tracks assignment deadlines using an LMS planner or notebook; keeps desk clutter-free.',
                        'recommendation' => 'Reinforce organizational routines at the start and end of every class period.',
                    ],
                ],
                'steps' => [
                    'Apply phonics decoding strategies to read grade-level decodable passages.',
                    'Utilize visual organizers and counters to solve functional arithmetic problems.',
                    'Maintain on-task attention for 20 minutes using an individualized task checklist.',
                ],
                'starter_plans' => [
                    [
                        'title' => 'Phonological Decoding and Sight Word Fluency',
                        'domain' => 'communication_language',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Visual Problem Solving in Arithmetic Operations',
                        'domain' => 'perceptuo_cognitive',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Self-Regulation and Task Management Strategies',
                        'domain' => 'socio_emotional',
                        'assignment_type' => 'all',
                    ],
                ],
            ],
            'speech_impairment' => [
                'name' => 'Speech & Language Impairment (Articulation & Expression Focus)',
                'category' => 'speech_impairment',
                'domains' => [
                    [
                        'name' => 'Communication & Language',
                        'desc' => 'Accurately produces target consonant sounds in words and short phrases; initiates conversation with peers with increased intelligibility.',
                        'recommendation' => 'Use tactile speech cues, mirror articulation modeling, and pacing boards.',
                    ],
                    [
                        'name' => 'Cognitive / Academics',
                        'desc' => 'Demonstrates sound-symbol correspondence for targeted phonemes; constructs complete 4-word grammatical sentences.',
                        'recommendation' => 'Color-coded syntactic word cards and visual vocabulary associations.',
                    ],
                    [
                        'name' => 'Socio-Emotional & Behavioral',
                        'desc' => 'Demonstrates confidence in oral participation without anxiety; uses repair strategies when misunderstood.',
                        'recommendation' => 'Praise communication attempts over perfect articulation; create low-pressure speaking circles.',
                    ],
                    [
                        'name' => 'Motor & Physical Development',
                        'desc' => 'Demonstrates controlled breath support and oral-motor coordination (lip rounding, tongue elevation) during speech games.',
                        'recommendation' => 'Incorporate straw-blowing, bubble drills, and fun tongue gym exercises.',
                    ],
                    [
                        'name' => 'Daily Living & Adaptive Skills',
                        'desc' => 'Confidently expresses personal needs, orders food, and reports emergencies clearly to school personnel.',
                        'recommendation' => 'Conduct functional role-play activities simulating everyday communication settings.',
                    ],
                ],
                'steps' => [
                    'Produce targeted phonemes with 80% accuracy in structured word drills.',
                    'Formulate complete 4-word descriptive sentences using picture prompts.',
                    'Initiate and maintain a 3-turn communicative exchange with a peer.',
                ],
                'starter_plans' => [
                    [
                        'title' => 'Target Phoneme Articulation and Word Drills',
                        'domain' => 'communication_language',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Sentence Building with Visual Picture Prompts',
                        'domain' => 'communication_language',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Functional Role-Play: Expressing Needs and Requests',
                        'domain' => 'daily_living_skills',
                        'assignment_type' => 'all',
                    ],
                ],
            ],
            'general_sped' => [
                'name' => 'General DepEd SPED Baseline (Comprehensive Inclusive Framework)',
                'category' => 'general_sped',
                'domains' => [
                    [
                        'name' => 'Cognitive / Academics',
                        'desc' => 'Demonstrates foundational functional literacy and numeracy skills aligned with individualized DepEd SPED curriculum pacing.',
                        'recommendation' => 'Provide differentiated learning materials, multi-sensory instruction, and extended time accommodations.',
                    ],
                    [
                        'name' => 'Communication & Language',
                        'desc' => 'Expresses thoughts and questions effectively using augmentative or verbal communication modes; understands instructional directions.',
                        'recommendation' => 'Use multimodal communication aids, visual demonstrations, and comprehension checks.',
                    ],
                    [
                        'name' => 'Socio-Emotional & Behavioral',
                        'desc' => 'Engages in respectful and collaborative classroom behavior; participates willingly in group activities.',
                        'recommendation' => 'Positive behavior support framework, peer modeling, and clear classroom expectations.',
                    ],
                    [
                        'name' => 'Motor & Physical Development',
                        'desc' => 'Participates in gross and fine motor coordination activities according to physical capabilities and health allowances.',
                        'recommendation' => 'Adapted physical education activities, assistive tools, and ergonomic seating.',
                    ],
                    [
                        'name' => 'Daily Living & Adaptive Skills',
                        'desc' => 'Maintains personal independence in daily routines, tool management, and classroom navigation.',
                        'recommendation' => 'Consistent daily routine structure and visual reminders.',
                    ],
                ],
                'steps' => [
                    'Demonstrate mastery of core foundational functional literacy skills.',
                    'Participate actively in collaborative inclusive classroom activities.',
                    'Maintain consistent daily routines and independent learning habits.',
                ],
                'starter_plans' => [
                    [
                        'title' => 'Foundational Literacy and Vocabulary Development',
                        'domain' => 'perceptuo_cognitive',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Functional Numeracy and Everyday Problem Solving',
                        'domain' => 'perceptuo_cognitive',
                        'assignment_type' => 'all',
                    ],
                    [
                        'title' => 'Cooperative Learning and Social Engagement',
                        'domain' => 'socio_emotional',
                        'assignment_type' => 'all',
                    ],
                ],
            ],
        ];
    }

    /**
     * Resolve template key from student's disability type
     */
    public static function resolveTemplateKeyFromDisability(?string $disability): string {
        $d = strtolower($disability ?? '');
        if (str_contains($d, 'deaf') || str_contains($d, 'hearing') || str_contains($d, 'fsl') || str_contains($d, 'pangdungog') || str_contains($d, 'bungol')) {
            return 'hearing_impairment';
        }
        if (str_contains($d, 'autism') || str_contains($d, 'asd') || str_contains($d, 'asperger')) {
            return 'autism_spectrum';
        }
        if (str_contains($d, 'intellectual') || str_contains($d, 'down') || str_contains($d, 'delay') || str_contains($d, 'mental') || str_contains($d, 'panghunahuna')) {
            return 'intellectual_disability';
        }
        if (str_contains($d, 'learning') || str_contains($d, 'adhd') || str_contains($d, 'dyslexia') || str_contains($d, 'attention')) {
            return 'learning_disability';
        }
        if (str_contains($d, 'speech') || str_contains($d, 'language') || str_contains($d, 'sulti') || str_contains($d, 'communication')) {
            return 'speech_impairment';
        }
        return 'general_sped';
    }

    /**
     * Fast-Track Provisioning of IEP & Implementation Workspace from Uploaded Document
     */
    public function provisionFastTrackIep(
        int $studentId,
        int $userId,
        string $schoolYear = '2026-2027',
        string $templateKey = '',
        string $uploadedDocPath = '',
        bool $createStarterLps = true
    ): array {
        $templates = self::getDepEdBaselineTemplates();
        if (empty($templateKey) || !isset($templates[$templateKey])) {
            $student = (new StudentModel())->findById($studentId);
            $templateKey = self::resolveTemplateKeyFromDisability($student['disability_type'] ?? '');
        }
        $tpl = $templates[$templateKey] ?? $templates['general_sped'];

        // 1. Ensure meeting
        $stmtM = $this->db->prepare("SELECT id FROM iep_meetings WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
        $stmtM->execute(['sid' => $studentId]);
        $meetingId = $stmtM->fetchColumn();
        if (!$meetingId) {
            $assessmentId = null;
            try {
                $stmtA = $this->db->prepare("SELECT id FROM assessment_records WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
                $stmtA->execute(['sid' => $studentId]);
                $assessmentId = $stmtA->fetchColumn() ?: null;

                if (!$assessmentId) {
                    $insA = $this->db->prepare("
                        INSERT INTO assessment_records (
                            student_id, assessed_by, conducted_by, status, version,
                            section_a_data, services_checked, screening_types,
                            created_at, updated_at
                        ) VALUES (
                            :sid, :uid, :uid, 'finalized', 1,
                            '{}', '[]', '[]',
                            NOW(), NOW()
                        )
                    ");
                    $insA->execute(['sid' => $studentId, 'uid' => $userId]);
                    $assessmentId = (int)$this->db->lastInsertId();
                }
            } catch (\Throwable $e) {
                error_log("Baseline assessment notice: " . $e->getMessage());
            }

            // Absolute fallback to guarantee foreign key iep_meetings_ibfk_2 is satisfied
            if (!$assessmentId) {
                $assessmentId = (int)$this->db->query("SELECT id FROM assessment_records ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;
            }

            $stmtInsM = $this->db->prepare("
                INSERT INTO iep_meetings (
                    student_id, assessment_id, scheduled_by, meeting_date,
                    status, agenda, created_at, updated_at
                ) VALUES (
                    :sid, :aid, :uid, NOW(),
                    'completed', 'Mid-Year SY 2026-2027 Ingestion: Baseline initialized from uploaded records', NOW(), NOW()
                )
            ");
            $stmtInsM->execute([
                'sid' => $studentId,
                'aid' => $assessmentId,
                'uid' => $userId
            ]);
            $meetingId = (int)$this->db->lastInsertId();
        }

        // 2. Ensure PDSP record
        $stmtP = $this->db->prepare("SELECT id, status FROM pdsp_records WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
        $stmtP->execute(['sid' => $studentId]);
        $pdsp = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$pdsp) {
            $stmtInsP = $this->db->prepare("
                INSERT INTO pdsp_records (meeting_id, student_id, filled_by, status, signed_document_path, completed_at, created_at, updated_at)
                VALUES (:mid, :sid, :uid, 'signed', :doc, NOW(), NOW(), NOW())
            ");
            $stmtInsP->execute([
                'mid' => $meetingId,
                'sid' => $studentId,
                'uid' => $userId,
                'doc' => !empty($uploadedDocPath) ? $uploadedDocPath : null,
            ]);
            $pdspId = (int)$this->db->lastInsertId();
        } else {
            $pdspId = (int)$pdsp['id'];
            $upSql = "UPDATE pdsp_records SET status = 'signed', completed_at = IFNULL(completed_at, NOW())";
            $upParams = ['id' => $pdspId];
            if (!empty($uploadedDocPath)) {
                $upSql .= ", signed_document_path = :doc";
                $upParams['doc'] = $uploadedDocPath;
            }
            $upSql .= " WHERE id = :id";
            $this->db->prepare($upSql)->execute($upParams);
        }

        // 3. Ensure PDSP domains with template content
        $stmtDomCheck = $this->db->prepare("SELECT COUNT(*) FROM pdsp_domains WHERE pdsp_id = :pid");
        $stmtDomCheck->execute(['pid' => $pdspId]);
        if ((int)$stmtDomCheck->fetchColumn() === 0) {
            $stmtInsDom = $this->db->prepare("
                INSERT INTO pdsp_domains (pdsp_id, domain_name, skills_description, educational_recommendation, mastered, q1_level, created_at)
                VALUES (:pid, :name, :desc, :rec, 0, 'Developing', NOW())
            ");
            foreach ($tpl['domains'] as $d) {
                $stmtInsDom->execute([
                    'pid'  => $pdspId,
                    'name' => $d['name'],
                    'desc' => $d['desc'],
                    'rec'  => $d['recommendation'] ?? null,
                ]);
            }
        }

        // 4. Ensure IEP record with status = 'signed' so workspace is directly unlocked
        $stmtIep = $this->db->prepare("
            SELECT id, status FROM iep_records 
            WHERE student_id = :sid AND school_year = :sy 
            ORDER BY id DESC LIMIT 1
        ");
        $stmtIep->execute(['sid' => $studentId, 'sy' => $schoolYear]);
        $iepRec = $stmtIep->fetch(PDO::FETCH_ASSOC);

        if (!$iepRec) {
            $reEvalDate = date('Y-m-d', strtotime('+1 year'));
            $stmtInsIep = $this->db->prepare("
                INSERT INTO iep_records (student_id, pdsp_id, drafted_by, school_year, status, signed_document_path, re_evaluation_date, created_at, updated_at)
                VALUES (:sid, :pid, :uid, :sy, 'signed', :doc, :reeval, NOW(), NOW())
            ");
            $stmtInsIep->execute([
                'sid'    => $studentId,
                'pid'    => $pdspId,
                'uid'    => $userId,
                'sy'     => $schoolYear,
                'doc'    => !empty($uploadedDocPath) ? $uploadedDocPath : null,
                'reeval' => $reEvalDate,
            ]);
            $iepId = (int)$this->db->lastInsertId();
        } else {
            $iepId = (int)$iepRec['id'];
            if ($iepRec['status'] !== 'signed' && $iepRec['status'] !== 'locked') {
                $this->db->prepare("UPDATE iep_records SET status = 'signed', pdsp_id = :pid, updated_at = NOW() WHERE id = :id")
                    ->execute(['id' => $iepId, 'pid' => $pdspId]);
            }
        }

        // 5. Seed IEP domains & steps
        $this->seedIepDomainsFromPdspIfEmpty($iepId, $pdspId);

        // Seed steps if empty
        $stmtStepsCount = $this->db->prepare("SELECT COUNT(*) FROM iep_steps WHERE iep_id = :iep_id");
        $stmtStepsCount->execute(['iep_id' => $iepId]);
        if ((int)$stmtStepsCount->fetchColumn() === 0 && !empty($tpl['steps'])) {
            $stmtInsStep = $this->db->prepare("
                INSERT INTO iep_steps (iep_id, step_number, step_domain, step_objective, duration_lp, instructional_evaluation, observation_unlocked, created_at)
                VALUES (:iep_id, :num, :dom, :obj, '4 Weeks', 'Quarterly Mastery Assessment Checklist', 1, NOW())
            ");
            $snum = 1;
            foreach ($tpl['steps'] as $stepObj) {
                $stmtInsStep->execute([
                    'iep_id' => $iepId,
                    'num'    => $snum++,
                    'dom'    => $tpl['domains'][($snum - 2) % count($tpl['domains'])]['name'] ?? 'Cognitive / Academics',
                    'obj'    => $stepObj,
                ]);
            }
        }

        // 6. If starter lesson plans requested, create them if none exist yet
        $lpsCreated = 0;
        if ($createStarterLps) {
            $stmtLpCount = $this->db->prepare("SELECT COUNT(*) FROM lesson_plans WHERE iep_id = :iep_id");
            $stmtLpCount->execute(['iep_id' => $iepId]);
            if ((int)$stmtLpCount->fetchColumn() === 0 && !empty($tpl['starter_plans'])) {
                $stmtInsLp = $this->db->prepare("
                    INSERT INTO lesson_plans (iep_id, student_id, created_by, title, pdsp_domain, assignment_type, status, created_at, updated_at)
                    VALUES (:iep_id, :sid, :uid, :title, :dom, :atype, 'published', NOW(), NOW())
                ");
                foreach ($tpl['starter_plans'] as $sp) {
                    $stmtInsLp->execute([
                        'iep_id' => $iepId,
                        'sid'    => $studentId,
                        'uid'    => $userId,
                        'title'  => $sp['title'],
                        'dom'    => $sp['domain'],
                        'atype'  => $sp['assignment_type'] ?? 'all',
                    ]);
                    $lpsCreated++;
                }
            }
        }

        return [
            'iep_id'       => $iepId,
            'pdsp_id'      => $pdspId,
            'meeting_id'   => $meetingId,
            'template_key' => $templateKey,
            'template_name'=> $tpl['name'],
            'lps_created'  => $lpsCreated,
        ];
    }

    /**
     * Get student auto-fill data for IEP header
     */
    public function getStudentAutoFill($studentId) {
        $stmt = $this->db->prepare("
            SELECT sr.*, es.first_name, es.middle_name, es.last_name, es.birth_date,
                   es.current_house_no, es.current_barangay, es.current_city, es.current_province,
                   es.grade_level_to_enroll, es.school_year AS enrollment_school_year
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE sr.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    // ============================================================
    // IEP DOMAINS, CORE, PDSP REFERENCE (Process 5 form Sections 3–4)
    // ============================================================

    /**
     * PDSP domain rows for read-only reference panel
     */
    public function getPdspDomainRows(int $pdspId): array {
        $stmt = $this->db->prepare("
            SELECT id, domain_name, sub_domain, skills_description, mastered,
                   educational_recommendation, q1_level, q2_level
            FROM pdsp_domains
            WHERE pdsp_id = :pdsp_id
            ORDER BY id ASC
        ");
        $stmt->execute(['pdsp_id' => $pdspId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Suggested priority needs text from PDSP rows not marked mastered
     */
    public function suggestPriorityNeedsFromPdsp(int $pdspId): string {
        $rows = $this->getPdspDomainRows($pdspId);
        $parts = [];
        foreach ($rows as $r) {
            if ((int) ($r['mastered'] ?? 0) === 1) {
                continue;
            }
            $line = trim($r['domain_name'] ?? '');
            if (!empty($r['skills_description'])) {
                $line .= ($line !== '' ? ' — ' : '') . trim($r['skills_description']);
            }
            if ($line !== '') {
                $parts[] = $line;
            }
        }
        return implode("\n", $parts);
    }

    public function getIepDomains(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT id, domain_name, display_order
            FROM iep_domains
            WHERE iep_id = :iep_id
            ORDER BY display_order ASC, id ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Copy PDSP domain names into iep_domains when the IEP has none yet
     */
    public function seedIepDomainsFromPdspIfEmpty(int $iepId, int $pdspId): void {
        $c = $this->db->prepare("SELECT COUNT(*) FROM iep_domains WHERE iep_id = :iep_id");
        $c->execute(['iep_id' => $iepId]);
        if ((int) $c->fetchColumn() > 0) {
            return;
        }
        $src = $this->db->prepare("
            SELECT domain_name FROM pdsp_domains WHERE pdsp_id = :pdsp_id ORDER BY id ASC
        ");
        $src->execute(['pdsp_id' => $pdspId]);
        $names = $src->fetchAll(PDO::FETCH_COLUMN);
        if (empty($names)) {
            return;
        }
        $ins = $this->db->prepare("
            INSERT INTO iep_domains (iep_id, domain_name, display_order)
            VALUES (:iep_id, :domain_name, :display_order)
        ");
        $ord = 0;
        foreach ($names as $n) {
            $n = trim((string) $n);
            if ($n === '') {
                continue;
            }
            $ins->execute([
                'iep_id'         => $iepId,
                'domain_name'   => $n,
                'display_order' => $ord++,
            ]);
        }
    }

    /**
     * Replace all domain tags for an IEP (prepared statements per row)
     */
    public function replaceIepDomains(int $iepId, array $domainNames): void {
        $this->db->beginTransaction();
        try {
            $del = $this->db->prepare("DELETE FROM iep_domains WHERE iep_id = :iep_id");
            $del->execute(['iep_id' => $iepId]);
            $ins = $this->db->prepare("
                INSERT INTO iep_domains (iep_id, domain_name, display_order)
                VALUES (:iep_id, :domain_name, :display_order)
            ");
            $ord = 0;
            foreach ($domainNames as $raw) {
                $name = trim((string) $raw);
                if ($name === '') {
                    continue;
                }
                $ins->execute([
                    'iep_id'         => $iepId,
                    'domain_name'   => $name,
                    'display_order' => $ord++,
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getIepCore(int $iepId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM iep_core WHERE iep_id = :iep_id LIMIT 1");
        $stmt->execute(['iep_id' => $iepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function upsertIepCore(int $iepId, ?string $developmental, ?string $priority, ?string $terminal): void {
        $stmt = $this->db->prepare("
            INSERT INTO iep_core (iep_id, developmental_domain, priority_needs, terminal_objectives)
            VALUES (:iep_id, :dd, :pn, :to)
            ON DUPLICATE KEY UPDATE
                developmental_domain = VALUES(developmental_domain),
                priority_needs       = VALUES(priority_needs),
                terminal_objectives  = VALUES(terminal_objectives)
        ");
        $stmt->execute([
            'iep_id' => $iepId,
            'dd'     => $developmental,
            'pn'     => $priority,
            'to'     => $terminal,
        ]);
    }

    // ============================================================
    // IEP STEPS (Section 5) + junctions to Process 6 / 7 data
    // ============================================================

    public function ensureDefaultStepForIep(int $iepId): void {
        $this->ensureStepDomainColumnExists();
        $c = $this->db->prepare("SELECT COUNT(*) FROM iep_steps WHERE iep_id = :iep_id");
        $c->execute(['iep_id' => $iepId]);
        if ((int) $c->fetchColumn() > 0) {
            return;
        }
        $ins = $this->db->prepare("
            INSERT INTO iep_steps (iep_id, step_number, step_domain, step_objective, duration_lp, instructional_evaluation, observation, observation_unlocked)
            VALUES (:iep_id, 1, NULL, '', '', '', '', 0)
        ");
        $ins->execute(['iep_id' => $iepId]);
    }

    public function getStepsForIep(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT s.*
            FROM iep_steps s
            WHERE s.iep_id = :iep_id
            ORDER BY s.step_number ASC, s.id ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findStepIdByIepAndStepNumber(int $iepId, int $stepNumber): int {
        $stmt = $this->db->prepare("
            SELECT id FROM iep_steps
            WHERE iep_id = :iep_id AND step_number = :n
            LIMIT 1
        ");
        $stmt->execute(['iep_id' => $iepId, 'n' => $stepNumber]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : 0;
    }

    public function stepBelongsToIep(int $stepId, int $iepId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM iep_steps WHERE id = :id AND iep_id = :iep_id LIMIT 1");
        $stmt->execute(['id' => $stepId, 'iep_id' => $iepId]);
        return (bool) $stmt->fetchColumn();
    }

    public function countStepJunctionLinks(int $stepId): int {
        $a = $this->db->prepare("SELECT COUNT(*) FROM iep_step_lesson_plans WHERE iep_step_id = :sid");
        $a->execute(['sid' => $stepId]);
        $b = $this->db->prepare("SELECT COUNT(*) FROM iep_step_materials WHERE iep_step_id = :sid");
        $b->execute(['sid' => $stepId]);
        return (int) $a->fetchColumn() + (int) $b->fetchColumn();
    }

    public function isObservationUnlockedForStep(int $stepId, int $studentId): bool {
        $pub = $this->db->prepare("
            SELECT 1
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            WHERE j.iep_step_id = :sid AND lp.status = 'published'
            LIMIT 1
        ");
        $pub->execute(['sid' => $stepId]);
        if (!$pub->fetchColumn()) {
            return false;
        }
        $sub = $this->db->prepare("
            SELECT 1
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            JOIN lms_activities a ON a.lesson_plan_id = lp.id
            JOIN lms_submissions s ON s.activity_id = a.id AND s.student_id = :student_id
            WHERE j.iep_step_id = :sid
            LIMIT 1
        ");
        $sub->execute(['sid' => $stepId, 'student_id' => $studentId]);
        return (bool) $sub->fetchColumn();
    }

    public function refreshObservationUnlockedForIep(int $iepId): void {
        $stmt = $this->db->prepare("SELECT student_id FROM iep_records WHERE id = :iep_id LIMIT 1");
        $stmt->execute(['iep_id' => $iepId]);
        $studentId = (int) $stmt->fetchColumn();
        if ($studentId <= 0) {
            return;
        }
        $ids = $this->db->prepare("SELECT id FROM iep_steps WHERE iep_id = :iep_id");
        $ids->execute(['iep_id' => $iepId]);
        $upd = $this->db->prepare("UPDATE iep_steps SET observation_unlocked = :u, updated_at = NOW() WHERE id = :id");
        foreach ($ids->fetchAll(PDO::FETCH_COLUMN) as $sid) {
            $sid = (int) $sid;
            $flag = $this->isObservationUnlockedForStep($sid, $studentId) ? 1 : 0;
            $upd->execute(['u' => $flag, 'id' => $sid]);
        }
    }

    public function getLessonPlansLinkedToStep(int $stepId): array {
        $stmt = $this->db->prepare("
            SELECT lp.id, lp.title, lp.status, lp.assignment_type, lp.pdsp_domain, lp.document_path
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            WHERE j.iep_step_id = :sid
            ORDER BY j.id ASC
        ");
        $stmt->execute(['sid' => $stepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Materials shown for this IEP step: explicit iep_step_materials links plus
     * all lesson_materials on lesson plans linked via iep_step_lesson_plans (workspace path).
     */
    public function getMaterialsLinkedToStep(int $stepId): array {
        $stmt = $this->db->prepare("
            SELECT id, title, material_type, file_path, external_url, uploaded_at
            FROM (
                SELECT lm.id, lm.title, lm.material_type, lm.file_path, lm.external_url, lm.uploaded_at, lm.display_order AS ord
                FROM iep_step_materials j
                JOIN lesson_materials lm ON lm.id = j.material_id
                WHERE j.iep_step_id = :sid
                UNION
                SELECT lm.id, lm.title, lm.material_type, lm.file_path, lm.external_url, lm.uploaded_at, lm.display_order AS ord
                FROM iep_step_lesson_plans j
                JOIN lesson_materials lm ON lm.lesson_plan_id = j.lesson_plan_id
                WHERE j.iep_step_id = :sid2
            ) AS combined
            ORDER BY combined.ord ASC, combined.id ASC
        ");
        $stmt->execute(['sid' => $stepId, 'sid2' => $stepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function linkLessonPlanToStep(int $stepId, int $lessonPlanId): void {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO iep_step_lesson_plans (iep_step_id, lesson_plan_id)
            VALUES (:step_id, :lesson_plan_id)
        ");
        $stmt->execute(['step_id' => $stepId, 'lesson_plan_id' => $lessonPlanId]);
    }

    public function unlinkLessonPlanFromStep(int $stepId, int $lessonPlanId): void {
        $stmt = $this->db->prepare("
            DELETE FROM iep_step_lesson_plans WHERE iep_step_id = :sid AND lesson_plan_id = :lpid
        ");
        $stmt->execute(['sid' => $stepId, 'lpid' => $lessonPlanId]);
    }

    public function insertStepRow(int $iepId, int $stepNumber): int {
        $this->ensureStepDomainColumnExists();
        $stmt = $this->db->prepare("
            INSERT INTO iep_steps (iep_id, step_number, step_domain, step_objective, duration_lp, instructional_evaluation, observation, observation_unlocked)
            VALUES (:iep_id, :sn, NULL, '', '', '', '', 0)
        ");
        $stmt->execute(['iep_id' => $iepId, 'sn' => $stepNumber]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStepFields(
        int $stepId,
        ?string $stepDomain,
        string $objective,
        string $duration,
        string $eval,
        ?string $observation,
        bool $observationUnlocked,
        ?string $pdspIndicatorText = null
    ): void {
        $this->ensureStepDomainColumnExists();
        $sd = $stepDomain !== null && $stepDomain !== '' ? $stepDomain : null;
        $pit = $pdspIndicatorText !== null && $pdspIndicatorText !== '' ? $pdspIndicatorText : null;
        if ($observationUnlocked) {
            $stmt = $this->db->prepare("
                UPDATE iep_steps
                SET step_domain = :sd, step_objective = :o, duration_lp = :d, instructional_evaluation = :e, observation = :ob, pdsp_indicator_text = :pit, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'sd' => $sd,
                'o'  => $objective,
                'd'  => $duration,
                'e'  => $eval,
                'ob' => $observation ?? '',
                'pit'=> $pit,
                'id' => $stepId,
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE iep_steps
                SET step_domain = :sd, step_objective = :o, duration_lp = :d, instructional_evaluation = :e, pdsp_indicator_text = :pit, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'sd' => $sd,
                'o' => $objective,
                'd' => $duration,
                'e' => $eval,
                'pit'=> $pit,
                'id' => $stepId,
            ]);
        }
    }

    public function deleteStepIfAllowed(int $stepId): bool {
        if ($this->countStepJunctionLinks($stepId) > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM iep_steps WHERE id = :id");
        $stmt->execute(['id' => $stepId]);
        return $stmt->rowCount() > 0;
    }

    public function renumberStepsForIep(int $iepId): void {
        $rows = $this->getStepsForIep($iepId);
        $n = 1;
        $upd = $this->db->prepare("UPDATE iep_steps SET step_number = :n WHERE id = :id");
        foreach ($rows as $r) {
            $upd->execute(['n' => $n++, 'id' => (int) $r['id']]);
        }
    }

    /**
     * First domain chip label for this IEP (used when step rows omit per-step domain).
     */
    public function getFirstIepDomainName(int $iepId): string {
        $stmt = $this->db->prepare('SELECT domain_name FROM iep_domains WHERE iep_id = :id ORDER BY id ASC LIMIT 1');
        $stmt->execute(['id' => $iepId]);
        $v = $stmt->fetchColumn();
        return $v ? trim((string) $v) : '';
    }

    /**
     * Sync step rows from ordered payload (ids optional for existing rows).
     *
     * @param array<int,array<string,mixed>> $rows
     */
    public function syncIepStepsFromPayload(int $iepId, array $rows): void {
        $this->db->beginTransaction();
        try {
            $stmtIds = $this->db->prepare("SELECT id FROM iep_steps WHERE iep_id = :iep_id ORDER BY step_number ASC, id ASC");
            $stmtIds->execute(['iep_id' => $iepId]);
            $existing = array_map('intval', $stmtIds->fetchAll(PDO::FETCH_COLUMN));

            $fallbackDomain = $this->getFirstIepDomainName($iepId);

            $kept = [];
            $ord  = 1;
            foreach ($rows as $r) {
                $obj = trim((string) ($r['step_objective'] ?? $r['objective'] ?? ''));
                $dur = trim((string) ($r['duration_lp'] ?? $r['strategies'] ?? ''));
                $ev  = trim((string) ($r['instructional_evaluation'] ?? $r['evaluation'] ?? ''));
                $dom = trim((string) ($r['step_domain'] ?? $r['domain'] ?? ''));
                $obs = isset($r['observation']) ? trim((string) $r['observation']) : '';
                $id  = isset($r['id']) ? (int) $r['id'] : 0;
                $pit = isset($r['pdsp_indicator_text']) ? trim((string) $r['pdsp_indicator_text']) : null;

                $unlocked = false;
                if ($id > 0) {
                    $chk = $this->db->prepare("SELECT observation_unlocked FROM iep_steps WHERE id = :id AND iep_id = :iep_id LIMIT 1");
                    $chk->execute(['id' => $id, 'iep_id' => $iepId]);
                    $unlocked = ((int) $chk->fetchColumn()) === 1;
                }
                if ($id > 0 && $unlocked && $obs === '') {
                    $obStmt = $this->db->prepare("SELECT observation FROM iep_steps WHERE id = :id AND iep_id = :iep_id LIMIT 1");
                    $obStmt->execute(['id' => $id, 'iep_id' => $iepId]);
                    $obs = (string) ($obStmt->fetchColumn() ?: '');
                }

                if ($dom === '' && $fallbackDomain !== '') {
                    $dom = $fallbackDomain;
                }
                $domainParam = $dom !== '' ? $dom : null;

                if ($id > 0 && in_array($id, $existing, true)) {
                    $this->updateStepFields($id, $domainParam, $obj, $dur, $ev, $obs, $unlocked, $pit);
                    $num = $this->db->prepare("UPDATE iep_steps SET step_number = :n WHERE id = :id");
                    $num->execute(['n' => $ord, 'id' => $id]);
                    $kept[] = $id;
                } else {
                    $nid = $this->insertStepRow($iepId, $ord);
                    $this->updateStepFields($nid, $domainParam, $obj, $dur, $ev, $obs, false, $pit);
                    $kept[] = $nid;
                }
                $ord++;
            }

            foreach ($existing as $eid) {
                if (!in_array($eid, $kept, true)) {
                    if ($this->countStepJunctionLinks($eid) > 0) {
                        throw new \RuntimeException(
                            'Cannot remove a step that still has linked lesson plans or materials. Remove those links first.'
                        );
                    }
                    $this->deleteStepIfAllowed($eid);
                }
            }

            $this->renumberStepsForIep($iepId);
            $this->refreshObservationUnlockedForIep($iepId);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getProgressSubmissionsForStep(int $stepId, int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT lp.title AS lesson_plan_title,
                   a.title AS activity_title,
                   a.max_score,
                   s.submitted_at,
                   s.auto_score
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            JOIN lms_activities a ON a.lesson_plan_id = lp.id
            JOIN lms_submissions s ON s.activity_id = a.id AND s.student_id = :student_id
            WHERE j.iep_step_id = :step_id
            ORDER BY s.submitted_at DESC
        ");
        $stmt->execute(['step_id' => $stepId, 'student_id' => $studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach ($rows as $row) {
            $parts = [];
            if ($row['lesson_plan_title'] ?? '') {
                $parts[] = 'LP: ' . $row['lesson_plan_title'];
            }
            if ($row['activity_title'] ?? '') {
                $parts[] = $row['activity_title'];
            }
            $score = $row['auto_score'];
            $max   = $row['max_score'];
            $scoreLine = '';
            if ($score !== null && $score !== '') {
                $scoreLine = 'Score: ' . $score . ($max !== null && $max !== '' ? ' / ' . $max : '');
            }
            $out[] = [
                'submitted_at' => $row['submitted_at'] ?? '',
                'status'       => 'Submitted',
                'notes'        => trim(implode(' — ', array_filter([implode(' · ', $parts), $scoreLine]))),
            ];
        }
        return $out;
    }

    /**
     * Get linked parent for a student
     */
    public function getLinkedParent($studentId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, u.role
            FROM users u
            JOIN enrollment_submissions es ON u.id = es.parent_id
            JOIN student_records sr ON es.id = sr.enrollment_id
            WHERE sr.id = :student_id
            AND u.role = 'parent'
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    // ============================================================
    // DOCUMENT COPIES & NOTIFICATIONS
    // ============================================================

    /**
     * Record that a copy was sent to a user
     */
    public function recordCopy($iepId, $userId) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO iep_copies (iep_id, sent_to, sent_at)
            VALUES (:iep_id, :user_id, NOW())
        ");
        return $stmt->execute(['iep_id' => $iepId, 'user_id' => $userId]);
    }

    /**
     * Mark copy as viewed by user
     */
    public function markCopyViewed($iepId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE iep_copies 
            SET viewed_at = NOW() 
            WHERE iep_id = :iep_id AND sent_to = :user_id AND viewed_at IS NULL
        ");
        return $stmt->execute(['iep_id' => $iepId, 'user_id' => $userId]);
    }

    public function getByGeneralTeacher($userId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            JOIN general_teacher_assignments gta ON sr.id = gta.student_id
            JOIN users u ON ir.drafted_by = u.id
            WHERE gta.general_teacher_id = :user_id
            ORDER BY ir.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}