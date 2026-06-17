<?php
// Part of: SignED — Process 9 Classroom Observation Model
// Last modified: 2026-06-17

require_once __DIR__ . '/../../config/db.php';

class ClassroomObservationModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get indicator set for a specific school year
     */
    public function getIndicatorSet(string $schoolYear): array {
        $stmt = $this->db->prepare("
            SELECT * FROM cot_indicator_sets
            WHERE school_year = :school_year
            ORDER BY indicator_number ASC
        ");
        $stmt->execute(['school_year' => $schoolYear]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all school years that have indicator sets
     */
    public function getIndicatorSchoolYears(): array {
        $stmt = $this->db->query("
            SELECT DISTINCT school_year FROM cot_indicator_sets
            ORDER BY school_year DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Save/Replace indicators for a school year
     */
    public function saveIndicatorSet(string $schoolYear, array $indicators, int $userId): bool {
        try {
            $this->db->beginTransaction();

            // Delete existing indicator set for this SY
            $stmt = $this->db->prepare("DELETE FROM cot_indicator_sets WHERE school_year = :school_year");
            $stmt->execute(['school_year' => $schoolYear]);

            // Insert new indicator set
            $stmtInsert = $this->db->prepare("
                INSERT INTO cot_indicator_sets (school_year, indicator_number, indicator_text, competency_code, created_by)
                VALUES (:school_year, :indicator_number, :indicator_text, :competency_code, :created_by)
            ");

            foreach ($indicators as $index => $indicator) {
                $stmtInsert->execute([
                    'school_year' => $schoolYear,
                    'indicator_number' => $index + 1,
                    'indicator_text' => trim($indicator['indicator_text']),
                    'competency_code' => trim($indicator['competency_code']),
                    'created_by' => $userId
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to save indicator set: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Schedule a new observation
     */
    public function scheduleObservation(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO classroom_observations (
                observer_id, observed_teacher_id, school_year, quarter,
                observation_number, subject_grade_level, scheduled_at, status
            ) VALUES (
                :observer_id, :observed_teacher_id, :school_year, :quarter,
                :observation_number, :subject_grade_level, :scheduled_at, 'scheduled'
            )
        ");

        $stmt->execute([
            'observer_id' => $data['observer_id'],
            'observed_teacher_id' => $data['observed_teacher_id'],
            'school_year' => $data['school_year'],
            'quarter' => $data['quarter'],
            'observation_number' => $data['observation_number'],
            'subject_grade_level' => $data['subject_grade_level'],
            'scheduled_at' => $data['scheduled_at']
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Find an observation by its ID, joining observer and observed teacher details
     */
    public function getObservationById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT co.*, 
                   u1.name AS observer_name, 
                   u2.name AS observed_teacher_name,
                   u2.first_name AS observed_teacher_first_name,
                   u2.last_name AS observed_teacher_last_name
            FROM classroom_observations co
            JOIN users u1 ON co.observer_id = u1.id
            JOIN users u2 ON co.observed_teacher_id = u2.id
            WHERE co.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Save or update a rating for an indicator in a specific observation
     */
    public function saveRating(int $observationId, int $indicatorId, string $rating): bool {
        $stmt = $this->db->prepare("
            INSERT INTO observation_ratings (observation_id, indicator_id, rating)
            VALUES (:observation_id, :indicator_id, :rating)
            ON DUPLICATE KEY UPDATE rating = :rating_update
        ");
        return $stmt->execute([
            'observation_id' => $observationId,
            'indicator_id' => $indicatorId,
            'rating' => $rating,
            'rating_update' => $rating
        ]);
    }

    /**
     * Save or update comments on an observation
     */
    public function saveComments(int $observationId, ?string $comments): bool {
        $stmt = $this->db->prepare("
            UPDATE classroom_observations
            SET other_comments = :comments
            WHERE id = :id
        ");
        return $stmt->execute([
            'comments' => $comments,
            'id' => $observationId
        ]);
    }

    /**
     * Get all current ratings for an observation
     */
    public function getObservationRatings(int $observationId): array {
        $stmt = $this->db->prepare("
            SELECT indicator_id, rating FROM observation_ratings
            WHERE observation_id = :observation_id
        ");
        $stmt->execute(['observation_id' => $observationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Finalize the observation, locking it and storing the computed average score
     */
    public function finalizeObservation(int $observationId, float $averageScore): bool {
        $stmt = $this->db->prepare("
            UPDATE classroom_observations
            SET status = 'finalized',
                average_score = :average_score,
                finalized_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'average_score' => $averageScore,
            'id' => $observationId
        ]);
    }

    /**
     * Get observation history (filtered by school year, quarter, observed teacher)
     */
    public function getObservationHistory(int $userId, string $role, array $filters = []): array {
        $sql = "
            SELECT co.*, 
                   u1.name AS observer_name, 
                   u2.name AS observed_teacher_name
            FROM classroom_observations co
            JOIN users u1 ON co.observer_id = u1.id
            JOIN users u2 ON co.observed_teacher_id = u2.id
            WHERE 1=1
        ";
        $params = [];

        // RBAC constraints: Master Teacher sees only observations they conducted.
        // Admin sees all observations.
        if ($role !== 'admin') {
            $sql .= " AND co.observer_id = :observer_id";
            $params['observer_id'] = $userId;
        }

        // Apply filters
        if (!empty($filters['school_year'])) {
            $sql .= " AND co.school_year = :school_year";
            $params['school_year'] = $filters['school_year'];
        }

        if (!empty($filters['quarter'])) {
            $sql .= " AND co.quarter = :quarter";
            $params['quarter'] = $filters['quarter'];
        }

        if (!empty($filters['observed_teacher_id'])) {
            $sql .= " AND co.observed_teacher_id = :observed_teacher_id";
            $params['observed_teacher_id'] = (int)$filters['observed_teacher_id'];
        }

        $sql .= " ORDER BY co.scheduled_at DESC, co.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active SPED teachers
     */
    public function getActiveSpedTeachers(): array {
        $stmt = $this->db->prepare("
            SELECT id, name, first_name, last_name, email
            FROM users
            WHERE role = 'sped_teacher' AND status = 'active'
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper to get user info by id
     */
    public function getUserInfo(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
