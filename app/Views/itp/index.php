<?php
// Individual Transition Plan view for Process 11
// Feature 1: Personal Information & Point of Entry

$pageTitle = 'Individual Transition Plan - SignED';
require_once __DIR__ . '/../layouts/header.php';
$role = $_SESSION['role'];
$basePath = BASE_PATH;
$isFinalized = ($itp && $itp['status'] === 'finalized');
$isReadinessNotReady = ($readiness && ($readiness['status'] ?? '') === 'finalized' && ($readiness['readiness_result'] ?? '') !== 'Ready for Inclusion');
$isReadOnly = ($isFinalized || $isReadinessNotReady);
$activeStep = 11;
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
                    <i class="bi bi-file-earmark-person me-2"></i>Individual Transition Plan (ITP)
                </h1>
                <p class="text-muted mb-0">Part 5 — Collaborative & Digital Transition Planning</p>
            </div>
            <div>
                <a href="<?= $basePath ?>/iep" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to IEPs
                </a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Wizard Navigation -->
        <?php require_once __DIR__ . '/../layouts/transition_nav.php'; ?>

        <?php if ($readiness && ($readiness['status'] ?? '') === 'finalized' && ($readiness['readiness_result'] ?? '') !== 'Ready for Inclusion'): ?>
            <div class="alert alert-warning py-3 mb-4 d-flex align-items-start gap-3 shadow-xs" style="border-left: 5px solid #ffc107; background-color: #fffbeb;">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-3 mt-1 flex-shrink-0"></i>
                <div>
                    <h5 class="alert-heading text-warning-emphasis mb-1 font-weight-bold">Learner Re-Evaluation / Support Needed</h5>
                    <p class="mb-0 small text-muted">
                        Based on the finalized transition readiness evaluation, this learner is evaluated as <strong><?= htmlspecialchars($readiness['readiness_result']) ?></strong>.
                        They are <strong>not yet ready</strong> to transition. Please proceed with caution and ensure additional learning and support accommodations are carefully documented in this plan.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isFinalized): ?>
            <div class="alert alert-info py-3 mb-4" style="border-left: 5px solid #1e4072; background-color: #eef4fc;">
                <h5 class="alert-heading text-primary-emphasis mb-1"><i class="bi bi-lock-fill me-2"></i>Finalized and Locked</h5>
                <p class="mb-0 small text-muted">This Individual Transition Plan has been finalized on <?= htmlspecialchars($itp['finalized_at'] ?? 'N/A') ?> and is now read-only.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/individual-transition-plan">
            <?php
            require_once __DIR__ . '/../../Models/StudentModel.php';
            $itpStudentRec = (new StudentModel())->findById((int)($iep['student_id'] ?? 0));
            $itpStudentCode = $itpStudentRec['student_id'] ?? null;
            ?>
            <!-- Part I: Personal Information -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-person-lines-fill me-2"></i>I. Personal Information</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label for="student_name" class="form-label small font-weight-bold text-muted text-uppercase">Learner's Name</label>
                            <input type="text" id="student_name" name="student_name" class="form-control form-control-lg border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['student_name'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : 'required' ?>>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="date_of_birth" class="form-label small font-weight-bold text-muted text-uppercase">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control form-control-lg border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['date_of_birth'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : 'required' ?>>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="student_id_display" class="form-label small font-weight-bold text-muted text-uppercase">Student ID</label>
                            <input type="text" id="student_id_display" class="form-control form-control-lg border-2 bg-light" style="border-radius: 8px;"
                                   value="<?= htmlspecialchars(StudentDisplayHelper::formatStudentId($itpStudentCode)) ?>" readonly>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="lrn" class="form-label small font-weight-bold text-muted text-uppercase">DepEd LRN (optional — assigned by DepEd LIS)</label>
                            <input type="text" id="lrn" name="lrn" class="form-control form-control-lg border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars(StudentDisplayHelper::lrnFieldValue($personalInfo['lrn'] ?? null)) ?>" <?= $isReadOnly ? 'readonly' : '' ?> maxlength="12">
                        </div>

                        <div class="col-md-6">
                            <label for="father_name" class="form-label small font-weight-bold text-muted text-uppercase">Father's Name / Guardian</label>
                            <input type="text" id="father_name" name="father_name" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['father_name'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label for="mother_name" class="form-label small font-weight-bold text-muted text-uppercase">Mother's Name / Guardian</label>
                            <input type="text" id="mother_name" name="mother_name" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['mother_name'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label for="level_of_education" class="form-label small font-weight-bold text-muted text-uppercase">Current Level of Education</label>
                            <input type="text" id="level_of_education" name="level_of_education" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['level_of_education'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="previous_school" class="form-label small font-weight-bold text-muted text-uppercase">Previous School</label>
                            <input type="text" id="previous_school" name="previous_school" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['previous_school'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="religion" class="form-label small font-weight-bold text-muted text-uppercase">Religion</label>
                            <input type="text" id="religion" name="religion" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['religion'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label for="years_in_school" class="form-label small font-weight-bold text-muted text-uppercase">No. of Year/s in School</label>
                            <input type="number" id="years_in_school" name="years_in_school" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['years_in_school'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?> min="0">
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="gender" class="form-label small font-weight-bold text-muted text-uppercase">Gender / Sex</label>
                            <input type="text" id="gender" name="gender" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['gender'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="contact_no" class="form-label small font-weight-bold text-muted text-uppercase">Phone / Contact No.</label>
                            <input type="text" id="contact_no" name="contact_no" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['contact_no'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label small font-weight-bold text-muted text-uppercase">Current Address</label>
                            <textarea id="address" name="address" class="form-control border-2" style="border-radius: 8px;" rows="2" <?= $isReadOnly ? 'readonly' : '' ?>><?= htmlspecialchars($personalInfo['address'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small font-weight-bold text-muted text-uppercase d-block">Exceptionality Assessment</label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exceptionality_type" id="exceptionality_with" value="With Assessment"
                                           <?= ($personalInfo['exceptionality_type'] ?? '') === 'With Assessment' ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="exceptionality_with">With Assessment</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exceptionality_type" id="exceptionality_without" value="Without Assessment"
                                           <?= ($personalInfo['exceptionality_type'] ?? '') === 'Without Assessment' ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="exceptionality_without">Without Assessment</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="exceptionality_assessment" class="form-label small font-weight-bold text-muted text-uppercase">Assessment Detail / Name of Institution</label>
                            <input type="text" id="exceptionality_assessment" name="exceptionality_assessment" class="form-control border-2" style="border-radius: 8px;" 
                                   value="<?= htmlspecialchars($personalInfo['exceptionality_assessment'] ?? '') ?>" <?= $isReadOnly ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Point of Entry -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-box-arrow-in-right me-2"></i>Point of Entry</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <p class="text-muted small mb-3">Please check the point of entry appropriate to the status of the learner (pre-selected based on Part 4 evaluation result).</p>
                    
                    <?php
                    $points = [
                        'Transition from home to school',
                        'Transition from school to functional life',
                        'Transition from SPED Center/SPED Classes to Inclusion Classes',
                        'Transition from one grade level to the next grade',
                        'Transition from school to employment or entrepreneurship',
                        'Transition level from one class to another in the same grade level'
                    ];
                    ?>
                    
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($points as $idx => $point): ?>
                            <div class="p-3 border rounded-3 d-flex align-items-center transition-all hover-shadow-sm" 
                                 style="border-color: #e2e8f0; background-color: <?= ($suggestedPointOfEntry === $point) ? '#f0f9ff' : '#ffffff' ?>; border-left: 4px solid <?= ($suggestedPointOfEntry === $point) ? '#a01422' : '#e2e8f0' ?>;">
                                <div class="form-check mb-0 w-100">
                                    <input class="form-check-input" type="radio" name="point_of_entry" id="point_entry_<?= $idx ?>" value="<?= htmlspecialchars($point) ?>"
                                           <?= ($suggestedPointOfEntry === $point) ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
                                    <label class="form-check-label font-weight-bold text-dark-emphasis ms-2 cursor-pointer w-100 d-block" for="point_entry_<?= $idx ?>">
                                        <?= htmlspecialchars($point) ?>
                                        <?php if (!$itp && $point === $this->suggestPointOfEntry($readiness['readiness_result'] ?? '')): ?>
                                            <span class="badge ms-2" style="background-color: #a01422; color: #fff;">Suggested</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <?php if (!$isReadOnly): ?>
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-4 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Saving creates or updates the ITP in draft state (`in_progress`).</span>
                        </div>
                        <button type="submit" class="btn btn-lg text-white px-5" style="background-color: #a01422; border-radius: 8px; font-weight: 600;">
                            <i class="bi bi-save me-2"></i>Save Part I & Entry Point
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </form>

        <!-- Part II: Transition Team -->
        <?php if (!$itp): ?>
            <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-people-fill me-2"></i>II. Transition Team</h5>
                </div>
                <div class="card-body p-4 bg-white text-center py-5">
                    <i class="bi bi-lock-fill text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Transition Team Section Locked</h5>
                    <p class="text-muted mb-0">Please save Part I (Personal Information & Point of Entry) first to initialize the collaborative Transition Team.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-people-fill me-2"></i>II. Transition Team</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <p class="text-muted small mb-4">
                        The Transition Team is a collaborative effort. SPED Teachers assign user accounts to team roles. 
                        Assigned team members will receive a notification and email to fill in their own contact information and start date.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead style="background-color: #1e4072; color: white;">
                                <tr>
                                    <th style="width: 20%;">Role</th>
                                    <th style="width: 25%;">Assigned Account</th>
                                    <th style="width: 20%;">Contact / Details</th>
                                    <th style="width: 15%;">Date Started</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teamMembers as $member): 
                                    $roleLabel = ucwords(str_replace('_', ' ', $member['role']));
                                    $isAutoFilledRole = in_array($member['role'], ['parent_guardian', 'learner', 'sped_teacher'], true);
                                    $isFilled = ($member['status'] === 'filled' || ($isAutoFilledRole && $member['assigned_user_id'] !== null));

                                    $statusClass = 'bg-secondary';
                                    if ($isFilled) {
                                        if ($member['name'] === 'Not Applicable') {
                                            $statusClass = 'bg-light text-muted border';
                                        } else {
                                            $statusClass = 'bg-success text-white';
                                        }
                                    } elseif ($member['assigned_user_id'] !== null) {
                                        $statusClass = 'bg-warning text-dark';
                                    }
                                    $isOwnRow = ((int)$member['assigned_user_id'] === intval($_SESSION['user_id']));
                                ?>
                                    <tr>
                                        <td class="font-weight-bold" style="color: #1e4072;"><?= htmlspecialchars($roleLabel) ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($member['name'] ?? 'Not Assigned') ?></span>
                                                <?php if ($member['user_name'] && $member['name'] !== $member['user_name'] && $member['name'] !== 'Not Applicable'): ?>
                                                    <span class="text-muted small">(Account: <?= htmlspecialchars($member['user_name']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($member['contact_details'] ?? 'N/A') ?></td>
                                        <td><?= $member['date_started'] ? htmlspecialchars(date('M d, Y', strtotime($member['date_started']))) : 'N/A' ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= $statusClass ?> px-3 py-2 small">
                                                <?php 
                                                if ($isFilled) {
                                                    if ($member['name'] === 'Not Applicable') {
                                                        echo 'Not Applicable';
                                                    } else {
                                                        echo 'Filled';
                                                    }
                                                } elseif ($member['assigned_user_id'] !== null) {
                                                    echo 'Pending Fill';
                                                } else {
                                                    echo 'Not Assigned';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <!-- SPED Teacher/Admin Assignment Action -->
                                                <?php if (!$isReadOnly && in_array($role, ['sped_teacher', 'master_teacher', 'admin'], true)): ?>
                                                    <?php if ($member['role'] !== 'sped_teacher'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal_<?= $member['id'] ?>" id="btn_assign_trigger_<?= $member['id'] ?>">
                                                            <i class="bi bi-person-plus"></i> Assign
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <!-- Edit own row action -->
                                                <?php if (!$isReadOnly && $isOwnRow && $member['name'] !== 'Not Applicable'): ?>
                                                    <button type="button" class="btn btn-sm btn-danger text-white" style="background-color: #a01422;" data-bs-toggle="modal" data-bs-target="#editOwnDetailsModal_<?= $member['id'] ?>" id="btn_edit_own_<?= $member['id'] ?>">
                                                        <i class="bi bi-pencil-square"></i> Fill My Details
                                                    </button>
                                                <?php endif; ?>

                                                <!-- Remind action for SPED Teacher/Admin -->
                                                <?php if (!$isReadOnly && in_array($role, ['sped_teacher', 'master_teacher', 'admin'], true) && !$isFilled && $member['assigned_user_id'] !== null): ?>
                                                    <form method="post" action="<?= $basePath ?>/itp-team/remind/<?= $member['id'] ?>" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" id="btn_remind_<?= $member['id'] ?>">
                                                            <i class="bi bi-bell"></i> Remind
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Assignment Modal -->
                                    <?php if (!$isReadOnly && in_array($role, ['sped_teacher', 'master_teacher', 'admin'], true) && $member['role'] !== 'sped_teacher'): ?>
                                        <div class="modal fade" id="assignModal_<?= $member['id'] ?>" tabindex="-1" aria-labelledby="assignModalLabel_<?= $member['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                                                    <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/itp/assign">
                                                        <input type="hidden" name="role" value="<?= htmlspecialchars($member['role']) ?>">
                                                        <div class="modal-header text-white" style="background-color: #1e4072;">
                                                            <h5 class="modal-title font-weight-bold" id="assignModalLabel_<?= $member['id'] ?>">Assign <?= htmlspecialchars($roleLabel) ?></h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                              <?php
                                                              // Query parent user ID and details
                                                              $db = Database::getInstance()->getConnection();
                                                              $stmtParent = $db->prepare("
                                                                  SELECT es.parent_id, u.name AS parent_name, u.email AS parent_email
                                                                  FROM student_records sr
                                                                  JOIN enrollment_submissions es ON es.id = sr.enrollment_id
                                                                  JOIN users u ON u.id = es.parent_id
                                                                  WHERE sr.id = :student_id LIMIT 1
                                                              ");
                                                              $stmtParent->execute(['student_id' => (int)($iep['student_id'] ?? 0)]);
                                                              $studentParent = $stmtParent->fetch(PDO::FETCH_ASSOC);

                                                              // Query learner user ID and details
                                                              $stmtLearner = $db->prepare("
                                                                  SELECT u.id AS learner_user_id, u.name AS learner_name, u.email AS learner_email
                                                                  FROM student_records sr
                                                                  JOIN users u ON u.email = CONCAT('learner_', sr.student_id, '@spedlms.local')
                                                                  WHERE sr.id = :student_id LIMIT 1
                                                              ");
                                                              $stmtLearner->execute(['student_id' => (int)($iep['student_id'] ?? 0)]);
                                                              $studentLearner = $stmtLearner->fetch(PDO::FETCH_ASSOC);
                                                              ?>

                                                              <?php if ($member['role'] === 'parent_guardian'): ?>
                                                                  <div class="mb-3">
                                                                      <label class="form-label font-weight-bold text-muted small text-uppercase d-block">Parent/Guardian Account (Auto-selected)</label>
                                                                      <?php if ($studentParent): ?>
                                                                          <div class="p-3 border rounded bg-light" style="border-left: 4px solid #1e4072;">
                                                                              <strong class="text-dark d-block"><?= htmlspecialchars($studentParent['parent_name']) ?></strong>
                                                                              <span class="text-muted small"><?= htmlspecialchars($studentParent['parent_email']) ?></span>
                                                                          </div>
                                                                          <input type="hidden" name="assigned_user_id" value="<?= intval($studentParent['parent_id']) ?>">
                                                                      <?php else: ?>
                                                                          <div class="alert alert-danger py-2 small mb-0">
                                                                              No parent linked to this student record.
                                                                          </div>
                                                                      <?php endif; ?>
                                                                  </div>
                                                              <?php elseif ($member['role'] === 'learner'): ?>
                                                                  <div class="mb-3">
                                                                      <label class="form-label font-weight-bold text-muted small text-uppercase d-block">Learner Account (Auto-selected)</label>
                                                                      <?php if ($studentLearner): ?>
                                                                          <div class="p-3 border rounded bg-light" style="border-left: 4px solid #1e4072;">
                                                                              <strong class="text-dark d-block"><?= htmlspecialchars($studentLearner['learner_name']) ?></strong>
                                                                              <span class="text-muted small"><?= htmlspecialchars($studentLearner['learner_email']) ?></span>
                                                                          </div>
                                                                          <input type="hidden" name="assigned_user_id" value="<?= intval($studentLearner['learner_user_id']) ?>">
                                                                      <?php else: ?>
                                                                          <div class="alert alert-danger py-2 small mb-0">
                                                                              No learner account found for this student.
                                                                          </div>
                                                                      <?php endif; ?>
                                                                  </div>
                                                              <?php else: ?>
                                                                  <div class="mb-3">
                                                                      <label for="assign_user_select_<?= $member['id'] ?>" class="form-label font-weight-bold text-muted small">Select User Account</label>
                                                                      <select name="assigned_user_id" id="assign_user_select_<?= $member['id'] ?>" class="form-select border-2" style="border-radius: 8px;">
                                                                          <option value="">-- Choose Account --</option>
                                                                          <?php 
                                                                          $userList = [];
                                                                          if ($member['role'] === 'school_head') $userList = $schoolHeads;
                                                                          elseif ($member['role'] === 'guidance_teacher') $userList = $guidanceTeachers;
                                                                          elseif ($member['role'] === 'parent_guardian') $userList = $parents;
                                                                          elseif ($member['role'] === 'learner') $userList = $learners;
                                                                          elseif ($member['role'] === 'itp_coordinator') $userList = $coordinators;
                                                                          else $userList = $linkagesUsers;

                                                                          foreach ($userList as $u):
                                                                          ?>
                                                                              <option value="<?= $u['id'] ?>" <?= (intval($member['assigned_user_id']) === intval($u['id'])) ? 'selected' : '' ?>>
                                                                                  <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['role']) ?>)
                                                                                </option>
                                                                          <?php endforeach; ?>
                                                                      </select>
                                                                  </div>
                                                              <?php endif; ?>    

                                                            <?php if (in_array($member['role'], ['guidance_teacher', 'learner', 'linkages'], true)): ?>
                                                                <div class="form-check p-3 border rounded-3 bg-light" style="border-color: #cbd5e1;">
                                                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="not_applicable" value="1" id="not_applicable_<?= $member['id'] ?>"
                                                                           <?= ($member['name'] === 'Not Applicable') ? 'checked' : '' ?>>
                                                                    <label class="form-check-label font-weight-bold text-dark" for="not_applicable_<?= $member['id'] ?>">
                                                                        Mark this role as "Not Applicable" for this learner
                                                                    </label>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer bg-light p-3">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn text-white px-4" style="background-color: #a01422; font-weight: 600;">Save Assignment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                const checkbox = document.getElementById('not_applicable_<?= $member['id'] ?>');
                                                const select = document.getElementById('assign_user_select_<?= $member['id'] ?>');
                                                if (checkbox && select) {
                                                    const toggleState = () => {
                                                        if (checkbox.checked) {
                                                            select.value = '';
                                                            select.disabled = true;
                                                        } else {
                                                            select.disabled = false;
                                                        }
                                                    };
                                                    checkbox.addEventListener('change', toggleState);
                                                    toggleState();
                                                }
                                            });
                                        </script>
                                    <?php endif; ?>

                                    <!-- Edit Own Details Modal -->
                                    <?php if (!$isReadOnly && $isOwnRow && $member['name'] !== 'Not Applicable'): ?>
                                        <div class="modal fade" id="editOwnDetailsModal_<?= $member['id'] ?>" tabindex="-1" aria-labelledby="editOwnDetailsModalLabel_<?= $member['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                                                    <form method="post" action="<?= $basePath ?>/itp-team/save/<?= intval($member['id']) ?>">
                                                        <div class="modal-header text-white" style="background-color: #1e4072;">
                                                            <h5 class="modal-title font-weight-bold" id="editOwnDetailsModalLabel_<?= $member['id'] ?>">Update My Details</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4 text-start">
                                                            <div class="mb-4 p-3 border rounded bg-light text-wrap" style="border-color: #cbd5e1; border-left: 4px solid #a01422; font-size: 0.9rem;">
                                                                <span class="font-weight-bold text-dark d-block">Role: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $member['role']))) ?></span>
                                                                <span class="text-muted small">
                                                                    <strong>Student:</strong> <?= htmlspecialchars($iep['student_name'] ?? 'N/A') ?><br>
                                                                    <strong>DepEd LRN:</strong> <?= htmlspecialchars($iep['lrn'] ?? '') ?>
                                                                </span>
                                                            </div>

                                                            <?php
                                                            // Prefill name/email if empty
                                                            $prefillName = $member['name'] ?: ($member['user_name'] ?? '');
                                                            if ($prefillName === 'Not Assigned') {
                                                                $prefillName = '';
                                                            }
                                                            $prefillContact = $member['contact_details'] ?: ($member['user_email'] ?? '');
                                                            ?>

                                                            <div class="mb-3">
                                                                <label for="modal_name_<?= $member['id'] ?>" class="form-label small font-weight-bold text-muted text-uppercase">Full Name / Representative</label>
                                                                <input type="text" id="modal_name_<?= $member['id'] ?>" name="name" class="form-control border-2" style="border-radius: 8px;" 
                                                                       value="<?= htmlspecialchars($prefillName) ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="modal_contact_<?= $member['id'] ?>" class="form-label small font-weight-bold text-muted text-uppercase">Contact Details (Email / Phone)</label>
                                                                <input type="text" id="modal_contact_<?= $member['id'] ?>" name="contact_details" class="form-control border-2" style="border-radius: 8px;" 
                                                                       value="<?= htmlspecialchars($prefillContact) ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="modal_date_started_<?= $member['id'] ?>" class="form-label small font-weight-bold text-muted text-uppercase">Date Started Working with Learner</label>
                                                                <input type="date" id="modal_date_started_<?= $member['id'] ?>" name="date_started" class="form-control border-2" style="border-radius: 8px;" 
                                                                       value="<?= htmlspecialchars($member['date_started'] ?? '') ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light p-3 gap-2">
                                                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn text-white px-5" style="background-color: #a01422; font-weight: 600; border-radius: 8px;">
                                                                <i class="bi bi-save me-2"></i>Save Details
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Part III: Narrative Assessment & Recommendations -->
        <?php if ($itp): ?>
            <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/itp/narrative">
                <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-journal-text me-2"></i>III. Narrative Assessment</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <p class="text-muted small mb-4">
                            Describe the learner's characteristics. Click "Add Item" to add multiple items dynamically.
                        </p>

                        <?php
                        $narrativeSections = [
                            'strengths' => ['label' => 'Strengths', 'icon' => 'bi-hand-thumbs-up'],
                            'interests' => ['label' => 'Interests', 'icon' => 'bi-heart'],
                            'talents' => ['label' => 'Talents', 'icon' => 'bi-star'],
                            'skills' => ['label' => 'Skills', 'icon' => 'bi-tools'],
                            'needs' => ['label' => 'Needs', 'icon' => 'bi-exclamation-triangle']
                        ];
                        $canEditNarrative = (!$isReadOnly && in_array($role, ['sped_teacher', 'master_teacher', 'admin'], true));
                        ?>

                        <div class="row g-4">
                            <?php foreach ($narrativeSections as $secId => $secInfo): 
                                $currentItems = $narrativeItems[$secId] ?? [];
                            ?>
                                <div class="<?= ($secId === 'needs') ? 'col-12' : 'col-md-6' ?>">
                                    <div class="card border-0 h-100 shadow-sm" style="border-radius: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0 !important;">
                                        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-0">
                                            <span class="font-weight-bold" style="color: #1e4072; font-size: 1.1rem;">
                                                <i class="bi <?= $secInfo['icon'] ?> me-2"></i><?= htmlspecialchars($secInfo['label']) ?>
                                            </span>
                                            <?php if ($canEditNarrative): ?>
                                                <button type="button" class="btn btn-sm text-white" style="background-color: #a01422;" onclick="addNarrativeItem('<?= $secId ?>')" id="btn_add_<?= $secId ?>">
                                                    <i class="bi bi-plus-circle me-1"></i>Add Item
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body p-3">
                                            <div id="<?= $secId ?>_container">
                                                <?php if ($canEditNarrative): ?>
                                                    <?php if (empty($currentItems)): ?>
                                                        <div class="d-flex gap-2 mb-2 align-items-center">
                                                            <input type="text" name="<?= $secId ?>[]" id="narrative_<?= $secId ?>_0" class="form-control border-2" style="border-radius: 8px;" placeholder="Enter item..." required>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()" id="btn_remove_<?= $secId ?>_0">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($currentItems as $idx => $item): ?>
                                                            <div class="d-flex gap-2 mb-2 align-items-center">
                                                                <input type="text" name="<?= $secId ?>[]" id="narrative_<?= $secId ?>_<?= $idx ?>" class="form-control border-2" style="border-radius: 8px;" value="<?= htmlspecialchars($item) ?>" required>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()" id="btn_remove_<?= $secId ?>_<?= $idx ?>">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if (empty($currentItems)): ?>
                                                        <span class="text-muted small italic">No items recorded.</span>
                                                    <?php else: ?>
                                                        <ul class="list-group list-group-flush" style="border-radius: 8px;">
                                                            <?php foreach ($currentItems as $item): ?>
                                                                <li class="list-group-item bg-transparent py-2 border-0 ps-0">
                                                                    <i class="bi bi-chevron-right text-danger me-2"></i><?= htmlspecialchars($item) ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Part IV: Recommendations (Beginning of SY) -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-chat-left-quote me-2"></i>IV. Recommendations (Beginning of School Year)</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="mb-3">
                            <label for="recommendation_beginning" class="form-label small font-weight-bold text-muted text-uppercase">Recommendation Details</label>
                            <textarea id="recommendation_beginning" name="recommendation_beginning" class="form-control border-2" style="border-radius: 8px;" rows="4" 
                                      placeholder="Provide recommendations for the learner at the beginning of the school year..." 
                                      <?= $canEditNarrative ? '' : 'readonly' ?>><?= htmlspecialchars($recBeginning) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <?php if ($canEditNarrative): ?>
                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-body p-4 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Saving preserves narrative assessment details and recommendations.</span>
                            </div>
                            <button type="submit" class="btn btn-lg text-white px-5" style="background-color: #a01422; border-radius: 8px; font-weight: 600;" id="btn_save_narrative">
                                <i class="bi bi-save me-2"></i>Save Narrative & Recommendations
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </form>

            <script>
                function addNarrativeItem(sectionId) {
                    const container = document.getElementById(sectionId + '_container');
                    const index = container.children.length;
                    
                    const div = document.createElement('div');
                    div.className = 'd-flex gap-2 mb-2 align-items-center';
                    div.innerHTML = `
                        <input type="text" name="${sectionId}[]" id="narrative_${sectionId}_${index}" class="form-control border-2" style="border-radius: 8px;" placeholder="Enter item..." required>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()" id="btn_remove_${sectionId}_${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                    container.appendChild(div);
                }
            </script>

            <!-- Part V: Transition Program Matrix -->
            <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/itp/matrix">
                <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-grid-3x3-gap me-2"></i>V. Transition Program Matrix</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <p class="text-muted small mb-4">
                            The table below shows options for provision of specific transition programs to targeted levels of entry/exit points. 
                            (The row matching the selected Point of Entry is pre-checked as a hint if no selections have been saved yet.)
                        </p>

                        <?php
                        $matrixRows = [
                            'Transition from school to functional life',
                            'Transition from home to school',
                            'Transition from one class to another in the same grade level/program option',
                            'Transition from SPED center to inclusion classes',
                            'Transition from one grade level to the next grade level',
                            'Transition from school to employment and entrepreneurship'
                        ];
                        $matrixCols = [
                            'Functional Academics',
                            'Pre-Vocational Skills',
                            'Life Skills',
                            'Enrichment Skills',
                            'Livelihood Skills',
                            'Care Skills',
                            'Career Skills'
                        ];
                        $canEditMatrix = (!$isReadOnly && in_array($role, ['sped_teacher', 'master_teacher', 'admin'], true));
                        ?>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead style="background-color: #1e4072; color: white;">
                                    <tr>
                                        <th style="width: 30%;">Type of Learner based on Entry/Exit Point in the Transition Program</th>
                                        <?php foreach ($matrixCols as $colLabel): ?>
                                            <th class="text-center small text-wrap" style="width: 10%;"><?= htmlspecialchars($colLabel) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matrixRows as $rIdx => $rowLabel): ?>
                                        <tr>
                                            <td class="font-weight-bold small" style="color: #1e4072;"><?= htmlspecialchars($rowLabel) ?></td>
                                            <?php foreach ($matrixCols as $cIdx => $colLabel): 
                                                // Check logic (including hint checking)
                                                $isCellChecked = isset($programMatrix["$rIdx-$cIdx"]) && $programMatrix["$rIdx-$cIdx"];
                                                if (empty($programMatrix)) {
                                                    $hintRow = -1;
                                                    if ($suggestedPointOfEntry === 'Transition from school to functional life') $hintRow = 0;
                                                    elseif ($suggestedPointOfEntry === 'Transition from home to school') $hintRow = 1;
                                                    elseif ($suggestedPointOfEntry === 'Transition level from one class to another in the same grade level') $hintRow = 2;
                                                    elseif ($suggestedPointOfEntry === 'Transition from SPED Center/SPED Classes to Inclusion Classes') $hintRow = 3;
                                                    elseif ($suggestedPointOfEntry === 'Transition from one grade level to the next grade') $hintRow = 4;
                                                    elseif ($suggestedPointOfEntry === 'Transition from school to employment or entrepreneurship') $hintRow = 5;

                                                    if ($rIdx === $hintRow) {
                                                        $isCellChecked = true;
                                                    }
                                                }
                                            ?>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center p-0 m-0">
                                                        <input class="form-check-input border-2" type="checkbox" name="matrix[]" value="<?= $rIdx ?>-<?= $cIdx ?>" 
                                                               id="matrix_cell_<?= $rIdx ?>_<?= $cIdx ?>"
                                                               <?= $isCellChecked ? 'checked' : '' ?> 
                                                               <?= $canEditMatrix ? '' : 'disabled' ?>
                                                               style="width: 1.25rem; height: 1.25rem; border-color: #cbd5e1;">
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Part VI: Recommendations (End of School Year) -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-chat-right-quote-fill me-2"></i>VI. Recommendations (End of School Year)</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="mb-3">
                            <label for="recommendation_end" class="form-label small font-weight-bold text-muted text-uppercase">Recommendation Details</label>
                            <textarea id="recommendation_end" name="recommendation_end" class="form-control border-2" style="border-radius: 8px;" rows="4" 
                                      placeholder="Provide recommendations for the learner at the end of the school year..." 
                                      <?= $canEditMatrix ? '' : 'readonly' ?>><?= htmlspecialchars($recEnd) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <?php if ($canEditMatrix): ?>
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-body p-4 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Saving preserves the transition program matrix and recommendations.</span>
                            </div>
                            <button type="submit" class="btn btn-lg text-white px-5" style="background-color: #a01422; border-radius: 8px; font-weight: 600;" id="btn_save_matrix">
                                <i class="bi bi-save me-2"></i>Save Matrix & Recommendations
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </form>

            <!-- Part VII: Digital Signatures & Finalization -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-patch-check-fill me-2"></i>VII. Digital Signatures & Finalization</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="row g-4">
                        <!-- Full-width: Finalization -->
                        <div class="col-12">
                            <h6 class="font-weight-bold mb-3" style="color: #1e4072;">
                                <i class="bi bi-shield-lock me-2"></i>ITP Finalize & Lock
                            </h6>

                            <?php if ($isFinalized): ?>
                                <div class="p-3 border rounded-3 bg-light text-center py-4" style="border-left: 4px solid #1e4072 !important;">
                                    <i class="bi bi-lock-fill text-success" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2 font-weight-bold text-success">This ITP is Finalized</h6>
                                    <p class="text-muted small mb-0">Completed on: <?= date('F d, Y h:i A', strtotime($itp['finalized_at'])) ?></p>
                                </div>
                            <?php elseif ($isReadinessNotReady): ?>
                                <div class="p-3 border rounded-3 bg-light text-center py-4" style="border-left: 4px solid #ffc107 !important;">
                                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2.2rem;"></i>
                                    <h6 class="mt-2 font-weight-bold text-warning-emphasis">ITP Locked (Learner Not Ready)</h6>
                                    <p class="text-muted small mb-0">This ITP cannot be filled or finalized because the learner requires re-evaluation based on their SF9 report card.</p>
                                </div>
                            <?php elseif (in_array($role, ['sped_teacher', 'master_teacher', 'admin'], true)): ?>
                                <!-- Team member Warnings -->
                                <?php if (!empty($incompleteMembers)): ?>
                                    <div class="alert alert-warning py-2 mb-3 small">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Notice:</strong> The following transition team roles have not filled their information:
                                        <ul class="mb-0 mt-1">
                                            <?php foreach ($incompleteMembers as $m): ?>
                                                <li><?= htmlspecialchars(ucwords(str_replace('_', ' ', $m['role']))) ?> (<?= htmlspecialchars($m['name'] ?? 'Not Assigned') ?>)</li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <span class="text-muted mt-1 d-block font-italic">This is a non-blocking warning. You can still finalize if needed.</span>
                                    </div>
                                <?php endif; ?>

                                <div class="alert alert-info py-3 mb-3 small d-flex align-items-start gap-2">
                                    <i class="bi bi-info-circle-fill text-info fs-5 mt-1"></i>
                                    <div>
                                        <strong class="text-info">Ready to Finalize:</strong> All required assignments and settings have been checked.
                                        <p class="mb-0 text-muted mt-1">Finalizing this transition plan will lock all sections from future edits.</p>
                                    </div>
                                </div>

                                <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/itp/finalize" onsubmit="return confirm('Are you sure you want to finalize and lock this ITP? This cannot be undone.');">
                                    <button type="submit" class="btn btn-lg w-100 text-white" style="background-color: #a01422; font-weight:600;" id="btn_finalize_itp">
                                        <i class="bi bi-unlock-fill me-2"></i>Finalize and Lock ITP
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="p-3 border rounded-3 bg-light text-center py-4">
                                    <i class="bi bi-info-circle text-info" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2 text-muted">Awaiting Teacher Finalization</h6>
                                    <p class="text-muted small mb-0">Only assigned SPED teachers, Master teachers, or administrators can finalize this ITP.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.transition-all {
    transition: all 0.2s ease-in-out;
}
.hover-shadow-sm:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    border-color: #cbd5e1 !important;
}
.cursor-pointer {
    cursor: pointer;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
