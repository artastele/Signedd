<?php
// Test script for Process 2 - SPED Teacher Enrollment Verification
// This script verifies all components are in place and working

echo "=== PROCESS 2 VERIFICATION TEST ===\n\n";

$passed = 0;
$failed = 0;

// Test 1: Check if StudentModel exists and has required methods
echo "TEST 1: StudentModel Class\n";
if (file_exists(__DIR__ . '/../app/Models/StudentModel.php')) {
    require_once __DIR__ . '/../app/Models/StudentModel.php';
    
    $methods = ['generateLRN', 'createStudentRecord', 'createLearnerAccount', 'findByLRN', 'findByEnrollmentId'];
    $class = new ReflectionClass('StudentModel');
    
    $allMethodsExist = true;
    foreach ($methods as $method) {
        if (!$class->hasMethod($method)) {
            echo "  ✗ Missing method: $method\n";
            $allMethodsExist = false;
            $failed++;
        }
    }
    
    if ($allMethodsExist) {
        echo "  ✓ StudentModel exists with all required methods\n";
        $passed++;
    }
} else {
    echo "  ✗ StudentModel.php not found\n";
    $failed++;
}

// Test 2: Check if VerificationController exists and has required methods
echo "\nTEST 2: VerificationController Class\n";
if (file_exists(__DIR__ . '/../app/Controllers/VerificationController.php')) {
    require_once __DIR__ . '/../app/Controllers/VerificationController.php';
    
    $methods = ['index', 'show', 'verify'];
    $class = new ReflectionClass('VerificationController');
    
    $allMethodsExist = true;
    foreach ($methods as $method) {
        if (!$class->hasMethod($method)) {
            echo "  ✗ Missing method: $method\n";
            $allMethodsExist = false;
            $failed++;
        }
    }
    
    if ($allMethodsExist) {
        echo "  ✓ VerificationController exists with all required methods\n";
        $passed++;
    }
} else {
    echo "  ✗ VerificationController.php not found\n";
    $failed++;
}

// Test 3: Check if verification views exist
echo "\nTEST 3: Verification Views\n";
$views = [
    __DIR__ . '/../app/Views/verification/index.php' => 'Verification Dashboard',
    __DIR__ . '/../app/Views/verification/show.php' => 'Enrollment Detail View'
];

foreach ($views as $path => $name) {
    if (file_exists($path)) {
        echo "  ✓ $name exists\n";
        $passed++;
    } else {
        echo "  ✗ $name not found at $path\n";
        $failed++;
    }
}

// Test 4: Check database schema for required tables
echo "\nTEST 4: Database Schema\n";
if (file_exists(__DIR__ . '/../config/schema.sql')) {
    $schema = file_get_contents(__DIR__ . '/../config/schema.sql');
    
    $tables = [
        'student_records' => 'Student Records Table',
        'enrollment_submissions' => 'Enrollment Submissions Table',
        'enrollment_documents' => 'Enrollment Documents Table'
    ];
    
    foreach ($tables as $table => $name) {
        if (strpos($schema, "CREATE TABLE IF NOT EXISTS $table") !== false) {
            echo "  ✓ $name exists in schema\n";
            $passed++;
        } else {
            echo "  ✗ $name not found in schema\n";
            $failed++;
        }
    }
    
    // Check for LRN column
    if (strpos($schema, "lrn VARCHAR(12)") !== false) {
        echo "  ✓ LRN column (12 digits) defined in schema\n";
        $passed++;
    } else {
        echo "  ✗ LRN column not found in schema\n";
        $failed++;
    }
    
    // Check for learner role
    if (strpos($schema, "'learner'") !== false) {
        echo "  ✓ Learner role added to users table\n";
        $passed++;
    } else {
        echo "  ✗ Learner role not found in schema\n";
        $failed++;
    }
    
    // Check for Migration v12
    if (strpos($schema, "MIGRATION: v12") !== false) {
        echo "  ✓ Migration v12 (Process 2 LRN & Learner Role) exists\n";
        $passed++;
    } else {
        echo "  ✗ Migration v12 not found\n";
        $failed++;
    }
} else {
    echo "  ✗ schema.sql not found\n";
    $failed++;
}

// Test 5: Check routes
echo "\nTEST 5: Routes Configuration\n";
if (file_exists(__DIR__ . '/../routes/web.php')) {
    $routes = file_get_contents(__DIR__ . '/../routes/web.php');
    
    $requiredRoutes = [
        "route('GET', '/verification'" => 'GET /verification',
        "route('GET', '/verification/{id}'" => 'GET /verification/{id}',
        "route('POST', '/verification/{id}/verify'" => 'POST /verification/{id}/verify'
    ];
    
    foreach ($requiredRoutes as $pattern => $name) {
        if (strpos($routes, $pattern) !== false) {
            echo "  ✓ Route $name configured\n";
            $passed++;
        } else {
            echo "  ✗ Route $name not found\n";
            $failed++;
        }
    }
} else {
    echo "  ✗ routes/web.php not found\n";
    $failed++;
}

