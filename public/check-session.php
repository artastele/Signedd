<?php
// Check session values
session_start();

header('Content-Type: text/plain; charset=utf-8');

echo "=== SESSION CHECK ===\n\n";

// 1. Check if session is started
echo "1. SESSION STATUS:\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "   ✓ Session is ACTIVE\n";
    echo "   Session ID: " . session_id() . "\n\n";
} else {
    echo "   ❌ Session is NOT active\n\n";
}

// 2. Check session variables
echo "2. SESSION VARIABLES:\n";
if (empty($_SESSION)) {
    echo "   ❌ NO SESSION DATA - You are NOT logged in!\n\n";
    echo "   Please login first at:\n";
    echo "   http://localhost/Signedd/public/login\n\n";
} else {
    echo "   ✓ Session data exists\n\n";
    
    // Show all session variables
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            echo "   $key: " . json_encode($value) . "\n";
        } else {
            echo "   $key: $value\n";
        }
    }
    echo "\n";
}

// 3. Check specific required values
echo "3. REQUIRED VALUES FOR ENROLLMENT:\n";

$required = ['user_id', 'role', 'email', 'name'];
$allPresent = true;

foreach ($required as $key) {
    if (isset($_SESSION[$key])) {
        echo "   ✓ $key: {$_SESSION[$key]}\n";
    } else {
        echo "   ❌ $key: NOT SET\n";
        $allPresent = false;
    }
}

echo "\n";

// 4. Check if user is parent
echo "4. PARENT ROLE CHECK:\n";
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'parent') {
        echo "   ✓ You are logged in as PARENT\n";
        echo "   ✓ You CAN submit enrollments\n\n";
    } else {
        echo "   ❌ You are logged in as: {$_SESSION['role']}\n";
        echo "   ❌ Only PARENTS can submit enrollments\n";
        echo "   You need to:\n";
        echo "   1. Logout\n";
        echo "   2. Login with a parent account\n";
        echo "   3. Or apply for parent role\n\n";
    }
} else {
    echo "   ❌ Role not set in session\n";
    echo "   You need to login\n\n";
}

// 5. Check if user exists in database
if (isset($_SESSION['user_id'])) {
    echo "5. DATABASE CHECK:\n";
    try {
        require_once __DIR__ . '/../config/db.php';
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT id, name, email, role, status FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "   ✓ User found in database\n";
            echo "   ID: {$user['id']}\n";
            echo "   Name: {$user['name']}\n";
            echo "   Email: {$user['email']}\n";
            echo "   Role: {$user['role']}\n";
            echo "   Status: {$user['status']}\n\n";
            
            // Check if session matches database
            if ($_SESSION['role'] !== $user['role']) {
                echo "   ⚠️  WARNING: Session role doesn't match database!\n";
                echo "   Session role: {$_SESSION['role']}\n";
                echo "   Database role: {$user['role']}\n";
                echo "   You should logout and login again.\n\n";
            }
        } else {
            echo "   ❌ User NOT found in database!\n";
            echo "   User ID {$_SESSION['user_id']} doesn't exist.\n";
            echo "   Your session is invalid.\n\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Database error: " . $e->getMessage() . "\n\n";
    }
}

// 6. Final verdict
echo "=== VERDICT ===\n";
if ($allPresent && isset($_SESSION['role']) && $_SESSION['role'] === 'parent') {
    echo "✓✓✓ SESSION IS VALID!\n";
    echo "You are properly logged in as a parent.\n";
    echo "You should be able to submit enrollments.\n\n";
    
    echo "If enrollment still doesn't work, the issue is:\n";
    echo "1. Database configuration (autocommit)\n";
    echo "2. EnrollmentController logic\n";
    echo "3. Form validation\n";
} else {
    echo "❌ SESSION ISSUE DETECTED!\n\n";
    
    if (empty($_SESSION)) {
        echo "PROBLEM: You are NOT logged in.\n";
        echo "SOLUTION: Login at http://localhost/Signedd/public/login\n";
    } elseif (!isset($_SESSION['role']) || $_SESSION['role'] !== 'parent') {
        echo "PROBLEM: You are not logged in as a parent.\n";
        echo "SOLUTION:\n";
        echo "1. Logout\n";
        echo "2. Login with a parent account\n";
        echo "3. Or apply for parent role on the dashboard\n";
    } else {
        echo "PROBLEM: Some session values are missing.\n";
        echo "SOLUTION: Logout and login again.\n";
    }
}

echo "\n";

// 7. Show enrollment form access
echo "=== ENROLLMENT FORM ACCESS ===\n";
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'parent') {
    echo "You can access the enrollment form at:\n";
    echo "http://localhost/Signedd/public/enrollment\n";
} else {
    echo "You CANNOT access the enrollment form yet.\n";
    echo "Login as a parent first.\n";
}
