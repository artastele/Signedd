<?php
// Part of: SignED - Activity Play View (Learner Side)

$pageTitle    = htmlspecialchars($activity['title'] ?? 'Activity') . ' - SignED';
$basePath     = defined('BASE_PATH') ? BASE_PATH : '';
$actId        = (int)($activity['id'] ?? 0);
$actType      = $activity['activity_type'] ?? '';
$actData      = is_array($activity['activity_data'] ?? null) ? $activity['activity_data'] : [];
$hasSub       = !empty($submission['submission_id']);
$lessonPlanId = (int)($activity['lesson_plan_id'] ?? 0);
$typeLabel    = ucwords(str_replace('_', ' ', $actType));
$maxScore     = (int)($activity['max_score'] ?? 0);
$submittedAnswers = [];
$tfItems = [];
if (!empty($actData['questions']) && $actType === 'true_false') {
    foreach ($actData['questions'] as $question) {
        $tfItems[] = [
            'statement' => $question['statement'] ?? $question['question'] ?? '',
            'answer' => $question['answer'] ?? $question['correct_answer'] ?? 'true',
            'image' => $question['image'] ?? '',
            'points' => $question['points'] ?? ($actData['points'] ?? 1),
        ];
    }
} elseif ($actType === 'true_false') {
    $tfItems[] = [
        'statement' => $actData['statement'] ?? $actData['question'] ?? '',
        'answer' => $actData['answer'] ?? $actData['correct_answer'] ?? ($actData['questions'][0]['correct_answer'] ?? 'true'),
        'image' => $actData['image'] ?? ($actData['questions'][0]['image'] ?? ''),
        'points' => $actData['points'] ?? 1,
    ];
}

$fillItems = [];
if ($actType === 'fill_in_blanks' && !empty($actData['sentences'])) {
    foreach ($actData['sentences'] as $sentenceIndex => $sentence) {
        $answers = array_values((array)($sentence['answers'] ?? []));
        foreach ($answers as $answerIndex => $answerText) {
            $fillItems[] = [
                'text' => (string)($sentence['text'] ?? ''),
                'answers' => [$answerText],
                'blank_label' => count($answers) > 1 ? 'Blank ' . ($answerIndex + 1) : 'Answer',
                'points' => $sentence['points'] ?? ($actData['points'] ?? 1),
                'source_sentence' => $sentenceIndex,
                'source_blank' => $answerIndex,
            ];
        }
    }
} elseif ($actType === 'fill_in_blanks' && !empty($actData['sentence'])) {
    foreach (array_values((array)($actData['answers'] ?? [])) as $answerIndex => $answerText) {
        $fillItems[] = [
            'text' => (string)$actData['sentence'],
            'answers' => [$answerText],
            'blank_label' => 'Blank ' . ($answerIndex + 1),
            'points' => $actData['points'] ?? 1,
        ];
    }
}

$matchingSets = [];
if ($actType === 'matching') {
    $rawSets = $actData['sets'] ?? $actData['matching_sets'] ?? null;
    if (is_array($rawSets) && !empty($rawSets)) {
        foreach ($rawSets as $setIndex => $set) {
            $matchingSets[] = [
                'title' => $set['title'] ?? ('Matching Set ' . ($setIndex + 1)),
                'pairs' => $set['pairs'] ?? [],
                'points' => $set['points'] ?? ($actData['points'] ?? 1),
            ];
        }
    } else {
        $matchingSets[] = [
            'title' => 'Matching Set 1',
            'pairs' => $actData['pairs'] ?? [],
            'points' => $actData['points'] ?? 1,
        ];
    }
}

$sortSets = [];
if ($actType === 'drag_drop_sort') {
    $rawSets = $actData['sets'] ?? $actData['sort_sets'] ?? null;
    if (is_array($rawSets) && !empty($rawSets)) {
        foreach ($rawSets as $setIndex => $set) {
            $sortSets[] = [
                'title' => $set['title'] ?? ('Sorting Question ' . ($setIndex + 1)),
                'items' => $set['items'] ?? [],
                'points' => $set['points'] ?? ($actData['points'] ?? 1),
            ];
        }
    } else {
        $sortSets[] = [
            'title' => 'Sorting Question 1',
            'items' => $actData['items'] ?? [],
            'points' => $actData['points'] ?? 1,
        ];
    }
}

$sequenceSets = [];
if ($actType === 'sequencing') {
    $rawSets = $actData['sets'] ?? $actData['sequence_sets'] ?? null;
    if (is_array($rawSets) && !empty($rawSets)) {
        foreach ($rawSets as $setIndex => $set) {
            $sequenceSets[] = [
                'title' => $set['title'] ?? ('Sequence Question ' . ($setIndex + 1)),
                'steps' => $set['steps'] ?? $set['items'] ?? [],
                'points' => $set['points'] ?? ($actData['points'] ?? 1),
            ];
        }
    } else {
        $sequenceSets[] = [
            'title' => 'Sequence Question 1',
            'steps' => $actData['steps'] ?? $actData['items'] ?? [],
            'points' => $actData['points'] ?? 1,
        ];
    }
}

$flashcardSets = [];
if ($actType === 'flashcards') {
    $rawSets = $actData['sets'] ?? $actData['flashcard_sets'] ?? null;
    if (is_array($rawSets) && !empty($rawSets)) {
        foreach ($rawSets as $setIndex => $set) {
            $flashcardSets[] = [
                'title' => $set['title'] ?? ('Flashcard Set ' . ($setIndex + 1)),
                'cards' => $set['cards'] ?? [],
            ];
        }
    } else {
        $flashcardSets[] = [
            'title' => 'Flashcard Set 1',
            'cards' => $actData['cards'] ?? [],
        ];
    }
}

if (!empty($submission['answers'])) {
    $decodedAnswers = json_decode((string)$submission['answers'], true);
    $submittedAnswers = is_array($decodedAnswers) ? $decodedAnswers : [];
}

$earnedScore = null;
if (isset($submission['score']) && $submission['score'] !== null && $submission['score'] !== '') {
    $earnedScore = (int)$submission['score'];
    $scoreMax = (int)($submission['grade_max_score'] ?? $maxScore);
} elseif (isset($submission['auto_score']) && $submission['auto_score'] !== null && $submission['auto_score'] !== '') {
    $earnedScore = (int)$submission['auto_score'];
    $scoreMax = $maxScore;
} else {
    $scoreMax = $maxScore;
}

$scorePercent = ($earnedScore !== null && $scoreMax > 0) ? (int)round(($earnedScore / $scoreMax) * 100) : null;
$starsEarned = $scorePercent === null ? null : ($scorePercent >= 90 ? 3 : ($scorePercent >= 70 ? 2 : 1));
$questionTotal = match ($actType) {
    'multiple_choice' => count($actData['questions'] ?? []),
    'true_false' => count($tfItems),
    'fill_in_blanks' => count($fillItems),
    'matching' => count($matchingSets),
    'drag_drop_sort' => count($sortSets),
    'sequencing' => count($sequenceSets),
    'flashcards' => array_sum(array_map(static fn($set) => count($set['cards'] ?? []), $flashcardSets)),
    'image_label' => 1,
    default => 1,
};
$questionTotal = max($questionTotal, 1);

$getOptionText = static function ($option): string {
    if (is_array($option)) {
        return (string)($option['text'] ?? $option['label'] ?? '');
    }
    return (string)$option;
};

$getOptionImage = static function ($option): string {
    if (is_array($option)) {
        return (string)($option['image'] ?? '');
    }
    return '';
};

$getCorrectMcIndex = static function (array $question): ?int {
    if (isset($question['correct_answer'])) {
        return is_numeric($question['correct_answer']) ? (int)$question['correct_answer'] : null;
    }
    foreach (($question['options'] ?? []) as $index => $option) {
        if (!is_array($option)) {
            continue;
        }
        $isCorrect = $option['is_correct'] ?? $option['isCorrect'] ?? $option['correct_answer'] ?? false;
        if (!empty($isCorrect)) {
            return (int)$index;
        }
    }
    return null;
};

$feedbackData = [
    'multiple_choice' => [],
    'true_false' => null,
    'fill_in_blanks' => [],
    'matching' => [],
    'image_label' => [],
    'order' => [],
    'original_items' => [],
    'tolerance' => [],
];
foreach (($actData['questions'] ?? []) as $qi => $question) {
    $correctIndex = $getCorrectMcIndex($question);
    $feedbackData['multiple_choice'][$qi] = [
        'correct' => $correctIndex,
        'correctText' => $correctIndex !== null
            ? $getOptionText(($question['options'] ?? [])[$correctIndex] ?? '')
            : null,
    ];
}
$feedbackData['true_false'] = [];
foreach ($tfItems as $ti => $tfItem) {
    $feedbackData['true_false'][$ti] = strtolower((string)($tfItem['answer'] ?? 'true'));
}
foreach ($fillItems as $si => $sentence) {
    $feedbackData['fill_in_blanks'][$si] = array_values(array_map('strval', (array)($sentence['answers'] ?? [])));
}
if ($actType === 'matching') {
    foreach ($matchingSets as $setIndex => $set) {
        $feedbackData['matching'][$setIndex] = [];
        foreach (($set['pairs'] ?? []) as $pi => $pair) {
            $feedbackData['matching'][$setIndex][$pi] = $pair['right'] ?? '';
        }
    }
}
if ($actType === 'image_label') {
    $labels = $actData['labels'] ?? $actData['markers'] ?? [];
    foreach ($labels as $li => $label) {
        $feedbackData['image_label'][$li] = $label['answer'] ?? '';
    }
}
if ($actType === 'drag_drop_sort' || $actType === 'sequencing') {
    $orderSets = $actType === 'drag_drop_sort' ? $sortSets : $sequenceSets;
    foreach ($orderSets as $setIndex => $set) {
        $items = $set['items'] ?? $set['steps'] ?? [];
        $correctOrder = $set['correct_order'] ?? [];
        if (empty($correctOrder)) {
            $correctOrder = range(0, count($items) - 1);
        }
        $feedbackData['order'][$setIndex] = $correctOrder;
        $feedbackData['tolerance'][$setIndex] = (int)($set['tolerance'] ?? $actData['tolerance'] ?? 0);
        $feedbackData['original_items'][$setIndex] = [];
        foreach ($items as $ii => $item) {
            $feedbackData['original_items'][$setIndex][$ii] = is_array($item) ? ($item['text'] ?? $item['label'] ?? '') : $item;
        }
    }
}

