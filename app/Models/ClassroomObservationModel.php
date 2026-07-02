<?php
// Part of: SignED — Process 9 Classroom Observation Model
// Last modified: 2026-06-17

require_once __DIR__ . '/../../config/db.php';

class ClassroomObservationModel {
    private PDO $db;

    private static array $defaultIndicators = [
        'SY 2025-2026' => [
            ['1.1.2', 'Apply knowledge of content within and across curriculum teaching areas'],
            ['1.4.2', 'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills'],
            ['1.5.2', 'Apply a range of teaching strategies to develop critical and creative thinking, as well as other higher-order thinking skills'],
            ['2.3.2', 'Manage classroom structure to engage learners, individually or in groups, in meaningful exploration, discovery and hands-on activities within a range of physical learning environments'],
            ['2.6.2', 'Manage learner behavior constructively by applying positive and non-violent discipline to ensure learning-focused environments'],
            ['3.1.2', "Use differentiated, developmentally appropriate learning experiences to address learners' gender, needs, strengths, interests and experiences"],
            ['4.1.2', 'Plan, manage and implement developmentally sequenced teaching and learning process to meet curriculum requirements and varied teaching contexts'],
            ['4.5.2', 'Select, develop, organize and use appropriate teaching and learning resources, including ICT, to address learning goals'],
            ['5.1.2', 'Design, select, organize and use diagnostic, formative and summative assessment strategies consistent with curriculum requirements']
        ],
        'SY 2026-2027' => [
            ['1.1.2', 'Apply knowledge of content within and across curriculum teaching areas'],
            ['1.4.2', 'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills'],
            ['1.5.2', 'Apply a range of teaching strategies to develop critical and creative thinking, as well as other higher-order thinking skills'],
            ['1.6.2', 'Display proficient use of Mother Tongue, Filipino, and English to facilitate teaching and learning'],
            ['2.1.2', 'Establish safe and secure learning environments to enhance learning through the consistent implementation of policies, guidelines, and procedures'],
            ['2.2.2', 'Maintain learning environments that promote fairness, respect, and care to encourage learning'],
            ['3.2.2', 'Establish a learner-centered culture by using teaching strategies that respond to their linguistic, cultural, socio-economic, and religious backgrounds'],
            ['3.5.2', 'Adapt and use culturally appropriate teaching strategies to address the needs of learners from indigenous groups'],
            ['5.3.2', 'Use effective strategies for providing timely, accurate, and constructive feedback to improve learner performance']
        ],
        'SY 2027-2028' => [
            ['1.1.2', 'Apply knowledge of content within and across curriculum teaching areas'],
            ['1.4.2', 'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills'],
            ['1.3.2', 'Ensure the positive use of ICT to facilitate the teaching and learning process'],
            ['1.7.2', 'Use effective verbal and non-verbal classroom communication strategies to support learner understanding, participation, engagement, and achievement'],
            ['2.4.2', 'Maintain supportive learning environments that nurture and inspire learners to participate, cooperate, and collaborate in continued learning'],
            ['2.5.2', 'Apply a range of successful strategies that maintain learning environments that motivate learners to work productively by assuming responsibility for their own learning'],
            ['3.3.2', 'Design, adapt, and implement teaching strategies that are responsive to learners with disabilities, giftedness, and talents'],
            ['3.4.2', 'Plan and deliver teaching strategies that are responsive to the special educational needs of learners in difficult circumstances, including: geographic isolation; chronic illness; displacement due to armed conflict, urban resettlement or disasters; child abuse and child labor practices']
        ]
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureDefaultIndicators();
        $this->ensureTeacherSignatureColumn();
    }

