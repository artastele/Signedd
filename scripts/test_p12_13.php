<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/TransitionWorkflowModel.php';

$model = new TransitionWorkflowModel();
$db = Database::getInstance()->getConnection();
$db->exec("SET FOREIGN_KEY_CHECKS=0");

// 1. Get/Create test users
$uniq = uniqid();
$password = 'password';
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// SPED Teacher
$stmt = $db->query("SELECT * FROM users WHERE role = 'sped_teacher' AND status = 'active' LIMIT 1");
$sped_teacher = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sped_teacher) {
    $stmt = $db->prepare("INSERT INTO users (name, first_name, last_name, email, password_hash, role, status) VALUES (?, 'SPED', 'Teacher', ?, ?, 'sped_teacher', 'active')");
    $stmt->execute(["sped_$uniq@test.local", $password_hash]);
    $sped_teacher_id = $db->lastInsertId();
    $sped_teacher_email = "sped_$uniq@test.local";
} else {
    $sped_teacher_id = (int)$sped_teacher['id'];
    $sped_teacher_email = $sped_teacher['email'];
}

// General Teacher
$general_teacher_email = "general_$uniq@test.local";
$stmt = $db->prepare("INSERT INTO users (name, first_name, last_name, email, password_hash, role, status, email_verified) VALUES (?, ?, ?, ?, ?, 'general_teacher', 'active', 1)");
$stmt->execute(["General Teacher $uniq", "General", "Teacher", $general_teacher_email, $password_hash]);
$general_teacher_id = (int)$db->lastInsertId();

// Parent
$parent_email = "parent_$uniq@test.local";
$stmt = $db->prepare("INSERT INTO users (name, first_name, last_name, email, password_hash, role, status, email_verified) VALUES (?, ?, ?, ?, ?, 'parent', 'active', 1)");
$stmt->execute(["Parent $uniq", "Parent", "Test", $parent_email, $password_hash]);
$parent_id = (int)$db->lastInsertId();

// Create Student
$stmt = $db->prepare("INSERT INTO enrollment_submissions (parent_id, enrollment_type, school_year, status, last_name, first_name, birth_date, sex, grade_level_to_enroll) VALUES (?, 'new', '2025-2026', 'verified', ?, 'Student', '2015-01-01', 'Male', 'Grade 1')");
$stmt->execute([$parent_id, $uniq]);
$enrollment_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO student_records (enrollment_id, student_name, verified_by, status) VALUES (?, ?, ?, 'active')");
$stmt->execute([$enrollment_id, "Student $uniq", $sped_teacher_id]);
$student_id = (int)$db->lastInsertId();

// Create meeting and PDSP
$stmt = $db->prepare("INSERT INTO iep_meetings (student_id, scheduled_by, meeting_date, status) VALUES (?, ?, '2025-01-01', 'completed')");
$stmt->execute([$student_id, $sped_teacher_id]);
$meeting_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO pdsp_records (student_id, meeting_id, status) VALUES (?, ?, 'signed')");
$stmt->execute([$student_id, $meeting_id]);
$pdsp_id = $db->lastInsertId();

// Create signed IEP record
$yesterday = date('Y-m-d', strtotime('-1 day'));
$stmt = $db->prepare("INSERT INTO iep_records (student_id, pdsp_id, drafted_by, school_year, status, re_evaluation_date) VALUES (?, ?, ?, '2025-2026', 'signed', ?)");
$stmt->execute([$student_id, $pdsp_id, $sped_teacher_id, $yesterday]);
$iep_id = (int)$db->lastInsertId();

// Create finalized Transition Readiness (P10)
$stmt = $db->prepare("INSERT INTO transition_readiness (student_id, iep_record_id, created_by, status, readiness_result) VALUES (?, ?, ?, 'finalized', 'Ready for Inclusion')");
$stmt->execute([$student_id, $iep_id, $sped_teacher_id]);
$readiness_id = $db->lastInsertId();

// Create finalized Individual Transition Plan (ITP)
$stmt = $db->prepare("INSERT INTO itp_records (student_id, transition_readiness_id, school_year, status, point_of_entry, drafted_by) VALUES (?, ?, '2025-2026', 'finalized', 'Regular Class Mainstreamed', ?)");
$stmt->execute([$student_id, $readiness_id, $sped_teacher_id]);
$itp_id = (int)$db->lastInsertId();

$db->exec("SET FOREIGN_KEY_CHECKS=1");

// Session cookies file
$cookie_jar = tempnam(sys_get_temp_dir(), 'cookies');
$base_url = 'http://localhost/Signedd/public';

