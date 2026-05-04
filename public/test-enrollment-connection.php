<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1 Diagnostic
// Last modified: 2026-05-03
// Part of: SPED LMS — Enrollment Database Connection Test

session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Database Test - SPED LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #28a745; font-weight: bold; }
        .test-fail { color: #dc3545; font-weight: bold; }
        .test-info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">🔍 Enrollment Database Connection Test</h1>
        
        <?php
        $allPassed = true;
        
        // Test 1: Database Connection
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'><strong>Test 1:</strong> Database Connection</div>";
        echo "<div class='card-body'>";
        try {
            $db = Database::getInstance()->getConnection();
            echo "<p class='test-pass'>✓ Database connection successful</p>";
            echo "<p class='test-info'>Connection type: " . get_class($db) . "</p>";
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Database connection failed</p>";
            echo "<p class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $allPassed = false;
        }
        echo "</div></div>";
        
        // Test 2: Check if enrollment_submissions table exists
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'><strong>Test 2:</strong> Table Structure</div>";
        echo "<div class='card-body'>";
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'enrollment_submissions'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                echo "<p class='test-pass'>✓ Table 'enrollment_submissions' exists</p>";
                
                // Get column count
                $stmt = $db->query("DESCRIBE enrollment_submissions");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "<p class='test-info'>Total columns: " . count($columns) . "</p>";
                
                // Show first 10 columns
                echo "<details><summary>View columns (first 10)</summary><pre>";
                foreach (array_slice($columns, 0, 10) as $col) {
                    echo $col['Field'] . " - " . $col['Type'] . " - " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
                }
                echo "</pre></details>";
            } else {
                echo "<p class='test-fail'>✗ Table 'enrollment_submissions' does not exist</p>";
                $allPassed = false;
            }
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Error checking table structure</p>";
            echo "<p class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $allPassed = false;
        }
        echo "</div></div>";
        
        // Test 3: Check required columns
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'><strong>Test 3:</strong> Required Columns</div>";
        echo "<div class='card-body'>";
        try {
            $requiredColumns = [
                'id', 'parent_id', 'enrollment_type', 'school_year', 'is_draft', 'status',
                'last_name', 'first_name', 'birth_date', 'sex', 'grade_level_to_enroll',
                'signature_data', 'submitted_at', 'verified_by', 'verified_at'
            ];
            
            $stmt = $db->query("DESCRIBE enrollment_submissions");
            $existingColumns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
            
            $missingColumns = array_diff($requiredColumns, $existingColumns);
            
            if (empty($missingColumns)) {
                echo "<p class='test-pass'>✓ All required columns exist</p>";
            } else {
                echo "<p class='test-fail'>✗ Missing columns: " . implode(', ', $missingColumns) . "</p>";
                $allPassed = false;
            }
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Error checking columns</p>";
            echo "<p class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $allPassed = false;
        }
        echo "</div></div>";
        
        // Test 4: Test INSERT with minimal data
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'><strong>Test 4:</strong> Test INSERT Operation</div>";
        echo "<div class='card-body'>";
        try {
            // Create a test user if not exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = 'test_parent@test.local' LIMIT 1");
            $stmt->execute();
            $testUser = $stmt->fetch();
            
            if (!$testUser) {
                // Create test parent user
                $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['Test Parent', 'test_parent@test.local', password_hash('test123', PASSWORD_DEFAULT), 'parent', 'active']);
                $testUserId = $db->lastInsertId();
                echo "<p class='test-info'>Created test parent user (ID: $testUserId)</p>";
            } else {
                $testUserId = $testUser['id'];
                echo "<p class='test-info'>Using existing test parent user (ID: $testUserId)</p>";
            }
            
            // Prepare minimal enrollment data
            $testData = [
                'parent_id' => $testUserId,
                'enrollment_type' => 'new',
                'school_year' => '2026-2027',
                'previous_enrollment_id' => null,
                'is_draft' => false,
                'status' => 'pending',
                'lrn' => null,
                'last_name' => 'Test',
                'first_name' => 'Student',
                'middle_name' => 'Sample',
                'extension_name' => null,
                'birth_date' => '2010-01-01',
                'sex' => 'Male',
                'age' => 16,
                'birth_place' => 'Test City',
                'mother_tongue' => 'Filipino',
                'is_indigenous_people' => false,
                'indigenous_group' => null,
                'is_4ps_beneficiary' => false,
                'fourps_household_id' => null,
                'disability_visual' => false,
                'disability_hearing' => false,
                'disability_learning' => true,
                'disability_speech' => false,
                'disability_intellectual' => false,
                'disability_physical' => false,
                'disability_emotional' => false,
                'disability_chronic_illness' => false,
                'disability_others' => false,
                'disability_others_specify' => null,
                'current_house_no' => '123',
                'current_barangay' => 'Test Barangay',
                'current_city' => 'Test City',
                'current_province' => 'Test Province',
                'current_zip_code' => '1234',
                'same_as_current_address' => true,
                'permanent_house_no' => '123',
                'permanent_barangay' => 'Test Barangay',
                'permanent_city' => 'Test City',
                'permanent_province' => 'Test Province',
                'permanent_zip_code' => '1234',
                'father_last_name' => 'Test',
                'father_first_name' => 'Father',
                'father_middle_name' => null,
                'father_contact_number' => '09123456789',
                'mother_maiden_last_name' => 'Test',
                'mother_first_name' => 'Mother',
                'mother_middle_name' => null,
                'mother_contact_number' => '09123456789',
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
                'modality_modular_print' => true,
                'modality_modular_digital' => false,
                'modality_online' => false,
                'modality_educational_tv' => false,
                'modality_radio' => false,
                'modality_blended' => false,
                'modality_face_to_face' => false,
                'preferred_distance_modality' => 'Modular (Print)',
                'signature_data' => 'data:image/png;base64,test',
                'date_signed' => date('Y-m-d'),
                'draft_saved_at' => null,
                'submitted_at' => date('Y-m-d H:i:s'),
                'verified_by' => null,
                'verified_at' => null
            ];
            
            // Build INSERT query
            $columns = array_keys($testData);
            $placeholders = array_map(function($col) { return ":$col"; }, $columns);
            
            $sql = "INSERT INTO enrollment_submissions (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($testData);
            
            if ($result) {
                $insertId = $db->lastInsertId();
                echo "<p class='test-pass'>✓ Test INSERT successful (ID: $insertId)</p>";
                
                // Clean up test data
                $stmt = $db->prepare("DELETE FROM enrollment_submissions WHERE id = ?");
                $stmt->execute([$insertId]);
                echo "<p class='test-info'>Test enrollment record deleted</p>";
            } else {
                echo "<p class='test-fail'>✗ Test INSERT failed</p>";
                echo "<pre>" . print_r($stmt->errorInfo(), true) . "</pre>";
                $allPassed = false;
            }
            
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Test INSERT failed with exception</p>";
            echo "<p class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            $allPassed = false;
        }
        echo "</div></div>";
        
        // Final Summary
        echo "<div class='card mb-3 " . ($allPassed ? 'border-success' : 'border-danger') . "'>";
        echo "<div class='card-header " . ($allPassed ? 'bg-success text-white' : 'bg-danger text-white') . "'>";
        echo "<strong>Summary</strong>";
        echo "</div>";
        echo "<div class='card-body'>";
        if ($allPassed) {
            echo "<h4 class='test-pass'>✓ All tests passed!</h4>";
            echo "<p>The database is properly configured and ready for enrollment submissions.</p>";
        } else {
            echo "<h4 class='test-fail'>✗ Some tests failed</h4>";
            echo "<p>Please review the errors above and fix the database configuration.</p>";
        }
        echo "</div></div>";
        ?>
        
        <div class="mt-4">
            <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/enrollment/create" class="btn btn-primary">Go to Enrollment Form</a>
            <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
