<?php
$pageTitle = 'Master Teacher Dashboard - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Master Teacher Dashboard</h1>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-diagram-3 text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Process 7–13 Modules</h5>
                    <p class="text-muted small">Use the dedicated COT, readiness, and progress-report module links from the IEP records page.</p>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Go to IEP Records
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-eye text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Class Observation</h5>
                    <span class="text-muted small">Temporarily unavailable for demo</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">COT Results</h5>
                    <span class="text-muted small">Temporarily unavailable for demo</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
