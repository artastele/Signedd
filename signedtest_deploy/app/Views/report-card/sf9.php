<?php
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
    // Fallback if no school days are configured yet
    $activeMonths = array_slice($months, 0, 10, true);
}

// Fetch teacher remarks
$q1Teacher = getQuarterRemark($reportRemarks, '1st', 'teacher')['remark_text'] ?? '';
$q2Teacher = getQuarterRemark($reportRemarks, '2nd', 'teacher')['remark_text'] ?? '';
$q3Teacher = getQuarterRemark($reportRemarks, '3rd', 'teacher')['remark_text'] ?? '';
$q4Teacher = getQuarterRemark($reportRemarks, '4th', 'teacher')['remark_text'] ?? '';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Form 9 (SF9) — <?= htmlspecialchars($student['student_name']) ?></title>
    <style>
        :root {
            --primary-color: #1e4072;
            --secondary-color: #3b6d11;
            --border-color: #000;
            --text-color: #000;

            /* Base Font Scale for Screen (Larger and highly readable) */
            --body-font: 11.5pt;
            --table-font: 10.5pt;
            --header-font: 10.5pt;
            --header-h3: 12.5pt;
            --header-h4: 10.5pt;
            --school-name: 13.5pt;
            --form-title: 14.5pt;
            --section-title: 12.5pt;
            --student-info: 11pt;
            --parents-letter: 11pt;
            --sig-name: 11pt;
            --sig-title: 9.5pt;
            --cert-transfer: 11pt;
            --remark-line: 10.5pt;
            --parent-comments: 10.5pt;
            --parent-sig-name: 9.5pt;
            --inside-header: 10.5pt;
            --legend-box: 9.5pt;
            --legend-title: 10pt;
        }

        @media print {
            :root {
                /* Exact compact print dimensions to fit landscape A4 sheets */
                --body-font: 9.5pt;
                --table-font: 8.5pt;
                --header-font: 8.5pt;
                --header-h3: 10pt;
                --header-h4: 8.5pt;
                --school-name: 11pt;
                --form-title: 12pt;
                --section-title: 10pt;
                --student-info: 9pt;
                --parents-letter: 9pt;
                --sig-name: 9pt;
                --sig-title: 7.5pt;
                --cert-transfer: 9pt;
                --remark-line: 8.5pt;
                --parent-comments: 8.5pt;
                --parent-sig-name: 7.5pt;
                --inside-header: 8.5pt;
                --legend-box: 8pt;
                --legend-title: 8.5pt;
            }
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-color);
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: var(--body-font);
            line-height: 1.25;
        }
        
        /* Screen styling: Display pages stacked with shadows */
        @media screen {
            body {
                background-color: #f0f2f5;
                padding: 30px 10px;
            }
            .print-page {
                background-color: #fff;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                margin: 0 auto 30px auto;
                padding: 20px 30px;
                max-width: 1100px;
                min-height: 750px;
                border-radius: 8px;
                border: 1px solid #ddd;
                box-sizing: border-box;
            }
        }

        /* Print styling: Force landscape A4 and page breaks */
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm 12mm 8mm 12mm;
            }
            body {
                background: none;
                color: #000;
                margin: 0;
                padding: 0;
            }
            .print-page {
                page-break-after: always;
                break-after: page;
                width: 100%;
                box-sizing: border-box;
                height: auto;
            }
            .no-print {
                display: none !important;
            }
        }

        /* Landscape cover layout: Split into 2 columns (Back cover | Front cover) */
        .cover-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 50px;
            box-sizing: border-box;
        }
        .cover-left, .cover-right {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        /* Header section with logos */
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

        /* Section layout */
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            font-size: var(--section-title);
            margin: 8px 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        /* Tables */
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

        /* Attendance Table Specifics */
        .attendance-table th, .attendance-table td {
            text-align: center;
            padding: 3px;
        }
        
        /* Student Info Section */
        .student-info-section {
            margin-top: 10px;
            font-size: var(--student-info);
        }
        .info-row {
            display: flex;
            gap: 15px;
        }
        .info-line {
            display: flex;
            margin-bottom: 6px;
            align-items: flex-end;
            width: 100%;
        }
        .half-width {
            width: 50%;
        }
        .info-label {
            font-weight: bold;
            margin-right: 5px;
            white-space: nowrap;
        }
        .info-value {
            flex-grow: 1;
        }
        .underline-value {
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
            min-height: 15px;
        }

        /* Dear Parents Box */
        .parents-letter-box {
            border: 1.5px solid var(--border-color);
            padding: 8px 12px;
            margin-top: 10px;
            font-size: var(--parents-letter);
        }
        .letter-title {
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .letter-body {
            text-indent: 15px;
            margin: 0 0 6px 0;
            text-align: justify;
            line-height: 1.2;
        }
        .signatures-row {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .signature-block {
            text-align: center;
            width: 45%;
            position: relative;
        }
        .sig-image-container {
            height: 25px;
            position: relative;
        }
        .sig-overlay {
            max-height: 40px;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
        }
        .sig-name {
            font-weight: bold;
            border-bottom: 1px solid var(--border-color);
            margin-top: 3px;
            font-size: var(--sig-name);
        }
        .sig-title {
            font-size: var(--sig-title);
            color: #333;
        }

        /* Certificate to Transfer & Cancellation */
        .certificate-transfer, .cancellation-transfer {
            margin-top: 10px;
            font-size: var(--cert-transfer);
        }

        /* Rating Guide Specifics */
        .rating-guide-table td {
            padding: 3px;
        }
        .rating-guide-table .symbol {
            font-weight: bold;
            text-align: center;
        }
        .rating-guide-table .desc {
            font-weight: bold;
        }
        
        /* Remarks section lines */
        .remarks-lines {
            margin-top: 5px;
        }
        .remark-row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 6px;
        }
        .remark-quarter {
            font-weight: bold;
            margin-right: 8px;
            width: 30px;
            white-space: nowrap;
        }
        .remark-line {
            flex-grow: 1;
            font-style: italic;
            color: #1e4072;
            padding-left: 5px;
            font-size: var(--remark-line);
        }

        /* Parent Comments Table */
        .parent-comments-table {
            width: 100%;
            border-collapse: collapse;
        }
        .parent-comments-table th, .parent-comments-table td {
            padding: 3px 5px;
            font-size: var(--parent-comments);
        }
        .parent-comments-table th {
            border-bottom: 1.5px solid #000;
            text-align: left;
        }
        .parent-comments-table td {
            height: 28px;
            vertical-align: bottom;
        }
        .parent-comments-table .quarter-col {
            font-weight: bold;
            text-align: center;
            border-bottom: none;
        }
        .parent-comments-table .comment-col {
            padding-left: 10px;
            font-style: italic;
            color: #1e4072;
        }
        .parent-sig-overlay {
            max-height: 25px;
            max-width: 100px;
            position: absolute;
            bottom: 1px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
        }
        .parent-sig-name {
            font-size: var(--parent-sig-name);
            font-style: italic;
            color: #555;
        }

        /* Inside Ratings Page Specifics */
        .inside-header {
            display: flex;
            justify-content: space-between;
            font-size: var(--inside-header);
            border-bottom: 1.5px solid var(--border-color);
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .inside-header-item {
            display: flex;
            gap: 5px;
        }
        .domain-row {
            background-color: #e6e6e6;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .rating-col {
            text-align: center;
            width: 45px;
            font-weight: bold;
        }
        .legend-box {
            border: 1px solid var(--border-color);
            padding: 6px;
            margin-top: 8px;
            font-size: var(--legend-box);
            background-color: #fcfcfc;
        }
        .legend-title {
            font-weight: bold;
            margin-bottom: 3px;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: var(--legend-title);
        }
        .legend-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 5px;
        }
        .legend-item {
            text-align: center;
        }

        /* Top control bar */
        .no-print-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 10pt;
            cursor: pointer;
            border-radius: 4px;
            margin-bottom: 15px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        .no-print-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div style="max-width: 1100px; margin: 0 auto; padding: 15px 10px; display: flex; gap: 10px;" class="no-print">
        <a href="<?= $basePath ?>/progress-reports/<?= $student['id'] ?>" class="no-print-btn" style="background-color: #6c757d; text-decoration: none;">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="margin-right:4px;">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Back to Progress Report
        </a>
        <button onclick="window.print()" class="no-print-btn">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="margin-right:2px;">
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm7 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2zM5 10h6a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1z"/>
            </svg>
            Print SF9 Report Card
        </button>
    </div>

    <!-- PAGE 1: OUTER COVER (Back Cover on Left | Front Cover on Right) -->
    <div class="print-page">
        <div class="cover-grid">
            
            <!-- LEFT COLUMN: BACK COVER -->
            <div class="cover-left">
                <div>
                    <!-- ATTENDANCE RECORD -->
                    <div class="attendance-section">
                        <div class="section-title">ATTENDANCE RECORD</div>
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th style="width: 140px; text-align: left; font-size: 8pt;"></th>
                                    <?php foreach ($activeMonths as $mNum => $m): ?>
                                        <th style="font-size: 8pt;"><?= strtoupper($m['name']) ?></th>
                                    <?php endforeach; ?>
                                    <th style="font-size: 8pt;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: left; font-weight: bold; font-size: 8pt;">No. of School Days</td>
                                    <?php 
                                    $totalSDays = 0;
                                    foreach ($activeMonths as $mNum => $m): 
                                        $totalSDays += $m['school_days'];
                                    ?>
                                        <td><?= $m['school_days'] ?></td>
                                    <?php endforeach; ?>
                                    <td style="font-weight: bold;"><?= $totalSDays ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; font-weight: bold; font-size: 8pt;">No. of School Days (Present)</td>
                                    <?php 
                                    $totalPDays = 0;
                                    foreach ($activeMonths as $mNum => $m): 
                                        $totalPDays += $m['present'];
                                    ?>
                                        <td><?= $m['present'] ?></td>
                                    <?php endforeach; ?>
                                    <td style="font-weight: bold;"><?= $totalPDays ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; font-weight: bold; font-size: 8pt;">No. of School Days (Absent)</td>
                                    <?php 
                                    $totalADays = 0;
                                    foreach ($activeMonths as $mNum => $m): 
                                        $totalADays += $m['absent'];
                                    ?>
                                        <td><?= $m['absent'] ?></td>
                                    <?php endforeach; ?>
                                    <td style="font-weight: bold;"><?= $totalADays ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- GUIDE FOR RATING -->
                    <div class="rating-guide-section">
                        <div class="section-title">GUIDE FOR RATING</div>
                        <table class="rating-guide-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">SYMBOL</th>
                                    <th style="width: 130px;">DESCRIPTION</th>
                                    <th>EXPLANATION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="symbol">P</td>
                                    <td class="desc">Proficient</td>
                                    <td class="expl">The child always manifests the skills</td>
                                </tr>
                                <tr>
                                    <td class="symbol">AP</td>
                                    <td class="desc">Approaching Proficiency</td>
                                    <td class="expl">The child manifests the skills most of the time</td>
                                </tr>
                                <tr>
                                    <td class="symbol">D</td>
                                    <td class="desc">Developing</td>
                                    <td class="expl">The child sometime manifests the skills</td>
                                </tr>
                                <tr>
                                    <td class="symbol">B</td>
                                    <td class="desc">Beginning</td>
                                    <td class="expl">The child seldom manifests the skills</td>
                                </tr>
                                <tr>
                                    <td class="symbol">NO/NA</td>
                                    <td class="desc">Not Observed/Not Applicable</td>
                                    <td class="expl">No manifestations of skills at all / Not Applicable</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TEACHER'S REMARKS -->
                    <div class="remarks-section">
                        <div class="section-title">TEACHER'S REMARKS</div>
                        <div class="remarks-lines">
                            <div class="remark-row">
                                <span class="remark-quarter">1st</span>
                                <span class="remark-line underline-value"><?= htmlspecialchars($q1Teacher) ?></span>
                            </div>
                            <div class="remark-row">
                                <span class="remark-quarter">2nd</span>
                                <span class="remark-line underline-value"><?= htmlspecialchars($q2Teacher) ?></span>
                            </div>
                            <div class="remark-row">
                                <span class="remark-quarter">3rd</span>
                                <span class="remark-line underline-value"><?= htmlspecialchars($q3Teacher) ?></span>
                            </div>
                            <div class="remark-row">
                                <span class="remark-quarter">4th</span>
                                <span class="remark-line underline-value"><?= htmlspecialchars($q4Teacher) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PARENT'S / GUARDIAN'S COMMENTS AND SIGNATURE -->
                <div class="parent-comments-section" style="margin-top: 15px;">
                    <div class="section-title">PARENT'S / GUARDIAN'S COMMENTS AND SIGNATURE</div>
                    <table class="parent-comments-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">QUARTER</th>
                                <th>COMMENTS</th>
                                <th style="width: 140px; text-align: center;">SIGNATURE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (['1st', '2nd', '3rd', '4th'] as $qText): 
                                $qSig = getQuarterRemark($reportRemarks, $qText, 'parent');
                                $commentText = $qSig['remark_text'] ?? '';
                                $sigName = $qSig['signature_name'] ?? '';
                                $sigData = $qSig['signature_data'] ?? '';
                            ?>
                                <tr>
                                    <td class="quarter-col" style="border-bottom: 1px solid #ddd;"><?= $qText ?></td>
                                    <td class="comment-col underline-value"><?= htmlspecialchars($commentText) ?></td>
                                    <td class="signature-col underline-value" style="position: relative;">
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
            </div>

            <!-- RIGHT COLUMN: FRONT COVER -->
            <div class="cover-right">
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
                                <span class="info-val underline-value"><?= htmlspecialchars($student['latest_school_year'] ?? date('Y') . '-' . (date('Y')+1)) ?></span>
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

        </div>
    </div>

    <!-- PAGE 2: INSIDE PAGE (Quarterly Ratings & Remarks) -->
    <div class="print-page" style="page-break-before: always;">
        <!-- Header -->
        <div class="inside-header">
            <div class="inside-header-item">
                <strong>LEARNER:</strong> <span><?= htmlspecialchars($student['student_name']) ?></span>
            </div>
            <div class="inside-header-item">
                <strong>LRN:</strong> <span><?= htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($student['lrn'] ?? null)) ?></span>
            </div>
            <div class="inside-header-item">
                <strong>SCHOOL YEAR:</strong> <span><?= htmlspecialchars($student['latest_school_year'] ?? date('Y') . '-' . (date('Y')+1)) ?></span>
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
                    <th class="rating-col">Q1</th>
                    <th class="rating-col">Q2</th>
                    <th class="rating-col">Q3</th>
                    <th class="rating-col">Q4</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ratingsGrouped)): ?>
                    <tr>
                        <td colspan="5" style="padding:15px; text-align:center; color: #555;">No domain ratings recorded yet. Initialize PDSP and observe activities to populate.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ratingsGrouped as $domain => $indicators): ?>
                        <tr class="domain-row">
                            <td colspan="5" style="font-size: 8.5pt;"><?= htmlspecialchars($domain) ?></td>
                        </tr>
                        <?php foreach ($indicators as $indicator => $qval): ?>
                            <tr>
                                <td style="padding-left:15px; font-weight:normal; font-size: 8.5pt;"><?= htmlspecialchars($indicator) ?></td>
                                <td class="rating-col"><?= htmlspecialchars($qval[1] ?? '—') ?></td>
                                <td class="rating-col"><?= htmlspecialchars($qval[2] ?? '—') ?></td>
                                <td class="rating-col"><?= htmlspecialchars($qval[3] ?? '—') ?></td>
                                <td class="rating-col"><?= htmlspecialchars($qval[4] ?? '—') ?></td>
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

        <!-- Narrative Summary / Comments at Bottom of Inside Page -->
        <?php if ($progressReport && (!empty($progressReport['progress_summary']) || !empty($progressReport['teacher_remarks']))): ?>
            <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <?php if (!empty($progressReport['progress_summary'])): ?>
                    <div style="border: 1px solid var(--border-color); padding: 8px;">
                        <strong>General Progress Summary Narrative:</strong>
                        <p style="margin: 4px 0 0 0; text-align: justify; font-size: 8.5pt; line-height: 1.2; font-style: italic;">
                            <?= htmlspecialchars($progressReport['progress_summary']) ?>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($progressReport['teacher_remarks'])): ?>
                    <div style="border: 1px solid var(--border-color); padding: 8px;">
                        <strong>Teacher's Remark / Recommendations:</strong>
                        <p style="margin: 4px 0 0 0; text-align: justify; font-size: 8.5pt; line-height: 1.2; font-style: italic;">
                            <?= htmlspecialchars($progressReport['teacher_remarks']) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