function makeRequest($url, $method = 'GET', $data = []) {
    global $cookie_jar;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $http_code, 'body' => $response];
}

function makeRequestDebug($url, $method = 'GET', $data = [], $label = '') {
    $res = makeRequest($url, $method, $data);
    echo "--- REQUEST: $label ($method $url) ---\n";
    echo "HTTP CODE: " . $res['code'] . "\n";
    echo "BODY PREVIEW (300 chars): " . trim(substr(strip_tags($res['body']), 0, 300)) . "\n\n";
    return $res;
}

$results = [];

// 1. Log in as General Teacher before assignment (should be forbidden or redirect because not assigned)
makeRequestDebug($base_url . '/login', 'POST', ['email' => $general_teacher_email, 'password' => $password], 'Login General Teacher (Unassigned)');
$itgp_url = $base_url . "/iep/$iep_id/inclusive-iep-itgp";
$res = makeRequestDebug($itgp_url, 'GET', [], 'Get ITGP (Unassigned)');
if (stripos($res['body'], 'You are not assigned') !== false) {
    $results['P12 Gate (Unassigned GT Block)'] = 'PASS';
} else {
    $results['P12 Gate (Unassigned GT Block)'] = 'FAIL - GT accessed unassigned student';
}

// 2. Log in as SPED Teacher and assign General Teacher
unlink($cookie_jar);
$cookie_jar = tempnam(sys_get_temp_dir(), 'cookies');
makeRequestDebug($base_url . '/login', 'POST', ['email' => $sped_teacher_email, 'password' => $password], 'Login SPED Teacher');

$assign_url = $base_url . "/iep/$iep_id/inclusive-iep-itgp/assign";
$res = makeRequestDebug($assign_url, 'POST', ['general_teacher_id' => $general_teacher_id], 'Assign GT');

// Check in DB
$assigned = $db->query("SELECT * FROM general_teacher_assignments WHERE student_id = $student_id")->fetch(PDO::FETCH_ASSOC);
if ($assigned && (int)$assigned['general_teacher_id'] === $general_teacher_id) {
    $results['GT Assignment'] = 'PASS';
} else {
    $results['GT Assignment'] = 'FAIL - Assignment not saved in DB';
}

// 3. Log in as General Teacher (should now be allowed)
unlink($cookie_jar);
$cookie_jar = tempnam(sys_get_temp_dir(), 'cookies');
makeRequestDebug($base_url . '/login', 'POST', ['email' => $general_teacher_email, 'password' => $password], 'Login General Teacher (Assigned)');

$res = makeRequestDebug($itgp_url, 'GET', [], 'Get ITGP (Assigned)');
if ($res['code'] === 200 && stripos($res['body'], 'Inclusive IEP') !== false) {
    $results['P12 Gate (Assigned GT Allow)'] = 'PASS';
} else {
    $results['P12 Gate (Assigned GT Allow)'] = 'FAIL - GT blocked even when assigned';
}

// 4. Save draft ITGP
$post_data = [
    'itgp_goal' => 'Test Goal',
    'entry_point' => 'Regular Class',
    'learning_packages' => 'Vocational Package',
    'itgp_recommendations' => 'Grade 1 Bonifacio',
    'status' => 'draft',
    'activities' => [
        [
            'competency_skill' => 'Writing',
            'activities' => 'Write names',
            'time_frame' => 'Quarter 1',
            'person_responsible' => 'Teacher',
            'remarks' => 'Starting'
        ]
    ]
];
$res = makeRequestDebug($itgp_url, 'POST', $post_data, 'Save Draft ITGP');

// Check DB
$itgp_record = $db->query("SELECT * FROM itgp_records WHERE student_id = $student_id ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($itgp_record && $itgp_record['status'] === 'draft') {
    $results['P12 Save Draft'] = 'PASS';
} else {
    $results['P12 Save Draft'] = 'FAIL - ITGP draft status is not draft';
}

// 5. Post co-teaching comment
$comment_url = $base_url . "/iep/$iep_id/inclusive-iep-itgp/comment";
$res = makeRequestDebug($comment_url, 'POST', ['comment_text' => 'Hello from General Teacher!'], 'Post Comment');

// Check DB
$comment_record = $db->query("SELECT * FROM itgp_comments WHERE itgp_id = {$itgp_record['id']} ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($comment_record && $comment_record['comment_text'] === 'Hello from General Teacher!') {
    $results['P12 Comment Post'] = 'PASS';
} else {
    $results['P12 Comment Post'] = 'FAIL - Comment not found';
}

