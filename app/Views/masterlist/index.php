<?php
// Part of: SignED — Stage 2 Masterlist & LIS Sync View
// Last modified: 2026-08-21
$pageTitle = 'Learner Masterlist & Enrollment Registry — SignED';
require_once __DIR__ . '/../layouts/header.php';
?>
<style>
    .page-header-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    .action-btn-group .btn {
        height: 38px;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        padding: 0.375rem 0.875rem;
        transition: all 0.2s ease;
    }
    .table-custom {
        background: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .table-custom th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.825rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.875rem 1rem;
    }
    .table-custom td {
        padding: 0.875rem 1rem;
        font-size: 0.875rem;
        vertical-align: middle;
    }
    .badge-disability {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 500;
        border-radius: 6px;
        padding: 0.3rem 0.6rem;
        font-size: 0.775rem;
    }
    .badge-lis-status {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
    }
    .search-input {
        height: 38px;
        font-size: 0.875rem;
        border-radius: 6px;
    }
</style>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid p-0">

                <!-- Page Header Card -->
                <div class="page-header-card d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill me-2 text-warning"></i>Learner Masterlist &amp; Enrollment Registry</h4>
                        <p class="text-white-50 mb-0 small">Comprehensive Learner Enrollment Directory &amp; Attendance Records</p>
                    </div>

                    <div class="action-btn-group d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-success text-white d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                            <i class="bi bi-file-earmark-arrow-up-fill"></i> Import Enrolled Learners
                        </button>
                        <button type="button" class="btn btn-warning text-dark d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importLrnModal">
                            <i class="bi bi-cloud-arrow-up-fill"></i> Update Official LRNs
                        </button>
                        <a href="<?= $basePath ?>/masterlist/export-register" class="btn btn-primary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-excel-fill"></i> Export Enrollment Register
                        </a>
                        <a href="<?= $basePath ?>/masterlist/export-attendance" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                            <i class="bi bi-calendar-check-fill"></i> Export Attendance Register
                        </a>
                    </div>
                </div>

                <!-- Bulk Import Learners Modal -->
                <div class="modal fade" id="bulkImportModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content text-dark">
                            <form method="POST" action="<?= $basePath ?>/masterlist/bulk-import" enctype="multipart/form-data">
                                <div class="modal-header bg-success-subtle">
                                    <h6 class="modal-title fw-bold text-success-emphasis"><i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Bulk Import Enrolled Learners (Client CSV)</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted mb-3">
                                        Upload your school's existing spreadsheet of enrolled SPED learners to quickly onboard them into SignED for this school year.
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-2.5 bg-light rounded border">
                                        <div class="small">
                                            <i class="bi bi-filetype-csv text-success fs-5 me-1"></i>
                                            <span class="fw-semibold">Need a sample format?</span>
                                        </div>
                                        <a href="<?= $basePath ?>/masterlist/import-template" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-download me-1"></i> Download Template
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Select Client CSV File <span class="text-danger">*</span></label>
                                        <input type="file" name="bulk_csv" class="form-control" accept=".csv,text/csv" required>
                                    </div>
                                    <div class="alert alert-light border small text-muted mb-0">
                                        <i class="bi bi-info-circle me-1"></i> Supported headers: <code>LRN</code>, <code>Learner Name</code>, <code>Sex</code>, <code>Birth Date</code>, <code>Grade Level</code>, <code>Section</code>, <code>Disability Category</code>, <code>Parent Name</code>, <code>Parent Contact</code>.
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success btn-sm text-white fw-semibold">Upload &amp; Import Learners</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Update LRN Modal -->
                <div class="modal fade" id="importLrnModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content text-dark">
                            <form method="POST" action="<?= $basePath ?>/masterlist/import-lrn" enctype="multipart/form-data">
                                <div class="modal-header bg-warning-subtle">
                                    <h6 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Update Official Learner Reference Numbers (LRNs)</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted mb-3">
                                        Upload a CSV file containing assigned 12-digit Learner Reference Numbers (LRNs). The system will automatically map them to learner records and update registration status.
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Select CSV File with LRNs <span class="text-danger">*</span></label>
                                        <input type="file" name="lrn_csv" class="form-control" accept=".csv" required>
                                    </div>
                                    <div class="alert alert-light border small text-muted mb-0">
                                        <i class="bi bi-info-circle me-1"></i> Expected CSV columns: <code>LRN</code> (12 digits) and <code>Student ID</code> or <code>Student Name</code>.
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning btn-sm text-dark fw-semibold">Update &amp; Save LRNs</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Summary Statistics Bar -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-dark bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-dark" style="width: 46px; height: 46px;">
                                    <i class="bi bi-people-fill fs-4"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-0 text-dark"><?= (int)($totalCount ?? count($learners)) ?></h4>
                                    <small class="text-secondary fw-semibold">Total Enrolled</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-primary" style="width: 46px; height: 46px;">
                                    <i class="bi bi-laptop fs-4"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-0 text-primary"><?= (int)($candidateCount ?? $lmsCount ?? 0) ?></h4>
                                    <small class="text-secondary fw-semibold">SignED Candidates</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-success" style="width: 46px; height: 46px;">
                                    <i class="bi bi-person-workspace fs-4"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-0 text-success"><?= (int)($traditionalCount ?? 0) ?></h4>
                                    <small class="text-secondary fw-semibold">Traditional SEN Track</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-info bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-info" style="width: 46px; height: 46px;">
                                    <i class="bi bi-cloud-check-fill fs-4"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-0 text-info"><?= (int)($syncedCount ?? 0) ?></h4>
                                    <small class="text-secondary fw-semibold">Official LRN Registered</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Track Tabs -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom p-3">
                        <ul class="nav nav-pills card-header-pills gap-2">
                            <li class="nav-item">
                                <a class="nav-link <?= empty($currentTrack) ? 'active bg-dark' : 'text-dark' ?> fw-semibold py-1.5 px-3" 
                                   href="<?= $basePath ?>/masterlist?<?= http_build_query(array_merge($_GET, ['track' => ''])) ?>">
                                    <i class="bi bi-people-fill me-1"></i> All Enrolled Learners (<?= (int)($totalCount ?? count($learners)) ?>)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentTrack === 'lms' ? 'active bg-primary' : 'text-primary' ?> fw-semibold py-1.5 px-3" 
                                   href="<?= $basePath ?>/masterlist?<?= http_build_query(array_merge($_GET, ['track' => 'lms'])) ?>">
                                    <i class="bi bi-laptop me-1"></i> SignED Candidates &amp; LMS (<?= (int)($lmsCount ?? 0) ?>)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentTrack === 'traditional' ? 'active bg-success' : 'text-success' ?> fw-semibold py-1.5 px-3" 
                                   href="<?= $basePath ?>/masterlist?<?= http_build_query(array_merge($_GET, ['track' => 'traditional'])) ?>">
                                    <i class="bi bi-person-workspace me-1"></i> Traditional SEN Track (<?= (int)($traditionalCount ?? 0) ?>)
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-3">
                        <form method="GET" action="<?= $basePath ?>/masterlist" class="row g-2 align-items-center">
                            <?php if (!empty($currentTrack)): ?>
                                <input type="hidden" name="track" value="<?= htmlspecialchars($currentTrack) ?>">
                            <?php endif; ?>
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 search-input" 
                                           placeholder="Search by LRN, Student ID, or Name..." 
                                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <select name="disability" class="form-select search-input">
                                    <option value="">All Disability Classifications</option>
                                    <option value="hearing_impaired" <?= ($_GET['disability'] ?? '') === 'hearing_impaired' ? 'selected' : '' ?>>Deaf / Hard of Hearing (DHH)</option>
                                    <option value="visual" <?= ($_GET['disability'] ?? '') === 'visual' ? 'selected' : '' ?>>Visual Impairment</option>
                                    <option value="intellectual" <?= ($_GET['disability'] ?? '') === 'intellectual' ? 'selected' : '' ?>>Intellectual Disability</option>
                                    <option value="autism" <?= ($_GET['disability'] ?? '') === 'autism' ? 'selected' : '' ?>>Autism Spectrum Disorder</option>
                                    <option value="learning" <?= ($_GET['disability'] ?? '') === 'learning' ? 'selected' : '' ?>>Learning Disability</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-dark w-100 search-input d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-funnel-fill"></i> Filter
                                </button>
                                <a href="<?= $basePath ?>/masterlist" class="btn btn-outline-secondary search-input d-inline-flex align-items-center justify-content-center" title="Reset Filters">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Masterlist Table -->
                <div class="table-responsive table-custom mb-4">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Official LRN</th>
                                <th>Learner Name</th>
                                <th>Sex / Age</th>
                                <th>Grade & Section</th>
                                <th>Learning Track</th>
                                <th>Disability Category</th>
                                <th>Registry Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($learners)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                                        No learner records found matching the current search filters.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($learners as $l): ?>
                                    <?php 
                                    $hasActiveAccount = !empty($l['learner_user_id']) || ($l['learning_track'] === 'lms');
                                    $isCandidate = ($l['learning_track'] === 'candidate')
                                        || (($l['es_learning_track'] ?? '') === 'candidate')
                                        || (($l['lms_track'] ?? '') === 'candidate')
                                        || (!empty($l['survey_has_internet']) && (!empty($l['survey_willing_online']) || !empty($l['willing_digital'])))
                                        || (!empty($l['has_device']) && !empty($l['willing_digital']))
                                        || !empty($l['modality_online'])
                                        || !empty($l['modality_modular_digital']);
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($l['lrn'] ?? $l['student_id'] ?? 'Pending LRN') ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <a href="<?= $basePath ?>/students/view/<?= $l['student_record_id'] ?>" class="fw-semibold text-primary text-decoration-none" title="View Full Learner Profile">
                                                    <?= htmlspecialchars($l['student_name']) ?> <i class="bi bi-box-arrow-up-right small ms-1" style="font-size: 0.75rem;"></i>
                                                </a>
                                            </div>
                                            <div class="small text-muted">
                                                Parent: <?= htmlspecialchars($l['guardian_first_name'] ? ($l['guardian_first_name'] . ' ' . $l['guardian_last_name']) : ($l['parent_name'] ?? 'N/A')) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span><?= htmlspecialchars($l['sex'] ?? '—') ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($l['age'] ? $l['age'] . ' yrs old' : '—') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-wrap"><?= htmlspecialchars($l['grade_level_to_enroll'] ?? 'Kinder') ?></span>
                                            <small class="d-block text-muted mt-1"><?= htmlspecialchars($l['section_name'] ?? 'SPED Section A') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($hasActiveAccount): ?>
                                                <span class="badge bg-primary px-2.5 py-1.5" title="Active LMS Account">
                                                    <i class="bi bi-check2-circle me-1"></i>SignED LMS (Active)
                                                </span>
                                            <?php elseif ($isCandidate): ?>
                                                <span class="badge bg-info bg-opacity-15 text-info-emphasis border border-info px-2.5 py-1.5" title="Recommended for SignED LMS via Digital Survey">
                                                    <i class="bi bi-laptop me-1"></i>SignED Candidate
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2.5 py-1.5">
                                                    <i class="bi bi-person-workspace me-1"></i>Traditional SEN
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-disability">
                                                <i class="bi bi-person-wheelchair me-1"></i><?= htmlspecialchars($l['disability_type'] ?? 'Hearing Impaired (DHH)') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                             $isRegistered = !empty($l['lrn']) || (($l['lis_status'] ?? '') === 'synced');
                                             $badgeClass = $isRegistered ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle';
                                            ?>
                                            <span class="badge-lis-status <?= $badgeClass ?>">
                                                <i class="bi <?= $isRegistered ? 'bi-check-circle-fill' : 'bi-clock-history' ?> me-1"></i><?= $isRegistered ? 'REGISTERED' : 'PENDING LRN' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 justify-content-end flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadDocModal<?= $l['student_record_id'] ?>"
                                                        title="Upload IEP Documents, Medical Records, or Assessment Reports"
                                                        style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;">
                                                    <i class="bi bi-file-earmark-arrow-up"></i> Upload Docs
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" 
                                                        onclick="openQrInviteModal(<?= $l['student_record_id'] ?>, '<?= htmlspecialchars(addslashes($l['student_name'])) ?>', '<?= htmlspecialchars($l['claim_token'] ?? '') ?>')"
                                                        style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;">
                                                    <i class="bi bi-qr-code"></i> QR Invite
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editSectionModal<?= $l['student_record_id'] ?>"
                                                        style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;">
                                                    <i class="bi bi-pencil-square"></i> Section
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Upload Documents Modal for Learner -->
                                    <div class="modal fade text-start" id="uploadDocModal<?= $l['student_record_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                                                <form method="POST" action="<?= $basePath ?>/students/upload-document" enctype="multipart/form-data">
                                                    <input type="hidden" name="student_id" value="<?= $l['student_record_id'] ?>">
                                                    <input type="hidden" name="return_to" value="/masterlist">
                                                    <div class="modal-header border-bottom py-3">
                                                        <h5 class="modal-title fw-bold text-dark fs-6">
                                                            <i class="bi bi-file-earmark-arrow-up-fill text-primary me-2"></i>Upload Documents: <?= htmlspecialchars($l['student_name']) ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-3">
                                                        <p class="small text-muted mb-3">
                                                            Upload existing physical IEP documents, clinical medical evaluations, or assessment reports required to generate and track this learner's IEP.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small text-secondary">Document Category *</label>
                                                            <select name="document_type" class="form-select form-select-sm" required>
                                                                <option value="auto">✨ Auto-Detect Category from Filename (Recommended for Multiple Files)</option>
                                                                <option value="physical_iep">Physical / Previous Traditional IEP Document</option>
                                                                <option value="assessment_report">Clinical Assessment / Diagnostic Evaluation Report</option>
                                                                <option value="medical_record">Medical Certificate / Disability Diagnosis</option>
                                                                <option value="psa_birth_cert">PSA Birth Certificate</option>
                                                                <option value="pwd_id">Person with Disability (PWD) ID</option>
                                                                <option value="dll">Daily Lesson Log (DLL)</option>
                                                                <option value="other">DepEd SF10 / Form 137 / Other Record</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small text-secondary">Document Title <small class="text-muted fw-normal">(Optional if uploading multiple files)</small></label>
                                                            <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Previous Grade 1 IEP / Audiogram Report">
                                                        </div>
                                                        <div class="row g-2 mb-3">
                                                            <div class="col-6">
                                                                <label class="form-label fw-bold small text-secondary">School Year</label>
                                                                <input type="text" name="school_year" class="form-control form-control-sm" value="2026-2027">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label fw-bold small text-secondary">Grading Quarter</label>
                                                                <select name="quarter" class="form-select form-select-sm">
                                                                    <option value="">Baseline / Annual</option>
                                                                    <option value="Q1">Quarter 1</option>
                                                                    <option value="Q2">Quarter 2</option>
                                                                    <option value="Q3">Quarter 3</option>
                                                                    <option value="Q4">Quarter 4</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small text-secondary">File Attachment(s) * <small class="text-muted">(PDF, JPG, PNG, DOC, DOCX up to 15MB each)</small></label>
                                                            <input type="file" name="doc_files[]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple required>
                                                            <small class="text-muted d-block mt-1">
                                                                <i class="bi bi-info-circle me-1 text-primary"></i><strong>Bulk Selection:</strong> Pwede ka mo-select og <strong>daghang files dungan</strong> (pindota ang <kbd>Ctrl</kbd> o <kbd>Shift</kbd> samtang mamili og files).
                                                            </small>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label fw-bold small text-secondary">Teacher Notes / Diagnostic Notes</label>
                                                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Brief notes on findings, accommodations, or goals..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top py-2">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px; font-weight: 600;">
                                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Document(s)
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Section Modal -->
                                    <div class="modal fade" id="editSectionModal<?= $l['student_record_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="POST" action="<?= $basePath ?>/masterlist/update-section">
                                                    <input type="hidden" name="student_id" value="<?= $l['student_record_id'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Update Learner Section &amp; Registry Status</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">Learner: <strong><?= htmlspecialchars($l['student_name']) ?></strong> (LRN: <?= htmlspecialchars($l['lrn'] ?? 'N/A') ?>)</p>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Section Name</label>
                                                            <input type="text" name="section_name" class="form-control" 
                                                                   value="<?= htmlspecialchars($l['section_name'] ?? 'SPED Section A') ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Registry Status</label>
                                                            <select name="lis_status" class="form-select">
                                                                <option value="synced" <?= ($l['lis_status'] ?? '') === 'synced' ? 'selected' : '' ?>>Registered / Active LRN</option>
                                                                <option value="pending" <?= ($l['lis_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending LRN Assignment</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

    </div> <!-- End .container-fluid -->
</div> <!-- End .main-content -->

    <!-- QR Parent Invite Modal -->
    <div class="modal fade" id="qrInviteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="modal-header bg-success-subtle">
                    <h6 class="modal-title fw-bold text-success-emphasis"><i class="bi bi-qr-code-scan me-2"></i>Parent SignED Invite &amp; Activation</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <span class="badge bg-primary-subtle text-primary px-2.5 py-1 mb-1" style="font-size: 0.75rem;">Learner Invitation</span>
                        <h5 id="qrModalLearnerName" class="fw-bold text-dark mb-0">Learner Name</h5>
                    </div>

                    <!-- QR Display -->
                    <div class="d-flex justify-content-center mb-3">
                        <div class="p-2 border rounded bg-white shadow-sm" style="display:inline-block;">
                            <img id="qrModalImg" src="" alt="Parent QR Invite" style="width:200px; height:200px; display:block;">
                        </div>
                    </div>

                    <p class="small text-muted mb-3 px-3">
                        Ask the parent to scan this QR code using their smartphone camera to instantly create their parent account and connect to their child.
                    </p>

                    <!-- Direct Link Box -->
                    <div class="input-group input-group-sm mb-2 px-2">
                        <input type="text" id="qrModalLinkInput" class="form-control font-monospace" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyInviteLink()">
                            <i class="bi bi-clipboard-check me-1"></i> Copy Link
                        </button>
                    </div>
                    <small id="copySuccessMsg" class="text-success fw-semibold" style="display:none;">
                        <i class="bi bi-check-circle-fill me-1"></i> Link copied to clipboard!
                    </small>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="printQrSlip()">
                        <i class="bi bi-printer me-1"></i> Print Invite Slip
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Page Scripts -->
    <script>
    let currentClaimUrl = '';
    let currentLearnerName = '';

    function openQrInviteModal(studentId, learnerName, token) {
        currentLearnerName = learnerName;
        document.getElementById('qrModalLearnerName').textContent = learnerName;
        document.getElementById('copySuccessMsg').style.display = 'none';

        const modal = new bootstrap.Modal(document.getElementById('qrInviteModal'));

        if (token && token.trim() !== '') {
            renderQrModal(token);
            modal.show();
        } else {
            // Fetch or generate token via AJAX
            fetch('<?= $basePath ?>/masterlist/get-qr-token?student_id=' + studentId)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.token) {
                        renderQrModal(data.token);
                        modal.show();
                    } else {
                        alert('Could not generate invite token: ' + (data.error || 'Server error'));
                    }
                })
                .catch(err => {
                    alert('Connection error while generating QR token.');
                });
        }
    }

    function renderQrModal(token) {
        const protocol = window.location.protocol;
        const host = window.location.host;
        const basePath = '<?= $basePath ?>';
        currentClaimUrl = protocol + '//' + host + basePath + '/invite/claim/' + token;

        document.getElementById('qrModalLinkInput').value = currentClaimUrl;

        // Standard high-reliability QR code image generator
        const qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(currentClaimUrl);
        document.getElementById('qrModalImg').src = qrApiUrl;
    }

    function copyInviteLink() {
        const input = document.getElementById('qrModalLinkInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const msg = document.getElementById('copySuccessMsg');
            msg.style.display = 'block';
            setTimeout(() => { msg.style.display = 'none'; }, 3000);
        });
    }

    function printQrSlip() {
        const qrImg = document.getElementById('qrModalImg').src;
        const win = window.open('', '_blank', 'width=600,height=650');
        win.document.write(`
            <html>
            <head>
                <title>SignED Parent Invite - ${currentLearnerName}</title>
                <style>
                    body { font-family: sans-serif; text-align: center; padding: 30px; }
                    .slip { border: 2px dashed #0284c7; border-radius: 12px; padding: 25px; max-width: 420px; margin: 0 auto; }
                    h2 { color: #0284c7; margin-bottom: 4px; font-size: 20px; }
                    .subtitle { color: #475569; font-size: 14px; margin-bottom: 12px; }
                    .qr { margin: 15px 0; }
                    .link { word-break: break-all; font-family: monospace; font-size: 12px; color: #64748b; background: #f8fafc; padding: 6px; border-radius: 4px; }
                </style>
            </head>
            <body>
                <div class="slip">
                    <h2>SignED SPED LMS</h2>
                    <div class="subtitle">Parent / Guardian Portal Invitation</div>
                    <hr style="border:0; border-top:1px solid #cbd5e1; margin:12px 0;">
                    <p style="margin:6px 0;">Learner: <strong>${currentLearnerName}</strong></p>
                    <div class="qr"><img src="${qrImg}" width="200" height="200" style="border:1px solid #e2e8f0; border-radius:8px; padding:4px;"></div>
                    <p style="font-size:13px; color:#334155; margin-bottom:10px;">Scan with your smartphone camera to connect to your child's digital learning account.</p>
                    <p class="link">${currentClaimUrl}</p>
                </div>
                <script>window.onload = function() { window.print(); }<\/script>
            </body>
            </html>
        `);
        win.document.close();
    }
    </script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

