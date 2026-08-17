<?php
// Script to register 3 demo staff accounts (Master Teacher, Guidance, General Teacher) for Piedad Central Elementary School

require_once __DIR__ . '/../config/db.php';

function seedStaffInDb($pdo, $label) {
    echo "--- Seeding Staff in $label ---\n";
    
    // Find Piedad Central Elementary School ID
    $stmt = $pdo->query("SELECT id, school_name FROM schools WHERE school_id = '129688' OR school_name LIKE '%Piedad%' LIMIT 1");
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$school) {
        $stmt = $pdo->query("SELECT id, school_name FROM schools ORDER BY id ASC LIMIT 1");
        $school = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    $schoolId = $school ? (int)$school['id'] : 1;
    $schoolName = $school ? $school['school_name'] : 'Piedad Central Elementary School';
    
    echo "Using School: ID {$schoolId} - {$schoolName}\n";
    
    $passwordHash = password_hash('Password123!', PASSWORD_DEFAULT);
    
    $demoStaff = [
        [
            'name' => 'Maria Santos',
            'email' => 'masterteacher.piedad@signed.ph',
            'role' => 'master_teacher',
            'label' => 'Master Teacher'
        ],
        [
            'name' => 'Elena Ramos',
            'email' => 'guidance.piedad@signed.ph',
            'role' => 'guidance',
            'label' => 'Guidance Counselor'
        ],
        [
            'name' => 'Roberto Cruz',
            'email' => 'generalteacher.piedad@signed.ph',
            'role' => 'general_teacher',
            'label' => 'General Teacher'
        ]
    ];
    
    foreach ($demoStaff as $staff) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $staff['email']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $userId = (int)$existing['id'];
            $stmt = $pdo->prepare("UPDATE users SET name = :name, password_hash = :pass, role = 'user', school_id = :sid, status = 'active', email_verified = 1 WHERE id = :uid");
            $stmt->execute([
                'name' => $staff['name'],
                'pass' => $passwordHash,
                'sid' => $schoolId,
                'uid' => $userId
            ]);
            echo "[UPDATED] User {$staff['email']} (ID: {$userId})\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, email_verified, school_id, created_at) VALUES (:name, :email, :pass, 'user', 'active', 1, :sid, NOW())");
            $stmt->execute([
                'name' => $staff['name'],
                'email' => $staff['email'],
                'pass' => $passwordHash,
                'sid' => $schoolId
            ]);
            $userId = (int)$pdo->lastInsertId();
            echo "[CREATED] User {$staff['email']} (ID: {$userId})\n";
        }
        
        // Remove old role requests for this user
        $stmt = $pdo->prepare("DELETE FROM role_requests WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        
        // Insert pending role request
        $docsJson = json_encode([
            'school_id' => $schoolId,
            'school_name' => $schoolName,
            'license_number' => 'LPT-' . rand(100000, 999999),
            'years_experience' => '5 years'
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO role_requests (user_id, requested_role, status, approver_role, submitted_docs, created_at) VALUES (:uid, :role, 'pending', 'principal', :docs, NOW())");
        $stmt->execute([
            'uid' => $userId,
            'role' => $staff['role'],
            'docs' => $docsJson
        ]);
        
        echo "  └─ [PENDING ROLE REQUEST] Registered for {$staff['label']} ('{$staff['role']}')\n";
    }
}

// 1. Seed Local Database
try {
    $localPdo = Database::getInstance()->getConnection();
    seedStaffInDb($localPdo, "LOCAL LARAGON DB");
} catch (Exception $e) {
    echo "[LOCAL DB ERROR] " . $e->getMessage() . "\n";
}
