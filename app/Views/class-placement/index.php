<?php
// Class Placement Notice view for Process 13
$pageTitle = 'Class Placement Notice — SignED';
require_once __DIR__ . '/../layouts/header.php';
$role = $_SESSION['role'];
$basePath = BASE_PATH;
$isMainstreamed = ($iep['status'] === 'mainstreamed');
$hasAssignment = !empty($workflow['assignment']);
$isAssignedTeacher = ($hasAssignment && (int)$workflow['assignment']['general_teacher_id'] === (int)$_SESSION['user_id']);
$canReview = (!$isMainstreamed && ($role === 'general_teacher' && $isAssignedTeacher) || $role === 'admin');
require_once __DIR__ . '/../../Models/StudentModel.php';
$classPlacementRec = (new StudentModel())->findById((int)($iep['student_id'] ?? 0));
$classPlacementStudentCode = $classPlacementRec['student_id'] ?? null;
$activeStep = 13;
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4" style="max-width: 1200px;">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="mb-1" style="color:#1e4072; font-weight:700;">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Regular Class Placement Review
                </h1>
                <p class="text-muted mb-0">Part 7 — Transition & Mainstream Decision</p>
            </div>
            <div>
                <a href="<?= $basePath ?>/iep" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to IEPs
                </a>
            </div>
        </div>

        <!-- Alert messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Wizard Navigation -->
        <?php require_once __DIR__ . '/../layouts/transition_nav.php'; ?>

        <?php if ($isMainstreamed || ($iep['status'] ?? '') === 'mainstreamed'): ?>
            <div class="alert alert-success py-3 mb-4 d-flex align-items-center shadow-sm" style="border-left: 5px solid #3b6d11; background-color: #f4faf0;">
                <i class="bi bi-check-circle-fill me-3 fs-3 text-success"></i>
                <div>
                    <h5 class="alert-heading text-success mb-1" style="font-weight: 600;">Student Mainstreamed</h5>
                    <p class="mb-0 small text-muted">This student has been successfully placed in a regular education class and archived from active SPED tracking.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Student Profile Overview -->
        <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
            <div class="card-body p-4 bg-white">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div class="bg-light text-primary rounded p-3 d-flex align-items-center justify-content-center border" style="width: 70px; height: 70px; border-radius: 12px !important;">
                        <i class="bi bi-person-badge fs-1" style="color: #1e4072;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold" style="color: #1e4072;"><?= htmlspecialchars($iep['student_name']) ?></h4>
                        <span class="text-muted small">Student ID: <strong><?= htmlspecialchars(StudentDisplayHelper::formatStudentId($classPlacementStudentCode)) ?></strong> · DepEd LRN: <strong><?= htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($iep['lrn'] ?? null)) ?></strong></span>
                        <span class="mx-2 text-muted">|</span>
                        <span class="text-muted small">Grade Level: <strong><?= htmlspecialchars($iep['grade_level_to_enroll'] ?? 'N/A') ?></strong></span>
                        <span class="mx-2 text-muted">|</span>
                        <span class="text-muted small">School Year: <strong><?= htmlspecialchars($iep['school_year'] ?? 'N/A') ?></strong></span>
                    </div>
                    <div class="ms-md-auto">
                        <?php if ($isMainstreamed || ($iep['status'] ?? '') === 'mainstreamed'): ?>
                            <span class="badge bg-success p-2 fs-6"><i class="bi bi-check-lg me-1"></i>Mainstreamed</span>
                        <?php else: ?>
                            <span class="badge bg-primary p-2 fs-6"><i class="bi bi-activity me-1"></i>Active SPED</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-3 font-weight-bold" style="color: #1e4072;">Transition Chain Summary Snapshot</h3>

        <!-- Summary Cards Grid -->
        <div class="row g-4 mb-4">
            <!-- 1. IEP Goals Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #1e4072 !important;">
                    <div class="card-body p-4 bg-white">
                        <h5 class="card-title font-weight-bold mb-3" style="color: #1e4072;"><i class="bi bi-file-earmark-medical me-2"></i>IEP Objectives</h5>
                        <?php if (empty($iepSteps)): ?>
                            <p class="text-muted small mb-0">No IEP goals found.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush small" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($iepSteps as $step): ?>
                                    <li class="list-group-item px-0 py-2 border-light-subtle d-flex align-items-start gap-2 bg-transparent text-muted">
                                        <i class="bi bi-dot fs-5 text-primary"></i>
                                        <div>
                                            <strong class="text-dark small">[<?= htmlspecialchars($step['pdsp_domain']) ?>]</strong>
                                            <span><?= htmlspecialchars($step['goal_text']) ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Transition Readiness Status -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #3b6d11 !important;">
                    <div class="card-body p-4 bg-white">
                        <h5 class="card-title font-weight-bold mb-3" style="color: #3b6d11;"><i class="bi bi-shield-check me-2"></i>Transition Readiness</h5>
                        <?php if (empty($readiness)): ?>
                            <p class="text-muted small mb-0">No readiness records found.</p>
                        <?php else: ?>
                            <div class="mb-3">
                                <span class="badge p-2 text-uppercase font-weight-bold" style="background-color: <?= ($readiness['overall_status'] === 'ready') ? '#3b6d11' : (($readiness['overall_status'] === 'partial') ? '#ffc107' : '#a01422') ?>; color: white;">
                                    <?= htmlspecialchars($readiness['readiness_result'] ?? 'Pending') ?>
                                </span>
                            </div>
                            <strong class="small text-muted text-uppercase d-block mb-1">Teacher Recommendation:</strong>
                            <p class="small text-muted p-2 rounded border bg-light shadow-xs mb-0">
                                <?= htmlspecialchars($readiness['teacher_recommendation'] ?? 'No recommendation notes.') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 3. ITP Transition Plan -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #17a2b8 !important;">
                    <div class="card-body p-4 bg-white">
                        <h5 class="card-title font-weight-bold mb-3" style="color: #17a2b8;"><i class="bi bi-arrow-left-right me-2"></i>ITP Transition Plan</h5>
                        <?php if (empty($itp)): ?>
                            <p class="text-muted small mb-0">No transition plan found.</p>
                        <?php else: ?>
                            <div class="mb-2">
                                <span class="small text-muted font-weight-bold">Suggested Point of Entry:</span>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($itp['point_of_entry'] ?? 'N/A') ?></span>
                            </div>
                            <strong class="small text-muted text-uppercase d-block mb-1">Key Recommendations (End of SY):</strong>
                            <p class="small text-muted p-2 rounded border bg-light shadow-xs mb-0" style="max-height: 120px; overflow-y: auto;">
                                <?= nl2br(htmlspecialchars($itpRecommendationsEnd ?? 'No recommendations set.')) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 4. ITGP Goal Plan -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #fd7e14 !important;">
                    <div class="card-body p-4 bg-white">
                        <h5 class="card-title font-weight-bold mb-3" style="color: #fd7e14;"><i class="bi bi-journal-text me-2"></i>ITGP Goal Plan</h5>
                        <?php if (empty($itgp)): ?>
                            <p class="text-muted small mb-0">No goal plan found.</p>
                        <?php else: ?>
                            <div class="mb-2">
                                <strong class="small text-muted text-uppercase d-block mb-1">Transition Goal:</strong>
                                <p class="small text-dark font-weight-bold mb-2"><?= htmlspecialchars($itgp['goal'] ?? '') ?></p>
                            </div>
                            <div class="mb-2">
                                <strong class="small text-muted text-uppercase d-block mb-1">Learning Packages:</strong>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($itgp['learning_packages'] ?? 'N/A') ?></span>
                            </div>
                            <strong class="small text-muted text-uppercase d-block mb-1">Key Enabling Activities:</strong>
                            <ul class="mb-0 ps-3 small text-muted" style="max-height: 100px; overflow-y: auto;">
                                <?php foreach ($itgpActivities as $act): ?>
                                    <?php if (!empty($act['activities'])): ?>
                                        <li><?= htmlspecialchars($act['activities']) ?> (<?= htmlspecialchars($act['competency_skill'] ?? '') ?>)</li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placement Decisions Trigger -->
        <?php if ($canReview): ?>
            <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; background-color: #f8f9fa;">
                <div class="card-body p-4 text-center">
                    <h5 class="font-weight-bold text-dark mb-3"><i class="bi bi-check2-square me-2"></i>Determine Placement Decision</h5>
                    <p class="text-muted small mb-4">Please carefully review the transition summary data before selecting one of the actions below.</p>
                    
                    <form id="placementForm" method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/placement-notice">
                        <input type="hidden" name="status" id="statusInput" value="">
                        <input type="hidden" name="hold_reason" id="holdReasonInput" value="">

                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <!-- Confirm Placement Button -->
                            <button type="button" class="btn btn-lg text-white px-5 shadow-xs" id="confirmPlacementBtn" style="background-color: #a01422; font-weight: 600; border-radius: 8px;">
                                <i class="bi bi-check-lg me-2"></i>Confirm Placement
                            </button>
                            
                            <!-- Not Ready / Hold Button -->
                            <button type="button" class="btn btn-lg btn-outline-dark px-5 shadow-xs" id="holdPlacementBtn" style="font-weight: 600; border-radius: 8px; border-color: #1e4072; color: #1e4072;">
                                <i class="bi bi-pause-fill me-2"></i>Not Ready / Hold
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Placement Decisions Log History -->
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header text-white py-3" style="background-color: #1e4072;">
                <h5 class="mb-0 font-weight-bold"><i class="bi bi-clock-history me-2"></i>Placement Decision Log History</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($placementHistory)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-card-text fs-2"></i>
                        <p class="mt-2 mb-0">No past placement history records found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date/Time</th>
                                    <th>Decision Maker</th>
                                    <th>Decision Status</th>
                                    <th class="pe-4">Hold Reason / Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($placementHistory as $log): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small">
                                            <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($log['reviewer_name']) ?></strong><br>
                                            <span class="badge bg-secondary small"><?= ucwords(str_replace('_', ' ', $log['reviewer_role'])) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($log['status'] === 'confirmed'): ?>
                                                <span class="badge bg-success p-2"><i class="bi bi-check-circle-fill me-1"></i>Confirmed</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger p-2"><i class="bi bi-dash-circle-fill me-1"></i>On Hold</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-muted small">
                                            <?= !empty($log['hold_reason']) ? nl2br(htmlspecialchars($log['hold_reason'])) : '<span class="text-muted italic">N/A</span>' ?>
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
</div>

