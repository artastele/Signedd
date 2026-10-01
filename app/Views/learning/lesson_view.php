<?php
// Part of: SignED - Lesson Viewer (Learner Side)

$pageTitle = htmlspecialchars($lessonPlan['title'] ?? 'Lesson') . ' - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';

require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . $basePath . '/css/learner.css?v=' . time() . '">';

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

$domainLabel = $lessonPlan['pdsp_domain'] ?? 'Aralin';
$schoolYear = $lessonPlan['school_year'] ?? '';
$lessonPages = $lessonPages ?? [];
$totalPages = count($lessonPages);
$lastPageNum = max(1, min($totalPages ?: 1, (int)($pageProgress['last_page_number'] ?? 1)));
$isCompleted = !empty($pageProgress['is_completed']);
$initialPercent = $isCompleted ? 100 : ($totalPages > 0 ? round(($lastPageNum / $totalPages) * 100) : 0);

$typeMap = [
    'multiple_choice' => ['Maramihang Pagpipilian', 'ph-bold ph-list-checks', 'Pagsusulit'],
    'true_false'      => ['Tama o Mali', 'ph-bold ph-check', 'Pagtukoy ng Tama o Mali'],
    'fill_in_blanks'  => ['Punan ang Patlang', 'ph-bold ph-pencil-simple', 'Pagsusulat sa Patlang'],
    'matching'        => ['Pagtambalin', 'ph-bold ph-link', 'Pagtatambal ng mga Bagay'],
    'drag_drop_sort'  => ['Pagsasaayos', 'ph-bold ph-arrows-down-up', 'Pagsusunod-sunod'],
    'image_label'     => ['Pangalanan ang Larawan', 'ph-bold ph-tag', 'Paglalagay ng Pangalan'],
    'flashcards'      => ['Mga Flashcard', 'ph-bold ph-cards', 'Kard ng Aralin'],
    'sequencing'      => ['Pagkakasunod-sunod', 'ph-bold ph-list-numbers', 'Tamang Pagkakasunod'],
];
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page lesson-mission-page p-3 p-md-4">
    <!-- Animated Soft Ambient Background Orbs -->
    <div class="animated-bg-canvas" aria-hidden="true">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>
        <div class="ambient-orb orb-3"></div>
        <div class="ambient-orb orb-4"></div>
    </div>

    <div class="lesson-mission-shell" style="max-width: 1400px; width: 100%; margin: 0 auto; position: relative; z-index: 1;">
        
        <!-- Back Navigation Bar -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <?php if (!empty($isTeacherPreview)): ?>
                <a href="javascript:window.close();" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="background:#ffffff;" title="Isara ang Preview">
                    <i class="ph-bold ph-x"></i> Isara ang Preview
                </a>
                <span class="badge bg-primary px-2.5 py-1.5 rounded-pill fw-semibold" style="font-size:0.8rem;"><i class="ph-bold ph-eye me-1"></i> Teacher Preview</span>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="btn btn-sm btn-bubble-outline rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" style="background:#ffffff;" title="Bumalik sa Aking mga Aralin">
                    <i class="ph-bold ph-arrow-left"></i> Bumalik sa Aking mga Aralin
                </a>
            <?php endif; ?>
        </div>

        <!-- Clean Flat Header Strip -->
        <div class="bg-white rounded-3 p-3 p-md-4 mb-4 border shadow-sm" style="border-radius:14px !important; border-color:#e2e8f0 !important;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">
                <div>
                    <h1 class="fw-bold text-dark mb-1" style="font-size: 1.65rem; line-height: 1.3;">
                        <?php echo htmlspecialchars($lessonPlan['title'] ?? 'Aralin'); ?>
                    </h1>
                    <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                        Tuklasin at matuto sa pamamagitan ng masayang aralin, mga larawan, at pagsasanay.
                    </p>
                </div>
                <div>
                    <span class="badge <?php echo $isCompleted ? 'bg-success text-white' : 'bg-primary-subtle text-primary border border-primary-subtle'; ?> px-3 py-2" id="lessonStatusBadge" style="font-size: 0.85rem; border-radius:8px;">
                        <i class="ph-bold <?php echo $isCompleted ? 'ph-check-circle' : 'ph-book-open'; ?> me-1"></i>
                        <span id="lessonStatusText"><?php echo $isCompleted ? 'Tapos na ang Aralin' : 'Kasalukuyang Binabasa'; ?></span>
                    </span>
                </div>
            </div>

            <!-- Thin Clean Progress Bar -->
            <?php if ($totalPages > 0): ?>
            <div class="mt-3 pt-2">
                <div class="d-flex justify-content-between align-items-center small text-muted mb-1" style="font-size:0.85rem;">
                    <span id="lessonProgressLabel" class="fw-semibold text-dark">
                        Natapos mo na ang <strong id="progressPercentNum"><?php echo $initialPercent; ?>%</strong> ng aralin
                    </span>
                    <span id="pageCounterLabel">Pahina <strong id="currentSlideDisplay"><?php echo $lastPageNum; ?></strong> ng <strong><?php echo $totalPages; ?></strong></span>
                </div>
                <div class="progress" style="height: 6px; border-radius: 4px; background-color: #e2e8f0;">
                    <div class="progress-bar bg-success" id="lessonProgressBar" role="progressbar" style="width: <?php echo $initialPercent; ?>%;" aria-valuenow="<?php echo $initialPercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- 3 Dedicated Tabs: Slides, Materials/Guides, Activities -->
        <div class="vle-course-tabs d-flex align-items-center gap-2 mb-4 border-bottom pb-2 flex-wrap" role="tablist">
            <button type="button" class="btn btn-sm px-3 py-2 fw-bold rounded-2 vle-tab-btn <?php echo $totalPages > 0 ? 'active' : ''; ?>" id="vleTabLessons" onclick="switchCourseTab('lessons')" style="font-size:0.92rem;">
                <i class="ph-bold ph-presentation me-1 text-primary"></i> Mga Pahina ng Aralin
                <?php if (!empty($lessonPages)): ?>
                    <span class="badge bg-primary ms-1" style="font-size:0.75rem;"><?php echo count($lessonPages); ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-sm px-3 py-2 fw-bold rounded-2 vle-tab-btn <?php echo ($totalPages == 0 && $materialCount > 0) ? 'active' : 'btn-light'; ?>" id="vleTabMaterials" onclick="switchCourseTab('materials')" style="font-size:0.92rem;">
                <i class="ph-bold ph-files me-1 text-info"></i> Mga Gabay at Babasahin
                <?php if ($materialCount > 0): ?>
                    <span class="badge bg-info text-dark ms-1" style="font-size:0.75rem;"><?php echo $materialCount; ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-sm px-3 py-2 fw-bold rounded-2 vle-tab-btn btn-light" id="vleTabActivities" onclick="switchCourseTab('activities')" style="font-size:0.92rem;">
                <i class="ph-bold ph-game-controller me-1 text-success"></i> Mga Pagsasanay at Laro
                <?php if ($activityCount > 0): ?>
                    <span class="badge bg-success ms-1" style="font-size:0.75rem;"><?php echo $activityCount; ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- ====================================================
             TAB 1: LESSON SLIDES (INTERACTIVE READING)
             ==================================================== -->
        <div id="coursePanelLessons" class="course-tab-panel" style="<?php echo $totalPages == 0 ? 'display:none;' : ''; ?>">
            <?php if (!empty($lessonPages)): ?>
            <!-- Interactive Lesson Reader -->
            <section class="mb-4" id="interactiveLessonSection">
                <div class="row g-4" id="slideReaderRow">
                    <!-- Slide Content (Expands to col-12 when Talaan is collapsed!) -->
                    <div class="col-lg-8 col-xl-9" id="slideReaderCol" style="transition: all 0.25s ease;">
                        <div class="card border rounded-3 p-3 p-md-4 bg-white d-flex flex-column justify-content-between shadow-sm" style="min-height: 520px; border-radius:14px !important; border-color:#e2e8f0 !important;">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge px-3 py-1.5 rounded-pill" style="background:var(--signed-navy); font-size:0.85rem;" id="slideNumberBadge">Pahina 1</span>
                                        <h3 class="fw-bold mb-0 text-dark" id="slideTitle" style="font-size: 1.35rem;">Binabasa ang aralin...</h3>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold align-items-center gap-1.5 shadow-sm" id="btnToggleOutline" onclick="toggleSlideOutline()" title="Ipakita ang Talaan ng mga Pahina" style="display:none;">
                                        <i class="ph-bold ph-list-checks"></i>
                                        <span>Talaan</span>
                                        <i class="ph-bold ph-caret-left"></i>
                                    </button>
                                </div>

                                <!-- Media Container -->
                                <div id="slideMediaWrap" class="mb-4 text-center" style="display:none;"></div>

                                <!-- Rich Text Content (Large, High-Contrast for SPED/Kids) -->
                                <div id="slideContent" class="lesson-slide-rich-content text-dark mb-4" style="font-size: 1.25rem; line-height: 1.85;"></div>

                                <!-- Guide Questions / Reflection Prompts -->
                                <div id="slideGuideWrap" class="p-3 p-md-4 mb-3 rounded-3 bg-light border border-start border-4 border-warning" style="display:none; border-radius:10px !important;">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size:1rem;">
                                        <i class="ph-bold ph-lightbulb text-warning me-2"></i>Mga Gabay na Tanong:
                                    </h6>
                                    <div id="slideGuideQuestions" class="text-secondary" style="white-space: pre-line; font-size:1rem; line-height:1.6;"></div>
                                </div>
                            </div>

                            <!-- Footer Navigation Buttons -->
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-4 flex-wrap gap-2">
                                <button type="button" class="btn btn-bubble-outline px-4 py-2" id="btnPrevSlide" onclick="prevSlide()">
                                    <i class="ph-bold ph-arrow-left me-1"></i>Nakaraan
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-bubble px-4 py-2" id="btnNextSlide" onclick="nextSlide()">
                                        Susunod<i class="ph-bold ph-arrow-right ms-1"></i>
                                    </button>
                                    <button type="button" class="btn btn-bubble-success px-4 py-2 d-none" id="btnCompleteSlide" onclick="completeLesson()" style="display:none !important;">
                                        <i class="ph-bold ph-check-circle me-1"></i>Tapusin ang Aralin
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide Outline Menu (Right Sidebar: Collapsible to maximize reading space!) -->
                    <div class="col-lg-4 col-xl-3" id="slideOutlineCol" style="transition: all 0.25s ease;">
                        <div class="card border rounded-3 bg-white shadow-sm" style="border-radius:14px !important; border-color:#e2e8f0 !important;">
                            <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <span class="fw-bold small text-dark" style="font-size:0.9rem;"><i class="ph-bold ph-list-checks me-1 text-primary"></i>Talaan ng mga Pahina</span>
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-secondary text-white" style="font-size:0.75rem;"><?php echo $totalPages; ?> Pahina</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2.5 rounded-pill fw-semibold d-inline-flex align-items-center gap-1" onclick="toggleSlideOutline()" title="Itago ang Talaan" style="font-size:0.75rem;">
                                        <i class="ph-bold ph-caret-right"></i> Itago
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-2" style="max-height: 520px; overflow-y: auto;">
                                <div class="list-group list-group-flush" id="slideMenuList">
                                    <?php foreach ($lessonPages as $idx => $pg): 
                                        $pNum = $idx + 1;
                                        $isPast = ($pNum <= $lastPageNum || $isCompleted);
                                    ?>
                                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2.5 rounded-2 mb-1 border-0 slide-nav-item <?php echo $pNum === $lastPageNum ? 'active-slide' : ''; ?>" id="slideNavItem_<?php echo $idx; ?>" onclick="goToSlide(<?php echo $idx; ?>)" style="border-radius:6px !important;">
                                            <div class="d-flex align-items-center gap-2 min-width-0">
                                                <span class="badge rounded-circle p-1 slide-num-circle <?php echo $isPast ? 'bg-success text-white' : 'bg-light text-dark border'; ?>" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem;">
                                                    <?php echo $pNum; ?>
                                                </span>
                                                <span class="text-truncate fw-semibold slide-item-title" style="font-size:0.9rem;"><?php echo htmlspecialchars($pg['title']); ?></span>
                                            </div>
                                            <i class="ph-bold <?php echo $isPast ? 'ph-check-circle text-success' : 'ph-circle text-muted'; ?> slide-check-icon ms-1" style="font-size: 1.05rem;" aria-label="<?php echo $isPast ? 'Natapos na' : 'Hindi pa tapos'; ?>"></i>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php else: ?>
                <div class="compact-empty-state bg-white p-5 rounded-4 text-center border shadow-sm my-4">
                    <i class="ph-bold ph-slides text-muted" style="font-size:3rem;"></i>
                    <h5 class="fw-bold text-dark mt-3 mb-1">Walang mga interactive slide ang aralin na ito.</h5>
                    <p class="text-muted mb-0">Tingnan ang <strong>Mga Gabay at Babasahin</strong> o <strong>Mga Pagsasanay</strong> sa itaas.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ====================================================
             TAB 2: LEARNING GUIDES & ATTACHED MATERIALS (DEDICATED FULL-WIDTH TAB)
             ==================================================== -->
        <div id="coursePanelMaterials" class="course-tab-panel" style="<?php echo ($totalPages == 0 && $materialCount > 0) ? '' : 'display:none;'; ?>">
            <section class="mission-section" id="materials">
                <div class="mission-section-head d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="quest-eyebrow text-primary fw-bold" style="font-size:0.75rem; text-transform:uppercase;">Mga Karagdagang Babasahin</div>
                        <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem;">Mga Gabay at Dokumento</h3>
                    </div>
                    <span class="badge bg-light text-dark border"><?php echo $materialCount; ?> babasahin</span>
                </div>

                <?php if (!empty($materials)): ?>
                    <div class="material-mission-grid">
                        <?php foreach ($materials as $index => $material):
                            $materialType = $material['material_type'] ?? '';
                            $embedType = $material['embed_type'] ?? '';
                            $externalUrl = $material['external_url'] ?? '';
                            $viewerUrl = '';
                            $openUrl = '';

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
                                'file' => 'ph-bold ph-file-text',
                                'link' => 'ph-bold ph-link',
                                'embed' => 'ph-bold ph-play-circle',
                                default => 'ph-bold ph-book-open',
                            };
                        ?>
                            <article class="material-mission-card">
                                <div class="material-icon"><i class="<?php echo $icon; ?>"></i></div>
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
                                                <i class="ph-bold ph-arrows-out-simple me-1" aria-hidden="true"></i> Open Material
                                            </button>
                                        <?php elseif ($openUrl): ?>
                                            <a class="quest-primary-btn" href="<?php echo htmlspecialchars($openUrl); ?>" target="_blank" rel="noopener noreferrer">
                                                <i class="ph-bold ph-arrow-square-out me-1" aria-hidden="true"></i> Open Material
                                            </a>
                                        <?php endif; ?>
                                        <span class="status-pill"><i class="ph-bold ph-book-open me-1" aria-hidden="true"></i> Read First</span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="compact-empty-state bg-white p-5 rounded-4 text-center border shadow-sm my-4">
                        <i class="ph-bold ph-files text-muted" style="font-size:3rem;" aria-hidden="true"></i>
                        <h5 class="fw-bold text-dark mt-3 mb-1">Walang mga karagdagang babasahin.</h5>
                        <p class="text-muted mb-0">Walang naka-attach na file o video para sa araling ito.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- ====================================================
             TAB 3: ACTIVITIES & CHALLENGES
             ==================================================== -->
        <div id="coursePanelActivities" class="course-tab-panel" style="display:none;">
            <section class="mission-section" id="activities">
                <div class="mission-section-head d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="quest-eyebrow text-success fw-bold" style="font-size:0.75rem; text-transform:uppercase;">Interactive Missions</div>
                        <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem;">Activities &amp; Challenges</h3>
                    </div>
                    <span class="badge bg-light text-dark border"><?php echo $activityCount; ?> challenge<?php echo $activityCount === 1 ? '' : 's'; ?></span>
                </div>

                <?php if (!empty($activities)): ?>
                    <div class="activity-mission-grid">
                        <?php foreach ($activities as $index => $activityItem):
                            $submission = $activityItem['submission'] ?? null;
                            $isSubmitted = !empty($submission['submission_id']);
                            $isGraded = $isSubmitted && ($submission['score'] !== null || !empty($submission['is_complete']));
                            $activityType = $activityItem['activity_type'] ?? '';
                            $typeInfo = $typeMap[$activityType] ?? [ucwords(str_replace('_', ' ', $activityType)), 'ph-bold ph-list-checks'];
                            $score = $submission['score'] ?? $submission['auto_score'] ?? null;
                            $scoreMax = $submission['grade_max_score'] ?? $activityItem['max_score'] ?? 0;
                            $dueTimestamp = !empty($activityItem['due_date']) ? strtotime($activityItem['due_date']) : null;
                            $isOverdue = $dueTimestamp && $dueTimestamp < time() && !$isSubmitted;
                        ?>
                            <article class="activity-mission-card <?php echo $isSubmitted ? 'is-complete' : ''; ?>">
                                <div class="activity-card-head">
                                    <span class="mission-number">Gawain <?php echo $index + 1; ?></span>
                                    <span class="activity-type-pill"><i class="<?php echo $typeInfo[1]; ?> me-1" aria-hidden="true"></i> <?php echo htmlspecialchars($typeInfo[0]); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($activityItem['title'] ?? 'Gawain'); ?></h3>
                                <div class="activity-status-line">
                                    <?php if ($isGraded): ?>
                                        <span class="status-pill success"><i class="ph-bold ph-star text-warning me-1" aria-hidden="true"></i> Natapos Na</span>
                                    <?php elseif ($isSubmitted): ?>
                                        <span class="status-pill success"><i class="ph-bold ph-paper-plane-tilt text-primary me-1" aria-hidden="true"></i> Naisumite Na</span>
                                    <?php else: ?>
                                        <span class="status-pill"><i class="ph-bold ph-hourglass text-muted me-1" aria-hidden="true"></i> Simulan Pa Lang</span>
                                    <?php endif; ?>

                                    <?php if ($score !== null && (int)$scoreMax > 0): ?>
                                        <span class="status-pill score"><i class="ph-bold ph-trophy text-warning me-1" aria-hidden="true"></i> <?php echo (int)$score; ?>/<?php echo (int)$scoreMax; ?> puntos</span>
                                    <?php elseif ((int)($activityItem['max_score'] ?? 0) > 0): ?>
                                        <span class="status-pill score"><i class="ph-bold ph-star text-warning me-1" aria-hidden="true"></i> <?php echo (int)$activityItem['max_score']; ?> puntos</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($dueTimestamp): ?>
                                    <div class="mission-due <?php echo $isOverdue ? 'is-overdue' : ''; ?>">
                                        <i class="ph-bold ph-calendar me-1" aria-hidden="true"></i>
                                        <?php echo $isOverdue ? 'Lagpas sa Araw' : 'Takdang Araw:'; ?> <?php echo date('M j, Y', $dueTimestamp); ?>
                                    </div>
                                <?php endif; ?>

                                <a href="<?php echo htmlspecialchars($basePath); ?>/learning/activity/<?php echo (int)$activityItem['id']; ?>" class="<?php echo $isSubmitted ? 'quest-secondary-btn' : 'quest-primary-btn'; ?>">
                                    <i class="ph-bold <?php echo $isSubmitted ? 'ph-magnifying-glass' : 'ph-arrow-right'; ?> me-1" aria-hidden="true"></i>
                                    <?php echo $isSubmitted ? 'Tingnan ang Sagot' : 'Simulan ang Laro'; ?>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="compact-empty-state bg-white p-4 rounded-3 text-center border">
                        <i class="ph-bold ph-game-controller text-muted" style="font-size:2.5rem;" aria-hidden="true"></i>
                        <p class="text-muted mt-2 mb-0">No activity challenges attached to this lesson yet.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<div class="lesson-viewer-modal" id="lessonViewer" aria-hidden="true" role="dialog" aria-modal="true" style="display: none;">
    <div class="lesson-viewer-dialog">
        <div class="lesson-viewer-head">
            <strong id="lessonViewerTitle">Learning Material</strong>
            <div>
                <a id="lessonViewerNewTab" href="#" target="_blank" rel="noopener noreferrer" class="viewer-new-tab">
                    <i class="ph-bold ph-arrow-square-out me-1" aria-hidden="true"></i> Open in New Tab
                </a>
                <button type="button" class="viewer-close" onclick="closeLessonMaterial()" aria-label="Close material viewer">
                    <i class="ph-bold ph-x" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="lesson-viewer-body" id="lessonViewerBody">
            <iframe id="lessonViewerFrame" src="" title="Learning material viewer"></iframe>
            <video id="lessonViewerVideo" controls class="d-none w-100" style="max-height: 70vh;">
                <source id="lessonVideoSource" src="" type="video/mp4">
                <track id="lessonVideoTrack" src="" kind="subtitles" srclang="fil" label="Filipino / English Subtitles" default>
            </video>
        </div>
        <div class="lesson-viewer-foot">If the file does not load, open it in a new tab.</div>
    </div>