    /**
     * Ensure teacher signature column exists (self-heal if migration v58 was skipped).
     */
    private function ensureTeacherSignatureColumn(): void {
        try {
            $chk = $this->db->prepare("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'classroom_observations'
                  AND COLUMN_NAME = 'teacher_signature_path'
            ");
            $chk->execute();
            if ((int) $chk->fetchColumn() > 0) {
                return;
            }
            $this->db->exec("
                ALTER TABLE classroom_observations
                ADD COLUMN teacher_signature_path VARCHAR(500) NULL AFTER teacher_signed_at
            ");
        } catch (\Throwable $e) {
            error_log('ClassroomObservationModel::ensureTeacherSignatureColumn: ' . $e->getMessage());
        }
    }

    /**
     * Auto-seed default indicators if missing
     */
    public function ensureDefaultIndicators(): void {
        try {
            $count = $this->db->query("SELECT COUNT(*) FROM cot_indicator_sets")->fetchColumn();
            if ($count > 0) {
                $existingYears = $this->db->query("SELECT DISTINCT school_year FROM cot_indicator_sets")->fetchAll(PDO::FETCH_COLUMN);
                $missingYears = array_diff(array_keys(self::$defaultIndicators), $existingYears);
                if (empty($missingYears)) {
                    return;
                }
            }

            $stmt = $this->db->prepare("
                INSERT IGNORE INTO cot_indicator_sets (school_year, indicator_number, indicator_text, competency_code, created_by)
                VALUES (:school_year, :indicator_number, :indicator_text, :competency_code, NULL)
            ");

            foreach (self::$defaultIndicators as $sy => $list) {
                // Check if this school year exists in DB
                $check = $this->db->prepare("SELECT COUNT(*) FROM cot_indicator_sets WHERE school_year = ?");
                $check->execute([$sy]);
                if ($check->fetchColumn() > 0) {
                    continue;
                }

                foreach ($list as $idx => $item) {
                    $stmt->execute([
                        'school_year' => $sy,
                        'indicator_number' => $idx + 1,
                        'indicator_text' => $item[1],
                        'competency_code' => $item[0]
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Failed to auto-seed indicators: " . $e->getMessage());
        }
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
     * Get default indicator set for a school year
     */
    public function getDefaultIndicatorSet(string $schoolYear): array {
        $items = self::$defaultIndicators[$schoolYear] ?? [];
        return array_map(fn($indicator) => [
            'competency_code' => $indicator[0],
            'indicator_text' => $indicator[1],
        ], $items);
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
        $dbVal = ($rating === 'N/A') ? null : $rating;
        $stmt = $this->db->prepare("
            INSERT INTO observation_ratings (observation_id, indicator_id, rating)
            VALUES (:observation_id, :indicator_id, :rating)
            ON DUPLICATE KEY UPDATE rating = :rating_update
        ");
        return $stmt->execute([
            'observation_id' => $observationId,
            'indicator_id' => $indicatorId,
            'rating' => $dbVal,
            'rating_update' => $dbVal
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
    public function finalizeObservation(int $observationId, float $averageScore, ?string $signaturePath = null): bool {
        $stmt = $this->db->prepare("
            UPDATE classroom_observations
            SET status = 'finalized',
                average_score = :average_score,
                finalized_at = NOW(),
                teacher_signed_at = NOW(),
                teacher_signature_path = :teacher_signature_path
            WHERE id = :id
        ");
        return $stmt->execute([
            'average_score' => $averageScore,
            'teacher_signature_path' => $signaturePath,
            'id' => $observationId
        ]);
    }

    /**
     * Set observation status to pending_signoff (observer finalization step)
     */
    public function setPendingSignoff(int $observationId): bool {
        $stmt = $this->db->prepare("
            UPDATE classroom_observations
            SET status = 'pending_signoff'
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $observationId]);
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

        // RBAC constraints: 
        // - Admin sees all observations.
        // - SPED Teacher sees only observations conducted on themselves.
        // - Master Teacher sees only observations they conducted.
        if ($role === 'sped_teacher') {
            $sql .= " AND co.observed_teacher_id = :observed_teacher_id";
            $params['observed_teacher_id'] = $userId;
        } elseif ($role !== 'admin') {
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
