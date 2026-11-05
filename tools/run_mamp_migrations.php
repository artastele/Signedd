<?php
// MAMP MySQL 8 compatible migration runner for SignED post-baseline migrations (v66-v69)
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/env.php';
if (!runtime_schema_changes_allowed()) {
    fwrite(STDERR, "Schema maintenance requires an approved backup and SIGNED_SCHEMA_MAINTENANCE=approved.\n");
    exit(1);
}
$host = env('DB_HOST', '127.0.0.1');
$port = env('DB_PORT', '8889');
$db   = env('DB_NAME', 'sped_lms');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Connected to MySQL 8 on MAMP (port $port)\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

function colExists(PDO $pdo, string $table, string $col): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
    $stmt->execute([$table, $col]);
    return (int)$stmt->fetchColumn() > 0;
}
function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}
function runSql(PDO $pdo, string $label, string $sql): void {
    try { $pdo->exec($sql); echo "  OK: $label\n"; }
    catch (PDOException $e) { echo "  WARN: $label -- " . $e->getMessage() . "\n"; }
}

echo "\n-- v66: student_records --\n";
if (!colExists($pdo,'student_records','status')) runSql($pdo,'Add status',"ALTER TABLE `student_records` ADD COLUMN `status` ENUM('active','mainstreamed') NOT NULL DEFAULT 'active'");
else echo "  SKIP: student_records.status exists\n";
if (!colExists($pdo,'student_records','claim_token')) runSql($pdo,'Add claim_token',"ALTER TABLE `student_records` ADD COLUMN `claim_token` VARCHAR(64) NULL DEFAULT NULL");
else echo "  SKIP: student_records.claim_token exists\n";

