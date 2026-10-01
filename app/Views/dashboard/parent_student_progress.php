<?php
// Part of: SignED — Parent Oversight & Child Learning Tracking
// Process 7: Step 17 — Detailed Parent Tracking Dossier (English Main + Tagalog Subtitles/Italics)

$pageTitle = htmlspecialchars($studentName) . " - Detailed Learning Progress — SignED";
require_once __DIR__ . '/../layouts/header.php';

$domainNameMap = [
    'daily_living_skills' => 'Daily Living Skills (Pang-araw-araw na Pamumuhay)',
    'perceptuo_cognitive' => 'Perceptuo-Cognitive (Perceptuo-Kognitibo)',
    'socio_emotional'     => 'Socio-Emotional (Sosyo-Emosyonal)',
    'communication_fsl'   => 'Communication & FSL (Komunikasyon at Sign Language)',
    'motor_skills'        => 'Motor Skills (Kasanayang Motor)',
];
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content p-3 p-md-4">
    <!-- Breadcrumbs & Navigation -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/parent/child-progress" class="text-decoration-none text-muted">Learner Progress</a></li>
                    <li class="breadcrumb-item active fw-semibold" aria-current="page"><?php echo htmlspecialchars($studentName); ?></li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <span class="text-primary"><i class="bi bi-person-lines-fill"></i></span>
                <?php echo htmlspecialchars($studentName); ?> — Detailed Learning Dossier
                <small class="text-muted fst-italic fw-normal fs-6 d-none d-md-inline">(Detalyadong Pagsubaybay)</small>
            </h1>
            <p class="text-muted small mb-0 mt-1">
                Complete records of lesson slides, story reading progress, quiz scores, and attempts for parent guidance.
                <span class="d-block text-muted fst-italic" style="font-size: 0.75rem;">(Lahat ng datos, slide reading progress, activity scores, at metric tracking para sa gabay ng magulang.)</span>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?php echo $basePath; ?>/parent/child-progress" class="btn btn-outline-secondary px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500; height: 38px;">
                <i class="bi bi-arrow-left"></i>
                <span>Back to List <small class="fst-italic text-muted" style="font-size: 0.72rem;">(Bumalik)</small></span>
            </a>
            <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>" class="btn btn-outline-primary px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500; height: 38px;">
                <i class="bi bi-file-earmark-medical"></i>
                <span>DepEd SF9 Report Card <small class="fst-italic text-muted" style="font-size: 0.72rem;">(SF9)</small></span>
            </a>
        </div>
    </div>

    <!-- Student Profile Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e4072 0%, #2a5298 100%); color: #fff;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-7 col-lg-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-2 flex-shrink-0 shadow-sm"
                             style="width: 68px; height: 68px; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.4);">
                            <?php echo mb_substr($studentName, 0, 1); ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h2 class="h4 fw-bold mb-0 text-white"><?php echo htmlspecialchars($studentName); ?></h2>
                                <span class="badge bg-warning text-dark fw-bold px-2.5 py-1" style="font-size: 0.78rem;">
                                    ID: <?php echo htmlspecialchars($student['student_code']); ?>
                                </span>
                                <?php if (!empty($student['lrn'])): ?>
                                    <span class="badge bg-light bg-opacity-25 text-white" style="font-size: 0.78rem;">
                                        LRN: <?php echo htmlspecialchars($student['lrn']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="small opacity-90 mb-1">
                                <i class="bi bi-mortarboard me-1"></i> <strong>Grade Level:</strong> <?php echo htmlspecialchars($student['grade_level']); ?> <em class="fst-italic text-white-50" style="font-size: 0.72rem;">(Baitang)</em>
                                <?php if (!empty($student['section_name'])): ?>
                                    • Section: <strong><?php echo htmlspecialchars($student['section_name']); ?></strong> <em class="fst-italic text-white-50" style="font-size: 0.72rem;">(Seksyon)</em>
                                <?php endif; ?>
                                • <i class="bi bi-building ms-1 me-1"></i> <?php echo htmlspecialchars($student['school_name'] ?? 'SPED Center'); ?>
                            </div>
                            <div class="small opacity-90">
                                <i class="bi bi-person-badge-fill me-1 text-warning"></i> 
                                <strong>SPED Teacher:</strong> <?php echo htmlspecialchars($student['teacher_name'] ?? 'Assigned Teacher'); ?> <em class="fst-italic text-white-50" style="font-size: 0.72rem;">(Guro)</em>
                                <?php if (!empty($student['teacher_email'])): ?>
                                    (<a href="mailto:<?php echo htmlspecialchars($student['teacher_email']); ?>" class="text-white text-decoration-underline"><?php echo htmlspecialchars($student['teacher_email']); ?></a>)
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-lg-4 text-md-end text-center">
                    <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-25">
                        <div class="text-center px-2">
                            <span class="d-block fs-3 fw-bold text-warning">⭐ <?php echo (int)$totalStars; ?></span>
                            <small class="text-white-50" style="font-size: 0.72rem;">Stars <em class="fst-italic">(Bituin)</em></small>
                        </div>
                        <div style="border-left: 1px solid rgba(255,255,255,0.25); height: 35px;"></div>
                        <div class="text-center px-2">
                            <span class="d-block fs-3 fw-bold text-white">🏆 <?php echo (int)$totalXP; ?></span>
                            <small class="text-white-50" style="font-size: 0.72rem;">XP <em class="fst-italic">(Puntos)</em></small>
                        </div>
                        <div style="border-left: 1px solid rgba(255,255,255,0.25); height: 35px;"></div>
                        <div class="text-center px-2">
                            <span class="d-block fs-3 fw-bold text-success">
                                <i class="bi bi-check-circle-fill"></i> <?php echo $overallPct; ?>%
                            </span>
                            <small class="text-white-50" style="font-size: 0.72rem;">Overall Rate <em class="fst-italic">(Rate)</em></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 High-Level Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white">
                <div class="text-primary mb-1"><i class="bi bi-pie-chart-fill fs-2"></i></div>
                <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.5rem;"><?php echo $overallPct; ?>%</h3>
                <span class="text-dark fw-semibold small d-block">Overall Completion</span>
                <small class="text-muted d-block fst-italic" style="font-size: 0.7rem;">(Kabuuang Pag-unlad)</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white">
                <div class="text-success mb-1"><i class="bi bi-journal-check fs-2"></i></div>
                <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.5rem;">
                    <?php echo $completedLessonsCount; ?> <span class="text-muted fs-6">/ <?php echo count($publishedLessons); ?></span>
                </h3>
                <span class="text-dark fw-semibold small d-block">Completed Lessons</span>
                <small class="text-muted d-block fst-italic" style="font-size: 0.7rem;">(Natapos na Aralin)</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white">
                <div class="text-info mb-1"><i class="bi bi-pencil-square fs-2"></i></div>
                <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.5rem;">
                    <?php echo $completedTasksAll; ?> <span class="text-muted fs-6">/ <?php echo $totalTasksAll; ?></span>
                </h3>
                <span class="text-dark fw-semibold small d-block">Tasks &amp; Slide Steps</span>
                <small class="text-muted d-block fst-italic" style="font-size: 0.7rem;">(Gawain at Kwento)</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white">
                <div class="text-warning mb-1"><i class="bi bi-stars fs-2"></i></div>
                <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.5rem;"><?php echo (int)$totalStars; ?> ⭐</h3>
                <span class="text-dark fw-semibold small d-block">Earned Star Rewards</span>
                <small class="text-muted d-block fst-italic" style="font-size: 0.7rem;">(Naipong Bituin)</small>
            </div>
        </div>
    </div>

    <!-- Domain Progress Breakdown -->
    <?php if (!empty($domainStats)): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
                        <i class="bi bi-diagram-3-fill text-primary me-2"></i> Learning Domain Progress 
                        <small class="text-muted fst-italic fw-normal">(Pag-uswag Bawat Domain)</small>
                    </h5>
                    <small class="text-muted">Competency tracking aligned with DepEd SPED / PDSP Learning Standards.</small>
                </div>
                <span class="badge bg-secondary-subtle text-secondary small">DepEd SPED Competencies</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($domainStats as $dKey => $dVal): ?>
                        <?php
                        $dDisplayName = $domainNameMap[$dKey] ?? ucfirst(str_replace('_', ' ', $dKey));
                        $dPct = (int)$dVal['pct'];
                        ?>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="fw-bold text-dark small"><?php echo htmlspecialchars($dDisplayName); ?></span>
                                    <span class="badge bg-white text-primary border fw-bold"><?php echo $dPct; ?>%</span>
                                </div>
                                <div class="progress mb-2" style="height: 8px; border-radius: 4px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $dPct; ?>%;" aria-valuenow="<?php echo $dPct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                                    <span>
                                        <?php echo (int)$dVal['completed_lessons']; ?> / <?php echo (int)$dVal['lessons_count']; ?> Lessons Completed 
                                        <em class="fst-italic opacity-75">(Aralin Natapos)</em>
                                    </span>
                                    <span><?php echo (int)$dVal['completed_tasks']; ?> / <?php echo (int)$dVal['total_tasks']; ?> Tasks</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Lesson-by-Lesson Detailed Cards -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
                    <i class="bi bi-book-half text-success me-2"></i> Lesson-by-Lesson Learning Tracker
                    <small class="text-muted fst-italic fw-normal">(Detalyadong Ulat ng mga Aralin)</small>
                </h5>
                <small class="text-muted">
                    Track story slide reading completion, supplementary materials, and practice activity scores.
                    <span class="d-block text-muted fst-italic" style="font-size: 0.72rem;">(Makikita ang progreso ng pagbabasa sa bawat kwento at resulta ng mga pagsasanay.)</span>
                </small>
            </div>
            <span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill fw-semibold">
                <?php echo count($lessonsDetail); ?> Assigned Lessons <em class="fst-italic fw-normal">(Aralin)</em>
            </span>
        </div>
        <div class="card-body p-4">
            <?php if (empty($lessonsDetail)): ?>
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary"></i>
                    No published lessons assigned at this time.
                    <small class="d-block fst-italic mt-1">(Walang nakatalagang aralin sa kasalukuyan.)</small>
                </div>
            <?php else: ?>
                <div class="accordion d-flex flex-column gap-3" id="lessonsAccordion">
                    <?php foreach ($lessonsDetail as $lIndex => $les): ?>
                        <?php
                        $isCompleted = $les['is_slides_done'] && ($les['activities_done'] >= $les['activities_count']);
                        $statusBadgeClass = $isCompleted ? 'bg-success text-white' : ($les['pct'] > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white');
                        $statusText = $isCompleted ? 'Completed (Natapos Na)' : ($les['pct'] > 0 ? 'In Progress (Kasalukuyang Ginagawa)' : 'Not Started (Hindi Pa Nasisimulan)');
                        $domainDisplay = $domainNameMap[$les['domain']] ?? ucfirst(str_replace('_', ' ', $les['domain']));
                        ?>
                        <div class="border rounded-3 overflow-hidden shadow-xs bg-white">
                            <!-- Card Header / Toggle -->
                            <div class="p-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: #fafbfc; border-bottom: 1px solid #eee;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                         style="width: 44px; height: 44px; background: <?php echo $isCompleted ? '#3b6d11' : '#1e4072'; ?>;">
                                        <?php if ($isCompleted): ?>
                                            <i class="bi bi-check-lg fs-5"></i>
                                        <?php else: ?>
                                            <span style="font-size: 1.1rem;"><?php echo $lIndex + 1; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                                                <?php echo htmlspecialchars($les['title']); ?>
                                            </h6>
                                            <span class="badge <?php echo $statusBadgeClass; ?> px-2 py-0.5" style="font-size: 0.72rem;">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </div>
                                        <div class="text-muted small">
                                            <span class="badge bg-primary-subtle text-primary me-1"><?php echo htmlspecialchars($domainDisplay); ?></span>
                                            <?php if (!empty($les['school_year'])): ?>
                                                • SY <?php echo htmlspecialchars($les['school_year']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Gauge & Details -->
                                <div class="d-flex align-items-center gap-3" style="min-width: 220px;">
                                    <div class="flex-grow-1 text-end">
                                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                Lesson Progress: <em class="fst-italic opacity-75">(Pag-unlad)</em>
                                            </span>
                                            <span class="fw-bold text-dark" style="font-size: 0.78rem;"><?php echo $les['pct']; ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 4px;">
                                            <div class="progress-bar <?php echo $isCompleted ? 'bg-success' : 'bg-primary'; ?>" role="progressbar" style="width: <?php echo $les['pct']; ?>%;"></div>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary px-2.5 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#lessonCollapse<?php echo $les['id']; ?>" aria-expanded="<?php echo $lIndex === 0 ? 'true' : 'false'; ?>" style="border-radius: 6px; font-size: 0.8rem;">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Expandable Details -->
                            <div id="lessonCollapse<?php echo $les['id']; ?>" class="collapse <?php echo $lIndex === 0 ? 'show' : ''; ?>">
                                <div class="p-3 p-md-4">
                                    <div class="row g-3 mb-3">
                                        <!-- Story Slides Progress -->
                                        <div class="col-md-6">
                                            <div class="p-3 bg-light rounded-3 border h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                                                        <i class="bi bi-card-text text-primary"></i> Story Reading &amp; Slides
                                                        <em class="fst-italic text-muted fw-normal" style="font-size: 0.72rem;">(Pagbabasa ng Kwento)</em>
                                                    </span>
                                                    <?php if ($les['is_slides_done']): ?>
                                                        <span class="badge bg-success text-white py-1 px-2" style="font-size: 0.72rem;">
                                                            <i class="bi bi-check-circle me-1"></i> Completed <small class="fst-italic">(Kumpleto Na)</small>
                                                        </span>
                                                    <?php elseif ($les['last_page'] > 0): ?>
                                                        <span class="badge bg-warning text-dark py-1 px-2" style="font-size: 0.72rem;">
                                                            In Progress <small class="fst-italic">(Binabasa)</small>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary text-white py-1 px-2" style="font-size: 0.72rem;">
                                                            Not Started <small class="fst-italic">(Hindi Pa)</small>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.78rem;">
                                                        <span>
                                                            Slide Reached: <em class="fst-italic">(Narating na Pahina)</em>
                                                        </span>
                                                        <strong>Page <?php echo (int)$les['last_page']; ?> of <?php echo (int)$les['total_pages']; ?></strong>
                                                    </div>
                                                    <?php
                                                    $slidePct = $les['total_pages'] > 0 ? min(100, round(($les['last_page'] / $les['total_pages']) * 100)) : 0;
                                                    if ($les['is_slides_done']) $slidePct = 100;
                                                    ?>
                                                    <div class="progress" style="height: 6px; border-radius: 4px;">
                                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $slidePct; ?>%;"></div>
                                                    </div>
                                                </div>

                                                <?php if (!empty($les['slides_completed_at'])): ?>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                        <i class="bi bi-clock-history me-1"></i> Completed reading on: <?php echo date('M d, Y h:i A', strtotime($les['slides_completed_at'])); ?>
                                                        <em class="fst-italic opacity-75">(Natapos basahin)</em>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Learning Materials Attached -->
                                        <div class="col-md-6">
                                            <div class="p-3 bg-light rounded-3 border h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                                                        <i class="bi bi-paperclip text-success"></i> Supplementary Materials
                                                        <em class="fst-italic text-muted fw-normal" style="font-size: 0.72rem;">(Kagamitan)</em>
                                                    </span>
                                                    <span class="badge bg-white text-secondary border" style="font-size: 0.72rem;">
                                                        <?php echo count($les['materials']); ?> Files Attached
                                                    </span>
                                                </div>

                                                <?php if (empty($les['materials'])): ?>
                                                    <p class="text-muted small mb-0">No supplementary materials attached to this lesson.</p>
                                                <?php else: ?>
                                                    <ul class="list-unstyled mb-0 small">
                                                        <?php foreach ($les['materials'] as $mat): ?>
                                                            <li class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                                                                <span class="text-truncate me-2">
                                                                    <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i>
                                                                    <?php echo htmlspecialchars($mat['title']); ?>
                                                                </span>
                                                                <span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size: 0.65rem;">
                                                                    <?php echo htmlspecialchars($mat['material_type'] ?? 'file'); ?>
                                                                </span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Practice Activities & Quizzes Table -->
                                    <div class="mt-3">
                                        <h6 class="fw-bold text-dark mb-2 small d-flex align-items-center gap-1">
                                            <i class="bi bi-pencil-fill text-warning"></i> Practice Activities &amp; Quizzes
                                            <small class="text-muted fst-italic fw-normal">(Mga Pagsasanay at Pagsusulit)</small>
                                        </h6>
                                        <?php if (empty($les['activities'])): ?>
                                            <p class="text-muted small mb-0 p-2 bg-light rounded">No practice activities assigned for this lesson.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.82rem;">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Activity Title <small class="text-muted fst-italic d-block">(Pagsasanay)</small></th>
                                                            <th>Type <small class="text-muted fst-italic d-block">(Uri)</small></th>
                                                            <th>Score / Points <small class="text-muted fst-italic d-block">(Marka)</small></th>
                                                            <th>Attempts <small class="text-muted fst-italic d-block">(Gidaghanon)</small></th>
                                                            <th>Status <small class="text-muted fst-italic d-block">(Katayuan)</small></th>
                                                            <th>Date Submitted <small class="text-muted fst-italic d-block">(Petsa)</small></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($les['activities'] as $act): ?>
                                                            <?php
                                                            $actTypeNice = ucwords(str_replace('_', ' ', $act['activity_type']));
                                                            $hasScore = $act['score'] !== null;
                                                            $scorePct = ($hasScore && $act['max_score'] > 0) ? round(($act['score'] / $act['max_score']) * 100) : null;
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php echo htmlspecialchars($act['title']); ?></strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($actTypeNice); ?></span>
                                                                </td>
                                                                <td>
                                                                    <?php if ($hasScore): ?>
                                                                        <span class="fw-bold text-primary"><?php echo (int)$act['score']; ?></span> / <?php echo (int)$act['max_score']; ?>
                                                                        <?php if ($scorePct !== null): ?>
                                                                            <span class="badge <?php echo $scorePct >= 75 ? 'bg-success' : 'bg-warning text-dark'; ?> ms-1">
                                                                                <?php echo $scorePct; ?>%
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($act['is_submitted']): ?>
                                                                        <span class="badge bg-secondary-subtle text-secondary">
                                                                            <?php echo (int)$act['attempts']; ?> attempt<?php echo (int)$act['attempts'] > 1 ? 's' : ''; ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">0</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($act['is_submitted']): ?>
                                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                                            <i class="bi bi-check-circle me-1"></i> Answered <small class="fst-italic">(Nasagutan)</small>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                                            <i class="bi bi-hourglass-split me-1"></i> Pending <small class="fst-italic">(Hindi Pa)</small>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-muted">
                                                                    <?php echo !empty($act['submitted_at']) ? date('M d, Y h:i A', strtotime($act['submitted_at'])) : '—'; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Submissions Log Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
                    <i class="bi bi-clock-history text-primary me-2"></i> Recent Submissions Log
                    <small class="text-muted fst-italic fw-normal">(Kamakailang Tala ng Pagsusumite)</small>
                </h5>
                <small class="text-muted">Last 10 submitted activities and answers.</small>
            </div>
            <span class="badge bg-light text-secondary border small">Activity Attempt Records</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentGrades)): ?>
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                    No activities submitted yet.
                    <small class="d-block fst-italic mt-1">(Wala pang nasusumiteng mga sagot sa kasalukuyan.)</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Lesson Plan <small class="text-muted fst-italic d-block">(Aralin)</small></th>
                                <th>Activity <small class="text-muted fst-italic d-block">(Pagsasanay)</small></th>
                                <th>Score <small class="text-muted fst-italic d-block">(Marka)</small></th>
                                <th>Submission Date <small class="text-muted fst-italic d-block">(Petsa)</small></th>
                                <th>Evaluation Status <small class="text-muted fst-italic d-block">(Katayuan)</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentGrades as $subItem): ?>
                                <?php
                                $sScore = $subItem['score'] ?? $subItem['auto_score'] ?? 0;
                                $sMax = $subItem['grade_max_score'] ?? $subItem['activity_max_score'] ?? 1;
                                $sPct = $sMax > 0 ? round(($sScore / $sMax) * 100) : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($subItem['lesson_plan_title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($subItem['activity_title']); ?></td>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo (int)$sScore; ?> / <?php echo (int)$sMax; ?></span>
                                        <span class="badge <?php echo $sPct >= 75 ? 'bg-success' : 'bg-warning text-dark'; ?> ms-1" style="font-size: 0.72rem;">
                                            <?php echo $sPct; ?>%
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo !empty($subItem['submitted_at']) ? date('M d, Y h:i A', strtotime($subItem['submitted_at'])) : '—'; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($subItem['graded_at'])): ?>
                                            <span class="badge bg-success-subtle text-success">Teacher Graded</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">Auto-scored</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- DepEd SF9 Callout Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #f8faff 0%, #edf2f7 100%); border: 1px solid #dce4ec;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                    <i class="bi bi-card-checklist fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 1rem;">
                        Official DepEd SF9 / Progress Report Card
                        <small class="text-muted fst-italic fw-normal d-block" style="font-size: 0.8rem;">(Opisyal na DepEd SF9 Progress Report)</small>
                    </h6>
                    <p class="text-muted small mb-0">
                        Review quarterly competency ratings and affix your parent/guardian digital signature.
                        <span class="d-block text-muted fst-italic" style="font-size: 0.75rem;">(Maaaring suriin at lagdaan ang quarterly assessment at progress report ng iyong anak.)</span>
                    </p>
                </div>
            </div>
            <div>
                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 8px; font-size: 0.875rem; font-weight: 600;">
                    <span>Open SF9 Report Card <small class="fst-italic opacity-75">(Buksan ang SF9)</small></span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
