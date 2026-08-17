<?php
$pageTitle = 'Principal & School Verification Applications - Admin - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';

$pendingApps = [];
$historyApps = [];

foreach ($requests as $r) {
    if (($r['status'] ?? '') === 'pending') {
        $pendingApps[] = $r;
    } else {
        $historyApps[] = $r;
    }
}
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1e4072;">
                <i class="bi bi-shield-check text-danger me-2"></i> Principal & School Verification Applications
            </h2>
            <p class="text-muted mb-0 small">Review and approve complete applications including Principal credentials and registered school profile details.</p>
        </div>
        <div>
            <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                <i class="bi bi-clock-history me-1"></i> <?php echo count($pendingApps); ?> Pending Review
            </span>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Pending Verification Applications Section (Inline Review Cards) -->
    <h4 class="fw-bold mb-3 text-dark">
        <i class="bi bi-hourglass-split text-warning me-2"></i> Pending Applications Requiring Verification (<?php echo count($pendingApps); ?>)
    </h4>

    <?php if (empty($pendingApps)): ?>
        <div class="card border-0 shadow-sm mb-5 text-center py-5">
            <div class="card-body">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold mt-3 text-dark">All Applications Processed!</h5>
                <p class="text-muted small mb-0">There are no pending Principal role or school registration applications waiting for approval.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-4 mb-5">
            <?php foreach ($pendingApps as $req): ?>
                <?php $docs = json_decode($req['submitted_docs'] ?? '', true); ?>
                <div class="card border-0 shadow-sm overflow-hidden" style="border-top: 4px solid #a01422 !important; border-radius: 12px;">
                    <div class="card-header bg-light border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning text-dark me-2 px-3 py-2 fs-6">
                                <i class="bi bi-clock-fill me-1"></i> PENDING VERIFICATION
                            </span>
                            <span class="text-muted small">
                                Submitted on <?php echo date('F j, Y g:i A', strtotime($req['created_at'])); ?>
                            </span>
                        </div>
                        <span class="badge bg-dark px-3 py-2 fs-6">Application ID: #<?php echo $req['id']; ?></span>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Principal Applicant Details -->
                            <div class="col-md-5 border-end">
                                <h5 class="fw-bold mb-3 text-primary">
                                    <i class="bi bi-person-badge-fill me-2"></i> Principal Applicant Information
                                </h5>
                                <div class="p-3 rounded-3 bg-light border mb-3">
                                    <p class="mb-2">
                                        <strong class="text-secondary small d-block">Full Name:</strong>
                                        <span class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($req['user_name']); ?></span>
                                    </p>
                                    <p class="mb-2">
                                        <strong class="text-secondary small d-block">Email Address:</strong>
                                        <a href="mailto:<?php echo htmlspecialchars($req['user_email']); ?>" class="text-decoration-none fw-semibold">
                                            <?php echo htmlspecialchars($req['user_email']); ?>
                                        </a>
                                    </p>
                                    <?php if (!empty($req['user_contact'])): ?>
                                        <p class="mb-2">
                                            <strong class="text-secondary small d-block">Contact Number:</strong>
                                            <span class="text-dark"><?php echo htmlspecialchars($req['user_contact']); ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <p class="mb-2">
                                        <strong class="text-secondary small d-block">Requested Role:</strong>
                                        <span class="badge bg-danger fs-6 px-3 py-1">
                                            <i class="bi bi-shield-lock-fill me-1"></i> <?php echo ucwords(str_replace('_', ' ', $req['requested_role'])); ?>
                                        </span>
                                    </p>
                                    <?php if ($docs && !empty($docs['employee_number'])): ?>
                                        <p class="mb-0">
                                            <strong class="text-secondary small d-block">DepEd Employee / ID Number:</strong>
                                            <span class="badge bg-secondary font-monospace fs-6"><?php echo htmlspecialchars($docs['employee_number']); ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>

                            </div>

                            <!-- Registered School Profile Dossier -->
                            <div class="col-md-7">
                                <h5 class="fw-bold mb-3 text-danger">
                                    <i class="bi bi-building-fill me-2"></i> Registered School Dossier & Profile
                                </h5>
                                <?php if (!empty($req['school_name'])): ?>
                                    <div class="p-3 rounded-3 border bg-white shadow-sm mb-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <!-- School Logo Badge -->
                                            <div class="text-center">
                                                <?php if (!empty($req['school_logo_path']) && file_exists(public_path($req['school_logo_path']))): ?>
                                                    <img src="<?php echo $basePath . '/' . htmlspecialchars($req['school_logo_path']); ?>" alt="School Logo" class="rounded-circle border shadow-sm" style="width: 85px; height: 85px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-secondary" style="width: 85px; height: 85px;">
                                                        <i class="bi bi-building fs-1"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="d-block small text-muted mt-1 fw-semibold">Official Logo</span>
                                            </div>

                                            <!-- School Information -->
                                            <div class="flex-grow-1">
                                                <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($req['school_name']); ?></h4>
                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                    <span class="badge bg-light text-dark border font-monospace">
                                                        <i class="bi bi-hash text-muted"></i> DepEd ID: <?php echo htmlspecialchars($req['school_code'] ?? 'N/A'); ?>
                                                    </span>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($req['school_division'] ?? 'Division Office'); ?>
                                                    </span>
                                                    <span class="badge bg-light text-dark border">
                                                        <?php echo htmlspecialchars($req['school_region'] ?? 'Region'); ?>
                                                    </span>
                                                </div>
                                                <p class="mb-0 text-secondary small">
                                                    <strong>Complete Address:</strong><br>
                                                    <i class="bi bi-pin-map-fill text-danger me-1"></i> <?php echo htmlspecialchars($req['school_address'] ?? 'Not specified'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 bg-light rounded-3 text-center border">
                                        <i class="bi bi-building-exclamation text-warning" style="font-size: 2.5rem;"></i>
                                        <p class="text-muted small mb-0 mt-2">No school registration data associated with this account request.</p>
                                    </div>
                                <?php endif; ?>

                                <!-- Inline Approval & Rejection Form Controls (No Modal Pop-up) -->
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="fw-bold text-dark mb-3">
                                        <i class="bi bi-check2-square text-success me-1"></i> System Admin Decision Panel
                                    </h6>

                                    <div class="row g-3">
                                        <!-- Approve Form -->
                                        <div class="col-md-6">
                                            <form method="POST" action="<?php echo $basePath; ?>/admin/role-requests/<?php echo $req['id']; ?>/approve" class="p-3 bg-light rounded-3 border">
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold text-dark mb-1">Approval Note (Optional)</label>
                                                    <input type="text" name="review_note" class="form-control form-control-sm" placeholder="e.g. Verified DepEd credentials">
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-success w-100 fw-bold py-1 text-nowrap">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Approve Application
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Reject Form -->
                                        <div class="col-md-6">
                                            <form method="POST" action="<?php echo $basePath; ?>/admin/role-requests/<?php echo $req['id']; ?>/reject" class="p-3 bg-light rounded-3 border">
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold text-dark mb-1">Rejection Reason <span class="text-danger">*</span></label>
                                                    <input type="text" name="review_note" class="form-control form-control-sm" placeholder="Specify reason..." required>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold py-1 text-nowrap">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Reject Application
                                                </button>
                                            </form>
                                        </div>
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

