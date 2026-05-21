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
$fillItems = $actData['sentences'] ?? [];
if (empty($fillItems) && !empty($actData['sentence'])) {
    $fillItems = [[
        'text' => (string)$actData['sentence'],
        'answers' => $actData['answers'] ?? [],
    ]];
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
    'fill_in_blanks' => count($fillItems),
    'matching' => count($actData['pairs'] ?? []),
    'flashcards' => count($actData['cards'] ?? []),
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

require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . $basePath . '/css/learner.css">';
?>
<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page">
    <div class="game-player-shell">
        <a href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>" class="mission-back-link">
            <i class="bi bi-arrow-left"></i> Back to Lesson
        </a>

        <section class="game-mission-header">
            <div>
                <div class="quest-eyebrow">Mission Challenge</div>
                <h1><?php echo htmlspecialchars($activity['title'] ?? 'Activity'); ?></h1>
                <p>Choose carefully, move one challenge at a time, and complete the mission when you are ready.</p>
            </div>
            <div class="game-score-chip" aria-label="Mission summary">
                <span><?php echo htmlspecialchars($typeLabel); ?></span>
                <?php if ($hasSub && $earnedScore !== null): ?>
                    <strong><?php echo $earnedScore; ?> / <?php echo max($scoreMax, 0); ?></strong>
                <?php else: ?>
                    <strong><?php echo $questionTotal; ?> challenge<?php echo $questionTotal === 1 ? '' : 's'; ?></strong>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($activity['instructions'])): ?>
            <section class="game-guide-card">
                <strong><i class="bi bi-compass"></i> Mission Guide</strong>
                <p><?php echo nl2br(htmlspecialchars($activity['instructions'])); ?></p>
            </section>
        <?php endif; ?>

        <?php if ($hasSub): ?>
            <section class="mission-complete-card">
                <div class="complete-badge"><i class="bi bi-trophy-fill"></i></div>
                <div class="quest-eyebrow">Mission Completed</div>
                <h2>Achievement unlocked</h2>
                <?php if ($earnedScore !== null): ?>
                    <div class="complete-score"><?php echo $earnedScore; ?> / <?php echo max($scoreMax, 0); ?></div>
                    <div class="stars-earned" aria-label="<?php echo (int)$starsEarned; ?> stars earned">
                        <?php for ($s = 1; $s <= 3; $s++): ?>
                            <span class="<?php echo ($starsEarned !== null && $s <= $starsEarned) ? 'earned' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                <?php else: ?>
                    <p>Your mission was submitted. Your teacher may review this activity.</p>
                <?php endif; ?>
                <div class="game-actions center">
                    <a class="quest-secondary-btn" href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>">
                        <i class="bi bi-arrow-left"></i> Back to Lessons
                    </a>
                    <a class="quest-primary-btn" href="<?php echo htmlspecialchars($basePath); ?>/learning/progress">
                        <i class="bi bi-bar-chart-line"></i> View Progress
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
                        ?>
                        <article class="review-card <?php echo $correct === null ? '' : ($isRight ? 'is-correct' : 'is-wrong'); ?>">
                            <strong><?php echo ($qi + 1) . '. ' . htmlspecialchars($q['text'] ?? $q['question'] ?? 'Question'); ?></strong>
                            <p>Your answer: <?php echo htmlspecialchars(isset(($q['options'] ?? [])[$selected]) ? $getOptionText(($q['options'] ?? [])[$selected]) : 'No answer'); ?></p>
                            <?php if ($correct !== null): ?>
                                <p>Correct answer: <?php echo htmlspecialchars($getOptionText(($q['options'] ?? [])[$correct] ?? '')); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'true_false'): ?>
                    <?php
                    $correct = $actData['answer'] ?? $actData['correct_answer'] ?? ($actData['questions'][0]['correct_answer'] ?? null);
                    $selected = $submittedAnswers[0] ?? ($submittedAnswers['answer'] ?? null);
                    $isRight = $correct !== null && strtolower((string)$selected) === strtolower((string)$correct);
                    ?>
                    <article class="review-card <?php echo $correct === null ? '' : ($isRight ? 'is-correct' : 'is-wrong'); ?>">
                        <strong><?php echo htmlspecialchars($actData['statement'] ?? $actData['question'] ?? 'True or false challenge'); ?></strong>
                        <p>Your answer: <?php echo htmlspecialchars(ucfirst((string)($selected ?? 'No answer'))); ?></p>
                        <?php if ($correct !== null): ?><p>Correct answer: <?php echo htmlspecialchars(ucfirst((string)$correct)); ?></p><?php endif; ?>
                    </article>
                <?php elseif ($actType === 'matching'): ?>
                    <?php foreach (($actData['pairs'] ?? []) as $pi => $pair): ?>
                        <?php $selected = $submittedAnswers[$pi] ?? ''; $isRight = (string)$selected === (string)($pair['right'] ?? ''); ?>
                        <article class="review-card <?php echo $isRight ? 'is-correct' : 'is-wrong'; ?>">
                            <strong><?php echo htmlspecialchars($pair['left'] ?? 'Match item'); ?></strong>
                            <p>Your match: <?php echo htmlspecialchars((string)($selected ?: 'No answer')); ?></p>
                            <p>Correct match: <?php echo htmlspecialchars((string)($pair['right'] ?? '')); ?></p>
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
                    <?php foreach (($actData['labels'] ?? []) as $li => $label): ?>
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
                            <div class="question-pill">Question Challenge</div>
                            <h2><?php echo htmlspecialchars($q['text'] ?? $q['question'] ?? 'Question'); ?></h2>
                            <p class="question-hint">Choose one answer card.</p>
                            <div class="answer-card-grid">
                                <?php foreach (($q['options'] ?? []) as $oi => $opt): ?>
                                    <button type="button" class="game-answer-card" data-answer="<?php echo $oi; ?>" onclick="selectGameChoice(this, <?php echo $qi; ?>, <?php echo $oi; ?>)">
                                        <span class="answer-letter"><?php echo chr(65 + $oi); ?></span>
                                        <span><?php echo htmlspecialchars($getOptionText($opt)); ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'true_false'): ?>
                    <article class="game-question-card" data-step="0" data-required="choice">
                        <div class="question-pill">True or False</div>
                        <h2><?php echo htmlspecialchars($actData['statement'] ?? $actData['question'] ?? 'Read the statement.'); ?></h2>
                        <p class="question-hint">Pick the answer that feels right.</p>
                        <div class="answer-card-grid two">
                            <button type="button" class="game-answer-card" data-answer="true" onclick="selectTrueFalse(this, 'true')">
                                <span class="answer-letter"><i class="bi bi-check-lg"></i></span><span>True</span>
                            </button>
                            <button type="button" class="game-answer-card" data-answer="false" onclick="selectTrueFalse(this, 'false')">
                                <span class="answer-letter"><i class="bi bi-x-lg"></i></span><span>False</span>
                            </button>
                        </div>
                    </article>
                <?php elseif ($actType === 'fill_in_blanks'): ?>
                    <?php foreach ($fillItems as $si => $sentence): ?>
                        <article class="game-question-card" data-step="<?php echo $si; ?>" data-required="text">
                            <div class="question-pill">Fill the Blank</div>
                            <h2><?php echo htmlspecialchars(str_replace('___', '_____', $sentence['text'] ?? 'Complete the sentence.')); ?></h2>
                            <label class="game-input-label" for="fib<?php echo $si; ?>">Your answer</label>
                            <input id="fib<?php echo $si; ?>" class="game-text-input fib-input" data-si="<?php echo $si; ?>" type="text" placeholder="Type your answer">
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'matching'): ?>
                    <?php $pairs = $actData['pairs'] ?? []; $rights = array_column($pairs, 'right'); shuffle($rights); ?>
                    <?php foreach ($pairs as $pi => $pair): ?>
                        <article class="game-question-card" data-step="<?php echo $pi; ?>" data-required="select">
                            <div class="question-pill">Matching Mission</div>
                            <h2><?php echo htmlspecialchars($pair['left'] ?? 'Match this item'); ?></h2>
                            <label class="game-input-label" for="match<?php echo $pi; ?>">Choose the matching card</label>
                            <select id="match<?php echo $pi; ?>" class="game-select-input matching-select" data-pi="<?php echo $pi; ?>">
                                <option value="">Choose a match</option>
                                <?php foreach ($rights as $right): ?>
                                    <option value="<?php echo htmlspecialchars((string)$right); ?>"><?php echo htmlspecialchars((string)$right); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </article>
                    <?php endforeach; ?>
                <?php elseif ($actType === 'drag_drop_sort' || $actType === 'sequencing'): ?>
                    <?php $items = $actData['items'] ?? $actData['steps'] ?? []; ?>
                    <article class="game-question-card" data-step="0" data-required="sort">
                        <div class="question-pill">Order Challenge</div>
                        <h2>Arrange the cards in the correct order.</h2>
                        <p class="question-hint">Drag the cards up or down.</p>
                        <ul class="game-sort-list drag-list" id="dragList">
                            <?php foreach ($items as $ii => $item): ?>
                                <li class="game-sort-item drag-item" draggable="true" data-index="<?php echo $ii; ?>">
                                    <span><i class="bi bi-grip-vertical"></i></span>
                                    <?php echo htmlspecialchars(is_array($item) ? ($item['text'] ?? $item['label'] ?? '') : $item); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php elseif ($actType === 'image_label'): ?>
                    <?php $imgPath = $actData['image_path'] ?? ''; $labels = $actData['labels'] ?? []; ?>
                    <article class="game-question-card" data-step="0" data-required="labels">
                        <div class="question-pill">Image Label Mission</div>
                        <h2>Label the numbered parts.</h2>
                        <?php if ($imgPath): ?>
                            <div class="game-image-label">
                                <img src="<?php echo htmlspecialchars($basePath . '/' . ltrim($imgPath, '/')); ?>" alt="Activity image to label">
                                <?php foreach ($labels as $li => $lbl): ?>
                                    <span class="label-dot" style="left:<?php echo (float)($lbl['x'] ?? 0); ?>%;top:<?php echo (float)($lbl['y'] ?? 0); ?>%;"><?php echo $li + 1; ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="label-inputs">
                            <?php foreach ($labels as $li => $lbl): ?>
                                <label class="game-input-label" for="label<?php echo $li; ?>">Label <?php echo $li + 1; ?></label>
                                <input id="label<?php echo $li; ?>" class="game-text-input label-input" data-li="<?php echo $li; ?>" type="text" placeholder="Type label <?php echo $li + 1; ?>">
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php elseif ($actType === 'flashcards'): ?>
                    <?php foreach (($actData['cards'] ?? []) as $ci => $card): ?>
                        <article class="game-question-card" data-step="<?php echo $ci; ?>" data-required="view">
                            <div class="question-pill">Flashcard Mission</div>
                            <button type="button" class="game-flashcard" onclick="this.classList.toggle('flipped')" aria-label="Flip flashcard">
                                <span class="front"><?php echo htmlspecialchars($card['front'] ?? ''); ?></span>
                                <span class="back"><?php echo htmlspecialchars($card['back'] ?? ''); ?></span>
                            </button>
                            <p class="question-hint">Click the card to flip it.</p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="game-question-card" data-step="0" data-required="view">
                        <div class="question-pill">Activity</div>
                        <h2>This activity type is ready for teacher review after submission.</h2>
                    </article>
                <?php endif; ?>

                <article class="game-question-card final-screen" data-step="<?php echo $questionTotal; ?>" data-required="final">
                    <div class="complete-badge"><i class="bi bi-stars"></i></div>
                    <div class="quest-eyebrow">Mission Complete</div>
                    <h2>Ready to submit?</h2>
                    <p>You answered <strong id="answeredCount">0</strong> of <strong><?php echo $questionTotal; ?></strong> challenges.</p>
                    <div class="stars-earned">
                        <span class="earned">★</span><span class="earned">★</span><span class="earned">★</span>
                    </div>
                    <div class="game-actions center">
                        <button type="button" class="quest-primary-btn" id="submitBtn" onclick="submitAnswers()">
                            <i class="bi bi-send-check"></i> Submit Mission
                        </button>
                        <a class="quest-secondary-btn" href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>">
                            <i class="bi bi-arrow-left"></i> Back to Lessons
                        </a>
                    </div>
                </article>
            </section>

            <nav class="game-navigation" aria-label="Challenge navigation">
                <button type="button" class="quest-secondary-btn" id="backChallengeBtn" onclick="goChallenge(-1)">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <button type="button" class="quest-primary-btn" id="nextChallengeBtn" onclick="goChallenge(1)" disabled>
                    Next Challenge <i class="bi bi-arrow-right"></i>
                </button>
            </nav>

            <div id="resultArea" class="game-result-area" style="display:none;"></div>
        <?php endif; ?>
    </div>
