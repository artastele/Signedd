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
    padding: 1px 5px;
    line-height: 1.4;
    color: #495057;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 4px;
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
    padding: 1px 4px;
}
</style>

<div class="card border-0 shadow-sm p-4 bg-white">
    <!-- Guide for Rating — top -->
    <div class="card bg-light border-0 mb-3">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> Guide for Rating</h6>
            <div class="row g-2 small">
                <div class="col-sm-6 col-md-4"><strong>P</strong> — Proficient (Always manifests)</div>
                <div class="col-sm-6 col-md-4"><strong>AP</strong> — Approaching Proficiency (Most of the time)</div>
                <div class="col-sm-6 col-md-4"><strong>D</strong> — Developing (Sometimes manifests)</div>
                <div class="col-sm-6 col-md-4"><strong>B</strong> — Beginning (Rarely manifests)</div>
                <div class="col-sm-6 col-md-4"><strong>NA</strong> — Not Observed / Not Applicable</div>
            </div>
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
            <a href="<?php echo $basePath; ?>/iep/print/report-card/<?php echo (int)$student['id']; ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Preview SF9 Print
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($pdspRecordId)): ?>
        <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            No PDSP record found for this student. Complete the IEP meeting and sign the PDSP before entering SF9 indicator ratings.
        </div>
    <?php elseif (empty($sf9Indicators)): ?>
        <div class="alert alert-danger mb-0">SF9 indicator list is not configured.</div>
    <?php else: ?>
        <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/ratings" id="sf9RatingsForm">
            <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($quarter); ?>">

            <div class="accordion" id="sf9DomainAccordion">
                <?php $domainIndex = 0; foreach ($sf9Indicators as $domain => $indicators): $domainIndex++; ?>
                    <div class="accordion-item border mb-2">
                        <h2 class="accordion-header" id="sf9-heading-<?php echo $domainIndex; ?>">
                            <button class="accordion-button <?php echo $domainIndex > 1 ? 'collapsed' : ''; ?> fw-bold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#sf9-collapse-<?php echo $domainIndex; ?>"
                                    aria-expanded="<?php echo $domainIndex === 1 ? 'true' : 'false'; ?>"
                                    style="color:#1e4072; background-color:#f8f9fa;">
                                <?php echo htmlspecialchars($domain); ?>
                                <span class="badge bg-secondary ms-2"><?php echo count($indicators); ?> indicators</span>
                            </button>
                        </h2>
                        <div id="sf9-collapse-<?php echo $domainIndex; ?>" class="accordion-collapse collapse <?php echo $domainIndex === 1 ? 'show' : ''; ?>"
                             data-bs-parent="#sf9DomainAccordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-start" style="min-width:260px;">Performance Indicator</th>
                                                <th class="text-center" style="min-width:280px;">
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
                                            ?>
                                                <tr>
                                                    <td class="text-start ps-3"><?php echo htmlspecialchars($indicator); ?></td>
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
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Save SF9 Indicator Ratings
                    </button>
                </div>
            <?php endif; ?>
        </form>

        <script>
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
                        hidden.value = btn.getAttribute('data-value') || '';
                    });
                });
            });
        });
        </script>
    <?php endif; ?>
</div>
