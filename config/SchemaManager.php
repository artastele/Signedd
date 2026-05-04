<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Schema Migration Manager

require_once __DIR__ . '/db.php';

class SchemaManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function applyMigrations() {
        try {
            // Read schema.sql
            $schemaPath = __DIR__ . '/schema.sql';
            if (!file_exists($schemaPath)) {
                throw new Exception("schema.sql not found");
            }

            $sql = file_get_contents($schemaPath);

            // Execute the entire schema (idempotent with IF NOT EXISTS)
            $this->db->exec($sql);

            return true;
        } catch (PDOException $e) {
            error_log("Schema migration failed: " . $e->getMessage());
            return false;
        }
    }

    public function getCurrentVersion() {
        try {
            $stmt = $this->db->query("SELECT MAX(version) as version FROM db_version");
            $result = $stmt->fetch();
            return $result['version'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function markVersionApplied($version) {
        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO db_version (version) VALUES (:version)");
            $stmt->execute(['version' => $version]);
            return true;
        } catch (PDOException $e) {
            error_log("Failed to mark version: " . $e->getMessage());
            return false;
        }
    }
}
