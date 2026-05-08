<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS — IEP Model (Individualized Education Plan)

require_once __DIR__ . '/../../config/db.php';

class IEPModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
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
                   pr.status AS pdsp_status, pr.meeting_id
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
     * Get latest IEP for a student (most recent non-locked, or latest)
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
     * Get all signed/locked IEPs (for guidance, principal, parent read-only)
     */
    public function getSignedForRole($role, $userId) {
        if ($role === 'parent') {
            $stmt = $this->db->prepare("
                SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
                FROM iep_records ir
                JOIN student_records sr ON ir.student_id = sr.id
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                JOIN users u ON ir.drafted_by = u.id
                WHERE ir.status IN ('signed','locked')
                AND es.parent_id = :user_id
                ORDER BY ir.created_at DESC
            ");
            $stmt->execute(['user_id' => $userId]);
        } else {
            // guidance, principal — see all signed/locked
            $stmt = $this->db->prepare("
                SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
                FROM iep_records ir
                JOIN student_records sr ON ir.student_id = sr.id
                JOIN users u ON ir.drafted_by = u.id
                WHERE ir.status IN ('signed','locked','signing')
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
        $allowed = ['school_year','status','signing_method','signed_document_path',
                    're_evaluation_date','locked_at'];
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
     * Mark IEP as signed and locked
     */
    public function markSigned($iepId) {
        $stmt = $this->db->prepare("
            UPDATE iep_records
            SET status = 'signed', locked_at = NOW(), updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $iepId]);
    }

    /**
     * Check if student has a signed PDSP (Process 5 trigger)
     */
    public function studentHasSignedPDSP($studentId) {
        $stmt = $this->db->prepare("
            SELECT pr.id
            FROM pdsp_records pr
            JOIN iep_meetings im ON pr.meeting_id = im.id
            WHERE im.student_id = :student_id
            AND pr.status = 'signed'
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
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
    // IEP DOMAINS
    // ============================================================

    /**
     * Save domains (replaces existing)
     */
    public function saveDomains($iepId, array $domains) {
        // Delete existing
        $stmt = $this->db->prepare("DELETE FROM iep_domains WHERE iep_id = :iep_id");
        $stmt->execute(['iep_id' => $iepId]);
        // Insert new
        $stmt = $this->db->prepare("
            INSERT INTO iep_domains (iep_id, domain_name, display_order)
            VALUES (:iep_id, :domain_name, :display_order)
        ");
        foreach ($domains as $order => $name) {
            $stmt->execute([
                'iep_id'        => $iepId,
                'domain_name'   => trim($name),
                'display_order' => $order,
            ]);
        }
    }

    /**
     * Get domains for an IEP
     */
    public function getDomains($iepId) {
        $stmt = $this->db->prepare("
            SELECT * FROM iep_domains WHERE iep_id = :iep_id ORDER BY display_order ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    /**
     * Get PDSP domains for pre-population (from signed PDSP)
     */
    public function getPDSPDomains($pdspId) {
        $stmt = $this->db->prepare("
            SELECT * FROM pdsp_domains WHERE pdsp_id = :pdsp_id ORDER BY id ASC
        ");
        $stmt->execute(['pdsp_id' => $pdspId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // IEP CORE
    // ============================================================

    /**
     * Save core fields (upsert)
     */
    public function saveCore($iepId, $devDomain, $priorityNeeds, $terminalObjectives) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE with VALUES() to avoid duplicate named param error
        $stmt = $this->db->prepare("
            INSERT INTO iep_core (iep_id, developmental_domain, priority_needs, terminal_objectives)
            VALUES (:iep_id, :dev, :needs, :objectives)
            ON DUPLICATE KEY UPDATE
                developmental_domain = VALUES(developmental_domain),
                priority_needs       = VALUES(priority_needs),
                terminal_objectives  = VALUES(terminal_objectives)
        ");
        return $stmt->execute([
            'iep_id'     => $iepId,
            'dev'        => $devDomain,
            'needs'      => $priorityNeeds,
            'objectives' => $terminalObjectives,
        ]);
    }

    /**
     * Get core fields for an IEP
     */
    public function getCore($iepId) {
        $stmt = $this->db->prepare("SELECT * FROM iep_core WHERE iep_id = :iep_id LIMIT 1");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetch();
    }

    // ============================================================
    // IEP STEPS
    // ============================================================

    /**
     * Save steps (replaces existing)
     */
    public function saveSteps($iepId, array $steps) {
        $stmt = $this->db->prepare("DELETE FROM iep_steps WHERE iep_id = :iep_id");
        $stmt->execute(['iep_id' => $iepId]);

        $stmt = $this->db->prepare("
            INSERT INTO iep_steps
                (iep_id, step_number, objectives, observation, activities, materials, evaluation, duration_lp)
            VALUES
                (:iep_id, :step_number, :objectives, :observation, :activities, :materials, :evaluation, :duration_lp)
        ");
        foreach ($steps as $i => $step) {
            $stmt->execute([
                'iep_id'      => $iepId,
                'step_number' => $i + 1,
                'objectives'  => $step['objectives']  ?? null,
                'observation' => $step['observation'] ?? null,
                'activities'  => $step['activities']  ?? null,
                'materials'   => $step['materials']   ?? null,
                'evaluation'  => $step['evaluation']  ?? null,
                'duration_lp' => $step['duration_lp'] ?? null,
            ]);
        }
    }

    /**
     * Get steps for an IEP
     */
    public function getSteps($iepId) {
        $stmt = $this->db->prepare("
            SELECT * FROM iep_steps WHERE iep_id = :iep_id ORDER BY step_number ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // IEP SIGNATORIES
    // ============================================================

    /**
     * Save signatories (replaces existing — only if IEP not yet signed)
     */
    public function saveSignatories($iepId, array $signatories) {
        // Only allow if status is draft or signing
        $iep = $this->findById($iepId);
        if (!$iep || in_array($iep['status'], ['signed','locked'])) return false;

        $stmt = $this->db->prepare("DELETE FROM iep_signatories WHERE iep_id = :iep_id");
        $stmt->execute(['iep_id' => $iepId]);

        $stmt = $this->db->prepare("
            INSERT INTO iep_signatories (iep_id, signatory_role, signatory_name)
            VALUES (:iep_id, :role, :name)
        ");
        foreach ($signatories as $sig) {
            if (empty(trim($sig['name'] ?? ''))) continue;
            $stmt->execute([
                'iep_id' => $iepId,
                'role'   => $sig['role'],
                'name'   => trim($sig['name']),
            ]);
        }
        return true;
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

    /**
     * Save digital signature image for a signatory slot
     */
    public function saveSignatureImage($signatoryId, $imagePath) {
        $stmt = $this->db->prepare("
            UPDATE iep_signatories
            SET signature_image_path = :path, signed_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute(['path' => $imagePath, 'id' => $signatoryId]);
    }

    /**
     * Get signatory by ID
     */
    public function getSignatoryById($signatoryId) {
        $stmt = $this->db->prepare("SELECT * FROM iep_signatories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $signatoryId]);
        return $stmt->fetch();
    }

    /**
     * Check if all sent signatories have signed (digital flow)
     */
    public function allDigitalSignatoriesSigned($iepId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN signature_image_path IS NOT NULL THEN 1 ELSE 0 END) as signed
            FROM iep_signatories
            WHERE iep_id = :iep_id
        ");
        $stmt->execute(['iep_id' => $iepId]);
        $row = $stmt->fetch();
        return $row['total'] > 0 && $row['total'] == $row['signed'];
    }

    // ============================================================
    // IEP COPIES
    // ============================================================

    /**
     * Record that a copy was sent to a user
     */
    public function recordCopy($iepId, $sentTo) {
        $stmt = $this->db->prepare("
            INSERT INTO iep_copies (iep_id, sent_to, sent_at)
            VALUES (:iep_id, :sent_to, NOW())
        ");
        return $stmt->execute(['iep_id' => $iepId, 'sent_to' => $sentTo]);
    }

    /**
     * Mark copy as viewed
     */
    public function markCopyViewed($iepId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE iep_copies SET viewed_at = NOW()
            WHERE iep_id = :iep_id AND sent_to = :user_id AND viewed_at IS NULL
        ");
        return $stmt->execute(['iep_id' => $iepId, 'user_id' => $userId]);
    }

    // ============================================================
    // STUDENT AUTO-FILL DATA
    // ============================================================

    /**
     * Get full student data for IEP header auto-fill
     * Maps to: Name, Age, LRN, Section, Teacher, School, School Year, Grade Level
     */
    public function getStudentAutoFill($studentId) {
        $stmt = $this->db->prepare("
            SELECT
                sr.student_name,
                sr.lrn,
                es.grade_level_to_enroll   AS grade_level,
                es.school_year,
                es.first_name,
                es.last_name,
                es.middle_name,
                es.birth_date,
                TIMESTAMPDIFF(YEAR, es.birth_date, CURDATE()) AS age
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE sr.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * Get assessment documents for a student (for PDSP reference panel)
     */
    public function getAssessmentDocuments($studentId) {
        $stmt = $this->db->prepare("
            SELECT ad.id, ad.file_path, ad.original_name, ad.file_type,
                   asv.service_name, ar.version, ar.created_at AS assessed_at
            FROM assessment_documents ad
            JOIN assessment_services asv ON ad.assessment_service_id = asv.id
            JOIN assessment_records ar   ON asv.assessment_id = ar.id
            WHERE ar.student_id = :student_id
            AND ar.status = 'finalized'
            ORDER BY ar.version DESC, asv.service_name ASC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get the parent user linked to a student
     */
    public function getLinkedParent($studentId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email
            FROM users u
            JOIN enrollment_submissions es ON es.parent_id = u.id
            JOIN student_records sr ON sr.enrollment_id = es.id
            WHERE sr.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * Search users by role (for signatory send feature)
     */
    public function searchUsersByRole($role, $search = '') {
        $roleMap = [
            'guidance_counselor' => 'guidance',
            'school_head'        => 'principal',
            'sned_teacher'       => 'sped_teacher',
            'teacher'            => 'sped_teacher',
            'parent_guardian'    => 'parent',
            'ilrc_supervisor'    => 'guidance',
        ];
        $dbRole = $roleMap[$role] ?? $role;
        if ($search) {
            $stmt = $this->db->prepare("
                SELECT id, name, email FROM users
                WHERE role = :role AND status = 'active'
                AND (name LIKE :search OR email LIKE :search)
                ORDER BY name ASC LIMIT 10
            ");
            $stmt->execute(['role' => $dbRole, 'search' => '%' . $search . '%']);
        } else {
            $stmt = $this->db->prepare("
                SELECT id, name, email FROM users
                WHERE role = :role AND status = 'active'
                ORDER BY name ASC LIMIT 20
            ");
            $stmt->execute(['role' => $dbRole]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get PDSP record with signed document path (for PDSP reference panel)
     */
    public function getPDSPRecord($pdspId) {
        $stmt = $this->db->prepare("
            SELECT pr.*, im.meeting_date, im.meeting_location
            FROM pdsp_records pr
            JOIN iep_meetings im ON pr.meeting_id = im.id
            WHERE pr.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $pdspId]);
        return $stmt->fetch();
    }

    /**
     * Get list of students with signed PDSP (eligible for Process 5)
     */
    public function getEligibleStudents($teacherId = null) {
        if ($teacherId) {
            $stmt = $this->db->prepare("
                SELECT DISTINCT sr.id, sr.student_name, sr.lrn,
                       es.grade_level_to_enroll, es.school_year,
                       pr.id AS pdsp_id, pr.status AS pdsp_status
                FROM student_records sr
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                JOIN iep_meetings im ON im.student_id = sr.id
                JOIN pdsp_records pr ON pr.meeting_id = im.id
                WHERE pr.status = 'signed'
                AND im.scheduled_by = :teacher_id
                ORDER BY sr.student_name ASC
            ");
            $stmt->execute(['teacher_id' => $teacherId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT DISTINCT sr.id, sr.student_name, sr.lrn,
                       es.grade_level_to_enroll, es.school_year,
                       pr.id AS pdsp_id, pr.status AS pdsp_status
                FROM student_records sr
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                JOIN iep_meetings im ON im.student_id = sr.id
                JOIN pdsp_records pr ON pr.meeting_id = im.id
                WHERE pr.status = 'signed'
                ORDER BY sr.student_name ASC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }
}
