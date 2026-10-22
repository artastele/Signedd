-- MIGRATION: v66 - Add status column to student_records for Class Placement workflow
ALTER TABLE `student_records` ADD COLUMN IF NOT EXISTS `status` ENUM('active','mainstreamed') NOT NULL DEFAULT 'active';

INSERT IGNORE INTO db_version (version) VALUES (66);
