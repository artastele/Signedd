<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1 Testing
// Last modified: 2026-05-03
// Part of: SPED LMS — Enrollment Full System Test

session_start();

// Define BASE_PATH if not already defined
if (!defined('BASE_PATH')) {
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = str_replace('/public', '', $scriptName);
    define('BASE_PATH', $basePath);
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/EnrollmentModel.php';

header('Content-Type: text/html; charset=utf-8');

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$db = Database::getInstance()->getConnection();
$enrollmentModel = new EnrollmentModel();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment System Tester - SPED LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #28a745; font-weight: bold; }
        .test-fail { color: #dc3545; font-weight: bold; }
        .test-info { color: #17a2b8; }
        .test-warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 12px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">🧪 Enrollment System Full Test</h1>
        
        <?php
        $allPassed = true;
        $testResults = [];
        
        // ============================================
        // TEST 1: Database Connection
        // ============================================
        echo "<div class='card'>";
        echo "<div class='card-header'><strong>Test 1:</strong> Database Connection</div>";
        echo "<div class='card-body'>";
        try {
            $db->query("SELECT 1");
            echo "<p class='test-pass'>✓ Database connection OK</p>";
            $testResults['db_connection'] = true;
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Database connection FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
            $allPassed = false;
            $testResults['db_connection'] = false;
        }
        echo "</div></div>";
        
        // ============================================
        // TEST 2: Table Structure
        // ============================================
        echo "<div class='card'>";
        echo "<div class='card-header'><strong>Test 2:</strong> Table Structure</div>";
        echo "<div class='card-body'>";
        try {
            $stmt = $db->query("DESCRIBE enrollment_submissions");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $requiredColumns = ['id', 'parent_id', 'is_draft', 'status', 'first_name', 'last_name', 'submitted_at', 'last_activity'];
            $missing = array_diff($requiredColumns, $columns);
            
            if (empty($missing)) {
                echo "<p class='test-pass'>✓ All required columns exist (" . count($columns) . " total)</p>";
                $testResults['table_structure'] = true;
            } else {
                echo "<p class='test-fail'>✗ Missing columns: " . implode(', ', $missing) . "</p>";
                $allPassed = false;
                $testResults['table_structure'] = false;
            }
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Table check FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
            $allPassed = false;
            $testResults['table_structure'] = false;
        }
        echo "</div></div>";
        
        // ============================================
        // TEST 3: BASE_PATH Configuration
        // ============================================
        echo "<div class='card'>";
        echo "<div class='card-header'><strong>Test 3:</strong> BASE_PATH Configuration</div>";
        echo "<div class='card-body'>";
        
        if (defined('BASE_PATH')) {
            echo "<p class='test-pass'>✓ BASE_PATH is defined: <code>" . BASE_PATH . "</code></p>";
            $testResults['base_path'] = true;
        } else {
            echo "<p class='test-warning'>⚠ BASE_PATH not defined (will use empty string)</p>";
            echo "<p class='test-info'>Expected: <code>/Signedd</code></p>";
            $testResults['base_path'] = false;
        }
        
        // Check actual paths
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $expectedBasePath = str_replace('/public', '', $scriptName);
        echo "<p class='test-info'>Detected BASE_PATH should be: <code>$expectedBasePath</code></p>";
        echo "</div></div>";
        
        // ============================================
        // TEST 4: Test Parent User
        // ============================================
        echo "<div class='card'>";
        echo "<div class='card-header'><strong>Test 4:</strong> Test Parent User</div>";
        echo "<div class='card-body'>";
        try {
            $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE role = 'parent' LIMIT 1");
            $stmt->execute();
            $testParent = $stmt->fetch();
            
            if ($testParent) {
                echo "<p class='test-pass'>✓ Test parent user found</p>";
                echo "<p class='test-info'>ID: {$testParent['id']} | Name: {$testParent['name']} | Email: {$testParent['email']}</p>";
                $testResults['test_parent'] = $testParent['id'];
            } else {
                echo "<p class='test-warning'>⚠ No parent user found - creating one...</p>";
                
                $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    'Test Parent',
                    'parent@test.local',
                    password_hash('test123', PASSWORD_DEFAULT),
                    'parent',
                    'active'
                ]);
                $testParentId = $db->lastInsertId();
                echo "<p class='test-pass'>✓ Created test parent (ID: $testParentId)</p>";
                echo "<p class='test-info'>Email: parent@test.local | Password: test123</p>";
                $testResults['test_parent'] = $testParentId;
            }
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Parent user check FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
            $allPassed = false;
            $testResults['test_parent'] = false;
        }
        echo "</div></div>";
        
        // ============================================
        // TEST 5: Simulate Enrollment Submission
        // ============================================
        echo "<div class='card'>";
        echo "<div class='card-header'><strong>Test 5:</strong> Simulate Enrollment Submission</div>";
        echo "<div class='card-body'>";
        
        if ($testResults['test_parent']) {
            try {
                $testData = [
                    'parent_id' => $testResults['test_parent'],
                    'enrollment_type' => 'new',
                    'school_year' => '2026-2027',
                    'previous_enrollment_id' => null,
                    'is_draft' => false,
                    'status' => 'pending',
                    'lrn' => 'TEST' . time(),
                    'last_name' => 'TestStudent',
                    'first_name' => 'Automated',
                    'middle_name' => 'Test',
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
                    'father_last_name' => 'TestFather',
                    'father_first_name' => 'John',
                    'father_middle_name' => null,
                    'father_contact_number' => '09123456789',
                    'mother_maiden_last_name' => 'TestMother',
                    'mother_first_name' => 'Jane',
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
                    'signature_data' => 'data:image/png;base64,TEST',
                    'date_signed' => date('Y-m-d'),
                    'draft_saved_at' => null,
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'verified_by' => null,
                    'verified_at' => null
                ];
                
                echo "<p class='test-info'>Attempting to create enrollment...</p>";
                $enrollmentId = $enrollmentModel->create($testData);
                
                if ($enrollmentId) {
                    echo "<p class='test-pass'>✓ Enrollment created successfully (ID: $enrollmentId)</p>";
                    
                    // Verify it exists
                    $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = ?");
                    $stmt->execute([$enrollmentId]);
                    $enrollment = $stmt->fetch();
                    
                    if ($enrollment) {
                        echo "<p class='test-pass'>✓ Enrollment verified in database</p>";
                        echo "<details><summary>View enrollment data</summary><pre>" . print_r($enrollment, true) . "</pre></details>";
                        
                        // Clean up test data
                        $stmt = $db->prepare("DELETE FROM enrollment_submissions WHERE id = ?");
                        $stmt->execute([$enrollmentId]);
                        echo "<p class='test-info'>Test enrollment deleted</p>";
                        
                        $testResults['enrollment_create'] = true;
                    } else {
                        echo "<p class='test-fail'>✗ Enrollment NOT found in database after insert!</p>";
                        $allPassed = false;
                        $testResults['enrollment_create'] = false;
                    }
                } else {
                    echo "<p class='test-fail'>✗ Enrollment creation returned false/null</p>";
                    $allPassed = false;
                    $testResults['enrollment_create'] = false;
                }
                
            } catch (Exception $e) {
                echo "<p class='test-fail'>✗ Enrollment creation FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                $allPassed = false;
                $testResults['enrollment_create'] = false;
            }
        } else {
            echo "<p class='test-warning'>⚠ Skipped (no test parent)</p>";
            $testResults['enrollment_create'] = 'skipped';
        }
        echo "</div></div>";
        
        // ============================================
        // TEST 6: Check Current Enrollments
        // ============================================
        echo "<div class='card'>";
        echo "<div class='card-header'><strong>Test 6:</strong> Current Enrollments in Database</div>";
        echo "<div class='card-body'>";
        try {
            $stmt = $db->query("SELECT COUNT(*) as total, 
                                       SUM(CASE WHEN is_draft = 1 THEN 1 ELSE 0 END) as drafts,
                                       SUM(CASE WHEN is_draft = 0 THEN 1 ELSE 0 END) as submitted
                                FROM enrollment_submissions");
            $stats = $stmt->fetch();
            
            echo "<p class='test-info'>Total enrollments: <strong>{$stats['total']}</strong></p>";
            echo "<p class='test-info'>Drafts: <strong>{$stats['drafts']}</strong> | Submitted: <strong>{$stats['submitted']}</strong></p>";
            
            if ($stats['total'] > 0) {
                $stmt = $db->query("SELECT id, parent_id, first_name, last_name, is_draft, status, submitted_at 
                                    FROM enrollment_submissions 
                                    ORDER BY id DESC LIMIT 5");
                $recent = $stmt->fetchAll();
                
                echo "<h6>Recent Enrollments:</h6>";
                echo "<table class='table table-sm'>";
                echo "<tr><th>ID</th><th>Parent</th><th>Name</th><th>Draft</th><th>Status</th><th>Submitted</th></tr>";
                foreach ($recent as $e) {
                    echo "<tr>";
                    echo "<td>{$e['id']}</td>";
                    echo "<td>{$e['parent_id']}</td>";
                    echo "<td>{$e['first_name']} {$e['last_name']}</td>";
                    echo "<td>" . ($e['is_draft'] ? 'YES' : 'NO') . "</td>";
                    echo "<td>{$e['status']}</td>";
                    echo "<td>" . ($e['submitted_at'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            $testResults['current_enrollments'] = $stats['total'];
        } catch (Exception $e) {
            echo "<p class='test-fail'>✗ Query FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
            $testResults['current_enrollments'] = false;
        }
        echo "</div></div>";
        
        // ============================================
        // FINAL SUMMARY
        // ============================================
        echo "<div class='card " . ($allPassed ? 'border-success' : 'border-danger') . "'>";
        echo "<div class='card-header " . ($allPassed ? 'bg-success text-white' : 'bg-danger text-white') . "'>";
        echo "<strong>Test Summary</strong>";
        echo "</div>";
        echo "<div class='card-body'>";
        
        if ($allPassed) {
            echo "<h4 class='test-pass'>✓ All Critical Tests Passed!</h4>";
            echo "<p>The enrollment system is ready for use.</p>";
        } else {
            echo "<h4 class='test-fail'>✗ Some Tests Failed</h4>";
            echo "<p>Please review the errors above and fix the issues.</p>";
        }
        
        echo "<hr>";
        echo "<h6>Test Results:</h6>";
        echo "<ul>";
        foreach ($testResults as $test => $result) {
            $icon = $result === true ? '✓' : ($result === false ? '✗' : '⚠');
            $class = $result === true ? 'test-pass' : ($result === false ? 'test-fail' : 'test-warning');
            echo "<li class='$class'>$icon " . ucwords(str_replace('_', ' ', $test)) . "</li>";
        }
        echo "</ul>";
        
        echo "</div></div>";
        ?>
        
        <div class='mt-4'>
            <a href='<?php echo $basePath; ?>/enrollment' class='btn btn-primary'>Go to Enrollment</a>
            <a href='<?php echo $basePath; ?>/dashboard' class='btn btn-secondary'>Dashboard</a>
            <button onclick='location.reload()' class='btn btn-info'>Run Tests Again</button>
        </div>
    </div>
</body>
</html>
