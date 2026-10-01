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
            JOIN schools s ON ta.school_id = s.id
            JOIN users assigner ON ta.assigned_by = assigner.id
            WHERE ta.teacher_id = :teacher_id
            LIMIT 1
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all teacher assignments for a school indexed by teacher_id
     */
    public function getBySchoolId($schoolId) {
        $stmt = $this->db->prepare("
            SELECT ta.*, u.name as teacher_name, u.email as teacher_email, u.role as teacher_role
            FROM teacher_assignments ta
            JOIN users u ON ta.teacher_id = u.id
            WHERE ta.school_id = :school_id
            ORDER BY ta.updated_at DESC
        ");
        $stmt->execute(['school_id' => $schoolId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $assignmentsMap = [];
        foreach ($rows as $row) {
            $assignmentsMap[$row['teacher_id']] = $row;
        }
        return $assignmentsMap;
    }

    /**
     * Delete assignment by ID
     */
    public function deleteAssignment($id, $schoolId) {
        $stmt = $this->db->prepare("DELETE FROM teacher_assignments WHERE id = :id AND school_id = :school_id");
        return $stmt->execute(['id' => $id, 'school_id' => $schoolId]);
    }
}
