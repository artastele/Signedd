<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5 (SIMPLIFIED)
// Last modified: 2026-05-13
// Part of: SPED LMS — IEP Form (Upload Only System)

$pageTitle = 'IEP — Individualized Education Plan';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1" style="color:#1e4072;">
                <i class="bi bi-file-earmark-medical me-2"></i>IEP — Individualized Education Plan
            </h1>
            <p class="text-muted mb-1">
                <strong><?= htmlspecialchars($studentData['student_name'] ?? 'Unknown Student') ?></strong>
                <?php if (!empty($studentData['lrn'])): ?>
                    &nbsp;·&nbsp; LRN: <?= htmlspecialchars($studentData['lrn']) ?>
                <?php endif; ?>
            </p>
            <span class="badge me-1" style="background:#1e4072;"><?= htmlspecialchars($iep['school_year']) ?></span>
            <?php
            $statusStyle = match($iep['status']) {
                'draft'  => 'background:#6c757d;',
                'signed' => 'background:#3b6d11;',
                'locked' => 'background:#a01422;',
                default  => 'background:#6c757d;'
            };
            $statusLabel = match($iep['status']) {
                'draft'  => 'Draft',
                'signed' => 'Signed',
                'locked' => 'Locked',
                default  => ucfirst($iep['status'])
            };
            ?>
            <span class="badge" style="<?= $statusStyle ?>"><?= $statusLabel ?></span>
        </div>
        <a href="<?= $basePath ?>/iep" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Repository
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['iep_errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($_SESSION['iep_errors'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['iep_errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Re-evaluation Passed Banner -->
    <?php if (!empty($canStartNewCycle)): ?>
        <div class="alert d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#fff3cd;border-left:4px solid #a01422;">
            <div>
                <i class="bi bi-exclamation-triangle-fill me-2" style="color:#a01422;"></i>
                <strong>Re-evaluation date has passed.</strong> Ready to start a new IEP cycle?
            </div>
            <form method="POST" action="<?= $basePath ?>/iep/new-cycle" class="d-inline">
                <input type="hidden" name="iep_id" value="<?= $iep['id'] ?>">
                <button type="submit" class="btn btn-sm"
                        style="background:#a01422;color:white;"
                        onclick="return confirm('Start new IEP cycle? The current IEP will be preserved.')">
                    <i class="bi bi-arrow-repeat me-1"></i>Start New Cycle
                </button>
            </form>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ===== LEFT COLUMN — Main Form ===== -->
        <div class="col-lg-8">

            <form id="iepForm" method="POST" action="<?= $basePath ?>/iep/submitIEP">
                <input type="hidden" name="iep_id" value="<?= $iep['id'] ?>">

                <!-- Card 1: IEP Document Upload -->
                <div class="card mb-4" style="border-left:4px solid #a01422;">
                    <div class="card-header" style="background:#a01422;color:white;">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Step 1 — Upload IEP Document</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Upload the completed and signed IEP document. Accepted: JPG, PNG, PDF · Max 10MB
                        </div>

                        <?php if (!empty($iep['signed_document_path'])): ?>
                            <!-- Document already uploaded -->
                            <div class="d-flex align-items-center justify-content-between p-3 rounded"
                                 style="background:#f0fdf4;border:1px solid #3b6d11;">
                                <div class="d-flex align-items-center gap-3">
                                    <?php
                                    $ext = strtolower(pathinfo($iep['signed_document_path'], PATHINFO_EXTENSION));
                                    $iconClass = $ext === 'pdf' ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-image text-primary';
                                    ?>
                                    <i class="bi <?= $iconClass ?> fs-3"></i>
                                    <div>
                                        <div class="fw-semibold" style="color:#3b6d11;">
                                            <i class="bi bi-check-circle-fill me-1"></i>Document Uploaded
                                        </div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(basename($iep['signed_document_path'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php if (in_array($ext, ['jpg','jpeg','png','pdf'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="viewDocument('<?= $basePath ?>/<?= htmlspecialchars($iep['signed_document_path']) ?>', '<?= $ext ?>')">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
                                    <?php endif; ?>
                                    <?php if (in_array($userRole, ['sped_teacher','guidance','principal','admin'])): ?>
                                        <a href="<?= $basePath ?>/iep/download/<?= $iep['id'] ?>"
                                           class="btn btn-sm" style="background:#1e4072;color:white;">
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$readOnly): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="replaceDocument()">
                                            <i class="bi bi-arrow-repeat me-1"></i>Replace
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Hidden replace zone (shown on Replace click) -->
                            <div id="replaceZone" class="mt-3" style="display:none;">
                                <?php
                                $fieldName     = 'iep_document';
                                $acceptedTypes = '.jpg,.jpeg,.png,.pdf';
                                $maxSize       = 10;
                                $showCamera    = true;
                                $uploadUrl     = $basePath . '/iep/upload-signed-doc';
                                $additionalData = ['iep_id' => $iep['id']];
                                include __DIR__ . '/../components/upload-zone.php';
                                ?>
                            </div>

                        <?php elseif (!$readOnly): ?>
                            <!-- Upload zone (no document yet) -->
                            <?php
                            $fieldName      = 'iep_document';
                            $acceptedTypes  = '.jpg,.jpeg,.png,.pdf';
                            $maxSize        = 10;
                            $showCamera     = true;
                            $uploadUrl      = $basePath . '/iep/upload-signed-doc';
                            $additionalData = ['iep_id' => $iep['id']];
                            include __DIR__ . '/../components/upload-zone.php';
                            ?>

                        <?php else: ?>
                            <!-- Read-only, no document -->
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-file-earmark-x fs-1 mb-2 d-block"></i>
                                No document uploaded yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card 2: Re-evaluation Date -->
                <div class="card mb-4" style="border-left:4px solid #1e4072;">
                    <div class="card-header" style="background:#1e4072;color:white;">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Step 2 — Re-evaluation Date</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$readOnly): ?>
                            <label class="form-label fw-medium">When should this IEP be re-evaluated?</label>
                            <input type="date"
                                   class="form-control"
                                   name="re_evaluation_date"
                                   value="<?= htmlspecialchars($iep['re_evaluation_date'] ?? '') ?>"
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                   required>
                            <small class="text-muted">Required. Must be a future date.</small>
                        <?php else: ?>
                            <p class="mb-0 fw-medium">
                                <i class="bi bi-calendar-check me-2" style="color:#1e4072;"></i>
                                <?= !empty($iep['re_evaluation_date'])
                                    ? date('F j, Y', strtotime($iep['re_evaluation_date']))
                                    : '<span class="text-muted">Not set</span>' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card 3: Signatories -->
                <div class="card mb-4" style="border-left:4px solid #1e4072;">
                    <div class="card-header" style="background:#1e4072;color:white;">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Step 3 — Signatories (Face to Face)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$readOnly): ?>
                            <p class="text-muted small mb-3">
                                Check each person who signed the IEP document face-to-face and enter their full name.
                                At least one signatory is required.
                            </p>
                        <?php endif; ?>

                        <?php
                        $signatoryRoles = [
                            'parent_guardian'    => 'Parents / Guardian',
                            'guidance_counselor' => 'Guidance Counselor / Teacher',
                            'teacher'            => 'Teacher/s',
                            'sned_teacher'       => 'SNEd Teacher',
                            'school_head'        => 'School Head',
                            'ilrc_supervisor'    => 'ILRC Supervisor',
                        ];
                        $signatoryLookup = [];
                        if (!empty($signatories)) {
                            foreach ($signatories as $sig) {
                                $signatoryLookup[$sig['signatory_role']] = $sig['signatory_name'];
                            }
                        }
                        ?>

                        <div class="row g-3">
                            <?php foreach ($signatoryRoles as $roleKey => $roleLabel): ?>
                                <div class="col-md-6">
                                    <div class="card h-100" style="border:1px solid #dee2e6;">
                                        <div class="card-body py-3">
                                            <?php if (!$readOnly): ?>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input signatory-checkbox"
                                                           type="checkbox"
                                                           name="signatory_<?= $roleKey ?>"
                                                           id="sig_<?= $roleKey ?>"
                                                           value="1"
                                                           <?= isset($signatoryLookup[$roleKey]) ? 'checked' : '' ?>>
                                                    <label class="form-check-label fw-semibold" for="sig_<?= $roleKey ?>">
                                                        <?= htmlspecialchars($roleLabel) ?>
                                                    </label>
                                                </div>
                                                <input type="text"
                                                       class="form-control form-control-sm signatory-name"
                                                       name="signatory_name_<?= $roleKey ?>"
                                                       placeholder="Full name"
                                                       value="<?= htmlspecialchars($signatoryLookup[$roleKey] ?? '') ?>"
                                                       <?= isset($signatoryLookup[$roleKey]) ? '' : 'disabled' ?>>
                                            <?php else: ?>
                                                <?php if (isset($signatoryLookup[$roleKey])): ?>
                                                    <div class="d-flex align-items-start gap-2">
                                                        <i class="bi bi-check-circle-fill mt-1" style="color:#3b6d11;"></i>
                                                        <div>
                                                            <div class="fw-semibold small text-muted"><?= htmlspecialchars($roleLabel) ?></div>
                                                            <div><?= htmlspecialchars($signatoryLookup[$roleKey]) ?></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-start gap-2 text-muted">
                                                        <i class="bi bi-dash-circle mt-1"></i>
                                                        <div>
                                                            <div class="fw-semibold small"><?= htmlspecialchars($roleLabel) ?></div>
                                                            <div class="small">Not signed</div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Submit / Status -->
                <?php if (!$readOnly && $iep['status'] === 'draft'): ?>
                    <div class="card" style="border-left:4px solid #3b6d11;">
                        <div class="card-body text-center py-4">
                            <button type="submit" id="submitBtn" class="btn btn-lg px-5"
                                    style="background:#a01422;border-color:#a01422;color:white;">
                                <i class="bi bi-check-circle me-2"></i>Submit & Lock IEP
                            </button>
                            <p class="text-muted small mt-2 mb-0">
                                Once submitted, this IEP will be locked until the re-evaluation date.
                            </p>
                        </div>
                    </div>
                <?php elseif (in_array($iep['status'], ['signed','locked'])): ?>
                    <div class="card" style="border-left:4px solid #3b6d11;">
                        <div class="card-body text-center py-4">
                            <span class="badge fs-6 px-4 py-2" style="background:#3b6d11;">
                                <i class="bi bi-check-circle-fill me-2"></i>IEP Submitted &amp; Locked
                            </span>
                            <?php if (!empty($iep['signed_at'])): ?>
                                <p class="text-muted small mt-2 mb-0">
                                    Submitted on <?= date('F j, Y g:i A', strtotime($iep['signed_at'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </form>
        </div>

        <!-- ===== RIGHT COLUMN — IEP History ===== -->
        <div class="col-lg-4">
            <div class="card" style="border-left:4px solid #1e4072;">
                <div class="card-header" style="background:#1e4072;color:white;">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>IEP History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($studentIEPs)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($studentIEPs as $h): ?>
                                <?php
                                $isCurrent = ($h['id'] == $iep['id']);
                                $hStatusStyle = match($h['status']) {
                                    'draft'  => 'background:#6c757d;',
                                    'signed' => 'background:#3b6d11;',
                                    'locked' => 'background:#a01422;',
                                    default  => 'background:#6c757d;'
                                };
                                ?>
                                <div class="list-group-item <?= $isCurrent ? '' : '' ?>"
                                     style="<?= $isCurrent ? 'background:#fff5f5;border-left:3px solid #a01422;' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge" style="background:#1e4072;">
                                            <?= htmlspecialchars($h['school_year']) ?>
                                        </span>
                                        <span class="badge" style="<?= $hStatusStyle ?>">
                                            <?= ucfirst($h['status']) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($h['re_evaluation_date'])): ?>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-calendar2 me-1"></i>Re-eval: <?= date('M j, Y', strtotime($h['re_evaluation_date'])) ?>
                                        </small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($h['drafted_by_name']) ?>
                                    </small>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-clock me-1"></i><?= date('M j, Y', strtotime($h['created_at'])) ?>
                                    </small>
                                    <?php if ($isCurrent): ?>
                                        <span class="badge" style="background:#a01422;font-size:0.7rem;">
                                            Current
                                        </span>
                                    <?php else: ?>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <a href="<?= $basePath ?>/iep/form/<?= $h['id'] ?>"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                            <?php if (!empty($h['signed_document_path']) && in_array($userRole, ['sped_teacher','guidance','principal','admin'])): ?>
                                                <a href="<?= $basePath ?>/iep/download/<?= $h['id'] ?>"
                                                   class="btn btn-sm" style="background:#1e4072;color:white;">
                                                    <i class="bi bi-download me-1"></i>Download
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                            No IEP history yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /row -->
</div><!-- /container-fluid -->
</div><!-- /main-content -->

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:white;">
                <h5 class="modal-title" id="documentModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>IEP Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="min-height:500px;">
                <div id="documentViewer" class="text-center p-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── After AJAX upload succeeds, reload to show the uploaded state ──
    // The upload-zone component fires a custom event on success
    document.addEventListener('uploadSuccess', function (e) {
        // Reload the page so the "Document Uploaded" block renders from DB
        window.location.reload();
    });

    // ── Signatory checkbox toggle ──────────────────────────────
    document.querySelectorAll('.signatory-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const nameInput = this.closest('.card-body').querySelector('.signatory-name');
            if (!nameInput) return;
            nameInput.disabled = !this.checked;
            if (!this.checked) nameInput.value = '';
            else nameInput.focus();
        });
    });

    // ── Form submit validation ─────────────────────────────────
    const form = document.getElementById('iepForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const errors = [];

            // 1. Document check — upload is AJAX, so check if already saved in DB
            const hasExisting = <?= !empty($iep['signed_document_path']) ? 'true' : 'false' ?>;
            if (!hasExisting) {
                errors.push('Please upload the IEP document first using the upload zone above.');
            }

            // 2. Re-evaluation date
            const reEval = document.querySelector('input[name="re_evaluation_date"]');
            if (reEval && !reEval.value) {
                errors.push('Please select a re-evaluation date.');
            }

            // 3. At least one signatory with a name
            const checked = document.querySelectorAll('.signatory-checkbox:checked');
            let hasValidSig = false;
            checked.forEach(function (cb) {
                const nameInput = cb.closest('.card-body').querySelector('.signatory-name');
                if (nameInput && nameInput.value.trim()) hasValidSig = true;
            });
            if (!hasValidSig) {
                errors.push('Please select at least one signatory and enter their name.');
            }

            if (errors.length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Please fix the following',
                    html: '<ul class="text-start mb-0">' + errors.map(function(e){ return '<li>' + e + '</li>'; }).join('') + '</ul>',
                    confirmButtonColor: '#a01422'
                });
                return false;
            }

            // Confirmation
            e.preventDefault();
            Swal.fire({
                title: 'Submit & Lock IEP?',
                text: 'This IEP will be locked until the re-evaluation date. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#a01422',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});

// ── Replace document toggle ────────────────────────────────────
function replaceDocument() {
    const zone = document.getElementById('replaceZone');
    if (zone) {
        zone.style.display = zone.style.display === 'none' ? 'block' : 'none';
    }
}

// ── Document viewer modal ──────────────────────────────────────
function viewDocument(path, type) {
    const viewer = document.getElementById('documentViewer');
    if (!viewer) return;

    if (type === 'pdf') {
        viewer.innerHTML = '<embed src="' + path + '" type="application/pdf" width="100%" height="600px">';
    } else {
        viewer.innerHTML = '<img src="' + path + '" class="img-fluid" alt="IEP Document" style="max-height:80vh;">';
    }

    new bootstrap.Modal(document.getElementById('documentModal')).show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
