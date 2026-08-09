<?php
// Part of: SignED — Process 8 Progress Report Card
// Last modified: 2026-06-16
// Part of: SPED LMS — Process 8 Controller

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/ProgressReportModel.php';
require_once __DIR__ . '/../Models/IEPModel.php';

class ProgressReportController {
    private ProgressReportModel $model;
    private IEPModel $iepModel;
    private int $userId;
    private string $userRole;
    private string $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $this->userId = (int) $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model = new ProgressReportModel();
        $this->iepModel = new IEPModel();
    }

    public function index(): void {
        if ($this->userRole === 'parent') {
            RoleMiddleware::check('progress_report.view_own_child');
            $reports = $this->model->getProgressReportsForParent($this->userId);
        } else {
            RoleMiddleware::check('progress_report.view');
            $reports = $this->model->getProgressReports();
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        require_once __DIR__ . '/../Views/progress-reports/index.php';
    }

    public function attendanceIndex(): void {
        RoleMiddleware::check('progress_report.view');

        if ($this->userRole === 'parent') {
            $learners = $this->model->getAttendanceLearnersForParent($this->userId);
        } else {
            $learners = $this->model->getAttendanceLearners();
        }
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/progress-reports/attendance-list.php';
    }

    public function show(int $studentId): void {
        if ($this->userRole === 'parent') {
            RoleMiddleware::check('progress_report.view_own_child');
            if (!$this->model->isParentOfStudent($this->userId, $studentId)) {
                $_SESSION['error'] = 'You may only view progress reports for your child.';
                header('Location: ' . $this->basePath . '/progress-reports');
                exit;
            }
        } else {
            RoleMiddleware::check('progress_report.view');
        }

        $student = $this->model->getStudent($studentId);
        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            header('Location: ' . $this->basePath . '/progress-reports');
            exit;
        }

        $iep = $this->model->getLatestIepForStudent($studentId);
        if (!$iep) {
            $_SESSION['error'] = 'No IEP record found for this student.';
            header('Location: ' . $this->basePath . '/progress-reports');
            exit;
        }

        $progressReport = $this->model->getLatestProgressReportByIepId((int) $iep['id']);

        $quarter = $_GET['quarter'] ?? ($progressReport['quarter'] ?? '1st Quarter');
        $activeTab = $_GET['tab'] ?? 'report';
        if ($activeTab === 'attendance') {
            header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '/attendance');
            exit;
        }
        $allowedTabs = ['report', 'indicators', 'transfer'];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'report';
        }
        $canEdit = RoleMiddleware::hasPermission('progress_report.manage') && (!$progressReport || $progressReport['status'] !== 'finalized');

        if ($activeTab === 'attendance') {
            $attendanceRecords = $this->model->getAttendanceRecords($studentId);
            $autoDates = $this->model->getAutoAttendanceFromLogs($studentId);
        } elseif ($activeTab === 'report' || $activeTab === 'transfer') {
            // Get F2F present dates and online dates for attendance aggregation
            $f2fStmt = $this->model->getAttendanceRecords($studentId);
            $f2fPresentDates = [];
            foreach ($f2fStmt as $r) {
                if ($r['status'] === 'present' && $r['source'] === 'manual') {
                    $f2fPresentDates[] = $r['date'];
                }
            }
            $onlineDates = $this->model->getAutoAttendanceFromLogs($studentId);
            $allPresentDates = array_unique(array_merge($f2fPresentDates, $onlineDates));

            $months = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'];
            $presentCounts = array_fill_keys($months, 0);
            foreach ($allPresentDates as $dateStr) {
                $time = strtotime($dateStr);
                if ($time) {
                    $mName = date('M', $time);
                    if (in_array($mName, $months)) {
                        $presentCounts[$mName]++;
                    }
                }
            }

            $attendanceSummary = [];
            if ($progressReport) {
                $attendanceSummary = json_decode($progressReport['attendance_summary'] ?? '', true) ?: [];
            }
            
            $remarksList = [];
            if ($progressReport) {
                $remarksList = $this->model->getReportRemarks((int)$progressReport['id']);
            }
            $remarksMap = [];
            foreach ($remarksList as $rem) {
                $remarksMap[$rem['quarter']][$rem['remark_type']] = [
                    'text' => $rem['remark_text'],
                    'signature' => $rem['signature_name'],
                    'signature_data' => $rem['signature_data'] ?? ''
                ];
            }
        }

        $sf9Indicators = [];
        $quarterlyRatingsMap = [];
        $pdspRecordId = null;
        if ($activeTab === 'indicators') {
            $sf9Indicators = $this->model->getSf9Indicators();
            $pdspRecordId = $this->model->getPdspRecordIdForStudent($studentId);
            if ($pdspRecordId) {
                $this->model->ensureQuarterlyRatingsSeeded($studentId, $pdspRecordId);
                $quarterlyRatingsMap = $this->model->getQuarterlyRatingsMap($studentId, $pdspRecordId);
            }
        }

        $canPrintReportCard = $this->userRole === 'admin'
            || (in_array($this->userRole, ['principal', 'guidance'], true) && RoleMiddleware::hasPermission('report_card.view'))
            || ($this->userRole === 'parent' && RoleMiddleware::hasPermission('report_card.view_own_child') && (int)($student['parent_id'] ?? 0) === $this->userId)
            || ($this->userRole === 'sped_teacher' && RoleMiddleware::hasPermission('progress_report.manage'));

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;

        if ($this->userRole === 'parent') {
            $pdspRecordId = $this->model->getPdspRecordIdForStudent($studentId);
            $ratings = [];
            if ($pdspRecordId) {
                $ratings = $this->model->getQuarterlyRatings($studentId, $pdspRecordId);
            }
            $ratingsGrouped = [];
            foreach ($ratings as $r) {
                $dom = $r['domain'];
                $ind = $r['indicator_text'];
                $q = (int)$r['quarter'];
                $val = $r['rating'];
                if (!isset($ratingsGrouped[$dom])) {
                    $ratingsGrouped[$dom] = [];
                }
                if (!isset($ratingsGrouped[$dom][$ind])) {
                    $ratingsGrouped[$dom][$ind] = [1 => '—', 2 => '—', 3 => '—', 4 => '—'];
                }
                $ratingsGrouped[$dom][$ind][$q] = $val ?: '—';
            }

            $signatures = [];
            $spedTeacherName = '—';
            $activeIepId = (int)$iep['id'];
            if ($activeIepId) {
                $signatures = $this->model->getIepSignatories($activeIepId);
                $spedTeacherName = $this->model->getDraftingSpedTeacherName((int)$iep['drafted_by']);
            }

            $reportRemarks = [];
            if ($progressReport) {
                $remarksRows = $this->model->getReportRemarks((int)$progressReport['id']);
                foreach ($remarksRows as $row) {
                    $qKey = $row['quarter'];
                    $type = $row['remark_type'];
                    $reportRemarks[$qKey][$type] = $row;
                }
            }

            $principalName = $this->model->getActivePrincipalName() ?: 'DAISY LYN A. BUENAFE';

            // Months layout
            $monthsList = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'];
            $monthsMapped = [];
            $monthsMapIndex = [
                'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11,
                'Dec' => 12, 'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5
            ];
            foreach ($monthsList as $mName) {
                $sDays = (int)($attendanceSummary['school_days'][$mName] ?? 0);
                $pDays = (int)($presentCounts[$mName] ?? 0);
                if ($pDays > $sDays && $sDays > 0) {
                    $pDays = $sDays;
                }
                $aDays = max(0, $sDays - $pDays);
                $monthsMapped[$monthsMapIndex[$mName]] = [
                    'name' => $mName,
                    'school_days' => $sDays,
                    'present' => $pDays,
                    'absent' => $aDays
                ];
            }
            $months = $monthsMapped;

            require_once __DIR__ . '/../Views/progress-reports/parent_view.php';
            exit;
        }

        require_once __DIR__ . '/../Views/progress-reports/show.php';
    }

    public function store(int $studentId): void {
        RoleMiddleware::check('progress_report.manage');

        $student = $this->model->getStudent($studentId);
        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            header('Location: ' . $this->basePath . '/progress-reports');
            exit;
        }

        $iep = $this->model->getLatestIepForStudent($studentId);
        if (!$iep) {
            $_SESSION['error'] = 'Cannot save progress report without an existing IEP record.';
            header('Location: ' . $this->basePath . '/progress-reports');
            exit;
        }

        $age = $_POST['age'] ?? '';
        if ($age !== '') {
            $this->model->updateStudentDetails($studentId, (int)$age);
        }

        $existingReport = $this->model->getLatestProgressReportByIepId((int) $iep['id']);
        
        $schoolDays = $_POST['school_days'] ?? [];
        if (!empty($schoolDays)) {
            $attendanceData = [
                'school_days' => $schoolDays
            ];
        } else {
            $attendanceData = $existingReport ? (json_decode($existingReport['attendance_summary'] ?? '{}', true) ?: []) : [];
        }

        $quarter = $_POST['quarter'] ?? ($existingReport['quarter'] ?? '1st Quarter');

        // Handle transfer details
        $transferDetails = [];
        if ($existingReport && !empty($existingReport['transfer_details'])) {
            $transferDetails = json_decode($existingReport['transfer_details'], true) ?: [];
        }

        if (isset($_POST['admitted_to'])) {
            $transferDetails['admitted_to'] = trim($_POST['admitted_to']);
        }
        if (isset($_POST['eligible_for_admission_to'])) {
            $transferDetails['eligible_for_admission_to'] = trim($_POST['eligible_for_admission_to']);
        }
        if (isset($_POST['cancellation_admitted_in'])) {
            $transferDetails['cancellation_admitted_in'] = trim($_POST['cancellation_admitted_in']);
        }
        if (isset($_POST['cancellation_date'])) {
            $transferDetails['cancellation_date'] = trim($_POST['cancellation_date']);
        }

        $this->model->upsertProgressReport((int) $iep['id'], $studentId, $this->userId, [
            'school_year' => $_POST['school_year'] ?? ($existingReport['school_year'] ?? ''),
            'quarter' => $quarter,
            'attendance_summary' => json_encode($attendanceData),
            'progress_summary' => isset($_POST['progress_summary']) ? trim($_POST['progress_summary']) : ($existingReport['progress_summary'] ?? ''),
            'teacher_remarks' => isset($_POST['teacher_remarks']) ? trim($_POST['teacher_remarks']) : ($existingReport['teacher_remarks'] ?? ''),
            'ratings' => [],
            'status' => $_POST['status'] ?? ($existingReport['status'] ?? 'draft'),
            'transfer_details' => json_encode($transferDetails)
        ]);

        $activeTab = $_POST['active_tab'] ?? 'report';

        $_SESSION['success'] = 'Progress report saved successfully.';
        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=' . urlencode($activeTab) . '&quarter=' . urlencode($quarter));
        exit;
    }

    public function saveGrades(int $studentId): void {
        RoleMiddleware::check('progress_report.manage');

        $quarter = $_POST['quarter'] ?? '1st Quarter';
        $domains = $_POST['domains'] ?? [];

        foreach ($domains as $domain => $scores) {
            if (isset($scores['manual']) && $scores['manual'] !== '') {
                $this->model->saveGradeEntry($studentId, $quarter, $domain, 'manual', (float)$scores['manual'], $this->userId);
            }
            if (isset($scores['auto']) && $scores['auto'] !== '') {
                $this->model->saveGradeEntry($studentId, $quarter, $domain, 'auto', (float)$scores['auto'], $this->userId);
            }
        }

        $_SESSION['success'] = 'Grades saved successfully.';
        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=grades&quarter=' . urlencode($quarter));
        exit;
    }

    public function saveQuarterlyRatings(int $studentId): void {
        RoleMiddleware::check('progress_report.manage');

        $student = $this->model->getStudent($studentId);
        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            header('Location: ' . $this->basePath . '/progress-reports');
            exit;
        }

        $iep = $this->model->getLatestIepForStudent($studentId);
        if (!$iep) {
            $_SESSION['error'] = 'No IEP record found for this student.';
            header('Location: ' . $this->basePath . '/progress-reports');
            exit;
        }

        $progressReport = $this->model->getLatestProgressReportByIepId((int) $iep['id']);
        if ($progressReport && $progressReport['status'] === 'finalized') {
            $_SESSION['error'] = 'This progress report is finalized and cannot be edited.';
            header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=indicators');
            exit;
        }

        $pdspRecordId = $this->model->getPdspRecordIdForStudent($studentId);
        if (!$pdspRecordId) {
            $_SESSION['error'] = 'No PDSP record found. Complete the IEP meeting and sign the PDSP first.';
            header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=indicators');
            exit;
        }

        $ratings = $_POST['ratings'] ?? [];
        if (!is_array($ratings)) {
            $ratings = [];
        }

        $this->model->ensureQuarterlyRatingsSeeded($studentId, $pdspRecordId);
        $this->model->saveQuarterlyRatings($studentId, $pdspRecordId, $ratings);

        $quarter = $_POST['quarter'] ?? '1st Quarter';
        $_SESSION['success'] = 'SF9 indicator ratings saved successfully.';
        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=indicators&quarter=' . urlencode($quarter));
        exit;
    }

    public function saveRemarks(int $studentId): void {
        $reportId = (int)($_POST['progress_report_id'] ?? 0);
        $report = $this->model->getProgressReportById($reportId);
        if (!$report) {
            $_SESSION['error'] = 'Progress report not found.';
            header('Location: ' . $this->basePath . '/progress-reports/' . $studentId);
            exit;
        }

        $quarter = $_POST['quarter'] ?? '1st Quarter';
        
        if ($this->userRole === 'parent') {
            RoleMiddleware::check('progress_report.view_own_child');
            if (!$this->model->isParentOfStudent($this->userId, $studentId)) {
                $_SESSION['error'] = 'Unauthorized.';
                header('Location: ' . $this->basePath . '/progress-reports/' . $studentId);
                exit;
            }
            $text = trim($_POST['parent_comment'] ?? '');
            $sig = trim($_POST['parent_signature'] ?? '');
            $sigData = trim($_POST['parent_signature_data'] ?? '');
            $existing = $this->model->getReportRemark($reportId, $quarter, 'parent');
            if ($sigData === '' && empty($existing['signature_data'])) {
                $_SESSION['error'] = 'Parent/guardian signature is required.';
                header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=report&quarter=' . urlencode($quarter));
                exit;
            }
            $this->model->saveReportRemarkWithSignatureData($reportId, $quarter, 'parent', $text, $sig, $sigData);
            $_SESSION['success'] = 'Comment and signature saved.';
        } else {
            RoleMiddleware::check('progress_report.manage');
            $text = trim($_POST['teacher_remark'] ?? '');
            $sig = trim($_POST['teacher_signature'] ?? '');
            $sigData = trim($_POST['teacher_signature_data'] ?? '');
            $existing = $this->model->getReportRemark($reportId, $quarter, 'teacher');
            if ($sigData === '' && empty($existing['signature_data'])) {
                $_SESSION['error'] = 'Teacher signature is required.';
                header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=report&quarter=' . urlencode($quarter));
                exit;
            }
            $this->model->saveReportRemarkWithSignatureData($reportId, $quarter, 'teacher', $text, $sig, $sigData);
            $_SESSION['success'] = 'Remarks and signature saved.';
        }

        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=report&quarter=' . urlencode($quarter));
        exit;
    }

    public function finalize(int $reportId): void {
        RoleMiddleware::check('progress_report.manage');

        $report = $this->model->getProgressReportById($reportId);
        if (!$report) {
            $_SESSION['error'] = 'Progress report not found.';
            header('Location: ' . $this->basePath . '/progress-reports');
            exit;
        }

        // Hard gate: check parent signature in report_remarks table
        $remarks = $this->model->getReportRemarks($reportId);
        $hasParentSignature = false;
        foreach ($remarks as $rem) {
            if ($rem['remark_type'] === 'parent' && (!empty($rem['signature_data']) || !empty($rem['signature_name']))) {
                $hasParentSignature = true;
                break;
            }
        }

        if (!$hasParentSignature) {
            $_SESSION['error'] = 'Progress report cannot be finalized without parent/guardian signature.';
            header('Location: ' . $this->basePath . '/progress-reports/' . $report['student_id'] . '?tab=report');
            exit;
        }

        $docPath = $report['document_path'] ?? null;
        if (!empty($_FILES['signed_document']['name'])) {
            $dir = __DIR__ . '/../../public/uploads/progress_reports/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $ext = pathinfo($_FILES['signed_document']['name'], PATHINFO_EXTENSION);
            $filename = 'progress_report_' . $reportId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['signed_document']['tmp_name'], $dir . $filename)) {
                $docPath = '/uploads/progress_reports/' . $filename;
            }
        }

        $this->model->finalizeProgressReportWithDoc($reportId, $docPath);
        $_SESSION['success'] = 'Progress report finalized successfully.';
        header('Location: ' . $this->basePath . '/progress-reports/' . $report['student_id'] . '?tab=report');
        exit;
    }

    public function attendance(int $studentId): void {
        if ($this->userRole === 'parent') {
            RoleMiddleware::check('progress_report.view_own_child');
            if (!$this->model->isParentOfStudent($this->userId, $studentId)) {
                $_SESSION['error'] = 'You may only view attendance for your child.';
                header('Location: ' . $this->basePath . '/progress-reports');
                exit;
            }
        } else {
            RoleMiddleware::check('progress_report.view');
        }

        $student = $this->model->getStudent($studentId);
        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            header('Location: ' . $this->basePath . '/attendance-log');
            exit;
        }

        $iep = $this->model->getLatestIepForStudent($studentId);
        if (!$iep) {
            $_SESSION['error'] = 'No IEP record found for this student.';
            header('Location: ' . $this->basePath . '/attendance-log');
            exit;
        }

        $yearMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            $yearMonth = date('Y-m');
        }

        $attendanceRecords = $this->model->getAttendanceRecordsForMonth($studentId, $yearMonth);
        $allAttendanceRecords = $this->model->getAttendanceRecords($studentId);
        $autoDates = $this->model->getAutoAttendanceFromLogs($studentId);
        $stats = $this->model->getAttendanceStats($studentId, $yearMonth);
        $canEdit = RoleMiddleware::hasPermission('progress_report.manage');

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/progress-reports/attendance.php';
    }

    public function saveAttendance(int $studentId): void {
        RoleMiddleware::check('progress_report.manage');

        $date = $_POST['attendance_date'] ?? '';
        $status = $_POST['status'] ?? 'present';
        $month = preg_match('/^\d{4}-\d{2}/', $date) ? substr($date, 0, 7) : date('Y-m');

        if (empty($date)) {
            $_SESSION['error'] = 'Attendance date is required.';
            header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '/attendance?month=' . urlencode($month));
            exit;
        }

        $saved = $this->model->saveAttendanceRecord($studentId, $date, $status, 'manual', $this->userId);

        if ($saved) {
            $_SESSION['success'] = 'Attendance recorded successfully.';
        } else {
            $_SESSION['error'] = 'Failed to record attendance.';
        }

        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '/attendance?month=' . urlencode($month));
        exit;
    }

    public function importAttendance(int $studentId): void {
        RoleMiddleware::check('progress_report.manage');

        $yearMonth = $_POST['sf2_month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            $yearMonth = date('Y-m');
        }

        if (empty($_FILES['sf2_file']['tmp_name']) || !is_uploaded_file($_FILES['sf2_file']['tmp_name'])) {
            $_SESSION['error'] = 'Please choose an SF2 CSV file to import.';
            header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '/attendance?month=' . urlencode($yearMonth));
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['sf2_file']['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $_SESSION['error'] = 'SF2 import currently accepts CSV files. Export the LIS SF2 sheet as CSV, then upload it here.';
            header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '/attendance?month=' . urlencode($yearMonth));
            exit;
        }

        $blankAsPresent = !empty($_POST['blank_as_present']);
        $result = $this->model->importSf2CsvForStudent($studentId, $_FILES['sf2_file']['tmp_name'], $yearMonth, $this->userId, $blankAsPresent);

        if (!empty($result['errors'])) {
            $_SESSION['error'] = implode(' ', $result['errors']);
        } else {
            $_SESSION['success'] = 'SF2 import complete: ' . (int)$result['imported'] . ' day(s) imported, ' . (int)$result['skipped'] . ' skipped.';
        }

        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '/attendance?month=' . urlencode($yearMonth));
        exit;
    }

    public function deleteAttendance(int $studentId, int $id): void {
        RoleMiddleware::check('progress_report.manage');

        $deleted = $this->model->deleteAttendanceRecord($id, $studentId);

        if ($deleted) {
            $_SESSION['success'] = 'Attendance entry deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete attendance entry.';
        }

        $month = $_GET['month'] ?? date('Y-m');
        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '/attendance?month=' . urlencode($month));
        exit;
    }
}
