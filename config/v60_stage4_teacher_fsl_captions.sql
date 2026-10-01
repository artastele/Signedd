-- Stage 4: Teacher FSL Training Module & Video Lesson Captions
-- File: config/v60_stage4_teacher_fsl_captions.sql

-- 1. Create teacher_fsl_modules table
CREATE TABLE IF NOT EXISTS `teacher_fsl_modules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Classroom Commands',
    `video_path` VARCHAR(255) NULL,
    `gif_path` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `tips` TEXT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`category`),
    INDEX (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