</div>

<script>
const BASE = <?php echo json_encode($basePath); ?>;
const ACTIVITY_ID = <?php echo $actId; ?>;
const ACTIVITY_TYPE = <?php echo json_encode($actType); ?>;
const LESSON_PLAN_ID = <?php echo $lessonPlanId; ?>;
const TOTAL_STEPS = <?php echo $questionTotal; ?>;

let currentStep = 0;
const mcAnswers = {};
let tfAnswer = null;

function getStepCards() {
    return Array.from(document.querySelectorAll('.game-question-card'));
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

    if (counter) counter.textContent = isFinal ? 'Final Review' : 'Question ' + (currentStep + 1) + ' of ' + TOTAL_STEPS;
    if (mood) mood.textContent = isFinal ? 'Mission complete!' : getFriendlyPrompt();
    if (fill) fill.style.width = Math.min(100, Math.round((currentStep / TOTAL_STEPS) * 100)) + '%';
    if (backBtn) backBtn.disabled = currentStep === 0;
    if (nextBtn) {
        nextBtn.style.display = isFinal ? 'none' : '';
        nextBtn.disabled = !isCurrentStepAnswered();
        nextBtn.innerHTML = (currentStep === TOTAL_STEPS - 1)
            ? 'Finish Mission <i class="bi bi-flag"></i>'
            : 'Next Challenge <i class="bi bi-arrow-right"></i>';
    }
    if (answered) answered.textContent = countAnswered();
}

