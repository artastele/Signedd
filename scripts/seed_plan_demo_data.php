<?php
/**
 * Seed Capstone Demo Accounts from DEMO_DATA_PLAN.md
 * Usage: php scripts/seed_plan_demo_data.php
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/EnrollmentModel.php';
require_once __DIR__ . '/../app/Models/StudentModel.php';

$db = Database::getInstance()->getConnection();
$enrollmentModel = new EnrollmentModel();
$studentModel = new StudentModel();

$demoPassword = 'ChangeMe_DemoPassword123!';
$passwordHash = password_hash($demoPassword, PASSWORD_BCRYPT);
$schoolYear = '2026-2027';
$signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

// Ensure upload directory exists
$uploadDir = __DIR__ . '/../public/uploads/enrollment';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

// 1. Create or update the primary demo roles
$usersToCreate = [
    [
        'email' => 'admin.demo@signed.local',
        'name' => 'Admin Demo',
        'first_name' => 'Admin',
        'last_name' => 'Demo',
        'role' => 'admin'
    ],
    [
        'email' => 'parent.demo@signed.local',
        'name' => 'Maria Santos',
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'role' => 'parent'
    ],
    [
        'email' => 'sped.teacher.demo@signed.local',
        'name' => 'Teacher Demo',
        'first_name' => 'Teacher',
        'last_name' => 'Demo',
        'role' => 'sped_teacher'
    ],
    [
        'email' => 'guidance.demo@signed.local',
        'name' => 'Guidance Demo',
        'first_name' => 'Guidance',
        'last_name' => 'Demo',
        'role' => 'guidance'
    ],
    [
        'email' => 'principal.demo@signed.local',
        'name' => 'Principal Demo',
        'first_name' => 'Principal',
        'last_name' => 'Demo',
        'role' => 'principal'
    ],
    [
        'email' => 'master.teacher.demo@signed.local',
        'name' => 'Master Teacher Demo',
        'first_name' => 'Master Teacher',
        'last_name' => 'Demo',
        'role' => 'master_teacher'
    ]
];

$userIds = [];

foreach ($usersToCreate as $u) {
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $u['email']]);
    $row = $stmt->fetch();
    
    if ($row) {
        $userIds[$u['role']] = (int)$row['id'];
        // Update password just in case
        $update = $db->prepare("UPDATE users SET password_hash = :hash, name = :name, first_name = :first, last_name = :last, status = 'active', email_verified = 1 WHERE id = :id");
        $update->execute([
            'hash' => $passwordHash,
            'name' => $u['name'],
            'first' => $u['first_name'],
            'last' => $u['last_name'],
            'id' => $row['id']
        ]);
        echo "Updated existing user: {$u['email']}\n";
    } else {
        $insert = $db->prepare("
            INSERT INTO users (name, first_name, last_name, email, password_hash, role, status, email_verified, auth_provider)
            VALUES (:name, :first, :last, :email, :hash, :role, 'active', 1, 'local')
        ");
        $insert->execute([
            'name' => $u['name'],
            'first' => $u['first_name'],
            'last' => $u['last_name'],
            'email' => $u['email'],
            'hash' => $passwordHash,
            'role' => $u['role']
        ]);
        $userIds[$u['role']] = (int)$db->lastInsertId();
        echo "Created user: {$u['email']}\n";
    }
}

// 2. Create Student Enrollment for Juan Santos
$parentId = $userIds['parent'];
$spedTeacherId = $userIds['sped_teacher'];

$stmt = $db->prepare("
    SELECT id FROM enrollment_submissions 
    WHERE parent_id = :parent_id AND first_name = 'Juan' AND last_name = 'Santos'
    LIMIT 1
");
$stmt->execute(['parent_id' => $parentId]);
$enrollmentRow = $stmt->fetch();

$enrollmentId = null;
if ($enrollmentRow) {
    $enrollmentId = (int)$enrollmentRow['id'];
    echo "Found existing enrollment for Juan Santos: #$enrollmentId\n";
} else {
    $enrollmentData = [
        'parent_id' => $parentId,
        'enrollment_type' => 'new',
        'school_year' => $schoolYear,
        'is_draft' => false,
        'status' => 'pending',
        'submitted_at' => date('Y-m-d H:i:s'),
        'first_name' => 'Juan',
        'middle_name' => 'D.',
        'last_name' => 'Santos',
        'birth_date' => '2016-08-15',
        'sex' => 'Male',
        'grade_level_to_enroll' => 'Grade 3',
        'birth_place' => 'Davao City',
        'mother_tongue' => 'Filipino',
        'age' => 9,
        'disability_hearing' => 1,
        'current_house_no' => '456',
        'current_barangay' => 'Buhangin',
        'current_city' => 'Davao City',
        'current_province' => 'Davao del Sur',
        'current_zip_code' => '8000',
        'guardian_first_name' => 'Maria',
        'guardian_last_name' => 'Santos',
        'guardian_contact_number' => '09123456789',
        'modality_face_to_face' => 1,
        'modality_blended' => 1,
        'signature_data' => $signature,
        'date_signed' => date('Y-m-d'),
        'lrn' => '123456789012'
    ];
    
    $enrollmentId = $enrollmentModel->create($enrollmentData);
    echo "Created enrollment #$enrollmentId for Juan Santos\n";
    
    // Add fake documents
    $docTypes = ['psa_birth_cert', 'pwd_id', 'beef_form'];
    foreach ($docTypes as $docType) {
        $filename = "demo_{$enrollmentId}_{$docType}.png";
        file_put_contents($uploadDir . '/' . $filename, $png);
        $enrollmentModel->addDocument($enrollmentId, $docType, 'uploads/enrollment/' . $filename);
    }
}

// 3. Process the enrollment to verify and create student record / learner account
$studentResult = null;
$stmt = $db->prepare("
    SELECT sr.id, sr.student_id, sr.lrn, sr.student_name 
    FROM student_records sr 
    WHERE sr.enrollment_id = :enrollment_id 
    LIMIT 1
");
$stmt->execute(['enrollment_id' => $enrollmentId]);
$studentRecord = $stmt->fetch();

if ($studentRecord) {
    echo "Student record already exists for Juan Santos: Student ID {$studentRecord['student_id']}\n";
    $studentResult = [
        'student_id' => (int)$studentRecord['id'],
        'student_id_code' => $studentRecord['student_id'],
        'lrn' => $studentRecord['lrn'],
        'name' => $studentRecord['student_name']
    ];
} else {
    // Approve documents
    foreach ($enrollmentModel->getDocuments($enrollmentId) as $doc) {
        $enrollmentModel->updateDocumentStatus($doc['id'], 'approved', $spedTeacherId);
    }
    
    // Create Student Record
    $studentData = $studentModel->createStudentRecord($enrollmentId, $spedTeacherId);
    
    // Create Learner Account
    $enrollment = $enrollmentModel->findById($enrollmentId);
    $accountData = $studentModel->createLearnerAccount(
        $studentData['id'],
        $studentData['student_id'],
        $enrollment
    );
    
    // Set enrollment as verified
    $enrollmentModel->updateStatus($enrollmentId, 'verified', $spedTeacherId);
    $enrollmentModel->markLearnerAccountCreated($enrollmentId);
    
    $studentResult = [
        'student_id' => (int)$studentData['id'],
        'student_id_code' => $studentData['student_id'],
        'lrn' => $studentData['lrn'],
        'name' => $studentData['name']
    ];
    echo "Created student record and learner account for Juan Santos\n";
}

// 4. Update the learner account's password to the demo password
$learnerEmail = 'learner_' . $studentResult['student_id_code'] . '@spedlms.local';
$stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
$stmt->execute(['hash' => $passwordHash, 'email' => $learnerEmail]);
echo "Updated learner user {$learnerEmail} password to: {$demoPassword}\n";

// Also support logging in with LRN directly, verify if there is a learner user with that LRN
$lrnLearnerEmail = 'learner_' . $studentResult['lrn'] . '@spedlms.local';
$stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $lrnLearnerEmail]);
if ($stmt->fetch()) {
    $stmtUpdate = $db->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
    $stmtUpdate->execute(['hash' => $passwordHash, 'email' => $lrnLearnerEmail]);
    echo "Updated legacy LRN learner user {$lrnLearnerEmail} password to: {$demoPassword}\n";
} else {
    // Let's create it as a backup or alias
    $insertLrnUser = $db->prepare("
        INSERT INTO users (name, first_name, last_name, email, password_hash, role, status, email_verified, auth_provider)
        VALUES (:name, :first, :last, :email, :hash, 'learner', 'active', 1, 'local')
    ");
    $insertLrnUser->execute([
        'name' => 'Juan Santos (LRN)',
        'first' => 'Juan',
        'last' => 'Santos',
        'email' => $lrnLearnerEmail,
        'hash' => $passwordHash
    ]);
    echo "Created backup LRN learner user {$lrnLearnerEmail} password to: {$demoPassword}\n";
}

echo "\n============================================\n";
echo "DEMO SEEDING COMPLETED SUCCESSFULLY!\n";
echo "Password for all accounts: {$demoPassword}\n";
echo "--------------------------------------------\n";
echo "SPED Teacher:   sped.teacher.demo@signed.local\n";
echo "Parent:         parent.demo@signed.local\n";
echo "Guidance:       guidance.demo@signed.local\n";
echo "Principal:      principal.demo@signed.local\n";
echo "Master Teacher: master.teacher.demo@signed.local\n";
echo "Admin:          admin.demo@signed.local\n";
echo "Learner:        {$studentResult['student_id_code']} (or LRN: {$studentResult['lrn']})\n";
echo "============================================\n";
