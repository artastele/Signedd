<?php
require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

echo "=== USERS ===\n";
$users = $db->query('SELECT id, email, role, name, status, password_hash FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    $matchesPassword123 = password_verify('password', $u['password_hash']) ? 'password' : (password_verify('password123', $u['password_hash']) ? 'password123' : (password_verify('admin123', $u['password_hash']) ? 'admin123' : 'other'));
    echo "ID: {$u['id']} | Email: {$u['email']} | Role: {$u['role']} | Name: {$u['name']} | Pwd: {$matchesPassword123}\n";
}

echo "\n=== STUDENTS ===\n";
$students = $db->query('SELECT id, learner_user_id, assigned_teacher_id, student_name, lrn, status, learning_track FROM student_records ORDER BY id ASC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($students as $s) {
    echo "ID: {$s['id']} | LearnerUserID: {$s['learner_user_id']} | TeacherID: {$s['assigned_teacher_id']} | Name: {$s['student_name']} | LRN: {$s['lrn']} | Track: {$s['learning_track']}\n";
}

echo "\n=== FSL SIGNS FOR MATH / NUMBERS ===\n";
$signs = $db->query("SELECT id, word, category, video_path FROM fsl_vocabulary WHERE word IN ('isa', 'dalawa', 'tatlo', 'apat', 'lima', 'anim', 'pito', 'walo', 'siyam', 'sampu', 'dagdag', 'bilang', 'lahat', 'kasama', 'aral', 'mag-aral') OR category IN ('Numbers', 'Math') ORDER BY word ASC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
foreach ($signs as $s) {
    echo "ID: {$s['id']} | Word: {$s['word']} | Cat: {$s['category']} | Video: {$s['video_path']}\n";
}

echo "\n=== LESSON PLANS ===\n";
$lps = $db->query('SELECT id, title, pdsp_domain, status, created_by, iep_id, student_id, mainstream_mastery_title, mainstream_mastery_url FROM lesson_plans ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($lps as $lp) {
    echo "ID: {$lp['id']} | Title: {$lp['title']} | Status: {$lp['status']} | Creator: {$lp['created_by']} | Mastery: {$lp['mainstream_mastery_title']}\n";
}

echo "\n=== RESOURCE HUB PACKS ===\n";
$packs = $db->query('SELECT id, title, pdsp_domain, status, created_by, source_lesson_plan_id, clone_count FROM resource_hub_packs ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
foreach ($packs as $p) {
    echo "ID: {$p['id']} | Title: {$p['title']} | Status: {$p['status']} | Creator: {$p['created_by']} | SourceLP: {$p['source_lesson_plan_id']} | Clones: {$p['clone_count']}\n";
}

echo "\n=== EXTERNAL MASTERY RESULTS ===\n";
$mr = $db->query('SELECT id, lesson_plan_id, student_id, recorded_by, resource_title, mastery_status, score FROM external_mastery_results ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
foreach ($mr as $m) {
    echo "ID: {$m['id']} | LP: {$m['lesson_plan_id']} | Student: {$m['student_id']} | RecordedBy: {$m['recorded_by']} | Status: {$m['mastery_status']} | Score: {$m['score']}\n";
}
