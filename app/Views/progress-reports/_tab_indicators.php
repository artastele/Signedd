<?php
$quarterNumMap = [
    '1st Quarter' => 1,
    '2nd Quarter' => 2,
    '3rd Quarter' => 3,
    '4th Quarter' => 4,
];
$activeQuarterNum = $quarterNumMap[$quarter] ?? 1;
$ratingButtons = [
    'P'  => 'P',
    'AP' => 'AP',
    'D'  => 'D',
    'B'  => 'B',
    'NA' => 'NA',
];

$recData = $recommendations ?? [];
$overallBaseline = $recData['recommended_baseline'] ?? 'P';
$overallAvgScore = $recData['overall_avg'] ?? 0;
$totalGraded = $recData['total_graded'] ?? 0;
$domainRecMap = $recData['domains'] ?? [];
?>

<style>
.sf9-rating-btns {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
}
.sf9-rating-btns .sf9-rating-btn {
    min-width: 30px;
    font-weight: 600;
    font-size: 0.7rem;
    padding: 2px 6px;
    line-height: 1.4;
    color: #495057;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 6px;
    transition: all 0.15s ease-in-out;
}
.sf9-rating-btns .sf9-rating-btn:hover {
    background: #f1f3f5;
    border-color: #adb5bd;
    color: #343a40;
}
.sf9-rating-btns .sf9-rating-btn.active {
    background: #1e4072;
    border-color: #1e4072;
    color: #fff;
}
.sf9-other-q-badge {
    font-size: 0.65rem;
    padding: 2px 5px;
    border-radius: 4px;
}
.rec-badge-chip {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.68rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 6px;
    background: #f0f4ff;
    color: #2b4c7e;
    border: 1px solid #c7d7f5;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    text-decoration: none;
    line-height: 1.3;
}
.rec-badge-chip:hover {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1e4072;
    transform: translateY(-1px);
}
.rec-badge-chip.matched {
    background: #ecfdf5;
    color: #065f46;
    border-color: #a7f3d0;
}
</style>