<!-- Not Ready / Hold Reason Modal -->
<div class="modal fade" id="holdReasonModal" tabindex="-1" aria-labelledby="holdReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: #1e4072;">
                <h5 class="modal-title font-weight-bold" id="holdReasonModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Hold Placement Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="holdReasonTextarea" class="form-label font-weight-bold text-muted small text-uppercase">Why is this student not yet ready for regular class placement? <span class="text-danger">*</span></label>
                    <textarea class="form-control border-2" id="holdReasonTextarea" rows="4" style="border-radius: 8px;" placeholder="Specify detailed indicators, deficiencies, or required learning support packages..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn text-white" id="submitHoldBtn" style="background-color: #1e4072; font-weight: 600;">
                    Submit Hold Decision
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var confirmBtn = document.getElementById("confirmPlacementBtn");
    var holdBtn = document.getElementById("holdPlacementBtn");
    var statusInput = document.getElementById("statusInput");
    var holdReasonInput = document.getElementById("holdReasonInput");
    var placementForm = document.getElementById("placementForm");
    
    // Modal elements
    var holdModal = new bootstrap.Modal(document.getElementById('holdReasonModal'));
    var holdReasonTextarea = document.getElementById("holdReasonTextarea");
    var submitHoldBtn = document.getElementById("submitHoldBtn");

    if (confirmBtn) {
        confirmBtn.addEventListener("click", function () {
            var confirmed = confirm("Confirm regular class placement for <?= htmlspecialchars($iep['student_name']) ?>? This will archive their student record from active SPED tracking and send parent email notification.");
            if (confirmed) {
                statusInput.value = "confirmed";
                holdReasonInput.value = "";
                placementForm.submit();
            }
        });
    }

    if (holdBtn) {
        holdBtn.addEventListener("click", function () {
            holdReasonTextarea.value = "";
            holdModal.show();
        });
    }

    if (submitHoldBtn) {
        submitHoldBtn.addEventListener("click", function () {
            var reason = holdReasonTextarea.value.trim();
            if (reason === "") {
                alert("Please provide a hold reason description.");
                return;
            }
            statusInput.value = "on_hold";
            holdReasonInput.value = reason;
            holdModal.hide();
            placementForm.submit();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
