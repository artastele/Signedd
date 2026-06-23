<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();

$db->exec("SET FOREIGN_KEY_CHECKS=0");

$stmt = $db->query("SELECT * FROM users WHERE role = 'sped_teacher' LIMIT 1");
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
$teacher_id = $teacher['id'];
$teacher_email = $teacher['email'];
$password = 'password';
$uniq = uniqid();

$stmt = $db->prepare("INSERT INTO users (name, first_name, last_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, 'parent', 'active')");
$stmt->execute(["Parent $uniq", "Parent", $uniq, "parent_$uniq@test.local", password_hash('password', PASSWORD_BCRYPT)]);
$parent_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO enrollment_submissions (parent_id, enrollment_type, school_year, status, last_name, first_name, birth_date, sex, grade_level_to_enroll) VALUES (?, 'new', '2025-2026', 'verified', ?, 'Student', '2015-01-01', 'Male', 'Grade 1')");
$stmt->execute([$parent_id, $uniq]);
$enrollment_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO student_records (enrollment_id, student_name, verified_by) VALUES (?, ?, ?)");
$stmt->execute([$enrollment_id, "Student $uniq", $teacher_id]);
$student_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO iep_meetings (student_id, scheduled_by, meeting_date, status) VALUES (?, ?, '2025-01-01', 'completed')");
$stmt->execute([$student_id, $teacher_id]);
$meeting_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO pdsp_records (student_id, meeting_id, status) VALUES (?, ?, 'signed')");
$stmt->execute([$student_id, $meeting_id]);
$pdsp_id = $db->lastInsertId();

$yesterday = date('Y-m-d', strtotime('-1 day'));
$stmt = $db->prepare("INSERT INTO iep_records (student_id, pdsp_id, drafted_by, school_year, status, re_evaluation_date) VALUES (?, ?, ?, '2025-2026', 'signed', ?)");
$stmt->execute([$student_id, $pdsp_id, $teacher_id, $yesterday]);
$iep_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO iep_steps (iep_id, step_number, step_domain, step_objective) VALUES (?, ?, ?, ?)");
$stmt->execute([$iep_id, 1, 'Self-Care', 'Learn to tie shoes']);
$step1_id = $db->lastInsertId();
$stmt->execute([$iep_id, 2, 'Academics', 'Read 10 words']);
$step2_id = $db->lastInsertId();

$stmt = $db->prepare("INSERT INTO progress_reports (student_id, iep_record_id, created_by, status) VALUES (?, ?, ?, 'draft')");
$stmt->execute([$student_id, $iep_id, $teacher_id]);
$pr_id = $db->lastInsertId();

$db->exec("SET FOREIGN_KEY_CHECKS=1");

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

$results = [];

$res = makeRequest($base_url . '/login', 'POST', ['email' => $teacher_email, 'password' => $password]);
if (stripos($res['body'], 'Dashboard') !== false || stripos($res['body'], 'Logout') !== false) {
    $results['Login'] = 'PASS';
} else {
    $results['Login'] = 'FAIL';
}

$url = $base_url . "/iep/$iep_id/transition-readiness";
$res = makeRequest($url);
if (stripos($res['body'], 'locked') !== false || stripos($res['body'], 'must be finalized') !== false || stripos($res['body'], 'draft') !== false) {
    if (stripos($res['body'], 'id="finalize-btn"') === false && stripos($res['body'], 'suggested_status') === false) {
        $results['Gate Test (Block)'] = 'PASS';
    } else {
        $results['Gate Test (Block)'] = 'FAIL - Elements accessible';
    }
} else {
    $results['Gate Test (Block)'] = 'FAIL - Lock message not found.';
}

$db->exec("UPDATE progress_reports SET status = 'finalized' WHERE id = $pr_id");
$res = makeRequest($url);
if (stripos($res['body'], 'locked') === false && stripos($res['body'], 'suggested') !== false) {
    $results['Gate Test (Unlock)'] = 'PASS';
} else {
    $results['Gate Test (Unlock)'] = 'FAIL - Page did not unlock properly.';
}

