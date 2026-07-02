<?php
$pageTitle = 'General Teacher Dashboard - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">General Teacher Dashboard</h1>

    <!-- Welcome Banner -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #1e4072 0%, #3a5c8c 100%); color: white; border: none; border-radius: 12px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h2 class="mb-2 fw-bold">Welcome back, <?php echo htmlspecialchars($userName ?? 'Teacher'); ?>!</h2>
                    <p class="mb-0 lead" style="font-size: 1.1rem;">Manage your inclusive education tasks and mainstreamed learners here.</p>
                </div>
                <div class="col-md-3 text-center d-none d-md-block">
                    <i class="bi bi-person-video3" style="font-size: 4rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #1e4072 !important; border-radius: 10px;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-book text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3 mb-0 fw-bold" style="color: #1e4072;"><?php echo (int)($assignedIepsCount ?? 0); ?></h3>
                    <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Assigned IEPs / ITGPs</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #ffc107 !important; border-radius: 10px;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-clipboard-check text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3 mb-0 fw-bold" style="color: #1e4072;"><?php echo (int)($activeITGPs ?? 0); ?></h3>
                    <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Active ITGPs</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #198754 !important; border-radius: 10px;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-bar-chart-line text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3 mb-0 fw-bold" style="color: #1e4072;"><?php echo (int)($submittedGrades ?? 0); ?></h3>
                    <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Grades Submitted</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h5 class="mb-3 fw-bold" style="color: #1e4072;">Quick Actions</h5>
    <div class="row">
        <!-- IEP Records (ITGP) -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; transition: transform 0.2s;">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: #e9ecef;">
                        <i class="bi bi-journal-text text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title fw-bold">Inclusive IEPs & ITGPs</h5>
                    <p class="text-muted small mb-4">View IEP records and draft or review Individualized Transition Goal Plans for your learners.</p>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep" class="btn text-white w-100" style="background: #1e4072; border-radius: 8px;">
                        <i class="bi bi-arrow-right me-1"></i> Go to IEPs
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Grades and Progress (Future Feature) -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; transition: transform 0.2s; opacity: 0.7;">
                <div class="card-body text-center p-4">
                    <span class="badge bg-secondary position-absolute top-0 end-0 mt-3 me-3">Coming Soon</span>
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: #e9ecef;">
                        <i class="bi bi-star-fill text-secondary" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title fw-bold text-muted">Grades &amp; Progress</h5>
                    <p class="text-muted small mb-4">Submit grades and track the progress of mainstreamed learners in your regular classes.</p>
                    <button class="btn btn-secondary w-100" style="border-radius: 8px;" disabled>
                        <i class="bi bi-lock me-1"></i> Future Feature
                    </button>
                </div>
            </div>
        </div>

        <!-- Placements -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; transition: transform 0.2s;">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: #d1e7dd;">
                        <i class="bi bi-envelope-paper text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title fw-bold">Placement Notices</h5>
                    <p class="text-muted small mb-4">Check for inclusion and class placement notices. Note: Notices will only appear once a learner has a finalized ITGP.</p>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep" class="btn btn-outline-success w-100" style="border-radius: 8px;">
                        <i class="bi bi-envelope me-1"></i> View Notices
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Note -->
    <div class="card border-0 shadow-sm mt-2" style="border-radius: 12px; background: #f8f9fa;">
        <div class="card-body p-4 d-flex align-items-start gap-3">
            <i class="bi bi-info-circle-fill text-primary mt-1 fs-5"></i>
            <div>
                <h6 class="fw-bold mb-1">Collaboration is Key</h6>
                <p class="text-muted small mb-0">If you have concerns about a learner's behavior, academics, or ITGP, use the Discussion Board in the ITGP section to communicate directly with their assigned SPED Teacher.</p>
            </div>
        </div>
    </div>

</div>

<style>
    .card.h-100:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
