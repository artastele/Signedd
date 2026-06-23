<?php
// Transition Readiness view for Process 10
$pageTitle = 'Transition Readiness — SignED';
require_once __DIR__ . '/../layouts/header.php';
$role = $_SESSION['role'];
$basePath = BASE_PATH;
$isFinalized = (!empty($readiness['status']) && $readiness['status'] === 'finalized');
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
                    <i class="bi bi-clipboard2-check me-2"></i>Transition Readiness
                </h1>
                <p class="text-muted mb-0">Process 10 — Preparedness & Goal Evaluations</p>
            </div>
            <div>
                <a href="<?= $basePath ?>/iep" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to IEPs
                </a>
            </div>
        </div>

        <!-- Alert messages -->
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

        <?php if ($isFinalized): ?>
            <div class="alert alert-info py-3 mb-4" style="border-left: 5px solid #1e4072; background-color: #eef4fc;">
                <h5 class="alert-heading text-primary-emphasis mb-1"><i class="bi bi-lock-fill me-2"></i>Finalized and Locked</h5>
                <p class="mb-0 small text-muted">This Transition Readiness assessment has been finalized on <?= htmlspecialchars($readiness['finalized_at'] ?? 'N/A') ?> and is now read-only.</p>
            </div>
        <?php endif; ?>

        <!-- Evidence Summary Card -->
        <div class="row g-4 mb-4">
            <!-- LMS / Progress Metrics -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header text-white py-3" style="background: #1e4072;">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-bar-chart-line me-2"></i>LMS Metrics Snapshot</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex flex-column gap-3">
                            <div class="p-3 border rounded-3 text-center" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <span class="d-block text-muted small text-uppercase font-weight-bold">Average Auto Score</span>
                                <h2 class="mb-0 mt-1 font-weight-bold" style="color: #a01422;">
                                    <?= isset($progressSnapshot['avg_auto_score']) ? number_format($progressSnapshot['avg_auto_score'], 1) : '0.0' ?>%
                                </h2>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-3 border rounded-3 text-center bg-light" style="border-color: #e2e8f0 !important;">
                                        <span class="d-block text-muted small">Submissions</span>
                                        <h4 class="mb-0 mt-1 font-weight-bold text-dark"><?= intval($progressSnapshot['submissions'] ?? 0) ?></h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 border rounded-3 text-center bg-light" style="border-color: #e2e8f0 !important;">
                                        <span class="d-block text-muted small">Attempted</span>
                                        <h4 class="mb-0 mt-1 font-weight-bold text-dark"><?= intval($progressSnapshot['activities_attempted'] ?? 0) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 border rounded-3 bg-light" style="border-color: #e2e8f0 !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Completion Rate</span>
                                    <span class="font-weight-bold text-dark"><?= $progressSnapshot['completion_rate'] ?? '0.0' ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= floatval($progressSnapshot['completion_rate'] ?? 0) ?>%; background-color: #3b6d11;" aria-valuenow="<?= floatval($progressSnapshot['completion_rate'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Context (Progress Report & COT) -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header text-white py-3" style="background: #1e4072;">
                        <h5 class="mb-0 font-weight-bold"><i class="bi bi-file-earmark-medical me-2"></i>Linked Assessment Records</h5>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3" style="background-color: #f8fafc; border-color: #cbd5e1 !important;">
                                    <h6 class="font-weight-bold text-dark mb-2"><i class="bi bi-file-earmark-bar-graph me-2 text-danger"></i>Progress Report</h6>
                                    <div class="small">
                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">School Year:</span>
                                            <span class="font-weight-bold"><?= htmlspecialchars($progressReport['school_year'] ?? 'N/A') ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Quarter:</span>
                                            <span class="font-weight-bold"><?= htmlspecialchars($progressReport['quarter'] ?? 'N/A') ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Status:</span>
                                            <span class="badge bg-success">Finalized</span>
                                        </div>
                                        <div class="mt-2 text-muted-hover text-wrap" style="font-size:0.85rem; line-height: 1.4;">
                                            <strong>Remarks:</strong> <?= htmlspecialchars($progressReport['teacher_remarks'] ?? 'No remarks provided.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3" style="background-color: #f8fafc; border-color: #cbd5e1 !important;">
                                    <h6 class="font-weight-bold text-dark mb-2"><i class="bi bi-eye me-2 text-primary"></i>Classroom Observation (COT)</h6>
                                    <div class="small">
                                        <?php if (!empty($cot)): ?>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Date Conducted:</span>
                                                <span class="font-weight-bold"><?= htmlspecialchars($cot['observation_date'] ?? 'N/A') ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Status:</span>
                                                <span class="badge bg-success"><?= ucfirst($cot['status']) ?></span>
                                            </div>
                                            <div class="mt-2 text-muted-hover text-wrap" style="font-size:0.85rem; line-height: 1.4;">
                                                <strong>Recommendations:</strong> <?= htmlspecialchars($cot['recommendations'] ?? 'No recommendations provided.') ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted small mb-0 mt-4 text-center">No classroom observations linked to this IEP context.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/transition-readiness">
            <!-- Form Card 1: Overall Evaluation -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-stars me-2"></i>Readiness Summary & Evaluation</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="readiness_result" class="form-label small font-weight-bold text-muted text-uppercase">Overall Readiness Recommendation</label>
                            <select id="readiness_result" name="readiness_result" class="form-select form-select-lg border-2" style="border-radius: 8px;" <?= $isFinalized ? 'disabled' : 'required' ?>>
                                <?php $selectedReadiness = $readiness['readiness_result'] ?? 'For Re-evaluation'; ?>
                                <option value="For Re-evaluation"<?= $selectedReadiness === 'For Re-evaluation' ? ' selected' : '' ?>>For Re-evaluation</option>
                                <option value="Ready for Inclusion"<?= $selectedReadiness === 'Ready for Inclusion' ? ' selected' : '' ?>>Ready for Inclusion</option>
                                <option value="Needs More Support"<?= $selectedReadiness === 'Needs More Support' ? ' selected' : '' ?>>Needs More Support</option>
                                <option value="Not Yet Ready"<?= $selectedReadiness === 'Not Yet Ready' ? ' selected' : '' ?>>Not Yet Ready</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="overall_status" class="form-label small font-weight-bold text-muted text-uppercase">Overall Performance Status</label>
                            <select id="overall_status" name="overall_status" class="form-select form-select-lg border-2" style="border-radius: 8px;" <?= $isFinalized ? 'disabled' : 'required' ?>>
                                <?php $selStatus = $readiness['overall_status'] ?? 'partial'; ?>
                                <option value="ready"<?= $selStatus === 'ready' ? ' selected' : '' ?>>Ready</option>
                                <option value="partial"<?= $selStatus === 'partial' ? ' selected' : '' ?>>Partial</option>
                                <option value="not_ready"<?= $selStatus === 'not_ready' ? ' selected' : '' ?>>Not Ready</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="evidence_summary" class="form-label small font-weight-bold text-muted text-uppercase">Evidence Summary</label>
                            <textarea id="evidence_summary" name="evidence_summary" class="form-control border-2" style="border-radius: 8px;" rows="4" 
                                      placeholder="Synthesize the evidence from the LMS, grades, progress reports, and classroom observations..." 
                                      <?= $isFinalized ? 'readonly' : '' ?>><?= htmlspecialchars($readiness['evidence_summary'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="teacher_recommendation" class="form-label small font-weight-bold text-muted text-uppercase">Teacher Recommendation Details</label>
                            <textarea id="teacher_recommendation" name="teacher_recommendation" class="form-control border-2" style="border-radius: 8px;" rows="3" 
                                      placeholder="Provide the teacher's formal recommendation for functional transition path..." 
                                      <?= $isFinalized ? 'readonly' : '' ?>><?= htmlspecialchars($readiness['teacher_recommendation'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="overall_remarks" class="form-label small font-weight-bold text-muted text-uppercase">Overall Remarks / Notes</label>
                            <textarea id="overall_remarks" name="overall_remarks" class="form-control border-2" style="border-radius: 8px;" rows="3" 
                                      placeholder="Provide additional remarks or contextual information..." 
                                      <?= $isFinalized ? 'readonly' : '' ?>><?= htmlspecialchars($readiness['overall_remarks'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check p-3 border rounded-3 bg-light" style="border-color: #cbd5e1;">
                                <input type="checkbox" id="overall_status_overridden" name="overall_status_overridden" value="1" class="form-check-input ms-0 me-2"
                                       <?= !empty($readiness['overall_status_overridden']) ? ' checked' : '' ?> <?= $isFinalized ? 'disabled' : '' ?> />
                                <label for="overall_status_overridden" class="form-check-label font-weight-bold text-dark ms-1 cursor-pointer">
                                    Override suggested readiness status and justify with manual assessment
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card 2: Transition Goals Checklist -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
                <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-check2-square me-2"></i>IEP Transition Goals Evaluation</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <p class="text-muted small mb-4">Evaluate the learner's achievement across the customized IEP steps/goals defined in Part 3 of their IEP.</p>
                    
                    <?php if (empty($readinessGoals)): ?>
                        <div class="alert alert-warning py-3 text-center mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>No transition goals/IEP steps found for this student.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($readinessGoals as $goal): 
                                $stepId = intval($goal['step_id']);
                            ?>
                                <div class="p-4 border rounded-3 position-relative transition-all hover-shadow-sm" style="background-color: #f8fafc; border-color: #e2e8f0; border-left: 5px solid #a01422 !important;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <h5 class="mb-0 font-weight-bold" style="color:#1e4072;">
                                            Goal <?= htmlspecialchars($goal['step_number']) ?> &mdash; <?= htmlspecialchars($goal['pdsp_domain']) ?>
                                        </h5>
                                        <span class="badge py-2 px-3 bg-secondary-subtle text-secondary-emphasis" style="border-radius: 6px;">IEP Step Context</span>
                                    </div>

                                    <input type="hidden" name="goals[<?= $stepId ?>][iep_step_id]" value="<?= $stepId ?>">
                                    <input type="hidden" name="goals[<?= $stepId ?>][pdsp_domain]" value="<?= htmlspecialchars($goal['pdsp_domain']) ?>">

                                    <div class="mb-3">
                                        <label for="goal_text_<?= $stepId ?>" class="form-label small font-weight-bold text-muted text-uppercase">Goal Description</label>
                                        <textarea id="goal_text_<?= $stepId ?>" name="goals[<?= $stepId ?>][goal_text]" class="form-control border-2" style="border-radius: 8px;" rows="2" <?= $isFinalized ? 'readonly' : 'required' ?>><?= htmlspecialchars($goal['goal_text']) ?></textarea>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="suggested_status_<?= $stepId ?>" class="form-label small font-weight-bold text-muted text-uppercase">Suggested Status (Based on LMS/Observation)</label>
                                            <select id="suggested_status_<?= $stepId ?>" name="goals[<?= $stepId ?>][suggested_status]" class="form-select border-2" style="border-radius: 8px;" <?= $isFinalized ? 'disabled' : 'required' ?>>
                                                <option value="ready"<?= $goal['suggested_status'] === 'ready' ? ' selected' : '' ?>>Ready</option>
                                                <option value="partial"<?= $goal['suggested_status'] === 'partial' ? ' selected' : '' ?>>Partial</option>
                                                <option value="not_ready"<?= $goal['suggested_status'] === 'not_ready' ? ' selected' : '' ?>>Not Ready</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="final_status_<?= $stepId ?>" class="form-label small font-weight-bold text-muted text-uppercase">Final Evaluated Status</label>
                                            <select id="final_status_<?= $stepId ?>" name="goals[<?= $stepId ?>][final_status]" class="form-select border-2" style="border-radius: 8px;" <?= $isFinalized ? 'disabled' : 'required' ?>>
                                                <option value="ready"<?= $goal['final_status'] === 'ready' ? ' selected' : '' ?>>Ready</option>
                                                <option value="partial"<?= $goal['final_status'] === 'partial' ? ' selected' : '' ?>>Partial</option>
                                                <option value="not_ready"<?= $goal['final_status'] === 'not_ready' ? ' selected' : '' ?>>Not Ready</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="goal_remarks_<?= $stepId ?>" class="form-label small font-weight-bold text-muted text-uppercase">Goal Notes / Remarks</label>
                                        <textarea id="goal_remarks_<?= $stepId ?>" name="goals[<?= $stepId ?>][remarks]" class="form-control border-2" style="border-radius: 8px;" rows="2" 
                                                  placeholder="Provide detail on objective criteria, observations, or override reasons..." 
                                                  <?= $isFinalized ? 'readonly' : '' ?>><?= htmlspecialchars($goal['remarks']) ?></textarea>
                                    </div>

                                    <div class="form-check p-3 border rounded-3 bg-white" style="border-color: #e2e8f0;">
                                        <input type="checkbox" id="goal_override_<?= $stepId ?>" name="goals[<?= $stepId ?>][status_overridden]" value="1" class="form-check-input ms-0 me-2"
                                               <?= !empty($goal['status_overridden']) ? ' checked' : '' ?> <?= $isFinalized ? 'disabled' : '' ?> />
                                        <label for="goal_override_<?= $stepId ?>" class="form-check-label font-weight-bold text-dark ms-1 cursor-pointer">
                                            Override suggested status for this goal
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Card 3: Action Controls -->
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4 bg-light d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <?php if (!$isFinalized): ?>
                        <div class="d-flex align-items-center gap-2">
                            <label for="status" class="form-label small font-weight-bold text-muted text-uppercase mb-0 me-2">Submission Action:</label>
                            <select id="status" name="status" class="form-select form-select-sm border-2" style="border-radius: 6px; width: 180px;">
                                <option value="draft"<?= ($readiness['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Save as Draft</option>
                                <option value="finalized"<?= ($readiness['status'] ?? '') === 'finalized' ? ' selected' : '' ?>>Finalize & Lock</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-lg text-white px-5" style="background-color: #a01422; border-radius: 8px; font-weight: 600;">
                            <i class="bi bi-save me-2"></i>Save Assessment
                        </button>
                    <?php else: ?>
                        <div>
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Assessment finalized. Click 'Back to IEPs' to view other records.</span>
                        </div>
                        <a href="<?= $basePath ?>/iep" class="btn btn-lg btn-outline-secondary px-5" style="border-radius: 8px; font-weight: 600;">
                            <i class="bi bi-arrow-left me-2"></i>Back to IEP Repository
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
