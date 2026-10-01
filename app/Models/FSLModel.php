<?php
// Part of: SignED — FSL (Filipino Sign Language) Vocabulary Model
// Last modified: 2026-08-27

require_once __DIR__ . '/../../config/db.php';

class FSLModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Look up word in FSL Vocabulary
     */
    public function lookup(string $word): ?array {
        $cleanWord = trim(strtolower($word));
        $stmt = $this->db->prepare("
            SELECT * FROM fsl_vocabulary 
            WHERE LOWER(word) = :word 
            LIMIT 1
        ");
        $stmt->execute(['word' => $cleanWord]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Partial match fallback by word
            $stmtLike = $this->db->prepare("
                SELECT * FROM fsl_vocabulary 
                WHERE LOWER(word) LIKE :word_like 
                ORDER BY (CASE WHEN video_path IS NOT NULL AND TRIM(video_path) != '' THEN 0 ELSE 1 END) ASC
                LIMIT 1
            ");
            $stmtLike->execute(['word_like' => '%' . $cleanWord . '%']);
            $row = $stmtLike->fetch(PDO::FETCH_ASSOC);
        }

        if (!$row) {
            // Translation fallback: check Tagalog keywords in description
            $stmtDesc = $this->db->prepare("
                SELECT * FROM fsl_vocabulary 
                WHERE LOWER(description) LIKE :desc_like
                ORDER BY (CASE WHEN video_path IS NOT NULL AND TRIM(video_path) != '' THEN 0 ELSE 1 END) ASC
                LIMIT 1
            ");
            $stmtDesc->execute(['desc_like' => '%' . $cleanWord . '%']);
            $row = $stmtDesc->fetch(PDO::FETCH_ASSOC);
        }

        return $row ?: null;
    }

    /**
     * Get all FSL dictionary items grouped or filtered by category
     */
    public function getAll(?string $category = null, string $search = '', bool $onlyWithVideo = false): array {
        $sql = "SELECT * FROM fsl_vocabulary WHERE 1=1";
        $params = [];

        if ($onlyWithVideo) {
            $sql .= " AND video_path IS NOT NULL AND TRIM(video_path) != ''";
        }

        if (!empty($category) && $category !== 'all') {
            $sql .= " AND category = :cat";
            $params['cat'] = $category;
        }

        if (!empty($search)) {
            $sql .= " AND (word LIKE :search OR description LIKE :search)";
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY word ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single FSL sign by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM fsl_vocabulary WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all unique categories
     */
    public function getCategories(bool $onlyWithVideo = false): array {
        $sql = "SELECT DISTINCT category FROM fsl_vocabulary WHERE category IS NOT NULL";
        if ($onlyWithVideo) {
            $sql .= " AND video_path IS NOT NULL AND TRIM(video_path) != ''";
        }
        $sql .= " ORDER BY category ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    /**
     * Add new FSL word
     */
    public function addWord(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO fsl_vocabulary (word, category, video_path, gif_path, description)
            VALUES (:word, :category, :video_path, :gif_path, :description)
        ");
        $stmt->execute([
            'word'        => trim(strtolower($data['word'])),
            'category'    => $data['category'] ?? 'General',
            'video_path'  => $data['video_path'] ?? null,
            'gif_path'    => $data['gif_path'] ?? null,
            'description' => $data['description'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }
}