</div>

<style>
/* ── Animated Ambient Background for Learner Lesson View ── */
.lesson-mission-page {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(135deg, #f0f7ff 0%, #fdf2f8 25%, #f0fdf4 50%, #fffbeb 75%, #eff6ff 100%);
    background-size: 300% 300%;
    animation: gentleBgShift 25s ease infinite alternate;
    overflow-x: hidden;
}

@keyframes gentleBgShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.animated-bg-canvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
}

.ambient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.40;
    will-change: transform;
}

.orb-1 {
    width: 520px;
    height: 520px;
    background: radial-gradient(circle, #38bdf8 0%, rgba(56, 189, 248, 0.05) 70%);
    top: -120px;
    left: -100px;
    animation: orbDrift1 22s infinite ease-in-out alternate;
}

.orb-2 {
    width: 540px;
    height: 540px;
    background: radial-gradient(circle, #f472b6 0%, rgba(244, 114, 182, 0.05) 70%);
    bottom: 5%;
    right: -120px;
    animation: orbDrift2 26s infinite ease-in-out alternate;
}

.orb-3 {
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, #34d399 0%, rgba(52, 211, 153, 0.05) 70%);
    top: 38%;
    left: 18%;
    animation: orbDrift3 28s infinite ease-in-out alternate;
}

.orb-4 {
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, #fbbf24 0%, rgba(251, 191, 36, 0.05) 70%);
    top: 15%;
    right: 15%;
    animation: orbDrift4 24s infinite ease-in-out alternate;
}

@keyframes orbDrift1 {
    0% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(70px, 90px) scale(1.12); }
    100% { transform: translate(-40px, 50px) scale(0.92); }
}

@keyframes orbDrift2 {
    0% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-90px, -70px) scale(1.15); }
    100% { transform: translate(50px, -40px) scale(0.9); }
}