function getFriendlyPrompt() {
    const prompts = ['Choose your answer', 'Great choice!', 'Keep going!', 'Almost there!'];
    return prompts[Math.min(currentStep, prompts.length - 1)];
}

function selectGameChoice(el, qi, oi) {
    document.querySelectorAll('[data-step="' + qi + '"] .game-answer-card').forEach(function(card) {
        card.classList.remove('selected');
        card.setAttribute('aria-pressed', 'false');
    });
    el.classList.add('selected');
    el.setAttribute('aria-pressed', 'true');
    mcAnswers[qi] = oi;
    updateGameStage();
}

function selectTrueFalse(el, value) {
    document.querySelectorAll('.game-answer-card').forEach(function(card) {
        card.classList.remove('selected');
        card.setAttribute('aria-pressed', 'false');
    });
    el.classList.add('selected');
    el.setAttribute('aria-pressed', 'true');
    tfAnswer = value;
    updateGameStage();
}

function isCurrentStepAnswered() {
    if (ACTIVITY_TYPE === 'multiple_choice') return mcAnswers[currentStep] !== undefined;
    if (ACTIVITY_TYPE === 'true_false') return tfAnswer !== null;
    if (ACTIVITY_TYPE === 'fill_in_blanks') {
        const input = document.querySelector('.game-question-card[data-step="' + currentStep + '"] .fib-input');
        return input && input.value.trim().length > 0;
    }
    if (ACTIVITY_TYPE === 'matching') {
        const select = document.querySelector('.game-question-card[data-step="' + currentStep + '"] .matching-select');
        return select && select.value !== '';
    }
    if (ACTIVITY_TYPE === 'image_label') {
        return Array.from(document.querySelectorAll('.label-input')).every(function(input) { return input.value.trim().length > 0; });
    }
    return true;
}

