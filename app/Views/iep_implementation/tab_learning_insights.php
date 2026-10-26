<?php
// Part of: SignED — IEP Implementation Workspace (Learning Insights & Support)
// Simplified teacher-friendly interface: Learning Result → Suggested Support → Teacher Review
//
// Guaranteed:
//   - Three clean sections: Section A (Learning Results), Section B (Suggested Materials), Section C (Learning History).
//   - Non-technical, plain-language teacher observations.
//   - Zero clinical/diagnostic jargon; no automatic IEP updates or auto-assignments.
//   - Safe de-identified AI explanations with fallback transparency.

$learningInsights = $learningInsights ?? [];
$studentId = (int)($studentId ?? ($iep['student_id'] ?? 0));
$basePath  = $basePath ?? '';

// Evidence-based plain-language observation helper
if (!function_exists('getEvidenceBasedObservation')) {
    function getEvidenceBasedObservation(array $summary, array $factors): string {
        $attempts      = $factors['attempts'] ?? [];
        $consist       = $factors['consistency'] ?? [];
        $trend         = $factors['performance_trend'] ?? [];
        
        $totalRecorded = (int)($attempts['total_recorded'] ?? 0);
        $gradedCount   = (int)($attempts['graded'] ?? ($summary['graded_count'] ?? 0));
        $latestAcc     = $summary['accuracy'] ?? null;
        
        $recurring     = $consist['recurring_incorrect_items'] ?? [];
        $activeRec     = array_filter($recurring, fn($i) => !empty($i['still_incorrect_latest']));
        $activeCount   = count($activeRec);
        
        $trendClass    = $trend['classification'] ?? 'Insufficient Evidence';
        $consistClass  = $consist['classification'] ?? 'Insufficient Evidence';

        // 1. No graded attempts
        if ($gradedCount === 0) {
            return 'No graded attempts recorded yet. A completed attempt is needed to evaluate performance.';
        }

        // 2. Single attempt completed
        // Rule: Do not call something a repeated difficulty based on one attempt; do not label as Stable.
        if ($gradedCount === 1) {
            if ($latestAcc !== null && $latestAcc < 60) {
                return '1 attempt completed with some incorrect answers. More attempts are needed to identify a learning pattern.';
            }
            return '1 attempt completed. More attempts are needed to identify a learning pattern.';
        }

        // 3. Multiple attempts with active recurring mistakes
        if ($activeCount > 0) {
            $itemLabel = ($activeCount === 1) ? 'Some questions were' : 'Some items were';
            return "{$itemLabel} answered incorrectly across multiple attempts. Additional practice may help.";
        }

        // 4. Multiple attempts with resolved prior mistakes
        $resolved = $consist['resolved_items'] ?? [];
        if (!empty($resolved) && $activeCount === 0) {
            return 'Prior mistakes were corrected in repeat attempts. Learner shows good recovery on practiced items.';
        }

        // 5. Score progression trend
        if ($trendClass === 'Improving') {
            return 'Score improved across recent attempts. Continued practice is encouraged.';
        }

        if ($trendClass === 'Declining') {
            return 'Score decreased across recent attempts. Teacher review recommended to check difficulty.';
        }

        if ($consistClass === 'Inconsistent' || $consistClass === 'Variable') {
            return 'Scores varied across attempts. Additional practice may help build consistent performance.';
        }

        if ($trendClass === 'Stable') {
            return 'Score remained consistent across completed attempts.';
        }

        return 'More results needed across comparable attempts to identify a learning trend.';
    }
}

// Activity type human labels
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

require_once __DIR__ . '/../../Helpers/CSRFHelper.php';
$insightsCsrfToken = CSRFHelper::getToken();
?>