@keyframes orbDrift3 {
    0% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-60px, 70px) scale(1.08); }
    100% { transform: translate(80px, -50px) scale(0.95); }
}

@keyframes orbDrift4 {
    0% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(60px, -60px) scale(1.1); }
    100% { transform: translate(-70px, 40px) scale(0.9); }
}

.lesson-mission-shell {
    position: relative;
    z-index: 1;
}

/* ── Suppress footer in learner mission view ── */
footer,
body.learner-layout-active footer {
    display: none !important;
}

/* ── Tabs Navigation ── */
.vle-tab-btn {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.vle-tab-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
    border-color: #cbd5e1;
}
.vle-tab-btn.active {
    background: #1e4072 !important;
    color: #ffffff !important;
    border-color: #1e4072 !important;
    box-shadow: 0 3px 8px rgba(30, 64, 114, 0.25);
}
.vle-tab-btn.active .badge {
    background-color: #ffffff !important;
    color: #1e4072 !important;
}

/* ── Roomy & Clean Material Mission Grid (Dedicated Tab) ── */
.material-mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.25rem;
    width: 100%;
}

.material-mission-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.4rem;
    display: flex;
    gap: 1.15rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.material-mission-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.07);
    border-color: #cbd5e1;
}

.material-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: #eff6ff;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
    border: 1px solid #bae6fd;
}