<div class="card border-0 shadow-sm p-4 bg-white">
    <!-- Guide for Rating — top -->
    <div class="card bg-light border-0 mb-3">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> Guide for Rating (DepEd SF9 SPED)</h6>
            <div class="row g-2 small">
                <div class="col-sm-6 col-md-4"><strong>P</strong> — Proficient (Always manifests / 90%+ Mastery)</div>
                <div class="col-sm-6 col-md-4"><strong>AP</strong> — Approaching Proficiency (Most of the time / 75-89%)</div>
                <div class="col-sm-6 col-md-4"><strong>D</strong> — Developing (Sometimes manifests / 50-74%)</div>
                <div class="col-sm-6 col-md-4"><strong>B</strong> — Beginning (Rarely manifests / Below 50%)</div>
                <div class="col-sm-6 col-md-4"><strong>NA</strong> — Not Observed / Not Applicable</div>
            </div>
        </div>
    </div>

    <!-- LMS Recommendation Benchmark Reference Card -->
    <div class="card border mb-3" style="border-color: #dbeafe !important; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-radius: 8px;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge px-2 py-1" style="font-size: 0.75rem; background-color: #1e4072; border-radius: 6px;">
                        <i class="bi bi-lightbulb-fill me-1 text-warning"></i> LMS Rating Reference
                    </span>
                    <span class="fw-bold small text-dark">
                        DepEd SF9 Suggested Baseline: 
                        <span class="badge bg-white text-primary border border-primary fw-bold" style="font-size: 0.8rem; border-radius: 6px;">
                            <?php echo htmlspecialchars($overallBaseline); ?>
                        </span>
                        <span class="text-muted fw-normal ms-1">
                            (Learner LMS Average: <strong><?php echo $overallAvgScore; ?>%</strong> across <?php echo $totalGraded; ?> graded activities)
                        </span>
                    </span>
                </div>
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-size: 0.75rem; padding: 4px 10px;" onclick="fillUnratedWithRecommended()">
                        <i class="bi bi-magic me-1"></i> Prefill Unrated Rows with Suggested
                    </button>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                <i class="bi bi-info-circle me-1 text-primary"></i> <strong>Pahibalo para kay Teacher:</strong> 
                Kini nga recommendation nagsilbing <em>guide ug benchmark reference</em> lamang base sa performance sa learner sa mga LMS activities ug lessons. 
                Dili kini awtomatikong ipugos — ikaw isip SPED Teacher ang may 100% awtoridad sa pag-usab o pagpili sa angay nga marka (P, AP, D, B, NA) sumala sa imong observation. Pwede nimo i-click ang <strong>💡 Rec</strong> badge tupad sa kada indicator kung uyon ka sa recommendation.
            </p>
        </div>
    </div>

    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e4072;">
                <i class="bi bi-list-check me-1"></i> SF9 Performance Indicators
            </h4>
            <p class="text-muted small mb-0">
                Select a quarter above, then tap a rating button for each indicator.
                Other quarters are preserved when you save.
            </p>
        </div>
        <?php if (!empty($canPrintReportCard) && !empty($pdspRecordId)): ?>
            <a href="<?php echo $basePath; ?>/iep/print/report-card/<?php echo (int)$student['id']; ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                <i class="bi bi-printer me-1"></i> Preview SF9 Print
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($pdspRecordId)): ?>
        <div class="alert alert-warning mb-0" style="border-radius: 8px;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            No PDSP record found for this student. Complete the IEP meeting and sign the PDSP before entering SF9 indicator ratings.
        </div>
    <?php elseif (empty($sf9Indicators)): ?>
        <div class="alert alert-danger mb-0" style="border-radius: 8px;">SF9 indicator list is not configured.</div>
    <?php else: ?>
        <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/ratings" id="sf9RatingsForm">
            <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($quarter); ?>">

            <div class="accordion" id="sf9DomainAccordion">
                <?php $domainIndex = 0; foreach ($sf9Indicators as $domain => $indicators): $domainIndex++; 
                    $domRec = $domainRecMap[$domain] ?? null;
                    $domSuggRating = $domRec['rating'] ?? $overallBaseline;
                    $domScore = $domRec['score'] ?? null;
                    $domHasLms = !empty($domRec['has_direct_lms']);
                ?>
                    <div class="accordion-item border mb-2" style="border-radius: 8px; overflow: hidden;">
                        <h2 class="accordion-header" id="sf9-heading-<?php echo $domainIndex; ?>">
                            <button class="accordion-button <?php echo $domainIndex > 1 ? 'collapsed' : ''; ?> fw-bold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#sf9-collapse-<?php echo $domainIndex; ?>"
                                    aria-expanded="<?php echo $domainIndex === 1 ? 'true' : 'false'; ?>"
                                    style="color:#1e4072; background-color:#f8f9fa;">
                                <span class="me-2"><?php echo htmlspecialchars($domain); ?></span>
                                <span class="badge bg-secondary me-auto"><?php echo count($indicators); ?> indicators</span>
                                <?php if ($domSuggRating): ?>
                                    <span class="badge me-3 <?php echo $domHasLms ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border'; ?>" style="font-size: 0.72rem; font-weight: 600; border-radius: 6px;">
                                        <i class="bi bi-lightbulb me-1"></i> Suggested: <strong><?php echo $domSuggRating; ?></strong><?php echo $domScore !== null ? ' (' . round($domScore) . '%)' : ''; ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="sf9-collapse-<?php echo $domainIndex; ?>" class="accordion-collapse collapse <?php echo $domainIndex === 1 ? 'show' : ''; ?>"
                             data-bs-parent="#sf9DomainAccordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-start ps-3" style="min-width:280px;">Performance Indicator</th>
                                                <th class="text-center" style="min-width:260px;">
                                                    Rating — <?php echo htmlspecialchars($quarter); ?>
                                                </th>
                                                <th class="text-center text-muted" style="width:120px;">Other Qtrs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($indicators as $indicator):
                                                $saved = $quarterlyRatingsMap[$indicator] ?? [1 => null, 2 => null, 3 => null, 4 => null];
                                                $currentVal = $saved[$activeQuarterNum] ?? '';
                                                $fieldName = 'ratings[' . $domain . '][' . $indicator . '][' . $activeQuarterNum . ']';
                                                $suggRating = $domSuggRating;
                                                $suggReason = $domRec['reason'] ?? 'Based on LMS performance';
                                            ?>
                                                <tr>
                                                    <td class="text-start ps-3 py-2">
                                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                                            <span><?php echo htmlspecialchars($indicator); ?></span>
                                                            <?php if ($canEdit && !empty($suggRating)): ?>
                                                                <button type="button" 
                                                                        class="rec-badge-chip flex-shrink-0 <?php echo $currentVal === $suggRating ? 'matched' : ''; ?>"
                                                                        title="LMS benchmark: <?php echo htmlspecialchars($suggRating); ?> (<?php echo htmlspecialchars($suggReason); ?>). Click to apply."
                                                                        data-rec="<?php echo htmlspecialchars($suggRating); ?>"
                                                                        onclick="applySingleRec(this, '<?php echo htmlspecialchars($suggRating); ?>')">
                                                                    <i class="bi bi-lightbulb"></i> Rec: <?php echo htmlspecialchars($suggRating); ?>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-center p-2">
                                                        <?php if ($canEdit): ?>
                                                            <input type="hidden"
                                                                   name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES); ?>"
                                                                   value="<?php echo htmlspecialchars((string) $currentVal); ?>"
                                                                   class="sf9-rating-input"
                                                                   data-domain="<?php echo htmlspecialchars($domain, ENT_QUOTES); ?>"
                                                                   data-indicator="<?php echo htmlspecialchars($indicator, ENT_QUOTES); ?>">
                                                            <div class="sf9-rating-btns" role="group">
                                                                <?php foreach ($ratingButtons as $val => $label): ?>
                                                                    <button type="button"
                                                                            class="sf9-rating-btn <?php echo $currentVal === $val ? 'active' : ''; ?>"
                                                                            data-value="<?php echo $val; ?>">
                                                                        <?php echo $label; ?>
                                                                    </button>
                                                                <?php endforeach; ?>
                                                                <button type="button"
                                                                        class="sf9-rating-btn sf9-rating-clear <?php echo $currentVal === '' ? 'active' : ''; ?>"
                                                                        data-value="">
                                                                    —
                                                                </button>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="fw-bold"><?php echo htmlspecialchars($currentVal ?: '—'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php for ($q = 1; $q <= 4; $q++):
                                                            if ($q === $activeQuarterNum) continue;
                                                            $otherVal = $saved[$q] ?? '';
                                                            if ($canEdit): ?>
                                                                <input type="hidden"
                                                                        name="ratings[<?php echo htmlspecialchars($domain, ENT_QUOTES); ?>][<?php echo htmlspecialchars($indicator, ENT_QUOTES); ?>][<?php echo $q; ?>]"
                                                                        value="<?php echo htmlspecialchars((string) $otherVal); ?>">
                                                            <?php endif;
                                                            if ($otherVal): ?>
                                                                <span class="badge bg-light text-dark border sf9-other-q-badge me-1">Q<?php echo $q; ?>:<?php echo htmlspecialchars($otherVal); ?></span>
                                                            <?php endif;
                                                        endfor; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($canEdit): ?>
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 6px;">
                        <i class="bi bi-save me-1"></i> Save SF9 Indicator Ratings
                    </button>
                </div>
            <?php endif; ?>
        </form>

        <script>
        function applySingleRec(btnEl, targetVal) {
            const tr = btnEl.closest('tr');
            if (!tr) return;
            const hidden = tr.querySelector('.sf9-rating-input');
            const group = tr.querySelector('.sf9-rating-btns');
            if (!hidden || !group) return;

            const targetBtn = group.querySelector(`.sf9-rating-btn[data-value="${targetVal}"]`);
            if (targetBtn) {
                group.querySelectorAll('.sf9-rating-btn').forEach(b => b.classList.remove('active'));
                targetBtn.classList.add('active');
                hidden.value = targetVal;
                btnEl.classList.add('matched');
            }
        }

        function fillUnratedWithRecommended() {
            let filledCount = 0;
            document.querySelectorAll('#sf9DomainAccordion tr').forEach(function(row) {
                const hidden = row.querySelector('.sf9-rating-input');
                const btnGroup = row.querySelector('.sf9-rating-btns');
                const recChip = row.querySelector('.rec-badge-chip');
                if (!hidden || !btnGroup || !recChip) return;

                // Only fill if currently unrated
                if (!hidden.value || hidden.value.trim() === '') {
                    const recVal = recChip.getAttribute('data-rec');
                    if (recVal) {
                        const targetBtn = btnGroup.querySelector(`.sf9-rating-btn[data-value="${recVal}"]`);
                        if (targetBtn) {
                            btnGroup.querySelectorAll('.sf9-rating-btn').forEach(b => b.classList.remove('active'));
                            targetBtn.classList.add('active');
                            hidden.value = recVal;
                            recChip.classList.add('matched');
                            filledCount++;
                        }
                    }
                }
            });

            if (filledCount > 0) {
                alert('Malampusong nabutangan ug recommended benchmarks ang ' + filledCount + ' ka unrated indicators! Mahimo pa gihapon nimo silang ilisan o i-adjust sa dili pa i-save.');
            } else {
                alert('Tanan indicators duna nay grado. Walay unrated nga nausab.');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.sf9-rating-btns').forEach(function (group) {
                const hidden = group.previousElementSibling;
                if (!hidden || !hidden.classList.contains('sf9-rating-input')) return;

                group.querySelectorAll('.sf9-rating-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        group.querySelectorAll('.sf9-rating-btn').forEach(function (b) {
                            b.classList.remove('active');
                        });
                        btn.classList.add('active');
                        const val = btn.getAttribute('data-value') || '';
                        hidden.value = val;

                        // Update chip matched state
                        const tr = group.closest('tr');
                        if (tr) {
                            const chip = tr.querySelector('.rec-badge-chip');
                            if (chip) {
                                const recVal = chip.getAttribute('data-rec');
                                if (val === recVal) {
                                    chip.classList.add('matched');
                                } else {
                                    chip.classList.remove('matched');
                                }
                            }
                        }
                    });
                });
            });
        });
        </script>
    <?php endif; ?>
</div>
