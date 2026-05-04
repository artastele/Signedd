<?php
// Enrollment Debug Script
// This script tests the enrollment submission process step by step

session_start();
define('BASE_PATH', '');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/EnrollmentModel.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Enrollment Submission Debug</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ No user logged in. Please log in first.</p>";
    echo "<p><a href='/login'>Go to Login</a></p>";
    exit;
}

echo "<p>✅ User logged in: ID = {$_SESSION['user_id']}, Role = {$_SESSION['role']}</p>";

// Test database connection
try {
    $db = Database::getInstance()->getConnection();
    echo "<p>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test minimal enrollment data
$testData = [
    'parent_id' => $_SESSION['user_id'],
    'enrollment_type' => 'new',
    'school_year' => '2026-2027',
    'previous_enrollment_id' => null,
    'is_draft' => false,
    'status' => 'pending',
    
    // Required fields
    'lrn' => null,
    'last_name' => 'Test',
    'first_name' => 'Student',
    'middle_name' => null,
    'extension_name' => null,
    'birth_date' => '2010-01-01',
    'sex' => 'Male',
    'age' => 16,
    'birth_place' => null,
    'mother_tongue' => null,
    'is_indigenous_people' => 0,
    'indigenous_group' => null,
    'is_4ps_beneficiary' => 0,
    'fourps_household_id' => null,
    
    // Disabilities
    'disability_visual' => 0,
    'disability_hearing' => 0,
    'disability_learning' => 1,
    'disability_speech' => 0,
    'disability_intellectual' => 0,
    'disability_physical' => 0,
    'disability_emotional' => 0,
    'disability_chronic_illness' => 0,
    'disability_others' => 0,
    'disability_others_specify' => null,
    
    // Current Address
    'current_house_no' => '123',
    'current_barangay' => 'Test Barangay',
    'current_city' => 'Test City',
    'current_province' => 'Test Province',
    'current_zip_code' => '1234',
    
    // Permanent Address
    'same_as_current_address' => 1,
    'permanent_house_no' => null,
    'permanent_barangay' => null,
    'permanent_city' => null,
    'permanent_province' => null,
    'permanent_zip_code' => null,
    
    // Parent/Guardian
    'father_last_name' => null,
    'father_first_name' => null,
    'father_middle_name' => null,
    'father_contact_number' => null,
    'mother_maiden_last_name' => 'Mother',
    'mother_first_name' => 'Test',
    'mother_middle_name' => null,
    'mother_contact_number' => '09123456789',
    'guardian_last_name' => null,
    'guardian_first_name' => null,
    'guardian_middle_name' => null,
    'guardian_contact_number' => null,
    
    // Previous School
    'previous_school_id' => null,
    'previous_school_name' => null,
    'previous_school_address' => null,
    'previous_grade_level' => null,
    'previous_school_year' => null,
    'previous_school_type' => null,
    
    // Enrollment Details
    'grade_level_to_enroll' => 'Grade 7',
    'is_balik_aral' => 0,
    'is_pept_passer' => 0,
    'pept_rating' => null,
    'is_als_passer' => 0,
    'als_rating' => null,
    
    // SHS
    'shs_track' => null,
    'shs_strand' => null,
    'shs_semester' => null,
    
    // Learning Modality
    'modality_modular_print' => 1,
    'modality_modular_digital' => 0,
    'modality_online' => 0,
    'modality_educational_tv' => 0,
    'modality_radio' => 0,
    'modality_blended' => 0,
    'modality_face_to_face' => 0,
    'preferred_distance_modality' => 'Modular (Print)',
    
    // Signature
    'signature_data' => 'data:image/png;base64,test',
    'date_signed' => date('Y-m-d'),
    
    // Timestamps
    'draft_saved_at' => null,
    'submitted_at' => date('Y-m-d H:i:s')
];

echo "<h2>Test Data Prepared</h2>";
echo "<pre>" . print_r($testData, true) . "</pre>";

// Test the model
$enrollmentModel = new EnrollmentModel();

echo "<h2>Attempting to Create Enrollment...</h2>";

try {
    $enrollmentId = $enrollmentModel->create($testData);
    
    if ($enrollmentId) {
        echo "<p style='color: green; font-size: 20px;'>✅ SUCCESS! Enrollment created with ID: $enrollmentId</p>";
        
        // Verify it was saved
        $saved = $enrollmentModel->findById($enrollmentId);
        if ($saved) {
            echo "<h3>Saved Data:</h3>";
            echo "<pre>" . print_r($saved, true) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ FAILED: create() returned false or null</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ EXCEPTION: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Check error log
echo "<h2>Recent Error Log Entries</h2>";
$logFile = __DIR__ . '/../logs/php_error.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recent = array_slice($lines, -20);
    echo "<pre>" . implode('', $recent) . "</pre>";
} else {
    echo "<p>No error log file found at: $logFile</p>";
    echo "<p>Check your PHP error_log configuration</p>";
}