.material-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.material-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.4rem;
}

.material-type-badge {
    font-size: 0.72rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #475569;
    padding: 2px 10px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
}

.material-body h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.35rem;
    line-height: 1.35;
}

.material-body p {
    font-size: 0.88rem;
    color: #64748b;
    margin-bottom: 0.85rem;
    line-height: 1.5;
}

.material-card-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
    margin-top: auto;
}

/* Rich Text Tables & Inline Images in Lesson Slides (High Accessibility for PWD Learners) */
.lesson-slide-rich-content {
    font-size: 1.15rem !important;
    line-height: 1.9 !important;
    color: #1e293b;
}
.lesson-slide-rich-content table {
    width: 100% !important;
    margin: 1.5rem 0 !important;
    border-collapse: collapse !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 10px !important;
    overflow: hidden !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
}
.lesson-slide-rich-content table th,
.lesson-slide-rich-content table td {
    padding: 0.9rem 1.15rem !important;
    border: 1px solid #e2e8f0 !important;
    font-size: 1rem !important;
}
.lesson-slide-rich-content table th {
    background-color: #f1f5f9 !important;
    font-weight: 700 !important;
    color: #0f172a !important;
}
.lesson-slide-rich-content table tr:nth-child(even) td {
    background-color: #f8fafc !important;
}
.lesson-slide-rich-content img {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 8px !important;
    margin: 0.75rem 0 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
}
.lesson-slide-rich-content h1,
.lesson-slide-rich-content h2,
.lesson-slide-rich-content h3 {
    font-weight: 700;
    color: #0f172a;
    margin-top: 1.25rem;
    margin-bottom: 0.75rem;
}
.lesson-slide-rich-content ul,
.lesson-slide-rich-content ol {
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

/* Inline FSL Highlighted Word in Lesson Text */
.lesson-slide-rich-content .fsl-word {
    display: inline-flex;
    align-items: center;
    background: #fef3c7;
    color: #92400e;
    font-weight: 700;
    padding: 1px 8px;
    margin: 0 2px;
    border-radius: 6px;
    border-bottom: 2px solid #f59e0b;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}
.lesson-slide-rich-content .fsl-word:hover {
    background: #fde68a;
    color: #78350f;
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(245, 158, 11, 0.35);
}
.lesson-slide-rich-content .fsl-word::after {
    content: "\e580";
    font-family: "Phosphor-Bold" !important;
    font-size: 0.85em;
    margin-left: 4px;
    vertical-align: -0.05em;
    display: inline-block;
}
</style>

<script>
// ================================================================
// TAB SWITCHER LOGIC (SLIDES, MATERIALS/GUIDES, ACTIVITIES)
// ================================================================
function switchCourseTab(tabName) {
    var tabs = ['lessons', 'materials', 'activities'];
    tabs.forEach(function(t) {
        var btn = document.getElementById('vleTab' + t.charAt(0).toUpperCase() + t.slice(1));
        var panel = document.getElementById('coursePanel' + t.charAt(0).toUpperCase() + t.slice(1));
        if (btn) btn.classList.remove('active');
        if (panel) panel.style.display = 'none';
    });

    var activeBtn = document.getElementById('vleTab' + tabName.charAt(0).toUpperCase() + tabName.slice(1));
    var activePanel = document.getElementById('coursePanel' + tabName.charAt(0).toUpperCase() + tabName.slice(1));
    if (activeBtn) activeBtn.classList.add('active');
    if (activePanel) activePanel.style.display = 'block';

}

// ================================================================
// COLLAPSIBLE TALAAN NG MGA PAHINA (MAXIMIZE SLIDE READING VIEW)
// ================================================================
let isOutlineVisible = true;
function toggleSlideOutline() {
    isOutlineVisible = !isOutlineVisible;
    const outlineCol = document.getElementById('slideOutlineCol');
    const readerCol = document.getElementById('slideReaderCol');
    const reopenBtn = document.getElementById('btnToggleOutline');

    if (isOutlineVisible) {
        if (outlineCol) outlineCol.classList.remove('d-none');
        if (readerCol) {
            readerCol.classList.remove('col-12');
            readerCol.classList.add('col-lg-8', 'col-xl-9');
        }
        if (reopenBtn) reopenBtn.style.display = 'none';
    } else {
        if (outlineCol) outlineCol.classList.add('d-none');
        if (readerCol) {
            readerCol.classList.remove('col-lg-8', 'col-xl-9');
            readerCol.classList.add('col-12');
        }
        if (reopenBtn) reopenBtn.style.display = 'inline-flex';
    }
}

// ================================================================
// MULTI-PAGE LESSON SLIDE VIEWER LOGIC
// ================================================================
<?php if (!empty($lessonPages)): ?>
const LESSON_PAGES = <?php echo json_encode($lessonPages); ?>;
const LESSON_PLAN_ID = <?php echo (int)($lessonPlan['id'] ?? 0); ?>;
const BASE = <?php echo json_encode($basePath); ?>;

let currentSlideIdx = Math.max(0, Math.min(LESSON_PAGES.length - 1, <?php echo (int)($pageProgress['last_page_number'] ?? 1) - 1; ?>));
let isLessonFinished = <?php echo !empty($pageProgress['is_completed']) ? 'true' : 'false'; ?>;
let highestSlideReached = currentSlideIdx;

const ALL_FSL_SIGNS = <?php echo json_encode($fslSigns ?? []); ?>;

// Build vocabulary matching dictionary (supports English word and Tagalog description keywords)
const SIGN_DICT = [];
if (ALL_FSL_SIGNS && ALL_FSL_SIGNS.length > 0) {
    ALL_FSL_SIGNS.forEach(s => {
        if (!s.word) return;
        const w = s.word.trim();
        SIGN_DICT.push({
            matchWord: w,
            targetSign: s
        });

        if (s.description && s.description.includes('-')) {
            const tagWord = s.description.split('-')[0].trim();
            if (tagWord && tagWord.length > 2 && !SIGN_DICT.some(x => x.matchWord.toLowerCase() === tagWord.toLowerCase())) {
                SIGN_DICT.push({
                    matchWord: tagWord,
                    targetSign: s
                });
            }
        }
    });
    // Sort descending by length so longer phrases (e.g. "anak na lalaki", "good morning") match first
    SIGN_DICT.sort((a, b) => b.matchWord.length - a.matchWord.length);
}

function highlightFslKeywords(rawHtml) {
    if (!rawHtml) return '';
    let content = rawHtml;

    // If plain text without HTML tags, wrap newlines in paragraphs
    if (!/<[a-z][\s\S]*>/i.test(content)) {
        content = content.split(/\n\n+/).map(p => `<p>${p.replace(/\n/g, '<br>')}</p>`).join('');
    }

    // Replace any explicit [fsl:word] tag
    content = content.replace(/\[fsl:([^\]]+)\]/gi, function(match, word) {
        const w = word.trim();
        const found = SIGN_DICT.find(x => x.matchWord.toLowerCase() === w.toLowerCase());
        const vid = found ? (found.targetSign.video_path || '') : '';
        const cat = found ? (found.targetSign.category || 'General') : 'General';
        const desc = found ? (found.targetSign.description || '') : '';
        return `<span class="fsl-word" data-word="${w.toLowerCase()}" data-video="${vid}" data-category="${cat}" data-desc="${desc}" title="Panoorin ang FSL Sign para sa: ${w}">${w}</span>`;
    });

    if (SIGN_DICT.length === 0) return content;

    // Automatically detect and highlight words with FSL signs on learner side
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;

    function walk(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            let text = node.nodeValue || '';
            if (!text || !text.trim()) return;

            const normalizedText = text.replace(/\u00A0/g, ' ');

            for (const item of SIGN_DICT) {
                const searchWord = item.matchWord.trim();
                if (!searchWord) continue;

                const escaped = searchWord.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace(/\s+/g, '[\\s\\u00A0]+');
                const regex = new RegExp('(^|[\\s\\u00A0,.!?;:\'"\\(\\)\\[\\]{}<>-])(' + escaped + ')(?=$|[\\s\\u00A0,.!?;:\'"\\(\\)\\[\\]{}<>-])', 'i');
                const match = regex.exec(normalizedText);

                if (match) {
                    const matchIndex = match.index + match[1].length;
                    const matchedStr = match[2];

                    const beforeText = text.substring(0, matchIndex);
                    const afterText = text.substring(matchIndex + matchedStr.length);

                    const span = document.createElement('span');
                    span.className = 'fsl-word';
                    span.dataset.word = item.targetSign.word.toLowerCase();
                    span.dataset.video = item.targetSign.video_path || '';
                    span.dataset.category = item.targetSign.category || 'General';
                    span.dataset.desc = item.targetSign.description || '';
                    span.title = 'Panoorin ang FSL Sign: ' + item.targetSign.word;
                    span.textContent = matchedStr;

                    const parent = node.parentNode;
                    if (beforeText) {
                        parent.insertBefore(document.createTextNode(beforeText), node);
                    }
                    parent.insertBefore(span, node);

                    if (afterText) {
                        const nextNode = document.createTextNode(afterText);
                        parent.insertBefore(nextNode, node);
                        parent.removeChild(node);
                        walk(nextNode);
                    } else {
                        parent.removeChild(node);
                    }
                    return;
                }
            }
        } else if (node.nodeType === Node.ELEMENT_NODE) {
            if (node.classList.contains('fsl-word') || (node.tagName === 'BUTTON' && node.classList.contains('fsl-word'))) {
                return;
            }
            if (node.tagName === 'SCRIPT' || node.tagName === 'STYLE') {
                return;
            }
            const children = Array.from(node.childNodes);
            for (const child of children) {
                walk(child);
            }
        }
    }

    walk(tempDiv);
    return tempDiv.innerHTML;
}