echo "\n-- v67: sms_logs --\n";
if (!tableExists($pdo,'sms_logs')) runSql($pdo,'Create sms_logs',"CREATE TABLE `sms_logs` (`id` INT AUTO_INCREMENT PRIMARY KEY,`recipient_phone` VARCHAR(30) NOT NULL,`recipient_name` VARCHAR(150) NULL,`message` TEXT NOT NULL,`notification_type` VARCHAR(50) DEFAULT 'general',`status` ENUM('sent','simulated','failed') DEFAULT 'simulated',`gateway_response` TEXT NULL,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX `idx_rp`(`recipient_phone`),INDEX `idx_st`(`status`),INDEX `idx_ca`(`created_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
else echo "  SKIP: sms_logs exists\n";

echo "\n-- v68: resource_hub --\n";
if (!tableExists($pdo,'resource_hub_packs')) runSql($pdo,'Create resource_hub_packs',"CREATE TABLE `resource_hub_packs` (`id` INT AUTO_INCREMENT PRIMARY KEY,`source_lesson_plan_id` INT NULL,`created_by` INT NOT NULL,`author_name` VARCHAR(255) NOT NULL,`title` VARCHAR(255) NOT NULL,`description` TEXT NULL,`pdsp_domain` ENUM('perceptuo_cognitive','psychosocial','socio_emotional','psychomotor','daily_living_skills','communication_language') NOT NULL DEFAULT 'communication_language',`learning_level` VARCHAR(100) NOT NULL DEFAULT 'Primary SPED / Deaf Ed',`learning_competency` TEXT NULL,`language` VARCHAR(100) NOT NULL DEFAULT 'Filipino / FSL',`fsl_supported` TINYINT(1) NOT NULL DEFAULT 1,`fsl_sign_count` INT NOT NULL DEFAULT 0,`page_count` INT NOT NULL DEFAULT 0,`activity_count` INT NOT NULL DEFAULT 0,`tags` VARCHAR(500) NULL,`mainstream_mastery_url` VARCHAR(1000) NULL,`mainstream_mastery_title` VARCHAR(255) NULL,`status` ENUM('published','archived') NOT NULL DEFAULT 'published',`clone_count` INT NOT NULL DEFAULT 0,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,INDEX `idx_domain`(`pdsp_domain`),INDEX `idx_st`(`status`),INDEX `idx_cb`(`created_by`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
else echo "  SKIP: resource_hub_packs exists\n";

if (!tableExists($pdo,'resource_hub_pages')) runSql($pdo,'Create resource_hub_pages',"CREATE TABLE `resource_hub_pages` (`id` INT AUTO_INCREMENT PRIMARY KEY,`pack_id` INT NOT NULL,`page_number` INT NOT NULL DEFAULT 1,`title` VARCHAR(255) NOT NULL,`content` LONGTEXT NULL,`guide_questions` MEDIUMTEXT NULL,`media_type` VARCHAR(50) NOT NULL DEFAULT 'none',`media_path` VARCHAR(500) NULL,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX `idx_pack`(`pack_id`,`page_number`),CONSTRAINT `fk_hp_pack` FOREIGN KEY(`pack_id`) REFERENCES `resource_hub_packs`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
else echo "  SKIP: resource_hub_pages exists\n";

if (!tableExists($pdo,'resource_hub_activities')) runSql($pdo,'Create resource_hub_activities',"CREATE TABLE `resource_hub_activities` (`id` INT AUTO_INCREMENT PRIMARY KEY,`pack_id` INT NOT NULL,`title` VARCHAR(255) NOT NULL,`instructions` LONGTEXT NULL,`activity_type` VARCHAR(50) NOT NULL,`activity_data` JSON NOT NULL,`max_score` INT NULL DEFAULT 0,`display_order` INT NOT NULL DEFAULT 0,`is_f2f` TINYINT(1) NOT NULL DEFAULT 0,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX `idx_pack`(`pack_id`),CONSTRAINT `fk_ha_pack` FOREIGN KEY(`pack_id`) REFERENCES `resource_hub_packs`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
else echo "  SKIP: resource_hub_activities exists\n";

if (!colExists($pdo,'lesson_plans','cloned_from_pack_id')) runSql($pdo,'Add lesson_plans.cloned_from_pack_id',"ALTER TABLE `lesson_plans` ADD COLUMN `cloned_from_pack_id` INT NULL DEFAULT NULL");
else echo "  SKIP: lesson_plans.cloned_from_pack_id exists\n";

echo "\n-- v69: external_mastery --\n";
if (!colExists($pdo,'lesson_plans','mainstream_mastery_url')) runSql($pdo,'Add lesson_plans.mainstream_mastery_url',"ALTER TABLE `lesson_plans` ADD COLUMN `mainstream_mastery_url` VARCHAR(1000) NULL DEFAULT NULL");
else echo "  SKIP: mainstream_mastery_url exists\n";
if (!colExists($pdo,'lesson_plans','mainstream_mastery_title')) runSql($pdo,'Add lesson_plans.mainstream_mastery_title',"ALTER TABLE `lesson_plans` ADD COLUMN `mainstream_mastery_title` VARCHAR(255) NULL DEFAULT NULL");
else echo "  SKIP: mainstream_mastery_title exists\n";

if (!tableExists($pdo,'external_mastery_results')) runSql($pdo,'Create external_mastery_results',"CREATE TABLE `external_mastery_results` (`id` INT AUTO_INCREMENT PRIMARY KEY,`lesson_plan_id` INT NOT NULL,`student_id` INT NOT NULL,`recorded_by` INT NOT NULL,`resource_title` VARCHAR(255) NULL,`resource_url` VARCHAR(1000) NULL,`mastery_status` ENUM('completed','needs_support','not_attempted') NOT NULL DEFAULT 'completed',`score` DECIMAL(5,2) NULL,`teacher_notes` TEXT NULL,`completed_at` DATE NULL,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY `idx_emr_ls`(`lesson_plan_id`,`student_id`),INDEX `idx_emr_st`(`student_id`),INDEX `idx_emr_rb`(`recorded_by`),CONSTRAINT `fk_emr_lp` FOREIGN KEY(`lesson_plan_id`) REFERENCES `lesson_plans`(`id`) ON DELETE CASCADE,CONSTRAINT `fk_emr_sr` FOREIGN KEY(`student_id`) REFERENCES `student_records`(`id`) ON DELETE CASCADE,CONSTRAINT `fk_emr_rb` FOREIGN KEY(`recorded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
else echo "  SKIP: external_mastery_results exists\n";

$count = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='sped_lms'")->fetchColumn();
echo "\nAll migrations complete! Total tables: $count\n";
