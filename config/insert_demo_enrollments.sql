-- ============================================================
-- DEMO DAY SEED DATA: 2 Pending Learner Enrollments
-- Run this in InfinityFree phpMyAdmin or local HeidiSQL / MySQL
-- ============================================================

-- 1. Ensure Parent User 1 exists (Maria Dela Cruz)
INSERT INTO users (name, first_name, last_name, email, password_hash, role, status, email_verified, auth_provider)
VALUES ('Maria Dela Cruz', 'Maria', 'Dela Cruz', 'parent_demojuan@spedlms.local', '$2y$10$abcdefghijklmnopqrstuuvwxyz123456', 'parent', 'active', TRUE, 'local')
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

SET @parent1_id = LAST_INSERT_ID();

-- 2. Ensure Parent User 2 exists (Roberto Santos)
INSERT INTO users (name, first_name, last_name, email, password_hash, role, status, email_verified, auth_provider)
VALUES ('Roberto Santos', 'Roberto', 'Santos', 'parent_demosophia@spedlms.local', '$2y$10$abcdefghijklmnopqrstuuvwxyz123456', 'parent', 'active', TRUE, 'local')
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

SET @parent2_id = LAST_INSERT_ID();


-- 3. Insert Learner 1: Juan Dela Cruz Jr. (Deaf & Hard of Hearing / DHH)
INSERT INTO enrollment_submissions (
    parent_id, enrollment_type, school_year, is_draft, status, lrn,
    last_name, first_name, middle_name, birth_date, sex, age, birth_place,
    mother_tongue, disability_hearing, current_house_no, current_barangay,
    current_city, current_province, grade_level_to_enroll,
    mother_maiden_last_name, mother_first_name, mother_contact_number,
    submitted_at, created_at
) VALUES (
    @parent1_id, 'new', '2026-2027', FALSE, 'pending', '109876543210',
    'Dela Cruz', 'Juan', 'Reyes', '2017-05-14', 'Male', 9, 'Cebu City',
    'Cebuano', 1, '123 Sampaguita St.', 'Poblacion',
    'Cebu City', 'Cebu', 'Grade 1 - SPED',
    'Reyes', 'Maria', '09171234567',
    NOW(), NOW()
);

SET @enrollment1_id = LAST_INSERT_ID();

-- Insert documents for Learner 1
INSERT INTO enrollment_documents (enrollment_id, document_type, file_path, status, uploaded_at)
VALUES 
(@enrollment1_id, 'psa_birth_cert', 'uploads/enrollments/sample_psa.pdf', 'pending', NOW()),
(@enrollment1_id, 'pwd_id', 'uploads/enrollments/sample_pwd.pdf', 'pending', NOW());


-- 4. Insert Learner 2: Sophia Santos (Learning Disability)
INSERT INTO enrollment_submissions (
    parent_id, enrollment_type, school_year, is_draft, status, lrn,
    last_name, first_name, middle_name, birth_date, sex, age, birth_place,
    mother_tongue, disability_learning, current_house_no, current_barangay,
    current_city, current_province, grade_level_to_enroll,
    father_last_name, father_first_name, father_contact_number,
    submitted_at, created_at
) VALUES (
    @parent2_id, 'new', '2026-2027', FALSE, 'pending', '109876543211',
    'Santos', 'Sophia', 'Flores', '2016-08-22', 'Female', 10, 'Mandaue City',
    'Cebuano', 1, '456 Acacia Ave.', 'Subangdaku',
    'Mandaue City', 'Cebu', 'Grade 3 - SPED',
    'Santos', 'Roberto', '09189876543',
    NOW(), NOW()
);

SET @enrollment2_id = LAST_INSERT_ID();

-- Insert documents for Learner 2
INSERT INTO enrollment_documents (enrollment_id, document_type, file_path, status, uploaded_at)
VALUES 
(@enrollment2_id, 'psa_birth_cert', 'uploads/enrollments/sample_psa.pdf', 'pending', NOW()),
(@enrollment2_id, 'medical_record', 'uploads/enrollments/sample_medical.pdf', 'pending', NOW());
