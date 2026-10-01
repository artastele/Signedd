<?php
// Part of: SignED — Parent Oversight & Child Learning Tracking
// Process 7: Parent Tracking View (English Main + Tagalog Subtitle/Italics)

$pageTitle = "My Child's Learning Progress — SignED";
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content p-3 p-md-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-semibold" aria-current="page">
                        Learner Progress <span class="fst-italic text-muted fw-normal" style="font-size: 0.75rem;">(Pag-uswag ng Mag-aaral)</span>
                    </li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <span class="text-primary"><i class="bi bi-person-workspace"></i></span>
                My Child's Learning Progress
                <small class="text-muted fst-italic fw-normal fs-6 d-none d-md-inline">(Pag-uswag ng Aking Mag-aaral)</small>
            </h1>
            <p class="text-muted small mb-0 mt-1">
                Monitor story reading progress, completed practice activities, and earned rewards.
                <span class="d-block text-muted fst-italic" style="font-size: 0.75rem;">(Subaybayan ang pagbabasa ng kwento, mga natapos na pagsasanay, at mga bituin na naipon ng iyong anak.)</span>
            </p>
        </div>
        <div>
            <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500; height: 38px;">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Dashboard <small class="fst-italic text-muted" style="font-size: 0.72rem;">(Bumalik)</small></span>
            </a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="alert alert-primary border-0 shadow-sm rounded-3 p-3 mb-4 d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
            <i class="bi bi-lightbulb-fill fs-5"></i>
        </div>
        <div class="small">
            <strong>Parent Guidance Overview:</strong> 
            Here you can track your child's comprehensive learning progress in detail. The learner's interface is kept clean and simple to prevent distraction, while all tracking metrics, slide reading records, and quiz scores are accessible here for your guidance.
            <div class="mt-1 text-muted fst-italic" style="font-size: 0.75rem;">
                (Gabay sa Ginikanan: Dito makikita ang detalyadong tracking. Ang learner view ay simple habang lahat ng metrics at marka ay nakikita mo dito bilang gabay sa pagkatuto.)
            </div>
        </div>
    </div>

    <?php if (empty($children)): ?>
        <div class="card border-0 shadow-sm rounded-4 text-center p-5">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-journal-x text-secondary fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">No Enrolled Learners Yet</h5>
            <small class="text-muted d-block fst-italic mb-2" style="font-size: 0.8rem;">(Wala pang Naka-enroll na Mag-aaral)</small>
            <p class="text-muted small mb-4" style="max-width: 460px; margin: 0 auto;">
                Once the SPED Center verifies your child's enrollment, their assigned lessons, practice activities, and scores will appear here.
                <span class="d-block text-muted fst-italic mt-1" style="font-size: 0.75rem;">(Sa oras na ma-verify ng SPED Center ang enrollment, lalabas dito ang mga aralin at marka.)</span>
            </p>
            <div>
                <a href="<?php echo $basePath; ?>/enroll" class="btn btn-primary px-4 py-2" style="border-radius: 8px; font-size: 0.875rem; font-weight: 600;">
                    <i class="bi bi-plus-circle me-1"></i> Enroll Child <small class="fst-italic opacity-75">(Mag-enroll ng Anak)</small>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($children as $ch): ?>
                <?php
                $sid = (int)$ch['student_id'];
                $pct = (int)($ch['pct'] ?? 0);
                $initial = mb_substr($ch['student_name'] ?? 'M', 0, 1);
                ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-4">
                                <!-- Col 1: Avatar & Identity -->
                                <div class="col-lg-4 col-md-5 border-end-md">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-3 flex-shrink-0 shadow-sm"
                                             style="width: 64px; height: 64px; background: linear-gradient(135deg, #1e4072 0%, #a01422 100%);">
                                            <?php echo htmlspecialchars($initial); ?>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <h4 class="fw-bold text-dark mb-0" style="font-size: 1.2rem;">
                                                    <?php echo htmlspecialchars($ch['student_name']); ?>
                                                </h4>
                                                <span class="badge bg-primary-subtle text-primary fw-semibold" style="font-size: 0.75rem;">
                                                    ID: <?php echo htmlspecialchars($ch['student_code']); ?>
                                                </span>
                                            </div>
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-mortarboard text-primary me-1"></i> 
                                                <strong>Grade Level:</strong> <?php echo htmlspecialchars($ch['grade_level']); ?>
                                                <em class="fst-italic text-muted" style="font-size: 0.72rem;">(Baitang)</em>
                                                <?php if (!empty($ch['section_name'])): ?>
                                                    • <span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($ch['section_name']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-building text-secondary me-1"></i> <?php echo htmlspecialchars($ch['school_name'] ?? 'SPED Center'); ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-person-check text-success me-1"></i> 
                                                <strong>Teacher:</strong> <?php echo htmlspecialchars($ch['teacher_name'] ?? 'SPED Educator'); ?>
                                                <em class="fst-italic text-muted" style="font-size: 0.72rem;">(Guro)</em>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col 2: Metrics Summary Grid -->
                                <div class="col-lg-5 col-md-7">
                                    <div class="row g-2 text-center mb-3">
                                        <div class="col-6 col-sm-3">
                                            <div class="p-2 bg-light rounded-3 border">
                                                <span class="d-block fw-bold text-success fs-5">
                                                    <?php echo (int)($ch['completed_lessons'] ?? 0); ?>/<?php echo (int)($ch['total_lessons'] ?? 0); ?>
                                                </span>
                                                <small class="text-dark d-block fw-semibold" style="font-size: 0.72rem;">Completed</small>
                                                <small class="text-muted d-block fst-italic" style="font-size: 0.65rem;">(Natapos na Aralin)</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="p-2 bg-light rounded-3 border">
                                                <span class="d-block fw-bold text-primary fs-5">
                                                    <?php echo (int)($ch['completed_tasks'] ?? 0); ?>/<?php echo (int)($ch['total_tasks'] ?? 0); ?>
                                                </span>
                                                <small class="text-dark d-block fw-semibold" style="font-size: 0.72rem;">Tasks &amp; Slides</small>
                                                <small class="text-muted d-block fst-italic" style="font-size: 0.65rem;">(Gawain at Kwento)</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="p-2 bg-light rounded-3 border">
                                                <span class="d-block fw-bold text-warning fs-5">
                                                    ⭐ <?php echo (int)($ch['total_stars'] ?? 0); ?>
                                                </span>
                                                <small class="text-dark d-block fw-semibold" style="font-size: 0.72rem;">Stars Earned</small>
                                                <small class="text-muted d-block fst-italic" style="font-size: 0.65rem;">(Mga Bituin)</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="p-2 bg-light rounded-3 border">
                                                <span class="d-block fw-bold text-danger fs-5">
                                                    🏆 <?php echo (int)($ch['total_xp'] ?? 0); ?>
                                                </span>
                                                <small class="text-dark d-block fw-semibold" style="font-size: 0.72rem;">XP Points</small>
                                                <small class="text-muted d-block fst-italic" style="font-size: 0.65rem;">(Puntos)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Overall Progress Bar -->
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                                            <span class="text-muted fw-semibold">
                                                Overall Progress:
                                                <em class="fst-italic text-muted" style="font-size: 0.72rem;">(Kabuuang Pag-unlad)</em>
                                            </span>
                                            <span class="fw-bold text-primary">
                                                <?php echo $pct; ?>% Completed
                                                <small class="fst-italic text-muted fw-normal" style="font-size: 0.72rem;">(Kumpleto)</small>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 9px; border-radius: 6px; background-color: #e9ecef;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?php echo $pct; ?>%;" 
                                                 aria-valuenow="<?php echo $pct; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col 3: Action Buttons -->
                                <div class="col-lg-3 col-md-12 text-lg-end text-center pt-2 pt-lg-0">
                                    <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2 justify-content-center justify-content-lg-end">
                                        <a href="<?php echo $basePath; ?>/parent/child-progress/<?php echo $sid; ?>" 
                                           class="btn btn-primary px-4 py-2 d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm"
                                           style="border-radius: 8px; font-size: 0.875rem; font-weight: 600; height: 40px;">
                                            <i class="bi bi-bar-chart-line-fill"></i>
                                            <span>Detailed Tracking <small class="fst-italic opacity-75">(Detalyadong Ulat)</small></span>
                                        </a>
                                        <a href="<?php echo $basePath; ?>/progress-reports/<?php echo $sid; ?>" 
                                           class="btn btn-outline-secondary px-3 py-2 d-inline-flex align-items-center justify-content-center gap-1.5"
                                           style="border-radius: 8px; font-size: 0.85rem; font-weight: 500; height: 40px;">
                                            <i class="bi bi-file-earmark-text"></i>
                                            <span>DepEd Progress Report <small class="fst-italic text-muted">(SF9)</small></span>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
