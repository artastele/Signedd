<?php
/**
 * SignED Competition Demo Reset Script
 * Idempotent, safe reset procedure to return the competition demo to initial state after rehearsals.
 *
 * Usage:
 *   php tools/reset_competition_demo.php
 *
 * DO NOT expose via a public web route.
 */

if (php_sapi_name() !== 'cli') {
    die("Error: This script can only be executed from the CLI.\n");
}

$appEnv = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');
if (strtolower($appEnv) === 'production') {
    die("CRITICAL ABORT: Cannot run reset script in PRODUCTION environment (APP_ENV=production).\n");
}

require_once __DIR__ . '/../config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "====================================================\n";
    echo "SignED Competition Demo - Resetting State...\n";
    echo "====================================================\n";

    // 1. Identify Demo Pack and Lesson
    $packId = 6;
    $sourceLessonId = 26;

    // Check pack and lesson existence
    $stmt = $db->prepare("SELECT id, title FROM lesson_plans WHERE id = :id");
    $stmt->execute(['id' => $sourceLessonId]);
    $sourceLesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sourceLesson) {
        // Fallback: look up by title if ID differed
        $stmt = $db->query("SELECT id, title FROM lesson_plans WHERE title = 'Mathematics: Basic Addition within 10' LIMIT 1");
        $sourceLesson = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sourceLesson) {
            $sourceLessonId = (int)$sourceLesson['id'];
        }
    }

    echo "Source Lesson Plan: ID {$sourceLessonId} (" . ($sourceLesson['title'] ?? 'Not found') . ")\n";

    // 2. Remove Cloned Lessons created during rehearsal / testing
    $stmt = $db->prepare("SELECT id, title, created_by FROM lesson_plans WHERE cloned_from_pack_id = :pack_id OR (created_by = 19 AND title LIKE '%Mathematics: Basic Addition within 10%')");
    $stmt->execute(['pack_id' => $packId]);
    $clones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $clonedIds = [];
    foreach ($clones as $clone) {
        if ((int)$clone['id'] === $sourceLessonId) continue;
        $clonedIds[] = (int)$clone['id'];
    }

    if (!empty($clonedIds)) {
        echo "Found " . count($clonedIds) . " cloned lesson(s) to remove: " . implode(', ', $clonedIds) . "\n";
        foreach ($clonedIds as $cId) {
            // Delete associated submissions
            $db->exec("DELETE FROM lms_submissions WHERE activity_id IN (SELECT id FROM lms_activities WHERE lesson_plan_id = {$cId})");
            // Delete activities
            $db->exec("DELETE FROM lms_activities WHERE lesson_plan_id = {$cId}");
            // Delete pages
            $db->exec("DELETE FROM lesson_plan_pages WHERE lesson_plan_id = {$cId}");
            // Delete assignments
            $db->exec("DELETE FROM lesson_assignments WHERE lesson_plan_id = {$cId}");
            // Delete mastery results
            $db->exec("DELETE FROM external_mastery_results WHERE lesson_plan_id = {$cId}");
            // Delete progress
            $db->exec("DELETE FROM lesson_page_progress WHERE lesson_plan_id = {$cId}");
            // Delete lesson plan
            $db->exec("DELETE FROM lesson_plans WHERE id = {$cId}");
            echo "  - Removed cloned lesson ID {$cId}\n";
        }
    } else {
        echo "No rehearsal clones found.\n";
    }

    // 3. Reset Clone Count on Resource Hub Pack(s)
    $db->prepare("UPDATE resource_hub_packs SET clone_count = 0, status = 'published' WHERE id = :id OR source_lesson_plan_id = :lpid")->execute(['id' => $packId, 'lpid' => $sourceLessonId]);
    echo "Reset Resource Hub Pack clone count to 0.\n";

    // 4. Reset Mastery Results on Source Lesson
    $db->prepare("DELETE FROM external_mastery_results WHERE lesson_plan_id = :id")->execute(['id' => $sourceLessonId]);
    echo "Cleared recorded mastery results for Lesson {$sourceLessonId}.\n";

    // 5. Reset Learner Slide Progress and Submissions on Source Lesson
    $db->prepare("DELETE FROM lesson_page_progress WHERE lesson_plan_id = :id")->execute(['id' => $sourceLessonId]);
    $db->prepare("DELETE FROM lms_submissions WHERE activity_id IN (SELECT id FROM lms_activities WHERE lesson_plan_id = :id)")->execute(['id' => $sourceLessonId]);
    echo "Reset slide progress and warm-up submissions for Lesson {$sourceLessonId}.\n";

    // 6. Ensure Source Lesson is Published with correct Khan Academy details
    $db->prepare("
        UPDATE lesson_plans 
        SET status = 'published',
            mainstream_mastery_url = 'https://www.khanacademy.org/math/early-math/cc-early-math-add-sub-basics/addition-within-10',
            mainstream_mastery_title = 'Khan Academy — Addition within 10'
        WHERE id = :id
    ")->execute(['id' => $sourceLessonId]);
    echo "Ensured Lesson {$sourceLessonId} status is 'published' with Khan Academy handoff.\n";

    // 7. Verify Demo Users Status
    $users = [
        ['email' => 'teacher_a@signed.ph', 'name' => 'Teacher Maria Santos', 'role' => 'sped_teacher'],
        ['email' => 'teacher_b@signed.ph', 'name' => 'Teacher Ben Reyes', 'role' => 'sped_teacher'],
        ['email' => 'learner_a@signed.ph', 'name' => 'Leo Ramos', 'role' => 'learner'],
        ['email' => 'learner_b@signed.ph', 'name' => 'Maya Dizon', 'role' => 'learner']
    ];

    echo "\nDemo Account Verification:\n";
    $stmtUser = $db->prepare("SELECT id, name, email, role, status FROM users WHERE email = :email");
    foreach ($users as $u) {
        $stmtUser->execute(['email' => $u['email']]);
        $row = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "  [OK] {$row['role']}: {$row['email']} ({$row['name']}) [status: {$row['status']}]\n";
        } else {
            echo "  [WARN] User not found: {$u['email']}\n";
        }
    }

    echo "\n====================================================\n";
    echo "DEMO RESET COMPLETED SUCCESSFULLY!\n";
    echo "System is in pristine starting state for competition presentation.\n";
    echo "====================================================\n";

} catch (Exception $e) {
    echo "RESET ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