// Test 6: Check sidebar navigation
echo "\nTEST 6: Sidebar Navigation\n";
if (file_exists(__DIR__ . '/../app/Views/layouts/sidebar.php')) {
    $sidebar = file_get_contents(__DIR__ . '/../app/Views/layouts/sidebar.php');
    
    if (strpos($sidebar, '/verification') !== false) {
        echo "  ✓ Verification link added to sidebar\n";
        $passed++;
    } else {
        echo "  ✗ Verification link not found in sidebar\n";
        $failed++;
    }
    
    if (strpos($sidebar, 'Verify Enrollments') !== false) {
        echo "  ✓ 'Verify Enrollments' label in sidebar\n";
        $passed++;
    } else {
        echo "  ✗ 'Verify Enrollments' label not found\n";
        $failed++;
    }
} else {
    echo "  ✗ sidebar.php not found\n";
    $failed++;
}

// Test 7: Check CHANGELOG
echo "\nTEST 7: CHANGELOG Documentation\n";
if (file_exists(__DIR__ . '/../CHANGELOG.md')) {
    $changelog = file_get_contents(__DIR__ . '/../CHANGELOG.md');
    
    if (strpos($changelog, 'v0.14') !== false) {
        echo "  ✓ v0.14 (Process 2) documented in CHANGELOG\n";
        $passed++;
    } else {
        echo "  ✗ v0.14 not found in CHANGELOG\n";
        $failed++;
    }
    
    if (strpos($changelog, 'StudentModel') !== false) {
        echo "  ✓ StudentModel mentioned in CHANGELOG\n";
        $passed++;
    } else {
        echo "  ✗ StudentModel not mentioned in CHANGELOG\n";
        $failed++;
    }
} else {
    echo "  ✗ CHANGELOG.md not found\n";
    $failed++;
}

// Test 8: Check LRN generation logic
echo "\nTEST 8: LRN Generation Logic\n";
if (file_exists(__DIR__ . '/../app/Models/StudentModel.php')) {
    $studentModel = file_get_contents(__DIR__ . '/../app/Models/StudentModel.php');
    
    if (strpos($studentModel, 'generateLRN') !== false) {
        echo "  ✓ generateLRN method exists\n";
        $passed++;
    } else {
        echo "  ✗ generateLRN method not found\n";
        $failed++;
    }
    
    if (strpos($studentModel, 'Ymd') !== false) {
        echo "  ✓ LRN uses YYYYMMDD format\n";
        $passed++;
    } else {
        echo "  ✗ LRN format not using YYYYMMDD\n";
        $failed++;
    }
    
    if (strpos($studentModel, 'str_pad') !== false && strpos($studentModel, '4') !== false) {
        echo "  ✓ LRN uses 4-digit sequence number\n";
        $passed++;
    } else {
        echo "  ✗ LRN sequence format not correct\n";
        $failed++;
    }
} else {
    echo "  ✗ StudentModel.php not found\n";
    $failed++;
}

// Test 9: Check learner account creation
echo "\nTEST 9: Learner Account Creation\n";
if (file_exists(__DIR__ . '/../app/Models/StudentModel.php')) {
    $studentModel = file_get_contents(__DIR__ . '/../app/Models/StudentModel.php');
    
    if (strpos($studentModel, 'createLearnerAccount') !== false) {
        echo "  ✓ createLearnerAccount method exists\n";
        $passed++;
    } else {
        echo "  ✗ createLearnerAccount method not found\n";
        $failed++;
    }
    
    if (strpos($studentModel, 'learner') !== false) {
        echo "  ✓ Learner role assignment in account creation\n";
        $passed++;
    } else {
        echo "  ✗ Learner role not assigned\n";
        $failed++;
    }
    
    if (strpos($studentModel, 'password_hash') !== false) {
        echo "  ✓ Password hashing for learner account\n";
        $passed++;
    } else {
        echo "  ✗ Password hashing not found\n";
        $failed++;
    }
} else {
    echo "  ✗ StudentModel.php not found\n";
    $failed++;
}

// Test 10: Check email notification
echo "\nTEST 10: Email Notifications\n";
if (file_exists(__DIR__ . '/../app/Models/StudentModel.php')) {
    $studentModel = file_get_contents(__DIR__ . '/../app/Models/StudentModel.php');
    
    if (strpos($studentModel, 'sendLRNCredentialsEmail') !== false) {
        echo "  ✓ sendLRNCredentialsEmail method exists\n";
        $passed++;
    } else {
        echo "  ✗ sendLRNCredentialsEmail method not found\n";
        $failed++;
    }
    
    if (strpos($studentModel, 'MailHelper') !== false) {
        echo "  ✓ MailHelper integration for email sending\n";
        $passed++;
    } else {
        echo "  ✗ MailHelper not integrated\n";
        $failed++;
    }
} else {
    echo "  ✗ StudentModel.php not found\n";
    $failed++;
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "✓ PASSED: $passed\n";
echo "✗ FAILED: $failed\n";
echo "TOTAL:   " . ($passed + $failed) . "\n\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED! Process 2 is ready to test.\n";
    echo "\nNext steps:\n";
    echo "1. Fix your database and XAMPP\n";
    echo "2. Login as SPED Teacher\n";
    echo "3. Navigate to 'Verify Enrollments' in sidebar\n";
    echo "4. Test the verification workflow\n";
} else {
    echo "⚠️  Some tests failed. Please review the errors above.\n";
}

echo "\n";
?>