// 6. Test Process 13 lock gate (ITGP status is 'draft')
$placement_url = $base_url . "/iep/$iep_id/placement-notice";
$res = makeRequestDebug($placement_url, 'GET', [], 'Get Placement Notice (Locked)');
if (stripos($res['body'], 'Inclusive IEP &amp; ITGP (Process 12) must be finalized') !== false) {
    $results['P13 Lock Gate'] = 'PASS';
} else {
    $results['P13 Lock Gate'] = 'FAIL - Accessed placement notice while ITGP draft';
}

// 7. Validate Finalization criteria (should block if fields are empty)
$invalid_data = $post_data;
$invalid_data['status'] = 'finalized';
$invalid_data['itgp_goal'] = ''; // goal is required
$res = makeRequestDebug($itgp_url, 'POST', $invalid_data, 'Save Invalid ITGP (Finalize)');

$itgp_check = $db->query("SELECT * FROM itgp_records WHERE id = {$itgp_record['id']}")->fetch(PDO::FETCH_ASSOC);
if ($itgp_check['status'] === 'draft') {
    $results['P12 Finalize Validation Block'] = 'PASS';
} else {
    $results['P12 Finalize Validation Block'] = 'FAIL - Finalized with empty goal';
}

// 8. Valid Finalization
$valid_data = $post_data;
$valid_data['status'] = 'finalized';
$res = makeRequestDebug($itgp_url, 'POST', $valid_data, 'Save Valid ITGP (Finalize)');

$itgp_finalized = $db->query("SELECT * FROM itgp_records WHERE id = {$itgp_record['id']}")->fetch(PDO::FETCH_ASSOC);
if ($itgp_finalized && $itgp_finalized['status'] === 'finalized' && !empty($itgp_finalized['finalized_at'])) {
    $results['P12 Finalization Success'] = 'PASS';
} else {
    $results['P12 Finalization Success'] = 'FAIL - Status not updated to finalized';
}

// 9. Verify Process 13 is now unlocked
$res = makeRequestDebug($placement_url, 'GET', [], 'Get Placement Notice (Unlocked)');
if ($res['code'] === 200 && stripos($res['body'], 'Regular Class Placement Review') !== false) {
    $results['P13 Unlock Gate'] = 'PASS';
} else {
    $results['P13 Unlock Gate'] = 'FAIL - Placement notice still locked';
}

// 10. Submit Class Placement Hold
$res = makeRequestDebug($placement_url, 'POST', ['status' => 'on_hold', 'hold_reason' => 'Requires visual aid support tools'], 'Submit Hold');

// Check DB
$placement = $db->query("SELECT * FROM class_placements WHERE student_id = $student_id ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($placement && $placement['status'] === 'on_hold' && $placement['hold_reason'] === 'Requires visual aid support tools') {
    $results['P13 Hold Action'] = 'PASS';
} else {
    $results['P13 Hold Action'] = 'FAIL - Hold reason or status mismatch';
}

// Check student record status (should still be active)
$student = $db->query("SELECT status FROM student_records WHERE id = $student_id")->fetch(PDO::FETCH_ASSOC);
if ($student && $student['status'] === 'active') {
    $results['P13 Hold Student Status'] = 'PASS';
} else {
    $results['P13 Hold Student Status'] = 'FAIL - Student was archived on Hold';
}

// 11. Confirm Class Placement
$res = makeRequestDebug($placement_url, 'POST', ['status' => 'confirmed'], 'Submit Confirm');

// Check DB
$placement = $db->query("SELECT * FROM class_placements WHERE student_id = $student_id ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($placement && $placement['status'] === 'confirmed' && !empty($placement['confirmed_at'])) {
    $results['P13 Confirm Action'] = 'PASS';
} else {
    $results['P13 Confirm Action'] = 'FAIL - Placement not confirmed';
}

// Check student record status (should be mainstreamed)
$student = $db->query("SELECT status FROM student_records WHERE id = $student_id")->fetch(PDO::FETCH_ASSOC);
if ($student && $student['status'] === 'mainstreamed') {
    $results['P13 Archive Student'] = 'PASS';
} else {
    $results['P13 Archive Student'] = 'FAIL - Student status not set to mainstreamed';
}

// Output results
echo "\n--- SignED Process 12 & 13 Integration Test ---\n";
$all_pass = true;
foreach ($results as $k => $v) {
    echo str_pad($k, 40) . ": $v\n";
    if ($v !== 'PASS') {
        $all_pass = false;
    }
}

unlink($cookie_jar);
exit($all_pass ? 0 : 1);
