<?php
// Direct Enrollment Test - Bypass Everything

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct Enrollment Test</h1><hr>";

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

// Database connection
try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'sped_lms';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $db = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Database connected<br>";
    echo "Database: <strong>$dbname</strong><br><hr>";
    
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Test 1: Check table structure
echo "<h2>Test 1: Table Structure</h2>";
try {
    $stmt = $db->query("SHOW COLUMNS FROM enrollment_submissions");
    $columns = $stmt->fetchAll();
    $columnNames = array_column($columns, 'Field');
    
    echo "Total columns: " . count($columnNames) . "<br>";
    echo "Has birth_place: " . (in_array('birth_place', $columnNames) ? '✅' : '❌') . "<br>";
    echo "Has current_region: " . (in_array('current_region', $columnNames) ? '❌ BAD' : '✅ GOOD') . "<br>";
    echo "<hr>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br><hr>";
}

// Test 2: Direct INSERT with minimal data
echo "<h2>Test 2: Direct INSERT (Minimal Data)</h2>";
try {
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, 
        last_name, first_name, birth_date, sex, age, birth_place,
        grade_level_to_enroll, status, is_draft
    ) VALUES (
        2, 'new', '2026-2027',
        'TestLast', 'TestFirst', '2010-01-01', 'Male', 16, 'Cebu City',
        'Grade 7', 'pending', 0
    )";
    
    $result = $db->exec($sql);
    $insertId = $db->lastInsertId();
    
    echo "✅ INSERT successful!<br>";
    echo "Insert ID: <strong>$insertId</strong><br>";
    echo "Rows affected: $result<br>";
    
    // Verify
    $verify = $db->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch();
    if ($verify) {
        echo "✅ Verified in database:<br>";
        echo "Name: {$verify['first_name']} {$verify['last_name']}<br>";
        echo "Birth Place: {$verify['birth_place']}<br>";
        echo "Status: {$verify['status']}<br>";
    }
    echo "<hr>";
    
} catch (Exception $e) {
    echo "❌ INSERT failed: " . $e->getMessage() . "<br>";
    echo "SQL State: " . $e->getCode() . "<br><hr>";
}

// Test 3: INSERT with prepared statement (like the model)
echo "<h2>Test 3: Prepared Statement INSERT</h2>";
try {
    $data = [
        'parent_id' => 2,
        'enrollment_type' => 'new',
        'school_year' => '2026-2027',
        'last_name' => 'PreparedTest',
        'first_name' => 'Student',
        'birth_date' => '2010-01-01',
        'sex' => 'Male',
        'age' => 16,
        'birth_place' => 'Cebu City',
        'grade_level_to_enroll' => 'Grade 7',
        'status' => 'pending',
        'is_draft' => 0
    ];
    
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year,
        last_name, first_name, birth_date, sex, age, birth_place,
        grade_level_to_enroll, status, is_draft
    ) VALUES (
        :parent_id, :enrollment_type, :school_year,
        :last_name, :first_name, :birth_date, :sex, :age, :birth_place,
        :grade_level_to_enroll, :status, :is_draft
    )";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($data);
    $insertId = $db->lastInsertId();
    
    echo "✅ Prepared statement successful!<br>";
    echo "Insert ID: <strong>$insertId</strong><br>";
    echo "Execute result: " . ($result ? 'TRUE' : 'FALSE') . "<br><hr>";
    
} catch (Exception $e) {
    echo "❌ Prepared statement failed: " . $e->getMessage() . "<br><hr>";
}

// Test 4: Check all enrollments
echo "<h2>Test 4: All Enrollments in Database</h2>";
try {
    $stmt = $db->query("SELECT id, parent_id, first_name, last_name, birth_place, status, is_draft, created_at 
                        FROM enrollment_submissions 
                        ORDER BY id DESC");
    $enrollments = $stmt->fetchAll();
    
    if (count($enrollments) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #1e4072; color: white;'>";
        echo "<th>ID</th><th>Parent</th><th>Name</th><th>Birth Place</th><th>Status</th><th>Draft?</th><th>Created</th>";
        echo "</tr>";
        foreach ($enrollments as $e) {
            echo "<tr>";
            echo "<td>{$e['id']}</td>";
            echo "<td>{$e['parent_id']}</td>";
            echo "<td>{$e['first_name']} {$e['last_name']}</td>";
            echo "<td>{$e['birth_place']}</td>";
            echo "<td>{$e['status']}</td>";
            echo "<td>" . ($e['is_draft'] ? 'Yes' : 'No') . "</td>";
            echo "<td>{$e['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<br>Total: " . count($enrollments) . " enrollment(s)<br>";
    } else {
        echo "⚠️ No enrollments found<br>";
    }
    echo "<hr>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br><hr>";
}

// Test 5: Check if form submission is even reaching the controller
echo "<h2>Test 5: Form Submission Check</h2>";
echo "<p>Now submit the enrollment form, then come back and refresh this page.</p>";
echo "<p>If Tests 2 & 3 work but form doesn't create enrollment, the problem is:</p>";
echo "<ul>";
echo "<li>❌ Form data not being sent correctly</li>";
echo "<li>❌ JavaScript preventing submission</li>";
echo "<li>❌ Controller not being called</li>";
echo "<li>❌ Validation blocking submission</li>";
echo "</ul>";

echo "<hr>";
echo "<button onclick='location.reload()' style='padding: 10px 20px; background: #a01422; color: white; border: none; cursor: pointer;'>🔄 Refresh</button>";
?>
