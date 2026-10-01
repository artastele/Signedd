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
     * Fetch all approved/verified registered schools (excludes pending principal applications)
     */
    public function getAllSchools() {
        $stmt = $this->db->query("
            SELECT DISTINCT s.* 
            FROM schools s
            JOIN users u ON u.school_id = s.id
            LEFT JOIN role_requests rr ON rr.user_id = u.id AND rr.requested_role = 'principal'
            WHERE u.role = 'principal' OR rr.status = 'approved'
            ORDER BY s.school_name ASC
        ");
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

    /**
     * Update school-specific enrollment guidelines and schedule
     */
    public function updateGuidelines($schoolId, $data) {
        $stmt = $this->db->prepare("
            UPDATE schools SET
                enrollment_sy = :sy,
                enrollment_status = :status,
                enrollment_start_date = :start_date,
                enrollment_end_date = :end_date,
                enrollment_guidelines = :guidelines,
                contact_email = :contact_email,
                contact_number = :contact_number,
                facebook_page = :facebook_page,
                guidelines_published = 1
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'             => $schoolId,
            'sy'             => $data['enrollment_sy'] ?? '2026-2027',
            'status'         => $data['enrollment_status'] ?? 'open',
            'start_date'     => !empty($data['enrollment_start_date']) ? $data['enrollment_start_date'] : null,
            'end_date'       => !empty($data['enrollment_end_date']) ? $data['enrollment_end_date'] : null,
            'guidelines'     => $data['enrollment_guidelines'] ?? null,
            'contact_email'  => !empty($data['contact_email']) ? $data['contact_email'] : null,
            'contact_number' => !empty($data['contact_number']) ? $data['contact_number'] : null,
            'facebook_page'  => !empty($data['facebook_page']) ? $data['facebook_page'] : null
        ]);
    }

    /**
     * Update pubmat poster file path
     */
    public function updatePubmat($schoolId, $pubmatPath) {
        $stmt = $this->db->prepare("UPDATE schools SET pubmat_path = :pubmat_path WHERE id = :id");
        return $stmt->execute([
            'id'          => $schoolId,
            'pubmat_path' => $pubmatPath
        ]);
    }

    /**
     * Update custom logo file path
     */
    public function updateLogo($schoolId, $logoPath) {
        $stmt = $this->db->prepare("UPDATE schools SET logo_path = :logo_path WHERE id = :id");
        return $stmt->execute([
            'id'        => $schoolId,
            'logo_path' => $logoPath
        ]);
    }

    /**
     * Update School Improvement Plan (SIP) PDF document file path
     */
    public function updateSipPath($schoolId, $sipPath) {
        $stmt = $this->db->prepare("UPDATE schools SET sip_path = :sip_path WHERE id = :id");
        return $stmt->execute([
            'id'       => $schoolId,
            'sip_path' => $sipPath
        ]);
    }

    /**
     * Hybrid Logo Resolver: Returns uploaded logo URL if available, else generates School Initials SVG Badge
     */
    public static function getSchoolLogoUrl($school, $basePath = '') {
        if (!empty($school['logo_path'])) {
            $relPath = ltrim($school['logo_path'], '/');
            $fullFilePath = function_exists('public_path') ? public_path($relPath) : (__DIR__ . '/../../public/' . $relPath);
            if (file_exists($fullFilePath)) {
                return $basePath . '/' . $relPath;
            }
        }

        // Dynamic SVG Seal Badge using School Initials & DepEd Navy/Gold Colors
        $name = $school['school_name'] ?? 'SPED Center';
        $words = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w) && !in_array(strtolower($w), ['of', 'the', 'and', 'in', 'at'])) {
                $initials .= strtoupper(substr($w, 0, 1));
            }
        }
        $initials = substr($initials, 0, 4) ?: 'SPED';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">' .
               '<circle cx="60" cy="60" r="58" fill="#1e4072" stroke="#f5b301" stroke-width="4"/>' .
               '<circle cx="60" cy="60" r="48" fill="none" stroke="#ffffff" stroke-width="1.5" stroke-dasharray="4 2"/>' .
               '<text x="60" y="67" font-family="Arial, sans-serif" font-size="22" font-weight="bold" fill="#f5b301" text-anchor="middle" letter-spacing="1">' . htmlspecialchars($initials) . '</text>' .
               '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

}
