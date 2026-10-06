<?php
// SignED — External Mainstream Mastery Model (MVP)
// Manages platform-neutral manual evidence and mastery result logging.

require_once __DIR__ . '/../../config/db.php';

class ExternalMasteryModel {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get mastery result for a specific lesson plan and student
     */
    public function getResultByLessonAndStudent(int $lessonPlanId, int $studentId): ?array {
        $stmt = $this->db->prepare("
            SELECT emr.*,
                   u.name AS recorder_name,
                   sr.student_name,
                   sr.lrn,
                   lp.title AS lesson_title
            FROM external_mastery_results emr
            JOIN users u ON emr.recorded_by = u.id
            JOIN student_records sr ON emr.student_id = sr.id
            JOIN lesson_plans lp ON emr.lesson_plan_id = lp.id
            WHERE emr.lesson_plan_id = :lp_id AND emr.student_id = :student_id
            LIMIT 1
        ");
        $stmt->execute([
            'lp_id'      => $lessonPlanId,
            'student_id' => $studentId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all recorded mastery results for a specific lesson plan
     */
    public function getResultsByLesson(int $lessonPlanId): array {
        $stmt = $this->db->prepare("
            SELECT emr.*,
                   u.name AS recorder_name,
                   sr.student_name,
                   sr.lrn
            FROM external_mastery_results emr
            JOIN users u ON emr.recorded_by = u.id
            JOIN student_records sr ON emr.student_id = sr.id
            WHERE emr.lesson_plan_id = :lp_id
            ORDER BY emr.updated_at DESC
        ");
        $stmt->execute(['lp_id' => $lessonPlanId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get all recorded mastery results for a student across all lessons
     */
    public function getResultsByStudent(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT emr.*,
                   u.name AS recorder_name,
                   lp.title AS lesson_title,
                   lp.pdsp_domain
            FROM external_mastery_results emr
            JOIN users u ON emr.recorded_by = u.id
            JOIN lesson_plans lp ON emr.lesson_plan_id = lp.id
            WHERE emr.student_id = :student_id
            ORDER BY emr.updated_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Record or update an external mainstream mastery result (manual teacher evidence)
     */
    public function saveResult(int $lessonPlanId, int $studentId, int $teacherId, array $data): int {
        $validStatuses = ['completed', 'needs_support', 'not_attempted'];
        $status = strtolower(trim($data['mastery_status'] ?? 'completed'));
        if (!in_array($status, $validStatuses, true)) {
            $status = 'completed';
        }

        $score = null;
        if (isset($data['score']) && $data['score'] !== '' && is_numeric($data['score'])) {
            $numScore = (float)$data['score'];
            if ($numScore >= 0 && $numScore <= 100) {
                $score = $numScore;
            }
        }

        $resourceTitle = trim($data['resource_title'] ?? '');
        $resourceUrl   = trim($data['resource_url'] ?? '');
        if (!empty($resourceUrl) && !filter_var($resourceUrl, FILTER_VALIDATE_URL)) {
            $resourceUrl = null;
        }

        $teacherNotes = trim($data['teacher_notes'] ?? '');
        $completedAt  = !empty($data['completed_at']) ? date('Y-m-d', strtotime($data['completed_at'])) : date('Y-m-d');

        $stmt = $this->db->prepare("
            INSERT INTO external_mastery_results (
                lesson_plan_id, student_id, recorded_by, resource_title, resource_url,
                mastery_status, score, teacher_notes, completed_at, created_at, updated_at
            ) VALUES (
                :lp_id, :sid, :teacher_id, :title, :url,
                :status, :score, :notes, :completed_at, NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                recorded_by    = VALUES(recorded_by),
                resource_title = VALUES(resource_title),
                resource_url   = VALUES(resource_url),
                mastery_status = VALUES(mastery_status),
                score          = VALUES(score),
                teacher_notes  = VALUES(teacher_notes),
                completed_at   = VALUES(completed_at),
                updated_at     = NOW()
        ");

        $stmt->execute([
            'lp_id'        => $lessonPlanId,
            'sid'          => $studentId,
            'teacher_id'   => $teacherId,
            'title'        => $resourceTitle ?: null,
            'url'          => $resourceUrl ?: null,
            'status'       => $status,
            'score'        => $score,
            'notes'        => $teacherNotes ?: null,
            'completed_at' => $completedAt
        ]);

        $existing = $this->getResultByLessonAndStudent($lessonPlanId, $studentId);
        return $existing ? (int)$existing['id'] : (int)$this->db->lastInsertId();
    }

    /**
     * Delete a recorded mastery result
     */
    public function deleteResult(int $id, int $teacherId, bool $isAdmin = false): bool {
        if ($isAdmin) {
            $stmt = $this->db->prepare("DELETE FROM external_mastery_results WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }
        $stmt = $this->db->prepare("DELETE FROM external_mastery_results WHERE id = :id AND recorded_by = :uid");
        return $stmt->execute(['id' => $id, 'uid' => $teacherId]);
    }
}
