<?php
// Part of: SignED — Stage 2 Masterlist & LIS Sync Controller (SF1/SF2)
// Last modified: 2026-08-21

require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../Models/MasterlistModel.php';
require_once __DIR__ . '/../Models/ProgressReportModel.php';

class MasterlistController {
    private MasterlistModel $model;
    private ProgressReportModel $progressModel;
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
        $this->model = new MasterlistModel();
        $this->progressModel = new ProgressReportModel();

        // Security check: masterlist is accessible to teachers, guidance, master teachers, principals, and admins
        RoleMiddleware::checkAny(['sped_teacher', 'master_teacher', 'guidance', 'principal', 'admin']);
    }

    /**
     * Masterlist Dashboard View
     */
    public function index(): void {
        $filters = [];

        if (in_array($this->userRole, ['sped_teacher', 'master_teacher'])) {
            $filters['teacher_id'] = $this->userId;
        }

        if (!empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }
        if (!empty($_GET['disability'])) {
            $filters['disability_type'] = trim($_GET['disability']);
        }
        if (!empty($_GET['track'])) {
            $filters['track'] = trim($_GET['track']);
        }

        $learners = $this->model->getMasterlist($filters);

        // Calculate track stats without search filters for the header pills
        $baseTeacherFilter = [];
        if (in_array($this->userRole, ['sped_teacher', 'master_teacher'])) {
            $baseTeacherFilter['teacher_id'] = $this->userId;
        }
        $allLearnersForStats = $this->model->getMasterlist($baseTeacherFilter);
        $totalCount = count($allLearnersForStats);
        $candidateCount = 0;
        $lmsActiveCount = 0;
        $traditionalCount = 0;
        $syncedCount = 0;

        foreach ($allLearnersForStats as $row) {
            $hasActiveAccount = !empty($row['learner_user_id']) || ($row['learning_track'] === 'lms');
            $isCandidate = ($row['learning_track'] === 'candidate')
                || (!empty($row['survey_has_internet']) && (!empty($row['survey_willing_online']) || !empty($row['willing_digital'])))
                || (!empty($row['has_device']) && !empty($row['willing_digital']))
                || !empty($row['modality_online'])
                || !empty($row['modality_modular_digital'])
                || (($row['lms_track'] ?? '') === 'candidate')
                || (($row['lms_track'] ?? '') === 'signed');

            if ($hasActiveAccount) {
                $lmsActiveCount++;
            } elseif ($isCandidate) {
                $candidateCount++;
            } else {
                $traditionalCount++;
            }

            if (($row['lis_status'] ?? '') === 'synced') {
                $syncedCount++;
            }
        }
        $lmsCount = $candidateCount + $lmsActiveCount;

        $syncLogs = $this->model->getSyncLogs(5);

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $basePath = $this->basePath;
        $role = $this->userRole;
        $currentTrack = $_GET['track'] ?? '';
        require_once __DIR__ . '/../Views/masterlist/index.php';
    }

    /**
     * Export Learner Enrollment Register (Comprehensive Directory CSV stream)
     */
    public function exportSf1(): void {
        $filters = [];
        if (in_array($this->userRole, ['sped_teacher', 'master_teacher'])) {
            $filters['teacher_id'] = $this->userId;
        }

        $learners = $this->model->getMasterlist($filters);

        $filename = 'Learner_Enrollment_Register_' . date('Y-m-d_His') . '.csv';

        // Log export action
        $this->model->logSyncAction($this->userId, 'enrollment_register_export', $filename, count($learners));

        // HTTP Headers for file download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $schoolName = $learners[0]['school_name'] ?? 'SignED SPED Partner School';
        $schoolId = $learners[0]['deped_school_id'] ?? 'N/A';
        $division = $learners[0]['division'] ?? 'Leyte';
        $region = $learners[0]['region'] ?? 'Region VIII';
        $schoolYear = $learners[0]['school_year'] ?? (date('Y') . '-' . (date('Y') + 1));

        // Registry Banner
        fputcsv($output, [
            'SignED — Learner Master Enrollment Register',
            'School Name: ' . $schoolName,
            'School ID: ' . $schoolId,
            'Division: ' . $division,
            'Region: ' . $region,
            'School Year: ' . $schoolYear
        ]);
        fputcsv($output, [
            'Generated: ' . date('F d, Y'),
            'Total Enrolled Learners: ' . count($learners)
        ]);
        fputcsv($output, []); // blank row

        // Detailed Header Columns
        fputcsv($output, [
            'LRN',
            'Learner Name (Last Name, First Name, Middle Name, Ext)',
            'Sex',
            'Birth Date (YYYY-MM-DD)',
            'Age',
            'Mother Tongue',
            'IP / Ethnic Group',
            'Religion',
            'House # / Street / Purok',
            'Barangay',
            'City / Municipality',
            'Province',
            'Father Name (Last Name, First Name, Middle Name)',
            'Father Contact Number',
            'Mother Maiden Name (Last Name, First Name, Middle Name)',
            'Mother Contact Number',
            'Guardian Name (if not Parent)',
            'Guardian Relationship',
            'Guardian Contact Number',
            'Grade Level',
            'Section',
            'Disability Category (SPED)',
            'Learning Track',
            'Enrollment Status',
            'Registry Status'
        ]);

        foreach ($learners as $l) {
            // Format Learner Name
            if (!empty($l['learner_last_name']) && !empty($l['learner_first_name'])) {
                $learnerName = trim($l['learner_last_name'] . ', ' . $l['learner_first_name'] . 
                    (!empty($l['learner_middle_name']) ? ' ' . $l['learner_middle_name'] : '') . 
                    (!empty($l['learner_extension_name']) ? ' ' . $l['learner_extension_name'] : ''));
            } else {
                $learnerName = $l['student_name'] ?? '';
            }

            // Format Father Name
            $fatherName = 'N/A';
            if (!empty($l['father_last_name']) || !empty($l['father_first_name'])) {
                $fatherName = trim(($l['father_last_name'] ?? '') . ', ' . ($l['father_first_name'] ?? '') . 
                    (!empty($l['father_middle_name']) ? ' ' . $l['father_middle_name'] : ''));
                if ($fatherName === ',') $fatherName = 'N/A';
            }

            // Format Mother Name
            $motherName = 'N/A';
            if (!empty($l['mother_maiden_last_name']) || !empty($l['mother_first_name'])) {
                $motherName = trim(($l['mother_maiden_last_name'] ?? '') . ', ' . ($l['mother_first_name'] ?? '') . 
                    (!empty($l['mother_middle_name']) ? ' ' . $l['mother_middle_name'] : ''));
                if ($motherName === ',') $motherName = 'N/A';
            }

            // Format Guardian Name
            $guardianName = 'N/A';
            if (!empty($l['guardian_last_name']) || !empty($l['guardian_first_name'])) {
                $guardianName = trim(($l['guardian_last_name'] ?? '') . ', ' . ($l['guardian_first_name'] ?? '') . 
                    (!empty($l['guardian_middle_name']) ? ' ' . $l['guardian_middle_name'] : ''));
                if ($guardianName === ',') $guardianName = 'N/A';
            }

            // Learning Track
            $hasActiveAccount = !empty($l['learner_user_id']) || ($l['learning_track'] === 'lms');
            $isCandidate = ($l['learning_track'] === 'candidate') || (($l['es_learning_track'] ?? '') === 'candidate');
            $trackLabel = $hasActiveAccount ? 'SignED Interactive LMS (Active)' : ($isCandidate ? 'SignED Candidate' : 'Traditional SEN Track');

            // Registry Status
            $regStatus = !empty($l['lrn']) ? 'Official LRN Registered' : 'Pending LRN Assignment';

            fputcsv($output, [
                $l['lrn'] ?? $l['student_id'] ?? 'Pending LRN',
                $learnerName,
                $l['sex'] ?? '—',
                $l['date_of_birth'] ?? '',
                $l['age'] ?? '',
                !empty($l['mother_tongue']) ? $l['mother_tongue'] : 'Not specified',
                !empty($l['is_indigenous_people']) ? ($l['indigenous_group'] ?: 'Yes') : 'No',
                'Not specified',
                $l['current_house_no'] ?? '',
                $l['current_barangay'] ?? '',
                $l['current_city'] ?? '',
                $l['current_province'] ?? '',
                $fatherName,
                $l['father_contact_number'] ?? '',
                $motherName,
                $l['mother_contact_number'] ?? '',
                $guardianName,
                $guardianName !== 'N/A' ? 'Guardian' : 'N/A',
                $l['guardian_contact_number'] ?? '',
                $l['grade_level_to_enroll'] ?? 'Kinder',
                $l['section_name'] ?? 'SPED Section A',
                $l['disability_type'] ?? 'General SPED',
                $trackLabel,
                ucfirst($l['enrollment_status'] ?? 'Verified'),
                $regStatus
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export Monthly Attendance Register CSV stream
     */
    public function exportSf2(): void {
        $yearMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            $yearMonth = date('Y-m');
        }

        $filters = [];
        if (in_array($this->userRole, ['sped_teacher', 'master_teacher'])) {
            $filters['teacher_id'] = $this->userId;
        }

        $learners = $this->model->getMasterlist($filters);
        $daysInMonth = (int)date('t', strtotime($yearMonth . '-01'));

        $filename = 'Learner_Monthly_Attendance_' . $yearMonth . '_' . date('His') . '.csv';

        // Log export action
        $this->model->logSyncAction($this->userId, 'attendance_register_export', $filename, count($learners));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['SignED — Monthly Learner Attendance Register']);
        fputcsv($output, ['Month:', date('F Y', strtotime($yearMonth . '-01'))]);
        fputcsv($output, []);

        // Build header row: LRN, Name, Day 1..Day N, Total Present, Total Absent
        $header = ['LRN', 'Learner Name'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $header[] = 'Day ' . $d;
        }
        $header[] = 'Total Present';
        $header[] = 'Total Absent';
        fputcsv($output, $header);

        foreach ($learners as $l) {
            $sid = (int)$l['student_record_id'];
            $monthRecords = $this->progressModel->getAttendanceRecordsForMonth($sid, $yearMonth);

            $attMap = [];
            foreach ($monthRecords as $rec) {
                $dayNum = (int)date('j', strtotime($rec['date']));
                $attMap[$dayNum] = strtoupper(substr($rec['status'], 0, 1)); // 'P', 'A', 'T', 'E'
            }

            $row = [
                $l['lrn'] ?? $l['student_id'] ?? 'N/A',
                $l['student_name'] ?? ''
            ];

            $totalPresent = 0;
            $totalAbsent = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $statusChar = $attMap[$d] ?? 'P'; // Default P if present
                $row[] = $statusChar;
                if ($statusChar === 'P' || $statusChar === 'T') {
                    $totalPresent++;
                } elseif ($statusChar === 'A') {
                    $totalAbsent++;
                }
            }

            $row[] = $totalPresent;
            $row[] = $totalAbsent;

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Import official 12-digit DepEd LRNs from LIS exported CSV (2-Way Sync)
     */
    public function importLrn(): void {
        RoleMiddleware::checkAny(['sped_teacher', 'master_teacher', 'principal', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['lrn_csv']['tmp_name'])) {
            $_SESSION['error'] = 'Please choose a valid CSV file to upload.';
            header('Location: ' . $this->basePath . '/masterlist');
            exit;
        }

        $file = $_FILES['lrn_csv']['tmp_name'];
        $filename = $_FILES['lrn_csv']['name'] ?? 'lrn_import.csv';
        $handle = fopen($file, 'r');
        if (!$handle) {
            $_SESSION['error'] = 'Could not open uploaded file.';
            header('Location: ' . $this->basePath . '/masterlist');
            exit;
        }

        $updatedCount = 0;
        $rowNum = 0;

        while (($row = fgetcsv($handle, 2000, ',')) !== false) {
            $rowNum++;
            if ($rowNum === 1 || empty($row[0])) {
                continue; // skip header or empty row
            }

            // Detect columns: Column 0 = LRN or Student ID, Column 1 = Name / LRN
            $col0 = trim($row[0]);
            $col1 = isset($row[1]) ? trim($row[1]) : '';

            $lrn = '';
            $identifier = '';

            // Check if col0 is 12-digit LRN
            if (preg_match('/^\d{12}$/', $col0)) {
                $lrn = $col0;
                $identifier = $col1;
            } elseif (preg_match('/^\d{12}$/', $col1)) {
                $lrn = $col1;
                $identifier = $col0;
            }

            if (!empty($lrn) && !empty($identifier)) {
                if ($this->model->updateLrn($identifier, $lrn)) {
                    $updatedCount++;
                }
            }
        }

        fclose($handle);

        $this->model->logSyncAction($this->userId, 'lrn_update', $filename, $updatedCount);
        $_SESSION['success'] = "Successfully updated official LRNs for {$updatedCount} learner(s)!";

        header('Location: ' . $this->basePath . '/masterlist');
        exit;
    }

    /**
     * Update section / registry information for a learner
     */
    public function updateSection(): void {
        RoleMiddleware::checkAny(['sped_teacher', 'master_teacher', 'admin']);

        $studentId = (int)($_POST['student_id'] ?? 0);
        $sectionName = trim($_POST['section_name'] ?? '');
        $lisStatus = trim($_POST['lis_status'] ?? 'synced');

        if ($studentId > 0 && !empty($sectionName)) {
            $this->model->updateLisInfo($studentId, $sectionName, $lisStatus);
            $_SESSION['success'] = 'Learner section and registry status updated.';
        } else {
            $_SESSION['error'] = 'Invalid student or section name.';
        }

        header('Location: ' . $this->basePath . '/masterlist');
        exit;
    }

    /**
     * Bulk Import Enrolled Learners from Client CSV
     */
    public function bulkImport(): void {
        RoleMiddleware::checkAny(['sped_teacher', 'master_teacher', 'principal', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['bulk_csv']['tmp_name'])) {
            $_SESSION['error'] = 'Please choose a valid CSV file to upload.';
            header('Location: ' . $this->basePath . '/masterlist');
            exit;
        }

        $file = $_FILES['bulk_csv']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $_SESSION['error'] = 'Could not open uploaded file.';
            header('Location: ' . $this->basePath . '/masterlist');
            exit;
        }

        // Resolve School ID
        $db = Database::getInstance()->getConnection();
        $schoolId = 1;
        $stmtUser = $db->prepare("SELECT school_id FROM users WHERE id = :uid LIMIT 1");
        $stmtUser->execute(['uid' => $this->userId]);
        $userSchoolId = (int)$stmtUser->fetchColumn();
        if ($userSchoolId > 0) {
            $schoolId = $userSchoolId;
        } else {
            $stmtSch = $db->query("SELECT id FROM schools ORDER BY id ASC LIMIT 1");
            $firstSchool = (int)$stmtSch->fetchColumn();
            if ($firstSchool > 0) $schoolId = $firstSchool;
        }

        $headers = null;
        $parsedRows = [];

        while (($row = fgetcsv($handle, 2000, ',')) !== false) {
            if ($headers === null) {
                // Strip UTF-8 BOM if present
                if (isset($row[0])) {
                    $row[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $row[0]);
                }
                $candidateHeaders = array_map(fn($h) => strtolower(trim(preg_replace('/[^a-z0-9]/', '', strtolower((string)$h)))), $row);
                $nonEmpty = array_filter($candidateHeaders, fn($h) => $h !== '');
                if (count($nonEmpty) === 0) {
                    continue;
                }

                // Check if this row is an actual table column header row (not school header metadata)
                $isHeader = false;
                $hasNameOrLearner = false;
                $hasOtherCols = false;
                foreach ($candidateHeaders as $cell) {
                    if (strpos($cell, ':') !== false) {
                        continue;
                    }
                    if (strpos($cell, 'name') !== false || strpos($cell, 'learner') !== false || strpos($cell, 'student') !== false) {
                        $hasNameOrLearner = true;
                    }
                    if (strpos($cell, 'lrn') !== false || strpos($cell, 'sex') !== false || strpos($cell, 'gender') !== false || strpos($cell, 'birth') !== false || strpos($cell, 'grade') !== false || strpos($cell, 'section') !== false || strpos($cell, 'track') !== false || strpos($cell, 'day') !== false) {
                        $hasOtherCols = true;
                    }
                }

                if (!($hasNameOrLearner && $hasOtherCols)) {
                    continue; // Skip banner / top school metadata rows
                }

                $headers = $candidateHeaders;
                continue;
            }

            if (empty(array_filter($row))) {
                continue; // Skip blank rows
            }

            $mapped = [
                'lrn' => '',
                'student_name' => '',
                'sex' => '',
                'birth_date' => '',
                'grade_level' => '',
                'section_name' => '',
                'disability_type' => '',
                'learning_track' => 'traditional',
                'parent_name' => '',
                'parent_contact' => '',
                'parent_email' => '',
            ];

            foreach ($headers as $colIdx => $colKey) {
                $val = trim($row[$colIdx] ?? '');
                if (empty($val)) continue;

                if (strpos($colKey, 'lrn') !== false || strpos($colKey, 'learnerreference') !== false) {
                    $mapped['lrn'] = $val;
                } elseif (strpos($colKey, 'learner') !== false || strpos($colKey, 'studentname') !== false || strpos($colKey, 'fullname') !== false || $colKey === 'name') {
                    $mapped['student_name'] = $val;
                } elseif ($colKey === 'lastname' || $colKey === 'last') {
                    $mapped['last_name'] = $val;
                } elseif ($colKey === 'firstname' || $colKey === 'first') {
                    $mapped['first_name'] = $val;
                } elseif ($colKey === 'middlename' || $colKey === 'middle') {
                    $mapped['middle_name'] = $val;
                } elseif (in_array($colKey, ['sex', 'gender'])) {
                    $mapped['sex'] = $val;
                } elseif (strpos($colKey, 'birth') !== false || strpos($colKey, 'dob') !== false) {
                    $mapped['birth_date'] = $val;
                } elseif (strpos($colKey, 'grade') !== false || $colKey === 'level') {
                    $mapped['grade_level'] = $val;
                } elseif (strpos($colKey, 'section') !== false) {
                    $mapped['section_name'] = $val;
                } elseif (strpos($colKey, 'disability') !== false || strpos($colKey, 'sped') !== false) {
                    $mapped['disability_type'] = $val;
                } elseif (strpos($colKey, 'track') !== false || strpos($colKey, 'program') !== false) {
                    $mapped['learning_track'] = strtolower($val);
                } elseif (strpos($colKey, 'parent') !== false || strpos($colKey, 'guardian') !== false) {
                    if (strpos($colKey, 'contact') !== false || strpos($colKey, 'phone') !== false || strpos($colKey, 'number') !== false) {
                        $mapped['parent_contact'] = $val;
                    } elseif (strpos($colKey, 'email') !== false) {
                        $mapped['parent_email'] = $val;
                    } else {
                        $mapped['parent_name'] = $val;
                    }
                } elseif (strpos($colKey, 'contact') !== false || strpos($colKey, 'phone') !== false) {
                    $mapped['parent_contact'] = $val;
                } elseif (strpos($colKey, 'email') !== false) {
                    $mapped['parent_email'] = $val;
                }
            }

            // Combine split names if full name not given
            if (empty($mapped['student_name']) && (!empty($mapped['last_name']) || !empty($mapped['first_name']))) {
                $mapped['student_name'] = trim(($mapped['last_name'] ?? '') . ', ' . ($mapped['first_name'] ?? '') . ' ' . ($mapped['middle_name'] ?? ''));
            }

            if (!empty($mapped['student_name'])) {
                $parsedRows[] = $mapped;
            }
        }

        fclose($handle);

        if (empty($parsedRows)) {
            $_SESSION['error'] = 'No valid learner records found in the uploaded CSV.';
            header('Location: ' . $this->basePath . '/masterlist');
            exit;
        }

        $result = $this->model->bulkImportLearners($parsedRows, $schoolId, $this->userId);

        $msg = "Bulk import complete! Newly Enrolled: {$result['imported']}, Updated: {$result['updated']}.";
        if ($result['skipped'] > 0) {
            $msg .= " Skipped: {$result['skipped']}.";
        }
        $_SESSION['success'] = $msg;

        header('Location: ' . $this->basePath . '/masterlist');
        exit;
    }

    /**
     * Download CSV template for bulk learner import
     */
    public function downloadImportTemplate(): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="SignED_Learner_Import_Template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Template Headers
        fputcsv($output, [
            'LRN',
            'Learner Name',
            'Sex',
            'Birth Date (YYYY-MM-DD)',
            'Grade Level',
            'Section',
            'Disability Category',
            'Parent / Guardian Name',
            'Parent Contact Number',
            'Parent Email'
        ]);

        // Sample Rows
        fputcsv($output, [
            '129688260010',
            'Dela Cruz, Juan M.',
            'Male',
            '2018-05-14',
            'Kinder',
            'SPED Section A',
            'Hearing Impaired (DHH)',
            'Maria Dela Cruz',
            '09171234567',
            'maria.delacruz@example.com'
        ]);
        fputcsv($output, [
            '129688260011',
            'Santos, Angela R.',
            'Female',
            '2017-10-22',
            'Grade 1',
            'SPED Section B',
            'Visual Impairment',
            'Roberto Santos',
            '09289876543',
            'roberto.santos@example.com'
        ]);

        fclose($output);
        exit;
    }

    /**
     * AJAX endpoint to generate or fetch QR claim token and link
     */
    public function getQrToken(): void {
        header('Content-Type: application/json');

        $studentId = (int)($_GET['student_id'] ?? 0);
        if ($studentId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
            exit;
        }

        $token = $this->model->generateClaimTokenForStudent($studentId);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $claimUrl = $protocol . $host . $this->basePath . '/invite/claim/' . $token;

        echo json_encode([
            'success' => true,
            'token' => $token,
            'claim_url' => $claimUrl
        ]);
        exit;
    }
}
