<?php
/**
 * Process 6-7 Testing Script
 * Tests all components before marking as DONE
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db.php';

// Test results
$tests = [];
$passed = 0;
$failed = 0;

function test($name, $callback) {
    global $tests, $passed, $failed;
    
    try {
        $result = $callback();
        if ($result === true) {
            $tests[] = ['name' => $name, 'status' => 'PASS', 'message' => 'OK'];
            $passed++;
        } else {
            $tests[] = ['name' => $name, 'status' => 'FAIL', 'message' => $result];
            $failed++;
        }
    } catch (Exception $e) {
        $tests[] = ['name' => $name, 'status' => 'ERROR', 'message' => $e->getMessage()];
        $failed++;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Process 6-7 Testing</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #a01422; }
        .test { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .pass { background: #d4edda; border-left: 4px solid #28a745; }
        .fail { background: #f8d7da; border-left: 4px solid #dc3545; }
        .error { background: #fff3cd; border-left: 4px solid #ffc107; }
        .summary { padding: 15px; background: #e9ecef; border-radius: 4px; margin: 20px 0; }
        .summary h2 { margin-top: 0; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Process 6-7 Testing Suite</h1>
        <p>Testing all components before marking as DONE...</p>

        <?php
        
        // ============================================
        // TEST 1: Database Schema
        // ============================================
        
        test('Migration v23 Applied', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT * FROM db_version WHERE version = 23");
            $result = $stmt->fetch();
            return $result ? true : 'Migration v23 not found in db_version';
        });
        
        test('Table: activity_templates exists', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW TABLES LIKE 'activity_templates'");
            return $stmt->rowCount() > 0 ? true : 'Table not found';
        });
        
        test('Table: activity_attempts exists', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW TABLES LIKE 'activity_attempts'");
            return $stmt->rowCount() > 0 ? true : 'Table not found';
        });
        
        test('Table: assignment_submissions exists', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW TABLES LIKE 'assignment_submissions'");
            return $stmt->rowCount() > 0 ? true : 'Table not found';
        });
        
        test('Table: learner_progress exists', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW TABLES LIKE 'learner_progress'");
            return $stmt->rowCount() > 0 ? true : 'Table not found';
        });
        
        test('Column: learning_materials.is_assignment exists', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW COLUMNS FROM learning_materials LIKE 'is_assignment'");
            return $stmt->rowCount() > 0 ? true : 'Column not found';
        });
        
        test('Column: learning_materials.due_date exists', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW COLUMNS FROM learning_materials LIKE 'due_date'");
            return $stmt->rowCount() > 0 ? true : 'Column not found';
        });
        
        test('Column: learning_materials.points exists', function() {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW COLUMNS FROM learning_materials LIKE 'points'");
            return $stmt->rowCount() > 0 ? true : 'Column not found';
        });
        
        // ============================================
        // TEST 2: Model Files Exist
        // ============================================
        
        test('Model: LearnerIEPModel.php exists', function() {
            return file_exists(__DIR__ . '/../app/Models/LearnerIEPModel.php') ? true : 'File not found';
        });
        
        test('Model: LearningMaterialModel.php exists', function() {
            return file_exists(__DIR__ . '/../app/Models/LearningMaterialModel.php') ? true : 'File not found';
        });
        
        test('Model: ActivityTemplateModel.php exists', function() {
            return file_exists(__DIR__ . '/../app/Models/ActivityTemplateModel.php') ? true : 'File not found';
        });
        
        test('Model: ActivityAttemptModel.php exists', function() {
            return file_exists(__DIR__ . '/../app/Models/ActivityAttemptModel.php') ? true : 'File not found';
        });
        
        test('Model: LearnerProgressModel.php exists', function() {
            return file_exists(__DIR__ . '/../app/Models/LearnerProgressModel.php') ? true : 'File not found';
        });
        
        test('Model: AssignmentSubmissionModel.php exists', function() {
            return file_exists(__DIR__ . '/../app/Models/AssignmentSubmissionModel.php') ? true : 'File not found';
        });
        
        test('Model: ModuleAccessLogModel.php exists', function() {
            return file_exists(__DIR__ . '/../app/Models/ModuleAccessLogModel.php') ? true : 'File not found';
        });
        
        // ============================================
        // TEST 3: Controller Files Exist
        // ============================================
        
        test('Controller: IEPImplementationController.php exists', function() {
            return file_exists(__DIR__ . '/../app/Controllers/IEPImplementationController.php') ? true : 'File not found';
        });
        
        test('Controller: LearningController.php exists', function() {
            return file_exists(__DIR__ . '/../app/Controllers/LearningController.php') ? true : 'File not found';
        });
        
        // ============================================
        // TEST 4: Model Instantiation
        // ============================================
        
        test('Model: LearnerIEPModel instantiates', function() {
            require_once __DIR__ . '/../app/Models/LearnerIEPModel.php';
            $model = new LearnerIEPModel();
            return is_object($model) ? true : 'Failed to instantiate';
        });
        
        test('Model: ActivityAttemptModel instantiates', function() {
            require_once __DIR__ . '/../app/Models/ActivityAttemptModel.php';
            $model = new ActivityAttemptModel();
            return is_object($model) ? true : 'Failed to instantiate';
        });
        
        // ============================================
        // TEST 5: Auto-Grading Logic
        // ============================================
        
        test('Auto-Grading: Multiple Choice (all correct)', function() {
            require_once __DIR__ . '/../app/Models/ActivityAttemptModel.php';
            $model = new ActivityAttemptModel();
            
            $template = [
                'activity_type' => 'multiple_choice',
                'activity_data' => [
                    'questions' => [
                        ['question' => 'Q1', 'correct_answer' => 0, 'points' => 10],
                        ['question' => 'Q2', 'correct_answer' => 1, 'points' => 10]
                    ]
                ],
                'total_points' => 20
            ];
            
            $answers = [0, 1]; // Both correct
            $score = $model->calculateScore($template, $answers);
            
            return $score === 20 ? true : "Expected 20, got $score";
        });
        
        test('Auto-Grading: Multiple Choice (one correct)', function() {
            require_once __DIR__ . '/../app/Models/ActivityAttemptModel.php';
            $model = new ActivityAttemptModel();
            
            $template = [
                'activity_type' => 'multiple_choice',
                'activity_data' => [
                    'questions' => [
                        ['question' => 'Q1', 'correct_answer' => 0, 'points' => 10],
                        ['question' => 'Q2', 'correct_answer' => 1, 'points' => 10]
                    ]
                ],
                'total_points' => 20
            ];
            
            $answers = [0, 0]; // One correct
            $score = $model->calculateScore($template, $answers);
            
            return $score === 10 ? true : "Expected 10, got $score";
        });
        
        test('Auto-Grading: True/False', function() {
            require_once __DIR__ . '/../app/Models/ActivityAttemptModel.php';
            $model = new ActivityAttemptModel();
            
            $template = [
                'activity_type' => 'true_false',
                'activity_data' => [
                    'questions' => [
                        ['question' => 'Q1', 'correct_answer' => true, 'points' => 5],
                        ['question' => 'Q2', 'correct_answer' => false, 'points' => 5]
                    ]
                ],
                'total_points' => 10
            ];
            
            $answers = [true, false]; // Both correct
            $score = $model->calculateScore($template, $answers);
            
            return $score === 10 ? true : "Expected 10, got $score";
        });
        
        // ============================================
        // TEST 6: File Encryption
        // ============================================
        
        test('FileEncryptionHelper: Class exists', function() {
            return class_exists('FileEncryptionHelper') ? true : 'Class not found';
        });
        
        test('FileEncryptionHelper: isEncrypted method exists', function() {
            return method_exists('FileEncryptionHelper', 'isEncrypted') ? true : 'Method not found';
        });
        
        // ============================================
        // TEST 7: Routes Check
        // ============================================
        
        test('Routes: web.php contains IEPImplementationController', function() {
            $routes = file_get_contents(__DIR__ . '/../routes/web.php');
            return strpos($routes, 'IEPImplementationController') !== false ? true : 'Route not found';
        });
        
        test('Routes: web.php contains LearningController', function() {
            $routes = file_get_contents(__DIR__ . '/../routes/web.php');
            return strpos($routes, 'LearningController') !== false ? true : 'Route not found';
        });
        
        test('Routes: /learning/modules route exists', function() {
            $routes = file_get_contents(__DIR__ . '/../routes/web.php');
            return strpos($routes, '/learning/modules') !== false ? true : 'Route not found';
        });
        
        test('Routes: /iep/implementation route exists', function() {
            $routes = file_get_contents(__DIR__ . '/../routes/web.php');
            return strpos($routes, '/iep/implementation') !== false ? true : 'Route not found';
        });
        
        // ============================================
        // TEST 8: Permissions
        // ============================================
        
        test('Permissions: learner role exists', function() {
            $permissions = require __DIR__ . '/../config/permissions.php';
            return isset($permissions['learner']) ? true : 'Learner role not found';
        });
        
        test('Permissions: learning.access permission exists', function() {
            $permissions = require __DIR__ . '/../config/permissions.php';
            return in_array('learning.access', $permissions['learner']) ? true : 'Permission not found';
        });
        
        test('Permissions: iep.implement permission exists for sped_teacher', function() {
            $permissions = require __DIR__ . '/../config/permissions.php';
            return in_array('iep.implement', $permissions['sped_teacher']) ? true : 'Permission not found';
        });
        
        // ============================================
        // Display Results
        // ============================================
        
        ?>
        
        <div class="summary">
            <h2>📊 Test Summary</h2>
            <p><strong>Total Tests:</strong> <?php echo count($tests); ?></p>
            <p><strong>Passed:</strong> <span style="color: #28a745;"><?php echo $passed; ?></span></p>
            <p><strong>Failed:</strong> <span style="color: #dc3545;"><?php echo $failed; ?></span></p>
            <p><strong>Success Rate:</strong> <?php echo round(($passed / count($tests)) * 100, 2); ?>%</p>
        </div>
        
        <h2>📋 Test Results</h2>
        
        <?php foreach ($tests as $test): ?>
            <div class="test <?php echo strtolower($test['status']); ?>">
                <strong><?php echo $test['status']; ?>:</strong> <?php echo htmlspecialchars($test['name']); ?>
                <?php if ($test['message'] !== 'OK'): ?>
                    <br><small><?php echo htmlspecialchars($test['message']); ?></small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <?php if ($failed === 0): ?>
            <div class="summary" style="background: #d4edda; border-left: 4px solid #28a745;">
                <h2>✅ ALL TESTS PASSED!</h2>
                <p>Process 6-7 backend is ready. You can now proceed to create views.</p>
            </div>
        <?php else: ?>
            <div class="summary" style="background: #f8d7da; border-left: 4px solid #dc3545;">
                <h2>❌ SOME TESTS FAILED</h2>
                <p>Please fix the failed tests before proceeding.</p>
            </div>
        <?php endif; ?>
        
        <h2>📝 Next Steps</h2>
        <ol>
            <li>If all tests pass, create the views (12 files)</li>
            <li>Add CSS for cartoon style (learner.css)</li>
            <li>Add JavaScript for activity builder and player</li>
            <li>Test end-to-end workflows</li>
            <li>Mark as DONE only after full testing</li>
        </ol>
        
    </div>
</body>
</html>
