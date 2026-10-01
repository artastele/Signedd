-- Stage 2: Dual-Track, Sections, LIS 2-Way Sync, and Universal Profile Migration
-- File: config/v58_stage2_dual_track_sections_profiles.sql

-- 1. Create Sections Table for Principal Management
CREATE TABLE IF NOT EXISTS `sections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT NULL,
    `section_name` VARCHAR(100) NOT NULL,
    `grade_level` VARCHAR(50) NOT NULL DEFAULT 'SPED',
    `room_number` VARCHAR(50) NULL,
    `max_capacity` INT NOT NULL DEFAULT 15,
    `current_count` INT NOT NULL DEFAULT 0,
    `adviser_teacher_id` INT NULL,
    `school_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
    `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`school_id`),
    INDEX (`adviser_teacher_id`),
    INDEX (`grade_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Update student_records with Section ID, Learning Track, and Learner User Link
ALTER TABLE `student_records` 
    ADD COLUMN IF NOT EXISTS `section_id` INT NULL AFTER `section_name`,
    ADD COLUMN IF NOT EXISTS `learning_track` ENUM('unassigned', 'lms', 'traditional') NOT NULL DEFAULT 'unassigned' AFTER `section_id`,
    ADD COLUMN IF NOT EXISTS `lms_invite_status` ENUM('none', 'sent', 'accepted', 'declined') NOT NULL DEFAULT 'none' AFTER `learning_track`,
    ADD COLUMN IF NOT EXISTS `lms_invited_at` DATETIME NULL AFTER `lms_invite_status`,
    ADD COLUMN IF NOT EXISTS `lms_accepted_at` DATETIME NULL AFTER `lms_invited_at`,
    ADD COLUMN IF NOT EXISTS `learner_user_id` INT NULL AFTER `lms_accepted_at`;

-- 3. Update enrollment_submissions with Survey responses, Track, Section, and LMS Invite fields
ALTER TABLE `enrollment_submissions`
    ADD COLUMN IF NOT EXISTS `survey_has_internet` TINYINT(1) NULL DEFAULT 0 AFTER `preferred_distance_modality`,
    ADD COLUMN IF NOT EXISTS `survey_devices` VARCHAR(255) NULL AFTER `survey_has_internet`,
    ADD COLUMN IF NOT EXISTS `survey_willing_online` TINYINT(1) NULL DEFAULT 0 AFTER `survey_devices`,
    ADD COLUMN IF NOT EXISTS `learning_track` ENUM('unassigned', 'lms', 'traditional') NOT NULL DEFAULT 'unassigned' AFTER `survey_willing_online`,
    ADD COLUMN IF NOT EXISTS `section_id` INT NULL AFTER `learning_track`,
    ADD COLUMN IF NOT EXISTS `lms_invite_status` ENUM('none', 'sent', 'accepted', 'declined') NOT NULL DEFAULT 'none' AFTER `section_id`,
    ADD COLUMN IF NOT EXISTS `learner_user_id` INT NULL AFTER `lms_invite_status`;

-- 4. Update users table with profile fields if missing
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `profile_photo` VARCHAR(255) NULL AFTER `role`,
    ADD COLUMN IF NOT EXISTS `phone_number` VARCHAR(50) NULL AFTER `profile_photo`,
    ADD COLUMN IF NOT EXISTS `bio` TEXT NULL AFTER `phone_number`;

-- 5. Create traditional_iep_documents table for Traditional SEN F2F Track Record Keeping
CREATE TABLE IF NOT EXISTS `traditional_iep_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `uploaded_by` INT NOT NULL,
    `document_type` ENUM('dll', 'physical_iep', 'assessment_report', 'progress_note', 'other') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT NULL,
    `school_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
    `quarter` VARCHAR(20) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`student_id`),
    INDEX (`uploaded_by`),
    INDEX (`document_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
