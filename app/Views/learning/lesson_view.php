<?php
// Part of: SignED - Lesson Viewer (Learner Side)

$pageTitle = htmlspecialchars($lessonPlan['title'] ?? 'Lesson') . ' - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';

require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . $basePath . '/css/learner.css">';

function ytNocookie(string $url): string {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $match)) {
        return 'https://www.youtube-nocookie.com/embed/' . $match[1] . '?rel=0';
    }
    return $url;
}

function gdPreview(string $url): string {
    if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $match)) {
        return 'https://drive.google.com/file/d/' . $match[1] . '/preview';
    }
    return $url;
}

$materialCount = count($materials ?? []);
$activityCount = count($activities ?? []);
$completedCount = 0;
foreach (($activities ?? []) as $activityItem) {
    if (!empty($activityItem['submission']['submission_id'])) {
        $completedCount++;
    }
}

$domainLabel = $lessonPlan['pdsp_domain'] ?? 'Learning';
$schoolYear = $lessonPlan['school_year'] ?? '';
$typeMap = [
    'multiple_choice' => ['Multiple Choice', 'bi-list-check'],
    'true_false' => ['True / False', 'bi-check2-square'],
    'fill_in_blanks' => ['Fill in Blanks', 'bi-pencil-square'],
    'matching' => ['Matching', 'bi-arrows-angle-contract'],
    'drag_drop_sort' => ['Sort', 'bi-arrow-down-up'],
    'image_label' => ['Image Label', 'bi-image'],
    'flashcards' => ['Flashcards', 'bi-card-text'],
    'sequencing' => ['Sequencing', 'bi-sort-numeric-down'],
];
?>
<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page lesson-mission-page">
    <div class="lesson-mission-shell">
        <div class="lesson-page-head">
            <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="mission-back-link">
                <i class="bi bi-arrow-left"></i> Back to My Lessons
            </a>
            <a href="<?php echo htmlspecialchars($basePath); ?>/learning/progress" class="quest-secondary-btn">
                <i class="bi bi-bar-chart-line"></i> View Progress
            </a>
        </div>

        <section class="lesson-hero-card">
            <div>
                <div class="quest-eyebrow">Learning Mission Hub</div>
                <h1><?php echo htmlspecialchars($lessonPlan['title'] ?? 'Lesson'); ?></h1>
                <p>Complete your learning mission today.</p>
                <div class="lesson-badge-row">
                    <span><i class="bi bi-bookmark-star"></i> <?php echo htmlspecialchars($domainLabel); ?></span>
                    <?php if (!empty($schoolYear)): ?>
                        <span><i class="bi bi-calendar3"></i> SY <?php echo htmlspecialchars($schoolYear); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="lesson-hero-stats">
                <div><strong><?php echo $materialCount; ?></strong><span>Materials</span></div>
                <div><strong><?php echo $activityCount; ?></strong><span>Activities</span></div>
                <div><strong><?php echo $completedCount; ?></strong><span>Completed</span></div>
            </div>
        </section>

        <section class="learning-path-card" aria-label="Learning path">
            <div class="path-step is-active"><span>1</span><strong>Read Material</strong></div>
            <div class="path-step"><span>2</span><strong>Start Activity</strong></div>
            <div class="path-step"><span>3</span><strong>Complete Mission</strong></div>
            <div class="path-step"><span>4</span><strong>View Progress</strong></div>
        </section>

        <section class="mission-section" id="materials">
            <div class="mission-section-head">
                <div>
                    <div class="quest-eyebrow">Learning Guides</div>
                    <h2>Materials</h2>
                </div>
                <span><?php echo $materialCount; ?> item<?php echo $materialCount === 1 ? '' : 's'; ?></span>
            </div>

            <?php if (!empty($materials)): ?>
                <div class="material-mission-grid">
                    <?php foreach ($materials as $index => $material):
                        $materialType = $material['material_type'] ?? '';
                        $embedType = $material['embed_type'] ?? '';
                        $externalUrl = $material['external_url'] ?? '';
                        $viewerUrl = '';
                        $openUrl = '';
                        $viewerType = 'iframe';

                        if ($materialType === 'file' && !empty($material['file_path'])) {
                            $viewerUrl = $basePath . '/file/view/lesson_material/' . (int)$material['id'];
                            $openUrl = $viewerUrl;
                        } elseif ($materialType === 'embed' && $embedType === 'youtube' && $externalUrl) {
                            $viewerUrl = ytNocookie($externalUrl);
                            $openUrl = $externalUrl;
                        } elseif ($materialType === 'embed' && $embedType === 'gdrive' && $externalUrl) {
                            $viewerUrl = gdPreview($externalUrl);
                            $openUrl = $externalUrl;
                        } elseif ($externalUrl) {
                            $openUrl = $externalUrl;
                        }

                        $icon = match ($materialType) {
                            'file' => 'bi-file-earmark-pdf',
                            'link' => 'bi-link-45deg',
                            'embed' => 'bi-play-btn',
                            default => 'bi-journal-text',
                        };
                    ?>
                        <article class="material-mission-card">
                            <div class="material-icon"><i class="bi <?php echo $icon; ?>"></i></div>
                            <div class="material-body">
                                <div class="material-card-top">
                                    <span class="mission-number">Guide <?php echo $index + 1; ?></span>
                                    <span class="material-type-badge"><?php echo htmlspecialchars(ucfirst($materialType ?: 'Material')); ?><?php echo $embedType ? ' / ' . htmlspecialchars(ucfirst($embedType)) : ''; ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($material['title'] ?? 'Learning Material'); ?></h3>
                                <?php if (!empty($material['description'])): ?>
                                    <p><?php echo htmlspecialchars($material['description']); ?></p>
                                <?php else: ?>
                                    <p>Open this learning guide before starting your mission.</p>
                                <?php endif; ?>
                                <div class="material-card-actions">
                                    <?php if ($viewerUrl): ?>
                                        <button type="button" class="quest-primary-btn" data-viewer-url="<?php echo htmlspecialchars($viewerUrl); ?>" data-open-url="<?php echo htmlspecialchars($openUrl); ?>" data-title="<?php echo htmlspecialchars($material['title'] ?? 'Material'); ?>" onclick="openLessonMaterial(this)">
                                            <i class="bi bi-arrows-fullscreen"></i> Open Material
                                        </button>
                                    <?php elseif ($openUrl): ?>
                                        <a class="quest-primary-btn" href="<?php echo htmlspecialchars($openUrl); ?>" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-box-arrow-up-right"></i> Open Material
                                        </a>
                                    <?php endif; ?>
                                    <span class="status-pill"><i class="bi bi-unlock"></i> Read First</span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="compact-empty-state">
                    <i class="bi bi-folder2-open"></i>
                    <p>No learning materials are attached to this lesson yet.</p>
                </div>
            <?php endif; ?>
        </section>

        <section class="mission-section" id="activities">
            <div class="mission-section-head">
                <div>
                    <div class="quest-eyebrow">Activity Missions</div>
                    <h2>Challenges</h2>
                </div>
                <span><?php echo $activityCount; ?> mission<?php echo $activityCount === 1 ? '' : 's'; ?></span>
            </div>

            <?php if (!empty($activities)): ?>
                <div class="activity-mission-grid">
                    <?php foreach ($activities as $index => $activityItem):
                        $submission = $activityItem['submission'] ?? null;
                        $isSubmitted = !empty($submission['submission_id']);
                        $isGraded = $isSubmitted && ($submission['score'] !== null || !empty($submission['is_complete']));
                        $activityType = $activityItem['activity_type'] ?? '';
                        $typeInfo = $typeMap[$activityType] ?? [ucwords(str_replace('_', ' ', $activityType)), 'bi-puzzle'];
                        $score = $submission['score'] ?? $submission['auto_score'] ?? null;
                        $scoreMax = $submission['grade_max_score'] ?? $activityItem['max_score'] ?? 0;
                        $dueTimestamp = !empty($activityItem['due_date']) ? strtotime($activityItem['due_date']) : null;
                        $isOverdue = $dueTimestamp && $dueTimestamp < time() && !$isSubmitted;
                    ?>
                        <article class="activity-mission-card <?php echo $isSubmitted ? 'is-complete' : ''; ?>">
                            <div class="activity-card-head">
                                <span class="mission-number">Mission <?php echo $index + 1; ?></span>
                                <span class="activity-type-pill"><i class="bi <?php echo $typeInfo[1]; ?>"></i> <?php echo htmlspecialchars($typeInfo[0]); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($activityItem['title'] ?? 'Activity'); ?></h3>
                            <div class="activity-status-line">
                                <?php if ($isGraded): ?>
                                    <span class="status-pill success"><i class="bi bi-star-fill"></i> Completed</span>
                                <?php elseif ($isSubmitted): ?>
                                    <span class="status-pill success"><i class="bi bi-check-circle-fill"></i> Submitted</span>
                                <?php else: ?>
                                    <span class="status-pill"><i class="bi bi-circle"></i> Not Started</span>
                                <?php endif; ?>

                                <?php if ($score !== null && (int)$scoreMax > 0): ?>
                                    <span class="status-pill score"><i class="bi bi-trophy"></i> <?php echo (int)$score; ?>/<?php echo (int)$scoreMax; ?></span>
                                <?php elseif ((int)($activityItem['max_score'] ?? 0) > 0): ?>
                                    <span class="status-pill score"><i class="bi bi-gem"></i> <?php echo (int)$activityItem['max_score']; ?> pts</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($dueTimestamp): ?>
                                <div class="mission-due <?php echo $isOverdue ? 'is-overdue' : ''; ?>">
                                    <i class="bi bi-clock"></i>
                                    <?php echo $isOverdue ? 'Overdue' : 'Due'; ?> <?php echo date('M j, Y', $dueTimestamp); ?>
                                </div>
                            <?php endif; ?>

                            <a href="<?php echo htmlspecialchars($basePath); ?>/learning/activity/<?php echo (int)$activityItem['id']; ?>" class="<?php echo $isSubmitted ? 'quest-secondary-btn' : 'quest-primary-btn'; ?>">
                                <i class="bi <?php echo $isSubmitted ? 'bi-eye' : 'bi-play-circle-fill'; ?>"></i>
                                <?php echo $isSubmitted ? 'Review Mission' : 'Start Mission'; ?>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="compact-empty-state">
                    <i class="bi bi-controller"></i>
                    <p>No activity missions have been added to this lesson yet.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<div class="lesson-viewer-modal" id="lessonViewer" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="lesson-viewer-dialog">
        <div class="lesson-viewer-head">
            <strong id="lessonViewerTitle">Learning Material</strong>
            <div>
                <a id="lessonViewerNewTab" href="#" target="_blank" rel="noopener noreferrer" class="viewer-new-tab">
                    <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                </a>
                <button type="button" class="viewer-close" onclick="closeLessonMaterial()" aria-label="Close material viewer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <div class="lesson-viewer-body">
            <iframe id="lessonViewerFrame" src="" title="Learning material viewer"></iframe>
        </div>
        <div class="lesson-viewer-foot">If the file does not load, open it in a new tab.</div>
    </div>
</div>

<script>
function openLessonMaterial(button) {
    var modal = document.getElementById('lessonViewer');
    var frame = document.getElementById('lessonViewerFrame');
    var title = document.getElementById('lessonViewerTitle');
    var newTab = document.getElementById('lessonViewerNewTab');
    var viewerUrl = button.getAttribute('data-viewer-url') || '';
    var openUrl = button.getAttribute('data-open-url') || viewerUrl;

    title.textContent = button.getAttribute('data-title') || 'Learning Material';
    frame.src = viewerUrl;
    newTab.href = openUrl;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeLessonMaterial() {
    var modal = document.getElementById('lessonViewer');
    var frame = document.getElementById('lessonViewerFrame');
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    frame.src = '';
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeLessonMaterial();
});

document.getElementById('lessonViewer').addEventListener('click', function(event) {
    if (event.target === this) closeLessonMaterial();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
