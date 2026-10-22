-- SignED Migration v67: Accessibility Pack Resource Hub
-- Isolated template tables ensuring strict separation of reusable educational content
-- from learner-specific IEP records, PII, and assessment history.

CREATE TABLE IF NOT EXISTS `resource_hub_packs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `source_lesson_plan_id` INT NULL,
    `created_by` INT NOT NULL,
    `author_name` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `pdsp_domain` ENUM('perceptuo_cognitive','psychosocial','socio_emotional','psychomotor','daily_living_skills','communication_language') NOT NULL DEFAULT 'communication_language',
    `learning_level` VARCHAR(100) NOT NULL DEFAULT 'Primary SPED / Deaf Ed',
    `learning_competency` TEXT NULL,
    `language` VARCHAR(100) NOT NULL DEFAULT 'Filipino / FSL',
    `fsl_supported` TINYINT(1) NOT NULL DEFAULT 1,
    `fsl_sign_count` INT NOT NULL DEFAULT 0,
    `page_count` INT NOT NULL DEFAULT 0,
    `activity_count` INT NOT NULL DEFAULT 0,
    `tags` VARCHAR(500) NULL,
    `mainstream_mastery_url` VARCHAR(1000) NULL,
    `mainstream_mastery_title` VARCHAR(255) NULL,
    `status` ENUM('published','archived') NOT NULL DEFAULT 'published',
    `clone_count` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hub_domain` (`pdsp_domain`),
    INDEX `idx_hub_status` (`status`),
    INDEX `idx_hub_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `resource_hub_pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pack_id` INT NOT NULL,
    `page_number` INT NOT NULL DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NULL,
    `guide_questions` MEDIUMTEXT NULL,
    `media_type` VARCHAR(50) NOT NULL DEFAULT 'none',
    `media_path` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_hub_pages_pack` (`pack_id`, `page_number`),
    CONSTRAINT `fk_hub_pages_pack` FOREIGN KEY (`pack_id`) REFERENCES `resource_hub_packs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `resource_hub_activities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pack_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `instructions` LONGTEXT NULL,
    `activity_type` VARCHAR(50) NOT NULL,
    `activity_data` JSON NOT NULL,
    `max_score` INT NULL DEFAULT 0,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_f2f` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_hub_activities_pack` (`pack_id`),
    CONSTRAINT `fk_hub_activities_pack` FOREIGN KEY (`pack_id`) REFERENCES `resource_hub_packs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Provenance traceability column on lesson_plans (additive only)
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'lesson_plans' 
      AND COLUMN_NAME = 'cloned_from_pack_id'
);

SET @stmt = IF(@col_exists = 0, 
    'ALTER TABLE `lesson_plans` ADD COLUMN `cloned_from_pack_id` INT NULL DEFAULT NULL AFTER `assignment_type`', 
    'SELECT "Column cloned_from_pack_id already exists"'
);
PREPARE add_col FROM @stmt;
EXECUTE add_col;
DEALLOCATE PREPARE add_col;
