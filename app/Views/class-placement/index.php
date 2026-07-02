<?php
// Class Placement Notice view for Process 13
$pageTitle = 'Class Placement Notice — SignED';
require_once __DIR__ . '/../layouts/header.php';
$role = $_SESSION['role'];
$basePath = BASE_PATH;
$hasAssignment = !empty($workflow['assignment']);
$isAssignedTeacher = ($hasAssignment && (int)$workflow['assignment']['general_teacher_id'] === (int)$_SESSION['user_id']);
require_once __DIR__ . '/../../Models/StudentModel.php';
$classPlacementRec = (new StudentModel())->findById((int)($iep['student_id'] ?? 0));
$classPlacementStudentCode = $classPlacementRec['student_id'] ?? null;
$isMainstreamed = (($classPlacementRec['status'] ?? '') === 'mainstreamed');
$canReview = (!$isMainstreamed && (($role === 'general_teacher' && $isAssignedTeacher) || $role === 'admin'));
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

        <?php if ($readiness && ($readiness['status'] ?? '') === 'finalized' && ($readiness['readiness_result'] ?? '') !== 'Ready for Inclusion'): ?>
            <div class="alert alert-warning py-3 mb-4 d-flex align-items-start gap-3 shadow-sm border-0" style="border-left: 5px solid #ffc107; background-color: #fffbeb; border-radius: 12px;">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-3 mt-1 flex-shrink-0"></i>
                <div>
                    <h5 class="alert-heading text-warning-emphasis mb-1 font-weight-bold">Learner Re-Evaluation / Support Needed</h5>
                    <p class="mb-0 small text-muted">
                        Based on the finalized transition readiness evaluation, this learner is evaluated as <strong><?= htmlspecialchars($readiness['readiness_result']) ?></strong>.
                        They are <strong>not yet ready</strong> to transition. Please proceed with caution and ensure additional learning and support accommodations are carefully documented.
                    </p>
                </div>
            </div>
        <?php endif; ?>

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
                                <?= !empty(trim($readiness['teacher_recommendation'] ?? '')) ? htmlspecialchars($readiness['teacher_recommendation']) : 'No recommendation notes.' ?>
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
                                <?= !empty(trim($itpRecommendationsEnd ?? '')) ? nl2br(htmlspecialchars($itpRecommendationsEnd)) : 'No recommendations set.' ?>
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

        <!-- Confirmed Placement Details -->
        <?php if ($isMainstreamed): ?>
            <?php 
            $confirmedLog = null;
            if (!empty($placementHistory)) {
                foreach ($placementHistory as $historyLog) {
                    if ($historyLog['status'] === 'confirmed') {
                        $confirmedLog = $historyLog;
                        break;
                    }
                }
            }
            ?>
            <?php if ($confirmedLog): ?>
                <div class="card shadow-sm border-0 mb-5 text-white" style="border-radius: 12px; background-color: #3b6d11;">
                    <div class="card-body p-4 text-center animate__animated animate__fadeIn">
                        <div class="mb-3">
                            <i class="bi bi-patch-check-fill" style="font-size: 3.5rem;"></i>
                        </div>
                        <h4 class="font-weight-bold mb-2">Regular Class Placement Confirmed</h4>
                        <p class="mb-3 opacity-90 small">This student has been recommended for regular class placement and successfully mainstreamed.</p>
                        
                        <div class="mx-auto col-md-8 rounded p-3 mb-0 text-start" style="background-color: rgba(255,255,255,0.15);">
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <strong>Decision Maker:</strong> <?= htmlspecialchars($confirmedLog['reviewer_name'] ?? 'Assigned Teacher') ?> (<?= htmlspecialchars(ucwords(str_replace('_', ' ', $confirmedLog['reviewer_role'] ?? 'general_teacher'))) ?>)
                                </div>
                                <div class="col-sm-6">
                                    <strong>Confirmed At:</strong> <?= !empty($confirmedLog['confirmed_at']) ? date('F d, Y h:i A', strtotime($confirmedLog['confirmed_at'])) : date('F d, Y h:i A', strtotime($confirmedLog['created_at'])) ?>
                                </div>
                                <?php if (!empty(trim($confirmedLog['hold_reason'] ?? ''))): ?>
                                    <div class="col-12 mt-2 pt-2 border-top border-white-50">
                                        <strong>Remarks / Comments:</strong>
                                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($confirmedLog['hold_reason'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Placement Decisions Trigger -->
        <?php if ($canReview): ?>
            <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; background-color: #f8f9fa; border: 1px solid #e2e8f0;">
                <div class="card-body p-4 text-center">
                    <h5 class="font-weight-bold text-dark mb-3" style="color: #1e4072;"><i class="bi bi-check2-square me-2"></i>Determine Placement Decision</h5>
                    <p class="text-muted small mb-4">Please carefully review the transition summary data before confirming regular class placement.</p>
                    
                    <form id="placementForm" method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/placement-notice">
                        <input type="hidden" name="status" value="confirmed">

                        <div class="mb-4 text-start col-md-8 mx-auto">
                            <label for="placement_remarks" class="form-label font-weight-bold text-muted small text-uppercase">Remarks / Comments (will be included in parent notification)</label>
                            <textarea name="remarks" id="placement_remarks" class="form-control border-2" rows="3" style="border-radius: 8px;" placeholder="Add remarks or comments for the parent..."></textarea>
                        </div>

                        <div class="d-flex justify-content-center">
                            <!-- Confirm Placement Button -->
                            <button type="button" class="btn btn-lg text-white px-5 shadow-xs" id="confirmPlacementBtn" style="background-color: #a01422; font-weight: 600; border-radius: 8px;">
                                <i class="bi bi-check-lg me-2"></i>Notify and Confirm Placement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var confirmBtn = document.getElementById("confirmPlacementBtn");
    var placementForm = document.getElementById("placementForm");
    
    if (confirmBtn) {
        confirmBtn.addEventListener("click", function () {
            var confirmed = confirm("Confirm regular class placement for <?= htmlspecialchars($iep['student_name']) ?>? This will archive their student record from active SPED tracking and send parent in-system and email notifications.");
            if (confirmed) {
                placementForm.submit();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
