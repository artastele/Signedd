<?php
// Part of: SignED — Teacher FSL Training Model
// Last modified: 2026-08-27

require_once __DIR__ . '/../../config/db.php';

class TeacherFSLModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all training modules filtered by category or search
     */
    public function getModules(?string $category = null, string $search = ''): array {
        $sql = "SELECT * FROM teacher_fsl_modules WHERE 1=1";
        $params = [];

        if (!empty($category) && $category !== 'all') {
            $sql .= " AND category = :cat";
            $params['cat'] = $category;
        }

        if (!empty($search)) {
            $sql .= " AND (title LIKE :s OR description LIKE :s OR tips LIKE :s)";
            $params['s'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY display_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unique categories
     */
    public function getCategories(): array {
        $stmt = $this->db->query("SELECT DISTINCT category FROM teacher_fsl_modules WHERE category IS NOT NULL ORDER BY category ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get single module by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM teacher_fsl_modules WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }
}
