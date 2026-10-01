-- SignED Migration v57 — Masterlist & LIS Sync (SF1/SF2 Support)
-- Idempotent schema migration for MariaDB / MySQL 8

-- Add LIS & Masterlist tracking columns to student_records
SET @dbname = DATABASE();

-- student_records: add section_name, track_strand, lis_status, lis_synced_at
SET @tablename = "student_records";
SET @columnname = "section_name";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(100) NULL AFTER grade_level_to_enroll;")
));
-- Exec column check safely
ALTER TABLE student_records ADD COLUMN IF NOT EXISTS section_name VARCHAR(100) NULL;
ALTER TABLE student_records ADD COLUMN IF NOT EXISTS track_strand VARCHAR(100) NULL;
ALTER TABLE student_records ADD COLUMN IF NOT EXISTS lis_status ENUM('pending', 'synced', 'error') DEFAULT 'pending';
ALTER TABLE student_records ADD COLUMN IF NOT EXISTS lis_synced_at DATETIME NULL;

-- Create LIS sync audit table
CREATE TABLE IF NOT EXISTS lis_sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sync_type ENUM('sf1_export', 'sf2_export', 'sf2_import') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    records_count INT DEFAULT 0,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sync_type (sync_type),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
