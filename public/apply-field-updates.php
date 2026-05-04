<?php
// Apply field updates for v0.11 changes
// This adds birth_place and removes region fields

require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

echo "<h1>Applying Field Updates (v0.11)</h1>";
echo "<p>This will update the enrollment_submissions table to match the latest code.</p>";

$errors = [];
$success = [];

// 1. Add birth_place column
echo "<h2>1. Adding birth_place column...</h2>";
try {
    $db->exec("ALTER TABLE enrollment_submissions ADD COLUMN IF NOT EXISTS birth_place VARCHAR(255) NULL AFTER age");
    $success[] = "✓ Added birth_place column";
    echo "<p style='color: green;'>✓ Added birth_place column</p>";
} catch (Exception $e) {
    $errors[] = "birth_place: " . $e->getMessage();
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// 2. Check if old columns exist (place_of_birth_city, place_of_birth_province)
echo "<h2>2. Checking old birth place columns...</h2>";
try {
    $stmt = $db->query("DESCRIBE enrollment_submissions");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('place_of_birth_city', $columns)) {
        echo "<p>Found place_of_birth_city (old column)</p>";
    }
    if (in_array('place_of_birth_province', $columns)) {
        echo "<p>Found place_of_birth_province (old column)</p>";
    }
    
    // Note: We don't drop these columns to preserve any existing data
    echo "<p style='color: blue;'><em>Note: Old columns are kept for data preservation</em></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// 3. Verify the table structure
echo "<h2>3. Current Table Structure:</h2>";
try {
    $stmt = $db->query("DESCRIBE enrollment_submissions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total columns:</strong> " . count($columns) . "</p>";
    
    // Check for critical columns
    $criticalColumns = [
        'id', 'parent_id', 'enrollment_type', 'school_year', 
        'first_name', 'last_name', 'birth_date', 'sex', 'age', 'birth_place',
        'grade_level_to_enroll', 'status', 'is_draft', 'submitted_at', 
        'signature_data', 'date_signed', 'created_at'
    ];
    
    $columnNames = array_column($columns, 'Field');
    
    echo "<h3>Critical Columns Check:</h3>";
    $allGood = true;
    foreach ($criticalColumns as $col) {
        if (in_array($col, $columnNames)) {
            echo "<span style='color: green;'>✓</span> $col<br>";
        } else {
            echo "<span style='color: red;'>✗</span> $col <strong>MISSING!</strong><br>";
            $allGood = false;
        }
    }
    
    if ($allGood) {
        echo "<p style='color: green; font-weight: bold; margin-top: 20px;'>✓ All critical columns present!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// 4. Test insert
echo "<hr><h2>4. Testing Enrollment Insert...</h2>";
try {
    $testData = [
        'parent_id' => 2,
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'is_draft' => 0,
        'status' => 'pending',
        'submitted_at' => date('Y-m-d H:i:s'),
        'first_name' => 'TestChild',
        'last_name' => 'AfterFix',
        'birth_date' => '2015-01-01',
        'birth_place' => 'Cebu City, Cebu',
        'sex' => 'Male',
        'age' => 11,
        'grade_level_to_enroll' => 'Grade 1',
        'signature_data' => 'test_signature',
        'date_signed' => date('Y-m-d')
    ];
    
    $cols = array_keys($testData);
    $placeholders = array_map(function($c) { return ":$c"; }, $cols);
    
    $sql = "INSERT INTO enrollment_submissions (" . implode(', ', $cols) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $id = $db->lastInsertId();
        echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS! Test enrollment created with ID: $id</p>";
        echo "<p>The enrollment form should now work correctly!</p>";
        
        // Show the inserted data
        $stmt = $db->prepare("SELECT id, first_name, last_name, birth_place, status, submitted_at FROM enrollment_submissions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Inserted Data:</h3>";
        echo "<table border='1' cellpadding='5'>";
        foreach ($enrollment as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>INSERT FAILED:</strong> " . $e->getMessage() . "</p>";
    $errors[] = "Test insert failed: " . $e->getMessage();
}

// Summary
echo "<hr><h2>Summary:</h2>";
if (empty($errors)) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ All updates applied successfully!</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Go to the enrollment form and try submitting again</li>";
    echo "<li>Check your dashboard - enrollments should now appear</li>";
    echo "<li>If you still have issues, check browser console for JavaScript errors</li>";
    echo "</ol>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Some errors occurred:</p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li style='color: red;'>$error</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='diagnose-enrollment.php'>Run Diagnosis</a> | ";
echo "<a href='test-enrollment-submit.php'>Check Enrollments</a> | ";
echo "<a href='../dashboard'>Back to Dashboard</a></p>";
?>
