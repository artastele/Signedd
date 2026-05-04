<?php
$pageTitle = 'SPED Teacher Dashboard - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">SPED Teacher Dashboard</h1>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Verify Enrollment</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/verification" class="btn btn-primary">Verify Documents</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Conduct Assessment</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/assessment" class="btn btn-primary">Assessments</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-book text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Implement IEP</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep/implementation" class="btn btn-primary">IEP Implementation</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
