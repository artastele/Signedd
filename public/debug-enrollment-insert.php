<?php
// Debug Enrollment Insert - Deep Diagnosis
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/EnrollmentModel.php';

echo "<h1>Enrollment Insert Deep Diagnosis</h1>";
echo "<hr>";

// Set test user session
$_SESSION['user_id'] = 1; // Use existing user ID
$_SESSION['role'] = 'parent';

echo "<h2>1. Database Connection Test</h2>";
try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connected successfully<br>";
    echo "Database: " . $db->query("SELECT DATABASE()")->fetchColumn() . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

echo "<hr><h2>2. Check enrollment_submissions Table Structure</h2>";
try {
    $stmt = $db->query("DESCRIBE enrollment_submissions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for critical columns
    $columnNames = array_column($columns, 'Field');
    echo "<br><strong>Critical Columns Check:</strong><br>";
    echo "birth_place: " . (in_array('birth_place', $columnNames) ? '✅' : '❌') . "<br>";
    echo "current_region: " . (in_array('current_region', $columnNames) ? '❌ SHOULD NOT EXIST' : '✅ Correctly removed') . "<br>";
    echo "permanent_region: " . (in_array('permanent_region', $columnNames) ? '❌ SHOULD NOT EXIST' : '✅ Correctly removed') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr><h2>3. Test Minimal Insert (Direct SQL)</h2>";
try {
    $testData = [
        'parent_id' => 1,
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'last_name' => 'Test',
        'first_name' => 'Student',
        'birth_date' => '2010-01-01',
        'sex' => 'Male',
        'age' => 16,
        'birth_place' => 'Cebu City',
        'grade_level_to_enroll' => 'Grade 7',
        'status' => 'draft',
        'is_draft' => true
    ];
    
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, last_name, first_name, 
        birth_date, sex, age, birth_place, grade_level_to_enroll, status, is_draft
    ) VALUES (
        :parent_id, :enrollment_type, :school_year, :last_name, :first_name,
        :birth_date, :sex, :age, :birth_place, :grade_level_to_enroll, :status, :is_draft
    )";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $insertId = $db->lastInsertId();
        echo "✅ <strong>SUCCESS!</strong> Minimal insert worked!<br>";
        echo "Inserted ID: <strong>$insertId</strong><br>";
        
        // Verify it's in database
        $verify = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch(PDO::FETCH_ASSOC);
        if ($verify) {
            echo "✅ Verified in database:<br>";
            echo "<pre>" . print_r($verify, true) . "</pre>";
        }
    } else {
        echo "❌ Insert failed but no exception thrown<br>";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><h2>4. Test EnrollmentModel->create()</h2>";
try {
    $model = new EnrollmentModel();
    
    $testData = [
        'parent_id' => 1,
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'previous_enrollment_id' => null,
        'is_draft' => true,
        'status' => 'draft',
        'lrn' => null,
        'last_name' => 'ModelTest',
        'first_name' => 'Student',
        'middle_name' => null,
        'extension_name' => null,
        'birth_date' => '2010-01-01',
        'sex' => 'Male',
        'age' => 16,
        'birth_place' => 'Cebu City',
        'mother_tongue' => 'Cebuano',
        'is_indigenous_people' => false,
        'indigenous_group' => null,
        'is_4ps_beneficiary' => false,
        'fourps_household_id' => null,
        'disability_visual' => false,
        'disability_hearing' => false,
        'disability_learning' => false,
        'disability_speech' => false,
        'disability_intellectual' => false,
        'disability_physical' => false,
        'disability_emotional' => false,
        'disability_chronic_illness' => false,
        'disability_others' => false,
        'disability_others_specify' => null,
        'current_house_no' => '123',
        'current_barangay' => 'Lahug',
        'current_city' => 'Cebu City',
        'current_province' => 'Cebu',
        'current_zip_code' => '6000',
        'same_as_current_address' => true,
        'permanent_house_no' => '123',
        'permanent_barangay' => 'Lahug',
        'permanent_city' => 'Cebu City',
        'permanent_province' => 'Cebu',
        'permanent_zip_code' => '6000',
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
        'grade_level_to_enroll' => 'Grade 7',
        'is_balik_aral' => false,
        'is_pept_passer' => false,
        'pept_rating' => null,
        'is_als_passer' => false,
        'als_rating' => null,
        'shs_track' => null,
        'shs_strand' => null,
        'shs_semester' => null,
        'modality_modular_print' => false,
        'modality_modular_digital' => false,
        'modality_online' => false,
        'modality_educational_tv' => false,
        'modality_radio' => false,
        'modality_blended' => false,
        'modality_face_to_face' => true,
        'preferred_distance_modality' => null,
        'signature_data' => null,
        'date_signed' => date('Y-m-d'),
        'draft_saved_at' => date('Y-m-d H:i:s'),
        'submitted_at' => null
    ];
    
    echo "Attempting to insert via EnrollmentModel->create()...<br>";
    $insertId = $model->create($testData);
    
    if ($insertId) {
        echo "✅ <strong>SUCCESS!</strong> Model insert worked!<br>";
        echo "Inserted ID: <strong>$insertId</strong><br>";
    } else {
        echo "❌ Model insert returned false/0<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>MODEL ERROR:</strong> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><h2>5. Check All Enrollments in Database</h2>";
try {
    $stmt = $db->query("SELECT id, parent_id, first_name, last_name, birth_place, status, created_at FROM enrollment_submissions ORDER BY id DESC LIMIT 10");
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($enrollments) > 0) {
        echo "Found " . count($enrollments) . " enrollment(s):<br><br>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Parent ID</th><th>Name</th><th>Birth Place</th><th>Status</th><th>Created</th></tr>";
        foreach ($enrollments as $e) {
            echo "<tr>";
            echo "<td>{$e['id']}</td>";
            echo "<td>{$e['parent_id']}</td>";
            echo "<td>{$e['first_name']} {$e['last_name']}</td>";
            echo "<td>{$e['birth_place']}</td>";
            echo "<td>{$e['status']}</td>";
            echo "<td>{$e['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No enrollments found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr><h2>6. Test Form Submission Flow</h2>";
echo "<p>If the tests above work but the actual form doesn't, the issue is in:</p>";
echo "<ul>";
echo "<li>EnrollmentController->submit() method</li>";
echo "<li>JavaScript form submission (enrollment.js)</li>";
echo "<li>Form data preparation</li>";
echo "</ul>";

echo "<hr><h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If Test #3 (Direct SQL) works → Database is fine</li>";
echo "<li>If Test #4 (Model) works → Model is fine</li>";
echo "<li>If both work but form doesn't → Issue is in Controller or JavaScript</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Check PHP error logs for controller errors</li>";
echo "</ol>";
?>
