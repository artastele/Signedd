-- Additive and idempotent activity submission prerequisites. Back up before applying.
-- Migration: v68 — Reliable Activity Attempt History
-- Preserves every learner activity attempt for Process 10.1 AI Learning Gap Identification
-- Keeps lms_submissions intact as latest-submission record.

CREATE TABLE IF NOT EXISTS `lms_activity_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `activity_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `submitted_by` INT NOT NULL,
    `attempt_number` INT NOT NULL DEFAULT 1,
    `score` INT NULL DEFAULT NULL,
    `max_score` INT NULL DEFAULT NULL,
    `accuracy_percentage` DECIMAL(5,2) NULL DEFAULT NULL,
    `answers` JSON NULL,
    `flashcard_results` JSON NULL,
    `is_remedial_attempt` TINYINT(1) NOT NULL DEFAULT 0,
    `needs_remediation` TINYINT(1) NOT NULL DEFAULT 0,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_lms_attempts_act_stud` (`activity_id`, `student_id`),
    INDEX `idx_lms_attempts_student` (`student_id`),
    CONSTRAINT `fk_lms_attempts_activity` FOREIGN KEY (`activity_id`) REFERENCES `lms_activities` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_attempts_student` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_attempts_user` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing item logs remain intact; historical rows have no linked attempt.
SET @signed_history_sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='activity_attempt_log' AND COLUMN_NAME='attempt_id') = 0,
    'ALTER TABLE activity_attempt_log ADD COLUMN attempt_id INT NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE signed_history_statement FROM @signed_history_sql;
EXECUTE signed_history_statement;
DEALLOCATE PREPARE signed_history_statement;

SET @signed_history_sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='activity_attempt_log' AND INDEX_NAME='idx_activity_attempt_id') = 0,
    'ALTER TABLE activity_attempt_log ADD INDEX idx_activity_attempt_id (attempt_id)',
    'SELECT 1'
);
PREPARE signed_history_statement FROM @signed_history_sql;
EXECUTE signed_history_statement;
DEALLOCATE PREPARE signed_history_statement;