require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . $basePath . '/css/learner.css?v=' . time() . '">';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page">
    <div class="game-player-shell">
        <a href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>" class="btn btn-bubble-outline mb-3">
            <i class="ph-bold ph-arrow-left me-1"></i> Bumalik sa Aralin
        </a>

        <section class="game-mission-header">
            <div>
                <div class="quest-eyebrow"><i class="ph-bold ph-game-controller me-1"></i> Pagsasanay</div>
                <h1><?php echo htmlspecialchars($activity['title'] ?? 'Activity'); ?></h1>
            </div>
            <div class="game-score-chip" aria-label="Mission summary">
                <span><?php echo htmlspecialchars($typeLabel); ?></span>
                <?php if ($hasSub && $earnedScore !== null): ?>
                    <strong><?php echo $earnedScore; ?> / <?php echo max($scoreMax, 0); ?></strong>
                <?php else: ?>
                    <strong><?php echo $questionTotal; ?> gawain</strong>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($activity['instructions'])): ?>
            <section class="game-guide-card">
                <strong><i class="bi bi-compass"></i> Mission Guide / Mga Panuto</strong>
                <div class="activity-instructions-rich">
                    <?php 
                    $instr = (string)$activity['instructions'];
                    if (preg_match('/<[a-z][\s\S]*>/i', $instr)) {
                        echo $instr;
                    } else {
                        echo '<p>' . nl2br(htmlspecialchars($instr)) . '</p>';
                    }
                    ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($hasSub): ?>
            <section class="mission-complete-card text-center p-4 bg-white border rounded-4 shadow-sm">
                <div class="complete-badge-icon mb-2"><i class="ph-bold ph-trophy text-warning" style="font-size: 3.5rem;" aria-hidden="true"></i></div>
                <div class="quest-eyebrow text-success fw-bold"><i class="ph-bold ph-check-circle me-1"></i> Natapos ang Gawain!</div>
                <h2 class="fw-bold text-dark mt-2">Napakagaling mo!</h2>
                <?php if ($earnedScore !== null): ?>
                    <div class="complete-score fw-bold fs-3 text-primary my-2"><?php echo $earnedScore; ?> / <?php echo max($scoreMax, 0); ?></div>
                    <div class="stars-earned d-flex justify-content-center gap-2 fs-3 my-3" aria-label="<?php echo (int)$starsEarned; ?> stars earned">
                        <?php for ($s = 1; $s <= 3; $s++): ?>
                            <i class="ph-bold ph-star <?php echo ($starsEarned !== null && $s <= $starsEarned) ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                <?php else: ?>
                    <p class="congrats-text text-muted my-2">Matagumpay mong naisumite ang iyong sagot!</p>
                <?php endif; ?>
                <div class="game-actions center d-flex justify-content-center gap-2 mt-4 flex-wrap">
                    <a class="btn btn-bubble-outline px-4 py-2 d-flex align-items-center gap-1.5" href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>">
                        <i class="ph-bold ph-book-open"></i> <span>Bumalik sa Aralin</span>
                    </a>
                    <a class="btn btn-bubble px-4 py-2 d-flex align-items-center gap-1.5" href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard?tab=badges">
                        <i class="ph-bold ph-trophy"></i> <span>Aking mga Bituin</span>
                    </a>
                </div>
            </section>

            <section class="answer-review-panel">
                <div class="review-header">
                    <div>
                        <div class="quest-eyebrow">Review Answers</div>
                        <h2>Your mission answers</h2>
                    </div>
                </div>

                <?php if ($actType === 'multiple_choice'): ?>
                    <?php foreach (($actData['questions'] ?? []) as $qi => $q): ?>
                        <?php
                        $selected = isset($submittedAnswers[$qi]) ? (int)$submittedAnswers[$qi] : null;
                        $correct = $getCorrectMcIndex($q);
                        $isRight = $correct !== null && $selected !== null && $selected === $correct;
                        $qImg = $q['image'] ?? '';
                        ?>
                        <article class="review-card <?php echo $correct === null ? '' : ($isRight ? 'is-correct' : 'is-wrong'); ?>">
                            <?php if (!empty($qImg)): ?>
                                <div class="mb-2"><img src="<?php echo htmlspecialchars($qImg); ?>" class="img-fluid rounded border" alt="Question illustration" style="max-height: 120px; object-fit: contain;"></div>
                            <?php endif; ?>
                            <strong><?php echo ($qi + 1) . '. ' . htmlspecialchars($q['text'] ?? $q['question'] ?? 'Question'); ?></strong>
                            <p>Your answer: <?php 
                                $selOpt = ($q['options'] ?? [])[$selected] ?? null;
                                $selImg = $getOptionImage($selOpt);
                                if (!empty($selImg)) {
                                    echo '<img src="' . htmlspecialchars($selImg) . '" class="rounded border me-1" style="max-height: 32px; vertical-align: middle;"> ';
                                }
                                echo htmlspecialchars($selOpt !== null ? $getOptionText($selOpt) : 'No answer'); 
                            ?></p>
                            <?php if ($correct !== null): ?>
                                <p>Correct answer: <?php 
                                    $corOpt = ($q['options'] ?? [])[$correct] ?? null;
                                    $corImg = $getOptionImage($corOpt);
                                    if (!empty($corImg)) {
                                        echo '<img src="' . htmlspecialchars($corImg) . '" class="rounded border me-1" style="max-height: 32px; vertical-align: middle;"> ';
                                    }
                                    echo htmlspecialchars($corOpt !== null ? $getOptionText($corOpt) : ''); 
                                ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'true_false'): ?>
                    <?php foreach ($tfItems as $ti => $tfItem): ?>
                        <?php
                        $correct = $tfItem['answer'] ?? null;
                        $selected = $submittedAnswers[$ti] ?? ($ti === 0 ? ($submittedAnswers['answer'] ?? null) : null);
                        $isRight = $correct !== null && strtolower((string)$selected) === strtolower((string)$correct);
                        $tfImg = $tfItem['image'] ?? '';
                        ?>
                        <article class="review-card <?php echo $correct === null ? '' : ($isRight ? 'is-correct' : 'is-wrong'); ?>">
                            <?php if (!empty($tfImg)): ?>
                                <div class="mb-2"><img src="<?php echo htmlspecialchars($tfImg); ?>" class="img-fluid rounded border" alt="Statement illustration" style="max-height: 120px; object-fit: contain;"></div>
                            <?php endif; ?>
                            <strong><?php echo htmlspecialchars($tfItem['statement'] ?? 'True or false challenge'); ?></strong>
                            <p>Your answer: <?php echo htmlspecialchars(ucfirst((string)($selected ?? 'No answer'))); ?></p>
                            <?php if ($correct !== null): ?><p>Correct answer: <?php echo htmlspecialchars(ucfirst((string)$correct)); ?></p><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'matching'): ?>
                    <?php foreach ($matchingSets as $setIndex => $set): ?>
                        <article class="review-card">
                            <strong><?php echo htmlspecialchars($set['title']); ?></strong>
                            <?php foreach (($set['pairs'] ?? []) as $pi => $pair): ?>
                                <?php $key = $setIndex . '_' . $pi; $selected = $submittedAnswers[$key] ?? ($submittedAnswers[$pi] ?? ''); ?>
                                <p><?php 
                                    if (!empty($pair['left_image'] ?? $pair['image'] ?? '')) {
                                        echo '<img src="' . htmlspecialchars($pair['left_image'] ?? $pair['image']) . '" class="rounded border me-1" style="max-height: 28px; vertical-align: middle;"> ';
                                    }
                                    echo htmlspecialchars($pair['left'] ?? 'Match item'); 
                                ?>: <?php echo htmlspecialchars((string)($selected ?: 'No answer')); ?> | Correct: <?php 
                                    if (!empty($pair['right_image'] ?? '')) {
                                        echo '<img src="' . htmlspecialchars($pair['right_image']) . '" class="rounded border me-1" style="max-height: 28px; vertical-align: middle;"> ';
                                    }
                                    echo htmlspecialchars((string)($pair['right'] ?? '')); 
                                ?></p>
                            <?php endforeach; ?>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'fill_in_blanks'): ?>
                    <?php foreach ($fillItems as $si => $sentence): ?>
                        <article class="review-card">
                            <strong><?php echo htmlspecialchars($sentence['text'] ?? 'Fill in the blank'); ?></strong>
                            <p>Your answer: <?php echo htmlspecialchars((string)($submittedAnswers[$si] ?? 'No answer')); ?></p>
                            <?php if (!empty($sentence['answers'])): ?>
                                <p>Accepted answer: <?php echo htmlspecialchars(implode(' / ', array_map('strval', (array)$sentence['answers']))); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'image_label'): ?>
                    <?php foreach (($actData['labels'] ?? $actData['markers'] ?? []) as $li => $label): ?>
                        <article class="review-card">
                            <strong>Label <?php echo $li + 1; ?></strong>
                            <p>Your answer: <?php echo htmlspecialchars((string)($submittedAnswers[$li] ?? 'No answer')); ?></p>
                            <?php if (!empty($label['answer'])): ?>
                                <p>Expected label: <?php echo htmlspecialchars((string)$label['answer']); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'drag_drop_sort' || $actType === 'sequencing'): ?>
                    <article class="review-card">
                        <strong>Your order</strong>
                        <p><?php echo htmlspecialchars(implode(', ', array_map('strval', $submittedAnswers))); ?></p>
                    </article>
                <?php else: ?>
                    <article class="review-card">
                        <strong>Submitted response</strong>
                        <p><?php echo htmlspecialchars(json_encode($submittedAnswers)); ?></p>
                    </article>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <style>
                /* Premium Accessible Styling */
                .game-answer-card, .matching-item-card, .game-sort-item {
                    transition: transform 0.2s ease, outline-color 0.2s ease, box-shadow 0.2s ease;
                    border: 2px solid #dee2e6;
                }
                @media (prefers-reduced-motion: no-preference) {
                    .game-answer-card:hover, .matching-item-card:hover {
                        transform: translateY(-2px);
                    }
                }
                .game-answer-card:focus, .matching-item-card:focus, .game-sort-item:focus {
                    outline: 3px solid #1e4072 !important;
                    box-shadow: 0 0 8px rgba(30, 64, 114, 0.5) !important;
                }
                .game-answer-card.selected {
                    transform: scale(1.02);
                    outline: 3px solid #a01422 !important;
                    box-shadow: 0 0 12px rgba(160, 20, 34, 0.4) !important;
                }
                .game-answer-card.true-card.selected {
                    outline: 3px solid #008080 !important;
                    box-shadow: 0 0 12px rgba(0, 128, 128, 0.4) !important;
                }
                .game-answer-card.false-card.selected {
                    outline: 3px solid #a01422 !important;
                    box-shadow: 0 0 12px rgba(160, 20, 34, 0.4) !important;
                }
                .fib-blank-slot {
                    border: 2px dashed #a01422;
                    background-color: #fff9f9;
                    min-height: 50px;
                    transition: box-shadow 0.2s ease;
                }
                .fib-blank-slot.active-slot {
                    outline: 3px solid #1e4072;
                    box-shadow: 0 0 10px rgba(30, 64, 114, 0.4);
                }
                .glow {
                    box-shadow: 0 0 15px #FFBF00 !important;
                    border-color: #FFBF00 !important;
                }
                .matching-item-card.selected-left {
                    outline: 3px solid #FFBF00 !important;
                }
                .label-dot-pin.pulsing {
                    animation: pulsePinGlow 1.5s infinite;
                }
                @keyframes pulsePinGlow {
                    0% { box-shadow: 0 0 0 0 rgba(30, 64, 114, 0.7); }
                    70% { box-shadow: 0 0 0 12px rgba(30, 64, 114, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(30, 64, 114, 0); }
                }
                @keyframes cardShake {
                    0%, 100% { transform: translateX(0); }
                    20%, 60% { transform: translateX(-8px); }
                    40%, 80% { transform: translateX(8px); }
                }
                .shake-animation {
                    animation: cardShake 0.45s ease-in-out;
                }
                .attempt-badge {
                    font-size: 0.8rem;
                    font-weight: 600;
                    padding: 4px 10px;
                    border-radius: 999px;
                    background: #f1f5f9;
                    color: #475569;
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                }
                .accuracy-score-chip {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 6px 14px;
                    background: #e0f2fe;
                    color: #0369a1;
                    border-radius: 999px;
                    font-weight: 700;
                    font-size: 0.9rem;
                }
                @media (prefers-reduced-motion: reduce) {
                    * {
                        animation-delay: 0s !important;
                        animation-duration: 0s !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0s !important;
                        scroll-behavior: auto !important;
                    }
                    .confetti-particle {
                        display: none !important;
                    }
                    .game-flashcard {
                        transition: none !important;
                    }
                    .shake-animation {
                        animation: none !important;
                    }
                }

                /* Widescreen & Maximize Space Optimization */
                .main-content.learner-quest-page {
                    padding: 1.25rem 2rem !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                }
                .game-player-shell {
                    max-width: 1240px !important;
                    width: 100% !important;
                    margin: 0 auto !important;
                    padding-bottom: 2rem !important;
                }
                .game-mission-header {
                    padding: 16px 24px !important;
                    border-radius: 14px !important;
                    margin-bottom: 12px !important;
                }
                .game-guide-card {
                    padding: 12px 18px !important;
                    margin-bottom: 12px !important;
                    border-radius: 12px !important;
                }
                .game-progress-panel {
                    padding: 12px 18px !important;
                    margin-bottom: 12px !important;
                    border-radius: 12px !important;
                }
                .game-question-card {
                    padding: 24px 28px !important;
                    border-radius: 14px !important;
                    margin-bottom: 14px !important;
                }
                .question-title-text {
                    font-size: 1.25rem !important;
                    line-height: 1.4 !important;
                    margin-bottom: 14px !important;
                }
                .answer-card-grid {
                    display: grid !important;
                    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) !important;
                    gap: 14px !important;
                    width: 100% !important;
                    margin: 14px 0 !important;
                }
                @media (min-width: 860px) {
                    .answer-card-grid {
                        grid-template-columns: repeat(2, 1fr) !important;
                    }
                }
                @media (max-width: 640px) {
                    .answer-card-grid {
                        grid-template-columns: 1fr !important;
                    }
                }
                .game-answer-card, .mc-option-btn, .tf-option-btn {
                    min-height: 52px !important;
                    padding: 12px 18px !important;
                    border-radius: 10px !important;
                    border: 2px solid #e2e8f0 !important;
                    background: #ffffff !important;
                    font-size: 0.95rem !important;
                    font-weight: 600 !important;
                    display: flex !important;
                    align-items: center !important;
                    gap: 12px !important;
                    text-align: left !important;
                    cursor: pointer !important;
                    transition: all 0.18s ease !important;
                }
                .game-navigation .btn,
                .confirm-answer-btn {
                    border-radius: 8px !important;
                    font-size: 0.875rem !important;
                    font-weight: 600 !important;
                    padding: 9px 20px !important;
                }
                #nextChallengeBtn:disabled,
                .next-btn-locked {
                    opacity: 0.45 !important;
                    cursor: not-allowed !important;
                    pointer-events: none !important;
                    filter: grayscale(35%) !important;
                }
            </style>

            <section class="game-progress-panel" aria-live="polite">
                <div>
                    <span id="challengeCounter">Question 1 of <?php echo $questionTotal; ?></span>
                    <strong id="challengeMood">Choose your answer</strong>
                </div>
                <div class="game-progress-track" aria-hidden="true">
                    <div id="gameProgressFill" style="width:0%;"></div>
                </div>
            </section>

            <section class="game-question-stage" id="questionStage">
                <?php if ($actType === 'multiple_choice'): ?>
                    <?php foreach (($actData['questions'] ?? []) as $qi => $q): ?>
                        <article class="game-question-card" data-step="<?php echo $qi; ?>" data-required="choice">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.85rem;">
                                    <i class="ph-bold ph-star me-1 text-warning"></i> Tanong <?php echo $qi + 1; ?>
                                </span>
                            </div>
                            <h2 class="question-title-text mb-3" style="font-size: 1.35rem; line-height: 1.4;"><?php echo htmlspecialchars($q['text'] ?? $q['question'] ?? 'Question'); ?></h2>
                            <?php if (!empty($q['image'])): ?>
                                <div class="text-center my-3">
                                    <img src="<?php echo htmlspecialchars($q['image']); ?>" class="img-fluid rounded border shadow-sm" alt="Question Image" style="max-height: 240px; max-width: 100%; object-fit: contain; background: #fff;">
                                </div>
                            <?php endif; ?>
                            <div class="answer-card-grid mc-card-grid">
                                <?php foreach (($q['options'] ?? []) as $oi => $opt): ?>
                                    <?php $optImg = $getOptionImage($opt); ?>
                                    <button type="button" class="game-answer-card mc-option-btn" data-answer="<?php echo $oi; ?>" data-step-index="<?php echo $qi; ?>" onclick="highlightGameChoice(this, <?php echo $qi; ?>, <?php echo $oi; ?>)">
                                        <span class="answer-letter"><?php echo chr(65 + $oi); ?></span>
                                        <div class="mc-option-content d-flex flex-column align-items-start gap-1 flex-grow-1">
                                            <?php if (!empty($optImg)): ?>
                                                <img src="<?php echo htmlspecialchars($optImg); ?>" class="mc-option-image rounded border mb-1" alt="Option <?php echo chr(65 + $oi); ?>" style="max-height: 100px; max-width: 100%; object-fit: contain; background: #fff;">
                                            <?php endif; ?>
                                            <?php if ($getOptionText($opt) !== ''): ?>
                                                <span class="mc-option-text fw-medium"><?php echo htmlspecialchars($getOptionText($opt)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="confirm-btn-wrap mt-4 text-center">
                                <button type="button" class="quest-primary-btn confirm-answer-btn" id="mcConfirmBtn_<?php echo $qi; ?>" disabled onclick="confirmMcChoice(<?php echo $qi; ?>)">
                                    <i class="ph-bold ph-check-circle me-1"></i> Confirm Answer
                                </button>
                            </div>
                            <div class="answer-feedback" aria-live="polite"></div>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'true_false'): ?>
                    <?php foreach ($tfItems as $ti => $tfItem): ?>
                    <article class="game-question-card" data-step="<?php echo $ti; ?>" data-required="choice">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.85rem;">
                                <i class="ph-bold ph-check-square me-1"></i> Tama o Mali? (<?php echo $ti + 1; ?>)
                            </span>
                        </div>
                        <h2 class="question-title-text mb-3" style="font-size: 1.35rem; line-height: 1.4;"><?php echo htmlspecialchars($tfItem['statement'] ?? 'Basahin ang pangungusap.'); ?></h2>
                        <?php if (!empty($tfItem['image'])): ?>
                            <div class="text-center my-3">
                                <img src="<?php echo htmlspecialchars($tfItem['image']); ?>" class="img-fluid rounded border shadow-sm" alt="Statement Image" style="max-height: 240px; max-width: 100%; object-fit: contain; background: #fff;">
                            </div>
                        <?php endif; ?>
                        <div class="answer-card-grid two tf-card-grid">
                            <button type="button" class="game-answer-card tf-option-btn true-card" data-answer="true" onclick="highlightTrueFalse(this, 'true', <?php echo $ti; ?>)">
                                <span class="answer-letter text-success"><i class="ph-bold ph-check"></i></span><span>Tama</span>
                            </button>
                            <button type="button" class="game-answer-card tf-option-btn false-card" data-answer="false" onclick="highlightTrueFalse(this, 'false', <?php echo $ti; ?>)">
                                <span class="answer-letter text-danger"><i class="ph-bold ph-x"></i></span><span>Mali</span>
                            </button>
                        </div>
                        <div class="confirm-btn-wrap mt-4 text-center">
                            <button type="button" class="quest-primary-btn confirm-answer-btn" id="tfConfirmBtn_<?php echo $ti; ?>" disabled onclick="confirmTfChoice(<?php echo $ti; ?>)">
                                <i class="ph-bold ph-check-circle me-1"></i> Confirm Answer
                            </button>
                        </div>
                        <?php if (!empty($actData['explanation'])): ?>
                            <div class="did-you-know-explanation mt-3 p-3 bg-light rounded text-start" style="display:none;" id="tfExplanation_<?php echo $ti; ?>">
                                <strong><i class="ph-bold ph-lightbulb text-warning me-1"></i> Alam mo ba?</strong>
                                <p class="mb-0 mt-1"><?php echo htmlspecialchars($actData['explanation']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="answer-feedback" aria-live="polite"></div>
                    </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'fill_in_blanks'): ?>
                    <?php 
                    $mode = $actData['answer_mode'] ?? 'word_bank';
                    foreach ($fillItems as $si => $sentence): 
                        $correctWord = (string)($sentence['answers'][0] ?? '');
                        $pillPool = array_merge([$correctWord], (array)($actData['distractors'] ?? []));
                        $pillPool = array_unique(array_filter(array_map('trim', $pillPool)));
                        shuffle($pillPool);
                    ?>
                        <article class="game-question-card" data-step="<?php echo $si; ?>" data-required="text" data-mode="<?php echo $mode; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.85rem;">
                                    <i class="ph-bold ph-pencil-simple me-1"></i> Punan ang Patlang (<?php echo $si + 1; ?>)
                                </span>
                            </div>
                            <h2 class="question-title-text"><?php echo htmlspecialchars(str_replace('___', '_____', $sentence['text'] ?? 'Complete the sentence.')); ?></h2>
                            
                            <?php if ($mode === 'free_type'): ?>
                                <label class="game-input-label" for="fib<?php echo $si; ?>"><?php echo htmlspecialchars($sentence['blank_label'] ?? 'Your answer'); ?></label>
                                <input id="fib<?php echo $si; ?>" class="game-text-input fib-input free-type-input" data-si="<?php echo $si; ?>" type="text" placeholder="Type your answer" oninput="checkSpellingHint(this, <?php echo $si; ?>)">
                                <div class="spelling-hint mt-2 text-warning fw-semibold" id="spellingHint_<?php echo $si; ?>" style="display:none;"><i class="bi bi-exclamation-triangle"></i> Close! Check spelling.</div>
                            <?php else: ?>
                                <label class="game-input-label">Word Pool (Drag or click a slot then click a word):</label>
                                <div class="fib-blank-slot-container mb-3">
                                    <div class="fib-blank-slot border border-2 border-dashed rounded p-3 text-center text-muted" id="fibSlot_<?php echo $si; ?>" data-si="<?php echo $si; ?>" ondragover="event.preventDefault(); this.classList.add('glow');" ondragleave="this.classList.remove('glow');" ondrop="dropWordPill(event, <?php echo $si; ?>)" onclick="highlightFibSlot(this)" style="min-height: 50px; cursor: pointer;">
                                        Drag word here or tap to highlight slot
                                    </div>
                                    <input type="hidden" id="fib<?php echo $si; ?>" class="fib-input word-bank-input" data-si="<?php echo $si; ?>">
                                </div>
                                <div class="fib-word-pool d-flex flex-wrap gap-2 mb-3" id="fibPool_<?php echo $si; ?>">
                                    <?php foreach ($pillPool as $word): ?>
                                        <div class="badge rounded-pill bg-light text-dark border p-3 word-pill-item" draggable="true" ondragstart="dragWordPill(event, '<?php echo htmlspecialchars($word, ENT_QUOTES); ?>')" onclick="clickWordPill('<?php echo htmlspecialchars($word, ENT_QUOTES); ?>', <?php echo $si; ?>)" style="cursor: grab; min-height: 44px; display: inline-flex; align-items: center; font-size: 0.95rem; user-select: none;">
                                            <?php echo htmlspecialchars($word); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <button type="button" class="quest-primary-btn check-answer-btn mt-2" onclick="checkFibAnswer(<?php echo $si; ?>)">
                                <i class="bi bi-check2-circle"></i> Check Answer
                            </button>
                            <div class="answer-feedback" aria-live="polite"></div>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'matching'): ?>
                    <?php foreach ($matchingSets as $setIndex => $set): ?>
                        <?php 
                        $pairs = $set['pairs'] ?? []; 
                        $rightItems = [];
                        foreach ($pairs as $pair) {
                            $rightItems[] = [
                                'text' => (string)($pair['right'] ?? ''),
                                'image' => (string)($pair['right_image'] ?? '')
                            ];
                        }
                        shuffle($rightItems);
                        ?>
                        <article class="game-question-card" data-step="<?php echo $setIndex; ?>" data-required="select" id="matchingCard_<?php echo $setIndex; ?>">
                            <div class="question-pill">Matching Mission</div>
                            <h2><?php echo htmlspecialchars($set['title'] ?? 'Matching Set'); ?></h2>
                            <p class="question-hint">Tap a card on the left, then tap its match on the right.</p>
                            
                            <!-- Hidden selects for screen readers/keyboards -->
                            <div class="matching-hidden-selects" style="position: absolute; opacity: 0; pointer-events: none; height: 1px; width: 1px; overflow: hidden;">
                                <?php foreach ($pairs as $pi => $pair): ?>
                                    <label for="match<?php echo $setIndex; ?>_<?php echo $pi; ?>"><?php echo htmlspecialchars($pair['left'] ?? 'Match item'); ?></label>
                                    <select id="match<?php echo $setIndex; ?>_<?php echo $pi; ?>" class="game-select-input matching-select" data-pi="<?php echo $setIndex; ?>_<?php echo $pi; ?>" onchange="syncMatchingDropdownToUI(<?php echo $setIndex; ?>)">
                                        <option value="">Choose a match</option>
                                        <?php foreach ($rightItems as $rItem): ?>
                                            <option value="<?php echo htmlspecialchars($rItem['text']); ?>"><?php echo htmlspecialchars($rItem['text']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endforeach; ?>
                            </div>

                            <!-- SVG connection container -->
                            <div class="matching-container position-relative mt-4 mb-4" style="min-height: 350px;">
                                <svg class="matching-svg-lines position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; z-index: 5;" id="matchingSvg_<?php echo $setIndex; ?>"></svg>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <h4 class="text-center small fw-bold text-uppercase text-secondary mb-3">Terms</h4>
                                        <div class="d-flex flex-column gap-3 left-terms-col">
                                            <?php foreach ($pairs as $pi => $pair): 
                                                $leftText = (string)($pair['left'] ?? '');
                                                $leftImg = (string)($pair['left_image'] ?? $pair['image'] ?? '');
                                            ?>
                                                <button type="button" class="btn btn-outline-primary border border-2 text-start p-3 matching-item-card left-card d-flex align-items-center gap-2" id="matchCardL_<?php echo $setIndex; ?>_<?php echo $pi; ?>" data-side="left" data-index="<?php echo $pi; ?>" data-text="<?php echo htmlspecialchars($leftText, ENT_QUOTES); ?>" onclick="clickMatchingCard(this, <?php echo $setIndex; ?>)" style="font-size:0.95rem; font-weight: 500; min-height: 54px; position: relative;">
                                                    <span class="matching-dot-badge position-absolute top-50 translate-middle-y end-0 me-3" style="width: 12px; height: 12px; border-radius: 50%; display: none;"></span>
                                                    <?php if (!empty($leftImg)): ?>
                                                        <img src="<?php echo htmlspecialchars($leftImg); ?>" class="rounded border flex-shrink-0" alt="Term image" style="width: 44px; height: 44px; object-fit: cover; background: #fff;">
                                                    <?php endif; ?>
                                                    <span class="matching-card-text flex-grow-1"><?php echo htmlspecialchars($leftText); ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-center small fw-bold text-uppercase text-secondary mb-3">Definitions</h4>
                                        <div class="d-flex flex-column gap-3 right-defs-col">
                                            <?php foreach ($rightItems as $ri => $rItem): 
                                                $rightText = (string)($rItem['text'] ?? '');
                                                $rightImg = (string)($rItem['image'] ?? '');
                                            ?>
                                                <button type="button" class="btn btn-outline-primary border border-2 text-start p-3 matching-item-card right-card d-flex align-items-center gap-2" id="matchCardR_<?php echo $setIndex; ?>_<?php echo $ri; ?>" data-side="right" data-index="<?php echo $ri; ?>" data-text="<?php echo htmlspecialchars($rightText, ENT_QUOTES); ?>" onclick="clickMatchingCard(this, <?php echo $setIndex; ?>)" style="font-size:0.95rem; font-weight: 500; min-height: 54px; position: relative;">
                                                    <span class="matching-dot-badge position-absolute top-50 translate-middle-y start-0 ms-3" style="width: 12px; height: 12px; border-radius: 50%; display: none;"></span>
                                                    <div class="ps-4 d-flex align-items-center gap-2 flex-grow-1">
                                                        <?php if (!empty($rightImg)): ?>
                                                            <img src="<?php echo htmlspecialchars($rightImg); ?>" class="rounded border flex-shrink-0" alt="Definition image" style="width: 44px; height: 44px; object-fit: cover; background: #fff;">
                                                        <?php endif; ?>
                                                        <span class="matching-card-text"><?php echo htmlspecialchars($rightText); ?></span>
                                                    </div>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="quest-primary-btn check-answer-btn mt-3" id="saveMatchingBtn_<?php echo $setIndex; ?>" disabled onclick="confirmMatchingStep(<?php echo $setIndex; ?>)">
                                <i class="bi bi-check2-circle"></i> Save Answer
                            </button>
                            <div class="answer-feedback" aria-live="polite"></div>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'drag_drop_sort' || $actType === 'sequencing'): ?>
                    <?php $orderSets = $actType === 'drag_drop_sort' ? $sortSets : $sequenceSets; ?>
                    <?php foreach ($orderSets as $setIndex => $set): ?>
                    <?php 
                    $items = $set['items'] ?? $set['steps'] ?? []; 
                    $shuffledItems = [];
                    foreach ($items as $ii => $item) {
                        $shuffledItems[] = [
                            'index' => $ii,
                            'text' => is_array($item) ? ($item['text'] ?? $item['label'] ?? '') : $item,
                            'image' => is_array($item) ? ($item['image'] ?? '') : '',
                        ];
                    }
                    shuffle($shuffledItems);
                    ?>
                    <article class="game-question-card" data-step="<?php echo $setIndex; ?>" data-required="sort" id="orderCard_<?php echo $setIndex; ?>">
                        <div class="question-pill"><?php echo $actType === 'drag_drop_sort' ? 'Sorting Challenge' : 'Sequencing Challenge'; ?></div>
                        <h2><?php echo htmlspecialchars($set['title'] ?? 'Arrange the cards in the correct order.'); ?></h2>
                        <p class="question-hint">Use drag-and-drop, or use the ↑ / ↓ buttons to rearrange. Tab and Arrow keys are also supported.</p>
                        
                        <ul class="game-sort-list drag-list list-unstyled" id="dragList_<?php echo $setIndex; ?>" data-step="<?php echo $setIndex; ?>">
                            <?php foreach ($shuffledItems as $sItem): 
                                $itemText = htmlspecialchars($sItem['text']);
                                $origIdx = $sItem['index'];
                                $itemImg = $sItem['image'];
                            ?>
                                <li class="game-sort-item drag-item border rounded p-3 mb-2 bg-white d-flex align-items-center justify-content-between" draggable="true" data-index="<?php echo $origIdx; ?>" tabindex="0" onkeydown="handleSortKeyboard(event, this, <?php echo $setIndex; ?>)">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="drag-handle-icon" style="cursor: grab;"><i class="ph-bold ph-dots-six-vertical text-muted"></i></span>
                                        <?php if (!empty($itemImg)): ?>
                                            <img src="<?php echo htmlspecialchars($itemImg); ?>" class="rounded border flex-shrink-0" alt="Step item" style="width: 44px; height: 44px; object-fit: cover; background: #fff;">
                                        <?php endif; ?>
                                        <span class="item-text-label"><?php echo $itemText; ?></span>
                                    </div>
                                    <div class="accessible-sort-buttons d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-light border up-btn" onclick="moveItemUp(this, <?php echo $setIndex; ?>)" title="Move up"><i class="bi bi-arrow-up"></i></button>
                                        <button type="button" class="btn btn-sm btn-light border down-btn" onclick="moveItemDown(this, <?php echo $setIndex; ?>)" title="Move down"><i class="bi bi-arrow-down"></i></button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <button type="button" class="quest-primary-btn check-answer-btn mt-3" onclick="confirmOrderStep(<?php echo $setIndex; ?>)">
                            <i class="bi bi-check2-circle"></i> Save Order
                        </button>
                        
                        <?php if ($actType === 'sequencing'): ?>
                            <!-- Timeline view for Sequencing -->
                            <div class="sequencing-timeline-view mt-4 text-start" id="timelineView_<?php echo $setIndex; ?>" style="display:none;">
                                <h4 class="small fw-bold text-uppercase text-secondary mb-3">Timeline Journey</h4>
                                <div class="timeline-steps-flow d-flex flex-row overflow-auto pb-2 gap-3" style="border-left: none;">
                                    <!-- Populated dynamically via JS -->
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="answer-feedback" aria-live="polite"></div>
                    </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'image_label'): ?>
                    <?php 
                    $imgPath = $actData['image_path'] ?? ''; 
                    $labels = $actData['labels'] ?? $actData['markers'] ?? [];
                    // Generate pill pool of correct answers and shuffle
                    $pillPool = array_column($labels, 'answer');
                    shuffle($pillPool);
                    ?>
                    <article class="game-question-card" data-step="0" data-required="labels" id="imageLabelCard">
                        <div class="question-pill">Image Label Mission</div>
                        <h2>Label the numbered parts.</h2>
                        
                        <?php $imgFullPath = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imgPath); ?>
                        <?php if ($imgPath && file_exists($imgFullPath)): ?>
                            <p class="question-hint">Drag a label pill and drop it onto a numbered pin, or click a pin first then click a pill below.</p>
                            
                            <div class="game-image-label position-relative overflow-hidden mb-4" style="max-width: 600px; margin: 0 auto;">
                                <img src="<?php echo htmlspecialchars($basePath . '/' . ltrim($imgPath, '/')); ?>" class="img-fluid rounded" alt="Activity image to label" id="imageLabelMainImg" style="width: 100%;">
                                <?php foreach ($labels as $li => $lbl): ?>
                                    <div class="label-dot-pin position-absolute border border-2 border-white bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         id="labelPin_<?php echo $li; ?>" 
                                         data-index="<?php echo $li; ?>"
                                         ondragover="event.preventDefault(); this.classList.add('glow');" 
                                         ondragleave="this.classList.remove('glow');" 
                                         ondrop="dropLabelPill(event, <?php echo $li; ?>)"
                                         onclick="clickLabelPin(this)"
                                         style="left:<?php echo (float)($lbl['x'] ?? 0); ?>%; top:<?php echo (float)($lbl['y'] ?? 0); ?>%; width: 34px; height: 34px; transform: translate(-50%, -50%); cursor: pointer; z-index: 10;">
                                        <span class="pin-number-label"><?php echo $li + 1; ?></span>
                                        <span class="assigned-text-label position-absolute bg-primary text-white border border-2 border-white rounded px-2 py-1 small" style="display:none; white-space:nowrap; top: 110%; left: 50%; transform: translateX(-50%); font-size: 0.75rem; z-index: 12;"></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="label-inputs-hidden" style="position: absolute; opacity: 0; pointer-events: none; height: 1px; width: 1px; overflow: hidden;">
                                <?php foreach ($labels as $li => $lbl): ?>
                                    <input id="label<?php echo $li; ?>" class="label-input" data-li="<?php echo $li; ?>" type="text">
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="label-pills-pool mt-3 text-center">
                                <label class="game-input-label d-block text-start mb-2">Available Labels:</label>
                                <div class="d-flex flex-wrap gap-2 justify-content-center" id="imageLabelPillsPool">
                                    <?php foreach ($pillPool as $word): ?>
                                        <div class="badge rounded-pill bg-light text-dark border p-3 image-label-pill" draggable="true" ondragstart="dragLabelPill(event, '<?php echo htmlspecialchars($word, ENT_QUOTES); ?>')" onclick="clickLabelPill('<?php echo htmlspecialchars($word, ENT_QUOTES); ?>')" style="cursor: grab; min-height: 44px; display: inline-flex; align-items: center; font-size: 0.9rem; user-select: none;">
                                            <?php echo htmlspecialchars($word); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- Fallback mode (Matching layout) -->
                            <div class="alert alert-warning py-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Image unavailable</strong> — complete the matching activity below.
                            </div>
                            <?php if (!empty($actData['description'])): ?>
                                <div class="bg-light p-3 border rounded mb-3 text-start">
                                    <strong>Accessible Description:</strong>
                                    <p class="mb-0 mt-1"><?php echo htmlspecialchars($actData['description']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="matching-container position-relative mb-4 mt-4" style="min-height: 250px;">
                                <div class="row">
                                    <div class="col-6">
                                        <h4 class="text-center small fw-bold text-secondary mb-3">Pins</h4>
                                        <div class="d-flex flex-column gap-3">
                                            <?php foreach ($labels as $li => $lbl): ?>
                                                <button type="button" class="btn btn-outline-primary border border-2 text-start p-3 matching-item-card left-card" id="labelCardL_<?php echo $li; ?>" data-side="left" data-index="<?php echo $li; ?>" onclick="clickLabelFallbackCard(this)" style="font-size:0.95rem; font-weight: 500; min-height: 54px; position: relative;">
                                                    <span class="matching-dot-badge position-absolute top-50 translate-middle-y end-0 me-3" style="width: 12px; height: 12px; border-radius: 50%; display: none;"></span>
                                                    Pin <?php echo $li + 1; ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-center small fw-bold text-secondary mb-3">Labels</h4>
                                        <div class="d-flex flex-column gap-3">
                                            <?php foreach ($pillPool as $ri => $word): ?>
                                                <button type="button" class="btn btn-outline-primary border border-2 text-start p-3 matching-item-card right-card" id="labelCardR_<?php echo $ri; ?>" data-side="right" data-index="<?php echo $ri; ?>" data-text="<?php echo htmlspecialchars($word, ENT_QUOTES); ?>" onclick="clickLabelFallbackCard(this)" style="font-size:0.95rem; font-weight: 500; min-height: 54px; position: relative;">
                                                    <span class="matching-dot-badge position-absolute top-50 translate-middle-y start-0 ms-3" style="width: 12px; height: 12px; border-radius: 50%; display: none;"></span>
                                                    <span class="ps-4 d-inline-block"><?php echo htmlspecialchars($word); ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="label-inputs-hidden" style="position: absolute; opacity: 0; pointer-events: none; height: 1px; width: 1px; overflow: hidden;">
                                <?php foreach ($labels as $li => $lbl): ?>
                                    <input id="label<?php echo $li; ?>" class="label-input" data-li="<?php echo $li; ?>" type="text">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <button type="button" class="quest-primary-btn check-answer-btn mt-3" onclick="confirmLabelsStep()">
                            <i class="bi bi-check2-circle"></i> Save Labels
                        </button>
                        <div class="answer-feedback" aria-live="polite"></div>
                    </article>
                <?php elseif ($actType === 'flashcards'): ?>
                    <!-- Reduce Motion toggle in Flashcard Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-muted" id="flashcardDeckProgress">Card 1 of <?php echo $questionTotal; ?></span>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flashcardReduceMotionToggle" onchange="toggleFlashcardMotion(this)">
                            <label class="form-check-label small text-secondary" for="flashcardReduceMotionToggle">Reduce Motion</label>
                        </div>
                    </div>

                    <?php $flashStep = 0; ?>
                    <?php foreach ($flashcardSets as $setIndex => $set): ?>
                        <?php foreach (($set['cards'] ?? []) as $ci => $card): 
                            $fImg = $card['front_image'] ?? $card['image'] ?? '';
                            $bImg = $card['back_image'] ?? '';
                        ?>
                        <article class="game-question-card" data-step="<?php echo $flashStep; ?>" data-required="view" id="flashcardArticle_<?php echo $flashStep; ?>">
                            <div class="question-pill">Flashcard Mission</div>
                            <h2><?php echo htmlspecialchars($set['title'] ?? ('Flashcard Set ' . ($setIndex + 1))); ?></h2>
                            
                            <div class="game-flashcard-shell position-relative mx-auto mb-3" style="max-width: 480px; min-height: 280px; perspective: 1000px;">
                                <div class="game-flashcard w-100 h-100 position-relative text-center border border-2 rounded p-4 d-flex align-items-center justify-content-center bg-white shadow-sm" onclick="flipFlashcard(this, <?php echo $flashStep; ?>)" style="cursor: pointer; min-height: 280px; transition: transform 0.6s; transform-style: preserve-3d; user-select: none;">
                                    <div class="front position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" style="backface-visibility: hidden; top: 0; left: 0;">
                                        <?php if (!empty($fImg)): ?>
                                            <img src="<?php echo htmlspecialchars($fImg); ?>" class="img-fluid rounded mb-2" alt="Flashcard front" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                        <?php endif; ?>
                                        <?php if (!empty($card['front'])): ?>
                                            <span class="fs-4 fw-bold"><?php echo htmlspecialchars($card['front']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="back position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" style="backface-visibility: hidden; transform: rotateY(180deg); top: 0; left: 0;">
                                        <?php if (!empty($bImg)): ?>
                                            <img src="<?php echo htmlspecialchars($bImg); ?>" class="img-fluid rounded mb-2" alt="Flashcard back" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                        <?php endif; ?>
                                        <?php if (!empty($card['back'])): ?>
                                            <span class="fs-4 fw-bold text-primary"><?php echo htmlspecialchars($card['back']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="question-hint text-center mb-3" id="flashcardHint_<?php echo $flashStep; ?>">Click the card to flip it and reveal the back.</p>
                            
                            <!-- Self-assessment loops -->
                            <div class="flashcard-self-assess-buttons d-flex justify-content-center gap-3" id="flashcardAssess_<?php echo $flashStep; ?>" style="display: none !important;">
                                <button type="button" class="btn btn-warning btn-lg px-4 text-white d-flex align-items-center gap-2" onclick="assessFlashcard(<?php echo $flashStep; ?>, 1)">
                                    <i class="bi bi-arrow-repeat"></i> Need to review
                                </button>
                                <button type="button" class="btn btn-success btn-lg px-4 text-white d-flex align-items-center gap-2" onclick="assessFlashcard(<?php echo $flashStep; ?>, 2)">
                                    <i class="bi bi-check-lg"></i> I got it!
                                </button>
                            </div>
                        </article>
                        <?php $flashStep++; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="game-question-card" data-step="0" data-required="view">
                        <div class="question-pill">Activity</div>
                        <h2>This activity type is ready for teacher review after submission.</h2>
                        <button type="button" class="quest-primary-btn check-answer-btn" onclick="saveCurrentStepAnswer()">
                            <i class="bi bi-check2-circle"></i> Continue
                        </button>
                        <div class="answer-feedback" aria-live="polite"></div>
                    </article>
                <?php endif; ?>

                <article class="game-question-card final-screen" data-step="<?php echo $questionTotal; ?>" data-required="final">
                    <div class="complete-badge text-warning"><i class="ph-bold ph-star fs-1"></i></div>
                    <div class="quest-eyebrow text-primary fw-bold small text-uppercase">Mission Review</div>
                    <h2 class="fw-bold text-dark mt-1">Ready to submit?</h2>
                    <p class="text-secondary">You answered <strong id="answeredCount">0</strong> of <strong><?php echo $questionTotal; ?></strong> challenges.</p>
                    <p id="frontendScorePreview" class="frontend-score-preview"></p>
                    <div id="missionReviewList" class="mission-review-list"></div>
                    <div class="stars-earned d-flex justify-content-center gap-2 fs-3 my-3">
                        <i class="ph-bold ph-star text-warning"></i><i class="ph-bold ph-star text-warning"></i><i class="ph-bold ph-star text-warning"></i>
                    </div>
                    <div class="game-actions center d-flex justify-content-center gap-2 mt-4 flex-wrap">
                        <button type="button" class="btn btn-bubble px-4 py-2" id="submitBtn" onclick="submitAnswers()">
                            <i class="ph-bold ph-paper-plane-tilt me-1"></i> <span>Ipasa ang Gawain</span>
                        </button>
                        <a class="btn btn-bubble-outline px-4 py-2" href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>">
                            <i class="ph-bold ph-arrow-left me-1"></i> <span>Bumalik sa Aralin</span>
                        </a>
                    </div>
                </article>
            </section>

            <nav class="game-navigation d-flex justify-content-between align-items-center mt-3" aria-label="Challenge navigation">
                <button type="button" class="btn btn-bubble-outline px-4 py-2" id="backChallengeBtn" onclick="goChallenge(-1)">
                    <i class="ph-bold ph-arrow-left me-1"></i> <span>Nakaraan</span>
                </button>
                <button type="button" class="btn btn-bubble px-4 py-2" id="nextChallengeBtn" onclick="goChallenge(1)">
                    <span>Susunod</span> <i class="ph-bold ph-arrow-right ms-1"></i>
                </button>
            </nav>

            <div id="resultArea" class="game-result-area" style="display:none;"></div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const BASE = <?php echo json_encode($basePath); ?>;
const ACTIVITY_ID = <?php echo $actId; ?>;
const ACTIVITY_TYPE = <?php echo json_encode($actType); ?>;
const LESSON_PLAN_ID = <?php echo $lessonPlanId; ?>;
const TOTAL_STEPS = <?php echo $questionTotal; ?>;
const FEEDBACK_DATA = <?php echo json_encode($feedbackData, JSON_UNESCAPED_SLASHES); ?>;

let currentStep = 0;
const mcAnswers = {};
const tfAnswers = {};
const checkedSteps = {};
const frontendResults = {};
const attemptCounts = {};
const AUTO_ADVANCE_MS = 1800;

function getItemAccuracy(attempts) {
    if (!attempts || attempts <= 1) return 100;
    if (attempts === 2) return 75;
    if (attempts === 3) return 50;
    return 25;
}

function calculateOverallAccuracy() {
    let totalAcc = 0;
    for (let step = 0; step < TOTAL_STEPS; step++) {
        const attempts = attemptCounts[step] || 1;
        totalAcc += getItemAccuracy(attempts);
    }
    return Math.round(totalAcc / Math.max(1, TOTAL_STEPS));
}

function triggerShakeCard(card) {
    if (!card || isMotionReduced) return;
    card.classList.remove('shake-animation');
    void card.offsetWidth;
    card.classList.add('shake-animation');
    setTimeout(() => card.classList.remove('shake-animation'), 500);
}

// Programmatic Web Audio Tone Synthesizer
function playAudioTone(isCorrect) {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const now = ctx.currentTime;
        if (isCorrect) {
            const osc1 = ctx.createOscillator();
            const osc2 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            const gain2 = ctx.createGain();
            
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(523.25, now);
            gain1.gain.setValueAtTime(0.1, now);
            gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
            
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(659.25, now + 0.1);
            gain2.gain.setValueAtTime(0.1, now + 0.1);
            gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
            
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            
            osc1.start(now);
            osc1.stop(now + 0.3);
            osc2.start(now + 0.1);
            osc2.stop(now + 0.4);
        } else {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(130, now);
            osc.frequency.exponentialRampToValueAtTime(80, now + 0.25);
            gain.gain.setValueAtTime(0.15, now);
            gain.gain.linearRampToValueAtTime(0.01, now + 0.25);
            
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.25);
        }
    } catch (e) {
        console.warn("Audio Context playback failed: ", e);
    }
}

// Levenshtein helper for spelling hint
function getLevenshteinDistance(a, b) {
    const matrix = [];
    for (let i = 0; i <= b.length; i++) matrix[i] = [i];
    for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            if (b.charAt(i - 1) === a.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                );
            }
        }
    }
    return matrix[b.length][a.length];
}

