<?php
// Part of: SPED LMS — Teacher Assignment Model

require_once __DIR__ . '/../../config/db.php';

class TeacherAssignmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create or update teacher classroom & section assignment
     */
    public function assignTeacher($schoolId, $teacherId, $gradeLevel, $sectionName, $buildingName, $roomNumber, $optionalMessage, $assignedBy) {
        $stmt = $this->db->prepare("
            INSERT INTO teacher_assignments 
                (school_id, teacher_id, grade_level, section_name, building_name, room_number, optional_message, assigned_by)
            VALUES 
                (:school_id, :teacher_id, :grade_level, :section_name, :building_name, :room_number, :optional_message, :assigned_by)
            ON DUPLICATE KEY UPDATE
                grade_level = VALUES(grade_level),
                section_name = VALUES(section_name),
                building_name = VALUES(building_name),
                room_number = VALUES(room_number),
                optional_message = VALUES(optional_message),
                assigned_by = VALUES(assigned_by),
                updated_at = CURRENT_TIMESTAMP
        ");

        return $stmt->execute([
            'school_id'        => $schoolId,
            'teacher_id'       => $teacherId,
            'grade_level'      => $gradeLevel,
            'section_name'     => $sectionName,
            'building_name'    => $buildingName,
            'room_number'      => $roomNumber,
            'optional_message' => $optionalMessage,
            'assigned_by'      => $assignedBy
        ]);
    }

    /**
     * Get assignment by teacher ID
     */
    public function getByTeacherId($teacherId) {
        $stmt = $this->db->prepare("
            SELECT ta.*, s.school_name, s.school_id as school_code,
                   assigner.name as assigned_by_name
            FROM teacher_assignments ta
            LEFT JOIN schools s ON ta.school_id = s.id
            LEFT JOIN users assigner ON ta.assigned_by = assigner.id
            WHERE ta.teacher_id = :teacher_id
            LIMIT 1
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res && !empty($res['section_name'])) {
            return $res;
        }

        // Fallback to sections table if teacher is adviser of an active section
        $secStmt = $this->db->prepare("
            SELECT s.id as section_id, s.school_id, s.section_name, s.grade_level, s.room_number,
                   sch.school_name, sch.school_id as school_code
            FROM sections s
            LEFT JOIN schools sch ON s.school_id = sch.id
            WHERE s.adviser_teacher_id = :teacher_id AND (s.status = 'active' OR s.status IS NULL)
            ORDER BY s.id DESC
            LIMIT 1
        ");
        $secStmt->execute(['teacher_id' => $teacherId]);
        $sec = $secStmt->fetch(PDO::FETCH_ASSOC);
        if ($sec) {
            return [
                'teacher_id'       => (int)$teacherId,
                'school_id'        => $sec['school_id'] ?: 1,
                'grade_level'      => $sec['grade_level'] ?: 'SPED',
                'section_name'     => $sec['section_name'],
                'building_name'    => $res['building_name'] ?? 'Main Building',
                'room_number'      => $sec['room_number'] ?: ($res['room_number'] ?? ''),
                'optional_message' => $res['optional_message'] ?? '',
                'school_name'      => $sec['school_name'] ?? '',
                'school_code'      => $sec['school_code'] ?? '',
                'assigned_by_name' => 'School Administration'
            ];
        }

        return $res ?: false;
    }

    /**
     * Get all teacher assignments for a school indexed by teacher_id, merging with sections table
     */
    public function getBySchoolId($schoolId) {
        $assignmentsMap = [];

        // 1. Fetch from teacher_assignments
        $stmt = $this->db->prepare("
            SELECT ta.*, u.name as teacher_name, u.email as teacher_email, u.role as teacher_role
            FROM teacher_assignments ta
            JOIN users u ON ta.teacher_id = u.id
            WHERE ta.school_id = :school_id OR :school_id_null IS NULL
            ORDER BY ta.updated_at DESC
        ");
        $stmt->execute(['school_id' => $schoolId, 'school_id_null' => $schoolId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $assignmentsMap[$row['teacher_id']] = $row;
        }

        // 2. Fetch from sections where adviser_teacher_id is assigned
        $secStmt = $this->db->prepare("
            SELECT s.id as section_id, s.school_id, s.section_name, s.grade_level, s.room_number, s.adviser_teacher_id
            FROM sections s
            WHERE s.adviser_teacher_id IS NOT NULL 
              AND s.adviser_teacher_id > 0
              AND (s.school_id = :school_id OR :school_id_check IS NULL OR s.school_id IS NULL)
              AND (s.status = 'active' OR s.status IS NULL)
            ORDER BY s.id ASC
        ");
        $secStmt->execute(['school_id' => $schoolId, 'school_id_check' => $schoolId]);
        $secRows = $secStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($secRows as $sec) {
            $tid = (int)$sec['adviser_teacher_id'];
            if (!$tid) continue;

            if (!isset($assignmentsMap[$tid])) {
                $assignmentsMap[$tid] = [
                    'teacher_id'       => $tid,
                    'school_id'        => $sec['school_id'] ?: $schoolId,
                    'grade_level'      => $sec['grade_level'] ?: 'SPED',
                    'section_name'     => $sec['section_name'],
                    'building_name'    => '',
                    'room_number'      => $sec['room_number'] ?: '',
                    'optional_message' => '',
                ];
            } else {
                // If section was updated in sections management, sync the section_name and room
                if (!empty($sec['section_name'])) {
                    $assignmentsMap[$tid]['section_name'] = $sec['section_name'];
                    if (!empty($sec['grade_level'])) {
                        $assignmentsMap[$tid]['grade_level'] = $sec['grade_level'];
                    }
                }
                if (!empty($sec['room_number'])) {
                    $assignmentsMap[$tid]['room_number'] = $sec['room_number'];
                }
            }
        }

        return $assignmentsMap;
    }

    /**
     * Synchronize all section advisers to teacher_assignments
     */
    public function syncFromSections($schoolId = null) {
        $sql = "
            INSERT INTO teacher_assignments (school_id, teacher_id, grade_level, section_name, building_name, room_number, optional_message, assigned_by)
            SELECT 
                COALESCE(s.school_id, u.school_id, 1),
                s.adviser_teacher_id,
                COALESCE(NULLIF(s.grade_level, ''), 'SPED'),
                s.section_name,
                'Main Building',
                COALESCE(s.room_number, ''),
                '',
                s.adviser_teacher_id
            FROM sections s
            JOIN users u ON s.adviser_teacher_id = u.id
            WHERE s.adviser_teacher_id IS NOT NULL AND s.adviser_teacher_id > 0
            ON DUPLICATE KEY UPDATE
                grade_level = VALUES(grade_level),
                section_name = VALUES(section_name),
                room_number = CASE WHEN VALUES(room_number) != '' THEN VALUES(room_number) ELSE teacher_assignments.room_number END,
                school_id = VALUES(school_id)
        ";
        return $this->db->exec($sql);
    }

    /**
     * Delete assignment by ID
     */
    public function deleteAssignment($id, $schoolId) {
        $stmt = $this->db->prepare("DELETE FROM teacher_assignments WHERE id = :id AND school_id = :school_id");
        return $stmt->execute(['id' => $id, 'school_id' => $schoolId]);
    }
}