function renderSlide(idx) {
    if (!LESSON_PAGES || LESSON_PAGES.length === 0) return;
    currentSlideIdx = Math.max(0, Math.min(LESSON_PAGES.length - 1, idx));
    highestSlideReached = Math.max(highestSlideReached, currentSlideIdx);
    
    const page = LESSON_PAGES[currentSlideIdx];
    const total = LESSON_PAGES.length;
    const pageNum = currentSlideIdx + 1;

    // Badges & Titles
    document.getElementById('slideNumberBadge').textContent = 'Pahina ' + pageNum;
    document.getElementById('slideTitle').textContent = page.title || ('Pahina ' + pageNum);
    document.getElementById('currentSlideDisplay').textContent = pageNum;
    
    // Render rich HTML content & highlight inline FSL signs
    const slideContentEl = document.getElementById('slideContent');
    slideContentEl.innerHTML = highlightFslKeywords(page.content || '');

    // Guide Questions
    const guideWrap = document.getElementById('slideGuideWrap');
    const guideText = document.getElementById('slideGuideQuestions');
    if (page.guide_questions && page.guide_questions.trim()) {
        guideText.textContent = page.guide_questions;
        guideWrap.style.display = 'block';
    } else {
        guideWrap.style.display = 'none';
    }

    // Media
    const mediaWrap = document.getElementById('slideMediaWrap');
    mediaWrap.innerHTML = '';
    if (page.media_type === 'image' && page.media_path) {
        const imgUrl = BASE + '/' + page.media_path.replace(/^\//, '');
        mediaWrap.innerHTML = `<img src="${imgUrl}" class="img-fluid rounded shadow-sm mb-3" style="max-height: 400px; object-fit: contain;" alt="${page.title}">`;
        mediaWrap.style.display = 'block';
    } else if (page.media_type === 'video' && page.media_path) {
        const vidUrl = BASE + '/' + page.media_path.replace(/^\//, '');
        mediaWrap.innerHTML = `<video controls class="w-100 rounded shadow-sm mb-3" style="max-height: 420px;"><source src="${vidUrl}" type="video/mp4">Hindi suportado ng browser ang video.</video>`;
        mediaWrap.style.display = 'block';
    } else if (page.media_type === 'embed' && page.media_path) {
        let embedUrl = page.media_path;
        const ytMatch = embedUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
        if (ytMatch) embedUrl = `https://www.youtube.com/embed/${ytMatch[1]}`;
        const gdMatch = embedUrl.match(/\/d\/([^/]+)/);
        if (gdMatch && embedUrl.includes('drive.google.com')) embedUrl = `https://drive.google.com/file/d/${gdMatch[1]}/preview`;
        mediaWrap.innerHTML = `<div class="ratio ratio-16x9 mb-3 rounded shadow-sm overflow-hidden"><iframe src="${embedUrl}" allowfullscreen frameborder="0"></iframe></div>`;
        mediaWrap.style.display = 'block';
    } else {
        mediaWrap.style.display = 'none';
    }

    // Buttons
    const btnPrev = document.getElementById('btnPrevSlide');
    const btnNext = document.getElementById('btnNextSlide');
    const btnComplete = document.getElementById('btnCompleteSlide');

    btnPrev.disabled = (currentSlideIdx === 0);

    if (currentSlideIdx === total - 1) {
        btnNext.style.setProperty('display', 'none', 'important');
        btnNext.classList.add('d-none');
        btnComplete.style.setProperty('display', 'inline-flex', 'important');
        btnComplete.classList.remove('d-none');
    } else {
        btnNext.style.setProperty('display', 'inline-flex', 'important');
        btnNext.classList.remove('d-none');
        btnComplete.style.setProperty('display', 'none', 'important');
        btnComplete.classList.add('d-none');
    }

    // Progress Bar & Percentage
    const completedPages = isLessonFinished ? total : Math.max(pageNum, highestSlideReached + 1);
    const percent = isLessonFinished ? 100 : Math.min(100, Math.round((completedPages / total) * 100));
    
    document.getElementById('lessonProgressBar').style.width = percent + '%';
    document.getElementById('progressPercentNum').textContent = percent + '%';
    const topbarPercent = document.getElementById('topbarPercentText');
    if (topbarPercent) topbarPercent.textContent = percent + '%';

    // Update Menu Items
    LESSON_PAGES.forEach((_, i) => {
        const navItem = document.getElementById('slideNavItem_' + i);
        if (!navItem) return;
        if (i === currentSlideIdx) {
            navItem.classList.add('bg-light', 'border-start', 'border-4', 'border-primary');
        } else {
            navItem.classList.remove('bg-light', 'border-start', 'border-4', 'border-primary');
        }

        const isVisited = isLessonFinished || (i <= highestSlideReached);
        const icon = navItem.querySelector('.slide-check-icon');
        const numCircle = navItem.querySelector('.slide-num-circle');
        if (icon && isVisited) {
            icon.className = 'bi bi-check-circle-fill text-success slide-check-icon ms-1';
        }
        if (numCircle && isVisited) {
            numCircle.className = 'badge rounded-circle p-1 slide-num-circle bg-success text-white';
        }
    });

    // Save reading progress in background
    saveProgress(pageNum, false);
}

function nextSlide() {
    if (currentSlideIdx < LESSON_PAGES.length - 1) {
        renderSlide(currentSlideIdx + 1);
        document.getElementById('interactiveLessonSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function prevSlide() {
    if (currentSlideIdx > 0) {
        renderSlide(currentSlideIdx - 1);
        document.getElementById('interactiveLessonSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function goToSlide(idx) {
    renderSlide(idx);
    document.getElementById('interactiveLessonSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function completeLesson() {
    isLessonFinished = true;
    saveProgress(LESSON_PAGES.length, true);

    document.getElementById('lessonProgressBar').style.width = '100%';
    document.getElementById('progressPercentNum').textContent = '100%';
    const topbarPercent = document.getElementById('topbarPercentText');
    if (topbarPercent) topbarPercent.textContent = '100%';
    const badge = document.getElementById('lessonStatusBadge');
    if (badge) {
        badge.className = 'badge bg-success px-3 py-2';
        document.getElementById('lessonStatusText').textContent = 'Tapos na ang Aralin';
    }

    // Mark all menu checkmarks green
    LESSON_PAGES.forEach((_, i) => {
        const navItem = document.getElementById('slideNavItem_' + i);
        if (navItem) {
            const icon = navItem.querySelector('.slide-check-icon');
            const numCircle = navItem.querySelector('.slide-num-circle');
            if (icon) icon.className = 'ph-bold ph-check-circle text-success slide-check-icon ms-1';
            if (numCircle) numCircle.className = 'badge rounded-circle p-1 slide-num-circle bg-success text-white';
        }
    });

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '🎉 Magaling!',
            html: '<p class="mb-1 fs-5 fw-semibold text-dark">Natapos mo ang aralin!</p><p class="text-muted small mb-0">Gusto mo na bang maglaro sa pagsasanay?</p>',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: '<i class="ph-bold ph-game-controller me-1"></i> Simulan ang Laro',
            cancelButtonText: 'Mamaya Na',
            customClass: {
                popup: 'rounded-4 shadow-lg p-3',
                confirmButton: 'btn btn-success px-4 py-2.5 rounded-pill fw-bold fs-6 shadow-sm mx-1',
                cancelButton: 'btn btn-light border px-3 py-2.5 rounded-pill text-secondary mx-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                switchCourseTab('activities');
                const actSection = document.getElementById('activities');
                if (actSection) {
                    actSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    }
}

function saveProgress(pageNum, isCompleted) {
    const fd = new FormData();
    fd.append('page_number', pageNum);
    if (isCompleted) {
        fd.append('is_completed', '1');
    }

    fetch(BASE + '/learning/lesson/' + LESSON_PLAN_ID + '/progress', {
        method: 'POST',
        body: fd
    }).catch(e => console.error('Error saving progress:', e));
}

// Keyboard Navigation (ArrowLeft / ArrowRight)
document.addEventListener('keydown', function(e) {
    if (document.activeElement && ['input', 'textarea'].includes(document.activeElement.tagName.toLowerCase())) return;
    if (e.key === 'ArrowRight') nextSlide();
    if (e.key === 'ArrowLeft') prevSlide();
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    renderSlide(currentSlideIdx);
});
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    <?php if (empty($lessonPages) && $materialCount > 0): ?>
    switchCourseTab('materials');
    <?php elseif (empty($lessonPages) && $activityCount > 0): ?>
    switchCourseTab('activities');
    <?php endif; ?>
});

// ================================================================
// MATERIAL VIEWER MODAL
// ================================================================
function openLessonMaterial(button) {
    var modal = document.getElementById('lessonViewer');
    var frame = document.getElementById('lessonViewerFrame');
    var video = document.getElementById('lessonViewerVideo');
    var vSource = document.getElementById('lessonVideoSource');
    var vTrack = document.getElementById('lessonVideoTrack');
    var title = document.getElementById('lessonViewerTitle');
    var newTab = document.getElementById('lessonViewerNewTab');
    var viewerUrl = button.getAttribute('data-viewer-url') || '';
    var openUrl = button.getAttribute('data-open-url') || viewerUrl;
    var captionUrl = button.getAttribute('data-caption-url') || '';

    title.textContent = button.getAttribute('data-title') || 'Learning Material';
    newTab.href = openUrl;

    if (viewerUrl.match(/\.(mp4|webm|ogg|mov)$/i)) {
        frame.classList.add('d-none');
        frame.src = '';
        video.classList.remove('d-none');
        vSource.src = viewerUrl;
        if (captionUrl) {
            vTrack.src = captionUrl;
            vTrack.mode = 'showing';
        } else {
            vTrack.src = '';
        }
        video.load();
    } else {
        video.classList.add('d-none');
        video.pause();
        frame.classList.remove('d-none');
        frame.src = viewerUrl;
    }

    modal.style.display = 'flex';
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeLessonMaterial() {
    var modal = document.getElementById('lessonViewer');
    var frame = document.getElementById('lessonViewerFrame');
    var video = document.getElementById('lessonViewerVideo');
    modal.classList.remove('open');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    frame.src = '';
    if (video) {
        video.pause();
        video.classList.add('d-none');
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeLessonMaterial();
});

document.getElementById('lessonViewer').addEventListener('click', function(event) {
    if (event.target === this) closeLessonMaterial();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
