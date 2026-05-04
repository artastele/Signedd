<?php
// Test form submission directly
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

echo "<h2>Testing Form Submission</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";

// Simulate a minimal enrollment submission
$_POST = [
    'enrollment_type' => 'new',
    'school_year' => '2026-2027',
    'last_name' => 'Test',
    'first_name' => 'Child',
    'birth_date' => '2015-01-01',
    'sex' => 'Male',
    'grade_level_to_enroll' => 'Grade 1',
    'signature_data' => 'data:image/png;base64,test'
];

echo "<h3>Attempting to create enrollment...</h3>";

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../app/Models/EnrollmentModel.php';
    
    $enrollmentModel = new EnrollmentModel();
    
    $data = [
        'parent_id' => $_SESSION['user_id'],
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'is_draft' => false,
        'status' => 'pending',
        'submitted_at' => date('Y-m-d H:i:s'),
        'last_name' => 'Test',
        'first_name' => 'Child',
        'birth_date' => '2015-01-01',
        'sex' => 'Male',
        'grade_level_to_enroll' => 'Grade 1',
        'signature_data' => 'data:image/png;base64,test',
        'date_signed' => date('Y-m-d'),
        
        // All other fields as null
        'previous_enrollment_id' => null,
        'lrn' => null,
        'middle_name' => null,
        'extension_name' => null,
        'age' => null,
        'place_of_birth_city' => null,
        'place_of_birth_province' => null,
        'mother_tongue' => null,
        'is_indigenous_people' => 0,
        'indigenous_group' => null,
        'is_4ps_beneficiary' => 0,
        'fourps_household_id' => null,
        'disability_visual' => 0,
        'disability_hearing' => 0,
        'disability_learning' => 0,
        'disability_speech' => 0,
        'disability_intellectual' => 0,
        'disability_physical' => 0,
        'disability_emotional' => 0,
        'disability_chronic_illness' => 0,
        'disability_others' => 0,
        'disability_others_specify' => null,
        'current_house_no' => null,
        'current_barangay' => null,
        'current_city' => null,
        'current_province' => null,
        'current_region' => null,
        'current_zip_code' => null,
        'same_as_current_address' => 0,
        'permanent_house_no' => null,
        'permanent_barangay' => null,
        'permanent_city' => null,
        'permanent_province' => null,
        'permanent_region' => null,
        'permanent_zip_code' => null,
        'father_last_name' => null,
        'father_first_name' => null,
        'father_middle_name' => null,
        'father_contact_number' => null,
        'mother_maiden_last_name' => null,
        'mother_first_name' => null,
        'mother_middle_name' => null,
        'mother_contact_number' => null,
        'guardian_last_name' => null,
        'guardian_first_name' => null,
        'guardian_middle_name' => null,
        'guardian_contact_number' => null,
        'previous_school_id' => null,
        'previous_school_name' => null,
        'previous_school_address' => null,
        'previous_grade_level' => null,
        'previous_school_year' => null,
        'previous_school_type' => null,
        'is_balik_aral' => 0,
        'is_pept_passer' => 0,
        'pept_rating' => null,
        'is_als_passer' => 0,
        'als_rating' => null,
        'shs_track' => null,
        'shs_strand' => null,
        'shs_semester' => null,
        'modality_modular_print' => 0,
        'modality_modular_digital' => 0,
        'modality_online' => 0,
        'modality_educational_tv' => 0,
        'modality_radio' => 0,
        'modality_blended' => 0,
        'modality_face_to_face' => 0,
        'preferred_distance_modality' => null,
        'draft_saved_at' => null
    ];
    
    echo "<p>Creating enrollment with data...</p>";
    $enrollmentId = $enrollmentModel->create($data);
    
    echo "<p style='color: green;'><strong>✓ SUCCESS!</strong> Enrollment created with ID: {$enrollmentId}</p>";
    
    // Verify it was created
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = :id");
    $stmt->execute(['id' => $enrollmentId]);
    $enrollment = $stmt->fetch();
    
    echo "<h3>Verification:</h3>";
    echo "<pre>";
    print_r($enrollment);
    echo "</pre>";
    
    echo "<p><a href='test-enrollment-submit.php'>Check enrollments again</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='../dashboard'>Back to Dashboard</a></p>";
?>
