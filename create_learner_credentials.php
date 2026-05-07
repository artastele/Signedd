<?php
// Create learner credentials for existing student
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';
require_once 'app/Models/StudentModel.php';

$studentId = 12;

echo "=== Creating Learner Credentials for Student ID: $studentId ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Get student record
    echo "Step 1: Getting student record...\n";
    $stmt = $db->prepare("
        SELECT sr.*, es.* 
        FROM student_records sr
        JOIN enrollment_submissions es ON sr.enrollment_id = es.id
        WHERE sr.id = :id
    ");
    $stmt->execute(['id' => $studentId]);
    $student = $stmt->fetch();
    
    if (!$student) {
        throw new Exception("Student ID $studentId not found");
    }
    
    echo "✅ Found student: {$student['student_name']}\n";
    echo "   LRN: {$student['lrn']}\n";
    echo "   Enrollment ID: {$student['enrollment_id']}\n\n";
    
    // 2. Check if learner account already exists
    echo "Step 2: Checking for existing account...\n";
    $learnerEmail = 'learner_' . $student['lrn'] . '@spedlms.local';
    
    $stmt = $db->prepare("SELECT id, email, status FROM users WHERE email = :email");
    $stmt->execute(['email' => $learnerEmail]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "⚠️  Account already exists!\n";
        echo "   User ID: {$existingUser['id']}\n";
        echo "   Email: {$existingUser['email']}\n";
        echo "   Status: {$existingUser['status']}\n\n";
        
        echo "Do you want to reset the password? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim($line) !== 'y') {
            echo "Cancelled.\n";
            exit;
        }
        
        echo "\nStep 3: Resetting password...\n";
    } else {
        echo "✅ No existing account found. Creating new account...\n\n";
        echo "Step 3: Creating learner account...\n";
    }
    
    // 3. Create/Reset learner account
    $studentModel = new StudentModel();
    
    $enrollmentData = [
        'enrollment_id' => $student['enrollment_id'],
        'first_name' => $student['first_name'],
        'last_name' => $student['last_name']
    ];
    
    $accountData = $studentModel->createLearnerAccount(
        $studentId,
        $student['lrn'],
        $enrollmentData
    );
    
    echo "✅ Learner account created/reset successfully!\n\n";
    
    // 4. Display credentials
    echo "=== LEARNER CREDENTIALS ===\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Student Name:      {$student['student_name']}\n";
    echo "LRN (Username):    {$accountData['lrn']}\n";
    echo "Temporary Password: {$accountData['temp_password']}\n";
    echo "User ID:           {$accountData['user_id']}\n";
    echo "Account Type:      " . ($accountData['is_existing'] ? "Existing (Reset)" : "New") . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // 5. Update enrollment record
    echo "Step 4: Updating enrollment record...\n";
    $stmt = $db->prepare("
        UPDATE enrollment_submissions
        SET learner_account_created = TRUE
        WHERE id = :id
    ");
    $stmt->execute(['id' => $student['enrollment_id']]);
    echo "✅ Enrollment marked as account created\n\n";
    
    // 6. Send notification to parent
    echo "Step 5: Sending notification to parent...\n";
    require_once 'app/Models/NotificationModel.php';
    $notificationModel = new NotificationModel();
    
    $notificationModel->create(
        $student['parent_id'],
        'learner_credentials',
        'Learner Account Created',
        "Login credentials for {$student['student_name']}: LRN: {$accountData['lrn']}, Password: {$accountData['temp_password']}",
        ['student_id' => $studentId]
    );
    echo "✅ Notification sent to parent\n\n";
    
    echo "✅ ALL DONE!\n\n";
    echo "The learner can now login with:\n";
    echo "  Username: {$accountData['lrn']}\n";
    echo "  Password: {$accountData['temp_password']}\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
