<?php
// Reusable printed SF9 styling
$pageTitle = 'SF9 Progress Report Card - SignED';
require_once __DIR__ . '/../layouts/header.php';

// Helper to get signature by role key
if (!function_exists('findSigForRole')) {
    function findSigForRole($roleKey, $signatures) {
        foreach ($signatures as $sig) {
            $role = strtolower(trim($sig['signatory_role'] ?? ''));
            if ($role === strtolower(trim($roleKey))) {
                return $sig;
            }
        }
        return null;
    }
}

// Helper to get quarterly remarks/comments
if (!function_exists('getQuarterRemark')) {
    function getQuarterRemark($reportRemarks, $qName, $type) {
        foreach ([$qName, $qName . ' Quarter'] as $key) {
            if (isset($reportRemarks[$key][$type])) {
                return $reportRemarks[$key][$type];
            }
        }
        return null;
    }
}

// Filter active months (those with non-zero school days)
$activeMonths = [];
foreach ($months as $mNum => $m) {
    if ($m['school_days'] > 0) {
        $activeMonths[$mNum] = $m;
    }
}
if (empty($activeMonths)) {
    $activeMonths = array_slice($months, 0, 10, true);
}

// Signatures mapping
$teacherSig = findSigForRole('teacher', $signatures) ?: findSigForRole('sped_teacher', $signatures);
$schoolHeadSig = findSigForRole('school_head', $signatures) ?: findSigForRole('principal', $signatures);

$teacherName = ($spedTeacherName && $spedTeacherName !== '—') ? $spedTeacherName : 'ANTHONY M. CASTRO';
$dob = $student['date_of_birth'] ?? null;
$ageDisplay = $dob ? (int)date_diff(date_create($dob), date_create('today'))->y . ' yrs' : '—';
$dobFormatted = $dob ? date('F j, Y', strtotime($dob)) : '—';

$transferDetails = [];
if ($progressReport && !empty($progressReport['transfer_details'])) {
    $transferDetails = json_decode($progressReport['transfer_details'], true) ?: [];
}
$admittedTo = $transferDetails['admitted_to'] ?? 'NON-GRADED';
$eligibleForAdmissionTo = $transferDetails['eligible_for_admission_to'] ?? 'NON-GRADED';
$cancellationAdmittedIn = $transferDetails['cancellation_admitted_in'] ?? '';
$cancellationDate = $transferDetails['cancellation_date'] ?? '';

// Check which quarters have parent signature
$signedQuarters = [];
foreach (['1st', '2nd', '3rd', '4th'] as $qText) {
    $qSig = getQuarterRemark($reportRemarks, $qText, 'parent');
    if ($qSig && (!empty($qSig['signature_data']) || !empty($qSig['signature_name']))) {
        $signedQuarters[] = $qText;
    }
}
?>

