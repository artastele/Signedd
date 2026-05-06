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

    <!-- LRN Notification Alert (if learner account created) -->
    <?php
    // Check for enrollments with learner accounts created
    $lrnNotifications = [];
    foreach ($enrollments as $enrollment) {
        if ($enrollment['learner_account_created'] && !empty($enrollment['lrn'])) {
            // Check if notification was dismissed in this session
            $dismissKey = 'lrn_dismissed_' . $enrollment['id'];
            if (!isset($_SESSION[$dismissKey])) {
                $lrnNotifications[] = $enrollment;
            }
        }
    }
    ?>

    <?php if (!empty($lrnNotifications)): ?>
        <?php foreach ($lrnNotifications as $lrnEnrollment): ?>
        <div class="alert alert-success alert-dismissible alert-permanent fade show mb-4" 
             id="lrn-alert-<?php echo $lrnEnrollment['id']; ?>"
             style="background: linear-gradient(135deg, #3b6d11 0%, #4a8514 100%); 
                    border: none; 
                    border-left: 5px solid #2d5409;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <button type="button" class="btn-close btn-close-white" 
                    onclick="dismissLrnNotification(<?php echo $lrnEnrollment['id']; ?>)"></button>
            
            <div class="d-flex align-items-center">
                <div class="me-3" style="font-size: 3rem;">
                    <i class="bi bi-check-circle-fill text-white"></i>
                </div>
                <div class="flex-grow-1 text-white">
                    <h4 class="alert-heading mb-2">
                        <i class="bi bi-person-check-fill"></i> Learner Account Created!
                    </h4>
                    <p class="mb-2">
                        <strong><?php echo htmlspecialchars($lrnEnrollment['first_name'] . ' ' . $lrnEnrollment['last_name']); ?></strong>'s 
                        enrollment has been verified and learner account is ready.
                    </p>
                    
                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <div class="card bg-white text-dark">
                                <div class="card-body py-2">
                                    <small class="text-muted d-block">Learner Reference Number (LRN)</small>
                                    <h3 class="mb-0" style="color: #3b6d11; font-weight: bold; letter-spacing: 2px;">
                                        <?php echo htmlspecialchars($lrnEnrollment['lrn']); ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="card bg-white text-dark">
                                <div class="card-body py-2">
                                    <small class="text-muted d-block">Login Credentials</small>
                                    <p class="mb-0">
                                        <strong>Username:</strong> <?php echo htmlspecialchars($lrnEnrollment['lrn']); ?><br>
                                        <small class="text-muted">Password sent to your email</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="bg-white my-3">
                    
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            Your child can now login using their <strong>LRN as username</strong>. 
                            The temporary password was sent to your email. Please change it after first login.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Rejected Enrollment Alert (if any) -->
    <?php
    $rejectedEnrollments = array_filter($enrollments, function($e) {
        return $e['status'] === 'rejected' && !empty($e['review_note']);
    });
    ?>
    <?php if (!empty($rejectedEnrollments)): ?>
        <?php foreach ($rejectedEnrollments as $rejected): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                Enrollment Rejected - <?php echo htmlspecialchars($rejected['first_name'] . ' ' . $rejected['last_name']); ?>
            </h5>
            <hr>
            <p class="mb-2"><strong>Reason for Rejection:</strong></p>
            <p class="mb-3" style="background: rgba(255,255,255,0.3); padding: 10px; border-radius: 5px;">
                <?php echo nl2br(htmlspecialchars($rejected['review_note'])); ?>
            </p>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_PATH; ?>/enrollment/view/<?php echo $rejected['id']; ?>" 
                   class="btn btn-sm btn-light">
                    <i class="bi bi-eye"></i> View Details
                </a>
                <a href="<?php echo BASE_PATH; ?>/enrollment?type=<?php echo $rejected['enrollment_type']; ?>" 
                   class="btn btn-sm btn-warning">
                    <i class="bi bi-arrow-repeat"></i> Resubmit Enrollment
                </a>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

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

<script>
function dismissLrnNotification(enrollmentId) {
    // Send AJAX request to dismiss notification
    fetch('<?php echo BASE_PATH; ?>/dashboard/dismiss-lrn-notification', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ enrollment_id: enrollmentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide the alert
            const alert = document.getElementById('lrn-alert-' + enrollmentId);
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }
    })
    .catch(error => {
        console.error('Error dismissing notification:', error);
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
