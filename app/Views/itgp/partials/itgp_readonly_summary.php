<?php
/**
 * Partial: Read-only summary of ITGP goal + activities table.
 * Requires: $itgp (array from workflow), $iep (context)
 */
if (empty($itgp)) return;
?>
<div class="card border-0 bg-light mb-4" style="border-radius:10px;">
    <div class="card-body p-3">
        <h6 class="fw-bold mb-3" style="color:#1e4072;"><i class="bi bi-journal-check me-1"></i>ITGP Draft Summary</h6>

        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <span class="small text-muted text-uppercase fw-bold d-block">Learner</span>
                <span class="fw-semibold"><?= htmlspecialchars($iep['student_name'] ?? '') ?></span>
            </div>
            <div class="col-md-6">
                <span class="small text-muted text-uppercase fw-bold d-block">Transition Goal / Target Objective</span>
                <p class="mb-0"><?= nl2br(htmlspecialchars($itgp['goal'] ?? '—')) ?></p>
            </div>
            <div class="col-md-6">
                <span class="small text-muted text-uppercase fw-bold d-block">Point of Entry</span>
                <p class="mb-0"><?= htmlspecialchars($itgp['entry_point'] ?? '—') ?></p>
            </div>
            <?php if (!empty($itgp['learning_packages'])): ?>
            <div class="col-12">
                <span class="small text-muted text-uppercase fw-bold d-block">Learning Packages</span>
                <p class="mb-0"><?= htmlspecialchars($itgp['learning_packages']) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($itgp['activities'])): ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered small mb-0 bg-white">
                <thead class="text-white" style="background:#1e4072;">
                    <tr>
                        <th>Competency / Skill</th>
                        <th>Activities</th>
                        <th>Time Frame</th>
                        <th>Person Responsible</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itgp['activities'] as $act): ?>
                        <tr>
                            <td><?= nl2br(htmlspecialchars($act['competency_skill'] ?? '')) ?></td>
                            <td><?= nl2br(htmlspecialchars($act['activities'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($act['time_frame'] ?? '') ?></td>
                            <td><?= htmlspecialchars($act['person_responsible'] ?? '') ?></td>
                            <td><?= nl2br(htmlspecialchars($act['remarks'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($itgp['recommendations'])): ?>
        <div class="mt-3">
            <span class="small text-muted text-uppercase fw-bold d-block">Educational Recommendations</span>
            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($itgp['recommendations'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