// Auto-generation
$stmt = $db->query("SELECT * FROM transition_readiness WHERE iep_record_id = $iep_id");
$readiness = $stmt->fetch(PDO::FETCH_ASSOC);
if ($readiness && $readiness['status'] === 'draft') {
    $results['Auto-generation'] = 'PASS';
} else {
    $results['Auto-generation'] = 'FAIL - Readiness draft not found in DB.';
}

// Regardless of auto-generation, we will now submit manually to test Override and Draft.
$post_data = ['status' => 'draft', 'overall_status' => 'ready', 'overall_remarks' => 'Test'];
$post_data['goals'][$step1_id] = [
    'goal_text' => 'Learn to tie shoes', 'pdsp_domain' => 'Self-Care',
    'suggested_status' => 'partial', 'final_status' => 'ready', 'status_overridden' => 1, 'remarks' => 'Override test'
];
$post_data['goals'][$step2_id] = [
    'goal_text' => 'Read 10 words', 'pdsp_domain' => 'Academics',
    'suggested_status' => 'partial', 'final_status' => 'partial', 'status_overridden' => 0, 'remarks' => ''
];

$res = makeRequest($url, 'POST', $post_data);
$readiness_updated = $db->query("SELECT * FROM transition_readiness WHERE iep_record_id = $iep_id")->fetch(PDO::FETCH_ASSOC);
if ($readiness_updated) {
    $tr_id = $readiness_updated['id'];
    $goals_updated = $db->query("SELECT * FROM transition_readiness_goals WHERE transition_readiness_id = $tr_id")->fetchAll(PDO::FETCH_ASSOC);
    $goal1 = array_filter($goals_updated, fn($g) => $g['iep_step_id'] == $step1_id);
    $goal1 = reset($goal1);
    if ($readiness_updated['overall_status'] === 'ready' && $goal1['final_status'] === 'ready' && $goal1['suggested_status'] === 'partial') {
        $results['Override Test'] = 'PASS';
        $results['Submit Test (Draft)'] = 'PASS';
    } else {
        $results['Override Test'] = 'FAIL - Data not matching override logic.';
        $results['Submit Test (Draft)'] = 'FAIL';
    }
} else {
    $results['Override Test'] = 'FAIL - Did not save.';
    $results['Submit Test (Draft)'] = 'FAIL';
    $tr_id = 0;
}

if ($tr_id) {
    $post_data['status'] = 'finalized';
    $res = makeRequest($url, 'POST', $post_data);
    $readiness_finalized = $db->query("SELECT * FROM transition_readiness WHERE id = $tr_id")->fetch(PDO::FETCH_ASSOC);
    if ($readiness_finalized['status'] === 'finalized' && !empty($readiness_finalized['finalized_at'])) {
        $results['Submit Test (Finalize)'] = 'PASS';
    } else {
        $results['Submit Test (Finalize)'] = 'FAIL';
    }
} else {
    $results['Submit Test (Finalize)'] = 'SKIP';
}

$itp_url = $base_url . "/iep/$iep_id/inclusion-planning/itp";
$res = makeRequest($itp_url);
if ($res['code'] === 200 && stripos($res['body'], 'Individual Transition Plan - SignED') !== false) {
    $results['Unlock Test'] = 'PASS';
} else {
    $results['Unlock Test'] = 'FAIL (Or Unverified) - Code: ' . $res['code'] . ' Body snippet: ' . substr(strip_tags($res['body']), 0, 200);
}

$stmt = $db->query("SELECT * FROM notifications WHERE (type LIKE '%transition%' OR message LIKE '%transition%') AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
$notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($notifs) > 0) {
    $results['Notification Test'] = 'PASS';
} else {
    $results['Notification Test'] = 'FAIL - No notification found';
}

echo "\n--- RESULTS ---\n";
foreach ($results as $k => $v) {
    echo str_pad($k, 25) . ": $v\n";
}

if (!empty($readiness_finalized)) {
    echo "\n--- FINAL DB ROW ---\n";
    print_r($readiness_finalized);
}

unlink($cookie_jar);
