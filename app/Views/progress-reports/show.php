<?php
$pageTitle = 'Student Progress Report Card - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<?php
$isTeacher = ($role === 'sped_teacher');
$isParent = ($role === 'parent');
$isPrincipal = ($role === 'principal');
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Main Top Banner -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/progress-reports" style="color:#1e4072;">Progress Reports</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($student['student_name']); ?></li>
                    </ol>
                </nav>
                <h2 class="mb-0 fw-bold" style="color:#1e4072;">
                    <i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Learner Profile & SF9 Report Card
                </h2>
            </div>
            <div>
                <a href="<?php echo $basePath; ?>/progress-reports" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Student Quick Details Card -->
        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e4072 0%, #112542 100%); color: white;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="bi bi-person-badge-fill fs-1" style="color: #1e4072;"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($student['student_name']); ?></h3>
                        <div class="d-flex flex-wrap gap-3 small opacity-90">
                            <span><strong>Student ID:</strong> <?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($student['student_id'] ?? null)); ?></span>
                            <span><strong>DepEd LRN:</strong> <?php echo htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($student['lrn'] ?? null)); ?></span>
                            <span><strong>Sex:</strong> <?php echo htmlspecialchars($student['sex'] ?? 'N/A'); ?></span>
                            <span><strong>Age:</strong> <?php echo htmlspecialchars($student['age'] ?? 'N/A'); ?> yrs old</span>
                            <span><strong>Birthday:</strong> <?php echo $student['date_of_birth'] ? date('F d, Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="badge bg-light text-dark fs-6 py-2 px-3">
                            Status: <span class="fw-bold text-uppercase <?php echo ($progressReport['status'] ?? '') === 'finalized' ? 'text-success' : 'text-warning'; ?>">
                                <?php echo htmlspecialchars($progressReport['status'] ?? 'Not Started'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quarter Switcher Widget -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-secondary">Active Quarter:</span>
                    <?php if ($activeTab === 'indicators'): ?>
                        <select class="form-select form-select-sm" style="width:auto; min-width:160px;"
                                onchange="window.location.href='?tab=indicators&quarter=' + encodeURIComponent(this.value)">
                            <?php foreach (['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'] as $q): ?>
                                <option value="<?php echo htmlspecialchars($q); ?>" <?php echo $quarter === $q ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($q); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div class="btn-group" role="group">
                            <?php foreach (['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'] as $q): ?>
                                <a href="?tab=<?php echo urlencode($activeTab); ?>&quarter=<?php echo urlencode($q); ?>" class="btn btn-sm <?php echo $quarter === $q ? 'btn-primary' : 'btn-outline-secondary'; ?>" style="<?php echo $quarter === $q ? 'background-color:#1e4072; border-color:#1e4072;' : ''; ?>">
                                    <?php echo $q; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="text-muted small">Academic Year: <strong><?php echo htmlspecialchars($student['school_year'] ?? 'Current'); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Page Tabs -->
        <?php
        $tabBase = $basePath . '/progress-reports/' . (int)$student['id'] . '?quarter=' . urlencode($quarter);
        ?>
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'report' ? 'active fw-bold' : ''; ?>" href="<?php echo $tabBase; ?>&tab=report" style="<?php echo $activeTab === 'report' ? 'color:#1e4072;' : ''; ?>">
                    <i class="bi bi-file-earmark-text me-1"></i> Report Card
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'indicators' ? 'active fw-bold' : ''; ?>" href="<?php echo $tabBase; ?>&tab=indicators" style="<?php echo $activeTab === 'indicators' ? 'color:#1e4072;' : ''; ?>">
                    <i class="bi bi-list-check me-1"></i> SF9 Indicators
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'transfer' ? 'active fw-bold' : ''; ?>" href="<?php echo $tabBase; ?>&tab=transfer" style="<?php echo $activeTab === 'transfer' ? 'color:#1e4072;' : ''; ?>">
                    <i class="bi bi-arrow-right-square me-1"></i> Transfer & Cancellation
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/attendance">
                    <i class="bi bi-calendar-check me-1"></i> Attendance Sheet
                </a>
            </li>
        </ul>

        <!-- Tab Content Areas -->
        <?php if ($activeTab === 'report'): ?>
            <!-- SF9 REPORT CARD TAB -->
            <div class="card border-0 shadow-sm p-4 bg-white">
                <!-- DepEd SF9 Heading Form Header -->
                <div class="text-center mb-4 border-bottom pb-3">
                    <h5 class="fw-bold mb-0 text-danger" style="font-size: 10pt;">Republic of the Philippines</h5>
                    <h5 class="fw-bold mb-0 text-primary" style="font-size: 10pt;">DEPARTMENT OF EDUCATION</h5>
                    <h6 class="text-muted small">Region XI - Davao City Division - Piedad District</h6>
                    <h4 class="fw-bold mt-2 text-uppercase" style="color: #1e4072; letter-spacing: 1px; font-size: 14pt;">PIEDAD CENTRAL ELEMENTARY SCHOOL</h4>
                    <h5 class="fw-bold mt-2 text-uppercase text-secondary" style="font-size: 11pt; letter-spacing: 0.5px;">Learner's Progress Report Card (SF9)</h5>
                </div>

                <!-- Editable / Auto-filled Header Fields Form -->
                <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>">
                    <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($quarter); ?>">
                    <input type="hidden" name="school_year" value="<?php echo htmlspecialchars($student['school_year'] ?? ''); ?>">
                    <input type="hidden" name="active_tab" value="report">

                    <!-- Student Info Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Name of Learner</label>
                            <input class="form-control bg-light" value="<?php echo htmlspecialchars($student['student_name']); ?>" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Age</label>
                            <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($student['age'] ?? ''); ?>" <?php echo $canEdit ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Birthdate</label>
                            <input class="form-control bg-light" value="<?php echo $student['date_of_birth'] ? date('Y-m-d', strtotime($student['date_of_birth'])) : ''; ?>" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Sex</label>
                            <input class="form-control bg-light" value="<?php echo htmlspecialchars($student['sex'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Student ID</label>
                            <input class="form-control bg-light" value="<?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($student['student_id'] ?? null)); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">DepEd LRN</label>
                            <input class="form-control bg-light" value="<?php echo htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($student['lrn'] ?? null)); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Type of Learner (e.g. ASD, ADHD, ID)</label>
                            <input type="text" name="type_of_learner" class="form-control" value="<?php echo htmlspecialchars($progressReport['type_of_learner'] ?? ''); ?>" placeholder="Specify student category..." <?php echo $canEdit ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">Assessment Type</label>
                            <select name="assessment_type" class="form-select" <?php echo $canEdit ? '' : 'disabled'; ?>>
                                <option value="With Assessment" <?php echo ($progressReport['assessment_type'] ?? 'With Assessment') === 'With Assessment' ? 'selected' : ''; ?>>With Assessment (with IEP)</option>
                                <option value="Without Assessment" <?php echo ($progressReport['assessment_type'] ?? '') === 'Without Assessment' ? 'selected' : ''; ?>>Without Assessment (without IEP)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Attendance Record Section & Remarks (Side-by-Side) -->
                    <div class="row g-4 mb-4 align-items-start">
                        <!-- Left Column: Attendance Record -->
                        <div class="col-lg-6 col-md-12">
                            <h5 class="fw-bold mb-3 text-center" style="color: #1e4072;"><i class="bi bi-clock-history me-1"></i> Attendance Record</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle text-center small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Month</th>
                                            <th>School Days</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $monthsList = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'];
                                        $totalDays = 0;
                                        $totalPresent = 0;
                                        $totalAbsent = 0;

                                        foreach ($monthsList as $m): 
                                            // Saved school days or default to 0
                                            $sDays = (int)($attendanceSummary['school_days'][$m] ?? 0);
                                            // Dynamic present days from F2F + logs
                                            $pDays = (int)($presentCounts[$m] ?? 0);
                                            // Clamp present days to not exceed school days
                                            if ($pDays > $sDays && $sDays > 0) $pDays = $sDays;
                                            
                                            $aDays = max(0, $sDays - $pDays);

                                            $totalDays += $sDays;
                                            $totalPresent += $pDays;
                                            $totalAbsent += $aDays;
                                        ?>
                                            <tr>
                                                <td class="fw-bold table-light"><?php echo $m; ?></td>
                                                <td>
                                                    <?php if ($canEdit): ?>
                                                        <input type="number" name="school_days[<?php echo $m; ?>]" class="form-control form-control-sm text-center py-0" style="max-width: 60px; margin: auto;" value="<?php echo $sDays; ?>">
                                                    <?php else: ?>
                                                        <?php echo $sDays; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $pDays; ?></td>
                                                <td><?php echo $aDays; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-secondary fw-bold">
                                            <td>Total</td>
                                            <td><?php echo $totalDays; ?></td>
                                            <td><?php echo $totalPresent; ?></td>
                                            <td><?php echo $totalAbsent; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right Column: Front Cover Preview (Signatures/Letters) -->
                        <div class="col-lg-6 col-md-12">
                            <?php
                            $db = Database::getInstance()->getConnection();
                            
                            // Decode transfer details
                            $transferDetails = [];
                            if ($progressReport && !empty($progressReport['transfer_details'])) {
                                $transferDetails = json_decode($progressReport['transfer_details'], true) ?: [];
                            }
                            $admittedTo = $transferDetails['admitted_to'] ?? 'NON-GRADED';
                            $eligibleForAdmissionTo = $transferDetails['eligible_for_admission_to'] ?? 'NON-GRADED';
                            $cancellationAdmittedIn = $transferDetails['cancellation_admitted_in'] ?? '';
                            $cancellationDate = $transferDetails['cancellation_date'] ?? '';
                            ?>
                            
                            <!-- Parents Letter Card -->
                            <div class="card mb-3 border shadow-sm" style="font-family: Arial, sans-serif; font-size: 11pt; color: #000; background: #fff;">
                                <div class="card-body p-3">
                                    <p class="fw-bold mb-2">Dear Parents/Guardian,</p>
                                    <p class="mb-2 text-justify" style="text-indent: 2em; line-height: 1.3;">
                                        This report card is designed to show your child's progress in the different learning areas of development and character formation.
                                    </p>
                                    <p class="mb-0 text-justify" style="text-indent: 2em; line-height: 1.3;">
                                        The school welcomes you to confer with the teacher / principal so that we may best understand your child's special educational needs.
                                    </p>
                                </div>
                            </div>

                            <!-- Certificate to Transfer -->
                            <div class="card mb-3 border shadow-sm" style="font-family: Arial, sans-serif; font-size: 11pt; color: #000; background: #fff;">
                                <div class="card-body p-3">
                                    <div class="fw-bold text-uppercase text-center text-decoration-underline mb-3" style="font-size: 12.5pt;">Certificate to Transfer</div>
                                    <div class="mb-2">
                                        <span class="fw-bold">Admitted to:</span>
                                        <span class="border-bottom d-inline-block text-center px-2" style="min-width: 150px; border-color: #000 !important;"><?php echo htmlspecialchars($admittedTo); ?></span>
                                    </div>
                                    <div class="mb-0">
                                        <span class="fw-bold">Eligible for Admission to:</span>
                                        <span class="border-bottom d-inline-block text-center px-2" style="min-width: 150px; border-color: #000 !important;"><?php echo htmlspecialchars($eligibleForAdmissionTo); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancellation of Eligibility to Transfer -->
                            <div class="card border shadow-sm" style="font-family: Arial, sans-serif; font-size: 11pt; color: #000; background: #fff;">
                                <div class="card-body p-3">
                                    <div class="fw-bold text-uppercase text-center text-decoration-underline mb-3" style="font-size: 12.5pt;">Cancellation of Eligibility to Transfer</div>
                                    <div class="row mb-0">
                                        <div class="col-6">
                                            <span class="fw-bold">Admitted in:</span>
                                            <span class="border-bottom d-inline-block px-2 text-center" style="min-width: 100px; border-color: #000 !important;"><?php echo htmlspecialchars($cancellationAdmittedIn ?: '—'); ?></span>
                                        </div>
                                        <div class="col-6">
                                            <span class="fw-bold">Date:</span>
                                            <span class="border-bottom d-inline-block px-2 text-center" style="min-width: 100px; border-color: #000 !important;"><?php echo htmlspecialchars($cancellationDate ? date('F j, Y', strtotime($cancellationDate)) : '—'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Remarks & Progress Narrative (Spread out side-by-side at the bottom) -->
                    <div class="row g-3 mt-4 border-top pt-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary">General Progress Summary Narrative</label>
                            <textarea name="progress_summary" class="form-control" rows="4" placeholder="Describe the learner's overall achievements and highlights..." <?php echo $canEdit ? '' : 'readonly'; ?>><?php echo htmlspecialchars($progressReport['progress_summary'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary">Teacher's Remark</label>
                            <textarea name="teacher_remarks" class="form-control" rows="4" placeholder="Add recommendations or support required..." <?php echo $canEdit ? '' : 'readonly'; ?>><?php echo htmlspecialchars($progressReport['teacher_remarks'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Save / Update Button -->
                    <div class="mt-4 text-end">
                        <?php if ($canEdit): ?>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Report Card Settings
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Remarks, Comments, & Signatures per Quarter Sub-Form -->
                <div class="mt-5 pt-4 border-top">
                    <h5 class="fw-bold mb-3" style="color: #1e4072;">
                        <i class="bi bi-chat-left-dots-fill me-1"></i> Remarks & Signatures for <?php echo htmlspecialchars($quarter); ?>
                    </h5>
                    
                    <?php if ($progressReport): ?>
                        <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/remarks">
                            <input type="hidden" name="progress_report_id" value="<?php echo (int)$progressReport['id']; ?>">
                            <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($quarter); ?>">

                            <div class="row g-3">
                                <!-- Teacher Remarks -->
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2 text-primary">Teacher's Section</h6>
                                            <input type="hidden" name="teacher_remark" value="<?php echo htmlspecialchars($remarksMap[$quarter]['teacher']['text'] ?? ''); ?>">
                                            <div>
                                                <label class="form-label small">Teacher Signature Name</label>
                                                <input type="text" name="teacher_signature" class="form-control form-control-sm" value="<?php echo htmlspecialchars($remarksMap[$quarter]['teacher']['signature'] ?? ''); ?>" <?php echo ($isTeacher && $canEdit) ? '' : 'readonly'; ?>>
                                                <input type="hidden" name="teacher_signature_data" id="teacher_signature_data">
                                                <?php if (!empty($remarksMap[$quarter]['teacher']['signature_data'])): ?>
                                                    <div class="mt-2 p-2 bg-white border rounded">
                                                        <img src="<?php echo htmlspecialchars($remarksMap[$quarter]['teacher']['signature_data']); ?>" alt="Teacher signature" style="max-height: 90px; max-width: 100%;">
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($isTeacher && $canEdit): ?>
                                                    <div class="mt-2">
                                                        <canvas id="teacherSignaturePad" class="sf9-signature-pad"></canvas>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-clear-signature="teacher">
                                                            <i class="bi bi-eraser me-1"></i> Clear Signature
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Parent Comments -->
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2 text-success">Parent / Guardian Section</h6>
                                            <div class="mb-3">
                                                <label class="form-label small">Parent/Guardian Comments</label>
                                                <textarea name="parent_comment" class="form-control form-control-sm" rows="3" <?php echo ($isParent && (!$progressReport || $progressReport['status'] !== 'finalized')) ? '' : 'readonly'; ?>><?php echo htmlspecialchars($remarksMap[$quarter]['parent']['text'] ?? ''); ?></textarea>
                                            </div>
                                            <div>
                                                <label class="form-label small">Parent/Guardian Signature Name</label>
                                                <input type="text" name="parent_signature" class="form-control form-control-sm" value="<?php echo htmlspecialchars($remarksMap[$quarter]['parent']['signature'] ?? ''); ?>" <?php echo ($isParent && (!$progressReport || $progressReport['status'] !== 'finalized')) ? '' : 'readonly'; ?>>
                                                <input type="hidden" name="parent_signature_data" id="parent_signature_data">
                                                <?php if (!empty($remarksMap[$quarter]['parent']['signature_data'])): ?>
                                                    <div class="mt-2 p-2 bg-white border rounded">
                                                        <img src="<?php echo htmlspecialchars($remarksMap[$quarter]['parent']['signature_data']); ?>" alt="Parent/guardian signature" style="max-height: 90px; max-width: 100%;">
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($isParent && (!$progressReport || $progressReport['status'] !== 'finalized')): ?>
                                                    <div class="mt-2">
                                                        <canvas id="parentSignaturePad" class="sf9-signature-pad"></canvas>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-clear-signature="parent">
                                                            <i class="bi bi-eraser me-1"></i> Clear Signature
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save remarks button -->
                                <div class="col-12 text-end">
                                    <?php if (($isTeacher && $canEdit) || ($isParent && (!$progressReport || $progressReport['status'] !== 'finalized'))): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-primary px-3">
                                            <i class="bi bi-save me-1"></i> Save Remarks/Signatures for <?php echo htmlspecialchars($quarter); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-secondary py-2 small">Save the report card settings above first to record quarterly remarks and signatures.</div>
                    <?php endif; ?>
                </div>

                <!-- Finalization Section (Print & Upload signed copy) -->
                <?php if ($progressReport): ?>
                    <div class="mt-5 pt-4 border-top">
                        <h5 class="fw-bold mb-3" style="color: #a01422;"><i class="bi bi-lock-fill me-1"></i> Finalize Progress Report</h5>

                        <?php if (!empty($canPrintReportCard)): ?>
                            <div class="mb-4">
                                <p class="text-muted small mb-2">
                                    After completing all inputs above (grades, attendance, remarks), print the official SF9 layout, have it signed, then optionally upload the signed copy before finalizing.
                                </p>
                                <a href="<?php echo $basePath; ?>/iep/print/report-card/<?php echo (int)$student['id']; ?>" target="_blank" rel="noopener" class="btn btn-outline-primary">
                                    <i class="bi bi-printer me-1"></i> Print SF9 Report Card
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($progressReport['status'] !== 'finalized'): ?>
                            <div class="alert alert-warning py-3">
                                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Warning</h6>
                                Finalizing this report card will lock attendance counts, remarks, and SF9 indicator ratings. 
                                Make sure to double check all inputs before finalization.
                            </div>
                            
                            <?php if ($isTeacher): ?>
                                <?php
                                $hasParentSignature = false;
                                if (!empty($remarksMap)) {
                                    foreach ($remarksMap as $q => $types) {
                                        if (isset($types['parent']) && (!empty($types['parent']['signature']) || !empty($types['parent']['signature_data']))) {
                                            $hasParentSignature = true;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <?php if ($hasParentSignature): ?>
                                    <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$progressReport['id']; ?>/finalize" enctype="multipart/form-data">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Upload Signed Document (Optional - PDF or Image)</label>
                                                <input type="file" name="signed_document" class="form-control" accept=".pdf,image/*">
                                            </div>
                                            <div class="col-md-6">
                                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to finalize this progress report? This action cannot be undone.');">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Lock & Finalize Report Card
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-danger py-3 mb-0">
                                        <h6 class="fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Finalization Blocked</h6>
                                        This progress report cannot be finalized because the parent/guardian has not signed it yet. The parent must log in to review the report card and sign using their designated signature pad first.
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-muted small">Only the assigned SPED teacher can finalize the progress report.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="card border-0 bg-success-subtle text-success p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-1"></i> Progress Report Finalized</h6>
                                        <p class="mb-0 small">This progress report has been locked and cannot be edited.</p>
                                    </div>
                                    <?php if (!empty($progressReport['document_path'])): ?>
                                        <a href="<?php echo $basePath . $progressReport['document_path']; ?>" target="_blank" class="btn btn-sm btn-success">
                                            <i class="bi bi-download me-1"></i> View Signed Copy
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="mt-5 pt-4 border-top">
                        <div class="alert alert-info py-3 mb-0">
                            <h6 class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Print &amp; Finalize</h6>
                            <p class="mb-0 small">Complete and save the report card settings above first. The print and finalize options will appear here after saving.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($activeTab === 'transfer'): ?>
            <!-- TRANSFER & CANCELLATION TAB -->
            <div class="card border-0 shadow-sm p-4 bg-white">
                <div class="text-center mb-4 border-bottom pb-3">
                    <h4 class="fw-bold mt-2 text-uppercase" style="color: #1e4072; letter-spacing: 1px; font-size: 14pt;">Transfer & Cancellation Details</h4>
                    <p class="text-muted small">Configure the Certificate to Transfer and Cancellation of Eligibility to Transfer section on the official SF9 report card.</p>
                </div>

                <?php
                // Decode the current transfer details
                $transferDetails = [];
                if ($progressReport && !empty($progressReport['transfer_details'])) {
                    $transferDetails = json_decode($progressReport['transfer_details'], true) ?: [];
                }
                $admittedTo = $transferDetails['admitted_to'] ?? 'NON-GRADED';
                $eligibleForAdmissionTo = $transferDetails['eligible_for_admission_to'] ?? 'NON-GRADED';
                $cancellationAdmittedIn = $transferDetails['cancellation_admitted_in'] ?? '';
                $cancellationDate = $transferDetails['cancellation_date'] ?? '';
                ?>

                <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>">
                    <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($quarter); ?>">
                    <input type="hidden" name="school_year" value="<?php echo htmlspecialchars($student['school_year'] ?? ''); ?>">
                    <input type="hidden" name="active_tab" value="transfer">
                    
                    <div class="row g-4">
                        <!-- Left Column: Certificate to Transfer -->
                        <div class="col-md-6 border-end pe-md-4">
                            <h5 class="fw-bold mb-3" style="color: #1e4072;"><i class="bi bi-file-earmark-arrow-right me-1"></i> Certificate to Transfer</h5>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Admitted to</label>
                                <input type="text" name="admitted_to" class="form-control" value="<?php echo htmlspecialchars($admittedTo); ?>" placeholder="e.g. NON-GRADED or Grade 1" <?php echo $canEdit ? '' : 'readonly'; ?>>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Eligible for Admission to</label>
                                <input type="text" name="eligible_for_admission_to" class="form-control" value="<?php echo htmlspecialchars($eligibleForAdmissionTo); ?>" placeholder="e.g. NON-GRADED or Grade 1" <?php echo $canEdit ? '' : 'readonly'; ?>>
                            </div>
                        </div>

                        <!-- Right Column: Cancellation of Eligibility -->
                        <div class="col-md-6 ps-md-4">
                            <h5 class="fw-bold mb-3" style="color: #1e4072;"><i class="bi-x-square me-1"></i> Cancellation of Eligibility to Transfer</h5>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Admitted in (School Name)</label>
                                <input type="text" name="cancellation_admitted_in" class="form-control" value="<?php echo htmlspecialchars($cancellationAdmittedIn); ?>" placeholder="e.g. PIEDAD CENTRAL ELEMENTARY SCHOOL" <?php echo $canEdit ? '' : 'readonly'; ?>>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Date</label>
                                <input type="date" name="cancellation_date" class="form-control" value="<?php echo htmlspecialchars($cancellationDate); ?>" <?php echo $canEdit ? '' : 'readonly'; ?>>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="mt-4 text-end border-top pt-3">
                        <?php if ($canEdit): ?>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Transfer Details
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        <?php elseif ($activeTab === 'indicators'): ?>
            <?php require __DIR__ . '/_tab_indicators.php'; ?>

        <?php endif; ?>
    </div>
</div>

<style>
.sf9-signature-pad {
    width: 100%;
    height: 150px;
    display: block;
    background: #fff;
    border: 2px dashed #1e4072;
    border-radius: 6px;
    touch-action: none;
    cursor: crosshair;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof SignaturePad === 'undefined') return;

    const pads = {};

    function setupPad(role, canvasId, inputId) {
        const canvas = document.getElementById(canvasId);
        const input = document.getElementById(inputId);
        if (!canvas || !input) return;

        const pad = new SignaturePad(canvas, { penColor: '#1e4072' });
        pads[role] = { pad, input, canvas };

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const width = canvas.offsetWidth;
            const height = canvas.offsetHeight;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            pad.clear();
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
    }

    setupPad('teacher', 'teacherSignaturePad', 'teacher_signature_data');
    setupPad('parent', 'parentSignaturePad', 'parent_signature_data');

    document.querySelectorAll('[data-clear-signature]').forEach(function (button) {
        button.addEventListener('click', function () {
            const role = button.getAttribute('data-clear-signature');
            if (pads[role]) {
                pads[role].pad.clear();
                pads[role].input.value = '';
            }
        });
    });

    document.querySelectorAll('form[action$="/remarks"]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            let missingSignature = false;
            Object.keys(pads).forEach(function (role) {
                const bundle = pads[role];
                if (!bundle.pad.isEmpty()) {
                    bundle.input.value = bundle.pad.toDataURL('image/png');
                } else {
                    const existingImage = bundle.canvas.closest('div').parentElement.querySelector('img');
                    if (!existingImage) {
                        missingSignature = true;
                    }
                }
            });

            if (missingSignature) {
                event.preventDefault();
                alert('Please draw the required signature before saving.');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
