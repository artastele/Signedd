<?php
$pageTitle = 'System Admin Dashboard - SignED';
require_once __DIR__ . '/../layouts/header.php';

$base = defined('BASE_PATH') ? BASE_PATH : '';
$userAnalytics = $userAnalytics ?? ['total' => 0, 'principal' => 0, 'sped_teacher' => 0, 'guidance' => 0, 'parent' => 0, 'learner' => 0, 'user' => 0];
$pendingRoleRequests = $pendingRoleRequests ?? [];
$approvedSchoolsCount = $approvedSchoolsCount ?? 0;
$pendingCount = count($pendingRoleRequests);
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header Banner -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1e4072;">
                <i class="bi bi-shield-lock-fill text-danger me-2"></i> System Admin Dashboard
            </h2>
            <p class="text-muted mb-0 small">Overview of user role analytics, pending applications, and system verification requests.</p>
        </div>
        <div>
            <a href="<?php echo $base; ?>/admin/role-requests" class="btn btn-danger position-relative fw-bold shadow-sm px-3">
                <i class="bi bi-person-check-fill me-1"></i> Role Requests
                <?php if ($pendingCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark border border-light">
                        <?php echo $pendingCount; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Pending Applicants Notification Banner -->
    <?php if ($pendingCount > 0): ?>
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border-left: 5px solid #ffc107 !important;">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-warning text-dark p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-bell-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">
                        Pending Applicant Verification Requests (<?php echo $pendingCount; ?>)
                    </h5>
                    <p class="mb-0 text-dark small">
                        There <?php echo $pendingCount === 1 ? 'is' : 'are'; ?> <strong><?php echo $pendingCount; ?> pending application<?php echo $pendingCount === 1 ? '' : 's'; ?></strong> (Principal / Staff Registration) requiring system administrator review.
                    </p>
                </div>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/role-requests" class="btn btn-dark fw-bold btn-sm px-3 py-2 shadow-sm">
                    <i class="bi bi-eye-fill me-1"></i> Review Applications Now
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- User Analytics Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Total Users -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1e4072 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 me-3" style="background: #eef2f7; color: #1e4072;">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold">Total System Users</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($userAnalytics['total']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verified Principals -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #a01422 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 me-3" style="background: #fde8e8; color: #a01422;">
                            <i class="bi bi-person-workspace fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold">School Heads / Principals</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($userAnalytics['principal']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SPED Teachers & Staff -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 me-3" style="background: #e8f5e9; color: #198754;">
                            <i class="bi bi-journal-check fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold">SPED Teachers & Faculty</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($userAnalytics['sped_teacher'] + $userAnalytics['guidance']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrolled Learners & Parents -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #fd7e14 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-3 me-3" style="background: #fff4e6; color: #fd7e14;">
                            <i class="bi bi-house-heart-fill fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold">Parents & Learners</span>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($userAnalytics['parent'] + $userAnalytics['learner']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Breakdown & Quick Actions -->
    <div class="row g-4 mb-4">
        <!-- User Roles Analytics Breakdown -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark fs-6">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i> User Distribution Analytics by Role
                    </h5>
                    <a href="<?php echo $base; ?>/admin/users" class="btn btn-sm btn-outline-primary fw-bold">
                        Manage Users <i class="bi bi-arrow-right me-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php 
                    $total = max(1, $userAnalytics['total']);
                    $rolesData = [
                        ['label' => 'School Principals', 'count' => $userAnalytics['principal'], 'color' => 'bg-danger', 'icon' => 'bi-person-workspace'],
                        ['label' => 'SPED Teachers', 'count' => $userAnalytics['sped_teacher'], 'color' => 'bg-success', 'icon' => 'bi-award-fill'],
                        ['label' => 'Guidance Staff', 'count' => $userAnalytics['guidance'], 'color' => 'bg-info text-dark', 'icon' => 'bi-heart-pulse-fill'],
                        ['label' => 'Parents / Guardians', 'count' => $userAnalytics['parent'], 'color' => 'bg-warning text-dark', 'icon' => 'bi-people'],
                        ['label' => 'Learners / Students', 'count' => $userAnalytics['learner'], 'color' => 'bg-primary', 'icon' => 'bi-mortarboard-fill'],
                        ['label' => 'Unassigned Users', 'count' => $userAnalytics['user'], 'color' => 'bg-secondary', 'icon' => 'bi-person-badge']
                    ];
                    foreach ($rolesData as $rd):
                        $pct = round(($rd['count'] / $total) * 100);
                    ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark small">
                                    <i class="bi <?php echo $rd['icon']; ?> me-1"></i> <?php echo $rd['label']; ?>
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <?php echo number_format($rd['count']); ?> (<?php echo $pct; ?>%)
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar <?php echo $rd['color']; ?>" role="progressbar" style="width: <?php echo $pct; ?>%;" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Administration Links & Verification Status -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark fs-6">
                        <i class="bi bi-sliders text-danger me-2"></i> System Controls & Management
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <a href="<?php echo $base; ?>/admin/role-requests" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="rounded p-2 bg-light text-danger me-3">
                                    <i class="bi bi-person-check-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0">Role & Verification Requests</div>
                                    <small class="text-muted">Approve new School Heads & Principal accounts</small>
                                </div>
                            </div>
                            <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                                <?php echo $pendingCount; ?> Pending
                            </span>
                        </a>

                        <a href="<?php echo $base; ?>/admin/users" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="rounded p-2 bg-light text-primary me-3">
                                    <i class="bi bi-people-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0">User Directory & Roles</div>
                                    <small class="text-muted">View, modify, or lock system user accounts</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border">
                                <?php echo number_format($userAnalytics['total']); ?> Users
                            </span>
                        </a>

                        <a href="<?php echo $base; ?>/admin/activity-logs" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="rounded p-2 bg-light text-success me-3">
                                    <i class="bi bi-shield-check fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0">System Activity Logs</div>
                                    <small class="text-muted">Audit security events, logins, and approvals</small>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>

                        <a href="<?php echo $base; ?>/admin/settings" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded p-2 bg-light text-secondary me-3">
                                    <i class="bi bi-gear-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0">Global System Settings</div>
                                    <small class="text-muted">Configure mail, security, and enrollment settings</small>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification List of Pending Applicant Requests -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3 px-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark fs-6">
                <i class="bi bi-bell-fill text-warning me-2"></i> Applicants Waiting for Admin Approval
            </h5>
            <a href="<?php echo $base; ?>/admin/role-requests" class="btn btn-sm btn-outline-danger fw-bold">
                View All Requests <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-4">
            <?php if (empty($pendingRoleRequests)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle-fill text-success mb-2" style="font-size: 2.5rem;"></i>
                    <h6 class="fw-bold text-dark">No Pending Verification Applications</h6>
                    <p class="small mb-0">All submitted Principal and staff registration requests have been reviewed.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Applicant Name</th>
                                <th>Email</th>
                                <th>Requested Role</th>
                                <th>Submitted Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingRoleRequests as $req): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($req['user_name']); ?></div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($req['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-lock-fill me-1"></i> <?php echo ucwords(str_replace('_', ' ', $req['requested_role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i> <?php echo date('M j, Y g:i A', strtotime($req['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo $base; ?>/admin/role-requests" class="btn btn-sm btn-primary px-3 fw-bold">
                                            <i class="bi bi-check-lg me-1"></i> Review Application
                                        </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

