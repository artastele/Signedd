<?php
// Activity guidance only. Teacher-defined IEP evaluations remain independent.
$learningInsights = $learningInsights ?? [];
$basePath = $basePath ?? '';
require_once __DIR__ . '/../../Helpers/CSRFHelper.php';
require_once __DIR__ . '/../../Services/InterventionDraftService.php';
$insightsCsrfToken = CSRFHelper::getToken();
?>
<div class="tab-pane fade" id="tab-learning-insights" role="tabpanel" aria-labelledby="tab-learning-insights-btn">
    <input type="hidden" id="learning_insights_csrf" value="<?= htmlspecialchars($insightsCsrfToken) ?>">
    <p class="small text-muted">Suggested ratings guide activity review only. The teacher determines final IEP evaluations using teacher-defined indicators. A quiz score does not establish practical FSL sign production.</p>
    <?php if (!$learningInsights): ?>
        <p class="text-muted">No learning results available yet.</p>
    <?php endif; ?>
    <?php foreach ($learningInsights as $ins):
        $act = $ins['activity'] ?? [];
        $actId = (int)($act['id'] ?? 0);
        $score = $ins['performance_summary']['accuracy'] ?? null;
        $rating = $ins['suggested_rating'] ?? null;
        $ratingNames = ['P'=>'Proficient', 'AP'=>'Approaching Proficiency', 'D'=>'Developing', 'B'=>'Beginning'];
        $history = $ins['factors']['accuracy']['accuracy_history'] ?? [];
        $latest = $history ? end($history) : null;
        $incorrectItems = $ins['factors']['accuracy']['latest_incorrect_items'] ?? [];
        $recs = $ins['recommendations'] ?? [];
        $draft = $ins['intervention'] ?? InterventionDraftService::blank();
        $needsAssessment = $score !== null && $score < 75 && empty($draft['actionable']);
    ?>
    <article class="card border rounded-3 p-3 mb-3" id="activity-card-<?= $actId ?>">
        <h6><?= htmlspecialchars($act['title'] ?? 'Activity') ?></h6>
        <div class="small mb-2"><strong>Latest Score:</strong> <?= $score !== null && $latest ? htmlspecialchars($latest['score'] . ' / ' . $latest['max_score'] . ' (' . round($score, 2) . '%)') : 'Ungraded' ?></div>
        <div class="small mb-2"><strong>Suggested Rating:</strong> <?= $rating !== null ? htmlspecialchars($rating . ' (' . ($ratingNames[$rating] ?? '') . ')') : 'No suggested rating' ?></div>
        <div class="small mb-3"><strong>What the result means</strong>
            <?php if ($needsAssessment): ?>
                <p class="mb-1 mt-1"><strong>Needs further assessment</strong></p>
                <p class="mb-0">This score suggests more support may be needed. The system cannot yet identify the exact skill needing practice. You can select a target skill or assess the learner again.</p>
            <?php elseif (!empty($draft['actionable'])): ?>
                <p class="mb-0 mt-1"><?= !empty($draft['skill_verified']) ? 'Practice focus: ' . htmlspecialchars($draft['learning_competency']) . '.' : 'Review the recorded items and select a target skill for practice.' ?></p>
            <?php elseif ($score === null): ?>
                <p class="mb-0 mt-1">A graded result is not available yet. You can browse resources or select a skill for a teacher-planned intervention.</p>
            <?php else: ?>
                <p class="mb-0 mt-1">No specific learning difficulty is confirmed in the latest result. Use your assessment to decide whether further practice is needed.</p>
            <?php endif; ?>
        </div>
        <?php if (!empty($draft['actionable'])): ?>
        <div class="small mb-3">
            <strong><?= $recs ? 'Use Existing Resource' : 'Suggested Resource' ?></strong>
            <?php if (!$recs): ?>
                <p class="text-muted mb-1">No verified resource match yet. Browse resources or create a practice activity.</p>
            <?php endif; ?>
        <?php endif; ?>
            <?php foreach (!empty($draft['actionable']) ? $recs : [] as $rec):
                $url = null;
                if (($rec['resource_type'] ?? '') === 'resource_hub_pack') {
                    $url = '/iep/implementation/resource-hub/preview/' . (int)$rec['source_id'];
                } elseif (($rec['resource_type'] ?? '') === 'fsl_vocabulary') {
                    $url = '/fsl-dictionary?search=' . rawurlencode($rec['lookup_word'] ?? $rec['evidence']['related_concept_or_skill'] ?? '');
                }
                if ($url === null) continue;
            ?>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                    <div>
                        <span><?= htmlspecialchars($rec['title'] ?? '') ?></span>
                        <?php if (isset($rec['access_label'])): ?><span class="badge bg-light text-dark"><?= htmlspecialchars($rec['access_label']) ?></span><?php endif ?>
                        <p class="small text-muted mb-0"><strong>Why it matches:</strong>
                            <?= htmlspecialchars($rec['evidence']['related_concept_or_skill'] ?? '') ?>.
                            <?= htmlspecialchars($rec['evidence']['performance_evidence'] ?? '') ?></p>
                    </div>
                    <a class="btn btn-sm btn-outline-primary rounded" href="<?= htmlspecialchars($basePath . $url) ?>"><?= ($rec['resource_type'] ?? '') === 'resource_hub_pack' ? 'Preview Resource' : 'Browse FSL Resource' ?></a>
                </div>
            <?php endforeach; ?>
        <?php if (!empty($draft['actionable'])): ?></div><?php endif; ?>
        <div class="mb-3">
            <a class="btn btn-sm btn-outline-secondary rounded" href="<?= htmlspecialchars($basePath) ?>/iep/implementation/resource-hub?tab=browse">Browse Existing Resources</a>
        </div>
            <div class="border rounded p-2 mb-3" data-intervention-draft="<?= htmlspecialchars(json_encode($draft), ENT_QUOTES, 'UTF-8') ?>" id="intervention-<?= $actId ?>">
                <strong class="small">Create Intervention</strong>
                <p class="small text-muted mb-2"><?= !empty($draft['requires_target_skill']) ? 'Choose the target skill in the next form. Skill and tags start blank for your review.' : 'Review the prefilled skill and tags in an editable private draft.' ?> Share only when you choose to publish.</p>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openInterventionDraft(<?= $actId ?>, 'builder')">Create with Slide Builder</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openInterventionDraft(<?= $actId ?>, 'upload')">Upload Material</button>
                    <?php if (!empty($draft['actionable']) && !empty($draft['skill_verified'])): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="suggestInterventionIdeas(<?= $actId ?>, <?= (int)($ins['student_id'] ?? 0) ?>, this)">Suggest Intervention Ideas</button>
                    <?php endif; ?>
                </div>
                <div class="mt-2" id="intervention-ideas-<?= $actId ?>" hidden>
                    <label class="small" for="intervention-ideas-text-<?= $actId ?>">Editable teaching ideas</label>
                    <textarea class="form-control form-control-sm" rows="4" id="intervention-ideas-text-<?= $actId ?>" oninput="document.getElementById('intervention-ideas-approved-<?= $actId ?>').checked=false"></textarea>
                    <label class="small"><input type="checkbox" id="intervention-ideas-approved-<?= $actId ?>"> I reviewed and approve these ideas for my draft.</label>
                </div>
                <p class="small text-muted mb-0 mt-1" id="intervention-status-<?= $actId ?>" role="status"></p>
            </div>
        <details id="review-details-<?= $actId ?>">
            <summary class="small text-primary" id="btn-review-activity-<?= $actId ?>">View Evidence Details — Attempt History</summary>
            <p class="small mt-2"><?= htmlspecialchars($ins['observation'] ?? 'More recorded results are needed.') ?></p>
            <?php if (!empty($ins['factors']['accuracy']['ambiguous_log_count'])): ?>
                <p class="small text-muted">Ambiguous historical item evidence was excluded. Existing scores and answers remain unchanged.</p>
            <?php endif; ?>
            <?php if ($incorrectItems): ?>
                <ul class="small mb-3">
                    <?php foreach ($incorrectItems as $item): ?>
                        <li>Review <?= ($item['unit'] ?? '') === 'sequence' ? 'evaluated sequence' : 'item' ?> <?= (int)$item['question_index'] + 1 ?>. Expected answer: <?= htmlspecialchars($item['expected']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p class="small text-muted mt-2">Newer attempts appear here when this tab is refreshed. Changes in performance do not establish that a particular intervention caused improvement.</p>
            <p class="small mt-2 mb-1"><strong>Recorded attempts:</strong> <?= (int)($ins['performance_summary']['attempt_count'] ?? 0) ?>;
                graded: <?= (int)($ins['performance_summary']['graded_count'] ?? 0) ?>;
                comparable: <?= (int)($ins['factors']['attempts']['comparable'] ?? 0) ?>.</p>
            <?php foreach (['performance_trend' => 'Performance trend', 'consistency' => 'Consistency'] as $factor => $label): ?>
                <p class="small mb-1"><strong><?= $label ?>:</strong> <?= htmlspecialchars($ins['factors'][$factor]['reason'] ?? 'Insufficient evidence.') ?></p>
            <?php endforeach; ?>
            <?php foreach ($ins['iep_alignment']['objectives'] ?? [] as $objective): if (empty($objective['verified'])) continue; ?>
                <p class="small mt-2">Teacher-defined indicator: <?= htmlspecialchars($objective['indicator'] ?? $objective['objective'] ?? '') ?></p>
            <?php endforeach; ?>
            <div class="table-responsive mt-2">
                <table class="table table-sm small">
                    <thead><tr><th>Attempt</th><th>Score</th><th>Date</th></tr></thead>
                    <tbody><?php foreach ($history as $h): ?>
                        <tr><td><?= (int)$h['attempt_number'] ?></td><td><?= $h['accuracy'] === null ? 'Ungraded' : htmlspecialchars($h['score'] . ' / ' . $h['max_score'] . ' (' . round($h['accuracy'], 1) . '%)') ?></td><td><?= htmlspecialchars($h['attempted_at'] ?? '') ?></td></tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>
            <?php if (!empty($ins['limitations'])): ?>
                <p class="small text-muted mb-1">Evidence limitations</p>
                <ul class="small text-muted mb-0">
                    <?php foreach ($ins['limitations'] as $limitation): ?>
                        <li><?= htmlspecialchars($limitation) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </details>
    </article>
    <?php endforeach; ?>
</div>
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