<div class="tab-pane fade" id="tab-learning-insights" role="tabpanel" aria-labelledby="tab-learning-insights-btn">
    
    <input type="hidden" id="learning_insights_csrf" value="<?= htmlspecialchars($insightsCsrfToken) ?>">

    <!-- Top Utility & Diagnostics Bar (Separate from learner-specific explanations) -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 px-3 bg-white border rounded mb-3 shadow-sm" style="font-size: 0.82rem;">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 26px; height: 26px;">
                <i class="ti ti-bulb" style="font-size: 0.95rem;"></i>
            </span>
            <span class="fw-semibold text-dark">Learning Insights &amp; Support</span>
            <span class="text-muted d-none d-md-inline">&bull; Learning Result &rarr; Suggested Support &rarr; Teacher Review</span>
        </div>
        <div>
            <button type="button" 
                    class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                    id="btn-test-gemini-global"
                    onclick="testGeminiSynthetic()"
                    style="border-radius: 6px; font-size: 0.78rem; height: 28px; padding: 0.15rem 0.55rem;"
                    title="Run connection benchmark using synthetic mock data (zero learner data transmitted)">
                <i class="ti ti-plug text-primary"></i>
                <span>Test Gemini Connection (Synthetic)</span>
            </button>
        </div>
    </div>

    <!-- Empty State -->
    <?php if (empty($learningInsights)): ?>
        <div class="card border-0 shadow-sm p-4 text-center my-3 bg-white" style="border-radius: 8px;">
            <div class="my-3">
                <i class="ti ti-chart-line text-muted opacity-50" style="font-size: 2.8rem;"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">No learning results available yet.</h6>
            <p class="text-muted small mb-0 mx-auto" style="max-width: 480px; font-size: 0.84rem;">
                Learning results will appear after the learner completes an activity.
            </p>
        </div>
    <?php else: ?>

        <!-- List of Evaluated Activities -->
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

                $typeLabel = $activityTypeLabels[$act['type'] ?? ''] ?? ucwords(str_replace('_', ' ', $act['type'] ?? 'Activity'));
                
                $latestAcc = $summary['accuracy'] ?? null;
                $scoreColor = ($latestAcc === null) ? 'secondary' : ($latestAcc >= 75 ? 'success' : ($latestAcc >= 50 ? 'warning' : 'danger'));
                $latestScoreText = ($latestAcc !== null) ? round($latestAcc, 1) . '%' : 'Ungraded';

                $attemptsTotal = (int)($attempts['total_recorded'] ?? 0);
                $gradedCount   = (int)($attempts['graded'] ?? ($summary['graded_count'] ?? 0));
                $observation   = getEvidenceBasedObservation($summary, $factors);

                // Simple labels for Section C
                $trendClass = $trend['classification'] ?? 'Insufficient Evidence';
                $progressLabel = match($trendClass) {
                    'Improving' => 'Improving over time',
                    'Declining' => 'Decreasing over time',
                    'Stable'    => 'Steady over time',
                    default     => 'More Results Needed'
                };

                $recurring = $consist['recurring_incorrect_items'] ?? [];
                $activeRecurring = array_filter($recurring, fn($item) => !empty($item['still_incorrect_latest']));
                $activeCount = count($activeRecurring);

                $repeatedMistakesLabel = ($gradedCount < 2) 
                    ? 'More Results Needed' 
                    : ($activeCount > 0 ? "{$activeCount} recurring mistake" . ($activeCount > 1 ? 's' : '') : 'None detected');

                $primaryIep = (!empty($iepAlign['has_verified_iep']) && !empty($iepAlign['objectives'])) 
                    ? reset($iepAlign['objectives']) 
                    : null;
                $hasVerifiedIep = ($primaryIep && !empty($primaryIep['verified']));

                $hasGradedEvidence = $gradedCount > 0;
                ?>

                <!-- Activity Card -->
                <div class="card border rounded-3 bg-white shadow-sm" id="activity-card-<?= $actId ?>" style="border-radius: 8px; overflow: hidden;">
                    
                    <!-- ========================================================
                         SECTION A — Learning Results
                         ======================================================== -->
                    <div class="p-3 p-md-3.5">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 0.74rem;">
                                        <?= htmlspecialchars($typeLabel) ?>
                                    </span>
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.96rem;">
                                        <?= htmlspecialchars($act['title'] ?? 'Activity #' . $actId) ?>
                                    </h6>
                                </div>
                                <div class="text-muted small mb-1" style="font-size: 0.82rem;">
                                    <strong>Latest Result:</strong> <span class="fw-semibold text-<?= $scoreColor ?>"><?= $latestScoreText ?></span>
                                    <span class="mx-1 text-secondary opacity-50">&bull;</span>
                                    <strong>Completed:</strong> <?= $attemptsTotal ?> <?= $attemptsTotal === 1 ? 'time' : 'times' ?><?= $gradedCount !== $attemptsTotal ? " ({$gradedCount} graded)" : '' ?>
                                </div>
                                <div class="small fst-italic text-dark mt-0.5" style="font-size: 0.84rem; color: #2d3748; line-height: 1.45;">
                                    &ldquo;<?= htmlspecialchars($observation) ?>&rdquo;
                                </div>
                            </div>

                            <div class="flex-shrink-0 align-self-start align-self-md-center pt-1 pt-md-0">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5"
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#review-details-<?= $actId ?>" 
                                        aria-expanded="false" 
                                        aria-controls="review-details-<?= $actId ?>"
                                        id="btn-review-activity-<?= $actId ?>"
                                        style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 32px;">
                                    <i class="ti ti-notes"></i>
                                    <span>Review Activity</span>
                                    <i class="ti ti-chevron-down ms-0.5" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Review Details (Section B & Section C) -->
                    <div class="collapse border-top bg-light" id="review-details-<?= $actId ?>">
                        <div class="p-3 p-md-3.5 d-flex flex-column gap-3">
                            
                            <!-- ========================================================
                                 SECTION B — Suggested Learning Materials
                                 ======================================================== -->
                            <div class="bg-white border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <i class="ti ti-bookmarks text-primary"></i>
                                        <strong class="text-dark small" style="font-size: 0.88rem;">Suggested Learning Materials</strong>
                                    </div>
                                    <?php if (!empty($recs)): ?>
                                        <span class="text-muted small" style="font-size: 0.74rem;">
                                            <?= count($recs) ?> material<?= count($recs) > 1 ? 's' : '' ?> available for teacher review
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (empty($recs)): ?>
                                    <div class="p-2.5 rounded bg-light border text-muted small" style="font-size: 0.8rem;">
                                        <i class="ti ti-info-circle text-secondary me-1"></i>
                                        No matching learning material is available yet. The teacher may choose another appropriate activity or resource.
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-2" id="recs-list-<?= $actId ?>">
                                        <?php foreach ($recs as $rIdx => $rec): ?>
                                            <?php
                                            $recDomId = 'rec-item-' . $actId . '-' . $rIdx;
                                            $rType = $rec['resource_type'] ?? '';
                                            $typeLabel = match($rType) {
                                                'fsl_vocabulary'        => 'FSL Video',
                                                'resource_hub_pack'     => 'Resource Hub Pack',
                                                'remedial_lesson'       => 'Remedial Lesson',
                                                'supplementary_activity'=> 'Practice Activity',
                                                default                 => 'Learning Resource'
                                            };
                                            $evidence = $rec['evidence'] ?? [];
                                            $relevance = $evidence['performance_evidence'] ?? $rec['description'] ?? 'Relevant learning material for practice.';
                                            ?>
                                            <div class="p-2.5 border rounded bg-white" id="<?= $recDomId ?>">
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                                    <div>
                                                        <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1">
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5" style="font-size: 0.72rem;">
                                                                <?= htmlspecialchars($typeLabel) ?>
                                                            </span>
                                                            <strong class="text-dark small" style="font-size: 0.85rem;">
                                                                <?= htmlspecialchars($rec['title']) ?>
                                                            </strong>
                                                        </div>
                                                        <div class="text-muted small" style="font-size: 0.78rem; line-height: 1.4;">
                                                            <?= htmlspecialchars($relevance) ?>
                                                        </div>
                                                    </div>

                                                    <!-- Preview / Open Action -->
                                                    <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                                        <?php if ($rType === 'fsl_vocabulary'): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                                    style="border-radius: 6px; font-size: 0.8rem; height: 30px;"
                                                                    onclick="previewFslResource('<?= htmlspecialchars(addslashes($evidence['related_concept_or_skill'] ?? $rec['title'])) ?>', '<?= htmlspecialchars(addslashes($rec['media_url'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($rec['category'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($rec['description'] ?? '')) ?>')">
                                                                <i class="ti ti-player-play"></i>Preview
                                                            </button>
                                                            <a href="<?= htmlspecialchars($basePath) ?>/fsl-dictionary?search=<?= urlencode($evidence['related_concept_or_skill'] ?? $rec['title']) ?>"
                                                               target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1 text-muted"
                                                               style="border-radius: 6px; font-size: 0.8rem; height: 30px;">
                                                                <i class="ti ti-book"></i>Dictionary
                                                            </a>
                                                        <?php elseif ($rType === 'resource_hub_pack'): ?>
                                                            <a href="<?= htmlspecialchars($basePath) ?>/iep/implementation/resource-hub/preview/<?= (int)$rec['source_id'] ?>"
                                                               target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                               style="border-radius: 6px; font-size: 0.8rem; height: 30px;">
                                                                <i class="ti ti-eye"></i>Preview Pack
                                                            </a>
                                                        <?php elseif ($rType === 'remedial_lesson'): ?>
                                                            <a href="<?= htmlspecialchars($basePath) ?>/learning/lesson/<?= (int)$rec['source_id'] ?>"
                                                               target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                               style="border-radius: 6px; font-size: 0.8rem; height: 30px;">
                                                                <i class="ti ti-external-link"></i>Open Lesson
                                                            </a>
                                                        <?php elseif ($rType === 'supplementary_activity'): ?>
                                                            <a href="<?= htmlspecialchars($basePath) ?>/learning/activity/<?= (int)$rec['source_id'] ?>"
                                                               target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                               style="border-radius: 6px; font-size: 0.8rem; height: 30px;">
                                                                <i class="ti ti-external-link"></i>Open Activity
                                                            </a>
                                                        <?php endif; ?>

                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                                                style="border-radius: 6px; font-size: 0.78rem; height: 30px; padding: 0.2rem 0.5rem;"
                                                                onclick="dismissRecommendation('<?= $recDomId ?>', '<?= htmlspecialchars(addslashes($rec['title'])) ?>')">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ========================================================
                                 SECTION C — Learning History
                                 ======================================================== -->
                            <div class="bg-white border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <i class="ti ti-history text-primary"></i>
                                        <strong class="text-dark small" style="font-size: 0.88rem;">Learning History &amp; Diagnostics</strong>
                                    </div>
                                </div>

                                <!-- Summary Row with Simple Labels -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-4">
                                        <div class="p-2 rounded bg-light border h-100">
                                            <div class="text-muted small" style="font-size: 0.74rem;">Progress Over Time</div>
                                            <div class="fw-semibold text-dark mt-0.5" style="font-size: 0.84rem;">
                                                <?= htmlspecialchars($progressLabel) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="p-2 rounded bg-light border h-100">
                                            <div class="text-muted small" style="font-size: 0.74rem;">Repeated Mistakes</div>
                                            <div class="fw-semibold text-dark mt-0.5" style="font-size: 0.84rem;">
                                                <?= htmlspecialchars($repeatedMistakesLabel) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="p-2 rounded bg-light border h-100">
                                            <div class="text-muted small" style="font-size: 0.74rem;">IEP Objective Link</div>
                                            <div class="fw-semibold text-dark mt-0.5" style="font-size: 0.84rem;">
                                                <?php if ($hasVerifiedIep): ?>
                                                    Step #<?= (int)($primaryIep['step_number'] ?? 1) ?> (<?= htmlspecialchars($primaryIep['domain'] ?? 'Domain') ?>)
                                                <?php else: ?>
                                                    General learning activity
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- IEP Objective Context (if verified) -->
                                <?php if ($hasVerifiedIep): ?>
                                    <div class="p-2.5 rounded bg-light border mb-3 small" style="font-size: 0.8rem;">
                                        <div class="fw-semibold text-dark mb-0.5">
                                            <i class="ti ti-target text-primary me-1"></i>
                                            IEP Step #<?= (int)($primaryIep['step_number'] ?? 1) ?>: <?= htmlspecialchars($primaryIep['objective'] ?? 'IEP Objective') ?>
                                        </div>
                                        <?php if (!empty($primaryIep['indicator'])): ?>
                                            <div class="text-muted">
                                                <strong>Defined Indicator:</strong> <?= htmlspecialchars($primaryIep['indicator']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-muted fst-italic mt-1" style="font-size: 0.74rem;">
                                            Teacher reminder: Activity scores reflect performance on this specific task; IEP mastery is determined independently by the teacher.
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Attempt History Table -->
                                <?php $history = $accuracy['accuracy_history'] ?? []; ?>
                                <?php if (!empty($history)): ?>
                                    <div class="mb-3">
                                        <div class="text-muted small fw-semibold mb-1" style="font-size: 0.76rem;">
                                            Attempt History Log:
                                        </div>
                                        <div class="table-responsive bg-light rounded border">
                                            <table class="table table-sm table-hover mb-0" style="font-size: 0.78rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-2">Times Completed</th>
                                                        <th>Raw Score</th>
                                                        <th>Score</th>
                                                        <th>Status</th>
                                                        <th class="pe-2">Date Completed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($history as $h): ?>
                                                        <tr>
                                                            <td class="ps-2 fw-semibold">Attempt <?= (int)$h['attempt_number'] ?></td>
                                                            <td><?= $h['score'] !== null ? $h['score'] . ' / ' . $h['max_score'] : '<span class="text-muted">N/A</span>' ?></td>
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
                                                            <td class="pe-2 text-muted"><?= htmlspecialchars($h['attempted_at'] ?? 'N/A') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Mistakes Detail (if recurring or resolved) -->
                                <?php if (!empty($recurring) || !empty($consist['resolved_items'])): ?>
                                    <div class="p-2.5 rounded bg-light border mb-3 small" style="font-size: 0.78rem;">
                                        <?php if (!empty($recurring)): ?>
                                            <div class="fw-semibold text-dark mb-1">
                                                <i class="ti ti-alert-circle text-danger me-1"></i>Repeated Mistakes:
                                            </div>
                                            <ul class="mb-2 ps-3">
                                                <?php foreach ($recurring as $recItem): ?>
                                                    <li>
                                                        <strong>Question <?= (int)$recItem['question_index'] + 1 ?>:</strong> Expected <em>"<?= htmlspecialchars($recItem['expected']) ?>"</em>
                                                        (missed in <?= (int)$recItem['incorrect_in_attempts'] ?> attempts)
                                                        <?php if (!empty($recItem['still_incorrect_latest'])): ?>
                                                            <span class="badge bg-danger-subtle text-danger ms-1">Active</span>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <?php if (!empty($consist['resolved_items'])): ?>
                                            <div class="fw-semibold text-dark mb-1">
                                                <i class="ti ti-circle-check text-success me-1"></i>Resolved on Repeat Attempt:
                                            </div>
                                            <ul class="mb-0 ps-3 text-muted">
                                                <?php foreach ($consist['resolved_items'] as $resItem): ?>
                                                    <li>Question <?= (int)$resItem['question_index'] + 1 ?> (<em>"<?= htmlspecialchars($resItem['expected']) ?>"</em>) corrected in subsequent attempt.</li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- AI Explanation Block ("Explain Learning Results") -->
                                <div class="p-2.5 rounded border bg-light" id="ai-box-<?= $actId ?>">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-1.5">
                                            <span class="badge px-2 py-0.5 fw-semibold" style="background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; font-size: 0.72rem;">
                                                <i class="ti ti-sparkles me-1"></i>AI Assistant
                                            </span>
                                            <strong class="text-dark small" style="font-size: 0.82rem;">Explain Learning Results</strong>
                                            <span class="text-muted" style="font-size: 0.7rem;">(De-identified)</span>
                                        </div>
                                        <div>
                                            <?php if ($hasGradedEvidence): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 btn-ai-explain"
                                                        id="btn-ai-explain-<?= $actId ?>"
                                                        style="border-radius: 6px; font-size: 0.8rem; font-weight: 500; height: 30px;"
                                                        onclick="generateAiExplanation(<?= $actId ?>, <?= (int)$studentId ?>)">
                                                    <i class="ti ti-sparkles"></i>
                                                    <span class="btn-text">Explain Learning Results</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 opacity-50"
                                                        disabled
                                                        style="border-radius: 6px; font-size: 0.8rem; font-weight: 500; height: 30px; cursor: not-allowed;"
                                                        title="Completed graded attempts are required to explain learning results">
                                                    <i class="ti ti-lock"></i>
                                                    <span>Explain Learning Results</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- AI Output Container -->
                                    <div id="ai-explanation-display-<?= $actId ?>" class="small text-muted" style="font-size: 0.8rem; line-height: 1.45;">
                                        <?php if ($hasGradedEvidence): ?>
                                            Click <strong>Explain Learning Results</strong> to generate a concise summary of the learner's attempts and support suggestions.
                                        <?php else: ?>
                                            No graded attempt evidence recorded yet for this activity.
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Teacher Next Steps Advisory -->
                                <div class="text-muted small mt-2 d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                    <i class="ti ti-info-circle text-primary flex-shrink-0"></i>
                                    <span>Teacher decision: Suggested materials are advisory and are not auto-assigned. Review results and determine whether additional practice or observation is appropriate.</span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<!-- Client-side Scripts for FSL Preview, Dismissal, and AI Narration Handlers -->
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

    el.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateY(-4px)';
    
    setTimeout(() => {
        el.outerHTML = `
            <div class="alert alert-secondary py-1.5 px-2.5 small d-flex align-items-center justify-content-between mb-2" style="border-radius: 6px; font-size: 0.76rem;">
                <span>
                    <i class="ti ti-check me-1 text-success"></i>
                    Material "${escapeHtml(title)}" hidden for this session.
                </span>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="location.reload()" title="Restore">
                    <i class="ti ti-reload"></i>
                </button>
            </div>
        `;
    }, 200);
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function generateAiExplanation(activityId, studentId) {
    const btn = document.getElementById('btn-ai-explain-' + activityId);
    const display = document.getElementById('ai-explanation-display-' + activityId);
    if (!btn || !display) return;

    const originalBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span><span style="font-size:0.78rem;">Explaining...</span>';

    display.innerHTML = `
        <div class="p-2.5 rounded bg-white border text-center text-muted">
            <span class="spinner-border spinner-border-sm text-primary me-2" role="status"></span>
            Evaluating attempt results and generating plain-language explanation...
        </div>
    `;

    const csrfToken = document.getElementById('learning_insights_csrf')?.value 
                   || document.querySelector('input[name="_csrf_token"]')?.value 
                   || '';

    fetch('<?= htmlspecialchars($basePath) ?>/iep/implementation/explain-insight', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            activity_id: activityId,
            student_id: studentId,
            _csrf_token: csrfToken
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
        renderAiExplanation(activityId, data, studentId);
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
        display.innerHTML = `
            <div class="alert alert-danger py-2 px-3 small mb-0" style="border-radius: 6px;">
                <i class="ti ti-alert-triangle me-1"></i> Failed to communicate with server: ${escapeHtml(err.message || 'Unknown network error')}
            </div>
        `;
    });
}

