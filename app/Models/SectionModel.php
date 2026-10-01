<?php
// Part of: SignED — Stage 2 Section Model
// Last modified: 2026-08-21

require_once __DIR__ . '/../../config/db.php';

class SectionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all sections for a school
     */
    public function getSectionsBySchool(?int $schoolId): array {
        if (!$schoolId) {
            $stmt = $this->db->query("
                SELECT s.*, u.name as adviser_name 
                FROM sections s
                LEFT JOIN users u ON s.adviser_teacher_id = u.id
                ORDER BY s.grade_level ASC, s.section_name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->db->prepare("
            SELECT s.*, u.name as adviser_name,
                   (SELECT COUNT(*) FROM student_records sr WHERE sr.section_id = s.id) as enrolled_students_count
            FROM sections s
            LEFT JOIN users u ON s.adviser_teacher_id = u.id
            WHERE s.school_id = :school_id AND s.status = 'active'
            ORDER BY s.grade_level ASC, s.section_name ASC
        ");
        $stmt->execute(['school_id' => $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single section by ID
     */
    public function getSectionById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name as adviser_name 
            FROM sections s
            LEFT JOIN users u ON s.adviser_teacher_id = u.id
            WHERE s.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Create a new section
     */
    public function createSection(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO sections (school_id, name, section_name, grade_level, room_number, max_capacity, adviser_teacher_id, school_year, status)
            VALUES (:school_id, :sec_name1, :sec_name2, :grade_level, :room_number, :max_capacity, :adviser_teacher_id, :school_year, 'active')
        ");
        $secName = trim($data['section_name'] ?? $data['name'] ?? 'SPED Section');
        $stmt->execute([
            'school_id'          => $data['school_id'] ?? null,
            'sec_name1'          => $secName,
            'sec_name2'          => $secName,
            'grade_level'        => $data['grade_level'] ?? 'SPED',
            'room_number'        => $data['room_number'] ?? null,
            'max_capacity'       => (int)($data['max_capacity'] ?? 15),
            'adviser_teacher_id' => !empty($data['adviser_teacher_id']) ? (int)$data['adviser_teacher_id'] : null,
            'school_year'        => $data['school_year'] ?? '2026-2027'
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update section details
     */
    public function updateSection(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE sections 
            SET name = :sec_name1,
                section_name = :sec_name2,
                grade_level = :grade_level,
                room_number = :room_number,
                max_capacity = :max_capacity,
                adviser_teacher_id = :adviser_teacher_id,
                school_year = :school_year
            WHERE id = :id
        ");
        $secName = trim($data['section_name'] ?? $data['name'] ?? 'SPED Section');
        return $stmt->execute([
            'id'                 => $id,
            'sec_name1'          => $secName,
            'sec_name2'          => $secName,
            'grade_level'        => $data['grade_level'] ?? 'SPED',
            'room_number'        => $data['room_number'] ?? null,
            'max_capacity'       => (int)($data['max_capacity'] ?? 15),
            'adviser_teacher_id' => !empty($data['adviser_teacher_id']) ? (int)$data['adviser_teacher_id'] : null,
            'school_year'        => $data['school_year'] ?? '2026-2027'
        ]);
    }

    /**
     * Delete / archive a section
     */
    public function deleteSection(int $id): bool {
        $stmt = $this->db->prepare("UPDATE sections SET status = 'archived' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get Next Round-Robin Section for an Enrollee
     * Finds active sections with capacity remaining in the school (matching grade level or general SPED)
     * and picks the one with the fewest enrolled learners or round-robin ID sequence.
     */
    public function getNextRoundRobinSection(?int $schoolId, string $gradeLevel = 'SPED'): ?array {
        if (!$schoolId) {
            $stmt = $this->db->query("SELECT school_id FROM schools LIMIT 1");
            $schoolId = (int)$stmt->fetchColumn();
        }

        // Query active sections for this school where enrolled count < max_capacity
        // Ordered by enrolled count ASC, then id ASC (Round-Robin with capacity checking)
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   COUNT(sr.id) as current_enrolled
            FROM sections s
            LEFT JOIN student_records sr ON sr.section_id = s.id
            WHERE s.school_id = :school_id 
              AND s.status = 'active'
            GROUP BY s.id
            HAVING current_enrolled < s.max_capacity
            ORDER BY current_enrolled ASC, s.id ASC
            LIMIT 1
        ");
        $stmt->execute(['school_id' => $schoolId]);
        $section = $stmt->fetch(PDO::FETCH_ASSOC);

        return $section ?: null;
    }

    /**
     * Assign student to section
     */
    public function assignStudentToSection(int $studentRecordId, int $sectionId): bool {
        $section = $this->getSectionById($sectionId);
        if (!$section) return false;

        $stmt = $this->db->prepare("
            UPDATE student_records 
            SET section_id = :section_id,
                section_name = :section_name
            WHERE id = :id
        ");
        $stmt->execute([
            'section_id'   => $sectionId,
            'section_name' => $section['section_name'],
            'id'           => $studentRecordId
        ]);

        // Sync count
        $this->db->prepare("
            UPDATE sections 
            SET current_count = (SELECT COUNT(*) FROM student_records WHERE section_id = :sid)
            WHERE id = :sid2
        ")->execute(['sid' => $sectionId, 'sid2' => $sectionId]);

        return true;
    }
}
