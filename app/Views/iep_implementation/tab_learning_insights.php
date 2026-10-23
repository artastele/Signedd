<?php
// Part of: SignED — IEP Implementation Workspace (Learning Insights & Resource Recommendations)
// Displays four-factor learning gap analysis and evidence-based learning material suggestions for SPED Teacher review.
//
// Guaranteed:
//   - Advisory observations only; teacher retains full discretion over IEP mastery and assignment.
//   - Exact repository linkage with verified evidence citations.
//   - Responsive layout, collapsible details, clean and modern buttons conforming to project rules.

$learningInsights = $learningInsights ?? [];
$totalInsightsRecs = 0;
$totalReviewAdvised = 0;
foreach ($learningInsights as $ins) {
    $totalInsightsRecs += (int)($ins['recommendations_count'] ?? 0);
    if (!empty($ins['performance_summary']['teacher_review_required'])) {
        $totalReviewAdvised++;
    }
}
?>

<div class="tab-pane fade" id="tab-learning-insights" role="tabpanel" aria-labelledby="tab-learning-insights-btn">
    
    <!-- Top Banner & Advisory Disclaimer -->
    <div class="card mb-3 border-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
        <div class="card-header py-3 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2 text-white"
             style="background: linear-gradient(135deg, #1e4072 0%, #2a5298 100%);">
            <div>
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2 text-white" style="font-size: 1.1rem;">
                    <i class="ti ti-bulb" style="font-size: 1.3rem;"></i>
                    Learning Insights &amp; Adaptive Recommendations
                </h5>
                <span class="small text-white-50">
                    Four-factor attempt analysis (Accuracy, Attempts, Consistency, Trend) &amp; targeted SignED learning materials.
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-dark py-1.5 px-2.5 fw-semibold" style="border-radius: 6px; font-size: 0.8rem;">
                    <i class="ti ti-activity me-1 text-primary"></i><?= count($learningInsights) ?> Activit<?= count($learningInsights) === 1 ? 'y' : 'ies' ?> Evaluated
                </span>
                <?php if ($totalReviewAdvised > 0): ?>
                    <span class="badge bg-warning text-dark py-1.5 px-2.5 fw-semibold" style="border-radius: 6px; font-size: 0.8rem;">
                        <i class="ti ti-alert-triangle me-1"></i><?= $totalReviewAdvised ?> Review Advised
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-3 bg-light border-bottom">
            <div class="d-flex align-items-start gap-2 text-muted small" style="line-height: 1.45;">
                <i class="ti ti-shield-check text-primary mt-0.5" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                <div>
                    <strong class="text-dark">SPED Teacher Advisory Protocol:</strong>
                    Observations and suggested materials are generated strictly from evaluated learner attempts for teacher review.
                    The SPED teacher exercises independent clinical judgement to determine IEP objective mastery, record official evaluations, and assign instructional interventions.
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <?php if (empty($learningInsights)): ?>
        <div class="card border-0 shadow-sm p-4 text-center my-3 bg-white" style="border-radius: 8px;">
            <div class="my-3">
                <i class="ti ti-chart-line text-muted opacity-50" style="font-size: 3rem;"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">No Activity Attempt Records Found</h6>
            <p class="text-muted small mb-0 mx-auto" style="max-width: 520px;">
                This learner has not yet completed interactive activities or submitted worksheet evaluations for this IEP.
                When the learner attempts learning exercises, four-factor learning gap trends and targeted FSL/remedial recommendations will appear here automatically.
            </p>
        </div>
    <?php else: ?>
        
        <!-- Activity Insight Cards List -->
        <div class="d-flex flex-column gap-3 mb-4">
            <?php foreach ($learningInsights as $idx => $ins): ?>
                <?php
                $act      = $ins['activity'] ?? [];
                $actId    = (int)($act['id'] ?? 0);
                $summary  = $ins['performance_summary'] ?? [];
                $factors  = $ins['factors'] ?? [];
                $accuracy = $factors['accuracy'] ?? [];
                $attempts = $factors['attempts'] ?? [];
                $trend    = $factors['performance_trend'] ?? [];
                $consist  = $factors['consistency'] ?? [];
                $recs     = $ins['recommendations'] ?? [];
                $iepAlign = $ins['iep_alignment'] ?? [];
                $limits   = $ins['limitations'] ?? [];

                $trendClass = $trend['classification'] ?? 'Insufficient Evidence';
                $trendBadgeColor = match($trendClass) {
                    'Improving' => 'success',
                    'Stable'    => 'info',
                    'Declining' => 'danger',
                    default     => 'secondary'
                };
                $trendIcon = match($trendClass) {
                    'Improving' => 'ti-trending-up',
                    'Stable'    => 'ti-arrows-horizontal',
                    'Declining' => 'ti-trending-down',
                    default     => 'ti-help'
                };

                $consistClass = $consist['classification'] ?? 'Insufficient Evidence';
                $consistBadgeColor = match($consistClass) {
                    'Stable'       => 'info',
                    'Inconsistent' => 'warning',
                    default        => 'secondary'
                };

                $latestAcc = $summary['accuracy'] ?? null;
                $accColor = ($latestAcc === null) ? 'secondary' : ($latestAcc >= 75 ? 'success' : ($latestAcc >= 50 ? 'warning' : 'danger'));

                $activityTypeLabels = [
                    'multiple_choice' => 'Multiple Choice',
                    'true_false'      => 'True / False',
                    'fill_in_blanks'  => 'Fill in the Blanks',
                    'drag_drop_sort'  => 'Drag & Drop Sort',
                    'sequencing'      => 'Sequencing',
                    'matching'        => 'Matching Pairs',
                    'flashcards'      => 'Interactive Flashcards',
                    'custom_printable'=> 'Printed Worksheet'
                ];
                $typeLabel = $activityTypeLabels[$act['type'] ?? ''] ?? ucwords(str_replace('_', ' ', $act['type'] ?? 'Activity'));
                ?>

                <div class="card border shadow-sm" style="border-radius: 8px; overflow: hidden;" id="activity-insight-card-<?= $actId ?>">
                    
                    <!-- Activity Card Header -->
                    <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark border px-2 py-1 fw-semibold" style="font-size: 0.75rem;">
                                <?= htmlspecialchars($typeLabel) ?>
                            </span>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">
                                <?= htmlspecialchars($act['title'] ?? 'Activity #' . $actId) ?>
                            </h6>
                            <span class="text-muted small" style="font-size: 0.78rem;">
                                (Act ID: #<?= $actId ?> &bull; Lesson Plan #<?= (int)($act['lesson_plan_id'] ?? 0) ?>)
                            </span>
                        </div>

                        <!-- Top Status Badges -->
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="badge bg-<?= $accColor ?>-subtle text-<?= $accColor ?> border border-<?= $accColor ?> px-2 py-1" style="font-size: 0.75rem;">
                                <i class="ti ti-target me-1"></i>Latest: <?= $latestAcc !== null ? round($latestAcc, 1) . '%' : 'Ungraded' ?>
                            </span>
                            <span class="badge bg-<?= $trendBadgeColor ?>-subtle text-<?= $trendBadgeColor ?> border border-<?= $trendBadgeColor ?> px-2 py-1" style="font-size: 0.75rem;">
                                <i class="ti <?= $trendIcon ?> me-1"></i>Trend: <?= htmlspecialchars($trendClass) ?>
                            </span>
                            <span class="badge bg-<?= $consistBadgeColor ?>-subtle text-<?= $consistBadgeColor ?> border border-<?= $consistBadgeColor ?> px-2 py-1" style="font-size: 0.75rem;">
                                <i class="ti ti-activity me-1"></i>Consistency: <?= htmlspecialchars($consistClass) ?>
                            </span>
                            <?php if (!empty($summary['teacher_review_required'])): ?>
                                <span class="badge bg-warning text-dark px-2 py-1 fw-semibold" style="font-size: 0.75rem;">
                                    <i class="ti ti-alert-triangle me-1"></i>Review Advised
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        
                        <!-- 1. Associated IEP Objective & Teacher-Defined Indicator -->
                        <div class="mb-3 p-2.5 rounded-3 bg-light border">
                            <?php if (!empty($iepAlign['has_verified_iep']) && !empty($iepAlign['objectives'])): ?>
                                <?php $primaryIep = reset($iepAlign['objectives']); ?>
                                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-1">
                                    <span class="badge bg-success text-white px-2 py-1 fw-semibold" style="font-size: 0.72rem;">
                                        <i class="ti ti-check me-1"></i>Verified IEP Objective Link &bull; Step #<?= (int)($primaryIep['step_number'] ?? 1) ?>
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5" style="font-size: 0.72rem;">
                                        Domain: <?= htmlspecialchars($primaryIep['domain'] ?? 'SPED Domain') ?>
                                    </span>
                                </div>
                                <div class="small text-dark fw-semibold mb-1" style="font-size: 0.85rem;">
                                    <i class="ti ti-target text-primary me-1"></i><?= htmlspecialchars($primaryIep['objective'] ?? 'IEP Objective') ?>
                                </div>
                                <?php if (!empty($primaryIep['indicator'])): ?>
                                    <div class="small text-muted" style="font-size: 0.8rem;">
                                        <strong>Teacher-Defined Indicator:</strong> <?= htmlspecialchars($primaryIep['indicator']) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="ti ti-link-off me-1"></i>Curriculum Activity (Unlinked to IEP Step)
                                    </span>
                                    <span class="small text-muted" style="font-size: 0.8rem;">
                                        Evaluated against general curriculum competencies. Not currently tied to an individualized IEP objective.
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 2. Four-Factor Performance Summary Grid -->
                        <div class="row g-2 mb-3">
                            <!-- Accuracy -->
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 border bg-white h-100">
                                    <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">
                                        <i class="ti ti-percentage text-primary me-1"></i>Accuracy
                                    </div>
                                    <div class="fs-5 fw-bold text-<?= $accColor ?> mt-1">
                                        <?= $latestAcc !== null ? round($latestAcc, 1) . '%' : 'Ungraded' ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        Contextual threshold: 70%
                                    </div>
                                </div>
                            </div>
                            <!-- Attempts -->
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 border bg-white h-100">
                                    <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">
                                        <i class="ti ti-history text-primary me-1"></i>Attempts
                                    </div>
                                    <div class="fs-5 fw-bold text-dark mt-1">
                                        <?= (int)($attempts['total_recorded'] ?? 0) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        <?= (int)($attempts['graded'] ?? 0) ?> graded &bull; <?= (int)($attempts['comparable'] ?? 0) ?> comparable
                                    </div>
                                </div>
                            </div>
                            <!-- Consistency -->
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 border bg-white h-100">
                                    <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">
                                        <i class="ti ti-wave-sine text-primary me-1"></i>Consistency
                                    </div>
                                    <div class="fs-6 fw-bold text-<?= $consistBadgeColor ?> mt-1">
                                        <?= htmlspecialchars($consistClass) ?>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 0.72rem;" title="<?= htmlspecialchars($consist['reason'] ?? '') ?>">
                                        <?= htmlspecialchars($consist['reason'] ?? 'Evaluated across attempts') ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Performance Trend -->
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 border bg-white h-100">
                                    <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">
                                        <i class="ti <?= $trendIcon ?> text-<?= $trendBadgeColor ?> me-1"></i>Performance Trend
                                    </div>
                                    <div class="fs-6 fw-bold text-<?= $trendBadgeColor ?> mt-1">
                                        <?= htmlspecialchars($trendClass) ?>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 0.72rem;" title="<?= htmlspecialchars($trend['reason'] ?? '') ?>">
                                        <?= htmlspecialchars($trend['reason'] ?? 'Evaluated trend') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Expandable Attempt History, Question-Level Diagnostics & Limitations -->
                        <div class="mb-3">
                            <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5"
                                    type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse-details-<?= $actId ?>"
                                    aria-expanded="false" aria-controls="collapse-details-<?= $actId ?>"
                                    style="border-radius: 6px; font-size: 0.8rem; height: 32px; font-weight: 500;">
                                <i class="ti ti-list-details"></i>
                                View Attempt History &amp; Diagnostics
                                <i class="ti ti-chevron-down ms-1" style="font-size: 0.75rem;"></i>
                            </button>

                            <div class="collapse mt-2.5" id="collapse-details-<?= $actId ?>">
                                <div class="p-3 border rounded-3 bg-light">
                                    
                                    <!-- Attempt History Table -->
                                    <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.85rem;">
                                        <i class="ti ti-calendar-time me-1 text-primary"></i>Attempt History Log
                                    </h6>
                                    <?php $history = $accuracy['accuracy_history'] ?? []; ?>
                                    <?php if (!empty($history)): ?>
                                        <div class="table-responsive bg-white rounded border mb-3">
                                            <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-2">Attempt #</th>
                                                        <th>Raw Score</th>
                                                        <th>Accuracy</th>
                                                        <th>Status</th>
                                                        <th>Comparable</th>
                                                        <th class="pe-2">Timestamp</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($history as $h): ?>
                                                        <tr>
                                                            <td class="ps-2 fw-semibold">Attempt <?= (int)$h['attempt_number'] ?></td>
                                                            <td>
                                                                <?= $h['score'] !== null ? $h['score'] . ' / ' . $h['max_score'] : '<span class="text-muted">N/A</span>' ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($h['accuracy'] !== null): ?>
                                                                    <span class="fw-semibold text-<?= (float)$h['accuracy'] >= 75 ? 'success' : ((float)$h['accuracy'] >= 50 ? 'warning' : 'danger') ?>">
                                                                        <?= round((float)$h['accuracy'], 1) ?>%
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Ungraded</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?= !empty($h['graded']) ? '<span class="badge bg-success-subtle text-success">Graded</span>' : '<span class="badge bg-secondary-subtle text-secondary">Ungraded</span>' ?>
                                                            </td>
                                                            <td>
                                                                <?= !empty($h['comparable']) ? '<span class="text-success"><i class="ti ti-check"></i> Yes</span>' : '<span class="text-muted"><i class="ti ti-minus"></i> No</span>' ?>
                                                            </td>
                                                            <td class="pe-2 text-muted"><?= htmlspecialchars($h['attempted_at'] ?? 'N/A') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small mb-2">No historical attempt records available.</p>
                                    <?php endif; ?>

                                    <!-- Question-Level Recurring Errors & Resolved Items -->
                                    <div class="row g-2 mb-3">
                                        <!-- Recurring Errors -->
                                        <div class="col-md-6">
                                            <div class="p-2.5 bg-white border rounded h-100">
                                                <div class="fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1">
                                                    <i class="ti ti-alert-circle text-danger"></i>
                                                    Recurring Incorrect Items
                                                </div>
                                                <?php $recurring = $consist['recurring_incorrect_items'] ?? []; ?>
                                                <?php if (!empty($recurring)): ?>
                                                    <ul class="list-unstyled mb-0 small" style="font-size: 0.78rem;">
                                                        <?php foreach ($recurring as $recItem): ?>
                                                            <li class="mb-1 pb-1 border-bottom border-light">
                                                                <strong>Q<?= (int)$recItem['question_index'] + 1 ?>:</strong> Expected <em>"<?= htmlspecialchars($recItem['expected']) ?>"</em>
                                                                &bull; Missed in <?= (int)$recItem['incorrect_in_attempts'] ?> attempts
                                                                <?php if (!empty($recItem['still_incorrect_latest'])): ?>
                                                                    <span class="badge bg-danger-subtle text-danger ms-1">Active Gap</span>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        <i class="ti ti-check text-success me-1"></i>No recurring errors detected across attempts.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Resolved Items -->
                                        <div class="col-md-6">
                                            <div class="p-2.5 bg-white border rounded h-100">
                                                <div class="fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1">
                                                    <i class="ti ti-circle-check text-success"></i>
                                                    Resolved Prior Errors
                                                </div>
                                                <?php $resolved = $consist['resolved_items'] ?? []; ?>
                                                <?php if (!empty($resolved)): ?>
                                                    <ul class="list-unstyled mb-0 small" style="font-size: 0.78rem;">
                                                        <?php foreach ($resolved as $resItem): ?>
                                                            <li class="mb-1 text-muted">
                                                                <strong>Q<?= (int)$resItem['question_index'] + 1 ?>:</strong> <em>"<?= htmlspecialchars($resItem['expected']) ?>"</em>
                                                                <span class="badge bg-success-subtle text-success ms-1">Resolved on Repeat Attempt</span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        No prior errors recorded or awaiting repeat attempt.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Limitations & Guardrails -->
                                    <?php if (!empty($limits)): ?>
                                        <div class="p-2 rounded bg-white border small text-muted" style="font-size: 0.75rem;">
                                            <strong class="text-dark d-block mb-1"><i class="ti ti-info-circle text-primary me-1"></i>Data Reliability &amp; Analysis Limitations:</strong>
                                            <ul class="mb-0 ps-3">
                                                <?php foreach ($limits as $lim): ?>
                                                    <li><?= htmlspecialchars($lim) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                        <!-- 4. Recommended Resources Section -->
                        <div class="pt-2 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5" style="font-size: 0.88rem;">
                                    <i class="ti ti-bookmarks text-primary"></i>
                                    Recommended Resources for Teacher Review
                                </h6>
                                <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.72rem;">
                                    <?= count($recs) ?> Suggested Material<?= count($recs) === 1 ? '' : 's' ?>
                                </span>
                            </div>

                            <?php if (empty($recs)): ?>
                                <div class="alert alert-light border small text-muted mb-0 py-2.5 px-3 d-flex align-items-center gap-2" style="font-size: 0.8rem; border-radius: 6px;">
                                    <i class="ti ti-info-circle text-primary" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                                    <div><?= htmlspecialchars($ins['explanation'] ?? 'No remedial resources required based on current attempt evidence.') ?></div>
                                </div>
                            <?php else: ?>
                                
                                <div class="d-flex flex-column gap-2" id="recs-list-<?= $actId ?>">
                                    <?php foreach ($recs as $rIdx => $rec): ?>
                                        <?php
                                        $recDomId = 'rec-item-' . $actId . '-' . $rIdx;
                                        $rType = $rec['resource_type'] ?? '';
                                        $typeBadge = match($rType) {
                                            'fsl_vocabulary'        => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="ti ti-hand-stop me-1"></i>FSL Sign Video</span>',
                                            'resource_hub_pack'     => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="ti ti-package me-1"></i>Accessibility Pack</span>',
                                            'remedial_lesson'       => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="ti ti-refresh me-1"></i>Remedial Module</span>',
                                            'supplementary_activity'=> '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="ti ti-activity me-1"></i>Practice Activity</span>',
                                            default                 => '<span class="badge bg-light text-dark border px-2 py-1">Resource</span>'
                                        };

                                        $isHighPriority = ($rec['priority'] ?? '') === 'high';
                                        $evidence = $rec['evidence'] ?? [];
                                        ?>

                                        <div class="card bg-white border rounded-3 p-3 transition-all" id="<?= $recDomId ?>" style="border-radius: 8px;">
                                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <?= $typeBadge ?>
                                                    <?php if ($isHighPriority): ?>
                                                        <span class="badge bg-danger text-white px-2 py-1" style="font-size: 0.72rem;">High Priority</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5" style="font-size: 0.72rem;">Medium Priority</span>
                                                    <?php endif; ?>
                                                    <span class="text-muted small" style="font-size: 0.78rem;">
                                                        <?= htmlspecialchars($rec['category'] ?? ($rec['domain'] ?? 'General')) ?>
                                                    </span>
                                                </div>

                                                <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                                    <i class="ti ti-shield-check me-1"></i>Advisory Consideration Only
                                                </span>
                                            </div>

                                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;">
                                                <?= htmlspecialchars($rec['title']) ?>
                                            </h6>
                                            <?php if (!empty($rec['description'])): ?>
                                                <p class="text-muted small mb-2" style="font-size: 0.8rem;">
                                                    <?= htmlspecialchars($rec['description']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- Evidence Box -->
                                            <div class="p-2.5 rounded bg-light border small mb-2.5" style="font-size: 0.78rem; line-height: 1.45;">
                                                <div class="mb-1 text-dark">
                                                    <strong>Rationale:</strong> <?= htmlspecialchars($evidence['performance_evidence'] ?? 'Addresses identified gap.') ?>
                                                </div>
                                                <?php if (!empty($evidence['relevant_iep_objective'])): ?>
                                                    <div class="mb-1 text-muted">
                                                        <strong>Aligned IEP Objective:</strong> <?= htmlspecialchars($evidence['relevant_iep_objective']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="text-muted" style="font-size: 0.72rem;">
                                                    <strong>Verified Reference:</strong> <code><?= htmlspecialchars($evidence['verified_source_reference'] ?? 'Repository') ?></code>
                                                </div>
                                            </div>

                                            <!-- Teacher Review & Preview Controls (Clean & Simple Buttons) -->
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1 border-top">
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    
                                                    <!-- Preview Button -->
                                                    <?php if ($rType === 'fsl_vocabulary'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                                style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 32px;"
                                                                onclick="previewFslResource('<?= htmlspecialchars(addslashes($evidence['related_concept_or_skill'] ?? $rec['title'])) ?>', '<?= htmlspecialchars(addslashes($rec['media_url'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($rec['category'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($rec['description'] ?? '')) ?>')">
                                                            <i class="ti ti-player-play"></i>Preview FSL Sign
                                                        </button>
                                                        <a href="<?= htmlspecialchars($basePath) ?>/fsl-dictionary?search=<?= urlencode($evidence['related_concept_or_skill'] ?? $rec['title']) ?>"
                                                           target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1 text-muted"
                                                           style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 32px;">
                                                            <i class="ti ti-book"></i>Dictionary Entry
                                                        </a>
                                                    <?php elseif ($rType === 'resource_hub_pack'): ?>
                                                        <a href="<?= htmlspecialchars($basePath) ?>/iep/implementation/resource-hub/preview/<?= (int)$rec['source_id'] ?>"
                                                           target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                           style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 32px;">
                                                            <i class="ti ti-eye"></i>Preview Accessibility Pack
                                                        </a>
                                                    <?php elseif ($rType === 'remedial_lesson'): ?>
                                                        <a href="<?= htmlspecialchars($basePath) ?>/learning/lesson/<?= (int)$rec['source_id'] ?>"
                                                           target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                           style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 32px;">
                                                            <i class="ti ti-book"></i>Open Remedial Module
                                                        </a>
                                                    <?php elseif ($rType === 'supplementary_activity'): ?>
                                                        <a href="<?= htmlspecialchars($basePath) ?>/learning/activity/<?= (int)$rec['source_id'] ?>"
                                                           target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                           style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 32px;">
                                                            <i class="ti ti-activity"></i>View Practice Activity
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Teacher Consideration Badge (No fake auto-assign) -->
                                                    <span class="text-muted small ps-1" style="font-size: 0.75rem;">
                                                        <i class="ti ti-info-circle me-1"></i>Available for teacher manual inclusion
                                                    </span>
                                                </div>

                                                <!-- Dismiss Suggestion Button (Client-side view filter) -->
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                                            style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 32px;"
                                                            onclick="dismissRecommendation('<?= $recDomId ?>', '<?= htmlspecialchars(addslashes($rec['title'])) ?>')">
                                                        <i class="ti ti-x"></i>Dismiss
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<!-- Client-side Scripts for FSL Preview and UI Dismissal -->
<script>
function previewFslResource(word, mediaUrl, category, desc) {
    if (typeof window.openFSLModal === 'function') {
        window.openFSLModal(word, {
            video: mediaUrl,
            category: category || 'FSL Sign',
            desc: desc || ('Filipino Sign Language demonstration for ' + word)
        });
    } else if (mediaUrl) {
        window.open('<?= htmlspecialchars($basePath) ?>/' + mediaUrl.replace(/^\//, ''), '_blank');
    } else {
        window.open('<?= htmlspecialchars($basePath) ?>/fsl-dictionary?search=' + encodeURIComponent(word), '_blank');
    }
}

function dismissRecommendation(elementId, title) {
    const el = document.getElementById(elementId);
    if (!el) return;

    // Smooth client-side dismiss with explicit transparency alert
    el.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateY(-6px)';
    
    setTimeout(() => {
        el.outerHTML = `
            <div class="alert alert-secondary py-2 px-3 small d-flex align-items-center justify-content-between mb-2" style="border-radius: 6px; font-size: 0.78rem;">
                <span>
                    <i class="ti ti-check me-1 text-success"></i>
                    Suggested resource <strong>"${title}"</strong> was dismissed for this review session.
                    <span class="text-muted">(View-only dismissal &bull; no permanent database records modified).</span>
                </span>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="location.reload()" title="Reload insights">
                    <i class="ti ti-reload"></i>
                </button>
            </div>
        `;
    }, 250);
}
</script>
