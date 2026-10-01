<?php
// DO NOT ALTER WITHOUT APPROVAL — Student Records
// Last modified: 2026-05-06
// Part of: SignED — Student Record Detail

$pageTitle = 'Student Record - SignED';
require_once __DIR__ . '/../../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Back & Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="<?php echo $basePath; ?>/masterlist" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Masterlist
            </a>
            <a href="<?php echo $basePath; ?>/students/edit/<?php echo $student['id']; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit Student ID / LRN
            </a>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo $basePath; ?>/assessment/history/<?php echo $student['id']; ?>" class="btn btn-outline-primary">
                <i class="bi bi-clipboard-check"></i> Assessment History
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-text"></i> View IEP
            </a>
            <?php if (RoleMiddleware::hasPermission('progress_report.view') || RoleMiddleware::hasPermission('progress_report.view_own_child')): ?>
                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo $student['id']; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-bar-chart-line"></i> View Grades
                </a>
                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo $student['id']; ?>/attendance" class="btn btn-outline-success">
                    <i class="bi bi-calendar3"></i> Attendance (SF2)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Process Lifecycle Journey Banner -->
    <div class="card shadow-sm border-0 mb-4 bg-white" style="border-radius: 12px; border-left: 4px solid #1e4072 !important;">
        <div class="card-body py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <h6 class="mb-0 fw-bold" style="color: #1e4072;">
                    <i class="bi bi-diagram-3-fill me-1"></i> SPED 13-Process Lifecycle Status
                </h6>
                <span class="badge bg-success">Active Learner</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap small text-muted">
                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>P1: Enrolled</span>
                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>P2-P3: Assessment</span>
                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                <span class="badge bg-light text-secondary border"><i class="bi bi-calendar-event me-1"></i>P4: Meeting & PDSP</span>
                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                <span class="badge bg-light text-secondary border"><i class="bi bi-file-earmark-text me-1"></i>P5: IEP Formulation</span>
                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                <span class="badge bg-light text-secondary border"><i class="bi bi-laptop me-1"></i>P6-P7: Teaching & LMS</span>
                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                <span class="badge bg-light text-secondary border"><i class="bi bi-bar-chart me-1"></i>P8-P9: Monitoring & SF2</span>
                <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                <span class="badge bg-light text-secondary border"><i class="bi bi-arrow-up-right-circle me-1"></i>P10-P13: Transition & Placement</span>
            </div>
        </div>
    </div>

    <h1 class="mb-4">
        <i class="bi bi-person-badge text-primary"></i> Student Record
    </h1>

    <!-- Student Information Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-fill"></i> Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Student ID:</strong> <?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($student['student_id'] ?? null)); ?></p>
                    <p><strong>DepEd LRN:</strong> <?php echo htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($student['lrn'] ?? null)); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($student['student_name']); ?></p>
                    <p><strong>Birth Date:</strong> <?php echo !empty($student['date_of_birth']) && $student['date_of_birth'] !== '0000-00-00' ? date('F d, Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></p>
                    <p><strong>Disability Type:</strong> <?php echo htmlspecialchars($student['disability_type'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>PSA Number:</strong> <?php echo htmlspecialchars($student['psa_number'] ?? 'N/A'); ?></p>
                    <p><strong>PWD ID Number:</strong> <?php echo htmlspecialchars($student['pwd_id_number'] ?? 'N/A'); ?></p>
                    <p><strong>Parent/Guardian:</strong> <?php echo htmlspecialchars($student['parent_name'] ?? 'N/A'); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($student['contact_number'] ?? 'N/A'); ?></p>
                    <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment History -->
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Enrollment History</h5>
        </div>
        <div class="card-body">
            <?php if (empty($enrollments)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No enrollment history found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>School Year</th>
                                <th>Type</th>
                                <th>Grade Level</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Verified By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($enrollment['school_year']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($enrollment['enrollment_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'verified' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$enrollment['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($enrollment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($enrollment['submitted_at'])); ?></td>
                                    <td>
                                        <small><?php echo htmlspecialchars($enrollment['verifier_name'] ?? 'Pending'); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/enrollment/review/<?php echo $enrollment['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Documents -->
    <div class="card shadow">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> All Documents</h5>
            <button type="button" class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadDocModal" style="border-radius: 6px; font-weight: 500;">
                <i class="bi bi-cloud-arrow-up-fill"></i> Upload Document
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($allDocuments)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No documents found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Document Type</th>
                                <th>School Year</th>
                                <th>Enrollment Type</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>Reviewed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allDocuments as $doc): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo ucwords(str_replace('_', ' ', $doc['document_type'])); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc['enrollment_year']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($doc['enrollment_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$doc['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($doc['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></td>
                                    <td>
                                        <small><?php echo htmlspecialchars($doc['reviewer_name'] ?? 'Pending'); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/<?php echo $doc['file_path']; ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-download"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <form method="POST" action="<?php echo $basePath; ?>/students/upload-document" enctype="multipart/form-data">
                <input type="hidden" name="student_id" value="<?php echo (int)$student['id']; ?>">
                <input type="hidden" name="return_to" value="/students/view/<?php echo (int)$student['id']; ?>">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark fs-6">
                        <i class="bi bi-file-earmark-arrow-up-fill text-primary me-2"></i>Upload Document: <?php echo htmlspecialchars($student['student_name']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="small text-muted mb-3">
                        Upload existing physical IEP documents, medical records, or diagnostic assessment reports to maintain a complete learner portfolio.
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
                            <option value="other">DepEd SF10 / Form 137 / Other School Record</option>
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
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Notes / Diagnostic Remarks</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Brief notes or accommodations..."></textarea>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
