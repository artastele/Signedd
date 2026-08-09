<?php
$pageTitle = 'Dashboard - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-permanent" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Pending Application Alert -->
    <?php if (isset($pendingRequest) && $pendingRequest): ?>
        <div class="alert alert-warning alert-permanent" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-clock-history"></i> Application Pending Review
            </h5>
            <p class="mb-2">
                Your application is currently being reviewed.
            </p>
            <p class="mb-2">
                <strong>Applied Role:</strong> 
                <span class="badge bg-secondary"><?php echo ucwords(str_replace('_', ' ', $pendingRequest['requested_role'])); ?></span>
            </p>
            <p class="mb-0">
                <small class="text-muted">
                    <i class="bi bi-calendar"></i> Submitted: <?php echo date('F j, Y g:i A', strtotime($pendingRequest['created_at'])); ?>
                </small>
            </p>
            <hr>
            <p class="mb-0">
                <i class="bi bi-info-circle"></i> You will receive an email notification once your application is reviewed. 
                In the meantime, you can explore the system information below.
            </p>
        </div>
    <?php endif; ?>

    <!-- Rejected Application Alert -->
    <?php if (!isset($pendingRequest) && isset($rejectedRequest) && $rejectedRequest): ?>
        <div class="alert alert-danger alert-permanent" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-x-circle"></i> Application Rejected
            </h5>
            <p class="mb-2">
                Your application for <strong><?php echo ucwords(str_replace('_', ' ', $rejectedRequest['requested_role'])); ?></strong> 
                was not approved.
            </p>
            <?php if (!empty($rejectedRequest['review_note'])): ?>
                <div class="alert alert-light mb-3" style="border-left: 3px solid #a01422;">
                    <strong>Reason:</strong><br>
                    <?php echo nl2br(htmlspecialchars($rejectedRequest['review_note'])); ?>
                </div>
            <?php endif; ?>
            <p class="mb-2">
                <small class="text-muted">
                    <i class="bi bi-calendar"></i> Reviewed: <?php echo date('F j, Y g:i A', strtotime($rejectedRequest['updated_at'])); ?>
                    <?php if (!empty($rejectedRequest['reviewer_name'])): ?>
                        by <?php echo htmlspecialchars($rejectedRequest['reviewer_name']); ?>
                    <?php endif; ?>
                </small>
            </p>
            <hr>
            <div class="d-flex gap-2">
                <a href="<?php echo $basePath; ?>/role/select" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat"></i> Reapply for Role
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#applicationHistoryModal">
                    <i class="bi bi-clock-history"></i> View Application History
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Welcome Banner -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); color: white; border: none;">
        <div class="card-body p-4">
                <div class="col-md-12">
                    <h1 class="mb-2">Welcome to SignED, <?php echo htmlspecialchars($userName); ?>!</h1>
                    <p class="mb-0 lead">Special Education Learning Management System</p>
                    <p class="mb-0">Empowering educators, supporting learners, building futures.</p>
                </div>
        </div>
    </div>

    <!-- Featured Registered SPED Centers Hero Carousel -->
    <?php
    require_once __DIR__ . '/../../Models/SchoolModel.php';
    $schoolModel = new SchoolModel();
    $registeredSchools = $schoolModel->getAllSchools();
    ?>

    <div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-top: 4px solid #a01422 !important; background: #ffffff; border-radius: 12px;">
        <div class="card-header bg-white border-0 pt-3 px-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-dark fw-bold fs-5">
                <i class="bi bi-buildings-fill text-danger me-2"></i> Registered SPED Schools
            </h5>
            <span class="badge bg-light text-dark border px-3 py-2 fs-6 fw-semibold rounded-pill">
                <i class="bi bi-check-circle-fill text-success me-1"></i> <?php echo count($registeredSchools); ?> Active School<?php echo count($registeredSchools) === 1 ? '' : 's'; ?>
            </span>
        </div>
        <div class="card-body p-4">
            <?php if (empty($registeredSchools)): ?>
                <!-- Clean Placeholder when no schools exist yet -->
                <div class="p-4 text-center bg-light rounded-3 border my-1">
                    <i class="bi bi-building-add text-secondary" style="font-size: 2.8rem;"></i>
                    <h5 class="fw-bold mt-2 text-dark">No Registered SPED Schools Yet</h5>
                    <p class="text-muted mb-3 small">Are you a School Head or Principal? Register your school in SignED to establish your enrollment guidelines and faculty roster.</p>
                    <a href="<?php echo $basePath; ?>/role/select" class="btn btn-outline-danger btn-sm fw-bold px-4 py-2">
                        <i class="bi bi-plus-circle-fill me-1"></i> Register Your School Now
                    </a>
                </div>
            <?php else: ?>
                <!-- Clean Auto-Rotating Featured Schools Hero Carousel -->
                <div id="featuredSchoolsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
                    <!-- Pagination Indicators / Dots -->
                    <div class="carousel-indicators mb-0" style="bottom: -15px;">
                        <?php foreach ($registeredSchools as $idx => $sch): ?>
                            <button type="button" data-bs-target="#featuredSchoolsCarousel" data-bs-slide-to="<?php echo $idx; ?>" class="bg-secondary <?php echo $idx === 0 ? 'active' : ''; ?>" aria-current="<?php echo $idx === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $idx + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Slides -->
                    <div class="carousel-inner pb-4">
                        <?php foreach ($registeredSchools as $idx => $sch): ?>
                            <?php 
                            $schStatus = strtoupper($sch['enrollment_status'] ?? 'OPEN');
                            $schStatusClass = ($schStatus === 'OPEN') ? 'bg-success bg-opacity-10 text-success border border-success' : (($schStatus === 'UPCOMING') ? 'bg-warning bg-opacity-10 text-warning border border-warning' : 'bg-danger bg-opacity-10 text-danger border border-danger');
                            $schLogoUrl = SchoolModel::getSchoolLogoUrl($sch, $basePath);
                            ?>
                            <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                <div class="row align-items-center g-3 p-3 bg-light rounded-3 border mx-1">
                                    <div class="col-md-3 text-center border-end py-2">
                                        <div class="p-1 bg-white rounded-circle d-inline-block mb-1 shadow-sm border" style="width: 85px; height: 85px;">
                                            <img src="<?php echo htmlspecialchars($schLogoUrl); ?>" alt="<?php echo htmlspecialchars($sch['school_name']); ?> Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                                        </div>
                                        <div class="small text-muted fw-semibold" style="font-size: 0.75rem;">DEPED SPED SCHOOL</div>
                                        <span class="badge bg-white text-dark border fw-semibold px-2 py-1 small">ID: <?php echo htmlspecialchars($sch['school_id']); ?></span>
                                    </div>
                                    <div class="col-md-9 px-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <h4 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($sch['school_name']); ?></h4>
                                                <div class="text-muted small">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($sch['division'] ?? 'Division Office'); ?> | <?php echo htmlspecialchars($sch['region'] ?? 'DepEd Region'); ?>
                                                </div>
                                            </div>
                                            <span class="badge <?php echo $schStatusClass; ?> px-2 py-1 small">
                                                <i class="bi bi-clock-history me-1"></i> Enrollment <?php echo $schStatus; ?>
                                            </span>
                                        </div>
                                        <p class="text-secondary small mb-2">
                                            <i class="bi bi-pin-map text-muted me-1"></i> <?php echo htmlspecialchars($sch['address'] ?? 'Official DepEd Registered Address'); ?>
                                        </p>
                                        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> DepEd Verified School</small>
                                            <a href="<?php echo $basePath; ?>/role/select?type=parent&school_id=<?php echo $sch['id']; ?>" class="btn btn-sm btn-primary fw-semibold px-3 py-1.5 shadow-sm">
                                                <i class="bi bi-person-plus-fill me-1"></i> Enroll Child to <?php echo htmlspecialchars($sch['school_name']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Navigation Arrow Controls -->
                    <?php if (count($registeredSchools) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#featuredSchoolsCarousel" data-bs-slide="prev" style="width: 35px; opacity: 0.7;">
                            <span class="carousel-control-prev-icon p-2 bg-secondary rounded-circle" aria-hidden="true" style="width: 1.2rem; height: 1.2rem;"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#featuredSchoolsCarousel" data-bs-slide="next" style="width: 35px; opacity: 0.7;">
                            <span class="carousel-control-next-icon p-2 bg-secondary rounded-circle" aria-hidden="true" style="width: 1.2rem; height: 1.2rem;"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Get Started Section -->
    <?php if (!isset($pendingRequest) || !$pendingRequest): ?>
        <div class="row">
            <div class="col-12 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-secondary mb-1">
                            <i class="bi bi-arrow-right-circle"></i> Get Started
                        </h4>
                        <p class="text-muted mb-0">Choose how you'd like to use the system:</p>
                    </div>
                    <?php if (isset($applicationHistory) && count($applicationHistory) > 0): ?>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#applicationHistoryModal">
                            <i class="bi bi-clock-history"></i> View Application History
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Separate Role Application Banners: Principal & Staff -->
        <div class="row g-3 mb-4">
            <!-- Principal Banner -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1e4072 !important; background: #f8fafc;">
                    <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2.5 me-3 text-primary flex-shrink-0">
                                <i class="bi bi-building-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark fs-6">Are you a Principal or School Head?</h6>
                                <small class="text-muted d-block">Register a new SPED School to manage faculty rosters and publish enrollment guidelines.</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="<?php echo $basePath; ?>/role/select?type=principal" class="btn btn-sm btn-primary fw-bold px-3 py-2">
                                <i class="bi bi-plus-circle-fill me-1"></i> Register New SPED School (Principal)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- School Staff & Teacher Banner -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important; background: #f8fafc;">
                    <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-info bg-opacity-10 p-2.5 me-3 text-primary flex-shrink-0">
                                <i class="bi bi-person-badge-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark fs-6">Are you a SPED Teacher or School Staff?</h6>
                                <small class="text-muted d-block">Apply for access to join an existing registered SPED School's faculty roster (SPED Teacher, Guidance, Master Teacher).</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="<?php echo $basePath; ?>/role/select?type=staff" class="btn btn-sm btn-outline-primary fw-bold px-3 py-2">
                                <i class="bi bi-person-plus-fill me-1"></i> Apply as School Faculty / Staff
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary Focus: Enroll Your Child (Parent) - Wide Landscape Banner -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden" style="border: 2px solid #a01422 !important; border-radius: 14px; background: linear-gradient(135deg, #ffffff 60%, #fff5f5 100%);">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            <!-- Left Column: Icon + Text + Spread Out Features -->
                            <div class="col-lg-8 mb-4 mb-lg-0">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-heart-fill fs-1" style="color: #a01422;"></i>
                                    </div>
                                    <div>
                                        <h2 class="fw-bold text-primary mb-1">Enroll Your Child</h2>
                                        <p class="text-muted mb-0">Are you a parent or guardian? Start the official enrollment process and choose your target SPED school.</p>
                                    </div>
                                </div>
                                
                                <div class="row g-2 mt-2">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center text-dark small">
                                            <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                            <span>Select target SPED School & submit documents</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center text-dark small">
                                            <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                            <span>Real-time application status tracking</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center text-dark small">
                                            <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                            <span>View child's progress report & IEP updates</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center text-dark small">
                                            <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                            <span>Receive direct SPED teacher notifications</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Action Button + Badge -->
                            <div class="col-lg-4 text-lg-end text-center">
                                <div class="alert alert-success border-0 py-2 px-3 mb-3 d-inline-block text-start" style="background-color: #e8f5e9;">
                                    <small class="fw-semibold text-success">
                                        <i class="bi bi-lightning-fill text-warning me-1"></i> Instant Access — Start Immediately
                                    </small>
                                </div>
                                <div>
                                    <a href="<?php echo $basePath; ?>/role/select?type=parent" class="btn btn-primary fw-semibold px-4 py-2.5 shadow-sm rounded-3 w-100">
                                        <i class="bi bi-person-plus-fill me-2"></i> Enroll Your Child Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- About Our System Card -->
    <div class="row mt-4 mb-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #a01422 !important; background: #ffffff;">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold" style="color: #a01422;">
                        <i class="bi bi-info-circle-fill me-2"></i> About Our System
                    </h5>
                    <p class="card-text text-secondary mb-2">
                        The SPED Learning Management System is designed to streamline the management of special education programs. 
                        Our platform supports the entire IEP (Individualized Education Plan) lifecycle, from enrollment and assessment 
                        to implementation and progress tracking.
                    </p>
                    <p class="card-text mb-0 text-dark small">
                        <strong>Our Mission:</strong> To provide inclusive, quality education for learners with special needs through 
                        efficient collaboration between parents, teachers, and administrators.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Section (Below About Our System) -->
    <div class="row mt-3 mb-2">
        <div class="col-12">
            <div class="card" style="background-color: #f9f9f9; border: none;">
                <div class="card-body p-3 text-center">
                    <p class="mb-0 text-muted">
                        <i class="bi bi-question-circle"></i> 
                        Need help? Contact the administrator at 
                        <a href="mailto:admin@spedlms.local" class="text-decoration-none">admin@spedlms.local</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application History Modal -->
<div class="modal fade" id="applicationHistoryModal" tabindex="-1" aria-labelledby="applicationHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); color: white;">
                <h5 class="modal-title" id="applicationHistoryModalLabel">
                    <i class="bi bi-clock-history"></i> Application History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($applicationHistory) && count($applicationHistory) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead style="background-color: #1e4072; color: white;">
                                <tr>
                                    <th>Role Applied</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Reviewed</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applicationHistory as $app): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo ucwords(str_replace('_', ' ', $app['requested_role'])); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusBadge = [
                                                'pending' => '<span class="badge" style="background-color: #ffc107; color: #000;">Pending</span>',
                                                'approved' => '<span class="badge bg-success">Approved</span>',
                                                'rejected' => '<span class="badge bg-danger">Rejected</span>'
                                            ];
                                            echo $statusBadge[$app['status']] ?? '<span class="badge bg-secondary">Unknown</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('M j, Y', strtotime($app['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($app['status'] !== 'pending'): ?>
                                                <small>
                                                    <?php echo date('M j, Y', strtotime($app['updated_at'])); ?>
                                                    <?php if (!empty($app['reviewer_name'])): ?>
                                                        <br><em class="text-muted">by <?php echo htmlspecialchars($app['reviewer_name']); ?></em>
                                                    <?php endif; ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['review_note'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        data-bs-toggle="popover" 
                                                        data-bs-trigger="focus"
                                                        data-bs-placement="left"
                                                        data-bs-content="<?php echo htmlspecialchars($app['review_note']); ?>">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No application history found.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize popovers for review notes
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>

<style>
.role-selection-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
