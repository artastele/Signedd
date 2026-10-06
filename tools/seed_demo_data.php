<?php
/**
 * SignED Development-Only Demo Seed Script
 * 
 * Safely seeds reproducible, fictional demo records for local development and testing.
 * 
 * SAFETY RULES:
 * - NEVER executes in production (checks APP_ENV !== 'production').
 * - Only uses clearly fictional data (names, LRNs, dummy emails).
 * - Idempotent (can be safely run multiple times without duplicating records).
 * - Does not touch or overwrite real student or teacher records.
 *
 * Usage:
 *   php tools/seed_demo_data.php
 */

if (php_sapi_name() !== 'cli') {
    die("Error: This seed script can only be executed via the command line interface.\n");
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/LessonPlanModel.php';
require_once __DIR__ . '/../app/Models/ResourceHubModel.php';

// Check environment safety
$appEnv = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');
if (strtolower($appEnv) === 'production') {
    die("CRITICAL ABORT: Cannot seed demo data in PRODUCTION environment (APP_ENV=production).\n");
}

$db = Database::getInstance()->getConnection();

echo "====================================================\n";
echo "SignED Development Demo Seeder [ENV: {$appEnv}]\n";
echo "====================================================\n";

try {
    $defaultHash = password_hash('password', PASSWORD_DEFAULT);

    // ── 1. Seed Fictional Demo Teachers ───────────────────────────
    echo "[1/5] Verifying Fictional Demo Teachers...\n";

    // Teacher A: Maria Santos
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'teacher_a@signed.ph'");
    $stmt->execute();
    $teacherAId = $stmt->fetchColumn();
    if (!$teacherAId) {
        $stmt = $db->prepare("
            INSERT INTO users (name, email, role, status, password_hash, email_verified, created_at, updated_at)
            VALUES ('Teacher Maria Santos (Demo)', 'teacher_a@signed.ph', 'sped_teacher', 'active', :pwd, 1, NOW(), NOW())
        ");
        $stmt->execute(['pwd' => $defaultHash]);
        $teacherAId = (int)$db->lastInsertId();
        echo "  + Created Teacher A (ID: {$teacherAId})\n";
    } else {
        $db->prepare("UPDATE users SET name = 'Teacher Maria Santos (Demo)', status = 'active', password_hash = :pwd WHERE id = :id")
           ->execute(['pwd' => $defaultHash, 'id' => $teacherAId]);
        echo "  ✓ Existing Teacher A refreshed (ID: {$teacherAId})\n";
    }

    // Teacher B: Ben Reyes
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'teacher_b@signed.ph'");
    $stmt->execute();
    $teacherBId = $stmt->fetchColumn();
    if (!$teacherBId) {
        $stmt = $db->prepare("
            INSERT INTO users (name, email, role, status, password_hash, email_verified, created_at, updated_at)
            VALUES ('Teacher Ben Reyes (Demo)', 'teacher_b@signed.ph', 'sped_teacher', 'active', :pwd, 1, NOW(), NOW())
        ");
        $stmt->execute(['pwd' => $defaultHash]);
        $teacherBId = (int)$db->lastInsertId();
        echo "  + Created Teacher B (ID: {$teacherBId})\n";
    } else {
        $db->prepare("UPDATE users SET name = 'Teacher Ben Reyes (Demo)', status = 'active', password_hash = :pwd WHERE id = :id")
           ->execute(['pwd' => $defaultHash, 'id' => $teacherBId]);
        echo "  ✓ Existing Teacher B refreshed (ID: {$teacherBId})\n";
    }

    // ── 2. Seed Fictional Demo Learners ───────────────────────────
    echo "[2/5] Verifying Fictional Demo Learners...\n";

    // Learner A: Leo Ramos
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'learner_a@signed.ph'");
    $stmt->execute();
    $learnerAId = $stmt->fetchColumn();
    if (!$learnerAId) {
        $stmt = $db->prepare("
            INSERT INTO users (name, email, role, status, password_hash, email_verified, created_at, updated_at)
            VALUES ('Leo Ramos (Demo)', 'learner_a@signed.ph', 'learner', 'active', :pwd, 1, NOW(), NOW())
        ");
        $stmt->execute(['pwd' => $defaultHash]);
        $learnerAId = (int)$db->lastInsertId();
        echo "  + Created Learner A user (ID: {$learnerAId})\n";
    } else {
        $db->prepare("UPDATE users SET name = 'Leo Ramos (Demo)', status = 'active', password_hash = :pwd WHERE id = :id")
           ->execute(['pwd' => $defaultHash, 'id' => $learnerAId]);
        echo "  ✓ Existing Learner A refreshed (ID: {$learnerAId})\n";
    }

    // Learner B: Maya Dizon
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'learner_b@signed.ph'");
    $stmt->execute();
    $learnerBId = $stmt->fetchColumn();
    if (!$learnerBId) {
        $stmt = $db->prepare("
            INSERT INTO users (name, email, role, status, password_hash, email_verified, created_at, updated_at)
            VALUES ('Maya Dizon (Demo)', 'learner_b@signed.ph', 'learner', 'active', :pwd, 1, NOW(), NOW())
        ");
        $stmt->execute(['pwd' => $defaultHash]);
        $learnerBId = (int)$db->lastInsertId();
        echo "  + Created Learner B user (ID: {$learnerBId})\n";
    } else {
        $db->prepare("UPDATE users SET name = 'Maya Dizon (Demo)', status = 'active', password_hash = :pwd WHERE id = :id")
           ->execute(['pwd' => $defaultHash, 'id' => $learnerBId]);
        echo "  ✓ Existing Learner B refreshed (ID: {$learnerBId})\n";
    }

    // ── 3. Seed Student Records & Signed IEPs ──────────────────────
    echo "[3/5] Verifying Student Records and IEPs...\n";

    // Student A (Leo Ramos, assigned to Teacher A)
    $stmt = $db->prepare("SELECT id FROM student_records WHERE learner_user_id = :uid");
    $stmt->execute(['uid' => $learnerAId]);
    $studentAId = $stmt->fetchColumn();
    if (!$studentAId) {
        $stmt = $db->prepare("
            INSERT INTO student_records (learner_user_id, assigned_teacher_id, student_name, lrn, learning_track, status, created_at, updated_at)
            VALUES (:uid, :tid, 'Leo Ramos (Demo)', '999900000001', 'lms', 'active', NOW(), NOW())
        ");
        $stmt->execute(['uid' => $learnerAId, 'tid' => $teacherAId]);
        $studentAId = (int)$db->lastInsertId();
        echo "  + Created Student A record (ID: {$studentAId})\n";
    } else {
        $db->prepare("UPDATE student_records SET student_name = 'Leo Ramos (Demo)', assigned_teacher_id = :tid, learning_track = 'lms', status = 'active' WHERE id = :id")
           ->execute(['tid' => $teacherAId, 'id' => $studentAId]);
        echo "  ✓ Student A record refreshed (ID: {$studentAId})\n";
    }

    // IEP A
    $stmt = $db->prepare("SELECT id FROM iep_records WHERE student_id = :sid ORDER BY id ASC LIMIT 1");
    $stmt->execute(['sid' => $studentAId]);
    $iepAId = $stmt->fetchColumn();
    if (!$iepAId) {
        $stmt = $db->prepare("
            INSERT INTO iep_records (student_id, drafted_by, status, school_year, header_learner_name, header_teacher_name, created_at, updated_at)
            VALUES (:sid, :tid, 'signed', '2026-2027', 'Leo Ramos (Demo)', 'Teacher Maria Santos (Demo)', NOW(), NOW())
        ");
        $stmt->execute(['sid' => $studentAId, 'tid' => $teacherAId]);
        $iepAId = (int)$db->lastInsertId();
        echo "  + Created IEP A record (ID: {$iepAId})\n";
    } else {
        $db->prepare("UPDATE iep_records SET drafted_by = :tid, status = 'signed', header_learner_name = 'Leo Ramos (Demo)', header_teacher_name = 'Teacher Maria Santos (Demo)' WHERE id = :id")
           ->execute(['tid' => $teacherAId, 'id' => $iepAId]);
        echo "  ✓ IEP A refreshed (ID: {$iepAId})\n";
    }

    // Student B (Maya Dizon, assigned to Teacher B)
    $stmt = $db->prepare("SELECT id FROM student_records WHERE learner_user_id = :uid");
    $stmt->execute(['uid' => $learnerBId]);
    $studentBId = $stmt->fetchColumn();
    if (!$studentBId) {
        $stmt = $db->prepare("
            INSERT INTO student_records (learner_user_id, assigned_teacher_id, student_name, lrn, learning_track, status, created_at, updated_at)
            VALUES (:uid, :tid, 'Maya Dizon (Demo)', '999900000002', 'lms', 'active', NOW(), NOW())
        ");
        $stmt->execute(['uid' => $learnerBId, 'tid' => $teacherBId]);
        $studentBId = (int)$db->lastInsertId();
        echo "  + Created Student B record (ID: {$studentBId})\n";
    } else {
        $db->prepare("UPDATE student_records SET student_name = 'Maya Dizon (Demo)', assigned_teacher_id = :tid, learning_track = 'lms', status = 'active' WHERE id = :id")
           ->execute(['tid' => $teacherBId, 'id' => $studentBId]);
        echo "  ✓ Student B record refreshed (ID: {$studentBId})\n";
    }

    // IEP B
    $stmt = $db->prepare("SELECT id FROM iep_records WHERE student_id = :sid ORDER BY id ASC LIMIT 1");
    $stmt->execute(['sid' => $studentBId]);
    $iepBId = $stmt->fetchColumn();
    if (!$iepBId) {
        $stmt = $db->prepare("
            INSERT INTO iep_records (student_id, drafted_by, status, school_year, header_learner_name, header_teacher_name, created_at, updated_at)
            VALUES (:sid, :tid, 'signed', '2026-2027', 'Maya Dizon (Demo)', 'Teacher Ben Reyes (Demo)', NOW(), NOW())
        ");
        $stmt->execute(['sid' => $studentBId, 'tid' => $teacherBId]);
        $iepBId = (int)$db->lastInsertId();
        echo "  + Created IEP B record (ID: {$iepBId})\n";
    } else {
        $db->prepare("UPDATE iep_records SET drafted_by = :tid, status = 'signed', header_learner_name = 'Maya Dizon (Demo)', header_teacher_name = 'Teacher Ben Reyes (Demo)' WHERE id = :id")
           ->execute(['tid' => $teacherBId, 'id' => $iepBId]);
        echo "  ✓ IEP B refreshed (ID: {$iepBId})\n";
    }

    // ── 4. Seed Foundational Mathematics Lesson ───────────────────
    echo "[4/5] Verifying Generic Foundational Lesson (Mathematics: Basic Addition within 10)...\n";

    $masteryUrl = "https://www.khanacademy.org/math/early-math/cc-early-math-add-sub-basics/addition-within-10";
    $masteryTitle = "Khan Academy — Addition within 10";

    $stmt = $db->prepare("SELECT id FROM lesson_plans WHERE title = 'Mathematics: Basic Addition within 10' AND created_by = :tid");
    $stmt->execute(['tid' => $teacherAId]);
    $lpId = $stmt->fetchColumn();

    if (!$lpId) {
        $stmt = $db->prepare("
            INSERT INTO lesson_plans (iep_id, student_id, created_by, title, pdsp_domain, assignment_type, status, mainstream_mastery_url, mainstream_mastery_title, created_at, updated_at)
            VALUES (:iep_id, :student_id, :created_by, 'Mathematics: Basic Addition within 10', 'perceptuo_cognitive', 'individual', 'published', :url, :title, NOW(), NOW())
        ");
        $stmt->execute([
            'iep_id' => $iepAId,
            'student_id' => $studentAId,
            'created_by' => $teacherAId,
            'url' => $masteryUrl,
            'title' => $masteryTitle
        ]);
        $lpId = (int)$db->lastInsertId();
        echo "  + Created Lesson Plan (ID: {$lpId})\n";
    } else {
        $db->prepare("
            UPDATE lesson_plans 
            SET iep_id = :iep_id, student_id = :student_id, status = 'published', mainstream_mastery_url = :url, mainstream_mastery_title = :title, updated_at = NOW() 
            WHERE id = :id
        ")->execute(['iep_id' => $iepAId, 'student_id' => $studentAId, 'url' => $masteryUrl, 'title' => $masteryTitle, 'id' => $lpId]);
        echo "  ✓ Lesson Plan refreshed (ID: {$lpId})\n";
    }

    // Ensure assigned to Learner A
    $db->prepare("INSERT IGNORE INTO lesson_assignments (lesson_plan_id, student_id, assigned_by, assigned_at) VALUES (:lpid, :sid, :tid, NOW())")
       ->execute(['lpid' => $lpId, 'sid' => $studentAId, 'tid' => $teacherAId]);

    // Seed 4 crisp slides
    $db->prepare("DELETE FROM lesson_plan_pages WHERE lesson_plan_id = :lpid")->execute(['lpid' => $lpId]);
    $slides = [
        [
            'num' => 1,
            'title' => 'Panimula sa Pagdaragdag (Addition)',
            'content' => '<p>Maligayang pagdating sa aralin sa Matematika! Ang pagdaragdag ay ang pagsasama-sama ng mga bagay.</p><p>Ngayong araw, bibilangin natin ang <span class="fsl-word" data-word="isa" data-video="videos/fsl/20.mov" data-category="Number" data-desc="Bilang 1">isa</span> at <span class="fsl-word" data-word="dalawa" data-video="videos/fsl/21.mov" data-category="Number" data-desc="Bilang 2">dalawa</span> upang mahanap ang tamang kabuuan.</p>',
            'guide' => 'Ano ang ibig sabihin ng pagdaragdag o pagsasama ng mga bilang?'
        ],
        [
            'num' => 2,
            'title' => 'Pagsasama ng mga Bagay (Visual Counting)',
            'content' => '<p>Tingnan ang halimbawa ng mga prutas sa mesa:</p><p>Mayroon tayong <span class="fsl-word" data-word="tatlo" data-video="videos/fsl/22.mov" data-category="Number" data-desc="Bilang 3">tatlo</span> na mansanas. Dinagdagan natin ito ng <span class="fsl-word" data-word="dalawa" data-video="videos/fsl/21.mov" data-category="Number" data-desc="Bilang 2">dalawa</span> pa.</p><p>Sabay-sabay nating bilangin ang lahat upang makuha ang kabuuan!</p>',
            'guide' => 'Ilan ang kabuuang bilang kapag pinagsama ang tatlo at dalawa?'
        ],
        [
            'num' => 3,
            'title' => 'Pagsasanay sa Senyas at Numero (FSL + Visuals)',
            'content' => '<p>Sabayan natin ang senyas ng kamay para sa mga numero:</p><p>Ang <span class="fsl-word" data-word="apat" data-video="videos/fsl/23.mov" data-category="Number" data-desc="Bilang 4">apat</span> na mansanas kapag dinagdagan ng <span class="fsl-word" data-word="isa" data-video="videos/fsl/20.mov" data-category="Number" data-desc="Bilang 1">isa</span> ay magiging <span class="fsl-word" data-word="lima" data-video="videos/fsl/24.mov" data-category="Number" data-desc="Bilang 5">lima</span>!</p>',
            'guide' => 'Kaya mo bang ipakita ang senyas ng isa, dalawa, tatlo, apat, at lima gamit ang iyong kamay?'
        ],
        [
            'num' => 4,
            'title' => 'Handa na para sa Mainstream Challenge!',
            'content' => '<p>Magaling! Natapos mo na ang SignED visual at FSL preparation.</p><p>Sagutin ang maikling pagsasanay sa ibaba bilang warm-up bago tayo magtungo sa official <strong>Khan Academy Addition within 10</strong> activity!</p>',
            'guide' => 'Handa ka na bang ipakita ang iyong galing sa pagdaragdag?'
        ],
    ];

    $insSlide = $db->prepare("
        INSERT INTO lesson_plan_pages (lesson_plan_id, page_number, title, content, guide_questions, media_type, created_at, updated_at)
        VALUES (:lpid, :num, :title, :content, :guide, 'none', NOW(), NOW())
    ");
    foreach ($slides as $s) {
        $insSlide->execute([
            'lpid' => $lpId,
            'num' => $s['num'],
            'title' => $s['title'],
            'content' => $s['content'],
            'guide' => $s['guide']
        ]);
    }
    echo "  ✓ 4 slides verified for lesson {$lpId}\n";

    // Activity Warm-up
    $db->prepare("DELETE FROM lms_activities WHERE lesson_plan_id = :lpid")->execute(['lpid' => $lpId]);
    $activityData = json_encode([
        'questions' => [
            [
                'question' => 'Ilan ang 3 + 2?',
                'options' => ['4', '5', '6'],
                'correct' => 1,
                'points' => 10
            ]
        ]
    ]);
    $db->prepare("
        INSERT INTO lms_activities (lesson_plan_id, title, instructions, activity_type, activity_data, max_score, display_order, created_at)
        VALUES (:lpid, 'SignED Warm-up: Pagsasama ng Bilang', 'Piliin ang tamang sagot para sa kabuuang bilang.', 'multiple_choice', :data, 10, 1, NOW())
    ")->execute(['lpid' => $lpId, 'data' => $activityData]);
    echo "  ✓ Warm-up activity verified\n";

    // ── 5. Seed Generic Resource Hub Accessibility Pack ───────────
    echo "[5/5] Verifying Resource Hub Accessibility Pack...\n";

    $stmt = $db->prepare("SELECT id FROM resource_hub_packs WHERE source_lesson_plan_id = :lpid LIMIT 1");
    $stmt->execute(['lpid' => $lpId]);
    $existingPackId = $stmt->fetchColumn();

    if ($existingPackId) {
        $db->prepare("DELETE FROM resource_hub_activities WHERE pack_id = :pid")->execute(['pid' => $existingPackId]);
        $db->prepare("DELETE FROM resource_hub_pages WHERE pack_id = :pid")->execute(['pid' => $existingPackId]);
        $db->prepare("DELETE FROM resource_hub_packs WHERE id = :pid")->execute(['pid' => $existingPackId]);
    }

    $hubModel = new ResourceHubModel();
    $packId = $hubModel->shareLessonToHub($lpId, $teacherAId, 'Teacher Maria Santos (Demo)', [
        'title' => 'Mathematics: Basic Addition within 10 (Accessibility Pack)',
        'domain' => 'perceptuo_cognitive',
        'target_grade_level' => 'Primary Deaf Ed / SPED Math',
        'learning_competencies' => 'Demonstrates understanding of addition of whole numbers up to 10 using concrete objects, visuals, and FSL number signs.',
        'description' => 'Deaf-accessible lesson pack covering basic addition within 10 with Filipino Sign Language (FSL) visual signs, concrete counting examples, SignED warm-up, and Khan Academy mainstream mastery handoff.',
        'mainstream_mastery_url' => $masteryUrl,
        'mainstream_mastery_title' => $masteryTitle,
        'tags' => 'math, addition, numbers, fsl, deaf-ed'
    ]);
    echo "  ✓ Accessibility Pack shared (Pack ID: {$packId})\n";

    echo "\n====================================================\n";
    echo "DEMO SEEDING COMPLETED SUCCESSFULLY!\n";
    echo "====================================================\n";
    echo "Teacher A: teacher_a@signed.ph | password (Maria Santos)\n";
    echo "Teacher B: teacher_b@signed.ph | password (Ben Reyes)\n";
    echo "Learner A: learner_a@signed.ph | password (Leo Ramos)\n";
    echo "Learner B: learner_b@signed.ph | password (Maya Dizon)\n";
    echo "Lesson ID: {$lpId}\n";
    echo "Pack ID:   {$packId}\n";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "SEEDING ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
