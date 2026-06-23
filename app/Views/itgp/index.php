<?php
// Inclusive IEP + ITGP view for Process 12
$pageTitle = 'Inclusive IEP & ITGP — SignED';
require_once __DIR__ . '/../layouts/header.php';
$role = $_SESSION['role'];
$basePath = BASE_PATH;
$isFinalized = (!empty($itgp['status']) && $itgp['status'] === 'finalized');
$hasAssignment = !empty($assignment);
$isAssignedTeacher = ($hasAssignment && (int)$assignment['general_teacher_id'] === (int)$_SESSION['user_id']);
$canEdit = (!$isFinalized && ($role === 'general_teacher' && $isAssignedTeacher) || $role === 'admin');
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4" style="max-width: 1400px;">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="mb-1" style="color:#1e4072; font-weight:700;">
                    <i class="bi bi-journal-check me-2"></i>Inclusive IEP & ITGP
                </h1>
                <p class="text-muted mb-0">Process 12 — Individualized Transition Goal Plan</p>
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

        <?php if ($isFinalized): ?>
            <div class="alert alert-success py-3 mb-4 d-flex align-items-center shadow-sm" style="border-left: 5px solid #3b6d11; background-color: #f4faf0;">
                <i class="bi bi-lock-fill me-3 fs-3 text-success"></i>
                <div>
                    <h5 class="alert-heading text-success mb-1" style="font-weight: 600;">ITGP Finalized & Signed</h5>
                    <p class="mb-0 small text-muted">This ITGP transition plan has been finalized. Form fields are locked, but co-teaching comments remain active.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Reference Panels & Consultation Comments -->
            <div class="col-xl-5 col-lg-6">
                <!-- Collapsible References Accordion -->
                <div class="accordion shadow-sm border-0 mb-4" id="referenceAccordion" style="border-radius: 12px; overflow: hidden;">
                    <!-- IEP Reference Panel -->
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingIep">
                            <button class="accordion-button collapsed text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIep" aria-expanded="false" aria-controls="collapseIep" style="background-color: #1e4072;">
                                <i class="bi bi-file-earmark-medical me-2"></i>I. Original Signed IEP Goals
                            </button>
                        </h2>
                        <div id="collapseIep" class="accordion-collapse collapse" aria-labelledby="headingIep" data-bs-parent="#referenceAccordion">
                            <div class="accordion-body bg-light-subtle">
                                <?php if (empty($iepSteps)): ?>
                                    <p class="text-muted small mb-0">No IEP objectives found.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered small mb-0 bg-white">
                                            <thead>
                                                <tr class="table-secondary">
                                                    <th style="width: 50px;">No.</th>
                                                    <th>Domain</th>
                                                    <th>Target Objective / Steps</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($iepSteps as $step): ?>
                                                    <tr>
                                                        <td class="text-center font-weight-bold"><?= (int)$step['step_number'] ?></td>
                                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($step['pdsp_domain']) ?></span></td>
                                                        <td><?= htmlspecialchars($step['goal_text']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- PDSP Reference Panel -->
                    <div class="accordion-item border-0 border-top border-light-subtle">
                        <h2 class="accordion-header" id="headingPdsp">
                            <button class="accordion-button collapsed text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePdsp" aria-expanded="false" aria-controls="collapsePdsp" style="background-color: #1e4072;">
                                <i class="bi bi-person-badge-fill me-2"></i>II. Signed PDSP Domains & Recommendations
                            </button>
                        </h2>
                        <div id="collapsePdsp" class="accordion-collapse collapse" aria-labelledby="headingPdsp" data-bs-parent="#referenceAccordion">
                            <div class="accordion-body bg-light-subtle">
                                <?php if (empty($pdspDomains)): ?>
                                    <p class="text-muted small mb-0">No PDSP domains records found.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush gap-2">
                                        <?php foreach ($pdspDomains as $domain): ?>
                                            <div class="list-group-item p-3 border rounded shadow-xs bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <strong class="text-primary"><?= htmlspecialchars($domain['domain_name']) ?></strong>
                                                    <?php if (!empty($domain['sub_domain'])): ?>
                                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($domain['sub_domain']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="small text-muted mb-1"><strong>Skills:</strong> <?= htmlspecialchars($domain['skills_description'] ?? 'None') ?></p>
                                                <?php if (!empty($domain['educational_recommendation'])): ?>
                                                    <p class="small mb-0 text-success-emphasis bg-success-subtle p-2 rounded">
                                                        <i class="bi bi-lightbulb-fill me-1"></i><?= htmlspecialchars($domain['educational_recommendation']) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- ITP Reference Panel -->
                    <div class="accordion-item border-0 border-top border-light-subtle">
                        <h2 class="accordion-header" id="headingItp">
                            <button class="accordion-button text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseItp" aria-expanded="true" aria-controls="collapseItp" style="background-color: #1e4072;">
                                <i class="bi bi-arrow-left-right me-2"></i>III. Finalized Transition Plan (ITP)
                            </button>
                        </h2>
                        <div id="collapseItp" class="accordion-collapse collapse show" aria-labelledby="headingItp" data-bs-parent="#referenceAccordion">
                            <div class="accordion-body bg-light-subtle">
                                <div class="mb-3">
                                    <strong class="small text-muted text-uppercase d-block mb-1">Point of Entry:</strong>
                                    <span class="px-3 py-1 rounded bg-white border text-dark font-weight-bold d-inline-block shadow-xs">
                                        <?= htmlspecialchars($itp['point_of_entry'] ?? 'Not Specified') ?>
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <strong class="small text-muted text-uppercase d-block mb-1">Recommendations (Beginning of SY):</strong>
                                    <div class="p-2 border rounded bg-white small text-muted shadow-xs">
                                        <?= nl2br(htmlspecialchars($itpRecommendationsBeginning ?? 'No recommendations set.')) ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong class="small text-muted text-uppercase d-block mb-1">Recommendations (End of SY):</strong>
                                    <div class="p-2 border rounded bg-white small text-muted shadow-xs">
                                        <?= nl2br(htmlspecialchars($itpRecommendationsEnd ?? 'No recommendations set.')) ?>
                                    </div>
                                </div>

                                <div>
                                    <strong class="small text-muted text-uppercase d-block mb-2">Narratives:</strong>
                                    <div class="row g-2">
                                        <?php foreach (['strengths', 'interests', 'talents', 'skills', 'needs'] as $sec): ?>
                                            <div class="col-12">
                                                <div class="p-2 border rounded bg-white shadow-xs">
                                                    <strong class="small text-uppercase text-secondary d-block border-bottom pb-1 mb-1">
                                                        <?= ucfirst($sec) ?>
                                                    </strong>
                                                    <?php if (empty($itpSections[$sec])): ?>
                                                        <span class="text-muted small">None listed.</span>
                                                    <?php else: ?>
                                                        <ul class="mb-0 ps-3 small text-muted">
                                                            <?php foreach ($itpSections[$sec] as $item): ?>
                                                                <li><?= htmlspecialchars($item) ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments & Consultation Board -->
                <div class="card shadow-sm border-0" style="border-radius: 12px; height: 500px; display: flex; flex-direction: column; overflow: hidden;">
                    <div class="card-header text-white py-3" style="background: #1e4072;">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-chat-dots-fill me-2"></i>Co-Teaching Consultation & Comments</h5>
                    </div>
                    <!-- Chronological Message Bubbles Pane -->
                    <div class="card-body p-3 flex-grow-1 overflow-y-auto bg-light" id="commentsBox" style="display: flex; flex-direction: column; gap: 12px;">
                        <?php if (empty($comments)): ?>
                            <div class="text-center my-auto text-muted">
                                <i class="bi bi-chat-square-quote fs-2"></i>
                                <p class="small mt-2">No discussion messages yet.<br>General & SPED Teachers can collaborate here.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): 
                                $isMe = ((int)$comment['posted_by'] === (int)$_SESSION['user_id']);
                                $isGeneralTeacher = ($comment['author_role'] === 'general_teacher');
                                $bubbleBg = $isGeneralTeacher ? '#fceeee' : '#eef4fc';
                                $borderColor = $isGeneralTeacher ? '#f5c6cb' : '#bee5eb';
                                $textColor = $isGeneralTeacher ? '#a01422' : '#1e4072';
                                $align = $isMe ? 'align-self-end text-end' : 'align-self-start';
                                $bubbleAlign = $isMe ? 'align-self-end' : 'align-self-start';
                            ?>
                                <div class="d-flex flex-column <?= $bubbleAlign ?>" style="max-width: 85%;">
                                    <div class="d-flex align-items-center mb-1 px-1 gap-2 <?= $isMe ? 'flex-row-reverse' : '' ?>">
                                        <span class="small font-weight-bold" style="color: #495057;"><?= htmlspecialchars($comment['author_name']) ?></span>
                                        <span class="badge small" style="background-color: <?= $isGeneralTeacher ? '#a01422' : '#1e4072' ?>; color: white;">
                                            <?= $isGeneralTeacher ? 'General Ed' : 'SPED' ?>
                                        </span>
                                    </div>
                                    <div class="p-3 border rounded shadow-sm text-start" style="background-color: <?= $bubbleBg ?>; border-color: <?= $borderColor ?> !important; color: #212529; border-radius: 12px;">
                                        <span style="font-size: 0.95rem; line-height: 1.4;"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></span>
                                        <div class="text-muted small mt-2 text-end" style="font-size: 0.75rem;">
                                            <?= date('M d, Y h:i A', strtotime($comment['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Comment Input Box -->
                    <div class="card-footer bg-white border-top p-3">
                        <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/comment">
                            <div class="input-group">
                                <textarea name="comment_text" class="form-control" placeholder="Ask a question or advise on goals..." rows="1" style="border-radius: 8px 0 0 8px; resize: none;" required></textarea>
                                <button type="submit" class="btn text-white px-4" style="background: #1e4072; border-radius: 0 8px 8px 0;">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: ITGP Form & General Teacher Assignment -->
            <div class="col-xl-7 col-lg-6">
                <!-- General Teacher Assignment Section -->
                <?php if (!$hasAssignment): ?>
                    <div class="card shadow-sm border-0 mb-4 bg-warning-subtle" style="border-radius: 12px; border-left: 5px solid #ffc107 !important;">
                        <div class="card-body p-4">
                            <h5 class="font-weight-bold text-warning-emphasis mb-2"><i class="bi bi-person-plus-fill me-2"></i>General Education Teacher Assignment Pending</h5>
                            <p class="small text-muted">A General Education Teacher must be assigned to student records before editing or finalizing this goal plan.</p>
                            <?php if (in_array($role, ['sped_teacher', 'master_teacher', 'principal', 'admin'], true)): ?>
                                <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/assign" class="row g-2 align-items-center mt-2">
                                    <div class="col-sm-8">
                                        <select name="general_teacher_id" class="form-select border-2" required>
                                            <option value="">-- Choose General Ed Teacher --</option>
                                            <?php foreach ($generalTeachers as $gt): ?>
                                                <option value="<?= $gt['id'] ?>"><?= htmlspecialchars($gt['name']) ?> (<?= htmlspecialchars($gt['email']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <button type="submit" class="btn text-white w-100" style="background: #1e4072; font-weight: 600;">
                                            Assign Teacher
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-secondary p-2 mt-1">Contact the SPED Teacher to assign your account</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm border-0 mb-4 bg-light" style="border-radius: 12px;">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="bi bi-person-fill fs-4"></i>
                                </div>
                                <div>
                                    <span class="small text-muted d-block text-uppercase font-weight-bold">Assigned General Ed Teacher</span>
                                    <strong style="color: #1e4072;"><?= htmlspecialchars($assignment['general_teacher_name']) ?></strong>
                                    <span class="text-muted small ms-2">(<?= htmlspecialchars($assignment['general_teacher_email']) ?>)</span>
                                </div>
                            </div>
                            <?php if (in_array($role, ['sped_teacher', 'master_teacher', 'principal', 'admin'], true) && !$isFinalized): ?>
                                <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/assign" class="d-inline">
                                    <div class="input-group input-group-sm">
                                        <select name="general_teacher_id" class="form-select" required>
                                            <option value="">Re-assign Teacher...</option>
                                            <?php foreach ($generalTeachers as $gt): ?>
                                                <option value="<?= $gt['id'] ?>" <?= (int)$gt['id'] === (int)$assignment['general_teacher_id'] ? 'disabled' : '' ?>><?= htmlspecialchars($gt['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn text-white" style="background: #a01422;">Change</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ITGP Goal Form -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-pencil-square me-2"></i>Individual Transition Goal Plan (ITGP)</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <form id="itgpForm" method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp">
                            <!-- Student Profile Header (Auto-filled) -->
                            <div class="row mb-4 p-3 rounded border bg-light-subtle g-3">
                                <div class="col-md-6">
                                    <label class="form-label small font-weight-bold text-muted text-uppercase mb-0">Learner Name</label>
                                    <div class="form-control-plaintext font-weight-bold text-dark fs-5"><?= htmlspecialchars($iep['student_name']) ?></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small font-weight-bold text-muted text-uppercase mb-0">Disability Type / Exceptionality</label>
                                    <div class="form-control-plaintext text-muted"><?= htmlspecialchars($iep['disability_type'] ?? 'Not set') ?></div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Goal Pre-filled from ITP Recommendations -->
                                <div class="col-12">
                                    <label for="itgp_goal" class="form-label small font-weight-bold text-muted text-uppercase">Transition Goal / Target Objective <span class="text-danger">*</span></label>
                                    <textarea id="itgp_goal" name="itgp_goal" class="form-control border-2" style="border-radius: 8px;" rows="2" 
                                              placeholder="Define the primary transition target or objective..." 
                                              <?= !$canEdit ? 'readonly' : 'required' ?>><?= htmlspecialchars($itgp['goal'] ?? $itpRecommendationsBeginning ?? '') ?></textarea>
                                </div>

                                <!-- Entry Point Pre-filled from ITP Point of Entry -->
                                <div class="col-md-6">
                                    <label for="entry_point" class="form-label small font-weight-bold text-muted text-uppercase">Point of Entry <span class="text-danger">*</span></label>
                                    <input id="entry_point" name="entry_point" type="text" class="form-control border-2" style="border-radius: 8px;" 
                                           placeholder="e.g. Regular Class (Mainstreamed)"
                                           value="<?= htmlspecialchars($itgp['entry_point'] ?? $itp['point_of_entry'] ?? '') ?>" <?= !$canEdit ? 'readonly' : 'required' ?>>
                                </div>

                                <div class="col-md-6">
                                    <label for="learning_packages" class="form-label small font-weight-bold text-muted text-uppercase">Learning Packages / Curriculum Options</label>
                                    <input id="learning_packages" name="learning_packages" type="text" class="form-control border-2" style="border-radius: 8px;" 
                                           placeholder="e.g. Life Skills Package, Pre-vocational Package"
                                           value="<?= htmlspecialchars($itgp['learning_packages'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                                </div>

                                <!-- Activities Table (Dynamic Rows) -->
                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label small font-weight-bold text-muted text-uppercase mb-0">Enabling Activities & Learning Tasks</label>
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="addActivityBtn">
                                                <i class="bi bi-plus-lg me-1"></i>Add Activity Row
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle" id="activitiesTable" style="font-size: 0.9rem;">
                                            <thead class="text-white" style="background-color: #1e4072;">
                                                <tr>
                                                    <th>Competency / Skill</th>
                                                    <th>Activities</th>
                                                    <th>Time Frame</th>
                                                    <th>Person Responsible</th>
                                                    <th>Remarks</th>
                                                    <?php if ($canEdit): ?>
                                                        <th style="width: 50px;">Action</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $activitiesList = !empty($itgp['activities']) ? $itgp['activities'] : [['competency_skill' => '', 'activities' => '', 'time_frame' => '', 'person_responsible' => '', 'remarks' => '']];
                                                foreach ($activitiesList as $idx => $act): ?>
                                                    <tr>
                                                        <td>
                                                            <textarea name="activities[<?= $idx ?>][competency_skill]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Competency" rows="2" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($act['competency_skill'] ?? '') ?></textarea>
                                                        </td>
                                                        <td>
                                                            <textarea name="activities[<?= $idx ?>][activities]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Activities" rows="2" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($act['activities'] ?? '') ?></textarea>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="activities[<?= $idx ?>][time_frame]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Time Frame" value="<?= htmlspecialchars($act['time_frame'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="activities[<?= $idx ?>][person_responsible]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Responsible" value="<?= htmlspecialchars($act['person_responsible'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                                                        </td>
                                                        <td>
                                                            <textarea name="activities[<?= $idx ?>][remarks]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Remarks" rows="2" <?= !$canEdit ? 'readonly' : '' ?>><?= htmlspecialchars($act['remarks'] ?? '') ?></textarea>
                                                        </td>
                                                        <?php if ($canEdit): ?>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row-btn">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                </button>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <label for="itgp_recommendations" class="form-label small font-weight-bold text-muted text-uppercase">Educational Recommendations <span class="text-danger">*</span></label>
                                    <textarea id="itgp_recommendations" name="itgp_recommendations" class="form-control border-2" style="border-radius: 8px;" rows="3" 
                                              placeholder="Provide future recommendations for transition or regular class placement..." 
                                              <?= !$canEdit ? 'readonly' : 'required' ?>><?= htmlspecialchars($itgp['recommendations'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <?php if ($canEdit): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="status" class="form-label small font-weight-bold text-muted text-uppercase mb-0 me-2">Action Status:</label>
                                        <select id="status" name="status" class="form-select form-select-sm border-2" style="border-radius: 6px; width: 180px;">
                                            <option value="draft"<?= ($itgp['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Save as Draft</option>
                                            <option value="finalized"<?= ($itgp['status'] ?? '') === 'finalized' ? ' selected' : '' ?>>Finalize & Lock</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-lg text-white px-5" style="background-color: #a01422; border-radius: 8px; font-weight: 600;">
                                        <i class="bi bi-save me-2"></i>Save ITGP
                                    </button>
                                <?php elseif ($isFinalized): ?>
                                    <div>
                                        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Goal plan is finalized and signed. You can now view Class Placement Notice.</span>
                                    </div>
                                    <a href="<?= $basePath ?>/iep/<?= $iep['id'] ?>/placement-notice" class="btn btn-lg text-white px-5" style="background-color: #1e4072; border-radius: 8px; font-weight: 600;">
                                        View Class Placement <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                <?php else: ?>
                                    <div>
                                        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>You do not have editing permissions on this ITGP record.</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Scroll comments list to bottom
    var commentsBox = document.getElementById("commentsBox");
    if (commentsBox) {
        commentsBox.scrollTop = commentsBox.scrollHeight;
    }

    // Dynamic row addition/removal for activities
    var addBtn = document.getElementById("addActivityBtn");
    var tableBody = document.querySelector("#activitiesTable tbody");

    if (addBtn && tableBody) {
        addBtn.addEventListener("click", function () {
            var rowCount = tableBody.querySelectorAll("tr").length;
            var newRow = document.createElement("tr");
            newRow.innerHTML = `
                <td>
                    <textarea name="activities[${rowCount}][competency_skill]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Competency" rows="2"></textarea>
                </td>
                <td>
                    <textarea name="activities[${rowCount}][activities]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Activities" rows="2"></textarea>
                </td>
                <td>
                    <input type="text" name="activities[${rowCount}][time_frame]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Time Frame">
                </td>
                <td>
                    <input type="text" name="activities[${rowCount}][person_responsible]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Responsible">
                </td>
                <td>
                    <textarea name="activities[${rowCount}][remarks]" class="form-control form-control-sm border-0 shadow-none bg-transparent" placeholder="Remarks" rows="2"></textarea>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row-btn">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(newRow);
        });

        // Event delegation for removing rows
        tableBody.addEventListener("click", function (e) {
            var removeBtn = e.target.closest(".remove-row-btn");
            if (removeBtn) {
                var row = removeBtn.closest("tr");
                if (tableBody.querySelectorAll("tr").length > 1) {
                    row.remove();
                    reindexRows();
                } else {
                    // Just clear values of the last row
                    row.querySelectorAll("input, textarea").forEach(function (input) {
                        input.value = "";
                    });
                }
            }
        });
    }

    function reindexRows() {
        tableBody.querySelectorAll("tr").forEach(function (row, idx) {
            row.querySelectorAll("[name]").forEach(function (input) {
                var name = input.getAttribute("name");
                var updatedName = name.replace(/activities\[\d+\]/, "activities[" + idx + "]");
                input.setAttribute("name", updatedName);
            });
        });
    }

    // Finalize confirmation check
    var itgpForm = document.getElementById("itgpForm");
    var statusSelect = document.getElementById("status");
    if (itgpForm && statusSelect) {
        itgpForm.addEventListener("submit", function (e) {
            if (statusSelect.value === "finalized") {
                var confirmed = confirm("Finalize this ITGP? This marks the transition goal plan as ready and locks form fields.");
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