function countAnswered() {
    if (ACTIVITY_TYPE === 'multiple_choice') return Object.keys(mcAnswers).length;
    if (ACTIVITY_TYPE === 'true_false') return tfAnswer === null ? 0 : 1;
    if (ACTIVITY_TYPE === 'fill_in_blanks') return Array.from(document.querySelectorAll('.fib-input')).filter(function(input) { return input.value.trim().length > 0; }).length;
    if (ACTIVITY_TYPE === 'matching') return Array.from(document.querySelectorAll('.matching-select')).filter(function(select) { return select.value !== ''; }).length;
    if (ACTIVITY_TYPE === 'image_label') return Array.from(document.querySelectorAll('.label-input')).filter(function(input) { return input.value.trim().length > 0; }).length;
    return TOTAL_STEPS;
}

function goChallenge(direction) {
    if (direction > 0 && !isCurrentStepAnswered()) {
        Swal.fire({icon:'info',title:'Choose an answer first',text:'Pick or type your answer before moving on.',confirmButtonColor:'#1e4072'});
        return;
    }
    currentStep = Math.max(0, Math.min(TOTAL_STEPS, currentStep + direction));
    updateGameStage();
}

function collectAnswers() {
    const answers = {};
    if (ACTIVITY_TYPE === 'multiple_choice') {
        return mcAnswers;
    }
    if (ACTIVITY_TYPE === 'true_false') {
        return {0: tfAnswer};
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
        return Array.from(document.querySelectorAll('#dragList .drag-item')).map(function(item) { return parseInt(item.dataset.index, 10); });
    }
    if (ACTIVITY_TYPE === 'image_label') {
        document.querySelectorAll('.label-input').forEach(function(input) { answers[input.dataset.li] = input.value.trim(); });
        return answers;
    }
    if (ACTIVITY_TYPE === 'flashcards') {
        return {done: true};
    }
    return answers;
}

