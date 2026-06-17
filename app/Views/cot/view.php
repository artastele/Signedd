<?php
$pageTitle = 'View Observation #' . $observation['id'] . ' - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .rating-btn-container {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
    }

    .rating-btn-static {
        min-height: 40px;
        min-width: 48px;
        font-size: 1rem;
        font-weight: 700;
        border: 2px solid #e2e8f0;
        margin: 3px;
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: #f8fafc;
        color: #94a3b8;
    }

    /* Highlight class for active static rating */
    .rating-btn-static.active {
        background-color: #a01422 !important;
        border-color: #a01422 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 5px rgba(160, 20, 34, 0.2);
    }

    .indicator-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
</style>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Classroom Observation Details</h1>
        <a href="<?= $basePath ?>/cot/observations" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to History
        </a>
    </div>

    <!-- Finalized Hero Score Card -->
    <div class="card text-white border-0 shadow-sm mb-4" style="background-color: #1e4072;">
        <div class="card-body p-4 text-center">
            <h4 class="mb-2 text-uppercase tracking-wider small">Computed Average Score</h4>
            <h1 class="display-3 fw-bold mb-0">
                <?= $observation['average_score'] !== null ? number_format($observation['average_score'], 2) : 'N/A' ?>
            </h1>
            <p class="mb-0 mt-2 text-white-50">
                Finalized on <?= $observation['finalized_at'] ? date('F j, Y, g:i A', strtotime($observation['finalized_at'])) : 'N/A' ?>
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Details Column -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Observation Metadata</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">Observer / Master Teacher</label>
                            <div class="fw-bold"><?= htmlspecialchars($observation['observer_name']) ?></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">Observed Teacher</label>
                            <div class="fw-bold"><?= htmlspecialchars($observation['observed_teacher_name']) ?></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">Subject &amp; Grade Level</label>
                            <div class="fw-bold"><?= htmlspecialchars($observation['subject_grade_level']) ?></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">Observation Date</label>
                            <div class="fw-bold"><?= date('F j, Y', strtotime($observation['scheduled_at'])) ?></div>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label text-muted small mb-1">School Year</label>
                            <div class="fw-bold"><?= htmlspecialchars($observation['school_year']) ?></div>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label text-muted small mb-1">Quarter</label>
                            <div class="fw-bold"><?= htmlspecialchars($observation['quarter']) ?></div>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label text-muted small mb-1">Observation Round</label>
                            <div class="fw-bold"><?= $observation['observation_number'] == 1 ? '1st' : '2nd' ?> Observation</div>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label text-muted small mb-1">Record Status</label>
                            <div>
                                <span class="badge bg-success px-3 py-1">
                                    <i class="bi bi-shield-check"></i> Digital Authenticated (Finalized)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rated Indicators -->
            <h4 class="mb-3 text-navy"><i class="bi bi-list-check"></i> Indicator Ratings</h4>
            <div class="mb-4">
                <?php foreach ($indicators as $indicator): 
                    $selectedRating = $ratings[$indicator['id']] ?? null;
                ?>
                    <div class="card indicator-card border-0 shadow-sm mb-3 bg-light-surface">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <!-- Text -->
                                <div class="col-lg-7 mb-3 mb-lg-0">
                                    <div class="d-flex align-items-start">
                                        <span class="badge rounded-pill bg-light text-navy border me-2 mt-1">
                                            <?= $indicator['indicator_number'] ?>
                                        </span>
                                        <div>
                                            <p class="mb-0 fw-semibold text-dark fs-6"><?= htmlspecialchars($indicator['indicator_text']) ?></p>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace small">Competency Code: <?= htmlspecialchars($indicator['competency_code']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Static Rating Buttons -->
                                <div class="col-lg-5">
                                    <div class="rating-btn-container">
                                        <?php foreach (['2', '3', '4', '5', '6', 'NO'] as $val): ?>
                                            <div class="rating-btn-static <?= $selectedRating === $val ? 'active' : '' ?>">
                                                <?= $val ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Comments -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-chat-left-text"></i> Observer's Comments &amp; Notes</h5>
                </div>
                <div class="card-body">
                    <p class="text-dark bg-light p-3 rounded" style="white-space: pre-wrap; min-height: 100px;"><?= !empty($observation['other_comments']) ? htmlspecialchars($observation['other_comments']) : 'No comment provided.' ?></p>
                </div>
            </div>

            <div class="alert alert-info py-3 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Digitally Authenticated Record:</strong> This observation report has been finalized by Master Teacher <strong><?= htmlspecialchars($observation['observer_name']) ?></strong> on <strong><?= date('Y-m-d H:i:s', strtotime($observation['finalized_at'])) ?></strong>. Per system security rules, this record is locked and cannot be edited. SPED teachers have no access to view observation ratings in this system.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
