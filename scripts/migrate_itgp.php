<?php
define('BASE_PATH', '');
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

// Helper: only add column if not exists (for older MySQL)
function addColumnSafe(PDO $db, string $table, string $column, string $definition, string $after = ''): void {
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    if ((int)$stmt->fetchColumn() === 0) {
        $afterClause = $after ? " AFTER $after" : '';
        $db->exec("ALTER TABLE $table ADD COLUMN $column $definition$afterClause");
        echo "ADDED: $column" . PHP_EOL;
    } else {
        echo "EXISTS: $column" . PHP_EOL;
    }
}

addColumnSafe($db, 'itgp_records', 'sned_remarks', 'TEXT NULL', 'recommendations');
addColumnSafe($db, 'itgp_records', 'sned_reviewed_at', 'DATETIME NULL', 'sned_remarks');
addColumnSafe($db, 'itgp_records', 'sned_reviewed_by', 'INT NULL', 'sned_reviewed_at');
addColumnSafe($db, 'itgp_records', 'gen_teacher_revised', 'TINYINT(1) NOT NULL DEFAULT 0', 'sned_reviewed_by');
addColumnSafe($db, 'itgp_records', 'master_teacher_recommendations', 'TEXT NULL', 'gen_teacher_revised');
addColumnSafe($db, 'itgp_records', 'master_teacher_id', 'INT NULL', 'master_teacher_recommendations');
addColumnSafe($db, 'itgp_records', 'master_signature', 'LONGTEXT NULL', 'master_teacher_id');
addColumnSafe($db, 'itgp_records', 'inspected_at', 'DATETIME NULL', 'master_signature');

// Modify status ENUM
try {
    $db->exec("ALTER TABLE itgp_records MODIFY COLUMN status ENUM('draft','pending_sned_review','ready_for_inspection','inspected','finalized') NOT NULL DEFAULT 'draft'");
    echo "MODIFIED: status ENUM" . PHP_EOL;
} catch (Exception $e) {
    echo 'ERR status: ' . $e->getMessage() . PHP_EOL;
}

echo 'DONE' . PHP_EOL;