const matchingConnections = {}; 
let selectedLeftCard = null;
const matchingColors = ['#a01422', '#1e4072', '#008080', '#FFBF00', '#800080', '#FF7F50'];

let selectedLabelPin = null;
const imageLabelAnswers = {}; 
const fallbackConnections = {};
let selectedFallbackLeftCard = null;

const flashcardOrder = []; 
const flashcardConfidence = {}; 
const flashcardPasses = {}; 
let activeFlashcardIndex = 0; 
let isMotionReduced = localStorage.getItem('reduceMotion') === 'true';

function getCardForStep(step) {
    return document.querySelector(`.game-question-card[data-step="${step}"]`);
}

function getStepCards() {
    return Array.from(document.querySelectorAll('.game-question-card'));
}

function countAnswered() {
    return Object.keys(checkedSteps).length;
}

function isStepAnswered(step) {
    if (checkedSteps[step]) return true;

    if (ACTIVITY_TYPE === 'multiple_choice') {
        return mcSelectedAnswers[step] !== undefined || mcAnswers[step] !== undefined;
    }
    
    if (ACTIVITY_TYPE === 'true_false') {
        return tfSelectedAnswers[step] !== undefined || tfAnswers[step] !== undefined;
    }
    
    if (ACTIVITY_TYPE === 'fill_in_blanks') {
        const card = getCardForStep(step);
        if (!card) return false;
        const input = card.querySelector('.fib-input');
        if (input && input.value.trim().length > 0) return true;
        const slot = card.querySelector('.fib-blank-slot');
        if (slot && !slot.classList.contains('text-muted') && slot.textContent.trim().length > 0) return true;
        return false;
    }
    
    if (ACTIVITY_TYPE === 'matching') {
        const card = document.getElementById('matchingCard_' + step) || getCardForStep(step);
        if (!card) return false;
        const leftCards = card.querySelectorAll('.left-card');
        if (leftCards.length === 0) return true;
        const conns = matchingConnections[step] || {};
        return Object.keys(conns).length >= leftCards.length;
    }
    
    if (ACTIVITY_TYPE === 'image_label') {
        const totalLabels = document.querySelectorAll('.label-dot-pin, .game-label-pin').length;
        if (totalLabels === 0) return true;
        return Object.keys(imageLabelAnswers).length >= totalLabels;
    }

    return true;
}

