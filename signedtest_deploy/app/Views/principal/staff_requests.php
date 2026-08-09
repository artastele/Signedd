<?php
$pageTitle = 'Staff Requests - Principal - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-person-check me-2"></i>Staff Role Requests</h1>
            <p class="text-muted small mb-0">Review and approve incoming faculty applications for your school.</p>
        </div>
        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Your Approval Authority:</strong> As Principal, you approve role requests for SPED Teachers, Guidance Counselors, Master Teachers, and General Teachers assigned to your school.
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($requests)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3.5rem;"></i>
                    <p class="text-muted mt-3 mb-0">No staff role requests found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Applicant</th>
                                <th>Requested Role</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <!-- Main Row -->
                                <tr id="row-<?php echo $request['id']; ?>">
                                    <td class="ps-4">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($request['user_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($request['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucwords(str_replace('_', ' ', $request['requested_role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($request['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($request['created_at'])); ?></small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button"
                                                class="btn btn-sm <?php echo $request['status'] === 'pending' ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                                                onclick="toggleDetails(<?php echo $request['id']; ?>)">
                                            <i class="bi bi-chevron-down me-1" id="icon-<?php echo $request['id']; ?>"></i>
                                            <?php echo $request['status'] === 'pending' ? 'Review' : 'View'; ?>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Inline Detail Row (hidden by default) -->
                                <tr id="detail-<?php echo $request['id']; ?>" style="display: none;">
                                    <td colspan="5" class="p-0">
                                        <div class="bg-light border-top border-bottom px-4 py-4">
                                            <div class="row g-4">

                                                <!-- Applicant Info -->
                                                <div class="col-md-5">
                                                    <h6 class="fw-bold text-dark mb-3">
                                                        <i class="bi bi-person-badge me-1 text-primary"></i> Applicant Information
                                                    </h6>
                                                    <table class="table table-borderless table-sm small mb-0">
                                                        <tr>
                                                            <td class="fw-semibold text-muted" style="width:140px;">Full Name</td>
                                                            <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold text-muted">Email</td>
                                                            <td><?php echo htmlspecialchars($request['user_email']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold text-muted">Applied Role</td>
                                                            <td>
                                                                <span class="badge bg-secondary">
                                                                    <?php echo ucwords(str_replace('_', ' ', $request['requested_role'])); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $docs = json_decode($request['submitted_docs'], true);
                                                        if ($docs && isset($docs['employee_number']) && !empty($docs['employee_number'])):
                                                        ?>
                                                        <tr>
                                                            <td class="fw-semibold text-muted">Employee / DepEd ID</td>
                                                            <td><?php echo htmlspecialchars($docs['employee_number']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                        <?php if ($request['status'] !== 'pending'): ?>
                                                        <tr>
                                                            <td class="fw-semibold text-muted">Reviewed By</td>
                                                            <td><?php echo htmlspecialchars($request['reviewer_name'] ?? 'N/A'); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold text-muted">Review Note</td>
                                                            <td><?php echo htmlspecialchars($request['review_note'] ?? '—'); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </table>

                                                    <!-- Submitted Documents -->
                                                    <?php if ($docs && isset($docs['files']) && !empty($docs['files'])): ?>
                                                        <h6 class="fw-bold text-dark mb-2 mt-3">
                                                            <i class="bi bi-paperclip me-1 text-primary"></i> Submitted Documents
                                                        </h6>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <?php foreach ($docs['files'] as $type => $path): ?>
                                                                <a href="<?php echo $basePath . '/' . $path; ?>" target="_blank"
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>
                                                                    <?php echo ucwords(str_replace('_', ' ', $type)); ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted small mt-3 mb-0"><i class="bi bi-paperclip me-1"></i>No documents uploaded.</p>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Approve / Reject Actions (only for pending) -->
                                                <?php if ($request['status'] === 'pending'): ?>
                                                <div class="col-md-7">
                                                    <h6 class="fw-bold text-dark mb-3">
                                                        <i class="bi bi-check2-square me-1 text-success"></i> Review Decision
                                                    </h6>
                                                    <div class="row g-3">
                                                        <!-- Approve -->
                                                        <div class="col-md-6">
                                                            <form method="POST" action="<?php echo $basePath; ?>/principal/staff-requests/<?php echo $request['id']; ?>/approve">
                                                                <div class="mb-2">
                                                                    <label class="form-label fw-semibold small text-muted">Review Note <span class="fw-normal">(Optional)</span></label>
                                                                    <textarea name="review_note" class="form-control form-control-sm" rows="3" placeholder="Add a note for the applicant..."></textarea>
                                                                </div>
                                                                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                                                                    <i class="bi bi-check-circle-fill me-1"></i> Approve Application
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <!-- Reject -->
                                                        <div class="col-md-6">
                                                            <form method="POST" action="<?php echo $basePath; ?>/principal/staff-requests/<?php echo $request['id']; ?>/reject">
                                                                <div class="mb-2">
                                                                    <label class="form-label fw-semibold small text-muted">Rejection Reason <span class="text-danger">*</span></label>
                                                                    <textarea name="review_note" class="form-control form-control-sm border-danger" rows="3" placeholder="State the reason for rejection..." required></textarea>
                                                                </div>
                                                                <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">
                                                                    <i class="bi bi-x-circle-fill me-1"></i> Reject Application
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <div class="col-md-7 d-flex align-items-center">
                                                    <?php if ($request['status'] === 'approved'): ?>
                                                        <div class="alert alert-success border-0 w-100 mb-0 py-3">
                                                            <i class="bi bi-check-circle-fill me-2"></i>
                                                            <strong>Approved</strong> — This applicant has been granted access.
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-danger border-0 w-100 mb-0 py-3">
                                                            <i class="bi bi-x-circle-fill me-2"></i>
                                                            <strong>Rejected</strong> — This application was declined.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>

                                            </div><!-- end row -->
                                        </div>
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

<script>
function toggleDetails(id) {
    const detailRow = document.getElementById('detail-' + id);
    const icon = document.getElementById('icon-' + id);
    const isOpen = detailRow.style.display !== 'none';

    // Close all others
    document.querySelectorAll('[id^="detail-"]').forEach(function(row) {
        row.style.display = 'none';
    });
    document.querySelectorAll('[id^="icon-"]').forEach(function(ic) {
        ic.classList.remove('bi-chevron-up');
        ic.classList.add('bi-chevron-down');
    });

    // Toggle clicked
    if (!isOpen) {
        detailRow.style.display = 'table-row';
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
