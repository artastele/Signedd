<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment Review (SPED Teacher)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-clipboard-check"></i> Assessment Review
                </h1>
                <p class="text-muted mt-2">Review and approve/reject learner assessment</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Assessment Status Card -->
        <div class="card mb-4" style="border-top: 4px solid #1e4072;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Student Information</h6>
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($assessment['student_name']); ?></p>
                        <p class="mb-1"><strong>LRN:</strong> <code><?php echo htmlspecialchars($assessment['lrn']); ?></code></p>
                        <p class="mb-1"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($assessment['date_of_birth']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Assessment Information</h6>
                        <p class="mb-1"><strong>Quarter:</strong> <span class="badge" style="background-color: #1e4072;"><?php echo htmlspecialchars($assessment['quarter']); ?></span></p>
                        <p class="mb-1"><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($assessment['submitted_at'])); ?></p>
                        <p class="mb-1">
                            <strong>Status:</strong> 
                            <?php 
                            $statusColor = match($assessment['status']) {
                                'pending' => '#ffc107',
                                'approved' => '#3b6d11',
                                'rejected' => '#dc3545',
                                default => '#6c757d'
                            };
                            ?>
                            <span class="badge" style="background-color: <?php echo $statusColor; ?>;">
                                <?php echo ucfirst($assessment['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section A: Education History -->
        <div class="card mb-4 border-left-navy">
            <div class="card-header bg-light" style="border-left: 4px solid #1e4072;">
                <h5 class="mb-0" style="color: #1e4072;">
                    <i class="fas fa-book"></i> Section A.2: Education History
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Previous School:</strong></p>
                        <p class="text-muted"><?php echo htmlspecialchars($assessment['education_history']['previous_school'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Grade Level:</strong></p>
                        <p class="text-muted"><?php echo htmlspecialchars($assessment['education_history']['grade_level'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>With IEP:</strong></p>
                        <p class="text-muted"><?php echo ucfirst($assessment['education_history']['with_iep'] ?? 'No'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>With Support Services:</strong></p>
                        <p class="text-muted"><?php echo ucfirst($assessment['education_history']['with_support_services'] ?? 'No'); ?></p>
                    </div>
                </div>

                <?php if (!empty($assessment['education_history']['support_services'])): ?>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Support Services Availed:</strong></p>
                            <div class="text-muted">
                                <?php foreach ($assessment['education_history']['support_services'] as $service): ?>
                                    <span class="badge" style="background-color: #1e4072; margin-right: 5px; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($service))); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section B: Assessment Information -->
        <div class="card mb-4 border-left-crimson">
            <div class="card-header bg-light" style="border-left: 4px solid #a01422;">
                <h5 class="mb-0" style="color: #1e4072;">
                    <i class="fas fa-table"></i> Section B: Assessment Information
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead style="background-color: #f9f9f9;">
                            <tr>
                                <th>Assessment Service/s Availed</th>
                                <th>Members of MDT</th>
                                <th>Date/s of Assessment/s</th>
                                <th>Supporting Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($assessment['assessment_info'])): ?>
                                <?php foreach ($assessment['assessment_info'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['service'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['mdt_members'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['assessment_date'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['supporting_documents'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No assessment information provided</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Review Actions -->
        <?php if ($assessment['status'] === 'pending'): ?>
            <div class="card mb-4" style="border-top: 4px solid #a01422;">
                <div class="card-header bg-light">
                    <h5 class="mb-0" style="color: #1e4072;">
                        <i class="fas fa-check-circle"></i> Review Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-lg btn-success w-100" id="approveBtn">
                                <i class="fas fa-check"></i> Approve Assessment
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-lg btn-danger w-100" id="rejectBtn">
                                <i class="fas fa-times"></i> Reject Assessment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> This assessment has already been reviewed.
                        <?php if ($assessment['status'] === 'approved'): ?>
                            <strong>Status: Approved</strong> on <?php echo date('M d, Y', strtotime($assessment['reviewed_at'])); ?>
                        <?php elseif ($assessment['status'] === 'rejected'): ?>
                            <strong>Status: Rejected</strong> on <?php echo date('M d, Y', strtotime($assessment['reviewed_at'])); ?>
                            <br><strong>Reason:</strong> <?php echo htmlspecialchars($assessment['review_note']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mb-4">
            <a href="<?php echo BASE_PATH; ?>/assessment" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Assessments
            </a>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e4072; color: white;">
                <h5 class="modal-title">Reject Assessment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea id="rejectionReason" class="form-control" rows="4" placeholder="Please provide a detailed reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Reject Assessment</button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-crimson {
    border-left: 4px solid #a01422 !important;
}

.border-left-navy {
    border-left: 4px solid #1e4072 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const assessmentId = <?php echo $assessment['id']; ?>;
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));

    // Approve button
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to approve this assessment?')) {
                fetch('<?php echo BASE_PATH; ?>/assessment/' + assessmentId + '/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Assessment approved successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = '<?php echo BASE_PATH; ?>/assessment';
                        }, 2000);
                    } else {
                        showAlert(data.message || 'Error approving assessment', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error approving assessment', 'danger');
                });
            }
        });
    }

    // Reject button
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            rejectModal.show();
        });
    }

    // Confirm reject button
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function() {
            const reason = document.getElementById('rejectionReason').value.trim();
            
            if (!reason) {
                alert('Please provide a reason for rejection');
                return;
            }

            const formData = new FormData();
            formData.append('reason', reason);

            fetch('<?php echo BASE_PATH; ?>/assessment/' + assessmentId + '/reject', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Assessment rejected successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_PATH; ?>/assessment';
                    }, 2000);
                } else {
                    showAlert(data.message || 'Error rejecting assessment', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error rejecting assessment', 'danger');
            });
        });
    }

    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alertDiv);
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