function goChallenge(offset) {
    if (offset > 0) {
        if (currentStep < TOTAL_STEPS && !isStepAnswered(currentStep)) {
            const card = getCardForStep(currentStep);
            if (card) triggerShakeCard(card);
            Swal.fire({
                icon: 'info',
                title: 'Sagutin muna ang tanong',
                text: 'Pumili o maglagay muna ng sagot bago magpatuloy sa susunod.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'Sige'
            });
            return;
        }

        // Auto-check on Next if answered but not yet checked
        if (currentStep < TOTAL_STEPS && !checkedSteps[currentStep]) {
            if (ACTIVITY_TYPE === 'multiple_choice' && mcSelectedAnswers[currentStep] !== undefined) {
                confirmMcChoice(currentStep);
                if (!checkedSteps[currentStep]) {
                    return; // Incorrect answer; allow learner to retry on this question
                }
            } else if (ACTIVITY_TYPE === 'true_false' && tfSelectedAnswers[currentStep] !== undefined) {
                confirmTfChoice(currentStep);
                if (!checkedSteps[currentStep]) {
                    return;
                }
            } else if (ACTIVITY_TYPE === 'fill_in_blanks') {
                checkTextAnswer(currentStep);
                if (!checkedSteps[currentStep]) {
                    return;
                }
            }
        }
    }

    currentStep = Math.max(0, Math.min(TOTAL_STEPS, currentStep + offset));
    updateGameStage();
}

function updateGameStage() {
    const cards = getStepCards();
    cards.forEach(function(card) {
        card.classList.toggle('active', parseInt(card.dataset.step, 10) === currentStep);
    });

    const isFinal = currentStep >= TOTAL_STEPS;
    const counter = document.getElementById('challengeCounter');
    const mood = document.getElementById('challengeMood');
    const fill = document.getElementById('gameProgressFill');
    const backBtn = document.getElementById('backChallengeBtn');
    const nextBtn = document.getElementById('nextChallengeBtn');
    const answered = document.getElementById('answeredCount');

    if (counter) counter.textContent = isFinal ? 'Panghuling Pagsusuri' : 'Tanong ' + (currentStep + 1) + ' ng ' + TOTAL_STEPS;
    if (mood) mood.textContent = isFinal ? 'Magaling! Tapos na ang mga tanong!' : getFriendlyPrompt();
    if (fill) fill.style.width = Math.min(100, Math.round((countAnswered() / TOTAL_STEPS) * 100)) + '%';
    if (backBtn) backBtn.disabled = currentStep === 0;
    if (nextBtn) {
        if (isFinal) {
            nextBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'inline-flex';
            const answeredState = isStepAnswered(currentStep);
            nextBtn.disabled = !answeredState;
            nextBtn.classList.toggle('next-btn-locked', !answeredState);
            nextBtn.title = answeredState ? 'Pumunta sa susunod na tanong' : 'Pumili muna ng sagot para makapagpatuloy';
        }
    }
    if (answered) answered.textContent = countAnswered();
    if (isFinal) buildMissionReview();
    
    if (ACTIVITY_TYPE === 'matching' && !isFinal) {
        setTimeout(() => drawMatchingLines(currentStep), 100);
    }
    
    if (ACTIVITY_TYPE === 'flashcards' && !isFinal) {
        const currentCardIdx = flashcardOrder[activeFlashcardIndex];
        cards.forEach((card, idx) => {
            card.classList.toggle('active', idx === currentCardIdx);
        });
        const progressText = document.getElementById('flashcardDeckProgress');
        if (progressText) {
            progressText.textContent = "Card " + (TOTAL_STEPS - flashcardOrder.length + 1) + " of " + TOTAL_STEPS + " — " + (flashcardOrder.length) + " remaining for review";
        }
    }
}

