<?php
// Part of: SignED — Production Database Backup & Export Controller
// Last modified: 2026-08-27

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../config/db.php';

class BackupController {
    private int $userId;
    private string $userRole;
    private string $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $this->userId = (int)$_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';

        // Only Admin and Principal allowed
        if (!in_array($this->userRole, ['admin', 'principal'])) {
            $_SESSION['error'] = 'Unauthorized access to database backup.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }
    }

    /**
     * Export full database SQL dump
     */
    public function exportSql(): void {
        try {
            $db = Database::getInstance()->getConnection();
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            $sqlDump = "-- SignED Database Snapshot\n";
            $sqlDump .= "-- Exported on: " . date('Y-m-d H:i:s') . "\n";
            $sqlDump .= "-- Exported by: User ID {$this->userId} ({$this->userRole})\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Table structure
                $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sqlDump .= $createTable['Create Table'] . ";\n\n";

                // Table rows
                $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));
                        $vals = array_map(function($v) use ($db) {
                            return $v === null ? "NULL" : $db->quote($v);
                        }, array_values($row));

                        $sqlDump .= "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ");\n";
                    }
                    $sqlDump .= "\n";
                }
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $filename = 'signed_backup_' . date('Y_m_d_His') . '.sql';

            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($sqlDump));
            echo $sqlDump;
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Backup failed: ' . $e->getMessage();
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }
    }
}
