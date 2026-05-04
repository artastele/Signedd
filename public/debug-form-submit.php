<?php
// Debug form submission - show EXACTLY what's happening
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    die('Must be logged in as parent');
}

echo "<h1>Debug Form Submission</h1>";
echo "<p>This will show you EXACTLY what happens when you submit the enrollment form.</p>";

require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

// Get actual database columns
$stmt = $db->query("DESCRIBE enrollment_submissions");
$dbColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<h2>Database Columns (" . count($dbColumns) . " total):</h2>";
echo "<div style='background: #f0f0f0; padding: 10px; max-height: 200px; overflow-y: scroll;'>";
echo implode(', ', $dbColumns);
echo "</div>";

// Get columns that the CODE tries to insert
require_once __DIR__ . '/../app/Controllers/EnrollmentController.php';

echo "<hr><h2>Columns the CODE tries to insert:</h2>";

// Simulate what prepareEnrollmentData returns
$codeColumns = [
    'parent_id', 'enrollment_type', 'school_year', 'previous_enrollment_id',
    'lrn', 'last_name', 'first_name', 'middle_name', 'extension_name', 
    'birth_date', 'sex', 'age', 'place_of_birth_city', 'place_of_birth_province',
    'mother_tongue', 'is_indigenous_people', 'indigenous_group', 
    'is_4ps_beneficiary', 'fourps_household_id',
    'disability_visual', 'disability_hearing', 'disability_learning', 
    'disability_speech', 'disability_intellectual', 'disability_physical',
    'disability_emotional', 'disability_chronic_illness', 'disability_others',
    'disability_others_specify',
    'current_house_no', 'current_barangay', 'current_city', 'current_province',
    'current_region', 'current_zip_code',
    'same_as_current_address', 'permanent_house_no', 'permanent_barangay',
    'permanent_city', 'permanent_province', 'permanent_region', 'permanent_zip_code',
    'father_last_name', 'father_first_name', 'father_middle_name', 'father_contact_number',
    'mother_maiden_last_name', 'mother_first_name', 'mother_middle_name', 'mother_contact_number',
    'guardian_last_name', 'guardian_first_name', 'guardian_middle_name', 'guardian_contact_number',
    'previous_school_id', 'previous_school_name', 'previous_school_address',
    'previous_grade_level', 'previous_school_year', 'previous_school_type',
    'grade_level_to_enroll', 'is_balik_aral', 'is_pept_passer', 'pept_rating',
    'is_als_passer', 'als_rating',
    'shs_track', 'shs_strand', 'shs_semester',
    'modality_modular_print', 'modality_modular_digital', 'modality_online',
    'modality_educational_tv', 'modality_radio', 'modality_blended', 'modality_face_to_face',
    'preferred_distance_modality',
    'signature_data', 'date_signed', 'draft_saved_at'
];

echo "<div style='background: #fff3cd; padding: 10px; max-height: 200px; overflow-y: scroll;'>";
echo implode(', ', $codeColumns);
echo "</div>";

// Find mismatches
echo "<hr><h2>MISMATCHES:</h2>";

$missing = [];
$extra = [];

foreach ($codeColumns as $col) {
    if (!in_array($col, $dbColumns)) {
        $missing[] = $col;
    }
}

foreach ($dbColumns as $col) {
    if (!in_array($col, $codeColumns) && !in_array($col, ['id', 'created_at', 'updated_at', 'verified_by', 'verified_at', 'is_draft', 'status', 'submitted_at'])) {
        $extra[] = $col;
    }
}

if (!empty($missing)) {
    echo "<h3 style='color: red;'>❌ Columns CODE expects but DATABASE doesn't have:</h3>";
    echo "<ul style='color: red;'>";
    foreach ($missing as $col) {
        echo "<li><strong>$col</strong></li>";
    }
    echo "</ul>";
    echo "<p style='color: red; font-weight: bold;'>THIS IS THE PROBLEM! The code is trying to insert into columns that don't exist!</p>";
}

if (!empty($extra)) {
    echo "<h3 style='color: orange;'>⚠️ Columns DATABASE has but CODE doesn't use:</h3>";
    echo "<ul style='color: orange;'>";
    foreach ($extra as $col) {
        echo "<li>$col</li>";
    }
    echo "</ul>";
    echo "<p style='color: orange;'>These columns exist but aren't being used by the code (usually okay).</p>";
}

if (empty($missing) && empty($extra)) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ Perfect match! All columns align.</p>";
}

// Show the fix
if (!empty($missing)) {
    echo "<hr><h2>HOW TO FIX:</h2>";
    echo "<p>You need to update the <code>prepareEnrollmentData()</code> method in EnrollmentController.php</p>";
    echo "<p><strong>Option 1:</strong> Remove these columns from the code (if they're old fields)</p>";
    echo "<p><strong>Option 2:</strong> Add these columns to the database (if they're new fields)</p>";
    
    echo "<h3>Quick Fix SQL:</h3>";
    echo "<textarea style='width: 100%; height: 200px; font-family: monospace;'>";
    foreach ($missing as $col) {
        // Guess the column type
        $type = 'VARCHAR(255)';
        if (strpos($col, 'is_') === 0) $type = 'TINYINT(1) DEFAULT 0';
        if (strpos($col, '_date') !== false) $type = 'DATE';
        if (strpos($col, '_at') !== false) $type = 'DATETIME';
        if ($col === 'age') $type = 'INT';
        
        echo "ALTER TABLE enrollment_submissions ADD COLUMN IF NOT EXISTS $col $type NULL;\n";
    }
    echo "</textarea>";
    
    echo "<p><strong>Copy the SQL above and run it in phpMyAdmin!</strong></p>";
}

echo "<hr>";
echo "<p><a href='../dashboard'>Back to Dashboard</a></p>";
?>
