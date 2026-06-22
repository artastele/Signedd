<?php
// Transition Readiness view for Process 10
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transition Readiness</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Transition Readiness</h1>

        <div class="card mb-4">
            <div class="card-body">
                <p><strong>Student:</strong> <?= htmlspecialchars($iep['student_name'] ?? 'Unknown') ?> <span class="text-muted">(LRN: <?= htmlspecialchars($iep['lrn'] ?? 'N/A') ?>)</span></p>
                <p><strong>IEP Record:</strong> <?= intval($iep['id']) ?></p>
                <p><strong>Progress Report Status:</strong> <?= htmlspecialchars($progressReport['status'] ?? 'none') ?>
                    <?php if (!empty($progressReport['updated_at'])): ?>
                        <span class="text-muted">(updated <?= htmlspecialchars($progressReport['updated_at']) ?>)</span>
                    <?php endif; ?>
                </p>
                <?php if (!empty($readiness['status']) && $readiness['status'] === 'finalized'): ?>
                    <div class="alert alert-info">This readiness record is finalized and can still be updated if needed. Finalization date: <?= htmlspecialchars($readiness['finalized_at'] ?? 'N/A') ?>.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/transition-readiness">
            <div class="card mb-4">
                <div class="card-header"><strong>Readiness Summary</strong></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="readiness_result">Overall Readiness Result</label>
                        <select id="readiness_result" name="readiness_result" class="form-control">
                            <?php $selectedReadiness = $readiness['readiness_result'] ?? 'For Re-evaluation'; ?>
                            <option value="For Re-evaluation"<?= $selectedReadiness === 'For Re-evaluation' ? ' selected' : '' ?>>For Re-evaluation</option>
                            <option value="Ready for Inclusion"<?= $selectedReadiness === 'Ready for Inclusion' ? ' selected' : '' ?>>Ready for Inclusion</option>
                            <option value="Needs More Support"<?= $selectedReadiness === 'Needs More Support' ? ' selected' : '' ?>>Needs More Support</option>
                            <option value="Not Yet Ready"<?= $selectedReadiness === 'Not Yet Ready' ? ' selected' : '' ?>>Not Yet Ready</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="evidence_summary">Evidence Summary</label>
                        <textarea id="evidence_summary" name="evidence_summary" class="form-control" rows="5"><?= htmlspecialchars($readiness['evidence_summary'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="teacher_recommendation">Teacher Recommendation</label>
                        <textarea id="teacher_recommendation" name="teacher_recommendation" class="form-control" rows="4"><?= htmlspecialchars($readiness['teacher_recommendation'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>Overall Evaluation</strong></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="overall_status">Overall Status</label>
                        <select id="overall_status" name="overall_status" class="form-control">
                            <option value="ready"<?= ($readiness['overall_status'] ?? 'partial') === 'ready' ? ' selected' : '' ?>>Ready</option>
                            <option value="partial"<?= ($readiness['overall_status'] ?? 'partial') === 'partial' ? ' selected' : '' ?>>Partial</option>
                            <option value="not_ready"<?= ($readiness['overall_status'] ?? 'partial') === 'not_ready' ? ' selected' : '' ?>>Not Ready</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="overall_remarks">Overall Remarks</label>
                        <textarea id="overall_remarks" name="overall_remarks" class="form-control" rows="4"><?= htmlspecialchars($readiness['overall_remarks'] ?? '') ?></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" id="overall_status_overridden" name="overall_status_overridden" value="1" class="form-check-input"<?= !empty($readiness['overall_status_overridden']) ? ' checked' : '' ?> />
                        <label for="overall_status_overridden" class="form-check-label">Override suggested readiness status</label>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>Transition Goals</strong></div>
                <div class="card-body">
                    <?php if (empty($readinessGoals)): ?>
                        <div class="alert alert-warning">No transition goals are available for this IEP.</div>
                    <?php else: ?>
                        <?php foreach ($readinessGoals as $goal): ?>
                            <div class="goal-block mb-4 p-3 border rounded">
                                <h3>Goal <?= htmlspecialchars($goal['step_number']) ?> &ndash; <?= htmlspecialchars($goal['pdsp_domain']) ?></h3>
                                <input type="hidden" name="goals[<?= intval($goal['step_id']) ?>][iep_step_id]" value="<?= intval($goal['step_id']) ?>">
                                <input type="hidden" name="goals[<?= intval($goal['step_id']) ?>][pdsp_domain]" value="<?= htmlspecialchars($goal['pdsp_domain']) ?>">
                                <div class="form-group">
                                    <label for="goal_text_<?= intval($goal['step_id']) ?>">Goal Description</label>
                                    <textarea id="goal_text_<?= intval($goal['step_id']) ?>" name="goals[<?= intval($goal['step_id']) ?>][goal_text]" class="form-control" rows="3"><?= htmlspecialchars($goal['goal_text']) ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="suggested_status_<?= intval($goal['step_id']) ?>">Suggested Status</label>
                                            <select id="suggested_status_<?= intval($goal['step_id']) ?>" name="goals[<?= intval($goal['step_id']) ?>][suggested_status]" class="form-control">
                                                <option value="ready"<?= $goal['suggested_status'] === 'ready' ? ' selected' : '' ?>>Ready</option>
                                                <option value="partial"<?= $goal['suggested_status'] === 'partial' ? ' selected' : '' ?>>Partial</option>
                                                <option value="not_ready"<?= $goal['suggested_status'] === 'not_ready' ? ' selected' : '' ?>>Not Ready</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="final_status_<?= intval($goal['step_id']) ?>">Final Status</label>
                                            <select id="final_status_<?= intval($goal['step_id']) ?>" name="goals[<?= intval($goal['step_id']) ?>][final_status]" class="form-control">
                                                <option value="ready"<?= $goal['final_status'] === 'ready' ? ' selected' : '' ?>>Ready</option>
                                                <option value="partial"<?= $goal['final_status'] === 'partial' ? ' selected' : '' ?>>Partial</option>
                                                <option value="not_ready"<?= $goal['final_status'] === 'not_ready' ? ' selected' : '' ?>>Not Ready</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" id="goal_override_<?= intval($goal['step_id']) ?>" name="goals[<?= intval($goal['step_id']) ?>][status_overridden]" value="1" class="form-check-input"<?= !empty($goal['status_overridden']) ? ' checked' : '' ?> />
                                    <label for="goal_override_<?= intval($goal['step_id']) ?>" class="form-check-label">Override suggested status</label>
                                </div>
                                <div class="form-group">
                                    <label for="goal_remarks_<?= intval($goal['step_id']) ?>">Goal Notes / Remarks</label>
                                    <textarea id="goal_remarks_<?= intval($goal['step_id']) ?>" name="goals[<?= intval($goal['step_id']) ?>][remarks]" class="form-control" rows="3"><?= htmlspecialchars($goal['remarks']) ?></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-group">
                        <label for="status">Record Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="draft"<?= ($readiness['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft</option>
                            <option value="finalized"<?= ($readiness['status'] ?? '') === 'finalized' ? ' selected' : '' ?>>Finalize</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Transition Readiness</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
