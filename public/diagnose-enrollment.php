<?php
// Comprehensive enrollment diagnosis
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

echo "<h1>Comprehensive Enrollment Diagnosis</h1>";
echo "<p><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";

require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

// ============================================
// 1. CHECK DATABASE TABLES EXIST
// ============================================
echo "<hr><h2>1. Database Tables Check</h2>";

$tables = ['enrollment_submissions', 'enrollment_documents', 'notifications'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✓ Table <strong>$table</strong> exists (" . count($columns) . " columns)</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Table <strong>$table</strong> MISSING or ERROR: " . $e->getMessage() . "</p>";
    }
}

// ============================================
// 2. CHECK ENROLLMENT_SUBMISSIONS COLUMNS
// ============================================
echo "<hr><h2>2. Enrollment Submissions Table Structure</h2>";

try {
    $stmt = $db->query("DESCRIBE enrollment_submissions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for critical columns
    $requiredColumns = ['id', 'parent_id', 'first_name', 'last_name', 'status', 'is_draft', 'submitted_at', 'created_at'];
    $columnNames = array_column($columns, 'Field');
    
    echo "<h3>Required Columns Check:</h3>";
    foreach ($requiredColumns as $req) {
        if (in_array($req, $columnNames)) {
            echo "<p style='color: green;'>✓ $req</p>";
        } else {
            echo "<p style='color: red;'>✗ $req MISSING!</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

// ============================================
// 3. TEST ENROLLMENT INSERT
// ============================================
echo "<hr><h2>3. Test Enrollment Insert</h2>";

try {
    // Prepare minimal data
    $testData = [
        'parent_id' => $_SESSION['user_id'],
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'is_draft' => 0,
        'status' => 'pending',
        'submitted_at' => date('Y-m-d H:i:s'),
        'first_name' => 'TestChild',
        'last_name' => 'Diagnosis',
        'birth_date' => '2015-01-01',
        'sex' => 'Male',
        'grade_level_to_enroll' => 'Grade 1',
        'signature_data' => 'test_signature',
        'date_signed' => date('Y-m-d')
    ];
    
    // Build INSERT query dynamically
    $columns = array_keys($testData);
    $placeholders = array_map(function($col) { return ":$col"; }, $columns);
    
    $sql = "INSERT INTO enrollment_submissions (" . implode(', ', $columns) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";
    
    echo "<p><strong>SQL:</strong></p>";
    echo "<pre>" . $sql . "</pre>";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $insertId = $db->lastInsertId();
        echo "<p style='color: green;'><strong>✓ SUCCESS!</strong> Test enrollment inserted with ID: $insertId</p>";
        
        // Verify
        $stmt = $db->prepare("SELECT * FROM enrollment_submissions WHERE id = :id");
        $stmt->execute(['id' => $insertId]);
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h4>Inserted Data:</h4>";
        echo "<pre>";
        print_r($enrollment);
        echo "</pre>";
        
        // Clean up test data
        $db->prepare("DELETE FROM enrollment_submissions WHERE id = :id")->execute(['id' => $insertId]);
        echo "<p><em>(Test data cleaned up)</em></p>";
    } else {
        echo "<p style='color: red;'>✗ INSERT FAILED</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// ============================================
// 4. CHECK EXISTING ENROLLMENTS
// ============================================
echo "<hr><h2>4. Your Existing Enrollments</h2>";

try {
    $stmt = $db->prepare("SELECT id, first_name, last_name, status, is_draft, submitted_at, created_at 
                          FROM enrollment_submissions 
                          WHERE parent_id = :parent_id 
                          ORDER BY created_at DESC");
    $stmt->execute(['parent_id' => $_SESSION['user_id']]);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($enrollments)) {
        echo "<p style='color: orange;'>No enrollments found for your account.</p>";
    } else {
        echo "<p><strong>Found " . count($enrollments) . " enrollment(s):</strong></p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Status</th><th>Is Draft</th><th>Submitted</th><th>Created</th></tr>";
        foreach ($enrollments as $e) {
            $isDraft = $e['is_draft'] ? 'YES' : 'NO';
            $bgColor = $e['is_draft'] ? '#fff3cd' : '#d4edda';
            echo "<tr style='background: $bgColor;'>";
            echo "<td>" . $e['id'] . "</td>";
            echo "<td>" . htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) . "</td>";
            echo "<td>" . $e['status'] . "</td>";
            echo "<td>" . $isDraft . "</td>";
            echo "<td>" . ($e['submitted_at'] ?? '<em>NULL</em>') . "</td>";
            echo "<td>" . $e['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

// ============================================
// 5. CHECK NOTIFICATIONS TABLE
// ============================================
echo "<hr><h2>5. Notifications Check</h2>";

try {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<p>You have <strong>$count</strong> notification(s)</p>";
    
    if ($count > 0) {
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Type</th><th>Title</th><th>Created</th></tr>";
        foreach ($notifications as $n) {
            echo "<tr>";
            echo "<td>" . $n['id'] . "</td>";
            echo "<td>" . $n['type'] . "</td>";
            echo "<td>" . htmlspecialchars($n['title']) . "</td>";
            echo "<td>" . $n['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

// ============================================
// 6. RECOMMENDATIONS
// ============================================
echo "<hr><h2>6. Recommendations</h2>";

echo "<ol>";
echo "<li>If tables are missing, run: <code>php public/apply-migration-v6.php</code></li>";
echo "<li>If columns are missing, check <code>config/schema.sql</code> and re-apply migrations</li>";
echo "<li>If test insert works but form doesn't, check JavaScript console for errors</li>";
echo "<li>If notifications aren't working, check if NotificationModel is being called</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='../dashboard'>← Back to Dashboard</a></p>";
?>
