<?php
// DO NOT ALTER WITHOUT APPROVAL — SF9 Module
// Last modified: 2026-06-28

require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/IEPModel.php';
require_once __DIR__ . '/../Models/PDSPModel.php';

class ReportCardController {
    private $db;
    private $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
        $this->db = Database::getInstance()->getConnection();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    public function printReportCard($studentId) {
        $studentId = (int)$studentId;
        if (!$studentId) {
            http_response_code(400);
            echo "Invalid student ID.";
            exit;
        }

        // Fetch student record
        $studentModel = new StudentModel();
        $student = $studentModel->findById($studentId);
        if (!$student) {
            http_response_code(404);
            echo "Student not found.";
            exit;
        }

        // Access control enforcement
        $role = $_SESSION['role'] ?? '';
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $allowed = false;

        require_once __DIR__ . '/../Middleware/RoleMiddleware.php';

        if ($role === 'admin') {
            $allowed = true;
        } elseif (($role === 'principal' || $role === 'guidance') && RoleMiddleware::hasPermission('report_card.view')) {
            $allowed = true;
        } elseif ($role === 'general_teacher' && RoleMiddleware::hasPermission('report_card.view')) {
            $stmtAssign = $this->db->prepare(
                "SELECT 1 FROM general_teacher_assignments gta WHERE gta.student_id = :sid AND gta.general_teacher_id = :uid LIMIT 1"
            );
            $stmtAssign->execute(['sid' => $studentId, 'uid' => $userId]);
            if ($stmtAssign->fetchColumn()) {
                $allowed = true;
            }
        } elseif ($role === 'parent' && RoleMiddleware::hasPermission('report_card.view_own_child')) {
            // Check if this student is their child
            $parentId = (int)($student['parent_id'] ?? 0);
            if ($parentId === $userId) {
                $allowed = true;
            }
        } elseif ($role === 'sped_teacher' && RoleMiddleware::hasPermission('report_card.generate')) {
            // Check if teacher drafted the student's IEP
            $stmtIepChk = $this->db->prepare("SELECT id FROM iep_records WHERE student_id = :sid AND drafted_by = :uid LIMIT 1");
            $stmtIepChk->execute(['sid' => $studentId, 'uid' => $userId]);
            if ($stmtIepChk->fetchColumn()) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            http_response_code(403);
            echo "Access denied.";
            exit;
        }

        // Fetch active PDSP record to link ratings
        $stmtPdsp = $this->db->prepare("SELECT id FROM pdsp_records WHERE student_id = :sid AND status IN ('signed', 'complete') LIMIT 1");
        $stmtPdsp->execute(['sid' => $studentId]);
        $pdspRecordId = $stmtPdsp->fetchColumn();
        if (!$pdspRecordId) {
            // Fallback
            $stmtPdsp = $this->db->prepare("SELECT id FROM pdsp_records WHERE student_id = :sid LIMIT 1");
            $stmtPdsp->execute(['sid' => $studentId]);
            $pdspRecordId = $stmtPdsp->fetchColumn();
        }

        // Fetch quarterly ratings
        $ratings = [];
        if ($pdspRecordId) {
            $stmtRatings = $this->db->prepare("
                SELECT domain, indicator_text, quarter, rating, observation 
                FROM student_quarterly_ratings 
                WHERE student_id = :sid AND pdsp_record_id = :pid
                ORDER BY domain ASC, id ASC
            ");
            $stmtRatings->execute(['sid' => $studentId, 'pid' => $pdspRecordId]);
            $ratings = $stmtRatings->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        // Group ratings by Domain and Indicator
        $ratingsGrouped = [];
        $domainRemarks = [1 => [], 2 => [], 3 => [], 4 => []]; // track comments per quarter
        foreach ($ratings as $r) {
            $dom = $r['domain'];
            $ind = $r['indicator_text'];
            $q = (int)$r['quarter'];
            $val = $r['rating'];
            $obs = trim($r['observation'] ?? '');

            if (!isset($ratingsGrouped[$dom])) {
                $ratingsGrouped[$dom] = [];
            }
            if (!isset($ratingsGrouped[$dom][$ind])) {
                $ratingsGrouped[$dom][$ind] = [1 => '—', 2 => '—', 3 => '—', 4 => '—'];
            }
            $ratingsGrouped[$dom][$ind][$q] = $val ?: '—';

            // Gather observation remarks for summary
            if ($obs !== '') {
                $domainRemarks[$q][$dom][] = $obs;
            }
        }

        // Fetch finalized IEP meeting/signatories
        $activeIepId = null;
        $iepRecord = null;
        
        $iepStmt = $this->db->prepare("
            SELECT id, drafted_by, re_evaluation_date, status 
            FROM iep_records 
            WHERE student_id = :sid AND status IN ('signed', 'locked') 
            ORDER BY updated_at DESC LIMIT 1
        ");
        $iepStmt->execute(['sid' => $studentId]);
        $iepRow = $iepStmt->fetch();
        if ($iepRow) {
            $activeIepId = (int)$iepRow['id'];
            $iepRecord = $iepRow;
        } else {
            // Fallback — any IEP for this student
            $iepStmt = $this->db->prepare("
                SELECT id, drafted_by, re_evaluation_date, status 
                FROM iep_records 
                WHERE student_id = :sid 
                ORDER BY created_at DESC LIMIT 1
            ");
            $iepStmt->execute(['sid' => $studentId]);
            $iepRow = $iepStmt->fetch();
            if ($iepRow) {
                $activeIepId = (int)$iepRow['id'];
                $iepRecord = $iepRow;
            }
        }

        // Fetch signatures and teacher
        $signatures = [];
        $spedTeacherName = '—';
        if ($activeIepId) {
            $sigStmt = $this->db->prepare("
                SELECT signatory_role, signatory_name, signature_image_path, signed_at 
                FROM iep_signatories 
                WHERE iep_id = :iep_id
            ");
            $sigStmt->execute(['iep_id' => $activeIepId]);
            $signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Get drafting SPED teacher
            $tStmt = $this->db->prepare("SELECT name FROM users WHERE id = :uid LIMIT 1");
            $tStmt->execute(['uid' => (int)$iepRecord['drafted_by']]);
            $spedTeacherName = $tStmt->fetchColumn() ?: '—';
        }

        // Fetch active progress report for the student
        $progressReport = null;
        $attendanceSummary = [];
        if ($activeIepId) {
            $stmtReport = $this->db->prepare("
                SELECT id, school_year, quarter, attendance_summary, progress_summary, teacher_remarks, status 
                FROM progress_reports 
                WHERE iep_record_id = :iep_id 
                ORDER BY id DESC LIMIT 1
            ");
            $stmtReport->execute(['iep_id' => $activeIepId]);
            $progressReport = $stmtReport->fetch(PDO::FETCH_ASSOC);
        }

        if ($progressReport) {
            $attendanceSummary = json_decode($progressReport['attendance_summary'] ?? '', true) ?: [];
        }

        // Compute dynamic present counts from manual attendance records + online logs
        $f2fStmt = $this->db->prepare("
            SELECT date, status, source 
            FROM attendance_records 
            WHERE student_id = :sid
        ");
        $f2fStmt->execute(['sid' => $studentId]);
        $f2fRows = $f2fStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $f2fPresentDates = [];
        foreach ($f2fRows as $r) {
            if ($r['status'] === 'present' && $r['source'] === 'manual') {
                $f2fPresentDates[] = $r['date'];
            }
        }

        $stmtLogs = $this->db->prepare("
            SELECT DISTINCT DATE(performed_at) AS log_date
            FROM lms_logs
            WHERE student_id = :sid AND action IN ('opened', 'submitted')
        ");
        $stmtLogs->execute(['sid' => $studentId]);
        $onlineDates = $stmtLogs->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $allPresentDates = array_unique(array_merge($f2fPresentDates, $onlineDates));

        $monthsList = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'];
        $presentCounts = array_fill_keys($monthsList, 0);
        foreach ($allPresentDates as $dateStr) {
            $time = strtotime($dateStr);
            if ($time) {
                $mName = date('M', $time);
                if (in_array($mName, $monthsList)) {
                    $presentCounts[$mName]++;
                }
            }
        }

        // Populate monthly attendance grid using progress_reports.attendance_summary + presentCounts
        $months = [];
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
            
            $months[$monthsMapIndex[$mName]] = [
                'name' => $mName,
                'school_days' => $sDays,
                'present' => $pDays,
                'absent' => $aDays
            ];
        }

        // Fetch remarks/signatures from report_remarks table
        $reportRemarks = [];
        if ($progressReport) {
            $stmtRemarks = $this->db->prepare("
                SELECT quarter, remark_type, remark_text, signature_name, signature_data 
                FROM report_remarks 
                WHERE progress_report_id = :report_id
            ");
            $stmtRemarks->execute(['report_id' => $progressReport['id']]);
            $remarksRows = $stmtRemarks->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($remarksRows as $row) {
                $qKey = $row['quarter'];
                $type = $row['remark_type'];
                $reportRemarks[$qKey][$type] = $row;
            }
        }

        // Fetch active principal name
        $pStmt = $this->db->prepare("SELECT name FROM users WHERE role = 'principal' AND status = 'active' ORDER BY id ASC LIMIT 1");
        $pStmt->execute();
        $principalName = $pStmt->fetchColumn() ?: 'DAISY LYN A. BUENAFE';

        $basePath = $this->basePath;
        require __DIR__ . '/../Views/report-card/sf9.php';
    }
}
