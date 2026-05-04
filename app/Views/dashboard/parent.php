<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1 Part E
// Last modified: 2026-05-02
// Part of: SPED LMS — Parent Dashboard with Enrollment Tracking

$pageTitle = 'Parent Dashboard - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Parent Dashboard</h1>

    <!-- Statistics Cards - Simplified -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h4>
                    <small class="text-muted">Total Enrollments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0"><?php echo $stats['pending'] ?? 0; ?></h4>
                    <small class="text-muted">Pending Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0"><?php echo $stats['approved'] ?? 0; ?></h4>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0"><?php echo $stats['rejected'] ?? 0; ?></h4>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollments List -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">My Children's Enrollments</h5>
        </div>
        <div class="card-body">
            <?php if (empty($enrollments)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="mt-3 text-muted">No enrollments yet</p>
                    <a href="<?php echo BASE_PATH; ?>/enrollment" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Enroll Your Child
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>Grade Level</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <?php
                                // Status badge
                                $statusClass = '';
                                switch ($enrollment['status']) {
                                    case 'pending':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Pending';
                                        break;
                                    case 'verified':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Approved';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Rejected';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                        $statusText = ucfirst($enrollment['status']);
                                }

                                // Progress calculation
                                $total = $enrollment['total_documents'] ?? 0;
                                $approved = $enrollment['approved_documents'] ?? 0;
                                $progress = $total > 0 ? round(($approved / $total) * 100) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($enrollment['submitted_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($total > 0): ?>
                                            <small><?php echo $approved; ?>/<?php echo $total; ?> docs</small>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo $progress; ?>%"
                                                     aria-valuenow="<?php echo $progress; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">No documents</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_PATH; ?>/enrollment/view/<?php echo $enrollment['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <?php if ($enrollment['status'] === 'rejected'): ?>
                                            <a href="<?php echo BASE_PATH; ?>/enrollment?type=<?php echo $enrollment['enrollment_type']; ?>" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-arrow-repeat"></i> Resubmit
                                            </a>
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

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <a href="<?php echo BASE_PATH; ?>/enrollment" class="btn btn-primary w-100">
                <i class="bi bi-plus-circle"></i> Enroll Another Child
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <a href="<?php echo BASE_PATH; ?>/enrollment/status" class="btn btn-outline-secondary w-100">
                <i class="bi bi-list-check"></i> View All Enrollments
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