function getFriendlyPrompt() {
    const prompts = ['Piliin ang iyong sagot', 'Magaling!', 'Ipagpatuloy!', 'Malapit na matapos!'];
    return prompts[Math.min(currentStep, prompts.length - 1)];
}

function labelCard(answerCard, text, iconClass) {
    let badge = answerCard.querySelector('.status-badge');
    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'status-badge ms-2 badge rounded-pill';
        answerCard.appendChild(badge);
    }
    badge.innerHTML = `<i class="bi ${iconClass}"></i> ${text}`;
    if (iconClass.includes('check')) {
        badge.className = 'status-badge ms-2 badge bg-success text-white';
    } else if (iconClass.includes('x')) {
        badge.className = 'status-badge ms-2 badge bg-danger text-white';
    } else {
        badge.className = 'status-badge ms-2 badge bg-secondary text-white';
    }
}

function setFeedback(step, status, title, desc, nextText) {
    const card = getCardForStep(step);
    if (!card) return;
    const feedbackDiv = card.querySelector('.answer-feedback');
    if (!feedbackDiv) return;
    
    let alertClass = 'alert-info';
    let icon = 'bi-info-circle-fill';
    if (status === 'correct') {
        alertClass = 'alert-success';
        icon = 'bi-check-circle-fill';
    } else if (status === 'wrong') {
        alertClass = 'alert-danger';
        icon = 'bi-exclamation-circle-fill';
    }
    
    feedbackDiv.innerHTML = `
        <div class="alert ${alertClass} mt-3 d-flex align-items-center gap-2 text-start">
            <i class="bi ${icon} fs-4"></i>
            <div>
                <strong class="d-block">${title}</strong>
                <span>${desc}</span>
                <em class="d-block small text-muted mt-1">${nextText}</em>
            </div>
        </div>
    `;
}

function lockStep(step) {
    const card = getCardForStep(step);
    if (!card) return;
    card.querySelectorAll('button, input, select, textarea').forEach(el => {
        if (!el.classList.contains('read-aloud-btn') && !el.id.includes('backChallengeBtn')) {
            el.disabled = true;
        }
    });
}

function autoAdvance(step) {
    const nextText = step >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...';
    const feedback = getCardForStep(step)?.querySelector('.answer-feedback em');
    if (feedback) feedback.textContent = nextText;
    window.setTimeout(function() {
        if (currentStep === step) {
            currentStep = Math.min(TOTAL_STEPS, step + 1);
            updateGameStage();
        }
    }, AUTO_ADVANCE_MS);
}

// Activity 1: Multiple Choice
let mcSelectedAnswers = {};
function highlightGameChoice(el, qi, oi) {
    if (checkedSteps[qi]) return;
    const card = getCardForStep(qi);
    
    // Clear previous selection and incorrect markings for retry
    card.querySelectorAll('.mc-option-btn').forEach(btn => {
        btn.classList.remove('selected');
        btn.classList.remove('is-wrong');
        const badge = btn.querySelector('.status-badge');
        if (badge) badge.remove();
    });
    
    el.classList.add('selected');
    mcAnswers[qi] = oi;
    mcSelectedAnswers[qi] = oi;
    
    const confirmBtn = document.getElementById('mcConfirmBtn_' + qi);
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = `<i class="bi bi-check2-circle"></i> Confirm Answer`;
    }

    const nextBtn = document.getElementById('nextChallengeBtn');
    if (nextBtn) {
        nextBtn.disabled = false;
        nextBtn.classList.remove('next-btn-locked');
        nextBtn.title = 'Pumunta sa susunod na tanong';
    }
}

function confirmMcChoice(qi) {
    if (checkedSteps[qi]) return;
    const selectedIndex = mcSelectedAnswers[qi];
    if (selectedIndex === undefined) return;
    checkChoiceStep(qi, selectedIndex);
}

