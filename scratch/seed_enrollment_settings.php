<?php
require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('INSERT IGNORE INTO system_settings (setting_key, setting_value, category, description) VALUES (:key, :val, :cat, :desc)');

$items = [
    ['enrollment_sy', '2026-2027', 'enrollment', 'Active enrollment school year'],
    ['enrollment_status', 'open', 'enrollment', 'Enrollment status'],
    ['enrollment_start_date', '2026-06-01', 'enrollment', 'Enrollment period start date'],
    ['enrollment_end_date', '2026-08-15', 'enrollment', 'Enrollment period end date'],
    ['enrollment_guidelines', "Official DepEd SPED Enrollment Guidelines:\n1. PSA Birth Certificate\n2. Form 138/SF10 (Progress Report Card)\n3. Medical / Diagnostic Evaluation Report\n4. PWD ID Card (if available)", 'enrollment', 'Official enrollment requirements'],
    ['enrollment_announcement', 'Official Enrollment for SY 2026-2027 is now OPEN across all registered SPED Centers. Please review the guidelines below before registering.', 'enrollment', 'Public announcement banner']
];

foreach ($items as $item) {
    $stmt->execute([
        'key'  => $item[0],
        'val'  => $item[1],
        'cat'  => $item[2],
        'desc' => $item[3]
    ]);
}

echo "SEEDED_SUCCESSFULLY\n";
