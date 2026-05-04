<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment History

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
                    <i class="fas fa-history"></i> Assessment History
                </h1>
                <p class="text-muted mt-2">View all assessment versions for <?php echo htmlspecialchars($student['student_name']); ?></p>
            </div>
        </div>

        <!-- Student Information Card -->
        <div class="card mb-4" style="border-top: 4px solid #1e4072;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Student Name:</strong> <?php echo htmlspecialchars($student['student_name']); ?></p>
                        <p class="mb-1"><strong>LRN:</strong> <code><?php echo htmlspecialchars($student['lrn']); ?></code></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($student['date_of_birth']); ?></p>
                        <p class="mb-1"><strong>Total Assessments:</strong> <strong><?php echo count($history); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessment Timeline -->
        <?php if (empty($history)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No assessments found for this student</p>
                </div>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($history as $index => $assessment): ?>
                    <div class="card mb-3">
                        <div class="card-header" style="background-color: #f9f9f9; border-left: 4px solid #1e4072;">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-0">
                                        <span class="badge" style="background-color: #1e4072;">
                                            <?php echo htmlspecialchars($assessment['quarter']); ?>
                                        </span>
                                        Assessment
                                    </h6>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">
                                        Submitted: <?php echo date('M d, Y H:i', strtotime($assessment['submitted_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <p class="text-muted mb-1"><small>Status</small></p>
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
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1"><small>Submitted By</small></p>
                                    <p class="mb-0"><small><?php echo htmlspecialchars($assessment['parent_name']); ?></small></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1"><small>Reviewed By</small></p>
                                    <p class="mb-0"><small>
                                        <?php echo $assessment['reviewed_by'] ? 'SPED Teacher' : 'Pending'; ?>
                                    </small></p>
                                </div>
                            </div>

                            <?php if ($assessment['status'] === 'rejected' && $assessment['review_note']): ?>
                                <div class="alert alert-warning mb-3">
                                    <strong>Rejection Reason:</strong>
                                    <p class="mb-0"><?php echo htmlspecialchars($assessment['review_note']); ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- Education History -->
                            <div class="mb-3">
                                <h6 style="color: #1e4072;">Education History</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><small><strong>Previous School:</strong></small></p>
                                        <p class="text-muted mb-2"><small><?php echo htmlspecialchars($assessment['education_history']['previous_school'] ?? 'N/A'); ?></small></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><small><strong>Grade Level:</strong></small></p>
                                        <p class="text-muted mb-2"><small><?php echo htmlspecialchars($assessment['education_history']['grade_level'] ?? 'N/A'); ?></small></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><small><strong>With IEP:</strong></small></p>
                                        <p class="text-muted mb-2"><small><?php echo ucfirst($assessment['education_history']['with_iep'] ?? 'No'); ?></small></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><small><strong>With Support Services:</strong></small></p>
                                        <p class="text-muted mb-2"><small><?php echo ucfirst($assessment['education_history']['with_support_services'] ?? 'No'); ?></small></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Assessment Information -->
                            <div class="mb-3">
                                <h6 style="color: #1e4072;">Assessment Information</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead style="background-color: #f9f9f9;">
                                            <tr>
                                                <th>Service</th>
                                                <th>MDT Members</th>
                                                <th>Date</th>
                                                <th>Documents</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($assessment['assessment_info'])): ?>
                                                <?php foreach ($assessment['assessment_info'] as $item): ?>
                                                    <tr>
                                                        <td><small><?php echo htmlspecialchars($item['service'] ?? ''); ?></small></td>
                                                        <td><small><?php echo htmlspecialchars($item['mdt_members'] ?? ''); ?></small></td>
                                                        <td><small><?php echo htmlspecialchars($item['assessment_date'] ?? ''); ?></small></td>
                                                        <td><small><?php echo htmlspecialchars($item['supporting_documents'] ?? ''); ?></small></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted"><small>No assessment information</small></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-3">
                                <a href="<?php echo BASE_PATH; ?>/assessment/view/<?php echo $assessment['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Full Assessment
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="<?php echo BASE_PATH; ?>/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline .card {
    margin-left: 0;
}

.timeline .card::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 30px;
    width: 12px;
    height: 12px;
    background-color: #a01422;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px #a01422;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