function checkChoiceStep(step, selectedIndex) {
    const card = getCardForStep(step);
    const correct = FEEDBACK_DATA.multiple_choice?.[step]?.correct;
    const selectedCard = card.querySelector('.game-answer-card[data-answer="' + selectedIndex + '"]');
    const selectedText = selectedCard?.querySelector('.mc-option-text')?.textContent.trim() 
        || selectedCard?.querySelector('span:last-child')?.textContent.trim() 
        || String(selectedIndex);

    attemptCounts[step] = (attemptCounts[step] || 0) + 1;
    const currentAttempt = attemptCounts[step];

    if (correct !== null && correct !== undefined) {
        const isCorrect = parseInt(correct, 10) === selectedIndex;
        
        if (isCorrect) {
            checkedSteps[step] = true;
            playAudioTone(true);
            
            // Mark correct
            card.querySelectorAll('.game-answer-card').forEach(c => c.classList.add('locked'));
            if (selectedCard) {
                selectedCard.classList.add('is-correct');
                labelCard(selectedCard, 'Correct!', 'bi-check-circle-fill');
            }
            
            const acc = getItemAccuracy(currentAttempt);
            frontendResults[step] = {
                status: 'correct',
                selected: selectedText,
                attempts: currentAttempt,
                accuracy: acc
            };
            
            const attemptText = currentAttempt === 1 
                ? '⭐ 1st Try! Perfect accuracy (100%)' 
                : `Solved in ${currentAttempt} attempts (${acc}% accuracy)`;
            
            setFeedback(step, 'correct', 'Correct!', attemptText,
                step >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
            
            const confirmBtn = document.getElementById('mcConfirmBtn_' + step);
            if (confirmBtn) confirmBtn.disabled = true;
            
            lockStep(step);
            updateGameStage();
            autoAdvance(step);
        } else {
            // Cisco-style: Wrong answer -> Do NOT reveal correct answer! Allow retry
            playAudioTone(false);
            triggerShakeCard(card);
            
            if (selectedCard) {
                selectedCard.classList.add('is-wrong');
                labelCard(selectedCard, 'Try again', 'bi-x-circle-fill');
            }
            
            setFeedback(step, 'wrong', 'Not quite right', 
                'That answer is incorrect. Pick another choice and try again!', 
                `Attempt ${currentAttempt} • Choose another card to retry`);
            
            const confirmBtn = document.getElementById('mcConfirmBtn_' + step);
            if (confirmBtn) {
                confirmBtn.innerHTML = `<i class="bi bi-arrow-repeat"></i> Try Again`;
            }
        }
    } else {
        checkedSteps[step] = true;
        frontendResults[step] = {status: 'saved', selected: selectedText, attempts: 1, accuracy: 100};
        setFeedback(step, 'saved', 'Answer saved!', 'Your answer was saved for the mission.', step >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
        lockStep(step);
        updateGameStage();
        autoAdvance(step);
    }
}

// Activity 2: True / False
const tfSelectedAnswers = {};
function highlightTrueFalse(el, val, ti) {
    if (checkedSteps[ti]) return;
    const card = getCardForStep(ti);
    
    card.querySelectorAll('.tf-option-btn').forEach(btn => {
        btn.classList.remove('selected');
        btn.classList.remove('is-wrong');
        const badge = btn.querySelector('.status-badge');
        if (badge) badge.remove();
    });
    
    el.classList.add('selected');
    tfAnswers[ti] = val;
    tfSelectedAnswers[ti] = val;
    
    const confirmBtn = document.getElementById('tfConfirmBtn_' + ti);
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = `<i class="bi bi-check2-circle"></i> Confirm Answer`;
    }

    const nextBtn = document.getElementById('nextChallengeBtn');
    if (nextBtn) {
        nextBtn.disabled = false;
        nextBtn.classList.remove('next-btn-locked');
        nextBtn.title = 'Pumunta sa susunod na tanong';
    }
}

function confirmTfChoice(ti) {
    if (checkedSteps[ti]) return;
    const val = tfSelectedAnswers[ti];
    if (val === undefined) return;
    checkTrueFalseStep(val, ti);
}

function checkTrueFalseStep(value, step) {
    const card = getCardForStep(step);
    const correct = FEEDBACK_DATA.true_false?.[step];
    const selectedCard = card.querySelector(`.game-answer-card[data-answer="${value}"]`);

    attemptCounts[step] = (attemptCounts[step] || 0) + 1;
    const currentAttempt = attemptCounts[step];

    if (correct) {
        const isCorrect = String(value).toLowerCase() === correct;
        
        if (isCorrect) {
            checkedSteps[step] = true;
            playAudioTone(true);
            
            card.querySelectorAll('.game-answer-card').forEach(c => c.classList.add('locked'));
            if (selectedCard) {
                selectedCard.classList.add('is-correct');
                labelCard(selectedCard, 'Correct!', 'bi-check-circle-fill');
            }
            
            const exp = document.getElementById('tfExplanation_' + step);
            if (exp) exp.style.display = 'block';
            
            const acc = getItemAccuracy(currentAttempt);
            frontendResults[step] = {
                status: 'correct',
                selected: value,
                attempts: currentAttempt,
                accuracy: acc
            };
            
            const attemptText = currentAttempt === 1 
                ? '⭐ 1st Try! Perfect accuracy (100%)' 
                : `Solved in ${currentAttempt} attempts (${acc}% accuracy)`;
            
            setFeedback(step, 'correct', 'Correct!', attemptText,
                step >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
            
            const confirmBtn = document.getElementById('tfConfirmBtn_' + step);
            if (confirmBtn) confirmBtn.disabled = true;
            
            lockStep(step);
            updateGameStage();
            autoAdvance(step);
        } else {
            // Cisco-style: Wrong answer -> Do NOT reveal correct answer! Allow retry
            playAudioTone(false);
            triggerShakeCard(card);
            
            if (selectedCard) {
                selectedCard.classList.add('is-wrong');
                labelCard(selectedCard, 'Try again', 'bi-x-circle-fill');
            }
            
            setFeedback(step, 'wrong', 'Not quite right', 
                'Incorrect statement assessment. Think carefully and try again!', 
                `Attempt ${currentAttempt} • Select the other option to retry`);
            
            const confirmBtn = document.getElementById('tfConfirmBtn_' + step);
            if (confirmBtn) {
                confirmBtn.innerHTML = `<i class="bi bi-arrow-repeat"></i> Try Again`;
            }
        }
    } else {
        checkedSteps[step] = true;
        frontendResults[step] = {status: 'saved', selected: value, attempts: 1, accuracy: 100};
        setFeedback(step, 'saved', 'Answer saved!', 'Your answer was saved for the mission.', step >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
        lockStep(step);
        updateGameStage();
        autoAdvance(step);
    }
}

// Activity 3: Fill in the Blanks (Word Bank & Free Type)
function dragWordPill(event, word) {
    event.dataTransfer.setData("text/plain", word);
}

function dropWordPill(event, si) {
    event.preventDefault();
    const slot = document.getElementById('fibSlot_' + si);
    if (!slot || checkedSteps[si]) return;
    slot.classList.remove('glow');
    const word = event.dataTransfer.getData("text/plain");
    if (word) {
        placeWordInSlot(word, si);
    }
}

let activeFibSlot = null;
function highlightFibSlot(el) {
    const si = el.dataset.si;
    if (checkedSteps[si]) return;
    document.querySelectorAll('.fib-blank-slot').forEach(s => s.classList.remove('active-slot'));
    if (activeFibSlot === el) {
        activeFibSlot = null;
    } else {
        activeFibSlot = el;
        el.classList.add('active-slot');
    }
}

function clickWordPill(word, si) {
    if (checkedSteps[si]) return;
    if (activeFibSlot) {
        const targetSi = parseInt(activeFibSlot.dataset.si, 10);
        placeWordInSlot(word, targetSi);
        activeFibSlot.classList.remove('active-slot');
        activeFibSlot = null;
    } else {
        placeWordInSlot(word, si);
    }
}

function placeWordInSlot(word, si) {
    const slot = document.getElementById('fibSlot_' + si);
    const hiddenInput = document.getElementById('fib' + si);
    if (!slot || !hiddenInput) return;
    
    const oldWord = hiddenInput.value;
    if (oldWord) {
        returnWordToPool(oldWord, si);
    }
    
    hiddenInput.value = word;
    slot.innerHTML = `<span class="badge rounded-pill bg-primary text-white p-2" style="cursor: pointer; min-height: 38px; display: inline-flex; align-items: center;" onclick="event.stopPropagation(); removeWordFromSlot(${si})">${escapeHtml(word)} <i class="bi bi-x-circle-fill ms-2"></i></span>`;
    slot.classList.remove('text-muted');
    slot.style.borderColor = '';
    slot.style.backgroundColor = '';
    hideWordInPool(word, si);
    updateGameStage();
}

function removeWordFromSlot(si) {
    if (checkedSteps[si]) return;
    const slot = document.getElementById('fibSlot_' + si);
    const hiddenInput = document.getElementById('fib' + si);
    if (!slot || !hiddenInput) return;
    const word = hiddenInput.value;
    if (word) {
        returnWordToPool(word, si);
    }
    hiddenInput.value = '';
    slot.innerHTML = 'Drag word here or tap to highlight slot';
    slot.classList.add('text-muted');
    slot.style.borderColor = '';
    slot.style.backgroundColor = '';
    updateGameStage();
}

function hideWordInPool(word, si) {
    const pool = document.getElementById('fibPool_' + si);
    if (!pool) return;
    const pills = pool.querySelectorAll('.word-pill-item');
    for (let pill of pills) {
        if (pill.textContent.trim() === word && pill.style.display !== 'none') {
            pill.style.display = 'none';
            break;
        }
    }
}

function returnWordToPool(word, si) {
    const pool = document.getElementById('fibPool_' + si);
    if (!pool) return;
    const pills = pool.querySelectorAll('.word-pill-item');
    for (let pill of pills) {
        if (pill.textContent.trim() === word && pill.style.display === 'none') {
            pill.style.display = 'inline-flex';
            break;
        }
    }
}

function checkFibAnswer(si) {
    checkTextAnswer(si);
}

function checkTextAnswer(step) {
    if (checkedSteps[step]) return;
    const card = getCardForStep(step);
    const input = card ? card.querySelector('.fib-input') : null;
    const value = input ? input.value.trim() : '';
    if (!value) {
        Swal.fire({icon:'info',title:'Choose/Type an answer first',text:'Fill the blank before checking.',confirmButtonColor:'#1e4072'});
        return;
    }
    
    // Hide spelling hint
    const hint = document.getElementById('spellingHint_' + step);
    if (hint) hint.style.display = 'none';
    
    const accepted = FEEDBACK_DATA.fill_in_blanks?.[step] || [];
    const normalized = value.toLowerCase();
    const match = accepted.length > 0 && accepted.some(function(answer) {
        return String(answer).trim().toLowerCase() === normalized;
    });
    
    const isFreeType = card ? card.dataset.mode === 'free_type' : false;
    let isFuzzy = false;
    if (!match && isFreeType && accepted.length > 0) {
        isFuzzy = accepted.some(function(correct) {
            const dist = getLevenshteinDistance(normalized, String(correct).trim().toLowerCase());
            return dist > 0 && dist <= 2;
        });
    }
    
    attemptCounts[step] = (attemptCounts[step] || 0) + 1;
    const currentAttempt = attemptCounts[step];
    const isSuccess = match || isFuzzy;

    if (isSuccess || accepted.length === 0) {
        checkedSteps[step] = true;
        playAudioTone(true);
        
        const acc = getItemAccuracy(currentAttempt);
        frontendResults[step] = {
            status: 'correct',
            selected: value,
            attempts: currentAttempt,
            accuracy: acc
        };
        
        if (isFreeType) {
            if (input) {
                input.disabled = true;
                input.style.borderColor = '#28a745';
                input.style.backgroundColor = '#e8f5e9';
            }
        } else {
            const slot = document.getElementById('fibSlot_' + step);
            if (slot) {
                slot.style.borderColor = '#28a745';
                slot.style.backgroundColor = '#e8f5e9';
                const textSpan = slot.querySelector('span');
                if (textSpan) {
                    textSpan.className = 'badge rounded-pill bg-success text-white p-2';
                }
            }
        }
        
        const attemptText = currentAttempt === 1 
            ? '⭐ 1st Try! Perfect accuracy (100%)' 
            : `Solved in ${currentAttempt} attempts (${acc}% accuracy)`;
        
        setFeedback(step, 'correct', 'Correct!', 
            isFuzzy ? 'Correct! (Close enough spelling)' : attemptText,
            step >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
        
        lockStep(step);
        updateGameStage();
        autoAdvance(step);
    } else {
        // Cisco-style: Wrong answer -> Do NOT reveal correct answer! Allow retry
        playAudioTone(false);
        triggerShakeCard(card);
        
        if (isFreeType) {
            if (input) {
                input.style.borderColor = '#dc3545';
                input.style.backgroundColor = '#ffebee';
                input.focus();
            }
        } else {
            const slot = document.getElementById('fibSlot_' + step);
            if (slot) {
                slot.style.borderColor = '#dc3545';
                slot.style.backgroundColor = '#ffebee';
                const textSpan = slot.querySelector('span');
                if (textSpan) {
                    textSpan.className = 'badge rounded-pill bg-danger text-white p-2';
                }
            }
        }
        
        setFeedback(step, 'wrong', 'Not quite right', 
            'That word does not fit the blank. Remove it or type another answer to retry!', 
            `Attempt ${currentAttempt} • Try another word`);
    }
}

// Activity 4: Matching SVG Connect the Dots
function clickMatchingCard(el, setIndex) {
    if (checkedSteps[setIndex]) return;
    
    const side = el.dataset.side;
    const idx = parseInt(el.dataset.index, 10);
    
    if (!matchingConnections[setIndex]) {
        matchingConnections[setIndex] = {};
    }
    
    const getConnectedPair = (side, index) => {
        if (side === 'left') {
            return matchingConnections[setIndex][index] ? { leftIdx: index, rightIdx: matchingConnections[setIndex][index].rightIdx } : null;
        } else {
            for (let leftKey in matchingConnections[setIndex]) {
                if (matchingConnections[setIndex][leftKey]?.rightIdx === index) {
                    return { leftIdx: parseInt(leftKey, 10), rightIdx: index };
                }
            }
            return null;
        }
    };
    
    const existing = getConnectedPair(side, idx);
    if (existing) {
        // Disconnect
        delete matchingConnections[setIndex][existing.leftIdx];
        
        const leftCard = document.getElementById(`matchCardL_${setIndex}_${existing.leftIdx}`);
        const rightCard = document.getElementById(`matchCardR_${setIndex}_${existing.rightIdx}`);
        if (leftCard) {
            leftCard.style.borderColor = ''; leftCard.style.color = '';
            leftCard.classList.remove('selected-left');
            leftCard.classList.remove('is-wrong');
            const dot = leftCard.querySelector('.matching-dot-badge');
            if (dot) dot.style.display = 'none';
            const badge = leftCard.querySelector('.status-badge');
            if (badge) badge.remove();
        }
        if (rightCard) {
            rightCard.style.borderColor = ''; rightCard.style.color = '';
            rightCard.classList.remove('is-wrong');
            const dot = rightCard.querySelector('.matching-dot-badge');
            if (dot) dot.style.display = 'none';
            const badge = rightCard.querySelector('.status-badge');
            if (badge) badge.remove();
        }
        
        const select = document.getElementById(`match${setIndex}_${existing.leftIdx}`);
        if (select) select.value = '';
        
        if (selectedLeftCard && selectedLeftCard.id === `matchCardL_${setIndex}_${existing.leftIdx}`) {
            selectedLeftCard = null;
        }
        
        drawMatchingLines(setIndex);
        checkMatchingComplete(setIndex);
        return;
    }
    
    if (side === 'left') {
        if (selectedLeftCard) {
            selectedLeftCard.classList.remove('selected-left');
        }
        selectedLeftCard = el;
        el.classList.add('selected-left');
    } else {
        if (!selectedLeftCard) return;
        
        const leftIdx = parseInt(selectedLeftCard.dataset.index, 10);
        const rightIdx = idx;
        const rightText = el.dataset.text;
        const pairColor = matchingColors[leftIdx % matchingColors.length];
        
        matchingConnections[setIndex][leftIdx] = {
            rightIdx: rightIdx,
            rightText: rightText,
            color: pairColor
        };
        
        selectedLeftCard.style.borderColor = pairColor;
        selectedLeftCard.style.color = pairColor;
        selectedLeftCard.classList.remove('selected-left');
        selectedLeftCard.classList.remove('is-wrong');
        const leftDot = selectedLeftCard.querySelector('.matching-dot-badge');
        if (leftDot) {
            leftDot.style.backgroundColor = pairColor;
            leftDot.style.display = 'block';
        }
        
        el.style.borderColor = pairColor;
        el.style.color = pairColor;
        el.classList.remove('is-wrong');
        const rightDot = el.querySelector('.matching-dot-badge');
        if (rightDot) {
            rightDot.style.backgroundColor = pairColor;
            rightDot.style.display = 'block';
        }
        
        const select = document.getElementById(`match${setIndex}_${leftIdx}`);
        if (select) select.value = rightText;
        
        selectedLeftCard = null;
        drawMatchingLines(setIndex);
        checkMatchingComplete(setIndex);
    }
}

function checkMatchingComplete(setIndex) {
    const leftCardsCount = document.querySelectorAll(`#matchingCard_${setIndex} .left-card`).length;
    const connectionsCount = Object.keys(matchingConnections[setIndex] || {}).length;
    const saveBtn = document.getElementById('saveMatchingBtn_' + setIndex);
    if (saveBtn) {
        saveBtn.disabled = (connectionsCount < leftCardsCount);
    }
    updateGameStage();
}

function drawMatchingLines(setIndex) {
    const svg = document.getElementById('matchingSvg_' + setIndex);
    if (!svg) return;
    svg.innerHTML = '';
    
    const connections = matchingConnections[setIndex] || {};
    const containerRect = svg.getBoundingClientRect();
    
    for (let leftIdx in connections) {
        const conn = connections[leftIdx];
        const leftCard = document.getElementById(`matchCardL_${setIndex}_${leftIdx}`);
        const rightCard = document.getElementById(`matchCardR_${setIndex}_${conn.rightIdx}`);
        if (!leftCard || !rightCard) continue;
        
        const leftDot = leftCard.querySelector('.matching-dot-badge');
        const rightDot = rightCard.querySelector('.matching-dot-badge');
        if (!leftDot || !rightDot) continue;
        
        const rectL = leftDot.getBoundingClientRect();
        const rectR = rightDot.getBoundingClientRect();
        
        const x1 = rectL.left + rectL.width / 2 - containerRect.left;
        const y1 = rectL.top + rectL.height / 2 - containerRect.top;
        const x2 = rectR.left + rectR.width / 2 - containerRect.left;
        const y2 = rectR.top + rectR.height / 2 - containerRect.top;
        
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', conn.color);
        line.setAttribute('stroke-width', '4');
        line.setAttribute('stroke-linecap', 'round');
        svg.appendChild(line);
    }
}

function confirmMatchingStep(setIndex) {
    if (checkedSteps[setIndex]) return;
    
    const card = getCardForStep(setIndex);
    const correctAnswers = FEEDBACK_DATA.matching?.[setIndex] || {};
    const connections = matchingConnections[setIndex] || {};
    
    attemptCounts[setIndex] = (attemptCounts[setIndex] || 0) + 1;
    const currentAttempt = attemptCounts[setIndex];
    
    let allCorrect = true;
    const leftCards = document.querySelectorAll(`#matchingCard_${setIndex} .left-card`);
    
    leftCards.forEach(lCard => {
        const leftIdx = parseInt(lCard.dataset.index, 10);
        const conn = connections[leftIdx];
        const correctVal = correctAnswers[leftIdx];
        const submittedVal = conn ? conn.rightText : '';
        
        const rightCard = conn ? document.getElementById(`matchCardR_${setIndex}_${conn.rightIdx}`) : null;
        let isPairCorrect = (submittedVal && correctVal && submittedVal.trim().toLowerCase() === correctVal.trim().toLowerCase());
        
        if (!isPairCorrect) {
            allCorrect = false;
            lCard.classList.add('is-wrong');
            if (rightCard) rightCard.classList.add('is-wrong');
            labelCard(lCard, 'Try again', 'bi-x-circle-fill');
        } else {
            lCard.classList.remove('is-wrong');
            lCard.classList.add('is-correct');
            if (rightCard) {
                rightCard.classList.remove('is-wrong');
                rightCard.classList.add('is-correct');
            }
            labelCard(lCard, 'Correct!', 'bi-check-circle-fill');
        }
    });
    
    if (allCorrect) {
        checkedSteps[setIndex] = true;
        playAudioTone(true);
        
        leftCards.forEach(c => c.classList.add('locked'));
        document.querySelectorAll(`#matchingCard_${setIndex} .right-card`).forEach(c => c.classList.add('locked'));
        
        const acc = getItemAccuracy(currentAttempt);
        frontendResults[setIndex] = {
            status: 'correct',
            selected: getSelectedAnswerText(setIndex),
            attempts: currentAttempt,
            accuracy: acc
        };
        
        const attemptText = currentAttempt === 1 
            ? '⭐ 1st Try! All pairs matched correctly (100%)' 
            : `All pairs matched in ${currentAttempt} attempts (${acc}% accuracy)`;
        
        setFeedback(setIndex, 'correct', 'Correct!', attemptText,
            currentStep >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
            
        const saveBtn = document.getElementById('saveMatchingBtn_' + setIndex);
        if (saveBtn) saveBtn.disabled = true;
        
        lockStep(setIndex);
        updateGameStage();
        autoAdvance(setIndex);
    } else {
        // Cisco-style: Do NOT reveal correct answers! Allow disconnect & reconnect
        playAudioTone(false);
        triggerShakeCard(card);
        
        setFeedback(setIndex, 'wrong', 'Some pairs are incorrect', 
            'Review the pairs marked in red. Tap to disconnect and reconnect them!', 
            `Attempt ${currentAttempt} • Tap cards to adjust connections`);
    }
}

function syncMatchingDropdownToUI(setIndex) {
    if (checkedSteps[setIndex]) return;
    if (!matchingConnections[setIndex]) {
        matchingConnections[setIndex] = {};
    }
    
    const rightCards = document.querySelectorAll(`#matchingCard_${setIndex} .right-card`);
    rightCards.forEach(c => {
        c.style.borderColor = ''; c.style.color = '';
        const dot = c.querySelector('.matching-dot-badge');
        if (dot) dot.style.display = 'none';
    });
    
    const selects = document.querySelectorAll(`#matchingCard_${setIndex} .matching-select`);
    selects.forEach(select => {
        const parts = select.dataset.pi.split('_');
        const leftIdx = parseInt(parts[1], 10);
        const val = select.value;
        
        const leftCard = document.getElementById(`matchCardL_${setIndex}_${leftIdx}`);
        if (!leftCard) return;
        
        if (!val) {
            delete matchingConnections[setIndex][leftIdx];
            leftCard.style.borderColor = ''; leftCard.style.color = '';
            const dot = leftCard.querySelector('.matching-dot-badge');
            if (dot) dot.style.display = 'none';
            return;
        }
        
        let foundRightIdx = -1;
        for (let rightCard of rightCards) {
            if (rightCard.dataset.text === val) {
                foundRightIdx = parseInt(rightCard.dataset.index, 10);
                break;
            }
        }
        
        if (foundRightIdx > -1) {
            const pairColor = matchingColors[leftIdx % matchingColors.length];
            matchingConnections[setIndex][leftIdx] = {
                rightIdx: foundRightIdx,
                rightText: val,
                color: pairColor
            };
            
            leftCard.style.borderColor = pairColor;
            leftCard.style.color = pairColor;
            const leftDot = leftCard.querySelector('.matching-dot-badge');
            if (leftDot) {
                leftDot.style.backgroundColor = pairColor; leftDot.style.display = 'block';
            }
            
            const rightCard = document.getElementById(`matchCardR_${setIndex}_${foundRightIdx}`);
            if (rightCard) {
                rightCard.style.borderColor = pairColor; rightCard.style.color = pairColor;
                const rightDot = rightCard.querySelector('.matching-dot-badge');
                if (rightDot) { rightDot.style.backgroundColor = pairColor; rightDot.style.display = 'block'; }
            }
        }
    });
    
    drawMatchingLines(setIndex);
    checkMatchingComplete(setIndex);
}

// Activity 5 & 6: Drag & Drop Sorting and Sequencing
function updateSortButtonStates(setIndex) {
    const list = document.getElementById('dragList_' + setIndex) || document.querySelector('.drag-list');
    if (!list) return;
    const items = list.querySelectorAll('.drag-item');
    items.forEach((item, idx) => {
        const upBtn = item.querySelector('.up-btn');
        const downBtn = item.querySelector('.down-btn');
        if (upBtn) upBtn.disabled = (idx === 0);
        if (downBtn) downBtn.disabled = (idx === items.length - 1);
    });
}

function moveItemUp(btn, setIndex) {
    if (checkedSteps[setIndex]) return;
    const item = btn.closest('.drag-item');
    if (!item) return;
    const prev = item.previousElementSibling;
    if (prev) {
        item.parentNode.insertBefore(item, prev);
        updateSortButtonStates(setIndex);
        updateGameStage();
    }
}

function moveItemDown(btn, setIndex) {
    if (checkedSteps[setIndex]) return;
    const item = btn.closest('.drag-item');
    if (!item) return;
    const next = item.nextElementSibling;
    if (next) {
        item.parentNode.insertBefore(next, item);
        updateSortButtonStates(setIndex);
        updateGameStage();
    }
}

function handleSortKeyboard(event, item, setIndex) {
    if (checkedSteps[setIndex]) return;
    if (event.key === 'ArrowUp') {
        event.preventDefault();
        const prev = item.previousElementSibling;
        if (prev) {
            item.parentNode.insertBefore(item, prev);
            updateSortButtonStates(setIndex);
            updateGameStage();
            item.focus();
        }
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        const next = item.nextElementSibling;
        if (next) {
            item.parentNode.insertBefore(next, item);
            updateSortButtonStates(setIndex);
            updateGameStage();
            item.focus();
        }
    }
}

function confirmOrderStep(setIndex) {
    if (checkedSteps[setIndex]) return;
    
    const card = getCardForStep(setIndex);
    const list = document.getElementById('dragList_' + setIndex);
    const items = Array.from(list.querySelectorAll('.drag-item'));
    const correctOrder = FEEDBACK_DATA.order?.[setIndex] || [];
    const tolerance = parseInt(FEEDBACK_DATA.tolerance?.[setIndex] || 0, 10);
    
    attemptCounts[setIndex] = (attemptCounts[setIndex] || 0) + 1;
    const currentAttempt = attemptCounts[setIndex];
    
    let wrongCount = 0;
    items.forEach((item, idx) => {
        const origIndex = parseInt(item.dataset.index, 10);
        const isPosCorrect = (origIndex === correctOrder[idx]);
        if (!isPosCorrect) {
            wrongCount++;
        }
    });
    
    const isCorrect = (wrongCount <= tolerance);
    
    if (isCorrect) {
        checkedSteps[setIndex] = true;
        playAudioTone(true);
        
        items.forEach((item, idx) => {
            item.classList.add('locked');
            item.setAttribute('draggable', 'false');
            item.querySelectorAll('button, span[style]').forEach(el => el.disabled = true);
            item.classList.add('is-correct');
            labelCard(item, 'Correct!', 'bi-check-circle-fill');
        });
        
        const acc = getItemAccuracy(currentAttempt);
        frontendResults[setIndex] = {
            status: 'correct',
            selected: getSelectedAnswerText(setIndex),
            attempts: currentAttempt,
            accuracy: acc
        };
        
        const attemptText = currentAttempt === 1 
            ? '⭐ 1st Try! Perfect order (100%)' 
            : `Order solved in ${currentAttempt} attempts (${acc}% accuracy)`;
        
        setFeedback(setIndex, 'correct', 'Correct!', attemptText,
            currentStep >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
            
        if (ACTIVITY_TYPE === 'sequencing') {
            const timeline = document.getElementById('timelineView_' + setIndex);
            if (timeline) {
                timeline.style.display = 'block';
                const flow = timeline.querySelector('.timeline-steps-flow');
                if (flow) {
                    flow.innerHTML = '';
                    correctOrder.forEach((cIdx, idx) => {
                        const stepText = FEEDBACK_DATA.original_items?.[setIndex]?.[cIdx] || '';
                        const stepEl = document.createElement('div');
                        stepEl.className = 'timeline-step-card p-3 border rounded bg-white text-center';
                        stepEl.style.minWidth = '145px';
                        stepEl.innerHTML = `
                            <div class="badge bg-primary rounded-circle mb-2" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center;">${idx + 1}</div>
                            <div class="small fw-semibold text-wrap">${escapeHtml(stepText)}</div>
                        `;
                        flow.appendChild(stepEl);
                    });
                }
            }
        }
        
        const saveBtn = card.querySelector('.check-answer-btn');
        if (saveBtn) saveBtn.disabled = true;
        
        lockStep(setIndex);
        updateGameStage();
        autoAdvance(setIndex);
    } else {
        // Cisco-style: Wrong order -> Do NOT reveal correct order! Allow rearranging
        playAudioTone(false);
        triggerShakeCard(card);
        
        setFeedback(setIndex, 'wrong', 'Order is not quite right', 
            'The cards are not in the correct sequence yet. Drag or use the arrows to rearrange!', 
            `Attempt ${currentAttempt} • Rearrange the cards and click Save Order again`);
    }
}

// Activity 7: Image Labeling
function dragLabelPill(event, text) {
    event.dataTransfer.setData("text/plain", text);
}

function dropLabelPill(event, pinIndex) {
    event.preventDefault();
    if (checkedSteps[0]) return;
    const pin = document.getElementById('labelPin_' + pinIndex);
    if (!pin) return;
    pin.classList.remove('glow');
    const text = event.dataTransfer.getData("text/plain");
    if (text) {
        assignLabelToPin(pinIndex, text);
    }
}

function clickLabelPin(el) {
    if (checkedSteps[0]) return;
    const pinIndex = parseInt(el.dataset.index, 10);
    
    const input = document.getElementById('label' + pinIndex);
    if (input && input.value) {
        removeLabelFromPin(pinIndex);
        return;
    }
    
    document.querySelectorAll('.label-dot-pin').forEach(p => p.classList.remove('pulsing'));
    if (selectedLabelPin === el) {
        selectedLabelPin = null;
    } else {
        selectedLabelPin = el;
        el.classList.add('pulsing');
    }
}

function clickLabelPill(text) {
    if (checkedSteps[0]) return;
    if (selectedLabelPin) {
        const pinIndex = parseInt(selectedLabelPin.dataset.index, 10);
        assignLabelToPin(pinIndex, text);
        selectedLabelPin.classList.remove('pulsing');
        selectedLabelPin = null;
    }
}

function assignLabelToPin(pinIndex, text) {
    const pin = document.getElementById('labelPin_' + pinIndex);
    const input = document.getElementById('label' + pinIndex);
    if (!pin || !input) return;
    
    const oldText = input.value;
    if (oldText) {
        returnLabelToPool(oldText);
    }
    
    input.value = text;
    imageLabelAnswers[pinIndex] = text;
    
    pin.classList.remove('is-wrong');
    const textLabel = pin.querySelector('.assigned-text-label');
    if (textLabel) {
        textLabel.textContent = text;
        textLabel.className = 'assigned-text-label position-absolute bg-primary text-white border border-2 border-white rounded px-2 py-1 small';
        textLabel.style.display = 'block';
    }
    hideLabelInPool(text);
    updateGameStage();
}

function removeLabelFromPin(pinIndex) {
    const pin = document.getElementById('labelPin_' + pinIndex);
    const input = document.getElementById('label' + pinIndex);
    if (!pin || !input) return;
    
    const text = input.value;
    if (text) {
        returnLabelToPool(text);
    }
    
    input.value = '';
    delete imageLabelAnswers[pinIndex];
    
    pin.classList.remove('is-wrong');
    const textLabel = pin.querySelector('.assigned-text-label');
    if (textLabel) {
        textLabel.textContent = '';
        textLabel.style.display = 'none';
    }
    updateGameStage();
}

function hideLabelInPool(text) {
    const pool = document.getElementById('imageLabelPillsPool');
    if (!pool) return;
    const pills = pool.querySelectorAll('.image-label-pill');
    for (let pill of pills) {
        if (pill.textContent.trim() === text && pill.style.display !== 'none') {
            pill.style.display = 'none';
            break;
        }
    }
}

function returnLabelToPool(text) {
    const pool = document.getElementById('imageLabelPillsPool');
    if (!pool) return;
    const pills = pool.querySelectorAll('.image-label-pill');
    for (let pill of pills) {
        if (pill.textContent.trim() === text && pill.style.display === 'none') {
            pill.style.display = 'inline-flex';
            break;
        }
    }
}

function clickLabelFallbackCard(el) {
    if (checkedSteps[0]) return;
    const side = el.dataset.side;
    const idx = parseInt(el.dataset.index, 10);
    
    const getConnectedPair = (side, index) => {
        if (side === 'left') {
            return fallbackConnections[index] ? { leftIdx: index, rightIdx: fallbackConnections[index].rightIdx } : null;
        } else {
            for (let leftKey in fallbackConnections) {
                if (fallbackConnections[leftKey]?.rightIdx === index) {
                    return { leftIdx: parseInt(leftKey, 10), rightIdx: index };
                }
            }
            return null;
        }
    };
    
    const existing = getConnectedPair(side, idx);
    if (existing) {
        delete fallbackConnections[existing.leftIdx];
        const leftCard = document.getElementById(`labelCardL_${existing.leftIdx}`);
        const rightCard = document.getElementById(`labelCardR_${existing.rightIdx}`);
        if (leftCard) {
            leftCard.style.borderColor = ''; leftCard.style.color = '';
            leftCard.classList.remove('selected-left');
            leftCard.classList.remove('is-wrong');
            const dot = leftCard.querySelector('.matching-dot-badge');
            if (dot) dot.style.display = 'none';
            const badge = leftCard.querySelector('.status-badge');
            if (badge) badge.remove();
        }
        if (rightCard) {
            rightCard.style.borderColor = ''; rightCard.style.color = '';
            rightCard.classList.remove('is-wrong');
            const dot = rightCard.querySelector('.matching-dot-badge');
            if (dot) dot.style.display = 'none';
            const badge = rightCard.querySelector('.status-badge');
            if (badge) badge.remove();
        }
        const input = document.getElementById('label' + existing.leftIdx);
        if (input) input.value = '';
        
        if (selectedFallbackLeftCard && selectedFallbackLeftCard.id === `labelCardL_${existing.leftIdx}`) {
            selectedFallbackLeftCard = null;
        }
        return;
    }
    
    if (side === 'left') {
        if (selectedFallbackLeftCard) {
            selectedFallbackLeftCard.classList.remove('selected-left');
        }
        selectedFallbackLeftCard = el;
        el.classList.add('selected-left');
    } else {
        if (!selectedFallbackLeftCard) return;
        const leftIdx = parseInt(selectedFallbackLeftCard.dataset.index, 10);
        const rightIdx = idx;
        const rightText = el.dataset.text;
        const pairColor = matchingColors[leftIdx % matchingColors.length];
        
        fallbackConnections[leftIdx] = { rightIdx: rightIdx, rightText: rightText, color: pairColor };
        
        selectedFallbackLeftCard.style.borderColor = pairColor;
        selectedFallbackLeftCard.style.color = pairColor;
        selectedFallbackLeftCard.classList.remove('selected-left');
        selectedFallbackLeftCard.classList.remove('is-wrong');
        const leftDot = selectedFallbackLeftCard.querySelector('.matching-dot-badge');
        if (leftDot) { leftDot.style.backgroundColor = pairColor; leftDot.style.display = 'block'; }
        
        el.style.borderColor = pairColor; el.style.color = pairColor;
        el.classList.remove('is-wrong');
        const rightDot = el.querySelector('.matching-dot-badge');
        if (rightDot) { rightDot.style.backgroundColor = pairColor; rightDot.style.display = 'block'; }
        
        const input = document.getElementById('label' + leftIdx);
        if (input) input.value = rightText;
        
        selectedFallbackLeftCard = null;
    }
}

function confirmLabelsStep() {
    if (checkedSteps[0]) return;
    
    const card = getCardForStep(0);
    const inputs = document.querySelectorAll('.label-input');
    let allAnswered = true;
    inputs.forEach(input => {
        if (!input.value.trim()) allAnswered = false;
    });
    
    if (!allAnswered) {
        Swal.fire({icon:'info',title:'Labels incomplete',text:'Please assign a label to every pin.',confirmButtonColor:'#1e4072'});
        return;
    }
    
    attemptCounts[0] = (attemptCounts[0] || 0) + 1;
    const currentAttempt = attemptCounts[0];
    
    const correctAnswers = FEEDBACK_DATA.image_label || {};
    let allCorrect = true;
    
    inputs.forEach(input => {
        const idx = parseInt(input.dataset.li, 10);
        const given = input.value.trim().toLowerCase();
        const correct = (correctAnswers[idx] || '').trim().toLowerCase();
        const isPinCorrect = (given === correct);
        if (!isPinCorrect) allCorrect = false;
        
        const pin = document.getElementById('labelPin_' + idx);
        const fbCard = document.getElementById('labelCardL_' + idx);
        
        if (pin) {
            if (isPinCorrect) {
                pin.classList.remove('is-wrong');
                pin.classList.add('is-correct');
                pin.style.boxShadow = '0 0 15px #28a745';
                const textLabel = pin.querySelector('.assigned-text-label');
                if (textLabel) {
                    textLabel.className = 'assigned-text-label position-absolute bg-success text-white border border-2 border-white rounded px-2 py-1 small';
                }
            } else {
                pin.classList.add('is-wrong');
                pin.style.boxShadow = '0 0 15px #dc3545';
                const textLabel = pin.querySelector('.assigned-text-label');
                if (textLabel) {
                    textLabel.className = 'assigned-text-label position-absolute bg-danger text-white border border-2 border-white rounded px-2 py-1 small';
                }
            }
        }
        
        if (fbCard) {
            if (isPinCorrect) {
                fbCard.classList.remove('is-wrong');
                fbCard.classList.add('is-correct');
                labelCard(fbCard, 'Correct!', 'bi-check-circle-fill');
            } else {
                fbCard.classList.add('is-wrong');
                labelCard(fbCard, 'Try again', 'bi-x-circle-fill');
            }
        }
    });
    
    if (allCorrect) {
        checkedSteps[0] = true;
        playAudioTone(true);
        
        inputs.forEach(input => {
            const idx = parseInt(input.dataset.li, 10);
            const pin = document.getElementById('labelPin_' + idx);
            const fbCard = document.getElementById('labelCardL_' + idx);
            if (pin) pin.classList.add('locked');
            if (fbCard) fbCard.classList.add('locked');
        });
        
        const acc = getItemAccuracy(currentAttempt);
        frontendResults[0] = {
            status: 'correct',
            selected: getSelectedAnswerText(0),
            attempts: currentAttempt,
            accuracy: acc
        };
        
        const attemptText = currentAttempt === 1 
            ? '⭐ 1st Try! All labels correct (100%)' 
            : `All labels matched in ${currentAttempt} attempts (${acc}% accuracy)`;
        
        setFeedback(0, 'correct', 'Correct!', attemptText,
            currentStep >= TOTAL_STEPS - 1 ? 'Mission review loading...' : 'Next question starting...');
            
        lockStep(0);
        updateGameStage();
        autoAdvance(0);
    } else {
        // Cisco-style: Wrong labels -> Do NOT reveal correct answers! Allow re-assigning
        playAudioTone(false);
        triggerShakeCard(card);
        
        setFeedback(0, 'wrong', 'Some labels are incorrect', 
            'Review the pins or cards marked in red. Click a pin to remove its label and try another!', 
            `Attempt ${currentAttempt} • Adjust your labels and save again`);
    }
}

// Activity 8: Flashcards
function toggleFlashcardMotion(toggle) {
    isMotionReduced = toggle.checked;
    localStorage.setItem('reduceMotion', isMotionReduced ? 'true' : 'false');
}

function flipFlashcard(el, idx) {
    const card = el.querySelector('.game-flashcard') || el;
    const front = card.querySelector('.front');
    const back = card.querySelector('.back');
    
    const isFlipped = !card.classList.contains('flipped');
    card.classList.toggle('flipped', isFlipped);
    
    const buttonDiv = document.getElementById('flashcardAssess_' + idx);
    const hint = document.getElementById('flashcardHint_' + idx);
    
    if (isMotionReduced) {
        card.style.transition = 'none';
        card.style.transform = 'none';
        front.style.transition = 'opacity 0.2s';
        back.style.transition = 'opacity 0.2s';
        
        if (isFlipped) {
            front.style.opacity = '0';
            back.style.opacity = '1';
            back.style.transform = 'none';
            back.style.backfaceVisibility = 'visible';
            front.style.backfaceVisibility = 'hidden';
            if (buttonDiv) buttonDiv.setAttribute('style', 'display: flex !important;');
            if (hint) hint.textContent = 'How did you do? Select confidence below.';
        } else {
            front.style.opacity = '1';
            back.style.opacity = '0';
            back.style.transform = 'none';
            front.style.backfaceVisibility = 'visible';
            back.style.backfaceVisibility = 'hidden';
            if (buttonDiv) buttonDiv.setAttribute('style', 'display: none !important;');
            if (hint) hint.textContent = 'Click the card to flip it and reveal the back.';
        }
    } else {
        front.style.opacity = '';
        back.style.opacity = '';
        front.style.backfaceVisibility = '';
        back.style.backfaceVisibility = '';
        back.style.transform = '';
        card.style.transition = '';
        
        if (isFlipped) {
            card.style.transform = 'rotateY(180deg)';
            if (buttonDiv) buttonDiv.setAttribute('style', 'display: flex !important;');
            if (hint) hint.textContent = 'How did you do? Select confidence below.';
        } else {
            card.style.transform = 'rotateY(0deg)';
            if (buttonDiv) buttonDiv.setAttribute('style', 'display: none !important;');
            if (hint) hint.textContent = 'Click the card to flip it and reveal the back.';
        }
    }
}

function assessFlashcard(step, confidence) {
    const cardArticle = document.getElementById('flashcardArticle_' + step);
    if (cardArticle) {
        const card = cardArticle.querySelector('.game-flashcard');
        if (card) {
            card.classList.remove('flipped');
            card.style.transform = 'rotateY(0deg)';
            const front = card.querySelector('.front');
            const back = card.querySelector('.back');
            if (front) { front.style.opacity = ''; front.style.backfaceVisibility = ''; }
            if (back) { back.style.opacity = ''; back.style.backfaceVisibility = ''; back.style.transform = ''; }
        }
        const buttonDiv = document.getElementById('flashcardAssess_' + step);
        if (buttonDiv) buttonDiv.setAttribute('style', 'display: none !important;');
        const hint = document.getElementById('flashcardHint_' + step);
        if (hint) hint.textContent = 'Click the card to flip it and reveal the back.';
    }
    
    flashcardPasses[step] = (flashcardPasses[step] || 0) + 1;
    attemptCounts[step] = flashcardPasses[step];
    
    if (confidence === 1) {
        flashcardConfidence[step] = 1;
        const idxInOrder = flashcardOrder.indexOf(step);
        if (idxInOrder > -1) {
            flashcardOrder.splice(idxInOrder, 1);
        }
        flashcardOrder.push(step);
    } else {
        if (flashcardConfidence[step] === undefined) {
            flashcardConfidence[step] = 2; // Got it first try
        }
        const idxInOrder = flashcardOrder.indexOf(step);
        if (idxInOrder > -1) {
            flashcardOrder.splice(idxInOrder, 1);
        }
    }
    
    if (flashcardOrder.length === 0) {
        checkedSteps[0] = true;
        
        let sumConfidence = 0;
        for (let i = 0; i < TOTAL_STEPS; i++) {
            sumConfidence += flashcardConfidence[i] !== undefined ? flashcardConfidence[i] : 2;
        }
        const rate = sumConfidence / (TOTAL_STEPS * 2);
        const xp = Math.max(1, Math.round(rate * 20));
        
        frontendResults[0] = {
            status: 'saved',
            selected: `Confidence XP: ${xp}`,
            attempts: 1,
            accuracy: Math.round(rate * 100)
        };
        
        currentStep = TOTAL_STEPS;
        updateGameStage();
    } else {
        updateGameStage();
    }
}

function collectAnswers() {
    const answers = {};
    if (ACTIVITY_TYPE === 'multiple_choice') {
        return mcAnswers;
    }
    if (ACTIVITY_TYPE === 'true_false') {
        return tfAnswers;
    }
    if (ACTIVITY_TYPE === 'fill_in_blanks') {
        document.querySelectorAll('.fib-input').forEach(function(input) { answers[input.dataset.si] = input.value.trim(); });
        return answers;
    }
    if (ACTIVITY_TYPE === 'matching') {
        document.querySelectorAll('.matching-select').forEach(function(select) { answers[select.dataset.pi] = select.value; });
        return answers;
    }
    if (ACTIVITY_TYPE === 'drag_drop_sort' || ACTIVITY_TYPE === 'sequencing') {
        if (document.querySelectorAll('.drag-list').length === 1) {
            return Array.from(document.querySelectorAll('.drag-list .drag-item')).map(function(item) { return parseInt(item.dataset.index, 10); });
        }
        document.querySelectorAll('.drag-list').forEach(function(list) {
            answers[list.dataset.step] = Array.from(list.querySelectorAll('.drag-item')).map(function(item) { return parseInt(item.dataset.index, 10); });
        });
        return answers;
    }
    if (ACTIVITY_TYPE === 'image_label') {
        document.querySelectorAll('.label-input').forEach(function(input) { answers[input.dataset.li] = input.value.trim(); });
        return answers;
    }
    if (ACTIVITY_TYPE === 'flashcards') {
        const results = [];
        for (let i = 0; i < TOTAL_STEPS; i++) {
            results.push({
                card_id: i,
                confidence: flashcardConfidence[i] !== undefined ? flashcardConfidence[i] : 2
            });
        }
        return {results: results};
    }
    return answers;
}

function validateAllAnswered() {
    if (ACTIVITY_TYPE === 'multiple_choice') return Object.keys(mcAnswers).length >= TOTAL_STEPS;
    if (ACTIVITY_TYPE === 'true_false') return Object.keys(tfAnswers).length >= TOTAL_STEPS;
    if (ACTIVITY_TYPE === 'fill_in_blanks') return Array.from(document.querySelectorAll('.fib-input')).every(function(input) { return input.value.trim().length > 0; });
    if (ACTIVITY_TYPE === 'matching') return Array.from(document.querySelectorAll('.matching-select')).every(function(select) { return select.value !== ''; });
    if (ACTIVITY_TYPE === 'image_label') return Array.from(document.querySelectorAll('.label-input')).every(function(input) { return input.value.trim().length > 0; });
    if (ACTIVITY_TYPE === 'flashcards') return flashcardOrder.length === 0;
    return true;
}

function getQuestionTitle(step) {
    const card = getCardForStep(step);
    const title = card ? card.querySelector('h2') : null;
    return title ? title.textContent.trim() : 'Challenge ' + (step + 1);
}

function getSelectedAnswerText(step) {
    if (ACTIVITY_TYPE === 'multiple_choice') {
        const selected = getCardForStep(step)?.querySelector('.game-answer-card.selected .mc-option-text')
            || getCardForStep(step)?.querySelector('.game-answer-card.selected span:last-child');
        return selected ? selected.textContent.trim() : 'No answer';
    }
    if (ACTIVITY_TYPE === 'true_false') {
        return tfAnswers[step] === undefined ? 'No answer' : tfAnswers[step];
    }
    if (ACTIVITY_TYPE === 'fill_in_blanks') {
        const input = document.querySelector('.game-question-card[data-step="' + step + '"] .fib-input');
        return input ? input.value.trim() : 'No answer';
    }
    if (ACTIVITY_TYPE === 'matching') {
        const selects = Array.from(document.querySelectorAll('.game-question-card[data-step="' + step + '"] .matching-select'));
        if (!selects.length) return 'No answer';
        return selects.map(function(select, index) {
            return 'Pair ' + (index + 1) + ': ' + (select.value || 'No answer');
        }).join('; ');
    }
    if (ACTIVITY_TYPE === 'image_label') {
        return Array.from(document.querySelectorAll('.label-input')).map(function(input, index) {
            return 'Label ' + (index + 1) + ': ' + (input.value.trim() || 'No answer');
        }).join('; ');
    }
    if (ACTIVITY_TYPE === 'drag_drop_sort' || ACTIVITY_TYPE === 'sequencing') {
        const list = document.querySelector('.drag-list[data-step="' + step + '"]') || document.querySelector('.drag-list');
        return Array.from(list ? list.querySelectorAll('.drag-item') : []).map(function(item) {
            return item.textContent.trim();
        }).join(' -> ');
    }
    if (ACTIVITY_TYPE === 'flashcards') {
        return 'Card reviewed';
    }
    return 'Answer saved';
}

function buildMissionReview() {
    const list = document.getElementById('missionReviewList');
    const preview = document.getElementById('frontendScorePreview');
    if (!list) return;

    let correctCount = 0;
    let knownCount = 0;
    let totalAttempts = 0;
    let html = '';
    
    const overallAccuracy = calculateOverallAccuracy();

    for (let step = 0; step < TOTAL_STEPS; step++) {
        const result = frontendResults[step] || {status: 'saved', selected: getSelectedAnswerText(step), attempts: 1, accuracy: 100};
        const attempts = attemptCounts[step] || 1;
        totalAttempts += attempts;
        const itemAcc = getItemAccuracy(attempts);
        
        if (result.status === 'correct' || result.status === 'wrong') knownCount++;
        if (result.status === 'correct') correctCount++;
        
        const label = result.status === 'correct'
            ? `<span class="review-state correct"><i class="bi bi-check-circle-fill"></i> Passed (${attempts} attempt${attempts > 1 ? 's' : ''})</span>`
            : result.status === 'wrong'
                ? '<span class="review-state wrong"><i class="bi bi-x-circle-fill"></i> Needs practice</span>'
                : '<span class="review-state saved"><i class="bi bi-bookmark-check-fill"></i> Saved</span>';
        
        const attemptNote = `<div class="d-flex align-items-center gap-2 mt-1"><span class="badge bg-light text-dark border"><i class="bi bi-arrow-repeat me-1"></i> Attempts: <strong>${attempts}</strong></span><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-bullseye me-1"></i> Accuracy: <strong>${itemAcc}%</strong></span></div>`;

        html += `<article class="mission-review-item ${result.status}">
            <strong>${escapeHtml(getQuestionTitle(step))}</strong>
            <p class="mb-1">Your answer: ${escapeHtml(String(result.selected ?? getSelectedAnswerText(step)))}</p>
            ${attemptNote}
            ${label}
        </article>`;
    }
    
    // Overall Accuracy Card
    const accuracyCard = `
        <div class="alert alert-info border-primary d-flex align-items-center justify-content-between p-3 rounded-3 mb-3 text-start">
            <div class="d-flex align-items-center gap-3">
                <div class="badge bg-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-bullseye fs-4 text-white"></i>
                </div>
                <div>
                    <strong class="d-block text-dark fs-6">Overall Accuracy: ${overallAccuracy}%</strong>
                    <span class="small text-muted">Completed all ${TOTAL_STEPS} challenges across ${totalAttempts} total attempt${totalAttempts > 1 ? 's' : ''}</span>
                </div>
            </div>
            <div class="badge bg-primary fs-5 p-2 px-3 rounded-pill">${overallAccuracy}%</div>
        </div>
    `;

    list.innerHTML = accuracyCard + html;
    if (preview) {
        preview.textContent = `All challenges solved! Overall accuracy: ${overallAccuracy}%. Backend scoring will finalize the mission.`;
    }
}

function escapeHtml(value) {
    return value.replace(/[&<>"']/g, function(char) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
    });
}

function submitAnswers() {
    if (!validateAllAnswered()) {
        Swal.fire({icon:'warning',title:'Mission not finished',text:'Please answer every challenge before submitting.',confirmButtonColor:'#a01422'});
        return;
    }
    const btn = document.getElementById('submitBtn');
    if (btn) btn.disabled = true;
    Swal.fire({title:'Submitting mission...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); }});
    
    const payload = {
        answers: collectAnswers(),
        attempts: attemptCounts,
        accuracy_percentage: calculateOverallAccuracy(),
        total_attempts: Object.values(attemptCounts).reduce((a, b) => a + b, 0)
    };
    
    fetch(BASE + '/learning/activity/' + ACTIVITY_ID + '/submit', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        Swal.close();
        if (!data.success) {
            Swal.fire({icon:'error',title:'Mission not submitted',text:data.message || 'Please try again.',confirmButtonColor:'#a01422'});
            if (btn) btn.disabled = false;
            return;
        }
        showResult(data);
    })
    .catch(function() {
        Swal.close();
        Swal.fire({icon:'error',title:'Network error',text:'Could not submit. Please try again.',confirmButtonColor:'#a01422'});
        if (btn) btn.disabled = false;
    });
}

function showResult(data) {
    const stage = document.getElementById('questionStage');
    const nav = document.querySelector('.game-navigation');
    const area = document.getElementById('resultArea');
    if (stage) stage.style.display = 'none';
    if (nav) nav.style.display = 'none';
    if (!area) return;

    let scoreHtml = '<p class="congrats-text text-muted my-3">Great job! Your mission was sent to your teacher. Keep learning!</p>';
    let remedialBanner = '';

    if (data.auto_score !== null && data.auto_score !== undefined) {
        const max = data.max_score || 1;
        const pct = data.score_percentage !== undefined ? data.score_percentage : Math.round((data.auto_score / max) * 100);
        const stars = pct >= 90 ? 3 : (pct >= 70 ? 2 : 1);
        let starHtml = '';
        for (let i = 1; i <= 3; i++) starHtml += '<i class="ph-bold ph-star ' + (i <= stars ? 'text-warning' : 'text-muted') + ' fs-3 mx-1"></i>';
        
        let accHtml = '';
        if (data.accuracy_percentage !== undefined) {
            accHtml = '<div class="badge bg-primary-subtle text-primary p-2 px-3 rounded-pill my-2 fs-6"><i class="ph-bold ph-target me-1"></i> Accuracy Rating: ' + data.accuracy_percentage + '%</div>';
        }

        if (data.needs_remediation) {
            remedialBanner = '<div class="alert alert-warning text-dark border-warning p-3 rounded-3 my-3 text-start"><h6 class="fw-bold mb-1"><i class="ph-bold ph-arrow-counter-clockwise me-1"></i> Remedial Recommended</h6><p class="small mb-0">' + (data.message || 'Score is below 70%. We recommend reviewing the lesson or remedial materials before proceeding.') + '</p></div>';
        }

        scoreHtml = '<div class="complete-score fw-bold fs-3 text-primary my-2">' + data.auto_score + ' / ' + max + ' (' + pct + '%)</div>' + accHtml + '<div class="stars-earned my-3">' + starHtml + '</div>' + remedialBanner + '<p class="congrats-text text-muted">' + (data.message || 'Mission complete!') + '</p>';
    }

    // Add confetti particles inside the page!
    if (!data.needs_remediation) {
        triggerConfettiEffect();
    }

    area.style.display = 'block';
    area.innerHTML = '<section class="mission-complete-card text-center p-4 bg-white border rounded-4 shadow-sm"><div class="complete-badge-icon mb-3"><i class="ph-bold ph-trophy text-warning" style="font-size: 3.8rem;" aria-hidden="true"></i></div><div class="quest-eyebrow text-success fw-bold"><i class="ph-bold ph-check-circle me-1"></i> ' + (data.needs_remediation ? 'MISSION COMPLETED (REMEDIATION)' : 'MISSION ACCOMPLISHED!') + '</div><h2 class="fw-bold text-dark mt-2">' + (data.needs_remediation ? 'Practice Makes Progress!' : 'Superstar Achievement Unlocked!') + '</h2>' + scoreHtml + '<div class="game-actions center d-flex justify-content-center gap-2 mt-4 flex-wrap"><a class="btn btn-bubble-outline px-4 py-2 d-flex align-items-center gap-1.5" href="' + BASE + '/learning/lesson/' + LESSON_PLAN_ID + '"><i class="ph-bold ph-book-open"></i> <span>Back to Lesson Hub</span></a><a class="btn btn-bubble px-4 py-2 d-flex align-items-center gap-1.5" href="' + BASE + '/learning/dashboard?tab=badges"><i class="ph-bold ph-trophy"></i> <span>View My Badges</span></a></div></section>';
}

function triggerConfettiEffect() {
    const body = document.body;
    for (let i = 0; i < 40; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti-particle';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.backgroundColor = ['#FFD93D', '#FF9A3D', '#6BCB77', '#4D96FF', '#9D84FF', '#FF6B9D'][Math.floor(Math.random() * 6)];
        confetti.style.animationDelay = Math.random() * 2 + 's';
        confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
        body.appendChild(confetti);
        setTimeout(() => confetti.remove(), 4000);
    }
}

let dragSrc = null;
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.game-text-input, .game-select-input').forEach(function(field) {
        field.addEventListener('input', updateGameStage);
        field.addEventListener('change', updateGameStage);
        field.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && ACTIVITY_TYPE === 'fill_in_blanks') {
                event.preventDefault();
                checkTextAnswer(parseInt(field.dataset.si || currentStep, 10));
            }
        });
    });

    document.querySelectorAll('.drag-list').forEach(function(list) {
        list.addEventListener('dragstart', function(e) {
            dragSrc = e.target.closest('.drag-item');
            if (dragSrc) dragSrc.classList.add('dragging');
        });
        list.addEventListener('dragover', function(e) {
            e.preventDefault();
            const target = e.target.closest('.drag-item');
            if (target && target !== dragSrc) {
                const rect = target.getBoundingClientRect();
                if (e.clientY < rect.top + rect.height / 2) list.insertBefore(dragSrc, target);
                else list.insertBefore(dragSrc, target.nextSibling);
            }
        });
        list.addEventListener('dragend', function() {
            if (dragSrc) dragSrc.classList.remove('dragging');
            dragSrc = null;
        });
    });

    if (ACTIVITY_TYPE === 'drag_drop_sort' || ACTIVITY_TYPE === 'sequencing') {
        for (let step = 0; step < TOTAL_STEPS; step++) {
            updateSortButtonStates(step);
        }
    }

    if (ACTIVITY_TYPE === 'flashcards') {
        for (let i = 0; i < TOTAL_STEPS; i++) {
            flashcardOrder.push(i);
        }
        const reduceToggle = document.getElementById('flashcardReduceMotionToggle');
        if (reduceToggle) {
            reduceToggle.checked = isMotionReduced;
        }
    }

    updateGameStage();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
