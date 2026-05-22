<?php
$pageTitle = 'Transition Workflow - SignED';
require_once __DIR__ . '/../layouts/header.php';

$iepId = (int)($ctx['id'] ?? 0);
$studentId = (int)($ctx['student_id'] ?? 0);
$progress = $workflow['progress_report'] ?? null;
$cot = $workflow['cot'] ?? null;
$readiness = $workflow['readiness'] ?? null;
$itp = $workflow['itp'] ?? null;
$inclusive = $workflow['inclusive_iep'] ?? null;
$itgp = $workflow['itgp'] ?? null;
$placement = $workflow['placement'] ?? null;
$progressSummary = $evidence['progress_summary'] ?? ['submissions' => 0, 'avg_score' => null];

$statusBadge = static function (?array $row, string $empty = 'Not started'): string {
    if (!$row) {
        return '<span class="badge bg-secondary">' . htmlspecialchars($empty) . '</span>';
    }
    $status = $row['status'] ?? $row['placement_status'] ?? 'draft';
    $color = in_array($status, ['finalized','completed','signed','Notice Sent','Placed','Approved'], true) ? '#3b6d11' : '#5a6670';
    return '<span class="badge" style="background:' . $color . ';">' . htmlspecialchars(ucwords(str_replace('_', ' ', $status))) . '</span>';
};

$json = static function ($value): array {
    if (is_array($value)) return $value;
    if (!is_string($value) || $value === '') return [];
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : [];
};

