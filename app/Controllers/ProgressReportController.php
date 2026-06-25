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
        if ($activeTab !== 'report') {
            $activeTab = 'report';
        }
        $canEdit = RoleMiddleware::hasPermission('progress_report.manage') && (!$progressReport || $progressReport['status'] !== 'finalized');

        $activeDomains = $this->model->getActiveDomains();
        
        $p7Averages = $this->model->getProcess7DomainAverages($studentId);
        $p7AvgMap = [];
        foreach ($p7Averages as $p7) {
            $p7AvgMap[$p7['domain']] = (float)$p7['avg_score'];
        }

        $gradeEntries = $this->model->getGradeEntries($studentId, $quarter);
        $entriesMap = [];
        foreach ($gradeEntries as $entry) {
            $entriesMap[$entry['domain']][$entry['source']] = (float)$entry['score'];
        }

        if ($activeTab === 'attendance') {
            $attendanceRecords = $this->model->getAttendanceRecords($studentId);
            $autoDates = $this->model->getAutoAttendanceFromLogs($studentId);
        } elseif ($activeTab === 'report') {
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

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
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

        $schoolDays = $_POST['school_days'] ?? [];
        $attendanceData = [
            'school_days' => $schoolDays
        ];

        $activeDomains = $this->model->getActiveDomains();
        $quarter = $_POST['quarter'] ?? '1st Quarter';
        
        $gradeEntries = $this->model->getGradeEntries($studentId, $quarter);
        $entriesMap = [];
        foreach ($gradeEntries as $entry) {
            $entriesMap[$entry['domain']][$entry['source']] = (float)$entry['score'];
        }
        
        $p7Averages = $this->model->getProcess7DomainAverages($studentId);
        $p7AvgMap = [];
        foreach ($p7Averages as $p7) {
            $p7AvgMap[$p7['domain']] = (float)$p7['avg_score'];
        }

        $ratings = [];
        foreach ($activeDomains as $dom) {
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
            
            if ($combined !== null) {
                if ($combined >= 85) $ratingCode = 'P';
                elseif ($combined >= 70) $ratingCode = 'AP';
                elseif ($combined >= 50) $ratingCode = 'D';
                else $ratingCode = 'B';
            } else {
                $ratingCode = 'NO-NA';
            }
            $ratings[$dom] = $ratingCode;
        }

        $this->model->upsertProgressReport((int) $iep['id'], $studentId, $this->userId, [
            'school_year' => $_POST['school_year'] ?? '',
            'quarter' => $quarter,
            'attendance_summary' => json_encode($attendanceData),
            'progress_summary' => trim($_POST['progress_summary'] ?? ''),
            'teacher_remarks' => trim($_POST['teacher_remarks'] ?? ''),
            'ratings' => $ratings,
            'status' => $_POST['status'] ?? 'draft',
        ]);

        $_SESSION['success'] = 'Progress report saved successfully.';
        header('Location: ' . $this->basePath . '/progress-reports/' . $studentId . '?tab=report&quarter=' . urlencode($quarter));
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
