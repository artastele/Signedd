<?php
/**
 * Seed 3 demo students fully enrolled in the SPED program (Process 2 complete).
 * Usage: php scripts/seed_demo_enrollments.php
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/EnrollmentModel.php';
require_once __DIR__ . '/../app/Models/StudentModel.php';

$db = Database::getInstance()->getConnection();
$enrollmentModel = new EnrollmentModel();
$studentModel = new StudentModel();
$passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password
$schoolYear = '2025-2026';
$signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

$sped = $db->query("SELECT id FROM users WHERE email = 'demo.sped@spedlms.local' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$sped) {
    echo "FAIL: demo.sped@spedlms.local not found\n";
    exit(1);
}
$spedTeacherId = (int) $sped['id'];

$uploadDir = __DIR__ . '/../public/uploads/enrollment';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

$parents = [
    ['email' => 'demo.parent@spedlms.local',  'name' => 'Demo Parent',  'first' => 'Demo', 'last' => 'Parent',  'id' => null],
    ['email' => 'demo.parent2@spedlms.local', 'name' => 'Maria Parent', 'first' => 'Maria', 'last' => 'Parent', 'id' => null],
    ['email' => 'demo.parent3@spedlms.local', 'name' => 'Pedro Parent', 'first' => 'Pedro', 'last' => 'Parent', 'id' => null],
];

$students = [
    [
        'first_name' => 'Miguel',
        'middle_name' => 'A.',
        'last_name' => 'Santos',
        'birth_date' => '2017-03-15',
        'sex' => 'Male',
        'grade_level_to_enroll' => 'Grade 2',
        'birth_place' => 'Quezon City, Metro Manila',
        'mother_tongue' => 'Filipino',
    ],
    [
        'first_name' => 'Ana',
        'middle_name' => 'B.',
        'last_name' => 'Reyes',
        'birth_date' => '2016-07-22',
        'sex' => 'Female',
        'grade_level_to_enroll' => 'Grade 3',
        'birth_place' => 'Manila City, Metro Manila',
        'mother_tongue' => 'Filipino',
    ],
    [
        'first_name' => 'Jose',
        'middle_name' => 'C.',
        'last_name' => 'Garcia',
        'birth_date' => '2015-11-08',
        'sex' => 'Male',
        'grade_level_to_enroll' => 'Grade 4',
        'birth_place' => 'Pasig City, Metro Manila',
        'mother_tongue' => 'Filipino',
    ],
];

$docTypes = ['psa_birth_cert', 'pwd_id', 'beef_form'];
$enrolled = [];

function ensureParent(PDO $db, array &$parent, string $passwordHash): void {
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $parent['email']]);
    $row = $stmt->fetch();
    if ($row) {
        $parent['id'] = (int) $row['id'];
        return;
    }

    $insert = $db->prepare("
        INSERT INTO users (name, first_name, last_name, email, contact_number, password_hash, role, status, email_verified, auth_provider)
        VALUES (:name, :first, :last, :email, :contact, :hash, 'parent', 'active', 1, 'local')
    ");
    $insert->execute([
        'name' => $parent['name'],
        'first' => $parent['first'],
        'last' => $parent['last'],
        'email' => $parent['email'],
        'contact' => '0912345678' . ($parent['email'] === 'demo.parent@spedlms.local' ? '1' : substr($parent['email'], 13, 1)),
        'hash' => $passwordHash,
    ]);
    $parent['id'] = (int) $db->lastInsertId();
    echo "Created parent: {$parent['email']}\n";
}

function findEnrollmentId(PDO $db, int $parentId, string $firstName, string $lastName): ?int {
    $stmt = $db->prepare("
        SELECT id FROM enrollment_submissions
        WHERE parent_id = :parent_id AND first_name = :first AND last_name = :last AND is_draft = 0
        LIMIT 1
    ");
    $stmt->execute(['parent_id' => $parentId, 'first' => $firstName, 'last' => $lastName]);
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

function completeSpedEnrollment(
    EnrollmentModel $enrollmentModel,
    StudentModel $studentModel,
    int $enrollmentId,
    int $spedTeacherId
): array {
    $enrollment = $enrollmentModel->findById($enrollmentId);
    if (!$enrollment) {
        throw new RuntimeException("Enrollment #{$enrollmentId} not found");
    }

    if ($enrollment['status'] === 'verified' && $enrollment['learner_account_created']) {
        $stmt = Database::getInstance()->getConnection()->prepare("
            SELECT sr.id, sr.lrn, sr.student_name
            FROM student_records sr
            WHERE sr.enrollment_id = :enrollment_id
            LIMIT 1
        ");
        $stmt->execute(['enrollment_id' => $enrollmentId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'student_id' => (int) $existing['id'],
            'lrn' => $existing['lrn'],
            'name' => $existing['student_name'],
            'temp_password' => '(already enrolled)',
            'skipped' => true,
        ];
    }

    foreach ($enrollmentModel->getDocuments($enrollmentId) as $doc) {
        if ($doc['status'] !== 'approved') {
            $enrollmentModel->updateDocumentStatus($doc['id'], 'approved', $spedTeacherId);
        }
    }

    $studentData = $studentModel->createStudentRecord($enrollmentId, $spedTeacherId);
    $accountData = $studentModel->createLearnerAccount(
        $studentData['id'],
        $studentData['lrn'],
        $enrollment
    );

    $enrollmentModel->updateStatus($enrollmentId, 'verified', $spedTeacherId);
    $enrollmentModel->markLearnerAccountCreated($enrollmentId);
    $enrollmentModel->update($enrollmentId, ['lrn' => $studentData['lrn']]);

    return [
        'student_id' => (int) $studentData['id'],
        'lrn' => $studentData['lrn'],
        'name' => $studentData['name'],
        'temp_password' => $accountData['temp_password'],
        'skipped' => false,
    ];
}

foreach ($parents as $i => &$parent) {
    ensureParent($db, $parent, $passwordHash);
    $student = $students[$i];
    $enrollmentId = findEnrollmentId($db, $parent['id'], $student['first_name'], $student['last_name']);

    if (!$enrollmentId) {
        $birth = new DateTime($student['birth_date']);
        $data = array_merge($student, [
            'parent_id' => $parent['id'],
            'enrollment_type' => 'new',
            'school_year' => $schoolYear,
            'is_draft' => false,
            'status' => 'pending',
            'submitted_at' => date('Y-m-d H:i:s'),
            'age' => (int) $birth->diff(new DateTime())->y,
            'disability_hearing' => 1,
            'current_house_no' => '123',
            'current_barangay' => 'San Antonio',
            'current_city' => 'Quezon City',
            'current_province' => 'Metro Manila',
            'current_zip_code' => '1100',
            'guardian_first_name' => $parent['first'],
            'guardian_last_name' => $parent['last'],
            'guardian_contact_number' => '0912345678' . ($i + 1),
            'modality_face_to_face' => 1,
            'modality_blended' => 1,
            'signature_data' => $signature,
            'date_signed' => date('Y-m-d'),
        ]);

        $enrollmentId = $enrollmentModel->create($data);
        echo "Created enrollment #{$enrollmentId}: {$student['first_name']} {$student['last_name']}\n";

        foreach ($docTypes as $docType) {
            $filename = "demo_{$enrollmentId}_{$docType}.png";
            file_put_contents($uploadDir . '/' . $filename, $png);
            $enrollmentModel->addDocument($enrollmentId, $docType, 'uploads/enrollment/' . $filename);
        }
    }

    $result = completeSpedEnrollment($enrollmentModel, $studentModel, $enrollmentId, $spedTeacherId);
    $action = $result['skipped'] ? 'Already enrolled' : 'Enrolled in SPED';
    echo "{$action}: {$result['name']} | LRN {$result['lrn']} | learner password {$result['temp_password']}\n";

    $enrolled[] = array_merge($result, [
        'parent_email' => $parent['email'],
        'grade' => $student['grade_level_to_enroll'],
    ]);
}
unset($parent);

echo "\n--- Self-check ---\n";

$verified = $studentModel->getVerifiedStudents();
echo 'Verified SPED students: ' . count($verified) . "\n";
foreach ($verified as $row) {
    echo "  {$row['student_name']} | LRN {$row['lrn']} | {$row['disability_type']} | {$row['grade_level_to_enroll']}\n";
}

$pending = $enrollmentModel->getPending();
echo 'Pending enrollments remaining: ' . count($pending) . "\n";

if (count($verified) < 3) {
    echo "FAIL: expected at least 3 verified SPED students\n";
    exit(1);
}

echo "PASS: 3 students enrolled in SPED program\n";
echo "SPED teacher: demo.sped@spedlms.local / password\n";
echo "Students list: http://localhost/Signedd/public/students\n";
echo "Assessment:    http://localhost/Signedd/public/assessment\n";