$progressRatings = $json($progress['ratings'] ?? null);
$cotRatings = $json($cot['ratings'] ?? null);
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid py-3">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/workspace/<?php echo $iepId; ?>" class="btn btn-sm" style="background:#1e4072;color:#fff;">
            <i class="ti ti-arrow-left me-1"></i>Back to IEP Workspace
        </a>
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold" style="color:#1e4072;">Unified Transition Workflow</h4>
            <div class="text-muted small">
                <?php echo htmlspecialchars($ctx['student_name'] ?? 'Learner'); ?> |
                LRN: <?php echo htmlspecialchars($ctx['lrn'] ?? ''); ?> |
                IEP #<?php echo $iepId; ?>
            </div>
        </div>
        <span class="badge" style="background:#1e4072;"><?php echo htmlspecialchars($ctx['school_year'] ?? ''); ?></span>
    </div>

    <div class="alert alert-info py-2 small">
        This workflow reuses the existing IEP, learner submissions, progress evidence, COT result, and selected receiving teacher account. It does not create a separate learner record.
    </div>

    <div class="row g-3 mb-4">
        <?php
        $steps = [
            ['Progress Report', $progress, 'progress-report'],
            ['COT Observation', $cot, 'cot-observation'],
            ['Readiness', $readiness, 'transition-readiness'],
            ['ITP', $itp, 'individual-transition-plan'],
            ['Inclusive IEP + ITGP', $inclusive, 'inclusive-iep-itgp'],
            ['Placement Notice', $placement, 'placement-notice'],
        ];
        foreach ($steps as [$label, $row, $anchor]):
        ?>
        <div class="col-md-4 col-xl-2">
            <a href="#<?php echo $anchor; ?>" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="fw-semibold small mb-2" style="color:#1e4072;"><?php echo htmlspecialchars($label); ?></div>
                        <?php echo $statusBadge($row); ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="small text-muted">Learner submissions</div>
                    <div class="h4 mb-0" style="color:#1e4072;"><?php echo (int)($progressSummary['submissions'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="small text-muted">Average auto score</div>
                    <div class="h4 mb-0" style="color:#1e4072;"><?php echo $progressSummary['avg_score'] !== null ? round((float)$progressSummary['avg_score'], 1) : 'No data'; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="small text-muted">Suggested readiness</div>
                    <div class="fw-bold" style="color:#1e4072;"><?php echo htmlspecialchars($suggestedReadiness); ?></div>
                </div>
            </div>
        </div>
    </div>

    <section class="card mb-4" id="progress-report">
        <div class="card-header text-white" style="background:#1e4072;">Progress Report Card <?php echo $statusBadge($progress); ?></div>
        <div class="card-body">
            <form method="POST" action="<?php echo $basePath; ?>/iep/<?php echo $iepId; ?>/progress-report" class="row g-3">
                <div class="col-md-3"><label class="form-label small">School Year</label><input name="school_year" class="form-control" value="<?php echo htmlspecialchars($progress['school_year'] ?? $ctx['school_year'] ?? ''); ?>"></div>
                <div class="col-md-3"><label class="form-label small">Quarter</label><input name="quarter" class="form-control" value="<?php echo htmlspecialchars($progress['quarter'] ?? ''); ?>"></div>
                <?php foreach (['academic','behavior','communication','social'] as $key): ?>
                <div class="col-md-3"><label class="form-label small"><?php echo ucfirst($key); ?> rating</label><input name="rating_<?php echo $key; ?>" class="form-control" value="<?php echo htmlspecialchars($progressRatings[$key] ?? ''); ?>"></div>
                <?php endforeach; ?>
                <div class="col-md-6"><label class="form-label small">Attendance summary</label><textarea name="attendance_summary" class="form-control" rows="3"><?php echo htmlspecialchars($progress['attendance_summary'] ?? ''); ?></textarea></div>
                <div class="col-md-6"><label class="form-label small">Progress summary</label><textarea name="progress_summary" class="form-control" rows="3"><?php echo htmlspecialchars($progress['progress_summary'] ?? 'Submissions: ' . (int)($progressSummary['submissions'] ?? 0)); ?></textarea></div>
                <div class="col-12"><label class="form-label small">Teacher remarks</label><textarea name="teacher_remarks" class="form-control" rows="3"><?php echo htmlspecialchars($progress['teacher_remarks'] ?? ''); ?></textarea></div>
                <div class="col-md-3"><select name="status" class="form-select"><option value="draft">Draft</option><option value="finalized" <?php echo (($progress['status'] ?? '') === 'finalized') ? 'selected' : ''; ?>>Finalized</option></select></div>
                <div class="col-12"><button class="btn" style="background:#1e4072;color:#fff;">Save Progress Report</button></div>
            </form>
        </div>
    </section>

    <section class="card mb-4" id="cot-observation">
        <div class="card-header text-white" style="background:#1e4072;">COT Observation <?php echo $statusBadge($cot); ?></div>
        <div class="card-body">
            <form method="POST" action="<?php echo $basePath; ?>/iep/<?php echo $iepId; ?>/cot-observation" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Observed teacher</label>
                    <select name="observed_teacher_id" class="form-select" required>
                        <option value="">Select teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo (int)$teacher['id']; ?>" <?php echo ((int)($cot['observed_teacher_id'] ?? 0) === (int)$teacher['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($teacher['name'] . ' (' . $teacher['role'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label small">Quarter</label><input name="quarter" class="form-control" value="<?php echo htmlspecialchars($cot['quarter'] ?? ''); ?>"></div>
                <div class="col-md-3"><label class="form-label small">Observation date</label><input type="date" name="observation_date" class="form-control" value="<?php echo htmlspecialchars($cot['observation_date'] ?? ''); ?>"></div>
                <?php foreach (['planning','environment','instruction','assessment'] as $key): ?>
                <div class="col-md-3"><label class="form-label small"><?php echo ucfirst($key); ?> rating</label><input name="rating_<?php echo $key; ?>" class="form-control" value="<?php echo htmlspecialchars($cotRatings[$key] ?? ''); ?>"></div>
                <?php endforeach; ?>
                <div class="col-md-6"><label class="form-label small">Strengths</label><textarea name="strengths" class="form-control" rows="3"><?php echo htmlspecialchars($cot['strengths'] ?? ''); ?></textarea></div>
                <div class="col-md-6"><label class="form-label small">Recommendations</label><textarea name="recommendations" class="form-control" rows="3"><?php echo htmlspecialchars($cot['recommendations'] ?? ''); ?></textarea></div>
                <div class="col-md-3"><select name="status" class="form-select"><option value="draft">Draft</option><option value="finalized" <?php echo (($cot['status'] ?? '') === 'finalized') ? 'selected' : ''; ?>>Finalized + Notify Teacher</option></select></div>
                <div class="col-12"><button class="btn" style="background:#1e4072;color:#fff;">Save COT</button></div>
            </form>
        </div>
    </section>

    <section class="card mb-4" id="transition-readiness">
        <div class="card-header text-white" style="background:#1e4072;">Transition Readiness <?php echo $statusBadge($readiness); ?></div>
        <div class="card-body">
            <form method="POST" action="<?php echo $basePath; ?>/iep/<?php echo $iepId; ?>/transition-readiness" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Readiness result</label>
                    <select name="readiness_result" class="form-select">
                        <?php foreach (['Ready for Inclusion','Needs More Support','Not Yet Ready','For Re-evaluation'] as $result): ?>
                        <option value="<?php echo htmlspecialchars($result); ?>" <?php echo (($readiness['readiness_result'] ?? $suggestedReadiness) === $result) ? 'selected' : ''; ?>><?php echo htmlspecialchars($result); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8"><label class="form-label small">Evidence summary</label><textarea name="evidence_summary" class="form-control" rows="3"><?php echo htmlspecialchars($readiness['evidence_summary'] ?? 'Learner submissions: ' . (int)($progressSummary['submissions'] ?? 0)); ?></textarea></div>
                <div class="col-12"><label class="form-label small">Teacher recommendation</label><textarea name="teacher_recommendation" class="form-control" rows="3"><?php echo htmlspecialchars($readiness['teacher_recommendation'] ?? ''); ?></textarea></div>
                <div class="col-md-3"><select name="status" class="form-select"><option value="draft">Draft</option><option value="finalized" <?php echo (($readiness['status'] ?? '') === 'finalized') ? 'selected' : ''; ?>>Finalized</option></select></div>
                <div class="col-12"><button class="btn" style="background:#1e4072;color:#fff;">Save Readiness</button></div>
            </form>
        </div>
    </section>

    <section class="card mb-4" id="individual-transition-plan">
        <div class="card-header text-white" style="background:#1e4072;">Individual Transition Plan <?php echo $statusBadge($itp); ?></div>
        <div class="card-body">
            <?php if (!$readiness): ?><div class="alert alert-warning py-2">Create transition readiness before ITP.</div><?php endif; ?>
            <form method="POST" action="<?php echo $basePath; ?>/iep/<?php echo $iepId; ?>/individual-transition-plan" class="row g-3">
                <div class="col-md-4"><label class="form-label small">Entry point</label><input name="entry_point" class="form-control" value="<?php echo htmlspecialchars($itp['entry_point'] ?? ($readiness['readiness_result'] ?? '')); ?>"></div>
                <div class="col-md-8"><label class="form-label small">Transition services</label><textarea name="transition_services" class="form-control" rows="3"><?php echo htmlspecialchars($itp['transition_services'] ?? ''); ?></textarea></div>
                <div class="col-md-6"><label class="form-label small">Support needed</label><textarea name="support_needed" class="form-control" rows="3"><?php echo htmlspecialchars($itp['support_needed'] ?? ''); ?></textarea></div>
                <div class="col-md-6"><label class="form-label small">Team responsibilities</label><textarea name="team_responsibilities" class="form-control" rows="3"><?php echo htmlspecialchars($itp['team_responsibilities'] ?? ''); ?></textarea></div>
                <div class="col-md-3"><select name="status" class="form-select"><option value="draft">Draft</option><option value="completed" <?php echo (($itp['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option></select></div>
                <div class="col-12"><button class="btn" style="background:#1e4072;color:#fff;" <?php echo !$readiness ? 'disabled' : ''; ?>>Save ITP</button></div>
            </form>
        </div>
    </section>

    <section class="card mb-4" id="inclusive-iep-itgp">
        <div class="card-header text-white" style="background:#1e4072;">Inclusive IEP + ITGP <?php echo $statusBadge($inclusive); ?></div>
        <div class="card-body">
            <?php if (!$readiness || !$itp): ?><div class="alert alert-warning py-2">Transition readiness and ITP are required first.</div><?php endif; ?>
            <form method="POST" action="<?php echo $basePath; ?>/iep/<?php echo $iepId; ?>/inclusive-iep-itgp" class="row g-3">
                <div class="col-md-6"><label class="form-label small">Generated Inclusive IEP summary</label><textarea name="generated_summary" class="form-control" rows="3"><?php echo htmlspecialchars($inclusive['generated_summary'] ?? 'Generated from existing IEP #' . $iepId . ' plus transition details.'); ?></textarea></div>
                <div class="col-md-3"><label class="form-label small">Disability/exceptionality</label><input name="disability" class="form-control" value="<?php echo htmlspecialchars($itgp['disability'] ?? ''); ?>"></div>
                <div class="col-md-3"><label class="form-label small">Entry point</label><input name="entry_point" class="form-control" value="<?php echo htmlspecialchars($itgp['entry_point'] ?? $itp['entry_point'] ?? ''); ?>"></div>
                <div class="col-md-6"><label class="form-label small">ITGP goal</label><textarea name="itgp_goal" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6"><label class="form-label small">Learning package/s</label><textarea name="learning_packages" class="form-control" rows="2"></textarea></div>
                <div class="col-md-4"><label class="form-label small">Competency/Skill</label><textarea name="competency_skill" class="form-control" rows="2"></textarea></div>
                <div class="col-md-4"><label class="form-label small">Activities</label><textarea name="activities" class="form-control" rows="2"></textarea></div>
                <div class="col-md-4"><label class="form-label small">Time frame</label><input name="time_frame" class="form-control"></div>
                <div class="col-md-4"><label class="form-label small">Person responsible</label><input name="person_responsible" class="form-control"></div>
                <div class="col-md-4"><label class="form-label small">Remarks</label><input name="itgp_remarks" class="form-control"></div>
                <div class="col-md-4"><label class="form-label small">Recommendations</label><input name="itgp_recommendations" class="form-control" value="<?php echo htmlspecialchars($itgp['recommendations'] ?? ''); ?>"></div>
                <div class="col-md-3"><select name="status" class="form-select"><option value="draft">Draft</option><option value="for_signature">For Signature</option><option value="signed" <?php echo (($inclusive['status'] ?? '') === 'signed') ? 'selected' : ''; ?>>Signed</option></select></div>
                <div class="col-12"><button class="btn" style="background:#1e4072;color:#fff;" <?php echo (!$readiness || !$itp) ? 'disabled' : ''; ?>>Save Inclusive IEP + ITGP</button></div>
            </form>
        </div>
    </section>

    <section class="card mb-4" id="placement-notice">
        <div class="card-header text-white" style="background:#1e4072;">Regular Class Placement / Transfer Notice <?php echo $statusBadge($placement); ?></div>
        <div class="card-body">
            <?php if (!$inclusive || !$itgp): ?><div class="alert alert-warning py-2">Inclusive IEP and ITGP are required first.</div><?php endif; ?>
            <form method="POST" action="<?php echo $basePath; ?>/iep/<?php echo $iepId; ?>/placement-notice" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Receiving teacher account</label>
                    <select name="receiving_teacher_id" class="form-select" required>
                        <option value="">Select exact teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo (int)$teacher['id']; ?>" <?php echo ((int)($placement['receiving_teacher_id'] ?? 0) === (int)$teacher['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($teacher['name'] . ' - ' . $teacher['email']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Notification is sent only to this selected user ID.</div>
                </div>
                <div class="col-md-4"><label class="form-label small">Target grade/section/class</label><input name="target_grade_section" class="form-control" value="<?php echo htmlspecialchars($placement['target_grade_section'] ?? ''); ?>"></div>
                <div class="col-md-4"><label class="form-label small">Effective date</label><input type="date" name="effective_date" class="form-control" value="<?php echo htmlspecialchars($placement['effective_date'] ?? ''); ?>"></div>
                <div class="col-12"><label class="form-label small">Support needed</label><textarea name="support_needed" class="form-control" rows="3"><?php echo htmlspecialchars($placement['support_needed'] ?? $itp['support_needed'] ?? ''); ?></textarea></div>
                <div class="col-md-3"><label class="form-label small">Placement status</label><select name="placement_status" class="form-select"><?php foreach (['Draft','For Approval','Approved','Notice Sent','Placed'] as $st): ?><option value="<?php echo $st; ?>" <?php echo (($placement['placement_status'] ?? '') === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label small">Approval status</label><select name="approval_status" class="form-select"><option value="draft">Draft</option><option value="for_approval">For approval</option><option value="approved" <?php echo (($placement['approval_status'] ?? '') === 'approved') ? 'selected' : ''; ?>>Approved</option></select></div>
                <div class="col-12"><button class="btn" style="background:#1e4072;color:#fff;" <?php echo (!$inclusive || !$itgp) ? 'disabled' : ''; ?>>Save Placement Notice</button></div>
            </form>
        </div>
    </section>
</div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
