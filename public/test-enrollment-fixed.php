<?php
// Test if enrollment submission is now working
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/EnrollmentModel.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTING ENROLLMENT FIX ===\n\n";

try {
    $enrollmentModel = new EnrollmentModel();
    
    // Count before
    $db = Database::getInstance()->getConnection();
    $beforeCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "1. Records before test: $beforeCount\n\n";
    
    // Prepare test data (simulating a real enrollment submission)
    $testData = [
        'parent_id' => 2,
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'previous_enrollment_id' => null,
        'is_draft' => false,
        'status' => 'pending',
        
        // Required fields
        'lrn' => '',
        'last_name' => 'Test',
        'first_name' => 'Student',
        'middle_name' => 'Middle',
        'extension_name' => null,
        'birth_date' => '2020-01-01',
        'sex' => 'Male',
        'age' => 6,
        'birth_place' => 'Test City',
        'mother_tongue' => 'Cebuano',
        
        // Checkboxes (all false for test)
        'is_indigenous_people' => 0,
        'indigenous_group' => null,
        'is_4ps_beneficiary' => 0,
        'fourps_household_id' => null,
        
        // Disabilities
        'disability_visual' => 0,
        'disability_hearing' => 1,
        'disability_learning' => 0,
        'disability_speech' => 0,
        'disability_intellectual' => 0,
        'disability_physical' => 0,
        'disability_emotional' => 0,
        'disability_chronic_illness' => 0,
        'disability_others' => 0,
        'disability_others_specify' => null,
        
        // Address
        'current_house_no' => 'Test Address',
        'current_barangay' => 'Test Barangay',
        'current_city' => 'Davao City',
        'current_province' => 'Davao del Sur',
        'current_zip_code' => '8000',
        'same_as_current_address' => 1,
        'permanent_house_no' => 'Test Address',
        'permanent_barangay' => 'Test Barangay',
        'permanent_city' => 'Davao City',
        'permanent_province' => 'Davao del Sur',
        'permanent_zip_code' => '8000',
        
        // Parents
        'father_last_name' => 'Father',
        'father_first_name' => 'Test',
        'father_middle_name' => null,
        'father_contact_number' => '09123456789',
        'mother_maiden_last_name' => 'Mother',
        'mother_first_name' => 'Test',
        'mother_middle_name' => null,
        'mother_contact_number' => '09123456789',
        'guardian_last_name' => null,
        'guardian_first_name' => null,
        'guardian_middle_name' => null,
        'guardian_contact_number' => null,
        
        // Previous school
        'previous_school_id' => null,
        'previous_school_name' => null,
        'previous_school_address' => null,
        'previous_grade_level' => null,
        'previous_school_year' => null,
        'previous_school_type' => null,
        
        // Enrollment details - THIS IS THE KEY FIELD!
        'grade_level_to_enroll' => 'SPED Program',
        'is_balik_aral' => 0,
        'is_pept_passer' => 0,
        'pept_rating' => null,
        'is_als_passer' => 0,
        'als_rating' => null,
        'shs_track' => null,
        'shs_strand' => null,
        'shs_semester' => null,
        
        // Modality
        'modality_modular_print' => 0,
        'modality_modular_digital' => 1,
        'modality_online' => 0,
        'modality_educational_tv' => 0,
        'modality_radio' => 0,
        'modality_blended' => 0,
        'modality_face_to_face' => 0,
        'preferred_distance_modality' => null,
        
        // Signature
        'signature_data' => 'data:image/png;base64,test',
        'date_signed' => date('Y-m-d'),
        'draft_saved_at' => null,
        'submitted_at' => date('Y-m-d H:i:s'),
        'verified_by' => null,
        'verified_at' => null,
    ];
    
    echo "2. Creating enrollment...\n";
    $enrollmentId = $enrollmentModel->create($testData);
    echo "   ✓ Enrollment created with ID: $enrollmentId\n\n";
    
    // Count after
    $afterCount = $db->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "3. Records after test: $afterCount\n";
    echo "   Difference: " . ($afterCount - $beforeCount) . "\n\n";
    
    // Verify the record
    $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = ?");
    $stmt->execute([$enrollmentId]);
    $record = $stmt->fetch();
    
    if ($record) {
        echo "4. ✓ VERIFICATION SUCCESSFUL!\n";
        echo "   Student: {$record['first_name']} {$record['last_name']}\n";
        echo "   Grade: {$record['grade_level_to_enroll']}\n";
        echo "   Status: {$record['status']}\n";
        echo "   Is Draft: " . ($record['is_draft'] ? 'YES' : 'NO') . "\n";
        echo "   Submitted: {$record['submitted_at']}\n\n";
        
        echo "=== RESULT ===\n";
        echo "✓✓✓ ENROLLMENT SYSTEM IS NOW WORKING! ✓✓✓\n";
        echo "The fix has been applied successfully.\n";
        echo "You can now submit enrollments through the form.\n";
    } else {
        echo "4. ❌ VERIFICATION FAILED!\n";
        echo "   Record was not found in database.\n";
        echo "   The issue persists.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
