<?php
// Fix enrollment_submissions table structure

require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

echo "<h1>Fix Enrollment Table Structure</h1>";

// Get current table structure
echo "<h2>Current Table Structure:</h2>";
try {
    $stmt = $db->query("DESCRIBE enrollment_submissions");
    $currentColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Current columns: " . count($currentColumns) . "</p>";
    echo "<ul>";
    foreach ($currentColumns as $col) {
        echo "<li><strong>" . $col['Field'] . "</strong> - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    die("<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>");
}

// Check for birth_place column (new field we added)
$columnNames = array_column($currentColumns, 'Field');

echo "<hr><h2>Checking for New Fields:</h2>";

if (!in_array('birth_place', $columnNames)) {
    echo "<p style='color: orange;'>⚠️ Missing column: <strong>birth_place</strong></p>";
    echo "<p>Adding column...</p>";
    try {
        $db->exec("ALTER TABLE enrollment_submissions ADD COLUMN birth_place VARCHAR(255) NULL AFTER age");
        echo "<p style='color: green;'>✓ Added birth_place column</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ birth_place column exists</p>";
}

// Check if place_of_birth_city and place_of_birth_province still exist (we removed these)
if (in_array('place_of_birth_city', $columnNames) || in_array('place_of_birth_province', $columnNames)) {
    echo "<p style='color: orange;'>⚠️ Old columns still exist (place_of_birth_city, place_of_birth_province)</p>";
    echo "<p>These were replaced by birth_place but won't cause issues.</p>";
}

echo "<hr><h2>Test Insert with Actual Column Names:</h2>";

// Get the actual columns that exist
$stmt = $db->query("DESCRIBE enrollment_submissions");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<p>Will try to insert using only columns that exist in the table...</p>";

// Prepare minimal test data matching ONLY existing columns
$testData = [];
$possibleData = [
    'parent_id' => 2,
    'enrollment_type' => 'new',
    'school_year' => '2026-2027',
    'is_draft' => 0,
    'status' => 'pending',
    'submitted_at' => date('Y-m-d H:i:s'),
    'first_name' => 'FixTest',
    'last_name' => 'Child',
    'birth_date' => '2015-01-01',
    'sex' => 'Male',
    'grade_level_to_enroll' => 'Grade 1',
    'signature_data' => 'test',
    'date_signed' => date('Y-m-d'),
    'birth_place' => 'Cebu City, Cebu',
    'age' => 11
];

// Only include columns that actually exist
foreach ($possibleData as $key => $value) {
    if (in_array($key, $columns)) {
        $testData[$key] = $value;
    } else {
        echo "<p style='color: orange;'>Skipping column: $key (doesn't exist in table)</p>";
    }
}

echo "<p><strong>Columns to insert:</strong> " . implode(', ', array_keys($testData)) . "</p>";

try {
    $cols = array_keys($testData);
    $placeholders = array_map(function($c) { return ":$c"; }, $cols);
    
    $sql = "INSERT INTO enrollment_submissions (" . implode(', ', $cols) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $id = $db->lastInsertId();
        echo "<p style='color: green;'><strong>✓ SUCCESS!</strong> Test enrollment created with ID: $id</p>";
        
        // Verify
        $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Inserted Data:</h3>";
        echo "<pre>";
        print_r($enrollment);
        echo "</pre>";
        
        echo "<p><strong>This enrollment should now appear on the parent dashboard!</strong></p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>INSERT FAILED:</strong> " . $e->getMessage() . "</p>";
    echo "<p>This is likely why your enrollments aren't being saved!</p>";
}

echo "<hr>";
echo "<p><a href='diagnose-enrollment.php'>Run Full Diagnosis</a> | <a href='../dashboard'>Back to Dashboard</a></p>";
?>
