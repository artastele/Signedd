<?php
// Test Form POST to EnrollmentController

// Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Start session
session_start();

// Set up session as parent
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'parent';
$_SESSION['user_name'] = 'Test Parent';

echo "<h1>Test Enrollment Form Submission</h1>";
echo "<hr>";

echo "<h2>Simulating Form POST to /enrollment/submit</h2>";

// Simulate form data
$_POST = [
    'enrollment_type' => 'new',
    'school_year' => '2026-2027',
    
    // Step 1: Learner Info
    'lrn' => '',
    'last_name' => 'Dela Cruz',
    'first_name' => 'Juan',
    'middle_name' => 'Santos',
    'extension_name' => '',
    'birth_date' => '2010-05-15',
    'sex' => 'Male',
    'age' => '16',
    'birth_place' => 'Cebu City, Cebu',
    'mother_tongue' => 'Cebuano',
    'is_indigenous_people' => '',
    'indigenous_group' => '',
    'is_4ps_beneficiary' => '',
    'fourps_household_id' => '',
    
    // Disabilities
    'disability_visual' => '',
    'disability_hearing' => '',
    'disability_learning' => '1',
    'disability_speech' => '',
    'disability_intellectual' => '',
    'disability_physical' => '',
    'disability_emotional' => '',
    'disability_chronic_illness' => '',
    'disability_others' => '',
    'disability_others_specify' => '',
    
    // Step 2: Current Address
    'current_house_no' => '123 Main St',
    'current_barangay' => 'Lahug',
    'current_city' => 'Cebu City',
    'current_province' => 'Cebu',
    'current_zip_code' => '6000',
    
    // Permanent Address
    'same_as_current_address' => '1',
    'permanent_house_no' => '123 Main St',
    'permanent_barangay' => 'Lahug',
    'permanent_city' => 'Cebu City',
    'permanent_province' => 'Cebu',
    'permanent_zip_code' => '6000',
    
    // Step 3: Parent/Guardian
    'father_last_name' => 'Dela Cruz',
    'father_first_name' => 'Pedro',
    'father_middle_name' => 'Garcia',
    'father_contact_number' => '09123456789',
    
    'mother_maiden_last_name' => 'Santos',
    'mother_first_name' => 'Maria',
    'mother_middle_name' => 'Lopez',
    'mother_contact_number' => '09187654321',
    
    'guardian_last_name' => '',
    'guardian_first_name' => '',
    'guardian_middle_name' => '',
    'guardian_contact_number' => '',
    
    // Step 4: Previous School (optional for new)
    'previous_school_id' => '',
    'previous_school_name' => '',
    'previous_school_address' => '',
    'previous_grade_level' => '',
    'previous_school_year' => '',
    'previous_school_type' => '',
    
    // Step 5: Enrollment Details
    'grade_level_to_enroll' => 'Grade 7',
    'is_balik_aral' => '',
    'is_pept_passer' => '',
    'pept_rating' => '',
    'is_als_passer' => '',
    'als_rating' => '',
    
    // SHS (if applicable)
    'shs_track' => '',
    'shs_strand' => '',
    'shs_semester' => '',
    
    // Step 6: Learning Modality
    'modality_modular_print' => '',
    'modality_modular_digital' => '',
    'modality_online' => '',
    'modality_educational_tv' => '',
    'modality_radio' => '',
    'modality_blended' => '',
    'modality_face_to_face' => '1',
    'preferred_distance_modality' => '',
    
    // Step 7: Signature
    'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
    'date_signed' => date('Y-m-d')
];

echo "<strong>POST Data Prepared:</strong><br>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<hr><h2>Calling EnrollmentController->submit()</h2>";

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../app/Models/EnrollmentModel.php';
    require_once __DIR__ . '/../app/Controllers/EnrollmentController.php';
    
    $controller = new EnrollmentController();
    
    // Capture output
    ob_start();
    $controller->submit();
    $output = ob_get_clean();
    
    echo "<strong>Controller Output:</strong><br>";
    echo "<pre>$output</pre>";
    
    // Check if enrollment was created
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM enrollment_submissions ORDER BY id DESC LIMIT 1");
    $lastEnrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lastEnrollment) {
        echo "<hr><h2>✅ Last Enrollment in Database:</h2>";
        echo "<table border='1' cellpadding='5'>";
        foreach ($lastEnrollment as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<hr><h2>❌ No enrollments found in database</h2>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb;'>";
    echo "<strong>❌ ERROR:</strong> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<hr><h2>Check Session & Redirects:</h2>";
if (isset($_SESSION['success'])) {
    echo "✅ Success message: " . $_SESSION['success'] . "<br>";
}
if (isset($_SESSION['error'])) {
    echo "❌ Error message: " . $_SESSION['error'] . "<br>";
}
if (isset($_SESSION['errors'])) {
    echo "❌ Errors: <pre>" . print_r($_SESSION['errors'], true) . "</pre>";
}
?>
