-- SPED LMS — Database Schema (Clean Version)
-- DO NOT ALTER WITHOUT APPROVAL
-- All tables created with proper constraints and defaults

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- MIGRATION TRACKING
-- ============================================
CREATE TABLE IF NOT EXISTS db_version (
    version INT PRIMARY KEY,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- SECURITY MODULE 1 & 2 — Authentication & Authorization
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    suffix VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    contact_number VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'parent', 'sped_teacher', 'guidance', 'principal', 'master_teacher', 'learner', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(10),
    email_verification_expires DATETIME,
    verification_attempts INT DEFAULT 0,
    google_id VARCHAR(255) UNIQUE,
    profile_picture VARCHAR(255),
    auth_provider ENUM('local', 'google') DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role ENUM('sped_teacher', 'guidance', 'principal', 'master_teacher') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approver_role ENUM('admin', 'principal') DEFAULT 'principal',
    submitted_docs JSON,
    reviewed_by INT,
    review_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_approver_role (approver_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_request_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_request_id) REFERENCES role_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 1 — Parent Complying Enrollment Requirements
-- ============================================

CREATE TABLE IF NOT EXISTS enrollment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    enrollment_type ENUM('new', 'transfer', 'returning') NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    previous_enrollment_id INT NULL,
    is_draft BOOLEAN DEFAULT TRUE,
    status ENUM('draft', 'pending', 'verified', 'rejected') DEFAULT 'draft',
    lrn VARCHAR(12),
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    extension_name VARCHAR(20),
    birth_date DATE NOT NULL,
    sex ENUM('Male', 'Female') NOT NULL,
    age INT,
    birth_place VARCHAR(255),
    mother_tongue VARCHAR(100),
    is_indigenous_people BOOLEAN DEFAULT FALSE,
    indigenous_group VARCHAR(100),
    is_4ps_beneficiary BOOLEAN DEFAULT FALSE,
    fourps_household_id VARCHAR(50),
    disability_visual BOOLEAN DEFAULT FALSE,
    disability_hearing BOOLEAN DEFAULT FALSE,
    disability_learning BOOLEAN DEFAULT FALSE,
    disability_speech BOOLEAN DEFAULT FALSE,
    disability_intellectual BOOLEAN DEFAULT FALSE,
    disability_physical BOOLEAN DEFAULT FALSE,
    disability_emotional BOOLEAN DEFAULT FALSE,
    disability_chronic_illness BOOLEAN DEFAULT FALSE,
    disability_others BOOLEAN DEFAULT FALSE,
    disability_others_specify VARCHAR(255),
    current_house_no VARCHAR(255),
    current_barangay VARCHAR(100),
    current_city VARCHAR(100),
    current_province VARCHAR(100),
    current_zip_code VARCHAR(10),
    same_as_current_address BOOLEAN DEFAULT FALSE,
    permanent_house_no VARCHAR(255),
    permanent_barangay VARCHAR(100),
    permanent_city VARCHAR(100),
    permanent_province VARCHAR(100),
    permanent_zip_code VARCHAR(10),
    father_last_name VARCHAR(100),
    father_first_name VARCHAR(100),
    father_middle_name VARCHAR(100),
    father_contact_number VARCHAR(20),
    mother_maiden_last_name VARCHAR(100),
    mother_first_name VARCHAR(100),
    mother_middle_name VARCHAR(100),
    mother_contact_number VARCHAR(20),
    guardian_last_name VARCHAR(100),
    guardian_first_name VARCHAR(100),
    guardian_middle_name VARCHAR(100),
    guardian_contact_number VARCHAR(20),
    previous_school_id VARCHAR(50),
    previous_school_name VARCHAR(255),
    previous_school_address TEXT,
    previous_grade_level VARCHAR(50),
    previous_school_year VARCHAR(20),
    previous_school_type ENUM('Public', 'Private'),
    grade_level_to_enroll VARCHAR(50) NOT NULL,
    is_balik_aral BOOLEAN DEFAULT FALSE,
    is_pept_passer BOOLEAN DEFAULT FALSE,
    pept_rating VARCHAR(20),
    is_als_passer BOOLEAN DEFAULT FALSE,
    als_rating VARCHAR(20),
    shs_track VARCHAR(50),
    shs_strand VARCHAR(100),
    shs_semester ENUM('1st', '2nd'),
    modality_modular_print BOOLEAN DEFAULT FALSE,
    modality_modular_digital BOOLEAN DEFAULT FALSE,
    modality_online BOOLEAN DEFAULT FALSE,
    modality_educational_tv BOOLEAN DEFAULT FALSE,
    modality_radio BOOLEAN DEFAULT FALSE,
    modality_blended BOOLEAN DEFAULT FALSE,
    modality_face_to_face BOOLEAN DEFAULT FALSE,
    preferred_distance_modality VARCHAR(50),
    signature_data TEXT,
    date_signed DATE,
    draft_saved_at TIMESTAMP NULL,
    submitted_at TIMESTAMP NULL,
    verified_by INT,
    verified_at TIMESTAMP NULL,
    learner_account_created BOOLEAN DEFAULT FALSE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (previous_enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_parent_id (parent_id),
    INDEX idx_enrollment_type (enrollment_type),
    INDEX idx_school_year (school_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enrollment_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    document_type ENUM('psa_birth_cert', 'pwd_id', 'medical_record', 'beef_form') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by INT,
    review_note TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_enrollment_id (enrollment_id),
    INDEX idx_status (status),
    INDEX idx_document_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 2 — Verifying Enrollment Requirements
-- ============================================

CREATE TABLE IF NOT EXISTS student_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    lrn VARCHAR(12) UNIQUE,
    student_name VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    disability_type VARCHAR(255),
    psa_number VARCHAR(100),
    pwd_id_number VARCHAR(100),
    verified_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_name (student_name),
    INDEX idx_lrn (lrn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS education_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    previous_school VARCHAR(255),
    grade_level VARCHAR(50),
    year_attended VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 3 — Conducting Initial Assessment
-- ============================================

CREATE TABLE IF NOT EXISTS assessment_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    parent_id INT,
    assessed_by INT NOT NULL,
    assessment_type VARCHAR(100),
    assessment_data JSON,
    submitted_data JSON,
    education_history JSON,
    assessment_info JSON,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by INT,
    review_note TEXT,
    quarter VARCHAR(2),
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student_id (student_id),
    INDEX idx_version (version),
    INDEX idx_assessment_status (status),
    INDEX idx_assessment_quarter (quarter),
    INDEX idx_assessment_submitted (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 4 — Facilitating IEP Meeting
-- ============================================

CREATE TABLE IF NOT EXISTS iep_meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assessment_id INT NOT NULL,
    scheduled_by INT,
    meeting_date DATETIME NOT NULL,
    meeting_location VARCHAR(255),
    agenda TEXT,
    guidance_id INT,
    principal_id INT,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    FOREIGN KEY (scheduled_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guidance_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (principal_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_meeting_date (meeting_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 4 — IEP Meeting Calendar Availability
-- ============================================

CREATE TABLE IF NOT EXISTS iep_meeting_calendars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    calendar_file_path VARCHAR(500) NOT NULL,
    availability_data JSON,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valid_from DATE,
    valid_until DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_uploaded_at (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 4 — IEP P2 Documents
-- ============================================

CREATE TABLE IF NOT EXISTS iep_p2_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSON NOT NULL,
    pdf_path VARCHAR(500),
    status ENUM('draft', 'pending_review', 'reviewed_signed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_p2_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_p2_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewer_role ENUM('guidance', 'principal', 'parent', 'sped_teacher', 'school_head', 'ilrc_supervisor') NOT NULL,
    feedback TEXT,
    signature_data TEXT,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_p2_id) REFERENCES iep_p2_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reviewer_per_p2 (iep_p2_id, reviewer_id),
    INDEX idx_reviewer_role (reviewer_role),
    INDEX idx_reviewed_at (reviewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 5 — Generating IEP
-- ============================================

CREATE TABLE IF NOT EXISTS iep_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_content JSON NOT NULL,
    status ENUM('draft', 'pending_signatures', 'signed', 'locked') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    locked_at TIMESTAMP NULL,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    signer_id INT NOT NULL,
    signer_role ENUM('guidance', 'principal') NOT NULL,
    signature_data TEXT,
    remarks TEXT,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (signer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_signer_per_iep (iep_id, signer_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_p3_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSON NOT NULL,
    pdf_path VARCHAR(500),
    status ENUM('draft', 'pending_signatures', 'signed_approved') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_p3_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_p3_id INT NOT NULL,
    signer_id INT NOT NULL,
    signer_role ENUM('parent', 'guidance', 'teacher', 'sped_teacher', 'principal', 'school_head', 'ilrc_supervisor') NOT NULL,
    signature_data TEXT,
    remarks TEXT,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_p3_id) REFERENCES iep_p3_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (signer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_signer_per_p3 (iep_p3_id, signer_role),
    INDEX idx_signer_role (signer_role),
    INDEX idx_signed_at (signed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('p2', 'p3') NOT NULL,
    document_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document (document_type, document_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 6 — Implementing IEP
-- ============================================

CREATE TABLE IF NOT EXISTS learner_iep (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    iep_id INT NOT NULL,
    teacher_id INT NOT NULL,
    implementation_status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    start_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_id) REFERENCES iep_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_status (implementation_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learning_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    learner_iep_id INT NOT NULL,
    material_name VARCHAR(255) NOT NULL,
    material_type VARCHAR(100),
    file_path VARCHAR(500),
    description TEXT,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_iep_id) REFERENCES learner_iep(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 7 — Learner Engaging in Learning Activities
-- ============================================

CREATE TABLE IF NOT EXISTS activity_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    learner_iep_id INT NOT NULL,
    activity_type VARCHAR(100),
    activity_data JSON,
    performance_notes TEXT,
    recorded_by INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_iep_id) REFERENCES learner_iep(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_recorded_at (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS module_access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    module_name VARCHAR(255),
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_minutes INT,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_accessed_at (accessed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY MODULE 4 — Logging and Monitoring
-- ============================================

CREATE TABLE IF NOT EXISTS login_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    result ENUM('success', 'failure') NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_user_id (user_id),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    affected_table VARCHAR(100),
    affected_record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY MODULE 3 — In-App Notifications
-- ============================================

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY MODULE 3 — Encrypted Sensitive Fields
-- ============================================

CREATE TABLE IF NOT EXISTS encryption_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    action ENUM('encrypted', 'decrypted') NOT NULL,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY MODULE 4 — CSRF Protection
-- ============================================

CREATE TABLE IF NOT EXISTS csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY MODULE 5 — Login Rate Limiting
-- ============================================

CREATE TABLE IF NOT EXISTS rate_limit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    attempt_type ENUM('login', 'registration', 'password_reset') NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_time (email, attempted_at),
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY MODULE 6 — DLP (Data Loss Prevention)
-- ============================================

CREATE TABLE IF NOT EXISTS dlp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DEFAULT DATA
-- ============================================ 

INSERT IGNORE INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider)
VALUES (1, 'System Admin', 'admin@spedlms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', TRUE, 'local');

INSERT IGNORE INTO dlp_settings (setting_key, setting_value, description) VALUES
('dlp_enable_watermark', 'true', 'Enable watermark on sensitive documents'),
('dlp_enable_screenshot_block', 'true', 'Block screenshot attempts'),
('dlp_enable_copy_block', 'true', 'Block copy/paste on sensitive pages'),
('dlp_enable_print_block', 'true', 'Block printing of sensitive documents'),
('dlp_enable_export_block', 'true', 'Block export functionality'),
('dlp_watermark_format', '{user} | {timestamp} | {ip}', 'Watermark format string'),
('dlp_sensitive_pages', 'iep,assessment,student_records', 'Comma-separated list of sensitive page types');

SET FOREIGN_KEY_CHECKS = 1;


-- ============================================
-- MIGRATION v20: Add user_id to login_log
-- ============================================

-- Add user_id column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'login_log';
SET @columnname = 'user_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL AFTER id, ADD FOREIGN KEY (', @columnname, ') REFERENCES users(id) ON DELETE SET NULL, ADD INDEX idx_', @columnname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Mark migration as applied
INSERT IGNORE INTO db_version (version) VALUES (20);

-- ============================================
-- MIGRATION v21: System Settings Table
-- ============================================

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, category, description) VALUES
('session_timeout', '15', 'security', 'Session timeout in minutes'),
('max_login_attempts', '5', 'security', 'Maximum failed login attempts before lockout'),
('lockout_duration', '15', 'security', 'Account lockout duration in minutes'),
('otp_expiration', '10', 'security', 'OTP expiration time in minutes'),
('logout_warning', '2', 'security', 'Show logout warning X minutes before timeout');

-- Mark migration as applied
INSERT IGNORE INTO db_version (version) VALUES (21);

-- ============================================
-- MIGRATION v22: User Management Enhancements
-- ============================================

-- Add deleted_at column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'deleted_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add locked_until column if it doesn't exist
SET @columnname = 'locked_until';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_deleted_at ON users(deleted_at);
CREATE INDEX IF NOT EXISTS idx_locked_until ON users(locked_until);

-- Mark migration as applied
INSERT IGNORE INTO db_version (version) VALUES (22);

-- ============================================
-- MIGRATION v23: Manual Activity System & Assignment Tracking
-- ============================================

-- Add assignment-specific fields to learning_materials
SET @dbname = DATABASE();
SET @tablename = 'learning_materials';

-- Add is_assignment column
SET @columnname = 'is_assignment';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE AFTER description')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add due_date column
SET @columnname = 'due_date';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DATETIME NULL AFTER is_assignment')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add points column
SET @columnname = 'points';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT DEFAULT 0 AFTER due_date')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Create activity_templates table for manual activities
CREATE TABLE IF NOT EXISTS activity_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    activity_type ENUM(
        'multiple_choice', 
        'true_false', 
        'fill_blanks', 
        'matching', 
        'drag_drop_sort', 
        'image_label', 
        'sequencing', 
        'flashcards'
    ) NOT NULL,
    instructions TEXT,
    activity_data JSON NOT NULL,
    total_points INT DEFAULT 0,
    time_limit_minutes INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    INDEX idx_material_id (material_id),
    INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create activity_attempts table (learner answers)
CREATE TABLE IF NOT EXISTS activity_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    attempt_number INT DEFAULT 1,
    answers JSON NOT NULL,
    score INT DEFAULT 0,
    total_points INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    time_spent_minutes INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (activity_id) REFERENCES activity_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    INDEX idx_activity_id (activity_id),
    INDEX idx_student_id (student_id),
    INDEX idx_completed (completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create assignment_submissions table
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_type ENUM('file', 'text', 'both') NOT NULL,
    file_path VARCHAR(500),
    text_answer TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded BOOLEAN DEFAULT FALSE,
    grade INT,
    teacher_feedback TEXT,
    graded_at TIMESTAMP NULL,
    graded_by INT,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_material_id (material_id),
    INDEX idx_student_id (student_id),
    INDEX idx_graded (graded)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create learner_progress table
CREATE TABLE IF NOT EXISTS learner_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    material_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    time_spent_minutes INT DEFAULT 0,
    stars_earned INT DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_material (student_id, material_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mark migration as applied
INSERT IGNORE INTO db_version (version) VALUES (23);
