<?php
// Part of: SPED LMS — School Management Model

require_once __DIR__ . '/../../config/db.php';

class SchoolModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create or register a new school
     */
    public function createSchool($schoolIdCode, $schoolName, $division = null, $region = null, $address = null) {
        $stmt = $this->db->prepare("
            INSERT INTO schools (school_id, school_name, division, region, address)
            VALUES (:school_id, :school_name, :division, :region, :address)
        ");
        $stmt->execute([
            'school_id'   => trim($schoolIdCode),
            'school_name' => trim($schoolName),
            'division'    => $division ? trim($division) : null,
            'region'      => $region ? trim($region) : null,
            'address'     => $address ? trim($address) : null,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Fetch all registered schools
     */
    public function getAllSchools() {
        $stmt = $this->db->query("SELECT * FROM schools ORDER BY school_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find school by primary key ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM schools WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find school by DepEd School ID code (e.g. '104821')
     */
    public function findBySchoolCode($schoolIdCode) {
        $stmt = $this->db->prepare("SELECT * FROM schools WHERE school_id = :school_id LIMIT 1");
        $stmt->execute(['school_id' => trim($schoolIdCode)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
