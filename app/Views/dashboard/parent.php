<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1 Part E
// Last modified: 2026-05-02
// Part of: SignED — Parent Dashboard with Enrollment Tracking

$pageTitle = 'Parent Dashboard - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Parent Dashboard</h1>

    <!-- Enrollment Approved Confirmation Cards -->
    <?php
    require_once __DIR__ . '/../../Models/StudentModel.php';
    $parentDashStudentModel = new StudentModel();
    $verifiedEnrollments = array_filter($enrollments, function($e) {
        return !empty($e['learner_account_created']);
    });
    ?>

    <?php if (!empty($verifiedEnrollments)): ?>
        <?php foreach ($verifiedEnrollments as $ve): ?>
        <?php
        $veStudentRecord = $parentDashStudentModel->findByEnrollmentId((int)$ve['id']);
        $veStudentIdCode = $veStudentRecord['student_id'] ?? null;
        ?>
        <div class="card mb-4 lrn-confirm-card" id="lrn-card-<?php echo $ve['id']; ?>"
             style="border: 1px solid #e0e0e0; border-top: 3px solid #a01422; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: opacity 0.6s ease, transform 0.6s ease;">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                    <!-- Left: Status + Name -->
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 42px; height: 42px; background: #f0f7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-patch-check-fill" style="color: #3b6d11; font-size: 1.3rem;"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge" style="background: #3b6d11; font-size: 0.7rem;">Enrolled</span>
                                <small class="text-muted"><?php echo htmlspecialchars($ve['grade_level_to_enroll'] ?? ''); ?></small>
                            </div>
                            <h6 class="mb-0 fw-bold" style="color: #1e4072;">
                                <?php echo htmlspecialchars($ve['first_name'] . ' ' . $ve['last_name']); ?>
                            </h6>
                        </div>
                    </div>

                    <!-- Middle: Student ID -->
                    <div class="text-center px-4" style="border-left: 1px solid #eee; border-right: 1px solid #eee;">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">STUDENT ID</small>
                        <span class="fw-bold" style="color: #a01422; font-size: 1.25rem; letter-spacing: 2px;">
                            <?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($veStudentIdCode)); ?>
                        </span>
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                            DepEd LRN: <?php echo htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($ve['lrn'] ?? $veStudentRecord['lrn'] ?? null)); ?>
                        </small>
                    </div>

                    <!-- Right: Credentials -->
                    <div style="font-size: 0.83rem;">
                        <div class="mb-1">
                            <span class="text-muted">Username:</span>
                            <strong style="color: #1e4072;"><?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($veStudentIdCode)); ?></strong>
                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Password:</span>
                            <span class="badge" style="background: #a01422; font-size: 0.72rem;">
                                <i class="bi bi-envelope me-1"></i>Sent to your email
                            </span>
                        </div>
                        <div>
                            <span class="text-muted">Login at:</span>
                            <a href="<?php echo BASE_PATH; ?>/login" style="color: #a01422; font-size: 0.8rem;">
                                <?php echo getenv('APP_URL') ?: 'SignED'; ?>
                            </a>
                        </div>
                    </div>

                    <!-- Close button -->
                    <button type="button"
                            onclick="dismissLrnCard(<?php echo $ve['id']; ?>)"
                            style="background: none; border: none; color: #bbb; font-size: 1.1rem; cursor: pointer; padding: 0; line-height: 1; align-self: flex-start;"
                            title="Dismiss">
                        <i class="bi bi-x-lg"></i>
                    </button>

                </div>

                <!-- Footer: countdown -->
                <div class="mt-3 pt-2 d-flex align-items-center justify-content-between"
                     style="border-top: 1px solid #f0f0f0; font-size: 0.78rem; color: #999;">
                    <span>
                        <i class="bi bi-info-circle me-1"></i>
                        Use your Student ID as username. Change the temporary password after first login.
                    </span>
                    <span class="lrn-countdown" id="countdown-<?php echo $ve['id']; ?>" style="color: #bbb; white-space: nowrap; margin-left: 12px;">
                        Closing in 30s
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
    (function() {
        document.querySelectorAll('.lrn-confirm-card').forEach(function(card) {
            const id = card.id.replace('lrn-card-', '');
            const countdownEl = document.getElementById('countdown-' + id);
            let seconds = 30;

            const interval = setInterval(function() {
                seconds--;
                if (countdownEl) countdownEl.textContent = 'Closing in ' + seconds + 's';
                if (seconds <= 0) {
                    clearInterval(interval);
                    dismissLrnCard(id);
                }
            }, 1000);
        });
    })();

    function dismissLrnCard(id) {
        const card = document.getElementById('lrn-card-' + id);
        if (!card) return;
        card.style.opacity = '0';
        card.style.transform = 'translateY(-8px)';
        setTimeout(function() {
            card.style.display = 'none';
        }, 600);
    }
    </script>

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

    <?php
    // Load notifications for dashboard widget
    require_once __DIR__ . '/../../Models/NotificationModel.php';
    $parentNotifModel = new NotificationModel();
    $latestNotifications = $parentNotifModel->getByUserId($_SESSION['user_id'], 5);

    if (!function_exists('timeElapsedString')) {
        function timeElapsedString($datetime, $full = false) {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = array(
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            );
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }
    }
    ?>

    <div class="row">
        <!-- Left: Enrollments List -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100 mb-0">
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
                            <table class="table table-hover align-middle">
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
                                                $statusClass = 'bg-warning bg-opacity-10 text-warning border border-warning';
                                                $statusText = 'Pending';
                                                break;
                                            case 'verified':
                                                $statusClass = 'bg-success bg-opacity-10 text-success border border-success';
                                                $statusText = 'Approved';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'bg-danger bg-opacity-10 text-danger border border-danger';
                                                $statusText = 'Rejected';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary bg-opacity-10 text-secondary border border-secondary';
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
                                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                                        <span class="text-muted"><?php echo $approved; ?>/<?php echo $total; ?> docs</span>
                                                        <span class="fw-semibold text-primary"><?php echo $progress; ?>%</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
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
        </div>

        <!-- Right: Recent Notifications Widget -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 mb-0 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="mb-0 fw-bold" style="color: #1e4072; font-size: 1.05rem;">
                        <i class="bi bi-bell-fill me-2 text-warning"></i>Recent Notifications
                    </h5>
                    <?php if (!empty($latestNotifications)): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.72rem;">Latest</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($latestNotifications)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash text-secondary" style="font-size: 2.2rem;"></i>
                            <p class="mb-0 mt-2 small">No recent notifications</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($latestNotifications as $notif): ?>
                                <?php
                                $notifData = $notif['data'] ? json_decode($notif['data'], true) : [];
                                $notifLink = '';
                                if ($notif['type'] === 'role_rejected') {
                                    $notifLink = BASE_PATH . '/role/select';
                                } else if ($notif['type'] === 'enrollment_approved') {
                                    $notifLink = BASE_PATH . '/enrollment/status';
                                } else if ($notif['type'] === 'iep_signature_request' && !empty($notifData['iep_id']) && !empty($notifData['signatory_id'])) {
                                    $notifLink = BASE_PATH . '/iep/sign/' . $notifData['iep_id'] . '/' . $notifData['signatory_id'];
                                } else if ($notif['type'] === 'placement_confirmed' && !empty($notifData['iep_id'])) {
                                    $notifLink = BASE_PATH . '/iep/' . $notifData['iep_id'] . '/placement-notice';
                                } else if (!empty($notifData['iep_id'])) {
                                    $notifLink = BASE_PATH . '/iep';
                                }

                                $bgType = 'info';
                                $iconName = 'info-circle-fill';
                                switch ($notif['type']) {
                                    case 'role_approved':
                                    case 'enrollment_approved':
                                    case 'email_verified':
                                    case 'placement_confirmed':
                                        $bgType = 'success';
                                        $iconName = 'check-circle-fill';
                                        break;
                                    case 'role_rejected':
                                    case 'enrollment_rejected':
                                    case 'document_rejected':
                                        $bgType = 'danger';
                                        $iconName = 'x-circle-fill';
                                        break;
                                    case 'enrollment_submitted':
                                    case 'new_enrollment':
                                        $bgType = 'primary';
                                        $iconName = 'file-earmark-text-fill';
                                        break;
                                    case 'iep_signature_request':
                                        $bgType = 'warning';
                                        $iconName = 'pen-fill';
                                        break;
                                }
                                ?>
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-<?php echo $bgType; ?> bg-opacity-10 text-<?php echo $bgType; ?>" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="bi bi-<?php echo $iconName; ?>" style="font-size: 1.1rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <h6 class="mb-0 fw-semibold text-dark text-truncate" style="font-size: 0.88rem; max-width: 150px;">
                                                    <?php echo htmlspecialchars($notif['title']); ?>
                                                </h6>
                                                <?php if (!$notif['is_read']): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill" style="font-size: 0.65rem;">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-1 text-muted text-break" style="font-size: 0.8rem; line-height: 1.3;">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </p>
                                            <div class="d-flex align-items-center justify-content-between mt-2">
                                                <small class="text-muted" style="font-size: 0.72rem;">
                                                    <?php echo timeElapsedString($notif['created_at']); ?>
                                                </small>
                                                <?php if ($notifLink): ?>
                                                    <a href="<?php echo $notifLink; ?>" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" style="font-size: 0.75rem; color: #1e4072;">
                                                        Action <i class="bi bi-arrow-right ms-0"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
        <div class="col-md-6 mb-3">
            <a href="<?php echo BASE_PATH; ?>/iep" class="btn btn-outline-secondary w-100">
                <i class="bi bi-diagram-3"></i> View IEP / Transition Updates
            </a>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