<style>
    /* Embed printed SF9 styles */
    :root {
        --primary-color: #1e4072;
        --secondary-color: #3b6d11;
        --border-color: #000;
        --text-color: #000;
        --body-font: 11pt;
        --table-font: 10pt;
        --header-font: 10pt;
        --header-h3: 12pt;
        --header-h4: 10pt;
        --school-name: 13pt;
        --form-title: 14pt;
        --section-title: 12pt;
        --student-info: 11pt;
        --parents-letter: 11pt;
        --sig-name: 11pt;
        --sig-title: 9pt;
        --cert-transfer: 11pt;
        --remark-line: 10.5pt;
        --parent-comments: 10.5pt;
        --parent-sig-name: 9pt;
        --inside-header: 10pt;
        --legend-box: 9pt;
        --legend-title: 9.5pt;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: var(--text-color);
    }

    /* Desktop Preview Stacked Layout */
    @media screen {
        body {
            background-color: #f1f5f9;
        }
        .parent-workspace {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .sf9-preview-pane {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow-y: auto;
            margin-bottom: 24px;
        }
        .print-page {
            border: 1px solid #ccc;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            background: white;
            border-radius: 8px;
        }
        .signature-sidebar {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
    }

    /* Print styling overrides */
    @media print {
        .parent-workspace {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .signature-sidebar, .no-print, .main-content-header, header, .sidebar, .topbar {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        body {
            background: none !important;
        }
        .print-page {
            page-break-after: always;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        @page {
            size: A4 landscape;
            margin: 8mm 12mm;
        }
    }

    /* SF9 Specific Classes */
    .cover-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 50px;
    }
    .cover-left, .cover-right {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .header-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 5px;
    }
    .header-seal {
        height: 55px;
        width: auto;
    }
    .header-text {
        text-align: center;
        flex-grow: 1;
        font-size: var(--header-font);
    }
    .header-text h3 {
        margin: 1px 0;
        font-size: var(--header-h3);
        font-weight: bold;
    }
    .header-text h4 {
        margin: 1px 0;
        font-size: var(--header-h4);
        font-weight: normal;
    }
    .header-text .school-name {
        font-weight: bold;
        font-size: var(--school-name);
        margin-top: 2px;
    }
    .form-title {
        text-align: center;
        font-size: var(--form-title);
        font-weight: bold;
        margin: 8px 0;
        letter-spacing: 0.5px;
    }
    .section-title {
        font-weight: bold;
        text-decoration: underline;
        text-align: center;
        font-size: var(--section-title);
        margin: 8px 0 5px 0;
        text-transform: uppercase;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
        font-size: var(--table-font);
    }
    th, td {
        border: 1px solid var(--border-color);
        padding: 4px;
        vertical-align: middle;
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
    }
    .attendance-table th, .attendance-table td {
        text-align: center;
        padding: 3px;
    }
    .student-info-section {
        margin-top: 10px;
        font-size: var(--student-info);
    }
    .info-line {
        display: flex;
        margin-bottom: 6px;
        align-items: flex-end;
        width: 100%;
    }
    .info-row {
        display: flex;
        gap: 15px;
    }
    .half-width {
        width: 50%;
    }
    .info-label {
        font-weight: bold;
        margin-right: 5px;
        white-space: nowrap;
    }
    .underline-value {
        border-bottom: 1px solid #000;
        padding-bottom: 1px;
        min-height: 15px;
        flex-grow: 1;
    }
    .parents-letter-box {
        border: 1.5px solid var(--border-color);
        padding: 8px 12px;
        border-radius: 4px;
        margin-top: 10px;
    }
    .letter-title {
        font-weight: bold;
        margin: 0 0 6px 0;
        font-size: var(--parents-letter);
    }
    .letter-body {
        margin: 0 0 8px 0;
        text-indent: 20px;
        text-align: justify;
        font-size: var(--parents-letter);
        line-height: 1.25;
    }
    .signatures-row {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
    }
    .signature-block {
        text-align: center;
        width: 45%;
    }
    .sig-image-container {
        height: 45px;
        position: relative;
        margin-bottom: 2px;
    }
    .sig-overlay {
        max-height: 45px;
        width: auto;
    }
    .sig-name {
        font-weight: bold;
        font-size: var(--sig-name);
        border-top: 1px solid #000;
        padding-top: 2px;
        text-transform: uppercase;
    }
    .sig-title {
        font-size: var(--sig-title);
        color: #555;
    }
    .parent-sig-overlay {
        max-height: 25px;
        width: auto;
    }
    .parent-sig-name {
        font-size: var(--parent-sig-name);
        font-weight: bold;
    }
    .inside-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 1.5px solid var(--border-color);
        padding-bottom: 3px;
        margin-bottom: 10px;
        font-size: var(--inside-header);
    }
    .legend-box {
        border: 1px solid var(--border-color);
        padding: 5px 8px;
        font-size: var(--legend-box);
        margin-top: 10px;
    }
    .legend-title {
        font-weight: bold;
        font-size: var(--legend-title);
        margin-bottom: 3px;
        text-transform: uppercase;
    }
    .legend-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        column-gap: 10px;
        text-align: center;
    }
    .legend-item {
        font-size: 8pt;
        line-height: 1.15;
    }
</style>

<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header Banner -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 px-3 no-print">
        <div>
            <h2 class="mb-0 fw-bold" style="color:#1e4072;">
                <i class="bi bi-file-earmark-check-fill me-2"></i>Acknowledge SF9 Progress Report Card
            </h2>
            <p class="text-muted mb-0">Review your child's grades and sign off on remarks to finalize the report card.</p>
        </div>
        <div>
            <a href="<?= $basePath ?>/progress-reports" class="btn btn-outline-secondary">
                <i class="bi-arrow-left me-1"></i> Back to List
            </a>
            <button onclick="window.print()" class="btn btn-primary bg-navy">
                <i class="bi-printer me-1"></i> Print SF9
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="parent-workspace">
        
        <!-- Right: SF9 Preview Pane -->
        <div class="sf9-preview-pane">
            
            <!-- PAGE 1: COVER PAGE -->
            <div class="print-page">
                <div class="cover-grid">
                    
                    <!-- LEFT COLUMN: BACK COVER (Parent Comments & Attendance) -->
                    <div class="cover-left" style="border-right: 1px dashed #ccc; padding-right: 25px;">
                        
                        <!-- Attendance Table -->
                        <div>
                            <div class="section-title">Attendance Record</div>
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <?php foreach ($activeMonths as $m): ?>
                                            <th><?= htmlspecialchars($m['name']) ?></th>
                                        <?php endforeach; ?>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totSchool = 0;
                                    $totPresent = 0;
                                    $totAbsent = 0;
                                    ?>
                                    <tr>
                                        <td class="fw-bold">Days of School</td>
                                        <?php foreach ($activeMonths as $m): $totSchool += $m['school_days']; ?>
                                            <td><?= $m['school_days'] ?></td>
                                        <?php endforeach; ?>
                                        <td class="fw-bold"><?= $totSchool ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Days Present</td>
                                        <?php foreach ($activeMonths as $m): $totPresent += $m['present']; ?>
                                            <td><?= $m['present'] ?></td>
                                        <?php endforeach; ?>
                                        <td class="fw-bold"><?= $totPresent ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Days Absent</td>
                                        <?php foreach ($activeMonths as $m): $totAbsent += $m['absent']; ?>
                                            <td><?= $m['absent'] ?></td>
                                        <?php endforeach; ?>
                                        <td class="fw-bold"><?= $totAbsent ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Parent Comments Table -->
                        <div>
                            <div class="section-title" style="margin-top: 15px;">Parent/Guardian Signature &amp; Remarks</div>
                            <table>
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;">Quarter</th>
                                        <th style="width: 55%; text-align: left;">Comments / Suggestions</th>
                                        <th style="width: 25%;">Signature</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach (['1st', '2nd', '3rd', '4th'] as $qText): 
                                        $qSig = getQuarterRemark($reportRemarks, $qText, 'parent');
                                        $commentText = $qSig['remark_text'] ?? '';
                                        $sigName = $qSig['signature_name'] ?? '';
                                        $sigData = $qSig['signature_data'] ?? '';
                                    ?>
                                        <tr>
                                            <td class="quarter-col text-center" style="font-weight: bold; border-bottom: 1px solid #ddd;"><?= $qText ?></td>
                                            <td class="comment-col underline-value" style="font-style: italic;"><?= htmlspecialchars($commentText) ?></td>
                                            <td class="signature-col underline-value text-center" style="position: relative;">
                                                <?php if (!empty($sigData)): ?>
                                                    <img src="<?= htmlspecialchars($sigData) ?>" class="parent-sig-overlay" alt="Signature">
                                                <?php elseif (!empty($sigName)): ?>
                                                    <span class="parent-sig-name"><?= htmlspecialchars($sigName) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Certificate to Transfer -->
                        <div>
                            <div class="certificate-transfer">
                                <div class="section-title" style="margin-top: 10px;">Certificate to Transfer</div>
                                <div class="info-line" style="margin-bottom: 4px;">
                                    <span class="info-label">Admitted to:</span>
                                    <span class="info-val underline-value"><?= htmlspecialchars($admittedTo) ?></span>
                                </div>
                                <div class="info-line" style="margin-bottom: 4px;">
                                    <span class="info-label">Eligible for Admission to:</span>
                                    <span class="info-val underline-value"><?= htmlspecialchars($eligibleForAdmissionTo) ?></span>
                                </div>
                                
                                <div class="signatures-row" style="margin-top: 10px;">
                                    <div class="signature-block">
                                        <div class="sig-name" style="margin-top: 15px;"><?= htmlspecialchars($principalName) ?></div>
                                        <div class="sig-title">School Principal</div>
                                    </div>
                                    <div class="signature-block">
                                        <div class="sig-name" style="margin-top: 15px;"><?= htmlspecialchars($teacherName) ?></div>
                                        <div class="sig-title">Teacher</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancellation -->
                            <div class="cancellation-transfer">
                                <div class="section-title" style="margin-top: 10px;">Cancellation of Eligibility to Transfer</div>
                                <div class="info-row">
                                    <div class="info-line half-width" style="margin-bottom: 4px;">
                                        <span class="info-label">Admitted in:</span>
                                        <span class="info-val underline-value"><?= htmlspecialchars($cancellationAdmittedIn) ?></span>
                                    </div>
                                    <div class="info-line half-width" style="margin-bottom: 4px;">
                                        <span class="info-label">Date:</span>
                                        <span class="info-val underline-value"><?= htmlspecialchars($cancellationDate ? date('F j, Y', strtotime($cancellationDate)) : '') ?></span>
                                    </div>
                                </div>
                                <div class="signatures-row" style="justify-content: flex-end; margin-top: 5px;">
                                    <div class="signature-block" style="width: 45%;">
                                        <div class="sig-name" style="margin-top: 15px;"><?= htmlspecialchars($principalName) ?></div>
                                        <div class="sig-title">School Principal</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: FRONT COVER -->
                    <div class="cover-right" style="padding-left: 25px;">
                        <div>
                            <!-- School Logo / DepEd Header -->
                            <div class="header-container">
                                <img src="<?= htmlspecialchars($basePath . '/images/davao_division_seal.png') ?>" class="header-seal" alt="Division Seal">
                                <div class="header-text">
                                    <h4>Republic of the Philippines</h4>
                                    <h3>Department of Education</h3>
                                    <h4>Region XI</h4>
                                    <h4>Schools Division of Davao City</h4>
                                    <h4>Piedad District</h4>
                                    <div class="school-name">PIEDAD CENTRAL ELEMENTARY SCHOOL</div>
                                </div>
                                <img src="<?= htmlspecialchars($basePath . '/images/deped_seal.png') ?>" class="header-seal" alt="DepEd Seal">
                            </div>

                            <div class="form-title">PROGRESS REPORT CARD</div>

                            <!-- Student Info Grid -->
                            <div class="student-info-section">
                                <div class="info-line">
                                    <span class="info-label">Name:</span>
                                    <span class="info-val underline-value" style="font-weight: bold;"><?= htmlspecialchars($student['student_name']) ?></span>
                                </div>
                                <div class="info-row">
                                    <div class="info-line half-width">
                                        <span class="info-label">Age:</span>
                                        <span class="info-val underline-value"><?= htmlspecialchars($ageDisplay) ?></span>
                                    </div>
                                    <div class="info-line half-width">
                                        <span class="info-label">Date of Birth:</span>
                                        <span class="info-val underline-value"><?= htmlspecialchars($dobFormatted) ?></span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-line half-width">
                                        <span class="info-label">Sex:</span>
                                        <span class="info-val underline-value"><?= htmlspecialchars($student['sex'] ?? '—') ?></span>
                                    </div>
                                    <div class="info-line half-width">
                                        <span class="info-label">S.Y.:</span>
                                        <span class="info-val underline-value"><?= htmlspecialchars($student['school_year'] ?? date('Y') . '-' . (date('Y')+1)) ?></span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-line half-width">
                                        <span class="info-label">LRN:</span>
                                        <span class="info-val underline-value"><?= htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($student['lrn'] ?? null)) ?></span>
                                    </div>
                                    <div class="info-line half-width">
                                        <span class="info-label">Assessment:</span>
                                        <span class="info-val" style="padding-bottom: 2px;">
                                            <span style="font-size: 11pt; line-height: 1;">☑</span> with &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="font-size: 11pt; line-height: 1;">☐</span> without
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dear Parents Letter -->
                            <div class="parents-letter-box">
                                <p class="letter-title">Dear Parents/Guardian,</p>
                                <p class="letter-body">
                                    This report card is designed to show your child's progress in the different learning areas of development and character formation.
                                </p>
                                <p class="letter-body">
                                    The school welcomes you to confer with the teacher / principal so that we may best understand your child's special educational needs.
                                </p>
                                
                                <div class="signatures-row">
                                    <div class="signature-block">
                                        <div class="sig-image-container">
                                            <?php if ($schoolHeadSig && !empty($schoolHeadSig['signature_image_path'])): ?>
                                                <img src="<?= htmlspecialchars($basePath . '/' . ltrim($schoolHeadSig['signature_image_path'], '/')) ?>" class="sig-overlay" alt="Principal Signature">
                                            <?php endif; ?>
                                        </div>
                                        <div class="sig-name"><?= htmlspecialchars($principalName) ?></div>
                                        <div class="sig-title">School Principal</div>
                                    </div>
                                    <div class="signature-block">
                                        <div class="sig-image-container">
                                            <?php if ($teacherSig && !empty($teacherSig['signature_image_path'])): ?>
                                                <img src="<?= htmlspecialchars($basePath . '/' . ltrim($teacherSig['signature_image_path'], '/')) ?>" class="sig-overlay" alt="Teacher Signature">
                                            <?php endif; ?>
                                        </div>
                                        <div class="sig-name"><?= htmlspecialchars($teacherName) ?></div>
                                        <div class="sig-title">Teacher</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGE 2: INSIDE PAGE (Ratings) -->
            <div class="print-page">
                <div class="inside-header">
                    <div class="inside-header-item">
                        <strong>LEARNER:</strong> <span><?= htmlspecialchars($student['student_name']) ?></span>
                    </div>
                    <div class="inside-header-item">
                        <strong>LRN:</strong> <span><?= htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($student['lrn'] ?? null)) ?></span>
                    </div>
                    <div class="inside-header-item">
                        <strong>SCHOOL YEAR:</strong> <span><?= htmlspecialchars($student['school_year'] ?? date('Y') . '-' . (date('Y')+1)) ?></span>
                    </div>
                </div>

                <div class="section-title" style="text-align: left; text-decoration: none; margin-bottom: 8px; color: var(--primary-color);">
                    QUARTERLY DOMAIN DEVELOPMENT RATINGS
                </div>

                <!-- Ratings Table -->
                <table style="margin-bottom: 10px;">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Development Domains & Skill Competencies</th>
                            <th style="width: 50px; text-align:center;">Q1</th>
                            <th style="width: 50px; text-align:center;">Q2</th>
                            <th style="width: 50px; text-align:center;">Q3</th>
                            <th style="width: 50px; text-align:center;">Q4</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ratingsGrouped)): ?>
                            <tr>
                                <td colspan="5" style="padding:15px; text-align:center; color: #555;">No domain ratings recorded yet. Initialize PDSP and observe activities to populate.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ratingsGrouped as $domain => $indicators): ?>
                                <tr style="background-color: #f2f2f2; font-weight: bold;">
                                    <td colspan="5" style="font-size: 9pt;"><?= htmlspecialchars($domain) ?></td>
                                </tr>
                                <?php foreach ($indicators as $indicator => $qval): ?>
                                    <tr>
                                        <td style="padding-left:15px; font-weight:normal; font-size: 9pt;"><?= htmlspecialchars($indicator) ?></td>
                                        <td style="text-align:center; font-weight: bold;"><?= htmlspecialchars($qval[1] ?? '—') ?></td>
                                        <td style="text-align:center; font-weight: bold;"><?= htmlspecialchars($qval[2] ?? '—') ?></td>
                                        <td style="text-align:center; font-weight: bold;"><?= htmlspecialchars($qval[3] ?? '—') ?></td>
                                        <td style="text-align:center; font-weight: bold;"><?= htmlspecialchars($qval[4] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Grading Legend -->
                <div class="legend-box">
                    <div class="legend-title">Development Rating Legend</div>
                    <div class="legend-grid">
                        <div class="legend-item"><strong>P</strong><br>Proficient (85-100%)</div>
                        <div class="legend-item"><strong>AP</strong><br>Approaching Prof. (70-84%)</div>
                        <div class="legend-item"><strong>D</strong><br>Developing (50-69%)</div>
                        <div class="legend-item"><strong>B</strong><br>Beginning (&lt;50%)</div>
                        <div class="legend-item"><strong>NA / NO</strong><br>Not Applicable / Not Observed</div>
                    </div>
                </div>

                <!-- Remarks -->
                <?php if ($progressReport && (!empty($progressReport['progress_summary']) || !empty($progressReport['teacher_remarks']))): ?>
                    <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <?php if (!empty($progressReport['progress_summary'])): ?>
                            <div style="border: 1px solid var(--border-color); padding: 8px;">
                                <strong>General Progress Summary Narrative:</strong>
                                <p style="margin: 4px 0 0 0; text-align: justify; font-size: 9pt; line-height: 1.25; font-style: italic;">
                                    <?= htmlspecialchars($progressReport['progress_summary']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($progressReport['teacher_remarks'])): ?>
                            <div style="border: 1px solid var(--border-color); padding: 8px;">
                                <strong>Teacher's Remark / Recommendations:</strong>
                                <p style="margin: 4px 0 0 0; text-align: justify; font-size: 9pt; line-height: 1.25; font-style: italic;">
                                    <?= htmlspecialchars($progressReport['teacher_remarks']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom: Signature Panel -->
        <div class="signature-sidebar no-print mt-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
                <div class="card-header text-white py-3" style="background-color: #1e4072;">
                    <h5 class="mb-0 font-weight-bold"><i class="bi bi-vector-pen me-2"></i>Sign Report Card</h5>
                </div>
                <div class="card-body bg-white p-4">
                    <form method="post" action="<?= $basePath ?>/progress-reports/<?= intval($student['id']) ?>/remarks" id="remarksForm">
                        <input type="hidden" name="parent_signature_data" id="parent_signature_data" value="">
                        <input type="hidden" name="parent_signature" id="parent_signature" value="Signed by Parent">
                        <input type="hidden" name="progress_report_id" value="<?= intval($progressReport['id'] ?? 0) ?>">

                        <!-- Alert messages -->
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success py-2 small"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="row g-4">
                            <!-- Left Column: Inputs -->
                            <div class="col-lg-4 col-md-12">
                                <div class="mb-3">
                                    <label for="quarter_select" class="form-label font-weight-bold text-muted small text-uppercase">Select Quarter</label>
                                    <select name="quarter" id="quarter_select" class="form-select border-2" style="border-radius: 8px;" onchange="window.location.href='?quarter=' + encodeURIComponent(this.value)">
                                        <?php foreach (['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'] as $q): ?>
                                            <option value="<?= htmlspecialchars($q) ?>" <?= $quarter === $q ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($q) ?> (<?= in_array(str_replace(' Quarter', '', $q), $signedQuarters) ? 'Signed' : 'Awaiting Signature' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label for="parent_comment" class="form-label font-weight-bold text-muted small text-uppercase">Comments / Suggestions</label>
                                    <textarea id="parent_comment" name="parent_comment" class="form-control border-2" style="border-radius: 8px; resize: none;" rows="4" placeholder="Enter comments here..."><?= htmlspecialchars(getQuarterRemark($reportRemarks, str_replace(' Quarter', '', $quarter), 'parent')['remark_text'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Middle Column: Signature Pad -->
                            <div class="col-lg-4 col-md-12 text-center text-lg-start">
                                <label class="form-label font-weight-bold text-muted small text-uppercase d-block text-start">Parent's Signature</label>
                                <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 10px; position: relative;">
                                    <canvas id="sigCanvas" width="400" height="150" style="width: 100%; height: 150px; background: white; border-radius: 6px; touch-action: none; cursor: crosshair;"></canvas>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2 w-100" onclick="clearSignature()">
                                        <i class="bi bi-eraser me-1"></i> Clear Canvas
                                    </button>
                                </div>
                            </div>

                            <!-- Right Column: Info & Action -->
                            <div class="col-lg-4 col-md-12 d-flex flex-column justify-content-between">
                                <div class="card border-0 bg-light p-3 mb-3 mb-lg-0" style="border-radius: 8px; border-left: 4px solid #1e4072 !important;">
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;"><i class="bi bi-info-circle me-1 text-primary"></i>Digital Signature</h6>
                                    <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.3;">
                                        This signature will be embedded directly in the printed SF9 document. Quarters signed by you are immediately updated in the preview above.
                                    </p>
                                </div>
                                <button type="button" class="btn text-white w-100 py-3 font-weight-bold" style="background-color: #a01422; border-radius: 8px; font-size: 1.05rem;" onclick="submitSignature()">
                                    <i class="bi bi-save me-1"></i> Submit &amp; Sign Quarter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('sigCanvas');
    let sigPad;

    if (canvas) {
        sigPad = new SignaturePad(canvas, { penColor: '#000000' });

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            sigPad.clear();
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
    }

    function clearSignature() {
        if (sigPad) {
            sigPad.clear();
        }
    }

    function submitSignature() {
        if (sigPad.isEmpty()) {
            alert('Please provide your digital signature first.');
            return;
        }

        const dataUrl = sigPad.toDataURL('image/png');
        document.getElementById('parent_signature_data').value = dataUrl;
        document.getElementById('remarksForm').submit();
    }
</script>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>
</body>
</html>
