<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SPED LMS — Learning Controller (Learner Side)

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Models/LessonPlanModel.php';
require_once __DIR__ . '/../Models/GamificationModel.php';
require_once __DIR__ . '/../Middleware/ParentGateMiddleware.php';
require_once __DIR__ . '/../Helpers/ScoreHelper.php';
require_once __DIR__ . '/../Helpers/FlashcardResult.php';

class LearningController {

    private LessonPlanModel   $model;
    private GamificationModel $gamification;
    private int    $userId;
    private string $userRole;
    private string $basePath;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
        $this->userId   = (int) $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath      = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model         = new LessonPlanModel();
        $this->gamification  = new GamificationModel();
    }

    // ----------------------------------------------------------------
    // Resolve student_records.id for the current session user.
    // For role=learner: email is learner_{StudentID}@spedlms.local
    // For role=parent:  look up via enrollment_submissions.parent_id
    // ----------------------------------------------------------------
    private function getStudentId(): ?int {
        try {
            $db = Database::getInstance()->getConnection();

            if ($this->userRole === 'learner') {
                // Direct lookup via learner_user_id (Stage 2 & 3)
                $stmtLearner = $db->prepare("SELECT id FROM student_records WHERE learner_user_id = :uid LIMIT 1");
                $stmtLearner->execute(['uid' => $this->userId]);
                $sId = $stmtLearner->fetchColumn();
                if ($sId) return (int) $sId;

                $stmt = $db->prepare("SELECT email FROM users WHERE id = :uid LIMIT 1");
                $stmt->execute(['uid' => $this->userId]);
                $user = $stmt->fetch();

                if ($user && preg_match('/learner_(\d{8})@/', $user['email'], $m)) {
                    $code = $m[1];
                    $stmt = $db->prepare("SELECT id FROM student_records WHERE student_id = :student_id LIMIT 1");
                    $stmt->execute(['student_id' => $code]);
                    $row = $stmt->fetch();
                    if ($row) return (int) $row['id'];
                }

                if ($user && preg_match('/learner_(\d{12})@/', $user['email'], $m)) {
                    $lrn = $m[1];
                    $stmt = $db->prepare("SELECT id FROM student_records WHERE lrn = :lrn LIMIT 1");
                    $stmt->execute(['lrn' => $lrn]);
                    $row = $stmt->fetch();
                    if ($row) return (int) $row['id'];
                }
            }

            // Allow teachers to view submissions via ?student_id
            if (in_array($this->userRole, ['sped_teacher', 'admin']) && isset($_GET['student_id'])) {
                return (int) $_GET['student_id'];
            }

            // Fallback / parent role
            $stmt = $db->prepare("
                SELECT sr.id
                FROM student_records sr
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                WHERE es.parent_id = :uid
                LIMIT 1
            ");
            $stmt->execute(['uid' => $this->userId]);
            $row = $stmt->fetch();
            if ($row) return (int) $row['id'];

            return null;
        } catch (\Throwable $e) {
            error_log('LearningController::getStudentId() error: ' . $e->getMessage());
            return null;
        }
    }

    private function getStudentName(int $studentId): string {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT student_name FROM student_records WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $studentId]);
            $row = $stmt->fetch();
            return $row ? $row['student_name'] : 'Learner';
        } catch (\Throwable $e) {
            return 'Learner';
        }
    }

    // ----------------------------------------------------------------
    // GET /learning/dashboard
    // ----------------------------------------------------------------
    public function dashboard(): void {
        $studentId   = $this->getStudentId();
        $studentName = $studentId ? $this->getStudentName($studentId) : 'Learner';
        $studentIdCode = null;
        if ($studentId) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT student_id FROM student_records WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $studentId]);
            $row = $stmt->fetch();
            $studentIdCode = $row['student_id'] ?? null;
        }
        $basePath    = $this->basePath;

        // Gamification summary for sidebar/topbar
        $gamSummary  = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP     = $gamSummary['total_xp'];
        $totalStars  = $gamSummary['total_stars'];

        if (!$studentId) {
            $lessonPlans     = [];
            $upcomingDue     = [];
            $overallComplete = 0;
            $overallTotal    = 0;
            require __DIR__ . '/../Views/dashboard/learner.php';
            return;
        }

        $lessonPlans = $this->model->getPublishedForStudent($studentId);
        foreach ($lessonPlans as &$lp) {
            $progress = $this->model->getLessonProgress((int) $lp['id'], $studentId);
            $lp['activity_count']  = $progress['total'];
            $lp['completed_count'] = $progress['completed'];
            $lpMaterials = $this->model->getMaterials((int)$lp['id']);
            $lp['material_count'] = count($lpMaterials);
            $lpPages = $this->model->getPagesByLessonPlan((int)$lp['id']);
            $lp['page_count'] = count($lpPages);
            $pageProg = $this->model->getPageProgress($studentId, (int)$lp['id']);
            $lp['is_slides_completed'] = !empty($pageProg['is_completed']);
            $lp['last_page_number'] = (int)($pageProg['last_page_number'] ?? 0);
            
            // Calculate comprehensive completion %
            $totalTasks = $lp['activity_count'] + ($lp['page_count'] > 0 ? 1 : 0);
            $doneTasks  = $lp['completed_count'] + ($lp['is_slides_completed'] ? 1 : 0);
            $lp['progress_pct'] = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : ($lp['completed_count'] > 0 ? 100 : 0);
        }
        unset($lp);

        $overall         = $this->model->getStudentOverallProgress($studentId);
        $overallComplete = $overall['completed'];
        $overallTotal    = $overall['total'];
        $avgScore        = $overall['avg_score'] ?? 0;
        $upcomingDue     = $this->model->getUpcomingDue($studentId);

        // Badges — full list with earned/locked status (for My Badges tab)
        $badges          = $this->gamification->getBadgesWithStatus($studentId);
        // Total possible stars = 3 per activity that has been graded
        $totalStarsPossible = $overallTotal * 3;

        require __DIR__ . '/../Views/dashboard/learner.php';
    }

    // ----------------------------------------------------------------
    // GET /learning/lesson/{id}
    // ----------------------------------------------------------------
    public function lessonView(string $lessonPlanId): void {
        $lessonPlanId     = (int) $lessonPlanId;
        $studentId        = $this->getStudentId();
        $basePath         = $this->basePath;
        $isTeacherPreview = in_array($this->userRole, ['sped_teacher', 'teacher', 'master_teacher', 'admin', 'principal', 'guidance', 'general_teacher']);
        $studentName      = $studentId ? $this->getStudentName($studentId) : ($isTeacherPreview ? 'Teacher (Preview Mode)' : 'Learner');

        // Gamification summary for sidebar/topbar
        $gamSummary = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP    = $gamSummary['total_xp'];
        $totalStars = $gamSummary['total_stars'];

        $lessonPlan = $this->model->findById($lessonPlanId);
        if (!$lessonPlan || (!$isTeacherPreview && $lessonPlan['status'] !== 'published')) {
            $_SESSION['error'] = 'Lesson plan not found.';
            header('Location: ' . $this->basePath . '/learning/dashboard');
            exit;
        }

        $materials   = $this->model->getMaterials($lessonPlanId);
        $activities  = $this->model->getActivities($lessonPlanId);
        $lessonPages = $this->model->getPagesByLessonPlan($lessonPlanId);
        $pageProgress = $studentId ? $this->model->getPageProgress($studentId, $lessonPlanId) : ['last_page_number' => 1, 'is_completed' => 0];

        // Fetch FSL video vocabulary for automatic real-time interactive highlights
        require_once __DIR__ . '/../Models/FSLModel.php';
        $fslModel = new FSLModel();
        $fslSigns = $fslModel->getAll(null, '', true);

        foreach ($activities as &$act) {
            $act['submission']     = $studentId
                ? $this->model->getActivityWithStatus((int) $act['id'], $studentId)
                : null;
            $act['activity_data']  = json_decode($act['activity_data'] ?? '{}', true) ?? [];
        }
        unset($act);

        // Log 'opened'
        if ($studentId) {
            try {
                $db   = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    INSERT IGNORE INTO lms_logs
                        (student_id, activity_id, material_id, action, performed_by, performed_at)
                    VALUES (:student_id, NULL, NULL, 'opened', :performed_by, NOW())
                ");
                $stmt->execute(['student_id' => $studentId, 'performed_by' => $this->userId]);
            } catch (\Throwable $e) {
                error_log('LearningController::lessonView() log error: ' . $e->getMessage());
            }
        }

        require __DIR__ . '/../Views/learning/lesson_view.php';
    }

    // ----------------------------------------------------------------
    // POST /learning/lesson/{id}/progress
    // ----------------------------------------------------------------
    public function savePageProgress(string $lessonPlanId): void {
        header('Content-Type: application/json');
        try {
            $lessonPlanId = (int)$lessonPlanId;
            $studentId    = $this->getStudentId();
            if (!$studentId) {
                echo json_encode(['success' => true, 'message' => 'Teacher preview mode (progress not persisted to student record).']);
                exit;
            }

            $pageNumber  = (int)($_POST['page_number'] ?? 1);
            $isCompleted = !empty($_POST['is_completed']);

            $this->model->updatePageProgress($studentId, $lessonPlanId, $pageNumber, $isCompleted);

            if ($isCompleted) {
                $this->gamification->awardXP($studentId, 25, 'lesson_completed', $lessonPlanId);
            }

            echo json_encode(['success' => true, 'message' => 'Progress saved.']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ----------------------------------------------------------------
    // GET /learning/activity/{id}
    // ----------------------------------------------------------------
    public function activityPlay(string $activityId): void {
        $activityId  = (int) $activityId;
        $studentId   = $this->getStudentId();
        $basePath    = $this->basePath;
        $studentName = $studentId ? $this->getStudentName($studentId) : 'Learner';

        // Gamification summary for sidebar/topbar
        $gamSummary = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP    = $gamSummary['total_xp'];
        $totalStars = $gamSummary['total_stars'];

        $activity = $this->model->getActivityById($activityId);
        if (!$activity) {
            $_SESSION['error'] = 'Activity not found.';
            header('Location: ' . $this->basePath . '/learning/dashboard');
            exit;
        }

        $activity['activity_data'] = json_decode($activity['activity_data'] ?? '{}', true) ?? [];

        $submission = $studentId
            ? $this->model->getActivityWithStatus($activityId, $studentId)
            : null;

        require __DIR__ . '/../Views/learning/activity_play.php';
    }

    // ----------------------------------------------------------------
    // POST /learning/activity/{id}/submit
    // ------------------------------------------------------------
    public function submitActivity(string $activityId): void {
        header('Content-Type: application/json');
        $activityId = (int) $activityId;
        $studentId  = $this->getStudentId();

        if (!$studentId) {
            echo json_encode(['success' => false, 'message' => 'Student record not found.']);
            exit;
        }

        $input   = json_decode(file_get_contents('php://input'), true) ?? [];
        $answers = $input['answers'] ?? [];
        $attempts = $input['attempts'] ?? [];
        $accuracyPercentage = isset($input['accuracy_percentage']) ? (int)$input['accuracy_percentage'] : null;

        $activity = $this->model->getActivityById($activityId);
        if (!$activity) {
            echo json_encode(['success' => false, 'message' => 'Activity not found.']);
            exit;
        }

        $activityData = json_decode($activity['activity_data'] ?? '{}', true) ?? [];
        $type         = $activity['activity_type'] ?? '';
        $autoScore    = null;
        $maxScore     = (int) ($activity['max_score'] ?? 0);

        $db = Database::getInstance()->getConnection();

        // Attempt logging helper (scoring/accessibility)
        $logAttempt = function($questionIndex, $selectedValue, $correctValue, $isCorrect) use ($db, $activityId, $studentId, $attempts) {
            try {
                $itemAttempts = isset($attempts[$questionIndex]) ? (int)$attempts[$questionIndex] : 1;
                $stmt = $db->prepare("
                    INSERT INTO activity_attempt_log 
                        (activity_id, student_id, question_index, selected_value, correct_value, is_correct, attempted_at)
                    VALUES 
                        (:a, :s, :qi, :sv, :cv, :ic, NOW())
                ");
                $stmt->execute([
                    'a' => $activityId,
                    's' => $studentId,
                    'qi' => $questionIndex,
                    'sv' => (string) $selectedValue . ($itemAttempts > 1 ? " (Attempts: {$itemAttempts})" : ""),
                    'cv' => (string) $correctValue,
                    'ic' => $isCorrect ? 1 : 0
                ]);
            } catch (\Throwable $e) {
                error_log("Failed to log activity attempt: " . $e->getMessage());
            }
        };

        switch ($type) {
            case 'multiple_choice':
                $score = 0;
                foreach ($activityData['questions'] ?? [] as $qi => $q) {
                    $userAnswer = $answers[$qi] ?? null;
                    $questionPoints = (int) ($q['points'] ?? 1);
                    $correctIndex = null;
                    
                    if (isset($q['correct_answer'])) {
                        $correctIndex = $q['correct_answer'];
                    } else {
                        foreach ($q['options'] ?? [] as $oi => $opt) {
                            $isCorrectOpt = null;
                            if (isset($opt['is_correct'])) {
                                $isCorrectOpt = $opt['is_correct'];
                            } elseif (isset($opt['isCorrect'])) {
                                $isCorrectOpt = $opt['isCorrect'];
                            } elseif (isset($opt['correct_answer'])) {
                                $isCorrectOpt = $opt['correct_answer'];
                            }
                            if (!empty($isCorrectOpt)) {
                                $correctIndex = $oi;
                                break;
                            }
                        }
                    }

                    $isCorrect = ($correctIndex !== null && (string)$correctIndex === (string)$userAnswer);
                    if ($isCorrect) {
                        $score += $questionPoints;
                    }
                    
                    $userAnswerText = isset(($q['options'] ?? [])[$userAnswer]) 
                        ? (is_array($q['options'][$userAnswer]) ? ($q['options'][$userAnswer]['text'] ?? $q['options'][$userAnswer]['label'] ?? '') : $q['options'][$userAnswer])
                        : (string)$userAnswer;
                    $correctAnswerText = isset(($q['options'] ?? [])[$correctIndex]) 
                        ? (is_array($q['options'][$correctIndex]) ? ($q['options'][$correctIndex]['text'] ?? $q['options'][$correctIndex]['label'] ?? '') : $q['options'][$correctIndex])
                        : (string)$correctIndex;
                    
                    $logAttempt($qi, $userAnswerText, $correctAnswerText, $isCorrect);
                }
                $autoScore = $score;
                break;

            case 'true_false':
                $score = 0;
                $tfItems = [];
                if (!empty($activityData['questions'])) {
                    foreach ($activityData['questions'] as $q) {
                        $tfItems[] = [
                            'answer' => $q['answer'] ?? $q['correct_answer'] ?? 'true',
                            'points' => $q['points'] ?? ($activityData['points'] ?? 1),
                        ];
                    }
                } else {
                    $tfItems[] = [
                        'answer' => $activityData['answer'] ?? $activityData['correct_answer'] ?? ($activityData['questions'][0]['correct_answer'] ?? null),
                        'points' => $activityData['points'] ?? 1,
                    ];
                }
                foreach ($tfItems as $ti => $item) {
                    $given = $answers[$ti] ?? ($ti === 0 ? ($answers['answer'] ?? null) : null);
                    $correct = $item['answer'] ?? 'true';
                    $isCorrect = (strtolower(trim((string)$given)) === strtolower(trim((string)$correct)));
                    if ($isCorrect) {
                        $score += (int)($item['points'] ?? 1);
                    }
                    $logAttempt($ti, $given ?? '', $correct, $isCorrect);
                }
                $autoScore = $score;
                break;

            case 'fill_in_blanks':
                $score = 0;
                $mode = $activityData['answer_mode'] ?? 'word_bank';
                $useFuzzy = ($mode === 'free_type');
                
                if (!empty($activityData['sentences'])) {
                    $answerIndex = 0;
                    foreach ($activityData['sentences'] as $sentence) {
                        foreach (($sentence['answers'] ?? []) as $correct) {
                            $given = $answers[$answerIndex] ?? '';
                            $matched = false;
                            if ($useFuzzy) {
                                $matched = ScoreHelper::fuzzyMatch($given, (string)$correct, 2);
                            } else {
                                $matched = (strtolower(trim((string)$given)) === strtolower(trim((string)$correct)));
                            }
                            if ($matched) {
                                $score += (int) ($sentence['points'] ?? $activityData['points'] ?? 1);
                            }
                            $answerIndex++;
                        }
                    }
                } elseif (!empty($activityData['answers'])) {
                    foreach ($activityData['answers'] as $si => $correct) {
                        $given = $answers[$si] ?? '';
                        $matched = false;
                        if ($useFuzzy) {
                            $matched = ScoreHelper::fuzzyMatch($given, (string)$correct, 2);
                        } else {
                            $matched = (strtolower(trim((string)$given)) === strtolower(trim((string)$correct)));
                        }
                        if ($matched) {
                            $score += (int) ($activityData['points'] ?? 1);
                        }
                    }
                }
                $autoScore = $score;
                break;

            case 'matching':
                $score = 0;
                $sets = $activityData['sets'] ?? $activityData['matching_sets'] ?? null;
                if (empty($sets)) {
                    $sets = [['pairs' => $activityData['pairs'] ?? [], 'points' => $activityData['points'] ?? 1]];
                }
                
                $totalExpectedPairs = 0;
                foreach ($sets as $set) {
                    $totalExpectedPairs += count($set['pairs'] ?? []);
                }
                
                $totalSubmittedPairs = 0;
                foreach ($answers as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $totalSubmittedPairs++;
                    }
                }
                
                if ($totalSubmittedPairs !== $totalExpectedPairs) {
                    echo json_encode(['success' => false, 'message' => 'Please match all terms before saving.']);
                    exit;
                }
                
                $qi = 0;
                foreach ($sets as $si => $set) {
                    foreach ($set['pairs'] ?? [] as $pi => $pair) {
                        $given = $answers[$si . '_' . $pi] ?? ($si === 0 ? ($answers[$pi] ?? null) : null);
                        $correct = $pair['right'] ?? '';
                        $isCorrect = ((string)$given === (string)$correct);
                        if ($isCorrect) {
                            $score += (int)($set['points'] ?? $activityData['points'] ?? 1);
                        }
                        $logAttempt($qi++, $pair['left'] . ' = ' . $given, $pair['left'] . ' = ' . $correct, $isCorrect);
                    }
                }
                $autoScore = $score;
                break;

            case 'drag_drop_sort':
            case 'sequencing':
                $score = 0;
                $tolerance = (int)($activityData['tolerance'] ?? 0);
                $sets = $activityData['sets'] ?? ($type === 'drag_drop_sort' ? ($activityData['sort_sets'] ?? null) : ($activityData['sequence_sets'] ?? null));
                if (empty($sets)) {
                    $sets = [[
                        'items' => $activityData['items'] ?? $activityData['steps'] ?? [],
                        'points' => $activityData['points'] ?? 1,
                        'correct_order' => $activityData['correct_order'] ?? [],
                    ]];
                }
                foreach ($sets as $si => $set) {
                    $items = $set['items'] ?? $set['steps'] ?? [];
                    $correctOrder = $set['correct_order'] ?? [];
                    if (empty($correctOrder)) {
                        $correctOrder = range(0, count($items) - 1);
                    }
                    $givenOrder = is_array($answers[$si] ?? null) ? array_values($answers[$si]) : ($si === 0 ? array_values($answers) : []);
                    
                    $isPass = ScoreHelper::compareOrder(
                        array_map('strval', $givenOrder),
                        array_map('strval', $correctOrder),
                        $tolerance
                    );
                    
                    if ($isPass) {
                        $score += (int)($set['points'] ?? $activityData['points'] ?? 1);
                    }
                }
                $autoScore = $score;
                break;

            case 'image_label':
                $score = 0;
                $labels = $activityData['labels'] ?? $activityData['markers'] ?? [];
                foreach ($labels as $li => $label) {
                    $correct = trim((string)($label['answer'] ?? ''));
                    $given = trim((string)($answers[$li] ?? ''));
                    $isCorrect = ($correct !== '' && strtolower($given) === strtolower($correct));
                    if ($isCorrect) {
                        $score += (int) ($activityData['points'] ?? 1);
                    }
                    $logAttempt($li, $given, $correct, $isCorrect);
                }
                $autoScore = !empty($labels) ? $score : null;
                break;

            case 'flashcards':
                $autoScore = null;
                break;
        }

        $this->model->saveSubmission($activityId, $studentId, $this->userId, json_encode($answers), $autoScore);

        if ($type === 'flashcards' && !empty($input['flashcard_results'])) {
            $flashcardResultsJson = json_encode($input['flashcard_results']);
            $db->prepare("UPDATE lms_submissions SET flashcard_results = :res WHERE activity_id = :a AND student_id = :s")
               ->execute(['res' => $flashcardResultsJson, 'a' => $activityId, 's' => $studentId]);
        }

        // ── Step 12-14: Stars, XP, Badges ──────────────────────────
        try {
            // Fetch the submission id we just saved
            $stmtSub = $db->prepare("SELECT id FROM lms_submissions WHERE activity_id=:a AND student_id=:s ORDER BY submitted_at DESC LIMIT 1");
            $stmtSub->execute(['a' => $activityId, 's' => $studentId]);
            $subId = (int) $stmtSub->fetchColumn();

            // Step 12 — Stars (auto from score%)
            if ($subId && $autoScore !== null && $maxScore > 0) {
                $this->gamification->saveStarsForSubmission($subId, $studentId, $autoScore, $maxScore);
            }

            // Step 13 — XP per event
            if ($type === 'flashcards') {
                $results = $input['flashcard_results'] ?? [];
                $sumConfidence = 0;
                $count = count($results);
                foreach ($results as $resVal) {
                    $sumConfidence += isset($resVal['confidence']) ? (int)$resVal['confidence'] : 0;
                }
                $maxPossible = $count * 2;
                $rate = $maxPossible > 0 ? ($sumConfidence / $maxPossible) : 0.0;
                $baseXp = 20; // Base XP for flashcard completion
                $xp = (int) round($rate * $baseXp);
                $xp = max($xp, 1);
                
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'flashcards_complete', $activityId)) {
                    $this->gamification->awardXP($studentId, $xp, 'Flashcards Confidence XP', 'flashcards_complete', $activityId);
                }
            } else {
                $xpType = ($type === 'image_label') ? 'view' : ($autoScore !== null ? 'quiz' : 'submission');
                if ($xpType === 'view') {
                    if (!$this->gamification->xpAlreadyAwarded($studentId, 'view', $activityId))
                        $this->gamification->awardXP($studentId, 5, 'Viewed activity', 'view', $activityId);
                } elseif ($xpType === 'quiz' && $maxScore > 0) {
                    $xp = (int) round(($autoScore / $maxScore) * $maxScore);
                    if (!$this->gamification->xpAlreadyAwarded($studentId, 'quiz', $activityId))
                        $this->gamification->awardXP($studentId, max($xp, 1), 'Quiz submitted', 'quiz', $activityId);
                } else {
                    if (!$this->gamification->xpAlreadyAwarded($studentId, 'submission', $activityId))
                        $this->gamification->awardXP($studentId, 10, 'Activity submitted', 'submission', $activityId);
                }
            }

            // Lesson completion bonus (+20 XP)
            $prog = $this->model->getLessonProgress((int)$activity['lesson_plan_id'], $studentId);
            if ($prog['completed'] >= $prog['total'] && $prog['total'] > 0) {
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'lesson_bonus', (int)$activity['lesson_plan_id']))
                    $this->gamification->awardXP($studentId, 20, 'Lesson completed!', 'lesson_bonus', (int)$activity['lesson_plan_id']);
            }

            // Step 14 — Badges auto-check
            $totalSubs = (int) $db->query("SELECT COUNT(*) FROM lms_submissions WHERE student_id={$studentId}")->fetchColumn();
            if ($totalSubs >= 1)  $this->gamification->awardBadge($studentId, 'first_activity');
            if ($totalSubs >= 5)  $this->gamification->awardBadge($studentId, 'five_in_a_row');
            if ($autoScore !== null && $maxScore > 0 && $autoScore >= $maxScore)
                $this->gamification->awardBadge($studentId, 'perfect_score');
            if ($prog['completed'] >= $prog['total'] && $prog['total'] > 0)
                $this->gamification->awardBadge($studentId, 'lesson_complete');
            $totalStarsEarned = $this->gamification->getTotalStars($studentId);
            if ($totalStarsEarned >= 10) $this->gamification->awardBadge($studentId, 'star_collector');
            // all_done: all published plans fully complete
            $allDone = $db->query("SELECT COUNT(*) FROM lesson_plans lp JOIN lesson_assignments la ON lp.id=la.lesson_plan_id WHERE la.student_id={$studentId} AND lp.status='published'")->fetchColumn();
            $allDoneSub = $db->query("SELECT COUNT(DISTINCT act.lesson_plan_id) FROM lms_submissions sub JOIN lms_activities act ON sub.activity_id=act.id WHERE sub.student_id={$studentId}")->fetchColumn();
            if ($allDone > 0 && $allDoneSub >= $allDone) $this->gamification->awardBadge($studentId, 'all_done');

            // Badge XP bonus (+15 per new badge)
            $earnedKeys = $this->gamification->getEarnedBadgeKeys($studentId);
            foreach ($earnedKeys as $bk) {
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'badge_bonus', 0))
                    break;
            }

            // ── Stage 3: Smart Adaptive Remedial Track Check ──────────
            $scorePercentage = ($autoScore !== null && $maxScore > 0) ? round(($autoScore / $maxScore) * 100) : 100;
            $threshold = 70; // Default threshold
            
            // Check lesson plan configured threshold
            $stmtLp = $db->prepare("SELECT score_threshold, remedial_for_lesson_id FROM lesson_plans WHERE id = :lid LIMIT 1");
            $stmtLp->execute(['lid' => (int)$activity['lesson_plan_id']]);
            $lpRow = $stmtLp->fetch(PDO::FETCH_ASSOC);
            if ($lpRow && !empty($lpRow['score_threshold'])) {
                $threshold = (int)$lpRow['score_threshold'];
            }

            $needsRemediation = ($autoScore !== null && $maxScore > 0 && $scorePercentage < $threshold);
            if ($needsRemediation && $subId) {
                $db->prepare("UPDATE lms_submissions SET needs_remediation = 1 WHERE id = :id")->execute(['id' => $subId]);
            }

            // Log submission
            $db->prepare("INSERT INTO lms_logs (student_id,activity_id,material_id,action,performed_by,performed_at) VALUES (:s,:a,NULL,'submitted',:p,NOW())")
               ->execute(['s' => $studentId, 'a' => $activityId, 'p' => $this->userId]);

        } catch (\Throwable $e) {
            error_log('LearningController::submitActivity() gamification error: ' . $e->getMessage());
        }

        echo json_encode([
            'success'             => true,
            'auto_score'          => $autoScore,
            'max_score'           => $maxScore,
            'score_percentage'    => $scorePercentage ?? 100,
            'accuracy_percentage' => $accuracyPercentage ?? ($scorePercentage ?? 100),
            'threshold'           => $threshold ?? 70,
            'needs_remediation'   => $needsRemediation ?? false,
            'type'                => $type,
            'message'             => $autoScore !== null
                ? ($needsRemediation
                    ? "Score: {$autoScore} / {$maxScore} ({$scorePercentage}%). Girekomenda nga mo-review una sa Remedial Module aron mas masabtan ang leksyon!"
                    : "Excellent work! Score: {$autoScore} / {$maxScore} ({$scorePercentage}%). Nakapasar ka!")
                : ($type === 'image_label'
                    ? 'Submitted! Your teacher will review and grade this.'
                    : 'Great job reviewing!'),
        ]);

        exit;
    }

    // ----------------------------------------------------------------
    // GET /learning/progress
    // ----------------------------------------------------------------
    public function progress(): void {
        $studentId   = $this->getStudentId();
        $basePath    = $this->basePath;
        $studentName = $studentId ? $this->getStudentName($studentId) : 'Learner';

        // Gamification summary for sidebar/topbar
        $gamSummary = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP    = $gamSummary['total_xp'];
        $totalStars = $gamSummary['total_stars'];

        $overallComplete = 0;
        $overallTotal    = 0;
        $avgScore        = 0;
        $domainProgress  = [];
        $recentGrades    = [];
        $recentSubmissions = [];

        if ($studentId) {
            $overall         = $this->model->getStudentOverallProgress($studentId);
            $overallComplete = $overall['completed'];
            $overallTotal    = $overall['total'];
            $avgScore        = $overall['avg_score'] ?? 0;
            $domainProgress  = $this->model->getProgressByDomain($studentId);
            $recentGrades    = $this->getRecentGrades($studentId);
            $recentSubmissions = $this->getRecentSubmissions($studentId);
        }

        require __DIR__ . '/../Views/learning/progress.php';
    }

    private function getRecentGrades(int $studentId): array {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT
                    lp.title  AS lesson_plan_title,
                    act.title AS activity_title,
                    g.score,
                    g.max_score,
                    g.graded_at
                FROM lms_grades g
                JOIN lms_submissions sub ON g.submission_id = sub.id
                JOIN lms_activities act  ON sub.activity_id = act.id
                JOIN lesson_plans lp     ON act.lesson_plan_id = lp.id
                WHERE sub.student_id = :student_id
                ORDER BY g.graded_at DESC
                LIMIT 10
            ");
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('LearningController::getRecentGrades() error: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentSubmissions(int $studentId): array {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT
                    lp.title  AS lesson_plan_title,
                    act.title AS activity_title,
                    act.max_score AS activity_max_score,
                    sub.auto_score,
                    sub.submitted_at,
                    g.score,
                    g.max_score AS grade_max_score,
                    g.graded_at
                FROM lms_submissions sub
                JOIN lms_activities act ON sub.activity_id = act.id
                JOIN lesson_plans lp    ON act.lesson_plan_id = lp.id
                LEFT JOIN lms_grades g  ON g.submission_id = sub.id
                WHERE sub.student_id = :student_id
                ORDER BY sub.submitted_at DESC
                LIMIT 10
            ");
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('LearningController::getRecentSubmissions() error: ' . $e->getMessage());
            return [];
        }
    }

    // ----------------------------------------------------------------
    // Step 16 — GET /parent/child-progress
    // Lists all children enrolled under the parent with composite progress
    // ----------------------------------------------------------------
    public function parentChildProgress(): void {
        ParentGateMiddleware::check();
        if ($this->userRole !== 'parent') {
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Fetch all verified enrolled students linked to this parent
        $stmt = $db->prepare("
            SELECT sr.id AS student_id,
                   sr.student_id AS student_code,
                   sr.student_name,
                   sr.lrn,
                   sr.section_name,
                   COALESCE(es.grade_level_to_enroll, 'SPED Program') AS grade_level,
                   es.status,
                   sch.school_name,
                   u.name AS teacher_name
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN schools sch ON sr.school_id = sch.id
            LEFT JOIN users u ON sr.assigned_teacher_id = u.id
            WHERE es.parent_id = :pid
              AND es.status = 'verified'
            ORDER BY sr.student_name ASC
        ");
        $stmt->execute(['pid' => $this->userId]);
        $children = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Attach comprehensive progress summary per child
        foreach ($children as &$child) {
            $sid = (int)$child['student_id'];
            $gam = $this->gamification->getSummary($sid);
            $child['total_xp']    = $gam['total_xp'] ?? 0;
            $child['total_stars'] = $gam['total_stars'] ?? 0;

            // Fetch published lesson plans for this child
            $publishedLessons = $this->model->getPublishedForStudent($sid);
            $totalLessons = count($publishedLessons);
            $completedLessons = 0;
            $totalTasks = 0;
            $completedTasks = 0;

            foreach ($publishedLessons as $lp) {
                $lpId = (int)$lp['id'];
                $pages = $this->model->getPagesByLessonPlan($lpId);
                $pageProg = $this->model->getPageProgress($sid, $lpId);
                $hasSlides = count($pages) > 0;
                $slidesDone = !empty($pageProg['is_completed']);

                $actProg = $this->model->getLessonProgress($lpId, $sid);
                $actTotal = $actProg['total'];
                $actDone = $actProg['completed'];

                $lessonTaskTotal = $actTotal + ($hasSlides ? 1 : 0);
                $lessonTaskDone  = $actDone + ($slidesDone ? 1 : 0);

                $totalTasks += $lessonTaskTotal;
                $completedTasks += $lessonTaskDone;

                if ($lessonTaskTotal > 0 && $lessonTaskDone >= $lessonTaskTotal) {
                    $completedLessons++;
                }
            }

            $child['total_lessons']     = $totalLessons;
            $child['completed_lessons'] = $completedLessons;
            $child['total_tasks']       = $totalTasks;
            $child['completed_tasks']   = $completedTasks;
            $child['pct']               = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        }
        unset($child);

        $basePath = $this->basePath;
        require __DIR__ . '/../Views/dashboard/parent_child_progress.php';
    }

    // ----------------------------------------------------------------
    // Step 17 — GET /parent/child-progress/{id}
    // Detailed Parent Dossier: Story slides, Activities, Scores, Domains
    // ----------------------------------------------------------------
    public function parentStudentProgress(string $studentId): void {
        ParentGateMiddleware::check();
        if ($this->userRole !== 'parent') {
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $studentId = (int)$studentId;
        $db = Database::getInstance()->getConnection();

        // Security check: verify child belongs to parent
        $check = $db->prepare("
            SELECT sr.id,
                   sr.student_name,
                   sr.student_id AS student_code,
                   sr.lrn,
                   sr.section_name,
                   COALESCE(es.grade_level_to_enroll, 'SPED Program') AS grade_level,
                   es.status,
                   sch.school_name,
                   sch.address AS school_address,
                   u.name AS teacher_name,
                   u.email AS teacher_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN schools sch ON sr.school_id = sch.id
            LEFT JOIN users u ON sr.assigned_teacher_id = u.id
            WHERE sr.id = :sid
              AND es.parent_id = :pid
              AND es.status = 'verified'
            LIMIT 1
        ");
        $check->execute(['sid' => $studentId, 'pid' => $this->userId]);
        $student = $check->fetch(\PDO::FETCH_ASSOC);

        if (!$student) {
            $_SESSION['error'] = 'Access denied or student record not found.';
            header('Location: ' . $this->basePath . '/parent/child-progress');
            exit;
        }

        $studentName = $student['student_name'];
        $gam = $this->gamification->getSummary($studentId);
        $totalXP    = $gam['total_xp'] ?? 0;
        $totalStars = $gam['total_stars'] ?? 0;

        // Fetch detailed lessons and compute progress
        $publishedLessons = $this->model->getPublishedForStudent($studentId);
        $lessonsDetail = [];
        $totalTasksAll = 0;
        $completedTasksAll = 0;
        $completedLessonsCount = 0;
        $domainStats = [];

        foreach ($publishedLessons as $lp) {
            $lpId = (int)$lp['id'];
            $domain = $lp['pdsp_domain'] ?: 'General';

            if (!isset($domainStats[$domain])) {
                $domainStats[$domain] = [
                    'domain'            => $domain,
                    'total_tasks'       => 0,
                    'completed_tasks'   => 0,
                    'lessons_count'     => 0,
                    'completed_lessons' => 0
                ];
            }
            $domainStats[$domain]['lessons_count']++;

            $pages = $this->model->getPagesByLessonPlan($lpId);
            $pageProg = $this->model->getPageProgress($studentId, $lpId);
            $totalPages = count($pages);
            $lastPage = (int)($pageProg['last_page_number'] ?? 0);
            $isSlidesDone = !empty($pageProg['is_completed']);

            $materials = $this->model->getMaterials($lpId);
            $activities = $this->model->getActivities($lpId);
            $actList = [];
            $completedActCount = 0;

            foreach ($activities as $act) {
                $actId = (int)$act['id'];
                $actStatus = $this->model->getActivityWithStatus($actId, $studentId);
                $isSubmitted = !empty($actStatus['submission_id']);
                if ($isSubmitted) $completedActCount++;

                // Get attempt count for this activity
                $attStmt = $db->prepare("SELECT COUNT(*) FROM activity_attempt_log WHERE activity_id = :aid AND student_id = :sid");
                $attStmt->execute(['aid' => $actId, 'sid' => $studentId]);
                $attemptsCount = (int)$attStmt->fetchColumn();

                $actList[] = [
                    'id'            => $actId,
                    'title'         => $act['title'],
                    'activity_type' => $act['activity_type'],
                    'max_score'     => LessonPlanModel::displayMaxScoreForActivity($act),
                    'is_submitted'  => $isSubmitted,
                    'score'         => $actStatus['score'] ?? $actStatus['auto_score'] ?? null,
                    'submitted_at'  => $actStatus['submitted_at'] ?? null,
                    'remarks'       => $actStatus['remarks'] ?? null,
                    'attempts'      => $attemptsCount > 0 ? $attemptsCount : ($isSubmitted ? 1 : 0)
                ];
            }

            $lessonTasks = count($activities) + ($totalPages > 0 ? 1 : 0);
            $lessonDone  = $completedActCount + ($isSlidesDone ? 1 : 0);
            $lessonPct   = $lessonTasks > 0 ? round(($lessonDone / $lessonTasks) * 100) : 0;

            $totalTasksAll += $lessonTasks;
            $completedTasksAll += $lessonDone;

            $domainStats[$domain]['total_tasks'] += $lessonTasks;
            $domainStats[$domain]['completed_tasks'] += $lessonDone;
            if ($lessonTasks > 0 && $lessonDone >= $lessonTasks) {
                $domainStats[$domain]['completed_lessons']++;
                $completedLessonsCount++;
            }

            $lessonsDetail[] = [
                'id'                  => $lpId,
                'title'               => $lp['title'],
                'domain'              => $domain,
                'school_year'         => $lp['school_year'] ?? '',
                'total_pages'         => $totalPages,
                'last_page'           => $lastPage,
                'is_slides_done'      => $isSlidesDone,
                'slides_completed_at' => $pageProg['completed_at'] ?? null,
                'materials_count'     => count($materials),
                'materials'           => $materials,
                'activities_count'    => count($activities),
                'activities_done'     => $completedActCount,
                'activities'          => $actList,
                'total_tasks'         => $lessonTasks,
                'completed_tasks'     => $lessonDone,
                'pct'                 => $lessonPct
            ];
        }

        $overallPct = $totalTasksAll > 0 ? round(($completedTasksAll / $totalTasksAll) * 100) : 0;

        // Calculate domain percentages
        foreach ($domainStats as &$d) {
            $d['pct'] = $d['total_tasks'] > 0 ? round(($d['completed_tasks'] / $d['total_tasks']) * 100) : 0;
        }
        unset($d);

        // Graded / submitted activity logs
        $recentGrades = $this->getRecentSubmissions($studentId);

        $basePath = $this->basePath;
        require __DIR__ . '/../Views/dashboard/parent_student_progress.php';
    }
}
