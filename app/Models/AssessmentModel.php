<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment Model

require_once __DIR__ . '/../../config/db.php';

class AssessmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create assessment from parent submission
     * Returns: assessment ID
     */
    public function create($studentId, $educationHistory, $assessmentInfo, $submittedBy) {
        try {
            // Get current quarter (Q1 or Q2 based on month)
            $month = (int)date('m');
            $quarter = ($month >= 1 && $month <= 6) ? 'Q1' : 'Q2';
            
            // Check if assessment already exists for this quarter
            $existing = $this->getByQuarter($studentId, $quarter);
            if ($existing) {
                // Update existing assessment instead of creating new
                return $this->update($existing['id'], $educationHistory, $assessmentInfo);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO assessment_records (
                    student_id, parent_id, submitted_data, education_history,
                    assessment_info, status, quarter, submitted_at, assessed_by
                ) VALUES (
                    :student_id, :parent_id, :submitted_data, :education_history,
                    :assessment_info, 'pending', :quarter, NOW(), :assessed_by
                )
            ");
            
            $result = $stmt->execute([
                'student_id' => $studentId,
                'parent_id' => $submittedBy,
                'submitted_data' => json_encode([
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'submitted_by' => $submittedBy
                ]),
                'education_history' => json_encode($educationHistory),
                'assessment_info' => json_encode($assessmentInfo),
                'quarter' => $quarter,
                'assessed_by' => $submittedBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create assessment");
            }
            
            $assessmentId = $this->db->lastInsertId();
            error_log("Created assessment ID: $assessmentId for student: $studentId, quarter: $quarter");
            
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update assessment
     */
    private function update($assessmentId, $educationHistory, $assessmentInfo) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET education_history = :education_history,
                    assessment_info = :assessment_info,
                    submitted_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'education_history' => json_encode($educationHistory),
                'assessment_info' => json_encode($assessmentInfo)
            ]);
            
            if (!$result) {
                throw new Exception("Failed to update assessment");
            }
            
            error_log("Updated assessment ID: $assessmentId");
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->update() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assessment by ID
     */
    public function findById($assessmentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn, sr.date_of_birth,
                   u.name as parent_name, u.email as parent_email
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            JOIN users u ON ar.parent_id = u.id
            WHERE ar.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $assessmentId]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Decode JSON fields
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
            $result['submitted_data'] = json_decode($result['submitted_data'], true);
        }
        
        return $result;
    }

    /**
     * Get all assessments for a student
     */
    public function getByStudentId($studentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id
            ORDER BY ar.quarter DESC, ar.submitted_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $results;
    }

    /**
     * Get latest assessment for a student
     */
    public function getLatest($studentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id
            ORDER BY ar.submitted_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $result;
    }

    /**
     * Get assessment by quarter
     */
    public function getByQuarter($studentId, $quarter) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id AND ar.quarter = :quarter
            ORDER BY ar.submitted_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId, 'quarter' => $quarter]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $result;
    }

    /**
     * Get all pending assessments for SPED teacher review
     */
    public function getPendingForReview() {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn, sr.date_of_birth,
                   u.name as parent_name, u.email as parent_email
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            JOIN users u ON ar.parent_id = u.id
            WHERE ar.status = 'pending'
            ORDER BY ar.submitted_at DESC
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $results;
    }

    /**
     * Approve assessment
     */
    public function approve($assessmentId, $approvedBy) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET status = 'approved',
                    reviewed_by = :reviewed_by,
                    reviewed_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'reviewed_by' => $approvedBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to approve assessment");
            }
            
            error_log("Approved assessment ID: $assessmentId by user: $approvedBy");
            return true;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->approve() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reject assessment
     */
    public function reject($assessmentId, $rejectedBy, $reason) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET status = 'rejected',
                    reviewed_by = :reviewed_by,
                    review_note = :review_note,
                    reviewed_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'reviewed_by' => $rejectedBy,
                'review_note' => $reason
            ]);
            
            if (!$result) {
                throw new Exception("Failed to reject assessment");
            }
            
            error_log("Rejected assessment ID: $assessmentId by user: $rejectedBy");
            return true;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->reject() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assessment history for a student
     */
    public function getHistory($studentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id
            ORDER BY ar.quarter DESC, ar.submitted_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $results;
    }

    /**
     * Get students ready for assessment (verified but no assessment yet)
     */
    public function getStudentsReadyForAssessment() {
        $stmt = $this->db->prepare("
            SELECT sr.*, u.name as parent_name, u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            JOIN users u ON es.parent_id = u.id
            WHERE sr.id NOT IN (
                SELECT DISTINCT student_id FROM assessment_records
            )
            ORDER BY sr.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