function testGeminiSynthetic() {
    const btn = document.getElementById('btn-test-gemini-global');
    const modalEl = document.getElementById('modalAiTestResult');
    const modalBody = document.getElementById('modalAiTestResultBody');
    if (!modalEl || !modalBody) return;

    let originalHtml = '';
    if (btn) {
        originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Testing...';
    }

    modalBody.innerHTML = `
        <div class="text-center py-4 text-muted">
            <span class="spinner-border text-primary mb-2" role="status"></span>
            <div>Executing synthetic benchmark Gemini API test...</div>
            <small class="text-muted">Zero learner data transmitted; evaluating synthetic mock payload.</small>
        </div>
    `;

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const csrfToken = document.getElementById('learning_insights_csrf')?.value 
                   || document.querySelector('input[name="_csrf_token"]')?.value 
                   || '';

    fetch('<?= htmlspecialchars($basePath) ?>/iep/implementation/explain-insight', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            is_synthetic: true,
            _csrf_token: csrfToken
        })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        renderAiExplanation('modal-test', data, 0, modalBody, true);
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        modalBody.innerHTML = `
            <div class="alert alert-danger py-2.5 px-3 small mb-0" style="border-radius: 6px;">
                <i class="ti ti-alert-triangle me-1"></i> Gemini connection test failed: ${escapeHtml(err.message || 'Unknown error')}
            </div>
        `;
    });
}

