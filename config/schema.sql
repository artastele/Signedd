-- SPED LMS  Database Schema (Clean Version)
-- DO NOT ALTER WITHOUT APPROVAL
-- Last modified: 2026-05-13
-- All tables use CREATE TABLE IF NOT EXISTS (idempotent, safe to re-run)
-- All ALTER TABLE use INFORMATION_SCHEMA checks (MariaDB compatible)
--
-- NOTE: This file is the canonical merged schema import for fresh installs.
--       It already contains the migration blocks found in config/manual_migration_v41_v43.sql.
--       Import this file only; do not separately import config/manual_migration_v41_v43.sql unless
--       you need the legacy helper for a partial migration scenario.
--
-- Migration blocks: v39 .. v46 (see db_version). Latest: v46 (iep_steps.step_domain).
-- New machine: ensure app boots once (public/index.php runs SchemaManager) OR import this file;
-- MySQL < 8.0.12 / MariaDB without ADD COLUMN IF NOT EXISTS: use IEPModel::ensurePartOneSaveSchema at runtime
-- plus config/manual_migration_v41_v43.sql for gaps as needed.

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- MIGRATION TRACKING
-- ============================================
CREATE TABLE IF NOT EXISTS db_version (
    version INT PRIMARY KEY,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- SECURITY  Authentication & Authorization
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
    role ENUM('user','parent','sped_teacher','guidance','principal','master_teacher','learner','admin') DEFAULT 'user',
    status ENUM('active','inactive','pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(10),
    email_verification_expires DATETIME,
    verification_attempts INT DEFAULT 0,
    google_id VARCHAR(255) UNIQUE,
    profile_picture VARCHAR(255),
    auth_provider ENUM('local','google') DEFAULT 'local',
    deleted_at TIMESTAMP NULL,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role ENUM('sped_teacher','guidance','principal','master_teacher') NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approver_role ENUM('admin','principal') DEFAULT 'principal',
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
-- PROCESS 1  Parent Complying Enrollment Requirements
-- ============================================

CREATE TABLE IF NOT EXISTS enrollment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    enrollment_type ENUM('new','transfer','returning') NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    previous_enrollment_id INT NULL,
    is_draft BOOLEAN DEFAULT TRUE,
    status ENUM('draft','pending','verified','rejected') DEFAULT 'draft',
    lrn VARCHAR(12),
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    extension_name VARCHAR(20),
    birth_date DATE NOT NULL,
    sex ENUM('Male','Female') NOT NULL,
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
    previous_school_type ENUM('Public','Private'),
    grade_level_to_enroll VARCHAR(50) NOT NULL,
    is_balik_aral BOOLEAN DEFAULT FALSE,
    is_pept_passer BOOLEAN DEFAULT FALSE,
    pept_rating VARCHAR(20),
    is_als_passer BOOLEAN DEFAULT FALSE,
    als_rating VARCHAR(20),
    shs_track VARCHAR(50),
    shs_strand VARCHAR(100),
    shs_semester ENUM('1st','2nd'),
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
    review_note TEXT,
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
    document_type ENUM('psa_birth_cert','pwd_id','medical_record','beef_form') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
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
-- PROCESS 2  Verifying Enrollment Requirements
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
-- PROCESS 3  Conducting Initial Assessment
-- ============================================

CREATE TABLE IF NOT EXISTS assessment_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    parent_id INT,
    assessed_by INT NOT NULL,
    conducted_by INT,
    assessment_type VARCHAR(100),
    assessment_data JSON,
    submitted_data JSON,
    education_history JSON,
    assessment_info JSON,
    section_a_data JSON,
    services_checked JSON,
    screening_types JSON,
    status ENUM('draft','finalized','pending','approved','rejected') DEFAULT 'draft',
    reviewed_by INT,
    review_note TEXT,
    quarter VARCHAR(2),
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (conducted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student_id (student_id),
    INDEX idx_version (version),
    INDEX idx_assessment_status (status),
    INDEX idx_assessment_quarter (quarter),
    INDEX idx_assessment_submitted (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    mdt_members JSON,
    date_of_assessment DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    INDEX idx_assessment_id (assessment_id),
    INDEX idx_service_name (service_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_service_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_service_id) REFERENCES assessment_services(id) ON DELETE CASCADE,
    INDEX idx_service_id (assessment_service_id),
    INDEX idx_file_type (file_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    service_type VARCHAR(255) NOT NULL,
    checked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    INDEX idx_assessment_id (assessment_id),
    INDEX idx_service_type (service_type),
    UNIQUE KEY unique_assessment_service (assessment_id, service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 4  Facilitating IEP Meeting
-- ============================================
-- NOTE: iep_meetings uses DATETIME (combined date+time) and meeting_location.
-- The duplicate definition that existed in migration v1.27 has been removed.
-- The v1.27 block used separate DATE + TIME columns and "venue"  that was
-- superseded by this definition. This is the single authoritative definition.

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
    status ENUM('scheduled','rescheduled','completed','cancelled') DEFAULT 'scheduled',
    notes TEXT,
    reschedule_reason TEXT,
    cancellation_reason TEXT,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
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

CREATE TABLE IF NOT EXISTS meeting_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    notified_via ENUM('email','system','both') DEFAULT 'both',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_user (meeting_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User availability (recurring weekly schedule + exception dates)
-- Replaces the old iep_meeting_calendars file-upload approach (removed)
CREATE TABLE IF NOT EXISTS user_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('recurring','exception') NOT NULL,
    day_of_week TINYINT NULL COMMENT '0=Sunday ... 6=Saturday (recurring only)',
    specific_date DATE NULL COMMENT 'Exception dates only',
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_specific_date (specific_date),
    UNIQUE KEY unique_recurring (user_id, type, day_of_week),
    UNIQUE KEY unique_exception (user_id, type, specific_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PDSP records (Part II  filled during IEP meeting)
CREATE TABLE IF NOT EXISTS pdsp_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    filled_by INT NOT NULL,
    status ENUM('draft','signed') DEFAULT 'draft',
    signed_document_path VARCHAR(255) NULL,
    signatories TEXT NULL,
    uploaded_image_path VARCHAR(500) NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (filled_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meeting (meeting_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pdsp_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdsp_id INT NOT NULL,
    domain_name VARCHAR(100) NOT NULL,
    sub_domain VARCHAR(200) NULL,
    skills_description TEXT NULL,
    mastered BOOLEAN DEFAULT FALSE,
    educational_recommendation TEXT NULL,
    q1_level ENUM('Beginning','Developing','Approaching Proficiency','Proficient','Advanced') NULL,
    q2_level ENUM('Beginning','Developing','Approaching Proficiency','Proficient','Advanced') NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    INDEX idx_pdsp_domain (pdsp_id, domain_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Normalized signatory list per PDSP record
CREATE TABLE IF NOT EXISTS pdsp_signatories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdsp_id INT NOT NULL,
    signatory_role ENUM('sped_teacher','gen_ed_teacher','school_head','ilrc_supervisor',
                        'parent_guardian','medical_allied_1','medical_allied_2','medical_allied_3') NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    INDEX idx_pdsp_id (pdsp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- PROCESS 4 (continued) -- IEP P2 Documents and PDSP Signatures
-- Actively used by IEPP2DocumentModel, IEPDocumentController, FileController
-- ============================================

CREATE TABLE IF NOT EXISTS iep_p2_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSON NOT NULL,
    pdf_path VARCHAR(500),
    status ENUM('draft','pending_review','reviewed_signed') DEFAULT 'draft',
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
    reviewer_role ENUM('guidance','principal','parent','sped_teacher','school_head','ilrc_supervisor') NOT NULL,
    feedback TEXT,
    signature_data TEXT,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_p2_id) REFERENCES iep_p2_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reviewer_per_p2 (iep_p2_id, reviewer_id),
    INDEX idx_reviewer_role (reviewer_role),
    INDEX idx_reviewed_at (reviewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PDSP digital signatures (canvas-based per signatory)
-- Used by IEPMeetingController::saveSignature()
CREATE TABLE IF NOT EXISTS pdsp_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdsp_id INT NOT NULL,
    signatory_role ENUM('sped_teacher','gen_ed_teacher','school_head','ilrc_supervisor',
                        'parent_guardian','medical_allied_1','medical_allied_2','medical_allied_3') NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    signature_image_path VARCHAR(500) NOT NULL,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pdsp_signatory (pdsp_id, signatory_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 5  Generating IEP (P3 Documents)
-- ============================================

CREATE TABLE IF NOT EXISTS iep_p3_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSON NOT NULL,
    pdf_path VARCHAR(500),
    status ENUM('draft','pending_signatures','signed_approved') DEFAULT 'draft',
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
    signer_role ENUM('parent','guidance','teacher','sped_teacher','principal','school_head','ilrc_supervisor') NOT NULL,
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
    document_type ENUM('p2','p3') NOT NULL,
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
-- PROCESS 6  Implementing IEP
-- ============================================

CREATE TABLE IF NOT EXISTS learner_iep (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    iep_p3_id INT NOT NULL,
    teacher_id INT NOT NULL,
    implementation_status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
    start_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_p3_id) REFERENCES iep_p3_documents(id) ON DELETE CASCADE,
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
    is_assignment BOOLEAN DEFAULT FALSE,
    due_date DATETIME NULL,
    points INT DEFAULT 0,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_iep_id) REFERENCES learner_iep(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 7  Learner Engaging in Learning Activities
-- ============================================

CREATE TABLE IF NOT EXISTS activity_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    activity_type ENUM('multiple_choice','true_false','fill_blanks','matching',
                       'drag_drop_sort','image_label','sequencing','flashcards') NOT NULL,
    instructions TEXT,
    activity_data JSON NOT NULL,
    total_points INT DEFAULT 0,
    time_limit_minutes INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    INDEX idx_material_id (material_id),
    INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_type ENUM('file','text','both') NOT NULL,
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

CREATE TABLE IF NOT EXISTS learner_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    material_id INT NOT NULL,
    status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
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
-- SECURITY  Logging, Notifications, CSRF, Rate Limiting, DLP
-- ============================================

CREATE TABLE IF NOT EXISTS login_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    result ENUM('success','failure') NOT NULL,
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

CREATE TABLE IF NOT EXISTS encryption_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    action ENUM('encrypted','decrypted') NOT NULL,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS rate_limit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    attempt_type ENUM('login','registration','password_reset') NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_time (email, attempted_at),
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dlp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- ============================================
-- DEFAULT SEED DATA
-- ============================================

-- Default admin account (password: password)
INSERT IGNORE INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider)
VALUES (1, 'System Admin', 'admin@spedlms.local',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', 'active', TRUE, 'local');

-- Demo accounts (password: password)
INSERT IGNORE INTO users (id, name, first_name, last_name, email, contact_number, password_hash, role, status, email_verified, auth_provider) VALUES
(2,  'Demo Parent',         'Demo', 'Parent',         'demo.parent@spedlms.local',         '09123456701', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent',          'active', TRUE, 'local'),
(3,  'Demo SPED Teacher',   'Demo', 'SPED Teacher',   'demo.sped@spedlms.local',           '09123456702', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sped_teacher',    'active', TRUE, 'local'),
(4,  'Demo Guidance',       'Demo', 'Guidance',       'demo.guidance@spedlms.local',       '09123456703', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guidance',        'active', TRUE, 'local'),
(5,  'Demo Principal',      'Demo', 'Principal',      'demo.principal@spedlms.local',      '09123456704', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'principal',       'active', TRUE, 'local'),
(6,  'Demo Master Teacher', 'Demo', 'Master Teacher', 'demo.master@spedlms.local',         '09123456705', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master_teacher',  'active', TRUE, 'local'),
(7,  'Demo Learner',        'Demo', 'Learner',        'demo.learner@spedlms.local',        '09123456706', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'learner',         'active', TRUE, 'local');

INSERT IGNORE INTO dlp_settings (setting_key, setting_value, description) VALUES
('dlp_enable_watermark',        'true',                    'Enable watermark on sensitive documents'),
('dlp_enable_screenshot_block', 'true',                    'Block screenshot attempts'),
('dlp_enable_copy_block',       'true',                    'Block copy/paste on sensitive pages'),
('dlp_enable_print_block',      'true',                    'Block printing of sensitive documents'),
('dlp_enable_export_block',     'true',                    'Block export functionality'),
('dlp_watermark_format',        '{user} | {timestamp} | {ip}', 'Watermark format string'),
('dlp_sensitive_pages',         'iep,assessment,student_records', 'Comma-separated sensitive page types');

INSERT IGNORE INTO system_settings (setting_key, setting_value, category, description) VALUES
('session_timeout',    '15', 'security', 'Session timeout in minutes'),
('max_login_attempts', '5',  'security', 'Maximum failed login attempts before lockout'),
('lockout_duration',   '15', 'security', 'Account lockout duration in minutes'),
('otp_expiration',     '10', 'security', 'OTP expiration time in minutes'),
('logout_warning',     '2',  'security', 'Show logout warning X minutes before timeout');

-- Mark all migrations as applied on fresh install
INSERT IGNORE INTO db_version (version) VALUES
(20),(21),(22),(23),(24),(25),(26),(27),(28),(29),
(30),(31),(32),(33),(34),(35),(36),(37),(38);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- MIGRATION: v39 -- Process 5 IEP Tables
-- ============================================

CREATE TABLE IF NOT EXISTS iep_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    pdsp_id INT NOT NULL,
    drafted_by INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    status ENUM('draft','signing','signed') DEFAULT 'draft',
    signing_method ENUM('print_upload','digital') NULL,
    signed_document_path VARCHAR(500) NULL,
    re_evaluation_date DATE NULL,
    header_learner_name VARCHAR(255) NULL,
    header_learner_age VARCHAR(50) NULL,
    header_lrn VARCHAR(32) NULL,
    header_section VARCHAR(120) NULL,
    header_teacher_name VARCHAR(255) NULL,
    header_school_name VARCHAR(255) NULL,
    header_grade_level VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    FOREIGN KEY (drafted_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_school_year (school_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    domain_name VARCHAR(200) NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_core (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    developmental_domain TEXT NULL,
    priority_needs TEXT NULL,
    terminal_objectives TEXT NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_iep_core (iep_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    step_number INT NOT NULL,
    objectives TEXT NULL,
    observation TEXT NULL,
    activities TEXT NULL,
    materials TEXT NULL,
    evaluation TEXT NULL,
    duration_lp VARCHAR(100) NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_step_number (step_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_signatories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    signatory_role ENUM('parent_guardian','guidance_counselor','teacher',
                        'sned_teacher','school_head','ilrc_supervisor') NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    send_status ENUM('not_sent','pending','signed') NOT NULL DEFAULT 'not_sent',
    signature_request_sent_at TIMESTAMP NULL,
    signature_image_path VARCHAR(500) NULL,
    signed_at TIMESTAMP NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_role (signatory_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    sent_to INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    viewed_at TIMESTAMP NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_to) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_sent_to (sent_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (39);

-- END MIGRATION: v39

-- ============================================
-- MIGRATION: v40 - Simplify Process 5 IEP Generation
-- Remove complex digital form, keep simple upload system
-- ============================================

-- This is a migration step for older schema versions.
-- On a fresh install this is safe because IF EXISTS makes it a no-op.
-- If you want a clean-only import file, you can remove this block,
-- but keep it here if the same file is also used to migrate older databases.
-- Drop tables no longer needed
DROP TABLE IF EXISTS iep_domains;
DROP TABLE IF EXISTS iep_core; 
DROP TABLE IF EXISTS iep_steps;

-- Remove signing_method column from iep_records
-- This migration block is kept for upgrade history only. For fresh installs, the table definitions already contain the desired structure.

INSERT IGNORE INTO db_version (version) VALUES (40);

-- END MIGRATION: v40

-- ============================================
-- MIGRATION: v41 - Student Documents Centralized Storage
-- Mobile responsive preparation - centralized document tracking
-- ============================================

CREATE TABLE IF NOT EXISTS student_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    process_name VARCHAR(50) NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(20) NOT NULL,
    file_size INT NOT NULL,
    uploaded_by INT NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_hidden BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_process (student_id, process_name),
    INDEX idx_document_type (document_type),
    INDEX idx_uploaded_at (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (41);

-- END MIGRATION: v41

-- ============================================
-- MIGRATION: v42 - Process 6 & 7 LMS Tables
-- Lesson plans, materials, 8-type activities,
-- submissions, grading, learner access
-- ============================================

-- --------------------------------------------
-- Lesson Plans
-- Linked to iep_records (signed IEP from P5)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lesson_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    student_id INT NULL,                          -- NULL when assignment_type = 'shared'
    created_by INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    pdsp_domain ENUM(
        'perceptuo_cognitive',
        'psychosocial',
        'socio_emotional',
        'psychomotor',
        'daily_living_skills',
        'communication_language'
    ) NOT NULL,
    assignment_type ENUM('individual','shared') DEFAULT 'individual',
    document_path VARCHAR(500) NULL,              -- uploaded lesson plan file
    status ENUM('draft','published') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Lesson Assignments
-- Maps lesson plans to individual learners
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lesson_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_plan_id INT NOT NULL,
    student_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (lesson_plan_id, student_id),
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Lesson Materials
-- 3 types: file upload, external link, embed
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lesson_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_plan_id INT NOT NULL,
    material_type ENUM('file','link','embed') NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NULL,                  -- for type = 'file'
    external_url VARCHAR(1000) NULL,              -- for type = 'link' or 'embed'
    embed_type ENUM('youtube','gdrive','other') NULL, -- for type = 'embed'
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- LMS Activities
-- 8 types — all content stored in activity_data JSON
--
-- activity_type values and their activity_data shape:
--
-- multiple_choice:
--   { "options": [{"text":"...", "is_correct": true/false}], "points": N }
--
-- true_false:
--   { "correct_answer": "true"|"false", "points": N }
--
-- fill_in_blanks:
--   { "sentences": [{"text":"The ___ is red","answers":["apple"]}], "points": N }
--
-- matching:
--   { "pairs": [{"left":"...","right":"..."}], "points": N }
--
-- drag_drop_sort:
--   { "items": ["step1","step2","step3"], "correct_order": [0,1,2], "points": N }
--
-- image_label:
--   { "image_path":"...", "labels":[{"x":10,"y":20,"answer":"..."}], "points": N }
--
-- flashcards:
--   { "cards": [{"front":"...","back":"..."}] }
--   (no scoring — view only)
--
-- sequencing:
--   { "items": ["event1","event2","event3"], "correct_order": [2,0,1], "points": N }
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_plan_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    instructions TEXT NULL,
    activity_type ENUM(
        'multiple_choice',
        'true_false',
        'fill_in_blanks',
        'matching',
        'drag_drop_sort',
        'image_label',
        'flashcards',
        'sequencing'
    ) NOT NULL,
    activity_data JSON NOT NULL,                  -- type-specific content (see above)
    max_score INT NULL,                           -- NULL for flashcards
    due_date DATETIME NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Activity Submissions
-- Learner answers stored per activity
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    submitted_by INT NOT NULL,                    -- user_id (learner or parent)
    file_path VARCHAR(500) NULL,                  -- for file_submission type (future)
    answers JSON NULL,                            -- learner answers for scored types
    auto_score INT NULL,                          -- computed on submit for auto-scored types
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES lms_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (activity_id, student_id), -- one submission per learner per activity
    INDEX idx_activity_id (activity_id),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Activity Grades
-- Teacher manual score override + remarks
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    graded_by INT NOT NULL,
    score INT NULL,
    max_score INT NULL,
    is_complete TINYINT(1) DEFAULT 0,
    remarks TEXT NULL,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES lms_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade (submission_id),
    INDEX idx_graded_at (graded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- LMS Activity Logs
-- Tracks opened / submitted / graded events
-- Separate from system activity_log
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_id INT NULL,
    material_id INT NULL,
    action ENUM('opened','submitted','graded') NOT NULL,
    performed_by INT NOT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_action (action),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Learner Access Mode
-- direct = learner has own login
-- parent_managed = parent completes on behalf
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS learner_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    access_mode ENUM('direct','parent_managed') DEFAULT 'parent_managed',
    assigned_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_access (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (42);

-- END MIGRATION: v42

-- ============================================
-- MIGRATION: v43 — Process 7 Gamification Tables
-- learner_points, learner_badges, activity_stars
-- No changes to any existing Process 1–6 tables.
-- ============================================

-- --------------------------------------------
-- Learner Points
-- XP ledger — one row per earning event.
-- source_type maps to XP rules in process-7.md:
--   view         → +5 XP (first open of view-only activity)
--   submission   → +10 XP (file submission sent)
--   quiz         → score% × max_score XP
--   lesson_bonus → +20 XP (lesson plan fully completed)
--   badge_bonus  → +15 XP (badge earned)
-- source_id is nullable: activity id, lesson id,
-- or badge row id depending on source_type.
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS learner_points (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    points      INT NOT NULL DEFAULT 0,
    reason      VARCHAR(255) NOT NULL,
    source_type ENUM('view','submission','quiz','lesson_bonus','badge_bonus') NOT NULL,
    source_id   INT NULL,
    earned_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    INDEX idx_student_id  (student_id),
    INDEX idx_source_type (source_type),
    INDEX idx_earned_at   (earned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Learner Badges
-- One row per badge earned per learner.
-- badge_key ENUM matches the fixed set defined
-- in process-7.md — teacher cannot create custom badges.
-- UNIQUE KEY prevents duplicate badge awards.
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS learner_badges (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    badge_key  ENUM(
        'first_activity',
        'lesson_complete',
        'perfect_score',
        'five_in_a_row',
        'all_done',
        'star_collector'
    ) NOT NULL,
    earned_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_badge (student_id, badge_key),
    INDEX idx_student_id (student_id),
    INDEX idx_badge_key  (badge_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Activity Stars
-- Auto-calculated when teacher grades a submission.
-- Star rule (from process-7.md):
--   90–100% → 3 stars
--   70–89%  → 2 stars
--   <70%    → 1 star
--   View-only activities: no stars row created.
-- submission_id → lms_submissions (P7 graded activities only).
-- UNIQUE KEY: one star record per submission.
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS activity_stars (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    student_id    INT NOT NULL,
    stars         TINYINT NOT NULL,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES lms_submissions(id)  ON DELETE CASCADE,
    FOREIGN KEY (student_id)   REFERENCES student_records(id)   ON DELETE CASCADE,
    UNIQUE KEY unique_submission_stars (submission_id),
    INDEX idx_student_id (student_id),
    CONSTRAINT chk_stars CHECK (stars IN (1, 2, 3))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (43);

-- END MIGRATION: v43

-- ============================================
-- MIGRATION: v44 — Process 5 IEP (living document) + P5–P6–P7 links
-- Restores iep_domains / iep_core / iep_steps (dropped in v40),
-- removes locked state, restores signing_method, junction + edit log tables.
-- ============================================

UPDATE iep_records SET status = 'signed' WHERE status = 'locked';

-- ALTER statements removed for compatibility with older MySQL versions.
-- The fresh import schema defines iep_records in its final, supported shape.

CREATE TABLE IF NOT EXISTS iep_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    domain_name VARCHAR(200) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_display_order (iep_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_core (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    developmental_domain TEXT NULL,
    priority_needs TEXT NULL,
    terminal_objectives TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_iep_core (iep_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    step_number INT NOT NULL,
    step_domain VARCHAR(191) NULL,
    step_objective TEXT NULL,
    duration_lp VARCHAR(255) NULL,
    instructional_evaluation TEXT NULL,
    observation TEXT NULL,
    observation_unlocked TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_step_number (iep_id, step_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_step_lesson_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_step_id INT NOT NULL,
    lesson_plan_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_step_id) REFERENCES iep_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    UNIQUE KEY unique_step_lesson (iep_step_id, lesson_plan_id),
    INDEX idx_lesson_plan_id (lesson_plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_step_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_step_id INT NOT NULL,
    material_id INT NOT NULL,
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_step_id) REFERENCES iep_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES lesson_materials(id) ON DELETE CASCADE,
    UNIQUE KEY unique_step_material (iep_step_id, material_id),
    INDEX idx_material_id (material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_edit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    edited_by INT NOT NULL,
    field_name VARCHAR(191) NOT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    edited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (edited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_iep_edited (iep_id, edited_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ALTER statements removed for compatibility with older MySQL versions.
-- Fresh install schema already includes the send_status and signature_request_sent_at columns.

INSERT IGNORE INTO db_version (version) VALUES (44);

-- END MIGRATION: v44

-- ============================================
-- MIGRATION: v45 — Process 5 IEP header overrides (Section 2 editable snapshot)
-- ============================================

-- ALTER statements removed for compatibility with older MySQL versions.
-- Fresh install schema already includes the header_* columns on iep_records.

INSERT IGNORE INTO db_version (version) VALUES (45);

-- END MIGRATION: v45

-- ============================================
-- MIGRATION: v46 — IEP step domain label (Section 5)
-- ============================================

-- ALTER statements removed for compatibility with older MySQL versions.
-- Fresh install schema already includes the step_domain column on iep_steps.

INSERT IGNORE INTO db_version (version) VALUES (46);

-- END MIGRATION: v46

-- ============================================
-- MIGRATION: v47 - Unified Transition + IEP Workflow
-- Detailed runtime DDL is mirrored in TransitionWorkflowModel::ensureTables()
-- so demo databases can self-create missing workflow tables without reset/import.
-- Tables: progress_reports, cot_observations, transition_readiness,
-- individual_transition_plans, inclusive_iep_records, itgp_records,
-- itgp_items, placement_notices.
-- ============================================

CREATE TABLE IF NOT EXISTS progress_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    iep_record_id INT NOT NULL,
    created_by INT NOT NULL,
    school_year VARCHAR(20) NULL,
    quarter VARCHAR(50) NULL,
    attendance_summary TEXT NULL,
    progress_summary TEXT NULL,
    teacher_remarks TEXT NULL,
    ratings JSON NULL,
    status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    document_path VARCHAR(255) NULL,
    finalized_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_progress_reports_iep (iep_record_id),
    INDEX idx_progress_reports_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cot_observations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    iep_record_id INT NOT NULL,
    observed_teacher_id INT NOT NULL,
    created_by INT NOT NULL,
    lesson_plan_id INT NULL,
    school_year VARCHAR(20) NULL,
    quarter VARCHAR(50) NULL,
    observation_date DATE NULL,
    ratings JSON NULL,
    strengths TEXT NULL,
    recommendations TEXT NULL,
    status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    notification_sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (observed_teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cot_iep (iep_record_id),
    INDEX idx_cot_teacher (observed_teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (47);

-- END MIGRATION: v47

-- ============================================
-- MIGRATION: v48 - Student Attendance Records Table
-- ============================================

CREATE TABLE IF NOT EXISTS attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'tardy', 'excused') NOT NULL DEFAULT 'present',
    remarks TEXT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_date (student_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (48);

-- END MIGRATION: v48

-- ============================================
-- MIGRATION: v49 - Process 8 Progress Report Card Tables
-- ============================================

-- Recreate attendance_records to support F2F (manual) and online (auto_activity) attendance
DROP TABLE IF EXISTS attendance_records;
CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent') NOT NULL DEFAULT 'present',
    source ENUM('manual', 'auto_activity') NOT NULL DEFAULT 'manual',
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_date_src (student_id, date, source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create grade_entries to store F2F (manual) and online (auto) scores per quarter and domain
CREATE TABLE IF NOT EXISTS grade_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    quarter VARCHAR(50) NOT NULL,
    domain VARCHAR(191) NOT NULL,
    source ENUM('auto', 'manual') NOT NULL,
    score DECIMAL(5, 2) NOT NULL,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_quarter_domain_src (student_id, quarter, domain, source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create report_remarks to store teacher/parent remarks & signatures per quarter
CREATE TABLE IF NOT EXISTS report_remarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    progress_report_id INT NOT NULL,
    quarter VARCHAR(50) NOT NULL,
    remark_type ENUM('teacher', 'parent') NOT NULL,
    remark_text TEXT NULL,
    signature_name VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (progress_report_id) REFERENCES progress_reports(id) ON DELETE CASCADE,
    UNIQUE KEY unique_report_quarter_type (progress_report_id, quarter, remark_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (49);

-- END MIGRATION: v49

-- ============================================
-- MIGRATION: v50 - Process 9 Classroom Observation Tool (COT) Tables
-- ============================================

CREATE TABLE IF NOT EXISTS cot_indicator_sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_year VARCHAR(50) NOT NULL,
    indicator_number INT NOT NULL,
    indicator_text TEXT NOT NULL,
    competency_code VARCHAR(50) NOT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_school_year (school_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS classroom_observations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    observer_id INT NOT NULL,
    observed_teacher_id INT NOT NULL,
    school_year VARCHAR(50) NOT NULL,
    quarter VARCHAR(50) NOT NULL,
    observation_number INT NOT NULL,
    subject_grade_level VARCHAR(255) NOT NULL,
    scheduled_at DATETIME NOT NULL,
    status ENUM('scheduled', 'in_progress', 'finalized') NOT NULL DEFAULT 'scheduled',
    other_comments TEXT NULL,
    average_score DECIMAL(5, 2) NULL,
    finalized_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (observer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (observed_teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_observer_id (observer_id),
    INDEX idx_observed_teacher_id (observed_teacher_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS observation_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    observation_id INT NOT NULL,
    indicator_id INT NOT NULL,
    rating VARCHAR(5) NOT NULL,
    rated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (observation_id) REFERENCES classroom_observations(id) ON DELETE CASCADE,
    FOREIGN KEY (indicator_id) REFERENCES cot_indicator_sets(id) ON DELETE CASCADE,
    UNIQUE KEY unique_observation_indicator (observation_id, indicator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (50);

-- END MIGRATION: v50

-- ============================================
-- MIGRATION: v51 - Process 10 Transition Readiness Tables
-- ============================================

CREATE TABLE IF NOT EXISTS transition_readiness (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    iep_record_id INT NOT NULL,
    progress_report_id INT NULL,
    cot_observation_id INT NULL,
    created_by INT NOT NULL,
    readiness_result ENUM('Ready for Inclusion','Needs More Support','Not Yet Ready','For Re-evaluation') NOT NULL DEFAULT 'For Re-evaluation',
    evidence_summary TEXT NULL,
    teacher_recommendation TEXT NULL,
    status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    finalized_at DATETIME NULL,
    overall_status ENUM('ready','partial','not_ready') NOT NULL DEFAULT 'partial',
    overall_status_overridden BOOLEAN NOT NULL DEFAULT FALSE,
    overall_remarks TEXT NULL,
    evaluated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (progress_report_id) REFERENCES progress_reports(id) ON DELETE SET NULL,
    FOREIGN KEY (cot_observation_id) REFERENCES cot_observations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_transition_readiness_iep (iep_record_id),
    INDEX idx_transition_result (readiness_result),
    INDEX idx_transition_status (status),
    INDEX idx_transition_overall_status (overall_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transition_readiness_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transition_readiness_id INT NOT NULL,
    iep_step_id INT NOT NULL,
    goal_text TEXT NOT NULL,
    pdsp_domain VARCHAR(191) NOT NULL,
    suggested_status ENUM('ready','partial','not_ready') NOT NULL DEFAULT 'partial',
    final_status ENUM('ready','partial','not_ready') NOT NULL DEFAULT 'partial',
    status_overridden BOOLEAN NOT NULL DEFAULT FALSE,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_step_id) REFERENCES iep_steps(id) ON DELETE CASCADE,
    UNIQUE KEY unique_readiness_goal (transition_readiness_id, iep_step_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (51);

-- END MIGRATION: v51

-- ============================================
-- MIGRATION: v52 - Process 11 Individual Transition Plan (ITP) Tables
-- ============================================

CREATE TABLE IF NOT EXISTS itp_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    transition_readiness_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    point_of_entry VARCHAR(255) NULL,
    learner_information JSON NULL,
    status ENUM('in_progress', 'finalized') NOT NULL DEFAULT 'in_progress',
    drafted_by INT NOT NULL,
    finalized_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
    FOREIGN KEY (drafted_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itp_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itp_team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itp_id INT NOT NULL,
    role ENUM('itp_coordinator', 'school_head', 'sped_teacher', 'parent_guardian', 'learner', 'guidance_teacher', 'linkages') NOT NULL,
    assigned_user_id INT NULL,
    name VARCHAR(255) NULL,
    contact_details VARCHAR(255) NULL,
    date_started DATE NULL,
    status ENUM('pending', 'filled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_itp_role (itp_id, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itp_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itp_id INT NOT NULL,
    signatory_role ENUM('parent_guardian') NOT NULL DEFAULT 'parent_guardian',
    signature_image_path VARCHAR(255) NOT NULL,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itp_signature_role (itp_id, signatory_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itp_narrative (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itp_id INT NOT NULL,
    section ENUM('strengths', 'interests', 'talents', 'skills', 'needs') NOT NULL,
    item_text TEXT NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itp_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itp_id INT NOT NULL,
    timing ENUM('beginning_of_sy', 'end_of_sy') NOT NULL,
    recommendation_text TEXT NOT NULL,
    FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itp_recommendation_timing (itp_id, timing)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itp_program_matrix (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itp_id INT NOT NULL,
    row_type INT NOT NULL,
    column_type INT NOT NULL,
    is_checked BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itp_matrix_cell (itp_id, row_type, column_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (52);

-- END MIGRATION: v52

-- ============================================
-- MIGRATION: v53 - Process 12 General Teacher & ITGP Tables
-- ============================================

ALTER TABLE users MODIFY COLUMN role ENUM('user','parent','sped_teacher','guidance','principal','master_teacher','learner','admin','general_teacher') DEFAULT 'user';

INSERT IGNORE INTO users (id, name, first_name, last_name, email, contact_number, password_hash, role, status, email_verified, auth_provider)
VALUES (8, 'Demo General Teacher', 'Demo', 'General Teacher', 'demo.genteacher@spedlms.local', '09123456707',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'general_teacher', 'active', TRUE, 'local');

CREATE TABLE IF NOT EXISTS itgp_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    itp_id INT NOT NULL,
    general_teacher_id INT NOT NULL,
    goal TEXT NULL,
    entry_point VARCHAR(255) NULL,
    learning_packages TEXT NULL,
    recommendations TEXT NULL,
    status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    finalized_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
    FOREIGN KEY (general_teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itgp_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itgp_id INT NOT NULL,
    competency_skill TEXT NULL,
    activities TEXT NULL,
    time_frame VARCHAR(255) NULL,
    person_responsible VARCHAR(255) NULL,
    remarks TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itgp_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itgp_id INT NOT NULL,
    posted_by INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS general_teacher_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    general_teacher_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (general_teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (student_id, general_teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (53);

-- END MIGRATION: v53

-- ============================================
-- MIGRATION: v54 - Process 13 Class Placements & Mainstream Status
-- ============================================

ALTER TABLE student_records ADD COLUMN status ENUM('active','mainstreamed') NOT NULL DEFAULT 'active';

CREATE TABLE IF NOT EXISTS class_placements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    itgp_id INT NOT NULL,
    reviewed_by INT NOT NULL,
    status ENUM('confirmed','on_hold') NOT NULL DEFAULT 'confirmed',
    hold_reason TEXT NULL,
    confirmed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (54);

-- END MIGRATION: v54

