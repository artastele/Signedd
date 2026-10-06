<?php
// Setup Script: Controlled Competition Demo Scenario
// Mathematics: Basic Addition within 10

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/LessonPlanModel.php';
require_once __DIR__ . '/../app/Models/ResourceHubModel.php';

$db = Database::getInstance()->getConnection();

try {
    echo "=== STEP 1: CONFIGURE DEMO USERS ===\n";

    $hash = password_hash('password', PASSWORD_DEFAULT);

    // 1. Teacher A (User ID 6)
    $teacherAId = 6;
    $db->prepare("
        UPDATE users 
        SET name = 'Teacher Maria Santos', 
            email = 'teacher_a@signed.ph', 
            role = 'sped_teacher', 
            status = 'active', 
            password_hash = :pwd,
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['pwd' => $hash, 'id' => $teacherAId]);
    echo "Teacher A configured: ID {$teacherAId} | teacher_a@signed.ph (Teacher Maria Santos)\n";

    // 2. Teacher B (User ID 19)
    $teacherBId = 19;
    $db->prepare("
        UPDATE users 
        SET name = 'Teacher Ben Reyes', 
            email = 'teacher_b@signed.ph', 
            role = 'sped_teacher', 
            status = 'active', 
            password_hash = :pwd,
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['pwd' => $hash, 'id' => $teacherBId]);
    echo "Teacher B configured: ID {$teacherBId} | teacher_b@signed.ph (Teacher Ben Reyes)\n";

    // 3. Learner A (User ID 12)
    $learnerAUserId = 12;
    $db->prepare("
        UPDATE users 
        SET name = 'Leo Ramos', 
            email = 'learner_a@signed.ph', 
            role = 'learner', 
            status = 'active', 
            password_hash = :pwd,
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['pwd' => $hash, 'id' => $learnerAUserId]);
    echo "Learner A configured: ID {$learnerAUserId} | learner_a@signed.ph (Leo Ramos)\n";

    // 4. Learner B (User ID 14)
    $learnerBUserId = 14;
    $db->prepare("
        UPDATE users 
        SET name = 'Maya Dizon', 
            email = 'learner_b@signed.ph', 
            role = 'learner', 
            status = 'active', 
            password_hash = :pwd,
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['pwd' => $hash, 'id' => $learnerBUserId]);
    echo "Learner B configured: ID {$learnerBUserId} | learner_b@signed.ph (Maya Dizon)\n";

    echo "\n=== STEP 2: CONFIGURE STUDENT & IEP RECORDS ===\n";

    // Student A (Student ID 5) -> Assigned to Teacher A
    $studentAId = 5;
    $db->prepare("
        UPDATE student_records 
        SET student_name = 'Leo Ramos', 
            assigned_teacher_id = :tid, 
            learner_user_id = :lid, 
            learning_track = 'lms', 
            status = 'active',
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['tid' => $teacherAId, 'lid' => $learnerAUserId, 'id' => $studentAId]);
    echo "Student A updated: ID {$studentAId} (Leo Ramos) -> Teacher A\n";

    // IEP A (IEP ID 3) -> Leo Ramos, Teacher A
    $iepAId = 3;
    $db->prepare("
        UPDATE iep_records 
        SET student_id = :sid, 
            drafted_by = :tid, 
            status = 'signed', 
            school_year = '2026-2027', 
            header_learner_name = 'Leo Ramos', 
            header_teacher_name = 'Teacher Maria Santos',
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['sid' => $studentAId, 'tid' => $teacherAId, 'id' => $iepAId]);
    echo "IEP A updated: ID {$iepAId} (Signed)\n";

    // Student B (Student ID 6) -> Assigned to Teacher B
    $studentBId = 6;
    $db->prepare("
        UPDATE student_records 
        SET student_name = 'Maya Dizon', 
            assigned_teacher_id = :tid, 
            learner_user_id = :lid, 
            learning_track = 'lms', 
            status = 'active',
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['tid' => $teacherBId, 'lid' => $learnerBUserId, 'id' => $studentBId]);
    echo "Student B updated: ID {$studentBId} (Maya Dizon) -> Teacher B\n";

    // IEP B (IEP ID 4) -> Maya Dizon, Teacher B
    $iepBId = 4;
    $db->prepare("
        UPDATE iep_records 
        SET student_id = :sid, 
            drafted_by = :tid, 
            status = 'signed', 
            school_year = '2026-2027', 
            header_learner_name = 'Maya Dizon', 
            header_teacher_name = 'Teacher Ben Reyes',
            updated_at = NOW()
        WHERE id = :id
    ")->execute(['sid' => $studentBId, 'tid' => $teacherBId, 'id' => $iepBId]);
    echo "IEP B updated: ID {$iepBId} (Signed)\n";

    echo "\n=== STEP 3: TEACHER A'S ACCESSIBLE LESSON PLAN ===\n";

    $masteryUrl = "https://www.khanacademy.org/math/early-math/cc-early-math-add-sub-basics/addition-within-10";
    $masteryTitle = "Khan Academy — Addition within 10";

    // Look for existing lesson plan
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
        echo "Created Lesson Plan: ID {$lpId}\n";
    } else {
        $db->prepare("
            UPDATE lesson_plans 
            SET iep_id = :iep_id, 
                student_id = :student_id, 
                status = 'published', 
                mainstream_mastery_url = :url, 
                mainstream_mastery_title = :title, 
                updated_at = NOW() 
            WHERE id = :id
        ")->execute([
            'iep_id' => $iepAId,
            'student_id' => $studentAId,
            'url' => $masteryUrl,
            'title' => $masteryTitle,
            'id' => $lpId
        ]);
        echo "Updated Lesson Plan: ID {$lpId}\n";
    }

    // Assign to Learner A (Leo Ramos)
    $db->prepare("INSERT IGNORE INTO lesson_assignments (lesson_plan_id, student_id, assigned_by, assigned_at) VALUES (:lpid, :sid, :tid, NOW())")
       ->execute(['lpid' => $lpId, 'sid' => $studentAId, 'tid' => $teacherAId]);
    echo "Assigned Lesson Plan {$lpId} to Student {$studentAId}\n";

    // Clean older pages & set 4 crisp slides
    $db->prepare("DELETE FROM lesson_plan_pages WHERE lesson_plan_id = :lpid")->execute(['lpid' => $lpId]);

    $slides = [
        [
            'page_number' => 1,
            'title' => 'Panimula sa Pagdaragdag (Addition)',
            'content' => '<p>Maligayang pagdating sa aralin sa Matematika! Ang pagdaragdag ay ang pagsasama-sama ng mga bagay.</p><p>Ngayong araw, bibilangin natin ang <span class="fsl-word" data-word="isa" data-video="videos/fsl/20.mov" data-category="Number" data-desc="Bilang 1">isa</span> at <span class="fsl-word" data-word="dalawa" data-video="videos/fsl/21.mov" data-category="Number" data-desc="Bilang 2">dalawa</span> upang mahanap ang tamang kabuuan.</p>',
            'guide_questions' => 'Ano ang ibig sabihin ng pagdaragdag o pagsasama ng mga bilang?'
        ],
        [
            'page_number' => 2,
            'title' => 'Pagsasama ng mga Bagay (Visual Counting)',
            'content' => '<p>Tingnan ang halimbawa ng mga prutas sa mesa:</p><p>Mayroon tayong <span class="fsl-word" data-word="tatlo" data-video="videos/fsl/22.mov" data-category="Number" data-desc="Bilang 3">tatlo</span> na mansanas. Dinagdagan natin ito ng <span class="fsl-word" data-word="dalawa" data-video="videos/fsl/21.mov" data-category="Number" data-desc="Bilang 2">dalawa</span> pa.</p><p>Sabay-sabay nating bilangin ang lahat upang makuha ang kabuuan!</p>',
            'guide_questions' => 'Ilan ang kabuuang bilang kapag pinagsama ang tatlo at dalawa?'
        ],
        [
            'page_number' => 3,
            'title' => 'Pagsasanay sa Senyas at Numero (FSL + Visuals)',
            'content' => '<p>Sabayan natin ang senyas ng kamay para sa mga numero:</p><p>Ang <span class="fsl-word" data-word="apat" data-video="videos/fsl/23.mov" data-category="Number" data-desc="Bilang 4">apat</span> na mansanas kapag dinagdagan ng <span class="fsl-word" data-word="isa" data-video="videos/fsl/20.mov" data-category="Number" data-desc="Bilang 1">isa</span> ay magiging <span class="fsl-word" data-word="lima" data-video="videos/fsl/24.mov" data-category="Number" data-desc="Bilang 5">lima</span>!</p>',
            'guide_questions' => 'Kaya mo bang ipakita ang senyas ng isa, dalawa, tatlo, apat, at lima gamit ang iyong kamay?'
        ],
        [
            'page_number' => 4,
            'title' => 'Handa na para sa Mainstream Challenge!',
            'content' => '<p>Magaling! Natapos mo na ang SignED visual at FSL preparation.</p><p>Sagutin ang maikling pagsasanay sa ibaba bilang warm-up bago tayo magtungo sa official <strong>Khan Academy Addition within 10</strong> activity!</p>',
            'guide_questions' => 'Handa ka na bang ipakita ang iyong galing sa pagdaragdag?'
        ],
    ];

    $insSlide = $db->prepare("
        INSERT INTO lesson_plan_pages (lesson_plan_id, page_number, title, content, guide_questions, media_type, created_at, updated_at)
        VALUES (:lpid, :num, :title, :content, :guide, 'none', NOW(), NOW())
    ");
    foreach ($slides as $s) {
        $insSlide->execute([
            'lpid' => $lpId,
            'num' => $s['page_number'],
            'title' => $s['title'],
            'content' => $s['content'],
            'guide' => $s['guide_questions']
        ]);
    }
    echo "Inserted 4 slides for lesson {$lpId}\n";

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

    $stmt = $db->prepare("
        INSERT INTO lms_activities (lesson_plan_id, title, instructions, activity_type, activity_data, max_score, display_order, created_at)
        VALUES (:lpid, 'SignED Warm-up: Pagsasama ng Bilang', 'Piliin ang tamang sagot para sa kabuuang bilang.', 'multiple_choice', :data, 10, 1, NOW())
    ");
    $stmt->execute([
        'lpid' => $lpId,
        'data' => $activityData
    ]);
    echo "Created warm-up activity for lesson {$lpId}\n";

    echo "\n=== STEP 4: RESOURCE HUB REUSABLE TEMPLATE PACK ===\n";

    // Clean any prior pack for this source lesson
    $stmt = $db->prepare("SELECT id FROM resource_hub_packs WHERE source_lesson_plan_id = :lpid");
    $stmt->execute(['lpid' => $lpId]);
    $oldPackId = $stmt->fetchColumn();
    if ($oldPackId) {
        $db->prepare("DELETE FROM resource_hub_activities WHERE pack_id = :pid")->execute(['pid' => $oldPackId]);
        $db->prepare("DELETE FROM resource_hub_pages WHERE pack_id = :pid")->execute(['pid' => $oldPackId]);
        $db->prepare("DELETE FROM resource_hub_packs WHERE id = :pid")->execute(['pid' => $oldPackId]);
    }

    $hubModel = new ResourceHubModel();
    $packId = $hubModel->shareLessonToHub($lpId, $teacherAId, 'Teacher Maria Santos', [
        'title' => 'Mathematics: Basic Addition within 10 (Accessibility Pack)',
        'domain' => 'perceptuo_cognitive',
        'target_grade_level' => 'Primary Deaf Ed / SPED Math',
        'learning_competencies' => 'Demonstrates understanding of addition of whole numbers up to 10 using concrete objects, visuals, and FSL number signs.',
        'description' => 'Deaf-accessible lesson pack covering basic addition within 10 with Filipino Sign Language (FSL) visual signs, concrete counting examples, SignED warm-up, and Khan Academy mainstream mastery handoff.',
        'mainstream_mastery_url' => $masteryUrl,
        'mainstream_mastery_title' => $masteryTitle,
        'tags' => 'math, addition, numbers, fsl, deaf-ed'
    ]);
    echo "Resource Hub Pack created via Model: ID {$packId}\n";

    echo "\n====================================================\n";
    echo "COMPETITION DEMO SCENARIO SETUP COMPLETE!\n";
    echo "====================================================\n";
    echo "Teacher A: teacher_a@signed.ph | password\n";
    echo "Teacher B: teacher_b@signed.ph | password\n";
    echo "Learner A: learner_a@signed.ph | password\n";
    echo "Admin:     admin@spedlms.local | password\n";
    echo "Lesson ID: {$lpId}\n";
    echo "Pack ID:   {$packId}\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
