<?php
// Migration v6 - Apply enrollment_submissions table changes
// Run this once to update the database structure

require_once __DIR__ . '/../config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>Applying Migration v6 - Enrollment System</h1>";
    echo "<hr>";
    
    // Disable foreign key checks
    echo "<h2>Step 1: Disabling foreign key checks...</h2>";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "✅ Foreign key checks disabled<br>";
    
    // Check current structure
    echo "<h2>Step 2: Checking current table structure...</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'enrollment_submissions'");
    if ($stmt->rowCount() > 0) {
        $stmt = $db->query("DESCRIBE enrollment_submissions");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Current columns: " . count($columns) . "<br>";
    } else {
        echo "Table doesn't exist yet<br>";
    }
    
    // Drop old tables
    echo "<h2>Step 3: Dropping old tables...</h2>";
    $db->exec("DROP TABLE IF EXISTS enrollment_documents");
    echo "✅ Dropped enrollment_documents<br>";
    
    $db->exec("DROP TABLE IF EXISTS enrollment_submissions");
    echo "✅ Dropped enrollment_submissions<br>";
    
    // Recreate with new structure
    echo "<h2>Step 3: Creating new enrollment_submissions table...</h2>";
    $sql = "CREATE TABLE IF NOT EXISTS enrollment_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_id INT NOT NULL,
        
        -- Enrollment metadata
        enrollment_type ENUM('new', 'transfer', 'returning') NOT NULL,
        school_year VARCHAR(20) NOT NULL,
        previous_enrollment_id INT NULL,
        is_draft BOOLEAN DEFAULT TRUE,
        status ENUM('draft', 'pending', 'verified', 'rejected') DEFAULT 'draft',
        
        -- Section 1: Learner Information
        lrn VARCHAR(12),
        last_name VARCHAR(100) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100),
        extension_name VARCHAR(20),
        birth_date DATE NOT NULL,
        sex ENUM('Male', 'Female') NOT NULL,
        age INT,
        place_of_birth_city VARCHAR(100),
        place_of_birth_province VARCHAR(100),
        mother_tongue VARCHAR(100),
        is_indigenous_people BOOLEAN DEFAULT FALSE,
        indigenous_group VARCHAR(100),
        is_4ps_beneficiary BOOLEAN DEFAULT FALSE,
        fourps_household_id VARCHAR(50),
        
        -- Disabilities
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
        
        -- Section 2: Current Address
        current_house_no VARCHAR(255),
        current_barangay VARCHAR(100),
        current_city VARCHAR(100),
        current_province VARCHAR(100),
        current_region VARCHAR(100),
        current_zip_code VARCHAR(10),
        
        -- Section 3: Permanent Address
        same_as_current_address BOOLEAN DEFAULT FALSE,
        permanent_house_no VARCHAR(255),
        permanent_barangay VARCHAR(100),
        permanent_city VARCHAR(100),
        permanent_province VARCHAR(100),
        permanent_region VARCHAR(100),
        permanent_zip_code VARCHAR(10),
        
        -- Section 4: Parent/Guardian Information
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
        
        -- Section 5: Previous School Information
        previous_school_id VARCHAR(50),
        previous_school_name VARCHAR(255),
        previous_school_address TEXT,
        previous_grade_level VARCHAR(50),
        previous_school_year VARCHAR(20),
        previous_school_type ENUM('Public', 'Private'),
        
        -- Section 6: Enrollment Details
        grade_level_to_enroll VARCHAR(50) NOT NULL,
        is_balik_aral BOOLEAN DEFAULT FALSE,
        is_pept_passer BOOLEAN DEFAULT FALSE,
        pept_rating VARCHAR(20),
        is_als_passer BOOLEAN DEFAULT FALSE,
        als_rating VARCHAR(20),
        
        -- Section 7: Senior High School
        shs_track VARCHAR(50),
        shs_strand VARCHAR(100),
        shs_semester ENUM('1st', '2nd'),
        
        -- Section 8: Learning Modality
        modality_modular_print BOOLEAN DEFAULT FALSE,
        modality_modular_digital BOOLEAN DEFAULT FALSE,
        modality_online BOOLEAN DEFAULT FALSE,
        modality_educational_tv BOOLEAN DEFAULT FALSE,
        modality_radio BOOLEAN DEFAULT FALSE,
        modality_blended BOOLEAN DEFAULT FALSE,
        modality_face_to_face BOOLEAN DEFAULT FALSE,
        
        -- Section 9: Preferred Distance Learning
        preferred_distance_modality VARCHAR(50),
        
        -- Section 10: Signature
        signature_data TEXT,
        date_signed DATE,
        
        -- Timestamps
        draft_saved_at TIMESTAMP NULL,
        submitted_at TIMESTAMP NULL,
        verified_by INT,
        verified_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (previous_enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE SET NULL,
        INDEX idx_status (status),
        INDEX idx_parent_id (parent_id),
        INDEX idx_enrollment_type (enrollment_type),
        INDEX idx_school_year (school_year)
    )";
    
    $db->exec($sql);
    echo "✅ Created enrollment_submissions table<br>";
    
    // Create enrollment_documents table
    echo "<h2>Step 4: Creating enrollment_documents table...</h2>";
    $sql = "CREATE TABLE IF NOT EXISTS enrollment_documents (
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
    )";
    
    $db->exec($sql);
    echo "✅ Created enrollment_documents table<br>";
    
    // Update db_version
    echo "<h2>Step 5: Updating database version...</h2>";
    $stmt = $db->prepare("INSERT INTO db_version (version) VALUES (6) ON DUPLICATE KEY UPDATE version=6");
    $stmt->execute();
    echo "✅ Database version updated to 6<br>";
    
    // Verify new structure
    echo "<h2>Step 6: Verifying new structure...</h2>";
    $stmt = $db->query("DESCRIBE enrollment_submissions");
    $newColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "New columns: " . count($newColumns) . "<br>";
    
    if (in_array('is_draft', $newColumns)) {
        echo "✅ <strong>is_draft column exists!</strong><br>";
    }
    
    if (in_array('enrollment_type', $newColumns)) {
        echo "✅ <strong>enrollment_type column exists!</strong><br>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Migration v6 Applied Successfully!</h2>";
    echo "<p><a href='../enrollment'>Go to Enrollment Page</a></p>";
    
    // Re-enable foreign key checks
    echo "<h2>Step 7: Re-enabling foreign key checks...</h2>";
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Foreign key checks re-enabled<br>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    // Re-enable foreign key checks even on error
    try {
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    } catch (Exception $e2) {
        // Ignore
    }
}
?>
