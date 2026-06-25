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

// Helper to translate rating percentages to codes
function getRatingCode(?float $score): string {
    if ($score === null) return 'NO-NA';
    if ($score >= 85) return 'P';
    if ($score >= 70) return 'AP';
    if ($score >= 50) return 'D';
    return 'B';
}

function getRatingDescription(string $code): string {
    switch ($code) {
        case 'P': return 'Proficient (Always manifests)';
        case 'AP': return 'Approaching Proficiency (Most of the time)';
        case 'D': return 'Developing (Sometimes manifests)';
        case 'B': return 'Beginning (Rarely manifests)';
        default: return 'Not Observed / Not Applicable';
    }
}
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
                            <span><strong>LRN:</strong> <?php echo htmlspecialchars($student['lrn']); ?></span>
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
                    <div class="btn-group" role="group">
                        <?php foreach (['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'] as $q): ?>
                            <a href="?quarter=<?php echo urlencode($q); ?>" class="btn btn-sm <?php echo $quarter === $q ? 'btn-primary' : 'btn-outline-secondary'; ?>" style="<?php echo $quarter === $q ? 'background-color:#1e4072; border-color:#1e4072;' : ''; ?>">
                                <?php echo $q; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <span class="text-muted small">Academic Year: <strong><?php echo htmlspecialchars($student['school_year'] ?? 'Current'); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Tab Content Areas -->
        <?php if ($activeTab === 'report'): ?>
            <!-- SF9 REPORT CARD TAB -->
            <div class="card border-0 shadow-sm p-4 bg-white">
                <!-- DepEd SF9 Heading Form Header -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-0" style="color: #a01422;">Republic of the Philippines</h5>
                    <h5 class="fw-bold mb-0" style="color: #1e4072;">DEPARTMENT OF EDUCATION</h5>
                    <h6 class="text-muted">REGION IV-A CALABARZON</h6>
                    <h4 class="fw-bold mt-3 text-uppercase" style="color: #1e4072; letter-spacing: 1px;">SF9 NON-GRADED PROGRESS REPORT CARD</h4>
                </div>

                <hr class="border-secondary opacity-25">

                <!-- Editable / Auto-filled Header Fields Form -->
                <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>">
                    <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($quarter); ?>">
                    <input type="hidden" name="school_year" value="<?php echo htmlspecialchars($student['school_year'] ?? ''); ?>">

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
                            <label class="form-label small fw-bold text-secondary">LRN</label>
                            <input class="form-control bg-light" value="<?php echo htmlspecialchars($student['lrn']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Type of Learner (e.g. ASD, ADHD, ID)</label>
                            <input type="text" name="type_of_learner" class="form-control" value="<?php echo htmlspecialchars($progressReport['type_of_learner'] ?? ''); ?>" placeholder="Specify student category..." <?php echo $canEdit ? '' : 'readonly'; ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Assessment Type</label>
                            <select name="assessment_type" class="form-select" <?php echo $canEdit ? '' : 'disabled'; ?>>
                                <option value="With Assessment" <?php echo ($progressReport['assessment_type'] ?? '') === 'With Assessment' ? 'selected' : ''; ?>>With Assessment</option>
                                <option value="Without Assessment" <?php echo ($progressReport['assessment_type'] ?? '') === 'Without Assessment' ? 'selected' : ''; ?>>Without Assessment</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Left Panel: Attendance Record -->
                        <div class="col-lg-5">
                            <h5 class="fw-bold mb-3" style="color: #1e4072;"><i class="bi bi-clock-history me-1"></i> Attendance Record</h5>
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

                        <!-- Right Panel: Grading Domains Table & Rating Legend -->
                        <div class="col-lg-7">
                            <h5 class="fw-bold mb-3" style="color: #1e4072;"><i class="bi bi-award me-1"></i> Quarterly Domain Ratings</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle text-center small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">Learning Domain / Development Goal</th>
                                            <th style="width: 120px;">Active Rating</th>
                                            <th style="width: 120px;">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activeDomains as $dom): 
                                            // If saved rating exists, use it
                                            $ratingsArr = [];
                                            if (!empty($progressReport['ratings'])) {
                                                $ratingsArr = json_decode($progressReport['ratings'], true) ?: [];
                                            }
                                            $activeRating = $ratingsArr[$dom] ?? null;

                                            // Fallback to calculation if draft
                                            if (!$activeRating) {
                                                $autoVal = $entriesMap[$dom]['auto'] ?? ($p7AvgMap[$dom] ?? null);
                                                $manualVal = $entriesMap[$dom]['manual'] ?? null;
                                                if ($autoVal !== null && $manualVal !== null) {
                                                    $combined = ($autoVal + $manualVal) / 2;
                                                } elseif ($autoVal !== null) {
                                                    $combined = $autoVal;
                                                } elseif ($manualVal !== null) {
                                                    $combined = $manualVal;
                                                } else {
                                                    $combined = null;
                                                }
                                                $activeRating = getRatingCode($combined);
                                            }
                                        ?>
                                            <tr>
                                                <td class="text-start fw-semibold"><?php echo htmlspecialchars($dom); ?></td>
                                                <td>
                                                    <span class="badge px-3 py-1.5 fs-6 <?php 
                                                        echo $activeRating === 'P' ? 'bg-success' : 
                                                            ($activeRating === 'AP' ? 'bg-primary' : 
                                                            ($activeRating === 'D' ? 'bg-warning text-dark' : 
                                                            ($activeRating === 'B' ? 'bg-danger' : 'bg-secondary'))); 
                                                    ?>">
                                                        <?php echo $activeRating; ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small"><?php echo getRatingDescription($activeRating); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Rating Legend Panel -->
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> Guide for Rating (Legend)</h6>
                                    <div class="row g-2 small">
                                        <div class="col-sm-6"><strong>P</strong> - Proficient (Always manifests skills)</div>
                                        <div class="col-sm-6"><strong>AP</strong> - Approaching Proficiency (Most of the time)</div>
                                        <div class="col-sm-6"><strong>D</strong> - Developing (Sometimes manifests skills)</div>
                                        <div class="col-sm-6"><strong>B</strong> - Beginning (Rarely manifests skills)</div>
                                        <div class="col-sm-12 border-top pt-1 mt-1"><strong>NO/NA</strong> - Not Observed / Not Applicable (No manifestation at all)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Remarks & Progress Narrative -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">General Progress Summary Narrative</label>
                            <textarea name="progress_summary" class="form-control" rows="3" placeholder="Describe the learner's overall achievements and highlights..." <?php echo $canEdit ? '' : 'readonly'; ?>><?php echo htmlspecialchars($progressReport['progress_summary'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Teacher's Remark</label>
                            <textarea name="teacher_remarks" class="form-control" rows="3" placeholder="Add recommendations or support required..." <?php echo $canEdit ? '' : 'readonly'; ?>><?php echo htmlspecialchars($progressReport['teacher_remarks'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Save / Update Button (Only for Teacher in draft mode) -->
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
                                            <div class="mb-3">
                                                <label class="form-label small">Quarter Remarks</label>
                                                <textarea name="teacher_remark" class="form-control form-control-sm" rows="3" <?php echo ($isTeacher && $canEdit) ? '' : 'readonly'; ?>><?php echo htmlspecialchars($remarksMap[$quarter]['teacher']['text'] ?? ''); ?></textarea>
                                            </div>
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
                        
                        <?php if ($progressReport['status'] !== 'finalized'): ?>
                            <div class="alert alert-warning py-3">
                                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Warning</h6>
                                Finalizing this report card will lock all numerical scores, attendance counts, and remarks. 
                                Make sure to double check all inputs before finalization.
                            </div>
                            
                            <?php if ($isTeacher): ?>
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
                <?php endif; ?>
            </div>

        <?php elseif ($activeTab === 'grades'): ?>
            <!-- GRADES CONFIGURATION TAB -->
            <div class="card border-0 shadow-sm p-4 bg-white">
                <h4 class="fw-bold mb-3" style="color: #1e4072;"><i class="bi bi-percent me-1"></i> Configure Ratings & F2F Scores</h4>
                <p class="text-muted small">
                    Auto-computed grades are calculated from graded LMS online activities. 
                    You can input manual Face-to-Face class scores to average with the auto score.
                </p>

                <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/grades">
                    <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($quarter); ?>">

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Learning Domain</th>
                                    <th>Online Activity Average (LMS)</th>
                                    <th>Face-to-Face Class Score (Manual)</th>
                                    <th>Combined Score</th>
                                    <th>Final Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeDomains as $dom): 
                                    $autoVal = $entriesMap[$dom]['auto'] ?? ($p7AvgMap[$dom] ?? null);
                                    $manualVal = $entriesMap[$dom]['manual'] ?? null;
                                ?>
                                    <tr>
                                        <td class="text-start fw-semibold"><?php echo htmlspecialchars($dom); ?></td>
                                        <td>
                                            <?php if ($autoVal !== null): ?>
                                                <span class="fw-bold"><?php echo number_format($autoVal, 1); ?>%</span>
                                                <input type="hidden" name="domains[<?php echo htmlspecialchars($dom); ?>][auto]" value="<?php echo $autoVal; ?>">
                                            <?php else: ?>
                                                <span class="text-muted">No online logs</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($canEdit): ?>
                                                <input type="number" step="0.01" min="0" max="100" name="domains[<?php echo htmlspecialchars($dom); ?>][manual]" class="form-control text-center py-1 mx-auto" style="max-width: 120px;" value="<?php echo $manualVal !== null ? number_format($manualVal, 2) : ''; ?>">
                                            <?php else: ?>
                                                <span class="fw-bold"><?php echo $manualVal !== null ? number_format($manualVal, 1) . '%' : 'N/A'; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary" id="combined-<?php echo strtolower(str_replace(' ', '-', $dom)); ?>">
                                                <?php 
                                                if ($autoVal !== null && $manualVal !== null) {
                                                    echo number_format(($autoVal + $manualVal) / 2, 1) . '%';
                                                } elseif ($autoVal !== null) {
                                                    echo number_format($autoVal, 1) . '%';
                                                } elseif ($manualVal !== null) {
                                                    echo number_format($manualVal, 1) . '%';
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge px-3 py-1.5 fs-6" id="rating-<?php echo strtolower(str_replace(' ', '-', $dom)); ?>">
                                                <?php 
                                                $combined = null;
                                                if ($autoVal !== null && $manualVal !== null) $combined = ($autoVal + $manualVal) / 2;
                                                elseif ($autoVal !== null) $combined = $autoVal;
                                                elseif ($manualVal !== null) $combined = $manualVal;
                                                echo getRatingCode($combined);
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end">
                        <?php if ($canEdit): ?>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Numerical Scores
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

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
