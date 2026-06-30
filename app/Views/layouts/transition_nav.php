<?php
// Reusable wizard progress tracker for Transition Workflow (Processes 10-13)
// Variables required:
// - $iep: Array containing IEP record details (id, student_name)
// - $activeStep: int (10, 11, 12, or 13)
// - $workflow: Array containing workflow status records from TransitionWorkflowModel

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$iepId = (int)$iep['id'];

if (!isset($transitionNavStudentCode)) {
    require_once __DIR__ . '/../../Models/StudentModel.php';
    $transitionNavRec = (new StudentModel())->findById((int)($iep['student_id'] ?? 0));
    $transitionNavStudentCode = $transitionNavRec['student_id'] ?? null;
}

// Determine status of each step for gating display
$p10Finalized = (!empty($workflow['readiness']) && $workflow['readiness']['status'] === 'finalized');
$p11Finalized = (!empty($workflow['itp']) && $workflow['itp']['status'] === 'finalized');
$p12Finalized = (!empty($workflow['itgp']) && $workflow['itgp']['status'] === 'signed');
$p13Finalized = (!empty($workflow['placement']) && in_array($workflow['placement']['placement_status'], ['Notice Sent', 'Placed']));

$steps = [
    10 => [
        'label' => 'Transition Readiness',
        'sublabel' => 'Part 4',
        'url' => "$basePath/iep/$iepId/transition-readiness",
        'is_accessible' => true, // Always accessible once IEP is signed
        'is_completed' => $p10Finalized,
        'icon' => 'bi-clipboard2-check'
    ],
    11 => [
        'label' => 'Individual Transition Plan',
        'sublabel' => 'Part 5 (ITP)',
        'url' => "$basePath/iep/$iepId/individual-transition-plan",
        'is_accessible' => $p10Finalized,
        'is_completed' => $p11Finalized,
        'icon' => 'bi-file-earmark-person'
    ],
    12 => [
        'label' => 'Inclusive IEP & ITGP',
        'sublabel' => 'Part 6',
        'url' => "$basePath/iep/$iepId/inclusive-iep-itgp",
        'is_accessible' => $p10Finalized && $p11Finalized,
        'is_completed' => $p12Finalized,
        'icon' => 'bi-journal-check'
    ],
    13 => [
        'label' => 'Class Placement Notice',
        'sublabel' => 'Part 7',
        'url' => "$basePath/iep/$iepId/placement-notice",
        'is_accessible' => $p10Finalized && $p11Finalized && $p12Finalized,
        'is_completed' => $p13Finalized,
        'icon' => 'bi-door-open'
    ]
];
?>

<div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 12px;">
    <div class="card-body p-4 bg-white">
        <!-- Student mini-profile context banner -->
        <div class="d-flex align-items-center mb-4 pb-3 border-bottom flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #1e4072;">
                    <i class="bi bi-person-fill fs-4"></i>
                </div>
                <div class="ms-3">
                    <h5 class="mb-0 font-weight-bold text-dark"><?= htmlspecialchars($iep['student_name'] ?? 'Learner') ?></h5>
                    <span class="text-muted small">Active IEP School Year: <strong><?= htmlspecialchars($iep['school_year'] ?? 'N/A') ?></strong> | Student ID: <?= htmlspecialchars(StudentDisplayHelper::formatStudentId($transitionNavStudentCode ?? null)) ?> | DepEd LRN: <?= htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($iep['lrn'] ?? null)) ?></span>
                </div>
            </div>
            <div class="ms-md-auto d-flex align-items-center gap-2">
                <span class="badge text-uppercase py-2 px-3 bg-secondary-subtle text-secondary-emphasis" style="border-radius: 6px;">Transition Context</span>
            </div>
        </div>

        <!-- Wizard Steps Row -->
        <div class="row position-relative g-0 align-items-center py-2">
            <!-- Progress Line Background -->
            <div class="position-absolute start-0 end-0 translate-middle-y d-none d-lg-block" style="top: 35%; height: 4px; background-color: #e2e8f0; z-index: 1;">
                <div class="h-100 transition-all" style="width: <?= ($activeStep == 10 ? '0' : ($activeStep == 11 ? '33' : ($activeStep == 12 ? '66' : '100'))) ?>%; background-color: #a01422;"></div>
            </div>

            <?php foreach ($steps as $stepNum => $s): 
                $isActive = ($activeStep === $stepNum);
                $isAccessible = $s['is_accessible'];
                $isCompleted = $s['is_completed'];
                
                // Color states
                $circleBg = '#e2e8f0';
                $circleColor = '#64748b';
                $circleBorder = 'border-transparent';
                
                if ($isActive) {
                    $circleBg = '#a01422';
                    $circleColor = '#ffffff';
                    $circleBorder = 'border: 4px solid #fecdd3;';
                } elseif ($isCompleted) {
                    $circleBg = '#3b6d11';
                    $circleColor = '#ffffff';
                } elseif ($isAccessible) {
                    $circleBg = '#1e4072';
                    $circleColor = '#ffffff';
                }
            ?>
                <div class="col-12 col-md-6 col-lg-3 text-center position-relative mb-4 mb-lg-0" style="z-index: 2;">
                    <?php if ($isAccessible): ?>
                        <a href="<?= htmlspecialchars($s['url']) ?>" class="text-decoration-none d-flex flex-column align-items-center group">
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center text-muted" style="cursor: not-allowed;">
                    <?php endif; ?>

                        <!-- Circle Icon -->
                        <div class="rounded-circle d-flex align-items-center justify-content-center transition-all hover-scale shadow-sm"
                             style="width: 54px; height: 54px; background-color: <?= $circleBg ?>; color: <?= $circleColor ?>; <?= $circleBorder ?>">
                            <?php if ($isCompleted && !$isActive): ?>
                                <i class="bi bi-check-lg fs-4"></i>
                            <?php elseif (!$isAccessible): ?>
                                <i class="bi bi-lock-fill fs-5"></i>
                            <?php else: ?>
                                <i class="bi <?= $s['icon'] ?> fs-4"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Labels -->
                        <div class="mt-2 px-2">
                            <span class="d-block text-uppercase font-weight-bold tracking-wider mb-1" style="font-size: 0.75rem; color: <?= $isActive ? '#a01422' : ($isAccessible ? '#1e4072' : '#94a3b8') ?>;">
                                <?= htmlspecialchars($s['sublabel']) ?>
                            </span>
                            <span class="d-block font-weight-bold" style="font-size: 0.9rem; color: <?= $isActive ? '#1e293b' : '#64748b' ?>;">
                                <?= htmlspecialchars($s['label']) ?>
                            </span>
                        </div>

                    <?php if ($isAccessible): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.hover-scale {
    transition: transform 0.2s ease-in-out;
}
.group:hover .hover-scale {
    transform: scale(1.08);
}
.tracking-wider {
    letter-spacing: 0.05em;
}
</style>
