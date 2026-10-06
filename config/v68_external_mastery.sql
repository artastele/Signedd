-- SignED Migration v68: Mainstream Mastery Handoff & Manual Result Logging
-- Adds mainstream mastery resource fields to lesson_plans and creates the dedicated
-- external_mastery_results table for platform-neutral manual evidence tracking.

-- 1. Add mainstream mastery columns to lesson_plans if they do not exist
SET @col1_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'lesson_plans' 
      AND COLUMN_NAME = 'mainstream_mastery_url'
);

SET @stmt1 = IF(@col1_exists = 0, 
    'ALTER TABLE `lesson_plans` ADD COLUMN `mainstream_mastery_url` VARCHAR(1000) NULL DEFAULT NULL AFTER `cloned_from_pack_id`', 
    'SELECT "Column mainstream_mastery_url already exists"'
);
PREPARE add_col1 FROM @stmt1;
EXECUTE add_col1;
DEALLOCATE PREPARE add_col1;

SET @col2_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'lesson_plans' 
      AND COLUMN_NAME = 'mainstream_mastery_title'
);

SET @stmt2 = IF(@col2_exists = 0, 
    'ALTER TABLE `lesson_plans` ADD COLUMN `mainstream_mastery_title` VARCHAR(255) NULL DEFAULT NULL AFTER `mainstream_mastery_url`', 
    'SELECT "Column mainstream_mastery_title already exists"'
);
PREPARE add_col2 FROM @stmt2;
EXECUTE add_col2;
DEALLOCATE PREPARE add_col2;

-- 2. Create external_mastery_results table
CREATE TABLE IF NOT EXISTS `external_mastery_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lesson_plan_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `recorded_by` INT NOT NULL,
    `resource_title` VARCHAR(255) NULL,
    `resource_url` VARCHAR(1000) NULL,
    `mastery_status` ENUM('completed', 'needs_support', 'not_attempted') NOT NULL DEFAULT 'completed',
    `score` DECIMAL(5,2) NULL,
    `teacher_notes` TEXT NULL,
    `completed_at` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_emr_lesson_student` (`lesson_plan_id`, `student_id`),
    INDEX `idx_emr_student` (`student_id`),
    INDEX `idx_emr_recorded_by` (`recorded_by`),
    CONSTRAINT `fk_emr_lesson_plan` FOREIGN KEY (`lesson_plan_id`) REFERENCES `lesson_plans` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_emr_student` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_emr_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
