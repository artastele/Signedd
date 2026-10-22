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
                // If schema.sql is not present on production server, assume schema is pre-imported
                return true;
            }


            $sql = file_get_contents($schemaPath);

            // Strip UTF-8 BOM if present (prevents MySQL syntax error)
            if (substr($sql, 0, 3) === "\xEF\xBB\xBF") {
                $sql = substr($sql, 3);
            }

            // Extract and apply only migrations newer than current version
            $currentVersion = $this->getCurrentVersion();

            // Find all migration blocks: -- MIGRATION: vN ... -- END MIGRATION: vN
            preg_match_all(
                '/--\s*MIGRATION:\s*v(\d+).*?--\s*END MIGRATION:\s*v\1/s',
                $sql,
                $matches,
                PREG_SET_ORDER
            );

            if (empty($matches)) {
                // Fallback: run entire schema (idempotent with IF NOT EXISTS)
                $this->db->exec($sql);
                return true;
            }

            foreach ($matches as $match) {
                $version = (int)$match[1];
                if ($version > $currentVersion) {
                    $statements = array_filter(array_map('trim', explode(';', $match[0])));
                    foreach ($statements as $stmt_sql) {
                        $cleanSql = trim(preg_replace('/^\s*--[^\r\n]*/m', '', $stmt_sql));
                        if (!empty($cleanSql)) {
                            try {
                                $this->db->exec($cleanSql);
                            } catch (PDOException $e) {
                                error_log("Migration v{$version} statement warning: " . $e->getMessage());
                            }
                        }
                    }
                    $this->markVersionApplied($version);
                    error_log("SchemaManager: applied migration v{$version}");
                }
            }

            // Also check for standalone migration files in config/ (e.g. v67_resource_hub_packs.sql, v68_external_mastery.sql)
            $migrationFiles = glob(__DIR__ . '/v[0-9]*_*.sql');
            if (!empty($migrationFiles)) {
                sort($migrationFiles);
                foreach ($migrationFiles as $file) {
                    if (preg_match('/v(\d+)_/i', basename($file), $fileMatch)) {
                        $fileVersion = (int)$fileMatch[1];
                        if ($fileVersion > $currentVersion) {
                            $fileSql = file_get_contents($file);
                            if (substr($fileSql, 0, 3) === "\xEF\xBB\xBF") {
                                $fileSql = substr($fileSql, 3);
                            }
                            $statements = array_filter(array_map('trim', explode(';', $fileSql)));
                            foreach ($statements as $stmt_sql) {
                                $cleanSql = trim(preg_replace('/^\s*--[^\r\n]*/m', '', $stmt_sql));
                                if (!empty($cleanSql)) {
                                    try {
                                        $this->db->exec($cleanSql);
                                    } catch (PDOException $e) {
                                        error_log("Standalone migration file {$file} warning: " . $e->getMessage());
                                    }
                                }
                            }
                            $this->markVersionApplied($fileVersion);
                            error_log("SchemaManager: applied standalone migration v{$fileVersion} (" . basename($file) . ")");
                        }
                    }
                }
            }

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
