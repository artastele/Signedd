<?php
$pageTitle = 'SPED Teacher Dashboard - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">SPED Teacher Dashboard</h1>
    
    <!-- Pending Enrollments Alert -->
    <?php if (isset($pendingCount) && $pendingCount > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2.5rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">
                    <i class="bi bi-clipboard-check"></i> Pending Enrollment Applications
                </h5>
                <p class="mb-2">
                    You have <strong><?php echo $pendingCount; ?></strong> enrollment application<?php echo $pendingCount > 1 ? 's' : ''; ?> waiting for review.
                </p>
                <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/enrollment/review" class="btn btn-warning btn-sm">
                    <i class="bi bi-eye"></i> Review Now
                </a>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo isset($pendingCount) ? $pendingCount : 0; ?></h3>
                    <small class="text-muted">Pending Enrollments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0">-</h3>
                    <small class="text-muted">Verified Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data text-info" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0">-</h3>
                    <small class="text-muted">Assessments Done</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-book text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0">-</h3>
                    <small class="text-muted">Active IEPs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Enrollments List -->
    <?php if (isset($pendingEnrollments) && !empty($pendingEnrollments)): ?>
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check"></i> Recent Pending Enrollments
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Grade Level</th>
                            <th>Enrollment Type</th>
                            <th>Submitted</th>
                            <th>Documents</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Show only first 5 pending enrollments
                        $displayEnrollments = array_slice($pendingEnrollments, 0, 5);
                        foreach ($displayEnrollments as $enrollment): 
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo ucfirst($enrollment['enrollment_type']); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo date('M j, Y', strtotime($enrollment['submitted_at'])); ?></small><br>
                                <small class="text-muted"><?php echo date('g:i A', strtotime($enrollment['submitted_at'])); ?></small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php
                                    // Get document count
                                    require_once __DIR__ . '/../../Models/EnrollmentModel.php';
                                    $tempModel = new EnrollmentModel();
                                    $docs = $tempModel->getDocuments($enrollment['id']);
                                    echo count($docs) . ' file' . (count($docs) != 1 ? 's' : '');
                                    ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/enrollment/review/<?php echo $enrollment['id']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pendingCount > 5): ?>
            <div class="text-center mt-3">
                <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/enrollment/review" class="btn btn-outline-warning">
                    <i class="bi bi-list"></i> View All <?php echo $pendingCount; ?> Pending Enrollments
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <h5 class="mb-3">Quick Actions</h5>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Verify Enrollment</h5>
                    <p class="text-muted small">Review and approve enrollment applications</p>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/verification" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Go to Verification
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Conduct Assessment</h5>
                    <p class="text-muted small">Assess student abilities and needs</p>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/assessment" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Go to Assessments
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-book text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Implement IEP</h5>
                    <p class="text-muted small">Create learning materials and activities</p>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep/implementation" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Go to IEP Implementation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
