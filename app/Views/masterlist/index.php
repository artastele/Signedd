<?php
// DO NOT ALTER WITHOUT APPROVAL — Masterlist & Registry View
// Part of: SignED — Stage 2 Masterlist & LIS Sync View
// Security check: RoleMiddleware checked in controller
$pageTitle = ($role === 'admin') ? 'Enrollment Registry Metrics — SignED' : 'Learner Masterlist & Enrollment Registry — SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .page-header-card {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%);
        color: #ffffff;
        border-radius: 10px;
        padding: 22px 26px;
        margin-bottom: 24px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }
    .badge-lis-status {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }
    .badge-disability {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        background-color: #f0f4f8;
        color: #2d3748;
        border: 1px solid #e2e8f0;
    }
    .table-custom {
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .table-custom thead {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-custom thead th {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        padding: 12px 14px;
    }
    .table-custom tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease-in-out;
    }
    .table-custom tbody tr:hover {
        background: #f8fafc;
    }
    .table-custom tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    .search-input {
        height: 38px;
        font-size: 0.875rem;
        border-radius: 6px;
    }
    .section-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
    }
    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .section-card.active-section {
        border: 2px solid #0d6efd !important;
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.15) !important;
        background: #f8fbff !important;
    }
    .progress-slim {
        height: 6px;
        border-radius: 3px;
    }
