<?php
// Part of: SignED — Parent Printable Worksheets Hub
$pageTitle = 'Printable Worksheets & Gawaing Papel — SignED';
require_once __DIR__ . '/../layouts/header.php';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
?>

<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4" style="max-width: 1200px;">

        <!-- Breadcrumbs & Header -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="<?= $basePath ?>/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Printable Worksheets</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="mb-1 fw-bold text-dark fs-3 d-flex align-items-center gap-2">
                    <i class="bi bi-printer-fill text-primary"></i> Printable Worksheets &amp; Gawaing Papel
                </h1>
                <p class="text-muted small mb-0">
                    I-download o i-print ang mga worksheets nga gi-assign sa SPED Teacher aron masagutan sa balay.
                </p>
            </div>
            <a href="<?= $basePath ?>/dashboard" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px; font-weight: 500;">
                <i class="bi bi-arrow-left me-1"></i> Balik sa Dashboard
            </a>
        </div>

        <!-- Flash alerts -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- How It Works Guide Banner -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: #fff;">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <span class="badge bg-white text-teal mb-2 px-2.5 py-1 fw-bold" style="color: #0f766e; font-size: 0.75rem;">
                            <i class="bi bi-info-circle-fill me-1"></i> PARENT GUIDE (Giya sa Ginikanan)
                        </span>
                        <h5 class="fw-bold mb-1 text-white">Unsaon Paggamit sa Printable Worksheets?</h5>
                        <p class="text-white-50 small mb-0">
                            1. I-click ang <strong>"Download PDF"</strong> o <strong>"Print"</strong> aron ma-imprinta ang worksheet.<br>
                            2. Pasaguti kini sa bata gamit ang bolpen, lapis, o krayola sa balay.<br>
                            3. Pagkahuman, piktyuri ang papel gamit ang imong cellphone ug i-click ang <strong>"I-upload ang Nahuman"</strong> aron ma-check sa teacher.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end text-center">
                        <i class="bi bi-file-earmark-pdf-fill display-4 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($allWorksheets)): ?>
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <div class="card-body">
                    <i class="bi bi-printer text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">Walay Assigned nga Worksheets sa Pagkakaron</h5>
                    <p class="text-muted small mb-0">
                        Kung mag-upload ang SPED teacher og printable worksheet o gawaing papel, makita ug ma-download dayon kini dinhi.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($allWorksheets as $ws): 
                    $actData = json_decode($ws['activity_data'] ?? '{}', true) ?: [];
                    $filePath = !empty($actData['file_path']) ? ltrim($actData['file_path'], '/') : '';
                    $fileUrl = $filePath ? ($basePath . '/' . $filePath) : '';
                    $fileName = $actData['file_name'] ?? (basename($filePath) ?: 'Worksheet Material');
                    $isSubmitted = !empty($ws['submission_id']);
                ?>
                <div class="col-lg-6 col-12">
                    <div class="card border-0 shadow-sm rounded-4 h-100" style="border: 1px solid #e2e8f0 !important; border-left: 5px solid #0d9488 !important;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary fw-semibold px-2.5 py-1 mb-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($ws['student_name']) ?>
                                        </span>
                                        <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 mb-1" style="font-size: 0.72rem;">
                                            <?= htmlspecialchars($ws['section_name'] ?? 'SPED') ?>
                                        </span>
                                    </div>
                                    <?php if ($isSubmitted): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5" style="font-size: 0.78rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i> Na-submit Na
                                            <?php if ($ws['score'] !== null): ?>
                                                (Score: <?= (int)$ws['score'] ?>/<?= (int)$ws['max_score'] ?>)
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1.5" style="font-size: 0.78rem;">
                                            <i class="bi bi-hourglass-split me-1"></i> Wala Pa Masaguti (Pending)
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($ws['activity_title']) ?></h5>
                                <div class="small text-muted mb-3">
                                    <i class="bi bi-journal-bookmark text-primary me-1"></i> <strong>Aralin:</strong> <?= htmlspecialchars($ws['lesson_title']) ?>
                                    &bull; <span class="badge bg-light text-dark border"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $ws['pdsp_domain'] ?? 'General'))) ?></span>
                                </div>

                                <?php if (!empty($ws['instructions'])): ?>
                                    <div class="p-3 bg-light rounded-3 small text-secondary mb-3 border">
                                        <strong class="d-block text-dark mb-1"><i class="bi bi-info-circle me-1"></i> Tagubilin sa Guro:</strong>
                                        <?= nl2br(htmlspecialchars(strip_tags($ws['instructions']))) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Target file info box -->
                                <div class="p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #f0fdfa; border: 1px dashed #0d9488;">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <i class="bi bi-file-earmark-pdf-fill fs-2 text-danger"></i>
                                        <div>
                                            <strong class="text-dark d-block small"><?= htmlspecialchars($fileName) ?></strong>
                                            <small class="text-muted">Printable Worksheet Document</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?php if ($fileUrl): ?>
                                            <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-weight: 500;">
                                                <i class="bi bi-eye me-1"></i> View
                                            </a>
                                            <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank" download class="btn btn-sm text-white" style="background:#0d9488; border-radius: 6px; font-weight: 600;">
                                                <i class="bi bi-download me-1"></i> Download PDF
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($isSubmitted && !empty($ws['submitted_file'])): ?>
                                    <div class="p-2.5 bg-white rounded-3 border mb-3 small d-flex align-items-center justify-content-between">
                                        <span class="text-muted">
                                            <i class="bi bi-paperclip me-1 text-success"></i> Imohang Gi-upload nga File:
                                        </span>
                                        <a href="<?= $basePath ?>/<?= htmlspecialchars(ltrim($ws['submitted_file'], '/')) ?>" target="_blank" class="btn btn-xs btn-outline-success py-1 px-2" style="font-size: 0.75rem;">
                                            <i class="bi bi-image me-1"></i> Tan-awa ang Gi-upload
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Upload submission button -->
                            <div class="pt-3 border-top mt-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i> Assigned: <?= date('M d, Y', strtotime($ws['created_at'])) ?>
                                </small>
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#uploadWorksheetModal<?= $ws['activity_id'] ?>" 
                                        style="border-radius: 6px; font-weight: 500;">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> <?= $isSubmitted ? 'I-upload Pag-usab (Re-upload)' : 'I-upload ang Nahuman nga Worksheet' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Modal for this worksheet -->
                <div class="modal fade" id="uploadWorksheetModal<?= $ws['activity_id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <form action="<?= $basePath ?>/parent/submit-worksheet" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="activity_id" value="<?= (int)$ws['activity_id'] ?>">
                                <input type="hidden" name="student_id" value="<?= (int)$ws['student_id'] ?>">

                                <div class="modal-header border-0 pb-0 pt-4 px-4">
                                    <h5 class="modal-title fw-bold text-dark" style="font-size: 1.15rem;">
                                        <i class="bi bi-cloud-arrow-up text-success me-2"></i>I-upload ang Nahuman nga Worksheet
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="small text-muted mb-3">
                                        Learner: <strong><?= htmlspecialchars($ws['student_name']) ?></strong><br>
                                        Worksheet: <strong><?= htmlspecialchars($ws['activity_title']) ?></strong> (<?= htmlspecialchars($fileName) ?>)
                                    </p>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">
                                            Litrato o Scan sa Papel (Photo / PDF) <span class="text-danger">*</span>:
                                        </label>
                                        <input type="file" name="submission_file" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            Dawaton: JPG, PNG, o PDF gikan sa imong camera o scan.
                                        </small>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label fw-bold small text-secondary">Mensahe / Pahayag para sa Guro (Optional):</label>
                                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Pananglitan: Nahuman na ni Juan ang tanang items sa worksheet..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 6px;">Kanselahon</button>
                                    <button type="submit" class="btn btn-success px-3 py-2" style="border-radius: 6px; font-weight: 600;">
                                        <i class="bi bi-check2-circle me-1"></i> I-submit ngadto sa Guro
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