function renderAiExplanation(actId, data, studentId, targetElement = null, isSyntheticTest = false) {
    const display = targetElement || document.getElementById('ai-explanation-display-' + actId);
    if (!display) return;

    if (!data) {
        display.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0" style="border-radius: 6px;"><i class="ti ti-alert-circle me-1"></i> No response data received.</div>';
        return;
    }

    const isSynthetic = Boolean(isSyntheticTest || data.is_synthetic || data.data_mode === 'synthetic_test' || actId === 'modal-test');
    const isSuccess = Boolean(data.success && data.explanation);
    const exp = isSuccess ? data.explanation : (data.fallback || null);

    if (!exp) {
        display.innerHTML = `
            <div class="alert alert-warning py-2 px-3 small mb-0" style="border-radius: 6px;">
                <i class="ti ti-alert-triangle me-1"></i> ${escapeHtml(data.message || 'Unable to generate explanation.')}
            </div>
        `;
        return;
    }

    const summary = exp.performance_summary || '';
    const difficulties = exp.possible_learning_difficulties || exp.skills_needing_support || [];
    const why = exp.why_resources_may_help || '';
    const steps = exp.teacher_next_steps || [];
    const advisory = exp.advisory_note || 'Advisory observation for teacher review only.';
    const isFallback = Boolean(exp.is_fallback || !data.success);
    const modelName = data.model || 'Gemini Flash';

    let html = '';

    if (isSynthetic) {
        html += `
            <div class="alert alert-warning py-2 px-2.5 small mb-2 d-flex align-items-center gap-2 fw-bold text-dark border" style="border-radius: 6px; font-size: 0.8rem; background-color: #fff3cd; color: #664d03; border-color: #ffe69c !important;">
                <i class="ti ti-alert-triangle text-warning flex-shrink-0" style="font-size: 1.1rem;"></i>
                <span>DEMO DATA ONLY — Not an analysis of this learner.</span>
            </div>
        `;
    }

    if (isFallback) {
        html += `
            <div class="alert alert-warning py-1.5 px-2.5 small mb-2 d-flex align-items-center justify-content-between flex-wrap gap-1" style="border-radius: 6px; font-size: 0.76rem;">
                <span>
                    <i class="ti ti-info-circle me-1"></i>
                    <strong>Standard Rule-Based Summary:</strong> ${escapeHtml(data.message || 'Showing standard summary.')}
                </span>
                <span class="badge bg-secondary-subtle text-secondary border">Deterministic Fallback</span>
            </div>
        `;
    } else {
        html += `
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2 pb-1 border-bottom" style="font-size: 0.72rem;">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="badge bg-purple-subtle text-purple border px-1.5 py-0.5" style="background:#f3e8ff; color:#7e22ce; border-color:#e9d5ff;">
                        <i class="ti ti-sparkles me-0.5"></i>Model: ${escapeHtml(modelName)}
                    </span>
                    <span class="badge bg-light text-muted border px-1.5 py-0.5">
                        <i class="ti ti-shield-check me-0.5"></i>De-identified
                    </span>
                </div>
                <span class="text-muted">
                    Generated: ${escapeHtml(data.generated_at || 'Just now')}
                </span>
            </div>
        `;
    }

    html += `
        <div class="p-2 rounded bg-white border mb-2 text-dark small" style="line-height: 1.45; font-size: 0.82rem;">
            <strong>Result Summary:</strong> ${escapeHtml(summary)}
        </div>
    `;

    if (difficulties && difficulties.length > 0) {
        html += `
            <div class="p-2 rounded bg-white border mb-2 text-dark small" style="font-size: 0.8rem;">
                <strong>Concepts Needing Practice:</strong>
                <ul class="mb-0 ps-3 mt-1">
                    ${difficulties.map(d => `<li class="mb-0.5">${escapeHtml(d)}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    if (why) {
        html += `
            <div class="p-2 rounded bg-white border mb-2 text-dark small" style="font-size: 0.8rem;">
                <strong>Why Suggested Materials Help:</strong>
                <div class="mt-0.5">${escapeHtml(why)}</div>
            </div>
        `;
    }

    if (steps && steps.length > 0) {
        html += `
            <div class="p-2 rounded bg-white border mb-2 text-dark small" style="font-size: 0.8rem;">
                <strong>Suggested Next Steps:</strong>
                <ul class="mb-0 ps-3 mt-1">
                    ${steps.map(s => `<li class="mb-0.5">${escapeHtml(s)}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    html += `
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1 text-muted" style="font-size: 0.72rem;">
            <span><i class="ti ti-shield-check text-primary me-1"></i>${escapeHtml(advisory)}</span>
            ${(!isSynthetic && studentId) ? `
                <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none" onclick="generateAiExplanation(${actId}, ${studentId})" title="Regenerate explanation">
                    <i class="ti ti-reload me-1"></i>Regenerate
                </button>
            ` : ''}
        </div>
    `;

    display.innerHTML = html;
}
</script>

<!-- Modal: Synthetic AI Service Test Result -->
<div class="modal fade" id="modalAiTestResult" tabindex="-1" aria-labelledby="modalAiTestResultLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header py-2 px-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a5298 100%); color: white;">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1.5" id="modalAiTestResultLabel" style="font-size: 0.92rem;">
                    <i class="ti ti-activity text-warning"></i>
                    Gemini Connection Diagnostics (Synthetic Benchmark)
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="alert alert-warning border-0 rounded-0 mb-0 py-2 px-3 d-flex align-items-center gap-2 fw-bold text-dark" style="background-color: #fff3cd; color: #664d03; border-bottom: 1px solid #ffecb5 !important; font-size: 0.85rem;">
                <i class="ti ti-alert-triangle text-warning flex-shrink-0" style="font-size: 1.15rem;"></i>
                <span>DEMO DATA ONLY — Not an analysis of this learner.</span>
            </div>

            <div class="modal-body p-3" id="modalAiTestResultBody">
                <!-- Injected via testGeminiSynthetic() -->
            </div>
            <div class="modal-footer py-1.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 0.74rem;">
                    <i class="ti ti-shield-lock me-1"></i>Synthetic benchmark profile &bull; No learner data transmitted
                </span>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"
                        style="border-radius: 6px; font-size: 0.8125rem; font-weight: 500; height: 30px;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
