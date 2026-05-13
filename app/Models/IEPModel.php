<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5 (SIMPLIFIED)
// Last modified: 2026-05-12
// Part of: SPED LMS — IEP Model (Individualized Education Plan) - Upload Only System

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
                WHERE ir.status IN ('signed','locked')
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
        $allowed = ['school_year','status','signed_document_path','re_evaluation_date','locked_at'];
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

    /**
     * Save signatories (replaces existing)
     */
    public function saveSignatories($iepId, array $signatories) {
        // Delete existing
        $stmt = $this->db->prepare("DELETE FROM iep_signatories WHERE iep_id = :iep_id");
        $stmt->execute(['iep_id' => $iepId]);

        // Insert new ones
        if (!empty($signatories)) {
            $stmt = $this->db->prepare("
                INSERT INTO iep_signatories (iep_id, signatory_role, signatory_name, signed_at)
                VALUES (:iep_id, :role, :name, NOW())
            ");
            foreach ($signatories as $sig) {
                $stmt->execute([
                    'iep_id' => $iepId,
                    'role'   => $sig['role'],
                    'name'   => $sig['name']
                ]);
            }
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

    // ============================================================
    // STUDENT DATA & ELIGIBILITY
    // ============================================================

    /**
     * Get students eligible for new IEP (have signed PDSP, no active IEP draft)
     */
    public function getEligibleStudents($teacherId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT sr.id, sr.student_name, sr.lrn,
                   pr.created_at as pdsp_signed_at
            FROM student_records sr
            JOIN iep_meetings im ON sr.id = im.student_id
            JOIN pdsp_records pr ON im.id = pr.meeting_id
            WHERE pr.status = 'signed'
            AND sr.id NOT IN (
                SELECT student_id FROM iep_records 
                WHERE status IN ('draft') 
                AND YEAR(created_at) = YEAR(NOW())
            )
            ORDER BY pr.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get student auto-fill data for IEP header
     */
    public function getStudentAutoFill($studentId) {
        $stmt = $this->db->prepare("
            SELECT sr.*, es.first_name, es.middle_name, es.last_name, es.birth_date,
                   es.current_house_no, es.current_barangay, es.current_city, es.current_province
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE sr.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
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
}