</style>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid p-0">

        <?php if ($role === 'admin'): ?>
            <!-- ============================================================== -->
            <!-- SYSTEM ADMINISTRATOR VIEW: METRICS ONLY (NO PII / STUDENT INFO) -->
            <!-- ============================================================== -->

            <!-- Header Card for Admin -->
            <div class="page-header-card d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-fill me-2 text-warning"></i>Learner Enrollment Registry &amp; Modality Analytics</h4>
                    <p class="text-white-50 mb-0 small">System-Wide Aggregate Statistics &amp; SPED Track Distribution Across All Schools</p>
                </div>
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 fw-semibold">
                        <i class="bi bi-shield-lock-fill me-1"></i> System Administrator Executive View
                    </span>
                </div>
            </div>

            <!-- Data Privacy Alert Banner -->
            <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center gap-3 mb-4 rounded-3" style="background: #f8fafc; border-left: 5px solid #1e4072 !important;">
                <i class="bi bi-shield-check fs-2 text-primary"></i>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Data Privacy &amp; Role-Scope Governance</h6>
                    <p class="mb-0 small text-muted">
                        In strict compliance with DepEd data privacy guidelines and Process Governance, individual student records, names, LRNs, and PII are restricted to school-level personnel (School Principals and SPED Teachers). Macro-level statistical metrics across all registered schools are summarized below.
                    </p>
                </div>
            </div>

            <!-- Summary Statistics Bar (Global System-Wide) -->
            <div class="row g-3 mb-4 m-grid">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1e4072 !important;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-dark bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-dark" style="width: 48px; height: 48px;">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalCount) ?></h3>
                                <small class="text-secondary fw-semibold">Total Enrolled (All Schools)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px;">
                                <i class="bi bi-laptop fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-primary"><?= number_format($lmsCount) ?></h3>
                                <small class="text-secondary fw-semibold">SignED Candidates &amp; LMS</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-success" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-workspace fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-success"><?= number_format($traditionalCount) ?></h3>
                                <small class="text-secondary fw-semibold">Traditional SEN Track</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-info bg-opacity-10 p-2.5 d-flex align-items-center justify-content-center text-info" style="width: 48px; height: 48px;">
                                <i class="bi bi-cloud-check-fill fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-info"><?= number_format($syncedCount) ?></h3>
                                <small class="text-secondary fw-semibold">Official LRNs Registered</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Macro Analytics Grid: Track Distribution & Disability Categories -->
            <div class="row g-3 mb-4">
                <!-- Track Distribution Card -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Learning Track &amp; Modality Adoption</h6>
                        </div>
                        <div class="card-body p-4">
                            <?php 
                            $lmsPct = $totalCount > 0 ? round(($lmsCount / $totalCount) * 100, 1) : 0;
                            $tradPct = $totalCount > 0 ? round(($traditionalCount / $totalCount) * 100, 1) : 0;
                            ?>
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <span class="fw-semibold text-dark">SignED LMS &amp; Candidates</span>
                                <span class="fw-bold text-primary"><?= $lmsPct ?>% (<?= number_format($lmsCount) ?>)</span>
                            </div>
                            <div class="progress mb-4" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $lmsPct ?>%;"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <span class="fw-semibold text-dark">Traditional SEN Classroom Track</span>
                                <span class="fw-bold text-success"><?= $tradPct ?>% (<?= number_format($traditionalCount) ?>)</span>
                            </div>
                            <div class="progress mb-4" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $tradPct ?>%;"></div>
                            </div>

                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Official LRN Registry Rate:</span>
                                    <strong class="text-dark"><?= $totalCount > 0 ? round(($syncedCount / $totalCount) * 100, 1) : 0 ?>%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Disability Demographics Card -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-wheelchair text-info me-2"></i>SPED Disability Category Demographics</h6>
                        </div>
                        <div class="card-body p-3">
                            <?php if (empty($adminMetrics['disability_breakdown'])): ?>
                                <p class="text-muted small text-center my-4">No disability demographic records found.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($adminMetrics['disability_breakdown'] as $dis): ?>
                                        <?php 
                                        $dCount = (int)$dis['count'];
                                        $dPct = $totalCount > 0 ? round(($dCount / $totalCount) * 100, 1) : 0;
                                        ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
                                            <span class="small fw-semibold text-dark"><?= htmlspecialchars($dis['category']) ?></span>
                                            <span class="badge bg-light text-dark border px-2.5 py-1.5 font-monospace"><?= $dCount ?> (<?= $dPct ?>%)</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- School-by-School Enrollment Distribution Table (Pure Metrics Only - NO Student Records) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-building-fill text-secondary me-2"></i>School-by-School Enrollment Registry Breakdown
                    </h6>
                    <span class="badge bg-secondary-subtle text-secondary small">Statistical Aggregate</span>
                </div>
                <div class="table-responsive table-custom mb-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>School Name</th>
                                <th>DepEd ID</th>
                                <th>Division / Region</th>
                                <th class="text-center">Total Enrolled</th>
                                <th class="text-center">SignED LMS</th>
                                <th class="text-center">Traditional SEN</th>
                                <th class="text-center">LRN Synced</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($adminMetrics['school_breakdown'])): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No school enrollment records available.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($adminMetrics['school_breakdown'] as $sch): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($sch['school_name'] ?? 'SPED Center') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($sch['deped_id'] ?? '—') ?></span>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><?= htmlspecialchars($sch['division'] ?? '—') ?>, <?= htmlspecialchars($sch['region'] ?? '—') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold text-dark fs-6"><?= number_format($sch['enrolled_count']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary px-2.5 py-1.5"><?= number_format($sch['lms_count']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success px-2.5 py-1.5"><?= number_format($sch['traditional_count']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info bg-opacity-15 text-info-emphasis border border-info px-2.5 py-1.5">
                                                <?= number_format($sch['lrn_synced_count']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- ============================================================== -->
            <!-- SCHOOL STAFF VIEW: PRINCIPAL / MASTER TEACHER / SPED TEACHER  -->
            <!-- ============================================================== -->

            <!-- Page Header Card -->
            <div class="page-header-card d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-people-fill me-2 text-warning"></i>Learner Masterlist &amp; Enrollment Registry</h4>
                    <p class="text-white-50 mb-0 small">Comprehensive Learner Enrollment Directory &amp; Attendance Records</p>
                </div>

                <?php if ($role !== 'principal' && $role !== 'admin'): ?>
                <div class="action-btn-group d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-light text-white d-inline-flex align-items-center gap-2" onclick="copyUniversalGcLink()" title="Copy Universal Link for Messenger Group Chat">
                        <i class="bi bi-chat-heart-fill text-warning"></i> Copy GC Invite Link
                    </button>
                    <button type="button" class="btn btn-success text-white d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i> Import Enrolled Learners
                    </button>
                    <button type="button" class="btn btn-warning text-dark d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importLrnModal">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Update Official LRNs
                    </button>
                    <a href="<?= $basePath ?>/masterlist/export-register" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-excel-fill"></i> Export Register
                    </a>
                    <a href="<?= $basePath ?>/masterlist/export-attendance" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                        <i class="bi bi-calendar-check-fill"></i> Export Attendance
                    </a>
                </div>
                <?php endif; ?>
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

                                <?php if ($role === 'sped_teacher'): ?>
                                    <?php if (!empty($teacherSection)): ?>
                                        <div class="alert alert-primary border-0 small mb-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                                            <div>
                                                Target Advisory Section: <strong><?= htmlspecialchars($teacherSection['section_name']) ?></strong>
                                                <div class="text-muted" style="font-size: 0.75rem;">Imported learners will automatically be assigned to your designated classroom section.</div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning border-0 small mb-3">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            <strong>Notice:</strong> You are not currently assigned as an adviser to an active section. Please coordinate with your School Principal.
                                        </div>
                                    <?php endif; ?>
                                <?php elseif (in_array($role, ['principal', 'master_teacher']) && !empty($allActiveSections)): ?>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Target Section (Optional)</label>
                                        <select name="target_section_id" class="form-select form-select-sm">
                                            <option value="">-- Auto-Assign / Existing School Sections --</option>
                                            <?php foreach ($allActiveSections as $sec): ?>
                                                <option value="<?= $sec['id'] ?>">
                                                    <?= htmlspecialchars($sec['section_name']) ?> (<?= htmlspecialchars($sec['adviser_name'] ?? 'Unassigned') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text" style="font-size: 0.75rem;">If unspecified, learners will be matched against existing active sections.</div>
                                    </div>
                                <?php endif; ?>

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
                                    <i class="bi bi-info-circle me-1"></i> Supported headers: <code>LRN</code>, <code>Learner Name</code>, <code>Sex</code>, <code>Birth Date</code>, <code>Grade Level</code>, <code>Disability Category</code>, <code>Parent Name</code>, <code>Parent Contact</code>.
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
            <div class="row g-3 mb-4 m-grid">
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
                                <h4 class="fw-bold mb-0 text-primary"><?= (int)($lmsCount ?? ($candidateCount + $lmsActiveCount)) ?></h4>
                                <small class="text-secondary fw-semibold">SignED Candidates &amp; LMS</small>
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

            <?php
            // Group learners into sections for Principal and Master Teachers
            $sectionGroups = [];
            $unassignedLearners = [];

            if (!empty($allActiveSections)) {
                foreach ($allActiveSections as $sec) {
                    $sectionGroups[$sec['id']] = [
                        'section' => $sec,
                        'learners' => []
                    ];
                }
            }

            foreach ($learners as $l) {
                $secId = !empty($l['section_id']) ? (int)$l['section_id'] : null;
                if ($secId && isset($sectionGroups[$secId])) {
                    $sectionGroups[$secId]['learners'][] = $l;
                } else {
                    $matched = false;
                    if (!empty($l['section_name'])) {
                        foreach ($sectionGroups as $sid => &$grp) {
                            if (strcasecmp(trim($grp['section']['section_name']), trim($l['section_name'])) === 0) {
                                $grp['learners'][] = $l;
                                $matched = true;
                                break;
                            }
                        }
                        unset($grp);
                    }
                    if (!$matched) {
                        $unassignedLearners[] = $l;
                    }
                }
            }
            ?>

            <!-- ============================================================== -->
            <!-- SECTION CARDS GRID (PINA-SECTION STYLE FROM SECTIONS/INDEX.PHP)-->
            <!-- ============================================================== -->

            <?php if (in_array($role, ['principal', 'master_teacher']) && !empty($sectionGroups)): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-grid-3x3-gap-fill text-warning me-2"></i>Classroom Sections &amp; Enrollees
                        </h5>
                        <p class="text-muted small mb-0">I-click ang bisan asang section card aron ma-filter ang learner masterlist sa ubos.</p>
                    </div>
                    <button type="button" class="btn btn-outline-dark btn-sm fw-semibold" id="btnShowAllLearners" onclick="filterBySectionId('all', this)" style="border-radius: 6px;">
                        <i class="bi bi-people-fill me-1"></i> View All Learners (<?= count($learners) ?>)
                    </button>
                </div>

                <div class="row g-3 mb-4">
                    <?php 
                    $firstSecWithStudents = null;
                    foreach ($sectionGroups as $sid => $grp) {
                        if (!empty($grp['learners']) && $firstSecWithStudents === null) {
                            $firstSecWithStudents = $sid;
                        }
                    }
                    if ($firstSecWithStudents === null && !empty($sectionGroups)) {
                        $keys = array_keys($sectionGroups);
                        $firstSecWithStudents = $keys[0];
                    }
                    ?>

                    <?php foreach ($sectionGroups as $secId => $grp): ?>
                        <?php 
                        $sec = $grp['section'];
                        $secLearners = $grp['learners'];
                        $enrolled = count($secLearners);
                        $maxCap = max(1, (int)($sec['max_capacity'] ?? 15));
                        $percent = min(100, round(($enrolled / $maxCap) * 100));
                        $isFull = $enrolled >= $maxCap;
                        
                        $secLms = 0;
                        $secTrad = 0;
                        foreach ($secLearners as $sl) {
                            $isLms = !empty($sl['learner_user_id']) || ($sl['learning_track'] === 'lms') || ($sl['learning_track'] === 'candidate') || (($sl['lms_track'] ?? '') === 'candidate');
                            if ($isLms) $secLms++; else $secTrad++;
                        }
                        $isSelected = ($secId === $firstSecWithStudents);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card section-card bg-white h-100 shadow-sm section-grid-card <?= $isSelected ? 'active-section' : '' ?>" 
                                 id="grid-card-<?= $secId ?>" 
                                 onclick="filterBySectionId('<?= $secId ?>', this)"
                                 data-section-id="<?= $secId ?>"
                                 data-section-name="<?= htmlspecialchars($sec['section_name']) ?>"
                                 data-adviser="<?= htmlspecialchars($sec['adviser_name'] ?? 'Not Assigned') ?>"
                                 data-count="<?= $enrolled ?>">
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary fw-semibold mb-1" style="font-size: 0.75rem;">
                                                <?= htmlspecialchars($sec['grade_level'] ?? 'SPED Program') ?>
                                            </span>
                                            <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($sec['section_name']) ?></h5>
                                        </div>
                                        <?php if ($isFull): ?>
                                            <span class="badge bg-danger">Full Capacity</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <?= ($maxCap - $enrolled) ?> slots left
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <div><i class="bi bi-person-badge me-1"></i> Adviser: <strong><?= htmlspecialchars($sec['adviser_name'] ?? 'Not Assigned') ?></strong></div>
                                        <?php if (!empty($sec['room_number'])): ?>
                                            <div><i class="bi bi-door-open me-1"></i> Room: <?= htmlspecialchars($sec['room_number']) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex gap-1 mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle small" style="font-size: 0.72rem;">
                                            <i class="bi bi-laptop me-1"></i><?= $secLms ?> LMS
                                        </span>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle small" style="font-size: 0.72rem;">
                                            <i class="bi bi-person-workspace me-1"></i><?= $secTrad ?> Traditional
                                        </span>
                                    </div>

                                    <div class="mt-auto pt-2 border-top">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>Enrolled: <strong><?= $enrolled ?></strong> / <?= $maxCap ?> learners</span>
                                            <span><?= $percent ?>%</span>
                                        </div>
                                        <div class="progress progress-slim mb-3">
                                            <div class="progress-bar <?= $isFull ? 'bg-danger' : ($percent > 75 ? 'bg-warning' : 'bg-primary') ?>" 
                                                 style="width: <?= $percent ?>%"></div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm fw-semibold w-100 card-view-btn" style="border-radius:6px; font-size:0.8125rem;">
                                                <i class="bi bi-people-fill me-1"></i> View Enrollees (<?= $enrolled ?>)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($unassignedLearners)): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card section-card bg-white h-100 shadow-sm border-warning section-grid-card" 
                                 id="grid-card-unassigned" 
                                 onclick="filterBySectionId('unassigned', this)"
                                 data-section-id="unassigned"
                                 data-section-name="Unassigned / Pending Placement"
                                 data-adviser="None"
                                 data-count="<?= count($unassignedLearners) ?>">
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold mb-1" style="font-size: 0.75rem;">
                                                Pending Allocation
                                            </span>
                                            <h5 class="fw-bold mb-0 text-dark">Unassigned Learners</h5>
                                        </div>
                                        <span class="badge bg-warning text-dark">Action Needed</span>
                                    </div>

                                    <div class="small text-muted mb-3">
                                        <div><i class="bi bi-exclamation-triangle me-1 text-warning"></i> Enrolled in school but without an assigned classroom section.</div>
                                    </div>

                                    <div class="mt-auto pt-2 border-top">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>Pending: <strong><?= count($unassignedLearners) ?></strong> learners</span>
                                        </div>
                                        <div class="progress progress-slim mb-3">
                                            <div class="progress-bar bg-warning" style="width: 100%"></div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-semibold w-100 card-view-btn" style="border-radius:6px; font-size:0.8125rem;">
                                            <i class="bi bi-pencil-square me-1"></i> Assign Sections (<?= count($unassignedLearners) ?>)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

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

            <!-- ============================================================== -->
            <!-- ENROLLEE MASTERLIST TABLE (RESPONSIVE & CONNECTED TO CARDS)    -->
            <!-- ============================================================== -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i><span id="currentTableSectionTitle">Enrolled Learners</span>
                        </h5>
                        <span class="badge bg-primary" id="currentTableSectionBadge"><?= count($learners) ?> Learners</span>
                    </div>
                    <div class="small text-muted" id="currentTableSectionAdviser"></div>
                </div>

                <div class="table-responsive table-custom mb-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Official LRN</th>
                                <th>Learner Name</th>
                                <th>Sex / Age</th>
                                <th>Grade &amp; Section</th>
                                <th>Learning Track</th>
                                <th>Disability Category</th>
                                <th>Registry Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="masterlistTableBody">
                            <?php if (empty($learners)): ?>
                                <tr id="noLearnersDefaultRow">
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
                                    $isRegistered = !empty($l['lrn']) || (($l['lis_status'] ?? '') === 'synced');
                                    $badgeClass = $isRegistered ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle';
                                    
                                    // Match row to section ID
                                    $rowSecId = !empty($l['section_id']) ? (string)$l['section_id'] : '';
                                    if (empty($rowSecId) && !empty($l['section_name'])) {
                                        foreach ($sectionGroups as $sid => $grp) {
                                            if (strcasecmp(trim($grp['section']['section_name']), trim($l['section_name'])) === 0) {
                                                $rowSecId = (string)$sid;
                                                break;
                                            }
                                        }
                                    }
                                    if (empty($rowSecId)) {
                                        $rowSecId = 'unassigned';
                                    }
                                    ?>
                                    <tr class="learner-master-row" data-section-id="<?= $rowSecId ?>">
                                        <td>
                                            <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($l['lrn'] ?? $l['student_id'] ?? 'Pending LRN') ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                <?= htmlspecialchars($l['student_name']) ?>
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
                                            <small class="d-block text-primary fw-semibold mt-1">
                                                <i class="bi bi-grid-3x3-gap-fill me-1"></i><?= htmlspecialchars($l['section_name'] ?? 'Unassigned Section') ?>
                                            </small>
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
                                            <span class="badge-lis-status <?= $badgeClass ?>">
                                                <i class="bi <?= $isRegistered ? 'bi-check-circle-fill' : 'bi-clock-history' ?> me-1"></i><?= $isRegistered ? 'REGISTERED' : 'PENDING LRN' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 justify-content-end flex-wrap">
                                                <?php if ($role === 'principal'): ?>
                                                    <a href="<?= $basePath ?>/students/view/<?= $l['student_record_id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                       style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;"
                                                       title="View Complete Learner Profile &amp; Records">
                                                        <i class="bi bi-eye"></i> View Profile
                                                    </a>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadDocModal<?= $l['student_record_id'] ?>"
                                                            title="Upload IEP Documents, Medical Records, or Assessment Reports"
                                                            style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;">
                                                        <i class="bi bi-file-earmark-arrow-up"></i> Upload Docs
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" 
                                                            onclick="openQrInviteModal(<?= $l['student_record_id'] ?>, '<?= htmlspecialchars(addslashes($l['student_name'])) ?>', '<?= htmlspecialchars($l['claim_token'] ?? '') ?>')"
                                                            style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;" title="View QR Code or Link">
                                                        <i class="bi bi-qr-code"></i> QR
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1" 
                                                            onclick="sendQuickSmsInvite(<?= $l['student_record_id'] ?>, '<?= htmlspecialchars(addslashes($l['student_name'])) ?>')"
                                                            style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;" title="Invite Parent via Email, Messenger, or Phone">
                                                        <i class="bi bi-envelope-paper-heart"></i> Invite
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editSectionModal<?= $l['student_record_id'] ?>"
                                                            style="border-radius: 6px; font-size: 0.8rem; font-weight: 500;">
                                                        <i class="bi bi-pencil-square"></i> Section
                                                    </button>
                                                <?php endif; ?>
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
                                                                <option value="auto">✨ Auto-Detect Category from Filename</option>
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
                                                            <label class="form-label fw-bold small text-secondary">Document Title <small class="text-muted fw-normal">(Optional)</small></label>
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
                                                            <label class="form-label fw-bold small text-secondary">File Attachment(s) * <small class="text-muted">(PDF, JPG, PNG, DOCX up to 15MB)</small></label>
                                                            <input type="file" name="doc_files[]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple required>
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
                                    <div class="modal fade text-start" id="editSectionModal<?= $l['student_record_id'] ?>" tabindex="-1">
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
                                                            <label class="form-label fw-semibold">Classroom Section</label>
                                                            <?php if (!empty($allActiveSections)): ?>
                                                                <select name="section_id" class="form-select" required>
                                                                    <option value="">-- Select Active Section --</option>
                                                                    <?php foreach ($allActiveSections as $secOpt): ?>
                                                                        <?php 
                                                                        $isCurrentSec = ((int)($l['section_id'] ?? 0) === (int)$secOpt['id'])
                                                                            || (strcasecmp(trim($l['section_name'] ?? ''), trim($secOpt['section_name'])) === 0);
                                                                        ?>
                                                                        <option value="<?= $secOpt['id'] ?>" <?= $isCurrentSec ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($secOpt['section_name']) ?> (<?= htmlspecialchars($secOpt['adviser_name'] ?? 'Unassigned') ?>)
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            <?php else: ?>
                                                                <input type="text" name="section_name" class="form-control" 
                                                                       value="<?= htmlspecialchars($l['section_name'] ?? 'SPED Section A') ?>" required>
                                                            <?php endif; ?>
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
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 600;">Save Changes</button>
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
            </div>

        <?php endif; ?>

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
                <div class="modal-footer bg-light d-flex justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-dark btn-sm" onclick="printQrSlip()" style="border-radius: 6px;">
                            <i class="bi bi-printer me-1"></i> Print Slip
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="sendSmsFromModal()" style="border-radius: 6px;">
                            <i class="bi bi-chat-text-fill me-1"></i> Send SMS Invite
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius: 6px;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Direct Parent Invitation Modal (Email, Messenger, Phone) -->
    <div class="modal fade" id="directSmsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-primary-subtle py-3">
                    <h6 class="modal-title fw-bold text-primary mb-0">
                        <i class="bi bi-envelope-paper-heart-fill me-2"></i>Invite Parent / Guardian
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Recipient Parent / Learner</div>
                            <strong id="smsModalParentName" class="text-dark fs-6">—</strong>
                        </div>
                        <span id="smsModalPhoneBadge" class="badge bg-light text-secondary border px-2.5 py-1.5 font-monospace">09xxxxxxxxx</span>
                    </div>

                    <!-- Option 1: Instant Email Invite (100% Free Google SMTP) -->
                    <div class="card border-0 bg-light p-3 mb-3" style="border-radius: 10px;">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-envelope-at-fill text-primary me-1"></i> Option 1: Send Automated Email (Free)
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="email" id="inviteModalEmailInput" class="form-control" placeholder="Enter parent's email address...">
                            <button class="btn btn-primary fw-semibold px-3" type="button" id="sendEmailInviteBtn" onclick="sendAutomatedEmailInvite()">
                                <i class="bi bi-send-fill me-1"></i> Send Email
                            </button>
                        </div>
                        <div id="emailSendResult" class="small mt-2" style="display:none;"></div>
                    </div>

                    <!-- Option 2: Messenger / Viber Group Chat (Free) -->
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 w-100 py-2 fw-semibold" onclick="copyDirectSmsText()" style="border-radius: 7px; font-size: 0.875rem;">
                            <i class="bi bi-chat-heart-fill"></i> Option 2: Copy Link &amp; Text for Messenger / GC
                        </button>
                    </div>

                    <!-- Option 3: Phone Messages App (Free Unli-Text) -->
                    <div>
                        <a id="nativeSmsBtn" href="#" class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2 w-100 py-2 fw-semibold" style="border-radius: 7px; font-size: 0.875rem;">
                            <i class="bi bi-phone-fill"></i> Option 3: Open in Phone SMS App (Free Unli-Text)
                        </a>
                    </div>

                    <div id="smsCopySuccess" class="alert alert-success border-0 small py-2 px-3 mt-3 text-center mb-0" style="display: none; border-radius: 6px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Na-kopyar na ang text! Pwede na nimo i-paste sa inyong Messenger chat o GC.
                    </div>

                    <div class="alert alert-light border small text-muted mt-3 mb-0" style="border-radius: 8px;">
                        <i class="bi bi-info-circle me-1 text-primary"></i> <strong>100% Cost-Free:</strong> Walay bayad o SMS credits nga kaltason. Ang automated email diretsong moabot sa Gmail/Inbox sa ginikanan.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Page Scripts -->
    <script>
    let currentClaimUrl = '';
    let currentLearnerName = '';
    let currentStudentId = 0;
    let currentSmsPhone = '';
    let currentSmsBody = '';
    let currentParentEmail = '';

    function filterBySectionId(secId, triggerEl) {
        // Highlight active card
        document.querySelectorAll('.section-grid-card').forEach(c => c.classList.remove('active-section'));
        const allBtn = document.getElementById('btnShowAllLearners');
        if (allBtn) {
            allBtn.classList.remove('btn-dark');
            allBtn.classList.add('btn-outline-dark');
        }

        const titleEl = document.getElementById('currentTableSectionTitle');
        const badgeEl = document.getElementById('currentTableSectionBadge');
        const adviserEl = document.getElementById('currentTableSectionAdviser');

        let cardEl = null;
        if (triggerEl && triggerEl.classList.contains('section-grid-card')) {
            cardEl = triggerEl;
        } else if (triggerEl && triggerEl.closest('.section-grid-card')) {
            cardEl = triggerEl.closest('.section-grid-card');
        } else if (secId !== 'all') {
            cardEl = document.getElementById('grid-card-' + secId);
        }

        if (cardEl) {
            cardEl.classList.add('active-section');
        } else if (secId === 'all' && allBtn) {
            allBtn.classList.add('btn-dark');
            allBtn.classList.remove('btn-outline-dark');
        }

        const rows = document.querySelectorAll('.learner-master-row');
        let visibleCount = 0;

        if (secId === 'all') {
            rows.forEach(r => {
                r.style.display = '';
                visibleCount++;
            });
            if (titleEl) titleEl.textContent = 'All Enrolled SPED Learners';
            if (badgeEl) badgeEl.textContent = visibleCount + ' Learners';
            if (adviserEl) adviserEl.innerHTML = '<span class="badge bg-secondary-subtle text-secondary">All School Sections</span>';
        } else {
            rows.forEach(r => {
                const rSec = r.getAttribute('data-section-id');
                if (rSec === String(secId)) {
                    r.style.display = '';
                    visibleCount++;
                } else {
                    r.style.display = 'none';
                }
            });

            const secName = cardEl ? cardEl.getAttribute('data-section-name') : 'Section';
            const adviser = cardEl ? cardEl.getAttribute('data-adviser') : '';
            if (titleEl) titleEl.textContent = 'Section: ' + secName;
            if (badgeEl) badgeEl.textContent = visibleCount + ' Enrollees';
            if (adviserEl) {
                adviserEl.innerHTML = adviser ? '<i class="bi bi-person-badge me-1"></i>Adviser: <strong>' + adviser + '</strong>' : '';
            }
        }

        // Handle empty table placeholder for selected section
        let emptyRow = document.getElementById('dynamicEmptySectionRow');
        if (visibleCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'dynamicEmptySectionRow';
                emptyRow.innerHTML = '<td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox display-5 d-block mb-2 text-secondary opacity-50"></i><h6 class="fw-bold mb-1">No learners currently enrolled in this section.</h6><p class="small text-muted mb-0">Learners will be assigned via round-robin enrollment or you can assign them using the "Section" button.</p></td>';
                document.getElementById('masterlistTableBody').appendChild(emptyRow);
            }
            emptyRow.style.display = '';
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }

    // Initialize initial section view on load
    document.addEventListener('DOMContentLoaded', function() {
        const activeCard = document.querySelector('.section-grid-card.active-section');
        if (activeCard) {
            const secId = activeCard.getAttribute('data-section-id');
            filterBySectionId(secId, activeCard);
        }
    });

    function copyUniversalGcLink() {
        const protocol = window.location.protocol;
        const host = window.location.host;
        const basePath = '<?= $basePath ?>';
        const gcUrl = protocol + '//' + host + basePath + '/invite/claim';

        navigator.clipboard.writeText(gcUrl).then(() => {
            alert('Kopyado ang Universal GC Link!\n\nLink: ' + gcUrl + '\n\nPwede na nimo kini i-paste sa inyong Messenger Group Chat aron ma-claim sa ginikanan gamit ang Ngalan o Alias sa ilang anak.');
        }).catch(() => {
            prompt('Kopyaha kining maong Universal GC Link para sa Messenger:', gcUrl);
        });
    }

    function sendQuickSmsInvite(studentId, learnerName) {
        currentStudentId = studentId;
        currentLearnerName = learnerName;

        fetch('<?= $basePath ?>/masterlist/send-sms-invite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId, action: 'get_details' })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Wala makuha ang mga detalye.');
                return;
            }

            // Populate Modal
            currentSmsPhone = data.phone || '';
            currentSmsBody = data.sms_body || '';
            currentParentEmail = data.email || '';

            document.getElementById('smsModalParentName').textContent = data.parent + ' (Parent of ' + data.student + ')';
            document.getElementById('smsModalPhoneBadge').textContent = currentSmsPhone || 'No Phone';
            document.getElementById('inviteModalEmailInput').value = currentParentEmail;
            document.getElementById('smsCopySuccess').style.display = 'none';
            document.getElementById('emailSendResult').style.display = 'none';

            // Prepare native sms: URI scheme
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            const separator = isIOS ? '&' : '?';
            const nativeUrl = 'sms:' + currentSmsPhone + separator + 'body=' + encodeURIComponent(currentSmsBody);
            document.getElementById('nativeSmsBtn').setAttribute('href', nativeUrl);

            const smsModal = new bootstrap.Modal(document.getElementById('directSmsModal'));
            smsModal.show();
        })
        .catch(err => {
            alert('Adunay sayop sa koneksyon sa pagkuha sa invitation details.');
        });
    }

    function sendAutomatedEmailInvite() {
        const emailInput = document.getElementById('inviteModalEmailInput');
        const targetEmail = emailInput.value.trim();
        const resultEl = document.getElementById('emailSendResult');
        const btn = document.getElementById('sendEmailInviteBtn');

        if (!targetEmail || !targetEmail.includes('@')) {
            alert('Palihog pagbutang og sakto nga email address.');
            emailInput.focus();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
        resultEl.style.display = 'none';

        fetch('<?= $basePath ?>/masterlist/send-sms-invite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_id: currentStudentId,
                action: 'send_email',
                email: targetEmail
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Send Email';
            resultEl.style.display = 'block';

            if (data.success) {
                resultEl.className = 'small mt-2 text-success fw-semibold';
                resultEl.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + (data.message || 'Napadala na ang email invitation!');
            } else {
                resultEl.className = 'small mt-2 text-danger fw-semibold';
                resultEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'Wala napadala ang email.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Send Email';
            resultEl.style.display = 'block';
            resultEl.className = 'small mt-2 text-danger fw-semibold';
            resultEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error sa koneksyon sa email server.';
        });
    }

    function copyDirectSmsText() {
        navigator.clipboard.writeText(currentSmsBody).then(() => {
            const el = document.getElementById('smsCopySuccess');
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 4000);
        }).catch(() => {
            prompt('Kopyaha ang mensahe para sa Messenger:', currentSmsBody);
        });
    }

    function sendSmsFromModal() {
        if (!currentStudentId) return;
        const qrEl = document.getElementById('qrInviteModal');
        const qrModalInstance = bootstrap.Modal.getInstance(qrEl);
        if (qrModalInstance) {
            qrModalInstance.hide();
        }
        sendQuickSmsInvite(currentStudentId, currentLearnerName);
    }

    function openQrInviteModal(studentId, studentName, claimToken) {
        currentStudentId = studentId;
        currentLearnerName = studentName;
        document.getElementById('qrModalLearnerName').textContent = studentName;
        document.getElementById('copySuccessMsg').style.display = 'none';

        if (claimToken) {
            setupQrModal(claimToken);
        } else {
            fetch('<?= $basePath ?>/masterlist/get-qr-token?student_id=' + studentId)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.token) {
                        setupQrModal(data.token);
                    } else {
                        alert('Could not retrieve QR token for this student.');
                    }
                })
                .catch(err => {
                    alert('Error loading QR code token.');
                });
        }
    }

    function setupQrModal(token) {
        const protocol = window.location.protocol;
        const host = window.location.host;
        const basePath = '<?= $basePath ?>';
        currentClaimUrl = protocol + '//' + host + basePath + '/invite/claim/' + token;

        document.getElementById('qrModalLinkInput').value = currentClaimUrl;

        const qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(currentClaimUrl);
        document.getElementById('qrModalImg').src = qrApi;

        const qrModal = new bootstrap.Modal(document.getElementById('qrInviteModal'));
        qrModal.show();
    }

    function copyInviteLink() {
        const input = document.getElementById('qrModalLinkInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const msg = document.getElementById('copySuccessMsg');
            msg.style.display = 'block';
            setTimeout(() => { msg.style.display = 'none'; }, 3000);
        }).catch(() => {
            prompt('Copy this link:', input.value);
        });
    }

    function printQrSlip() {
        if (!currentClaimUrl) return;
        const printWin = window.open('', '_blank', 'width=600,height=650');
        printWin.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>SignED Parent Invitation - \${currentLearnerName}</title>
                <style>
                    body { font-family: sans-serif; text-align: center; padding: 30px; color: #1e3a5f; }
                    .card { border: 2px dashed #0d6efd; border-radius: 12px; padding: 25px; max-width: 440px; margin: 0 auto; }
                    h2 { margin-top: 0; color: #1e3a5f; }
                    p { font-size: 14px; color: #555; }
                    img { margin: 15px 0; border: 1px solid #ddd; padding: 6px; border-radius: 8px; }
                    .code { font-family: monospace; font-size: 11px; background: #f1f5f9; padding: 6px; border-radius: 4px; word-break: break-all; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h2>SignED SPED LMS</h2>
                    <h4>Parent Invitation Slip</h4>
                    <p>Learner: <strong>\${currentLearnerName}</strong></p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=\${encodeURIComponent(currentClaimUrl)}" alt="QR Code">
                    <p>I-scan kining QR code gamit ang cellphone camera aron makonektar sa account sa imong anak sa SignED.</p>
                    <div class="code">\${currentClaimUrl}</div>
                </div>
                <script>
                    window.onload = function() { window.print(); }
                <\/script>
            </body>
            </html>
        `);
        printWin.document.close();
    }
    </script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