function validateAllAnswered() {
    if (ACTIVITY_TYPE === 'multiple_choice') return Object.keys(mcAnswers).length >= TOTAL_STEPS;
    if (ACTIVITY_TYPE === 'true_false') return tfAnswer !== null;
    if (ACTIVITY_TYPE === 'fill_in_blanks') return Array.from(document.querySelectorAll('.fib-input')).every(function(input) { return input.value.trim().length > 0; });
    if (ACTIVITY_TYPE === 'matching') return Array.from(document.querySelectorAll('.matching-select')).every(function(select) { return select.value !== ''; });
    if (ACTIVITY_TYPE === 'image_label') return Array.from(document.querySelectorAll('.label-input')).every(function(input) { return input.value.trim().length > 0; });
    return true;
}

function submitAnswers() {
    if (!validateAllAnswered()) {
        Swal.fire({icon:'warning',title:'Mission not finished',text:'Please answer every challenge before submitting.',confirmButtonColor:'#a01422'});
        return;
    }
    const btn = document.getElementById('submitBtn');
    if (btn) btn.disabled = true;
    Swal.fire({title:'Submitting mission...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); }});
    fetch(BASE + '/learning/activity/' + ACTIVITY_ID + '/submit', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({answers: collectAnswers()})
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

    let scoreHtml = '<p>Your mission was sent to your teacher.</p>';
    if (data.auto_score !== null && data.auto_score !== undefined) {
        const max = data.max_score || 1;
        const pct = Math.round((data.auto_score / max) * 100);
        const stars = pct >= 90 ? 3 : (pct >= 70 ? 2 : 1);
        let starHtml = '';
        for (let i = 1; i <= 3; i++) starHtml += '<span class="' + (i <= stars ? 'earned' : '') + '">★</span>';
        scoreHtml = '<div class="complete-score">' + data.auto_score + ' / ' + max + '</div><div class="stars-earned">' + starHtml + '</div><p>' + (data.message || 'Mission complete!') + '</p>';
    }

    area.style.display = 'block';
    area.innerHTML = '<section class="mission-complete-card"><div class="complete-badge"><i class="bi bi-trophy-fill"></i></div><div class="quest-eyebrow">Mission Completed</div><h2>Achievement unlocked</h2>' + scoreHtml + '<div class="game-actions center"><a class="quest-secondary-btn" href="' + BASE + '/learning/lesson/' + LESSON_PLAN_ID + '"><i class="bi bi-arrow-left"></i> Back to Lessons</a><a class="quest-primary-btn" href="' + BASE + '/learning/progress"><i class="bi bi-bar-chart-line"></i> View Progress</a></div></section>';
}

let dragSrc = null;
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.game-text-input, .game-select-input').forEach(function(field) {
        field.addEventListener('input', updateGameStage);
        field.addEventListener('change', updateGameStage);
    });

    const list = document.getElementById('dragList');
    if (list) {
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
    }

    updateGameStage();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
