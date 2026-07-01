<?php
// Inclusive IEP + ITGP view for Part 6 — Three-Way Workflow
$pageTitle = 'Inclusive IEP & ITGP — SignED';
require_once __DIR__ . '/../layouts/header.php';
$role      = $_SESSION['role'];
$basePath  = BASE_PATH;
$itgpStatus = $itgp['status'] ?? 'not_started';
$isFinalized = ($itgpStatus === 'finalized');
$hasAssignment = !empty($assignment);
$isAssignedTeacher = ($hasAssignment && (int)$assignment['general_teacher_id'] === (int)$_SESSION['user_id']);
$activeStep = 12;

$isGenTeacher    = ($role === 'general_teacher');
$isSpedTeacher   = ($role === 'sped_teacher');
$isMasterTeacher = ($role === 'master_teacher');
$isAdmin         = ($role === 'admin');
$isPrivileged    = in_array($role, ['sped_teacher','master_teacher','principal','admin'], true);

$statusLabels = [
    'not_started'          => ['Not Started',                  'secondary'],
    'draft'                => ['Draft — Gen. Teacher',         'warning'],
    'pending_sned_review'  => ['Pending SPED Review',          'info'],
    'ready_for_inspection' => ['Ready for Inspection',         'primary'],
    'inspected'            => ['Inspected — Awaiting Finalize','success'],
    'finalized'            => ['Finalized & Locked',           'dark'],
];
[$statusLabel, $statusBadge] = $statusLabels[$itgpStatus] ?? ['Unknown', 'secondary'];
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid py-4" style="max-width:1400px;">

    <!-- ── Page Header ── -->
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h1 class="mb-1" style="color:#1e4072; font-weight:700; font-size:1.6rem;">
                <i class="bi bi-journal-check me-2"></i>Inclusive IEP & ITGP
            </h1>
            <p class="text-muted mb-0 small">Part 6 — Individualized Transition Goal Plan</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-<?= $statusBadge ?> py-2 px-3" style="font-size:0.82rem; border-radius:20px;">
                <i class="bi bi-circle-fill me-1" style="font-size:0.45rem; vertical-align:middle;"></i><?= $statusLabel ?>
            </span>
            <a href="<?= $basePath ?>/iep" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to IEPs
            </a>
        </div>
    </div>

    <!-- ── Flash Alerts ── -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ── Transition Wizard Nav ── -->
    <?php require_once __DIR__ . '/../layouts/transition_nav.php'; ?>

    <?php if ($readiness && ($readiness['status'] ?? '') === 'finalized' && ($readiness['readiness_result'] ?? '') !== 'Ready for Inclusion'): ?>
        <div class="alert alert-warning py-3 mb-4 d-flex align-items-start gap-3 shadow-sm border-0" style="border-left: 5px solid #ffc107; background-color: #fffbeb; border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-3 mt-1 flex-shrink-0"></i>
            <div>
                <h5 class="alert-heading text-warning-emphasis mb-1 font-weight-bold">Learner Re-Evaluation / Support Needed</h5>
                <p class="mb-0 small text-muted">
                    Based on the finalized transition readiness evaluation, this learner is evaluated as <strong><?= htmlspecialchars($readiness['readiness_result']) ?></strong>.
                    They are <strong>not yet ready</strong> to transition. Please proceed with caution and ensure additional learning and support accommodations are carefully documented in this goal plan.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- ── ITGP Workflow Progress Stepper ── -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-body px-4 py-3">
            <p class="small fw-semibold text-muted text-uppercase mb-3" style="letter-spacing:.05em;">ITGP Workflow Progress</p>
            <div class="d-flex align-items-start">
                <?php
                $wfSteps = [
                    ['icon'=>'bi-pencil-square',  'label'=>'Gen. Teacher', 'sub'=>'Draft ITGP',        'statuses'=>['draft','pending_sned_review','ready_for_inspection','inspected','finalized']],
                    ['icon'=>'bi-chat-dots-fill', 'label'=>'SPED Teacher', 'sub'=>'Review & Remarks',   'statuses'=>['pending_sned_review','ready_for_inspection','inspected','finalized']],
                    ['icon'=>'bi-pen-fill',        'label'=>'Master Teacher','sub'=>'Inspect & Sign',    'statuses'=>['ready_for_inspection','inspected','finalized']],
                    ['icon'=>'bi-lock-fill',       'label'=>'SPED Teacher', 'sub'=>'Finalize & Lock',   'statuses'=>['inspected','finalized']],
                ];
                $activeWf = match($itgpStatus) {
                    'draft'                => 0,
                    'pending_sned_review'  => 1,
                    'ready_for_inspection' => 2,
                    'inspected'            => 3,
                    'finalized'            => 4,
                    default                => 0,
                };
                foreach ($wfSteps as $i => $ws):
                    $isDone    = in_array($itgpStatus, $ws['statuses'], true);
                    $isCurrent = ($activeWf === $i);
                    if ($isDone && !$isCurrent) {
                        $bg = '#1e4072'; $textC = 'white'; $badge = 'Done';  $badgeCls = 'success';
                    } elseif ($isCurrent) {
                        $bg = '#a01422'; $textC = 'white'; $badge = 'Active'; $badgeCls = 'danger';
                    } else {
                        $bg = '#dee2e6'; $textC = '#6c757d'; $badge = null; $badgeCls = '';
                    }
                ?>
                <div class="text-center" style="flex:1; position:relative;">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;background:<?= $bg ?>;color:<?= $textC ?>;font-size:1.1rem;">
                        <i class="bi <?= $ws['icon'] ?>"></i>
                    </div>
                    <div class="small fw-semibold" style="color:<?= $isCurrent ? '#a01422' : ($isDone ? '#1e4072' : '#adb5bd') ?>; font-size:.78rem;"><?= $ws['label'] ?></div>
                    <div class="text-muted" style="font-size:.72rem;"><?= $ws['sub'] ?></div>
                    <?php if ($badge): ?>
                        <span class="badge bg-<?= $badgeCls ?> mt-1" style="font-size:.65rem;"><?= $badge ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($i < count($wfSteps)-1): ?>
                    <div style="flex:.8; height:3px; margin-top:22px; background:<?= in_array($itgpStatus, $ws['statuses']) ? '#1e4072' : '#dee2e6' ?>;"></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── MAIN TWO-COLUMN LAYOUT ── -->
    <div class="row g-4">

        <!-- ════ LEFT COLUMN: Reference Tools ════ -->
        <div class="col-xl-4 col-lg-5 d-flex flex-column gap-4">

            <!-- General Teacher Assignment Card -->
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius:12px 12px 0 0;">
                    <h6 class="mb-0 fw-bold" style="color:#1e4072; font-size:.9rem;">
                        <i class="bi bi-person-badge-fill me-2"></i>Assigned General Ed Teacher
                    </h6>
                </div>
                <div class="card-body p-3">
                    <?php if (!$hasAssignment): ?>
                        <div class="text-center py-2 mb-3">
                            <div class="rounded-circle bg-warning-subtle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                                <i class="bi bi-person-plus text-warning fs-4"></i>
                            </div>
                            <p class="small text-muted mb-0">No teacher assigned yet.</p>
                        </div>
                        <?php if ($isPrivileged): ?>
                            <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/assign">
                                <select name="general_teacher_id" class="form-select form-select-sm mb-2 border-2" required>
                                    <option value="">-- Select General Ed Teacher --</option>
                                    <?php foreach ($generalTeachers as $gt): ?>
                                        <option value="<?= $gt['id'] ?>"><?= htmlspecialchars($gt['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm w-100 text-white fw-semibold" style="background:#1e4072; border-radius:7px;">
                                    <i class="bi bi-check-lg me-1"></i>Assign Teacher
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-secondary w-100 py-2">Contact SPED Teacher to assign</span>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
                                <i class="bi bi-person-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="color:#1e4072; font-size:.93rem;"><?= htmlspecialchars($assignment['general_teacher_name']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($assignment['general_teacher_email'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php if ($isPrivileged && !$isFinalized): ?>
                            <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/assign">
                                <div class="input-group input-group-sm">
                                    <select name="general_teacher_id" class="form-select border-2" required>
                                        <option value="">Re-assign teacher...</option>
                                        <?php foreach ($generalTeachers as $gt): ?>
                                            <option value="<?= $gt['id'] ?>" <?= (int)$gt['id'] === (int)$assignment['general_teacher_id'] ? 'disabled' : '' ?>><?= htmlspecialchars($gt['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm text-white" style="background:#a01422;">Change</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reference Accordion -->
            <div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold" style="color:#1e4072; font-size:.9rem;">
                        <i class="bi bi-collection-fill me-2"></i>Reference Documents
                    </h6>
                </div>
                <div class="accordion" id="referenceAccordion" style="border-radius:0;">
                    <!-- IEP Goals -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIep" style="background:#f8f9fa; color:#1e4072;">
                                <i class="bi bi-file-earmark-medical me-2 text-primary"></i>I. Original IEP Goals
                            </button>
                        </h2>
                        <div id="collapseIep" class="accordion-collapse collapse" data-bs-parent="#referenceAccordion">
                            <div class="accordion-body p-3 bg-light-subtle" style="font-size:.85rem;">
                                <?php if (empty($iepSteps)): ?>
                                    <p class="text-muted small mb-0">No IEP objectives found.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0 bg-white" style="font-size:.82rem;">
                                            <thead><tr class="table-secondary"><th>No.</th><th>Domain</th><th>Objective</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($iepSteps as $step): ?>
                                                    <tr>
                                                        <td class="text-center fw-bold"><?= (int)$step['step_number'] ?></td>
                                                        <td><span class="badge bg-secondary" style="font-size:.7rem;"><?= htmlspecialchars($step['pdsp_domain']) ?></span></td>
                                                        <td><?= htmlspecialchars($step['goal_text']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- PDSP -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePdsp" style="background:#f8f9fa; color:#1e4072; font-size:.82rem;">
                                <i class="bi bi-person-badge-fill me-2 text-primary"></i>II. PDSP Domains
                            </button>
                        </h2>
                        <div id="collapsePdsp" class="accordion-collapse collapse" data-bs-parent="#referenceAccordion">
                            <div class="accordion-body p-2 bg-light-subtle" style="font-size:.78rem;">
                                <?php if (!empty($pdspSignedDocPath)):
                                    $pdspDocUrl = $basePath . '/' . ltrim($pdspSignedDocPath, '/');
                                    $pdspExt = strtolower(pathinfo($pdspSignedDocPath, PATHINFO_EXTENSION));
                                    $pdspIsImage = in_array($pdspExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                ?>
                                <div class="mb-2 p-2 rounded bg-white border text-center">
                                    <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.65rem; letter-spacing:.04em;">Part 2 — Signed PDSP</div>
                                    <?php if ($pdspIsImage): ?>
                                        <img src="<?= htmlspecialchars($pdspDocUrl) ?>" alt="Signed PDSP"
                                             class="rounded border img-fluid itgp-doc-thumb"
                                             style="max-height:90px; cursor:pointer; object-fit:contain;"
                                             onclick="window.open('<?= htmlspecialchars($pdspDocUrl, ENT_QUOTES) ?>','_blank')">
                                        <div class="mt-1"><a href="<?= htmlspecialchars($pdspDocUrl) ?>" target="_blank" class="small text-decoration-none"><i class="bi bi-box-arrow-up-right me-1"></i>View full size</a></div>
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars($pdspDocUrl) ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.72rem;">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>View PDSP Document
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (empty($pdspDomains)): ?>
                                    <p class="text-muted small mb-0">No PDSP domains found.</p>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-1">
                                    <?php foreach ($pdspDomains as $domain): ?>
                                        <div class="px-2 py-1 rounded bg-white border" style="line-height:1.3;">
                                            <div class="fw-semibold text-primary" style="font-size:.75rem;"><?= htmlspecialchars($domain['domain_name']) ?></div>
                                            <?php if (!empty($domain['skills_description'])): ?>
                                                <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($domain['skills_description']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($domain['educational_recommendation'])): ?>
                                                <div class="text-success-emphasis mt-1" style="font-size:.7rem;"><i class="bi bi-lightbulb me-1"></i><?= htmlspecialchars($domain['educational_recommendation']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- ITP -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseItp" style="background:#f8f9fa; color:#1e4072; font-size:.82rem;">
                                <i class="bi bi-arrow-left-right me-2 text-primary"></i>III. Finalized ITP
                            </button>
                        </h2>
                        <div id="collapseItp" class="accordion-collapse collapse" data-bs-parent="#referenceAccordion">
                            <div class="accordion-body p-2 bg-light-subtle" style="font-size:.78rem;">
                                <p class="mb-1 small"><strong>Point of Entry:</strong> <?= htmlspecialchars($itp['point_of_entry'] ?? 'Not set') ?></p>
                                <p class="mb-0 small text-muted"><strong>Recommendations (Beg. SY):</strong><br><?= nl2br(htmlspecialchars($itpRecommendationsBeginning ?? 'None')) ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Learner Documents -->
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button py-2 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocs" style="background:#f8f9fa; color:#1e4072; font-size:.82rem;">
                                <i class="bi bi-folder2-open me-2 text-primary"></i>IV. View Documents
                            </button>
                        </h2>
                        <div id="collapseDocs" class="accordion-collapse collapse show" data-bs-parent="#referenceAccordion">
                            <div class="accordion-body p-2 bg-light-subtle">
                                <div class="d-flex flex-column gap-2">
                                    <a href="<?= $basePath ?>/iep/print/report-card/<?= intval($iep['student_id']) ?>" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-danger py-1 px-2 d-flex align-items-center justify-content-between" style="font-size:.75rem; border-radius:6px;">
                                        <span><i class="bi bi-file-earmark-bar-graph me-1"></i>SF9 Report Card</span>
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <?php if (!empty($pdspSignedDocPath)):
                                        $pdspDocUrl = $basePath . '/' . ltrim($pdspSignedDocPath, '/');
                                    ?>
                                    <a href="<?= htmlspecialchars($pdspDocUrl) ?>" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-primary py-1 px-2 d-flex align-items-center justify-content-between" style="font-size:.75rem; border-radius:6px;">
                                        <span><i class="bi bi-file-earmark-medical me-1"></i>Signed PDSP (Part 2)</span>
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (empty($progressReport)): ?>
                                        <p class="text-muted mb-0 small" style="font-size:.7rem;"><i class="bi bi-info-circle me-1"></i>No finalized progress report on file yet.</p>
                                    <?php elseif ($progressReport['status'] !== 'finalized'): ?>
                                        <p class="text-muted mb-0 small" style="font-size:.7rem;"><i class="bi bi-info-circle me-1"></i>Progress report is still in draft.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Discussion Board -->
            <div class="card border-0 shadow-sm d-flex flex-column" style="border-radius:12px; overflow:hidden; min-height:380px; max-height:480px;">
                <div class="card-header border-bottom py-3 px-4" style="background:#1e4072; color:white; border-radius:12px 12px 0 0;">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-chat-dots-fill me-2"></i>Discussion Board</h6>
                </div>
                <div class="flex-grow-1 overflow-y-auto p-3 bg-light" id="commentsBox" style="display:flex; flex-direction:column; gap:10px;">
                    <?php if (empty($comments)): ?>
                        <div class="m-auto text-center text-muted">
                            <i class="bi bi-chat-square-quote fs-2"></i>
                            <p class="small mt-2">No messages yet.<br>Teachers can use this to collaborate.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment):
                            $isMe  = ((int)$comment['posted_by'] === (int)$_SESSION['user_id']);
                            $isGen = ($comment['author_role'] === 'general_teacher');
                            $bubbleBg = $isGen ? '#fff3f3' : '#f0f4ff';
                            $align = $isMe ? 'align-self-end' : 'align-self-start';
                        ?>
                            <div class="d-flex flex-column <?= $align ?>" style="max-width:88%;">
                                <div class="d-flex align-items-center gap-2 mb-1 <?= $isMe ? 'flex-row-reverse' : '' ?>">
                                    <span class="small fw-semibold text-muted"><?= htmlspecialchars($comment['author_name']) ?></span>
                                    <span class="badge" style="background:<?= $isGen ? '#a01422' : '#1e4072' ?>; font-size:.65rem;"><?= $isGen ? 'Gen. Ed' : 'SPED' ?></span>
                                </div>
                                <div class="p-2 px-3 rounded-3 shadow-sm" style="background:<?= $bubbleBg ?>; border:1px solid <?= $isGen ? '#f5c6cb' : '#d0dcf5' ?>; font-size:.88rem; line-height:1.4;">
                                    <?= nl2br(htmlspecialchars($comment['comment_text'])) ?>
                                    <div class="text-muted mt-1" style="font-size:.7rem;"><?= date('M d, Y g:i A', strtotime($comment['created_at'])) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!$isFinalized): ?>
                <div class="card-footer bg-white border-top p-2">
                    <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/comment">
                        <div class="input-group">
                            <textarea name="comment_text" class="form-control form-control-sm border-0 bg-light" placeholder="Write a note or question..." rows="1" style="resize:none; border-radius:8px 0 0 8px;" required></textarea>
                            <button type="submit" class="btn btn-sm text-white px-3" style="background:#1e4072; border-radius:0 8px 8px 0;">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /left column -->

        <!-- ════ RIGHT COLUMN: Workflow Action Panels ════ -->
        <div class="col-xl-8 col-lg-7 d-flex flex-column gap-4">

            <?php if ($isFinalized): ?>
            <!-- ══ FINALIZED ══ -->
            <div class="card border-0 shadow-sm" style="border-radius:14px; border-left:5px solid #3b6d11 !important;">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">
                        <i class="bi bi-lock-fill fs-3 text-success"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-success mb-1">ITGP Finalized & Locked</h5>
                        <p class="text-muted small mb-0">This transition goal plan has been signed by the Master Teacher and finalized by the SPED Teacher.</p>
                    </div>
                    <a href="<?= $basePath ?>/iep/<?= $iep['id'] ?>/placement-notice" class="btn text-white ms-auto fw-semibold" style="background:#1e4072; border-radius:8px;">
                        Part 7: Placement Notice <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <!-- Show full ITGP summary -->
            <?php include __DIR__ . '/partials/itgp_readonly_summary.php'; ?>

            <?php elseif ($itgpStatus === 'inspected' && $isSpedTeacher): ?>
            <!-- ══ STEP 4: SPED FINALIZE ══ -->
            <div class="card border-0 shadow" style="border-radius:14px; overflow:hidden;">
                <div class="card-header py-3 px-4 text-white" style="background:linear-gradient(135deg,#3b6d11,#5a9c1a);">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-check2-all me-2"></i>Step 4: Finalize & Lock ITGP</h5>
                    <p class="mb-0 small opacity-75">The Master Teacher has inspected and signed. Review below, then finalize.</p>
                </div>
                <div class="card-body p-4">
                    <?php include __DIR__ . '/partials/itgp_readonly_summary.php'; ?>

                    <!-- SPED Remarks display -->
                    <?php if (!empty($itgp['sned_remarks'])): ?>
                    <div class="card border-0 mb-4" style="border-radius:10px; border-left:4px solid #1e4072 !important; background:#f0f4ff;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-2" style="color:#1e4072; font-size:.88rem;"><i class="bi bi-chat-dots-fill me-2"></i>SPED Teacher Consult Remarks</h6>
                            <p class="mb-1 small"><?= nl2br(htmlspecialchars($itgp['sned_remarks'])) ?></p>
                            <?php if (!empty($itgp['sned_reviewed_at'])): ?>
                                <small class="text-muted">Reviewed: <?= date('M d, Y g:i A', strtotime($itgp['sned_reviewed_at'])) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Master Signature display -->
                    <?php if (!empty($itgp['master_signature'])): ?>
                    <div class="card border-0 mb-4 bg-light" style="border-radius:10px;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-muted text-uppercase small mb-2"><i class="bi bi-pen-fill me-1"></i>Master Teacher Signature & Recommendations</h6>
                            <?php if (!empty($itgp['master_teacher_recommendations'])): ?>
                                <p class="small mb-3"><strong>Recommendations:</strong><br><?= nl2br(htmlspecialchars($itgp['master_teacher_recommendations'])) ?></p>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($itgp['master_signature']) ?>" class="border rounded bg-white" style="max-width:280px; max-height:100px; object-fit:contain;" alt="Signature">
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/finalize" onsubmit="return confirm('Finalize and lock this ITGP? This action cannot be undone.');">
                        <button type="submit" class="btn btn-lg text-white px-5 fw-bold" style="background:#3b6d11; border-radius:8px;">
                            <i class="bi bi-lock-fill me-2"></i>Finalize & Lock ITGP
                        </button>
                    </form>
                </div>
            </div>

            <?php elseif ($itgpStatus === 'ready_for_inspection' && $isMasterTeacher): ?>
            <!-- ══ STEP 3: MASTER TEACHER INSPECT & SIGN ══ -->
            <div class="card border-0 shadow" style="border-radius:14px; overflow:hidden;">
                <div class="card-header py-3 px-4 text-white" style="background:linear-gradient(135deg,#1e4072,#2a528f);">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pen-fill me-2"></i>Step 3: Inspect & Digitally Sign</h5>
                    <p class="mb-0 small opacity-75">Review the ITGP draft and SPED Teacher remarks, then add your recommendations and signature.</p>
                </div>
                <div class="card-body p-4">
                    <?php include __DIR__ . '/partials/itgp_readonly_summary.php'; ?>

                    <!-- SPED Remarks display -->
                    <?php if (!empty($itgp['sned_remarks'])): ?>
                    <div class="card border-0 mb-4" style="border-radius:10px; border-left:4px solid #0d6efd !important; background:#f0f4ff;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-2" style="color:#1e4072; font-size:.88rem;"><i class="bi bi-chat-dots-fill me-2"></i>SPED Teacher Consult Remarks</h6>
                            <p class="mb-1 small"><?= nl2br(htmlspecialchars($itgp['sned_remarks'])) ?></p>
                            <?php if (!empty($itgp['sned_reviewed_at'])): ?>
                                <small class="text-muted">Reviewed: <?= date('M d, Y g:i A', strtotime($itgp['sned_reviewed_at'])) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/inspect" id="inspectForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Recommendations (Beginning of School Year)</label>
                            <textarea name="master_recommendations" class="form-control border-2" rows="4" style="border-radius:8px;"
                                placeholder="Enter your recommendations and observations as Master Teacher II..."><?= htmlspecialchars($itgp['master_teacher_recommendations'] ?? '') ?></textarea>
                        </div>

                        <!-- Signature Pad -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Digital Signature <span class="text-danger">*</span></label>
                            <div class="border rounded-3 overflow-hidden bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
                                    <small class="text-muted"><i class="bi bi-pen me-1"></i>Draw your signature below</small>
                                    <button type="button" id="clearSigBtn" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eraser me-1"></i>Clear
                                    </button>
                                </div>
                                <canvas id="signatureCanvas" height="160" style="width:100%; cursor:crosshair; display:block; touch-action:none; background:white;"></canvas>
                            </div>
                            <input type="hidden" name="master_signature" id="masterSignatureInput">
                            <div id="sigWarning" class="text-danger small mt-1" style="display:none;">
                                <i class="bi bi-exclamation-triangle me-1"></i>Please provide your digital signature.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-lg text-white px-5 fw-bold" style="background:#1e4072; border-radius:8px;">
                            <i class="bi bi-pen-fill me-2"></i>Digitally Inspect & Sign
                        </button>
                    </form>
                </div>
            </div>

            <?php elseif ($itgpStatus === 'pending_sned_review' && ($isSpedTeacher || $isAdmin)): ?>
            <!-- ══ STEP 2: SPED TEACHER REMARKS ══ -->
            <div class="card border-0 shadow" style="border-radius:14px; overflow:hidden;">
                <div class="card-header py-3 px-4 text-white" style="background:linear-gradient(135deg,#1e4072,#2a528f);">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-chat-dots-fill me-2"></i>Step 2: Review Draft & Add Consult Remarks</h5>
                    <p class="mb-0 small opacity-75">If revisions are needed, send consult remarks to the General Teacher. If the draft is acceptable as-is, forward directly for Master Teacher inspection.</p>
                </div>
                <div class="card-body p-4">
                    <?php include __DIR__ . '/partials/itgp_readonly_summary.php'; ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Consult Remarks / Recommended Learning Accommodations</label>
                        <textarea id="snedRemarksText" class="form-control form-control-sm border" rows="4" style="border-radius:6px; font-size:.88rem;"
                            placeholder="Optional for inspection. Required when sending back for revision..."><?= htmlspecialchars($itgp['sned_remarks'] ?? '') ?></textarea>
                        <div class="text-muted mt-1" style="font-size:.72rem;"><i class="bi bi-info-circle me-1"></i>Required only when sending remarks to the General Teacher.</div>
                    </div>

                    <form id="sendBackForm" method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/send-back" style="display:none;">
                        <input type="hidden" name="sned_remarks" id="hiddenRemarksBack">
                    </form>
                    <form id="forwardInspectForm" method="POST" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/sned-remarks" style="display:none;">
                        <input type="hidden" name="sned_remarks" id="hiddenRemarksForward">
                    </form>

                    <div class="d-flex align-items-center gap-2 flex-wrap pt-1">
                        <button type="button" id="sendRemarksBtn" class="btn btn-sm btn-outline-secondary fw-semibold px-3" style="border-radius:6px; font-size:.82rem;">
                            <i class="bi bi-arrow-return-left me-1"></i>Send Remarks to Gen. Teacher
                        </button>
                        <button type="button" id="forwardInspectBtn" class="btn btn-sm text-white fw-semibold px-3" style="background:#1e4072; border-radius:6px; font-size:.82rem;">
                            <i class="bi bi-send-fill me-1"></i>No Revisions — Forward for Inspection
                        </button>
                    </div>
                </div>
            </div>

            <?php elseif (($itgpStatus === 'draft' || $itgpStatus === 'not_started') && $isGenTeacher && $isAssignedTeacher): ?>
            <!-- ══ STEP 1: GENERAL TEACHER DRAFT FORM ══ -->
            <div class="card border-0 shadow" style="border-radius:14px; overflow:hidden;">
                <div class="card-header py-3 px-4 text-white" style="background:linear-gradient(135deg,#1e4072,#2a528f);">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Step 1: Draft Individualized Transition Goal Plan</h5>
                    <p class="mb-0 small opacity-75">Fill in the ITGP goals and activities. You can save a draft or submit for SPED Teacher review.</p>
                </div>
                <div class="card-body p-4">
                    <!-- SPED Revision Notice (shown when SPED sent remarks back) -->
                    <?php if (!empty($itgp['sned_remarks']) && !empty($itgp['sned_reviewed_at'])): ?>
                    <div class="alert alert-warning d-flex align-items-start gap-3 mb-4" style="border-left:5px solid #ffc107; border-radius:10px;">
                        <i class="bi bi-arrow-return-left fs-3 text-warning flex-shrink-0 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-warning-emphasis">Revision Requested by SPED Teacher</h6>
                            <p class="mb-1 small"><?= nl2br(htmlspecialchars($itgp['sned_remarks'])) ?></p>
                            <small class="text-muted">Sent back: <?= date('M d, Y g:i A', strtotime($itgp['sned_reviewed_at'])) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <form id="itgpForm" method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp">
                        <!-- Student Info Row -->
                        <div class="row g-3 mb-4 p-3 rounded-3 bg-light-subtle border">
                            <div class="col-md-6">
                                <span class="form-label small text-muted text-uppercase fw-bold d-block mb-0">Learner Name</span>
                                <span class="fw-bold" style="color:#1e4072; font-size:1.05rem;"><?= htmlspecialchars($iep['student_name']) ?></span>
                            </div>
                            <div class="col-md-6">
                                <span class="form-label small text-muted text-uppercase fw-bold d-block mb-0">Disability / Exceptionality</span>
                                <span class="text-muted"><?= htmlspecialchars($iep['disability_type'] ?? 'Not set') ?></span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="itgp_goal" class="form-label small fw-bold text-muted text-uppercase">Transition Goal / Target Objective <span class="text-danger">*</span></label>
                                <textarea id="itgp_goal" name="itgp_goal" class="form-control border-2" rows="2" style="border-radius:8px;"
                                    placeholder="Define the primary transition target or objective..." required><?= htmlspecialchars($itgp['goal'] ?? $itpRecommendationsBeginning ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label for="entry_point" class="form-label small fw-bold text-muted text-uppercase">Point of Entry <span class="text-danger">*</span></label>
                                <input id="entry_point" name="entry_point" type="text" class="form-control border-2" style="border-radius:8px;"
                                    placeholder="e.g. Regular Class (Mainstreamed)"
                                    value="<?= htmlspecialchars($itgp['entry_point'] ?? $itp['point_of_entry'] ?? '') ?>" required>
                            </div>

                            <!-- Activities Table -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-0">Enabling Activities & Learning Tasks</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addActivityBtn">
                                        <i class="bi bi-plus-lg me-1"></i>Add Row
                                    </button>
                                </div>
                                <div class="table-responsive border rounded-3">
                                    <table class="table table-bordered align-middle mb-0" id="activitiesTable" style="font-size:.85rem;">
                                        <thead class="text-white" style="background:#1e4072;">
                                            <tr>
                                                <th>Competency / Skill</th>
                                                <th>Activities (Indicator)</th>
                                                <th>Time Frame</th>
                                                <th>Person Responsible</th>
                                                <th>Remarks</th>
                                                <th style="width:40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $activitiesList = !empty($itgp['activities']) ? $itgp['activities'] : [['competency_skill'=>'','activities'=>'','time_frame'=>'','person_responsible'=>'','remarks'=>'']];
                                            foreach ($activitiesList as $idx => $act): ?>
                                                <tr>
                                                    <td>
                                                        <?php
                                                        $selectedCategory = $act['competency_skill'] ?? '';
                                                        $isCustomCategory = !empty($selectedCategory);
                                                        if ($isCustomCategory) {
                                                            if (isset($sf9Indicators[$selectedCategory])) {
                                                                $isCustomCategory = false;
                                                            }
                                                        }
                                                        ?>
                                                        <select name="activities[<?= $idx ?>][competency_skill]" class="form-select form-select-sm border-0 shadow-none competency-category-select">
                                                            <option value="">-- Select Category --</option>
                                                            <?php if ($isCustomCategory): ?>
                                                                <option value="<?= htmlspecialchars($selectedCategory) ?>" selected><?= htmlspecialchars($selectedCategory) ?> (Custom)</option>
                                                            <?php endif; ?>
                                                            <?php foreach ($sf9Indicators as $domain => $indicators): ?>
                                                                <option value="<?= htmlspecialchars($domain) ?>" <?= (!$isCustomCategory && $selectedCategory === $domain) ? 'selected' : '' ?>><?= htmlspecialchars($domain) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $selectedActivity = $act['activities'] ?? '';
                                                        $hasCustomActivity = !empty($selectedActivity);
                                                        if ($hasCustomActivity && !empty($selectedCategory) && isset($sf9Indicators[$selectedCategory])) {
                                                            if (in_array($selectedActivity, $sf9Indicators[$selectedCategory], true)) {
                                                                    $hasCustomActivity = false;
                                                            }
                                                        }
                                                        ?>
                                                        <select name="activities[<?= $idx ?>][activities]" class="form-select form-select-sm border-0 shadow-none activity-indicator-select">
                                                            <?php if (empty($selectedCategory)): ?>
                                                                <option value="">-- Select Category First --</option>
                                                            <?php else: ?>
                                                                <option value="">-- Select Activity --</option>
                                                                <?php if ($hasCustomActivity): ?>
                                                                    <option value="<?= htmlspecialchars($selectedActivity) ?>" selected><?= htmlspecialchars($selectedActivity) ?> (Custom)</option>
                                                                <?php endif; ?>
                                                                <?php if (isset($sf9Indicators[$selectedCategory])): ?>
                                                                    <?php foreach ($sf9Indicators[$selectedCategory] as $ind): ?>
                                                                        <option value="<?= htmlspecialchars($ind) ?>" <?= (!$hasCustomActivity && $selectedActivity === $ind) ? 'selected' : '' ?>><?= htmlspecialchars($ind) ?></option>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" name="activities[<?= $idx ?>][time_frame]" class="form-control form-control-sm border-0 shadow-none" placeholder="Time Frame" value="<?= htmlspecialchars($act['time_frame'] ?? '') ?>"></td>
                                                    <td><input type="text" name="activities[<?= $idx ?>][person_responsible]" class="form-control form-control-sm border-0 shadow-none" placeholder="Person" value="<?= htmlspecialchars($act['person_responsible'] ?? '') ?>"></td>
                                                    <td><textarea name="activities[<?= $idx ?>][remarks]" class="form-control form-control-sm border-0 shadow-none" rows="2" placeholder="Remarks"><?= htmlspecialchars($act['remarks'] ?? '') ?></textarea></td>
                                                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row-btn"><i class="bi bi-trash-fill"></i></button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Dropdown Template for JS dynamic row addition -->
                                <template id="competency-select-template">
                                    <select name="activities[__INDEX__][competency_skill]" class="form-select form-select-sm border-0 shadow-none competency-category-select">
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($sf9Indicators as $domain => $indicators): ?>
                                            <option value="<?= htmlspecialchars($domain) ?>"><?= htmlspecialchars($domain) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </template>
                            </div>

                            <div class="col-12">
                                <label for="itgp_recommendations" class="form-label small fw-bold text-muted text-uppercase">Educational Recommendations</label>
                                <textarea id="itgp_recommendations" name="itgp_recommendations" class="form-control border-2" rows="3" style="border-radius:8px;"
                                    placeholder="Optional: Future recommendations for transition or regular class placement..."><?= htmlspecialchars($itgp['recommendations'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex align-items-center gap-3 flex-wrap">
                            <button type="submit" name="action" value="save_draft" class="btn btn-outline-secondary px-4 fw-semibold">
                                <i class="bi bi-floppy me-1"></i>Save Draft
                            </button>
                            <button type="submit" name="submit_for_review" value="1" class="btn btn-lg text-white px-5 fw-bold" style="background:#1e4072; border-radius:8px;"
                                onclick="return confirm('Submit this ITGP draft to the SPED Teacher for review?\n\nYou will not be able to edit after submission.');">
                                <i class="bi bi-send-fill me-2"></i>Submit for SPED Teacher Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php else: ?>
            <!-- ══ VIEWING / WAITING STATE (read-only for non-active roles) ══ -->
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-body p-4">
                    <?php
                    $waitTitle = match($itgpStatus) {
                        'draft'                => 'General Teacher is Working on the Draft',
                        'pending_sned_review'  => 'Awaiting SPED Teacher Review',
                        'ready_for_inspection' => 'Awaiting Master Teacher Inspection',
                        'inspected'            => 'Awaiting SPED Teacher Finalization',
                        default                => 'ITGP Not Yet Started',
                    };
                    $waitDesc = match($itgpStatus) {
                        'draft'                => 'The General Teacher is currently drafting the ITGP. You can monitor progress here.',
                        'pending_sned_review'  => 'The ITGP draft has been submitted. The SPED Teacher will add consult remarks.',
                        'ready_for_inspection' => 'SPED Teacher remarks have been submitted. The Master Teacher II will inspect and sign.',
                        'inspected'            => 'The Master Teacher has signed. The SPED Teacher will finalize and lock the record.',
                        default                => 'A General Teacher must be assigned before the ITGP draft can begin.',
                    };
                    $waitIcon = match($itgpStatus) {
                        'draft'                => 'bi-pencil-square text-warning',
                        'pending_sned_review'  => 'bi-chat-dots-fill text-info',
                        'ready_for_inspection' => 'bi-pen-fill text-primary',
                        'inspected'            => 'bi-hourglass-split text-success',
                        default                => 'bi-person-plus text-secondary',
                    };
                    ?>
                    <div class="text-center py-3 mb-4">
                        <i class="bi <?= $waitIcon ?> fs-1 d-block mb-2"></i>
                        <h5 class="fw-semibold" style="color:#1e4072;"><?= $waitTitle ?></h5>
                        <p class="text-muted small mb-0"><?= $waitDesc ?></p>
                    </div>

                    <?php if ($itgp && !empty($itgp['goal'])): ?>
                        <?php include __DIR__ . '/partials/itgp_readonly_summary.php'; ?>
                    <?php endif; ?>

                    <?php if (!empty($itgp['sned_remarks'])): ?>
                    <div class="card border-0 mt-3" style="border-radius:10px; border-left:4px solid #1e4072 !important; background:#f0f4ff;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-2" style="color:#1e4072; font-size:.88rem;"><i class="bi bi-chat-dots-fill me-2"></i>SPED Teacher Consult Remarks</h6>
                            <p class="small mb-1"><?= nl2br(htmlspecialchars($itgp['sned_remarks'])) ?></p>
                            <?php if (!empty($itgp['sned_reviewed_at'])): ?>
                                <small class="text-muted">Reviewed: <?= date('M d, Y g:i A', strtotime($itgp['sned_reviewed_at'])) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($itgp['master_signature'])): ?>
                    <div class="card border-0 mt-3 bg-light" style="border-radius:10px;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-muted text-uppercase small mb-2"><i class="bi bi-pen-fill me-1"></i>Master Teacher Signature</h6>
                            <?php if (!empty($itgp['master_teacher_recommendations'])): ?>
                                <p class="small mb-2"><strong>Recommendations:</strong><br><?= nl2br(htmlspecialchars($itgp['master_teacher_recommendations'])) ?></p>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($itgp['master_signature']) ?>" class="border rounded bg-white" style="max-width:280px; max-height:90px; object-fit:contain;" alt="Signature">
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endif; ?>

        </div><!-- /right column -->
    </div><!-- /row -->
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Scroll discussion to bottom
    var commentsBox = document.getElementById("commentsBox");
    if (commentsBox) commentsBox.scrollTop = commentsBox.scrollHeight;

    // SF9 Indicators JSON
    const sf9Indicators = <?= json_encode($sf9Indicators) ?>;

    // Category change handler
    document.addEventListener("change", function (e) {
        if (e.target && e.target.classList.contains("competency-category-select")) {
            var selectCategory = e.target;
            var row = selectCategory.closest("tr");
            var selectActivity = row.querySelector(".activity-indicator-select");
            if (selectActivity) {
                var category = selectCategory.value;
                selectActivity.innerHTML = "";
                
                if (!category) {
                    var opt = document.createElement("option");
                    opt.value = "";
                    opt.textContent = "-- Select Category First --";
                    selectActivity.appendChild(opt);
                } else {
                    var defaultOpt = document.createElement("option");
                    defaultOpt.value = "";
                    defaultOpt.textContent = "-- Select Activity --";
                    selectActivity.appendChild(defaultOpt);
                    
                    if (sf9Indicators[category]) {
                        sf9Indicators[category].forEach(function (ind) {
                            var opt = document.createElement("option");
                            opt.value = ind;
                            opt.textContent = ind;
                            selectActivity.appendChild(opt);
                        });
                    }
                }
            }
        }
    });

    // Activity rows
    var addBtn    = document.getElementById("addActivityBtn");
    var tableBody = document.querySelector("#activitiesTable tbody");
    if (addBtn && tableBody) {
        addBtn.addEventListener("click", function () {
            var idx = tableBody.querySelectorAll("tr").length;
            var tr  = document.createElement("tr");
            
            // Get template HTML and replace index placeholders
            var selectTemplate = document.getElementById("competency-select-template").innerHTML;
            var selectHtml = selectTemplate.replace(/__INDEX__/g, idx);
            
            tr.innerHTML = `
                <td>${selectHtml}</td>
                <td>
                    <select name="activities[${idx}][activities]" class="form-select form-select-sm border-0 shadow-none activity-indicator-select">
                        <option value="">-- Select Category First --</option>
                    </select>
                </td>
                <td><input type="text" name="activities[${idx}][time_frame]" class="form-control form-control-sm border-0 shadow-none" placeholder="Time Frame"></td>
                <td><input type="text" name="activities[${idx}][person_responsible]" class="form-control form-control-sm border-0 shadow-none" placeholder="Person"></td>
                <td><textarea name="activities[${idx}][remarks]" class="form-control form-control-sm border-0 shadow-none" rows="2" placeholder="Remarks"></textarea></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row-btn"><i class="bi bi-trash-fill"></i></button></td>
            `;
            tableBody.appendChild(tr);
        });
        tableBody.addEventListener("click", function (e) {
            var btn = e.target.closest(".remove-row-btn");
            if (btn) {
                var row = btn.closest("tr");
                if (tableBody.querySelectorAll("tr").length > 1) row.remove();
                else row.querySelectorAll("input,textarea,select").forEach(el => el.value = "");
            }
        });
    }

    // ── SPED Teacher Remarks Handler ──
    var sendRemarksBtn    = document.getElementById("sendRemarksBtn");
    var forwardInspectBtn = document.getElementById("forwardInspectBtn");
    function getRemarks() {
        return (document.getElementById("snedRemarksText")?.value || "").trim();
    }
    if (sendRemarksBtn) {
        sendRemarksBtn.addEventListener("click", function () {
            var remarks = getRemarks();
            if (!remarks) {
                alert("Please write your consult remarks before sending them to the General Teacher.");
                document.getElementById("snedRemarksText")?.focus();
                return;
            }
            if (!confirm("Send these remarks to the General Teacher for revision?\n\nThey will revise and resubmit the draft.")) return;
            document.getElementById("hiddenRemarksBack").value = remarks;
            document.getElementById("sendBackForm").submit();
        });
    }
    if (forwardInspectBtn) {
        forwardInspectBtn.addEventListener("click", function () {
            var remarks = getRemarks();
            var msg = remarks
                ? "Forward this ITGP to the Master Teacher for inspection with your consult remarks?"
                : "No revisions needed — forward this ITGP directly to the Master Teacher for inspection?";
            if (!confirm(msg)) return;
            document.getElementById("hiddenRemarksForward").value = remarks;
            document.getElementById("forwardInspectForm").submit();
        });
    }

    // Signature pad
    var canvas = document.getElementById("signatureCanvas");
    if (canvas) {
        var c = canvas.getContext("2d");
        var drawing = false;

        function resize() {
            var rect = canvas.getBoundingClientRect();
            var imageData = c.getImageData(0, 0, canvas.width, canvas.height);
            canvas.width  = rect.width;
            canvas.height = 160;
            c.putImageData(imageData, 0, 0);
            c.strokeStyle = "#1e3a5f";
            c.lineWidth   = 2;
            c.lineCap     = "round";
            c.lineJoin    = "round";
        }
        resize();
        window.addEventListener("resize", resize);

        function pos(e) {
            var r = canvas.getBoundingClientRect();
            return e.touches
                ? { x: e.touches[0].clientX - r.left, y: e.touches[0].clientY - r.top }
                : { x: e.clientX - r.left, y: e.clientY - r.top };
        }

        canvas.addEventListener("mousedown",  e => { drawing = true; c.beginPath(); var p=pos(e); c.moveTo(p.x, p.y); });
        canvas.addEventListener("mousemove",  e => { if (!drawing) return; var p=pos(e); c.lineTo(p.x, p.y); c.stroke(); });
        canvas.addEventListener("mouseup",    ()=> drawing = false);
        canvas.addEventListener("mouseleave", ()=> drawing = false);
        canvas.addEventListener("touchstart", e => { e.preventDefault(); drawing = true; c.beginPath(); var p=pos(e); c.moveTo(p.x, p.y); }, {passive:false});
        canvas.addEventListener("touchmove",  e => { e.preventDefault(); if (!drawing) return; var p=pos(e); c.lineTo(p.x, p.y); c.stroke(); }, {passive:false});
        canvas.addEventListener("touchend",   ()=> drawing = false);

        document.getElementById("clearSigBtn")?.addEventListener("click", () => c.clearRect(0, 0, canvas.width, canvas.height));

        document.getElementById("inspectForm")?.addEventListener("submit", function (e) {
            var isEmpty = !c.getImageData(0, 0, canvas.width, canvas.height).data.some(v => v !== 0);
            if (isEmpty) {
                e.preventDefault();
                document.getElementById("sigWarning").style.display = "block";
                canvas.style.outline = "2px solid #dc3545";
                return false;
            }
            document.getElementById("masterSignatureInput").value = canvas.toDataURL("image/png");
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
