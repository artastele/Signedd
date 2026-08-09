<?php
// Part of: SignED — Process 8 Progress Report Card
// Last modified: 2026-06-16
// Part of: SPED LMS — Progress Report Data Access

require_once __DIR__ . '/../../config/db.php';

class ProgressReportModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTables();
    }

    private function ensureTables(): void {
        try {
            // Drop old attendance_records if it doesn't have the new 'source' column
            $checkCol = $this->db->query("SHOW COLUMNS FROM attendance_records LIKE 'source'")->fetch();
            if (!$checkCol) {
                $this->db->exec("DROP TABLE IF EXISTS attendance_records");
            }
        } catch (Throwable $e) {}

        // Helper function to add columns safely (for MySQL compatibility)
        $addColumnSafe = function (string $table, string $column, string $definition, string $after = '') {
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
                $stmt->execute([$table, $column]);
                if ((int)$stmt->fetchColumn() === 0) {
                    $afterClause = $after ? " AFTER $after" : '';
                    $this->db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition$afterClause");
                }
            } catch (Throwable $e) {
                error_log("Failed to add column $column to $table: " . $e->getMessage());
            }
        };

        $statements = [
            "CREATE TABLE IF NOT EXISTS attendance_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                date DATE NOT NULL,
                status ENUM('present', 'absent', 'tardy', 'excused') NOT NULL DEFAULT 'present',
                source ENUM('manual', 'auto_activity') NOT NULL DEFAULT 'manual',
                recorded_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_student_date_src (student_id, date, source)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS grade_entries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                quarter VARCHAR(50) NOT NULL,
                domain VARCHAR(191) NOT NULL,
                source ENUM('auto', 'manual') NOT NULL,
                score DECIMAL(5, 2) NOT NULL,
                recorded_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_student_quarter_domain_src (student_id, quarter, domain, source)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS report_remarks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                progress_report_id INT NOT NULL,
                quarter VARCHAR(50) NOT NULL,
                remark_type ENUM('teacher', 'parent') NOT NULL,
                remark_text TEXT NULL,
                signature_name VARCHAR(255) NULL,
                signature_data MEDIUMTEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (progress_report_id) REFERENCES progress_reports(id) ON DELETE CASCADE,
                UNIQUE KEY unique_report_quarter_type (progress_report_id, quarter, remark_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];

        foreach ($statements as $sql) {
            try {
                $this->db->exec($sql);
            } catch (Throwable $e) {
                error_log('ProgressReportModel::ensureTables execution error: ' . $e->getMessage());
            }
        }

        // Add columns to progress_reports safely
        $addColumnSafe('progress_reports', 'document_path', 'VARCHAR(255) NULL', 'status');
        $addColumnSafe('progress_reports', 'finalized_at', 'DATETIME NULL', 'document_path');
        $addColumnSafe('progress_reports', 'transfer_details', 'TEXT NULL', 'finalized_at');

        try {
            $this->db->exec("ALTER TABLE attendance_records MODIFY status ENUM('present', 'absent', 'tardy', 'excused') NOT NULL DEFAULT 'present'");
        } catch (Throwable $e) {
            error_log('ProgressReportModel::ensureTables attendance status migration error: ' . $e->getMessage());
        }

        // Add columns to report_remarks safely
        $addColumnSafe('report_remarks', 'signature_data', 'MEDIUMTEXT NULL', 'signature_name');
    }

    public function getProgressReports(): array {
        $stmt = $this->db->prepare(
            "SELECT sr.id AS student_id, sr.student_name, sr.lrn, ir.id AS iep_id,
                    pr.id AS progress_report_id, pr.status, pr.school_year, pr.quarter, pr.updated_at
             FROM student_records sr
             JOIN iep_records ir ON ir.student_id = sr.id
             LEFT JOIN progress_reports pr ON pr.iep_record_id = ir.id
             ORDER BY COALESCE(pr.updated_at, ir.created_at) DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProgressReportsForParent(int $parentId): array {
        $stmt = $this->db->prepare(
            "SELECT sr.id AS student_id, sr.student_name, sr.lrn, ir.id AS iep_id,
                    pr.id AS progress_report_id, pr.status, pr.school_year, pr.quarter, pr.updated_at
             FROM student_records sr
             JOIN iep_records ir ON ir.student_id = sr.id
             JOIN enrollment_submissions es ON es.id = sr.enrollment_id
             LEFT JOIN progress_reports pr ON pr.iep_record_id = ir.id
             WHERE es.parent_id = :parent_id
             ORDER BY COALESCE(pr.updated_at, ir.created_at) DESC"
        );
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudent(int $studentId): ?array {
        $stmt = $this->db->prepare(
            "SELECT sr.id, sr.student_name, sr.lrn, sr.date_of_birth, es.sex, es.age, es.school_year
             FROM student_records sr
             LEFT JOIN enrollment_submissions es ON es.id = sr.enrollment_id
             WHERE sr.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function isParentOfStudent(int $parentId, int $studentId): bool {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM student_records sr
             JOIN enrollment_submissions es ON es.id = sr.enrollment_id
             WHERE sr.id = :student_id AND es.parent_id = :parent_id
             LIMIT 1"
        );
        $stmt->execute(['student_id' => $studentId, 'parent_id' => $parentId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getLatestIepForStudent(int $studentId): ?array {
        $stmt = $this->db->prepare(
            "SELECT id, student_id, status
             FROM iep_records
             WHERE student_id = :student_id
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute(['student_id' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLatestProgressReportByIepId(int $iepId): ?array {
        $stmt = $this->db->prepare(
            "SELECT pr.*, sr.student_name, sr.lrn, ir.id AS iep_id
             FROM progress_reports pr
             JOIN iep_records ir ON ir.id = pr.iep_record_id
             JOIN student_records sr ON sr.id = pr.student_id
             WHERE pr.iep_record_id = :iep_id
             ORDER BY pr.updated_at DESC, pr.id DESC
             LIMIT 1"
        );
        $stmt->execute(['iep_id' => $iepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getProgressReportById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT pr.*, sr.student_name, sr.lrn, ir.id AS iep_id, sr.id AS student_id
             FROM progress_reports pr
             JOIN iep_records ir ON ir.id = pr.iep_record_id
             JOIN student_records sr ON sr.id = pr.student_id
             WHERE pr.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function upsertProgressReport(int $iepId, int $studentId, int $userId, array $data): int {
        $existing = $this->getLatestProgressReportByIepId($iepId);
        $payload = [
            'student_id' => $studentId,
            'iep_record_id' => $iepId,
            'created_by' => $userId,
            'school_year' => $data['school_year'] ?? null,
            'quarter' => $data['quarter'] ?? null,
            'attendance_summary' => $data['attendance_summary'] ?? null,
            'progress_summary' => $data['progress_summary'] ?? null,
            'teacher_remarks' => $data['teacher_remarks'] ?? null,
            'ratings' => json_encode($data['ratings'] ?? []),
            'status' => $data['status'] ?? 'draft',
            'transfer_details' => $data['transfer_details'] ?? null,
        ];

        if ($existing) {
            $payload['id'] = (int) $existing['id'];
            $stmt = $this->db->prepare(
                "UPDATE progress_reports SET student_id = :student_id,
                    iep_record_id = :iep_record_id,
                    created_by = :created_by,
                    school_year = :school_year,
                    quarter = :quarter,
                    attendance_summary = :attendance_summary,
                    progress_summary = :progress_summary,
                    teacher_remarks = :teacher_remarks,
                    ratings = :ratings,
                    status = :status,
                    transfer_details = :transfer_details,
                    updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute($payload);
            return $existing['id'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO progress_reports
                (student_id, iep_record_id, created_by, school_year, quarter, attendance_summary, progress_summary, teacher_remarks, ratings, status, transfer_details)
             VALUES
                (:student_id, :iep_record_id, :created_by, :school_year, :quarter, :attendance_summary, :progress_summary, :teacher_remarks, :ratings, :status, :transfer_details)"
        );
        $stmt->execute($payload);
        return (int) $this->db->lastInsertId();
    }

    public function finalizeProgressReport(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE progress_reports SET status = 'finalized', updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getAttendanceRecords(int $studentId): array {
        $stmt = $this->db->prepare(
            "SELECT ar.*, u.name AS logger_name
             FROM attendance_records ar
             JOIN users u ON u.id = ar.recorded_by
             WHERE ar.student_id = :student_id
             ORDER BY ar.date DESC, ar.id DESC"
        );
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceRecordsForMonth(int $studentId, string $yearMonth): array {
        $stmt = $this->db->prepare(
            "SELECT ar.*, u.name AS logger_name
             FROM attendance_records ar
             JOIN users u ON u.id = ar.recorded_by
             WHERE ar.student_id = :student_id
               AND DATE_FORMAT(ar.date, '%Y-%m') = :year_month
             ORDER BY ar.date ASC, ar.id ASC"
        );
        $stmt->execute([
            'student_id' => $studentId,
            'year_month' => $yearMonth
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceStats(int $studentId, ?string $yearMonth = null): array {
        $where = 'student_id = :student_id';
        $params = ['student_id' => $studentId];
        if ($yearMonth) {
            $where .= " AND DATE_FORMAT(date, '%Y-%m') = :year_month";
            $params['year_month'] = $yearMonth;
        }

        $stmt = $this->db->prepare(
            "SELECT status, COUNT(*) AS total
             FROM attendance_records
             WHERE $where
             GROUP BY status"
        );
        $stmt->execute($params);
        $stats = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'tardy' => 0,
            'excused' => 0,
            'rate' => 0
        ];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = $row['status'];
            $count = (int)$row['total'];
            if (array_key_exists($status, $stats)) {
                $stats[$status] = $count;
            }
            $stats['total'] += $count;
        }

        $stats['rate'] = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100, 1) : 0;
        return $stats;
    }

    public function getAttendanceLearners(): array {
        $stmt = $this->db->prepare(
            "SELECT sr.id AS student_id,
                    sr.student_name,
                    sr.lrn,
                    es.school_year,
                    es.grade_level_to_enroll,
                    ir.id AS iep_id,
                    COUNT(ar.id) AS attendance_entries,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_entries,
                    MAX(ar.created_at) AS last_attendance_at
             FROM student_records sr
             LEFT JOIN enrollment_submissions es ON es.id = sr.enrollment_id
             LEFT JOIN (
                SELECT student_id, MAX(id) AS id
                FROM iep_records
                GROUP BY student_id
             ) ir ON ir.student_id = sr.id
             LEFT JOIN attendance_records ar ON ar.student_id = sr.id
             GROUP BY sr.id, sr.student_name, sr.lrn, es.school_year, es.grade_level_to_enroll, ir.id
             ORDER BY sr.student_name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceLearnersForParent(int $parentId): array {
        $stmt = $this->db->prepare(
            "SELECT sr.id AS student_id,
                    sr.student_name,
                    sr.lrn,
                    es.school_year,
                    es.grade_level_to_enroll,
                    ir.id AS iep_id,
                    COUNT(ar.id) AS attendance_entries,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_entries,
                    MAX(ar.created_at) AS last_attendance_at
             FROM student_records sr
             JOIN enrollment_submissions es ON es.id = sr.enrollment_id
             LEFT JOIN (
                SELECT student_id, MAX(id) AS id
                FROM iep_records
                GROUP BY student_id
             ) ir ON ir.student_id = sr.id
             LEFT JOIN attendance_records ar ON ar.student_id = sr.id
             WHERE es.parent_id = :parent_id
             GROUP BY sr.id, sr.student_name, sr.lrn, es.school_year, es.grade_level_to_enroll, ir.id
             ORDER BY sr.student_name ASC"
        );
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAttendanceRecord(int $studentId, string $date, string $status, string $source, int $recordedBy): bool {
        $allowed = ['present', 'absent', 'tardy', 'excused'];
        if (!in_array($status, $allowed, true)) {
            $status = 'present';
        }

        $stmt = $this->db->prepare(
            "INSERT INTO attendance_records (student_id, date, status, source, recorded_by)
             VALUES (:student_id, :date, :status, :source, :recorded_by)
             ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                recorded_by = VALUES(recorded_by)"
        );
        return $stmt->execute([
            'student_id' => $studentId,
            'date' => $date,
            'status' => $status,
            'source' => $source,
            'recorded_by' => $recordedBy
        ]);
    }

    public function deleteAttendanceRecord(int $id, int $studentId): bool {
        $stmt = $this->db->prepare("DELETE FROM attendance_records WHERE id = :id AND student_id = :student_id");
        return $stmt->execute(['id' => $id, 'student_id' => $studentId]);
    }

    public function getAutoAttendanceFromLogs(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT DATE(performed_at) AS log_date
            FROM lms_logs
            WHERE student_id = :student_id AND action IN ('opened', 'submitted')
            ORDER BY log_date DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getGradeEntries(int $studentId, string $quarter): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM grade_entries WHERE student_id = :student_id AND quarter = :quarter"
        );
        $stmt->execute(['student_id' => $studentId, 'quarter' => $quarter]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveGradeEntry(int $studentId, string $quarter, string $domain, string $source, float $score, int $recordedBy): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO grade_entries (student_id, quarter, domain, source, score, recorded_by)
             VALUES (:student_id, :quarter, :domain, :source, :score, :recorded_by)
             ON DUPLICATE KEY UPDATE
                 score = VALUES(score),
                 recorded_by = VALUES(recorded_by)"
        );
        return $stmt->execute([
            'student_id' => $studentId,
            'quarter' => $quarter,
            'domain' => $domain,
            'source' => $source,
            'score' => $score,
            'recorded_by' => $recordedBy
        ]);
    }

    public function getProcess7DomainAverages(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT
                lp.pdsp_domain AS domain,
                ROUND(AVG(
                    CASE
                        WHEN g.score IS NOT NULL AND COALESCE(g.max_score, act.max_score, 0) > 0
                            THEN g.score / NULLIF(COALESCE(g.max_score, act.max_score), 0) * 100
                        WHEN sub.auto_score IS NOT NULL AND act.max_score > 0
                            THEN sub.auto_score / NULLIF(act.max_score, 0) * 100
                        ELSE NULL
                    END
                ), 1) AS avg_score
            FROM lesson_plans lp
            JOIN lesson_assignments la ON lp.id = la.lesson_plan_id
            JOIN lms_activities act ON act.lesson_plan_id = lp.id
            LEFT JOIN lms_submissions sub
                   ON sub.activity_id = act.id AND sub.student_id = :student_id
            LEFT JOIN lms_grades g ON g.submission_id = sub.id
            WHERE la.student_id = :student_id2 AND lp.status = 'published'
            GROUP BY lp.pdsp_domain
            ORDER BY lp.pdsp_domain ASC
        ");
        $stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activeDomains = $this->getActiveDomains();
        $mapped = [];
        foreach ($rows as $row) {
            $mappedDomain = $this->mapDbDomainToDisplay($row['domain'], $activeDomains);
            $mapped[] = [
                'domain' => $mappedDomain,
                'avg_score' => $row['avg_score']
            ];
        }
        return $mapped;
    }

    private function mapDbDomainToDisplay(string $dbDomain, array $activeDomains): string {
        $normDb = preg_replace('/[^a-z]/', '', strtolower($dbDomain));
        
        foreach ($activeDomains as $actDom) {
            $normAct = preg_replace('/[^a-z]/', '', strtolower($actDom));
            if ($normDb === 'perceptuocognitive' && $normAct === 'academic') {
                return $actDom;
            }
            if ($normDb === 'psychosocial' && $normAct === 'behavior') {
                return $actDom;
            }
            if ($normDb === 'socioemotional' && $normAct === 'socialemotional') {
                return $actDom;
            }
            if ($normDb === 'communicationlanguage' && $normAct === 'communication') {
                return $actDom;
            }
            if ($normDb === $normAct) {
                return $actDom;
            }
        }
        
        if ($normDb === 'perceptuocognitive') return 'Perceptuo-Cognitive';
        if ($normDb === 'psychosocial') return 'Psychosocial';
        if ($normDb === 'socioemotional') return 'Socio-Emotional';
        if ($normDb === 'psychomotor') return 'Psychomotor';
        if ($normDb === 'dailylivingskills') return 'Daily Living Skills';
        if ($normDb === 'communicationlanguage') return 'Communication and Language';
        
        return $dbDomain;
    }

    public function getActiveDomains(): array {
        $stmt = $this->db->prepare("SELECT DISTINCT domain_name FROM pdsp_domains ORDER BY id ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($rows)) {
            return ['Academic', 'Behavior', 'Communication', 'Social-Emotional'];
        }
        return $rows;
    }

    public function getReportRemarks(int $reportId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM report_remarks WHERE progress_report_id = :report_id"
        );
        $stmt->execute(['report_id' => $reportId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveReportRemark(int $reportId, string $quarter, string $type, ?string $text, ?string $signature): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO report_remarks (progress_report_id, quarter, remark_type, remark_text, signature_name)
             VALUES (:report_id, :quarter, :type, :text, :signature)
             ON DUPLICATE KEY UPDATE
                 remark_text = VALUES(remark_text),
                 signature_name = VALUES(signature_name)"
        );
        return $stmt->execute([
            'report_id' => $reportId,
            'quarter' => $quarter,
            'type' => $type,
            'text' => $text,
            'signature' => $signature
        ]);
    }

    public function saveReportRemarkWithSignatureData(int $reportId, string $quarter, string $type, ?string $text, ?string $signature, ?string $signatureData): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO report_remarks (progress_report_id, quarter, remark_type, remark_text, signature_name, signature_data)
             VALUES (:report_id, :quarter, :type, :text, :signature, NULLIF(:signature_data, ''))
             ON DUPLICATE KEY UPDATE
                 remark_text = VALUES(remark_text),
                 signature_name = VALUES(signature_name),
                 signature_data = COALESCE(NULLIF(:signature_data_update, ''), signature_data)"
        );
        return $stmt->execute([
            'report_id' => $reportId,
            'quarter' => $quarter,
            'type' => $type,
            'text' => $text,
            'signature' => $signature,
            'signature_data' => $signatureData ?? '',
            'signature_data_update' => $signatureData ?? ''
        ]);
    }

    public function getReportRemark(int $reportId, string $quarter, string $type): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM report_remarks
             WHERE progress_report_id = :report_id
               AND quarter = :quarter
               AND remark_type = :type
             LIMIT 1"
        );
        $stmt->execute([
            'report_id' => $reportId,
            'quarter' => $quarter,
            'type' => $type
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function importSf2CsvForStudent(int $studentId, string $filePath, string $yearMonth, int $recordedBy, bool $blankAsPresent = false): array {
        $student = $this->getStudent($studentId);
        if (!$student) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Student not found.']];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Could not read uploaded CSV file.']];
        }

        $header = null;
        $dayIndexes = [];
        $lrnIndex = null;
        $studentIdIndex = null;
        $nameIndex = null;
        $targetRow = null;

        while (($row = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $candidate = array_map(fn($cell) => trim((string)$cell), $row);
                $nonEmpty = array_filter($candidate, fn($cell) => $cell !== '');
                if (count($nonEmpty) < 2) {
                    continue;
                }

                foreach ($candidate as $idx => $label) {
                    $normalized = strtolower(trim($label));
                    if ($studentIdIndex === null && (strpos($normalized, 'student id') !== false || $normalized === 'student_id')) {
                        $studentIdIndex = $idx;
                    }
                    if ($lrnIndex === null && strpos($normalized, 'lrn') !== false) {
                        $lrnIndex = $idx;
                    }
                    if ($nameIndex === null && (strpos($normalized, 'name') !== false || strpos($normalized, 'learner') !== false)) {
                        $nameIndex = $idx;
                    }
                    if (preg_match('/^(?:day\s*)?0?([1-9]|[12][0-9]|3[01])(?:\D.*)?$/i', $normalized, $m)) {
                        $dayIndexes[(int)$m[1]] = $idx;
                    }
                }

                if (!empty($dayIndexes)) {
                    $header = $candidate;
                }
                continue;
            }

            $rowStudentId = $studentIdIndex !== null ? trim((string)($row[$studentIdIndex] ?? '')) : '';
            $rowLrn = $lrnIndex !== null ? trim((string)($row[$lrnIndex] ?? '')) : '';
            $rowName = $nameIndex !== null ? trim((string)($row[$nameIndex] ?? '')) : '';
            $matchesStudentId = $rowStudentId !== '' && $rowStudentId === (string)($student['student_id'] ?? '');
            $matchesLrn = $rowLrn !== '' && !empty($student['lrn']) && $rowLrn === (string)$student['lrn'];
            $matchesName = $rowName !== '' && strcasecmp($rowName, (string)$student['student_name']) === 0;

            if ($matchesStudentId || $matchesLrn || $matchesName || ($studentIdIndex === null && $lrnIndex === null && $nameIndex === null && $targetRow === null)) {
                $targetRow = $row;
                break;
            }
        }
        fclose($handle);

        if ($header === null) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Could not find SF2 day columns in the CSV header. Use columns named 1 through 31 or Day 1 through Day 31.']];
        }
        if ($targetRow === null) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['No row matched this learner by Student ID, DepEd LRN, or learner name.']];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $daysInMonth = (int)date('t', strtotime($yearMonth . '-01'));

        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (!isset($dayIndexes[$day])) {
                $skipped++;
                continue;
            }

            $raw = trim((string)($targetRow[$dayIndexes[$day]] ?? ''));
            $status = $this->normalizeSf2Status($raw, $blankAsPresent);
            if ($status === null) {
                $skipped++;
                continue;
            }

            $date = sprintf('%s-%02d', $yearMonth, $day);
            if ($this->saveAttendanceRecord($studentId, $date, $status, 'manual', $recordedBy)) {
                $imported++;
            } else {
                $errors[] = 'Failed to import day ' . $day . '.';
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function normalizeSf2Status(string $value, bool $blankAsPresent): ?string {
        $clean = strtolower(trim($value));
        $clean = str_replace(['.', '-', '_'], '', $clean);

        if ($clean === '') {
            return $blankAsPresent ? 'present' : null;
        }

        $presentValues = ['p', 'present', '/', '✓', '✔', '1'];
        $absentValues = ['a', 'absent', 'x', '0'];
        $tardyValues = ['t', 'tardy', 'late', 'l'];
        $excusedValues = ['e', 'excused', 'excuse'];

        if (in_array($clean, $presentValues, true)) return 'present';
        if (in_array($clean, $absentValues, true)) return 'absent';
        if (in_array($clean, $tardyValues, true)) return 'tardy';
        if (in_array($clean, $excusedValues, true)) return 'excused';

        return null;
    }

    public function finalizeProgressReportWithDoc(int $id, ?string $docPath): bool {
        $stmt = $this->db->prepare(
            "UPDATE progress_reports 
             SET status = 'finalized', 
                 document_path = :doc_path, 
                 finalized_at = NOW(), 
                 updated_at = NOW() 
             WHERE id = :id"
        );
        return $stmt->execute([
            'id' => $id,
            'doc_path' => $docPath
        ]);
    }

    public function updateStudentDetails(int $studentId, int $age): bool {
        $stmt = $this->db->prepare("
            UPDATE enrollment_submissions es
            JOIN student_records sr ON sr.enrollment_id = es.id
            SET es.age = :age
            WHERE sr.id = :student_id
        ");
        return $stmt->execute(['age' => $age, 'student_id' => $studentId]);
    }

    public function getPdspRecordIdForStudent(int $studentId): ?int {
        $stmt = $this->db->prepare(
            "SELECT id FROM pdsp_records WHERE student_id = :sid AND status IN ('signed', 'complete') LIMIT 1"
        );
        $stmt->execute(['sid' => $studentId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }

        $stmt = $this->db->prepare("SELECT id FROM pdsp_records WHERE student_id = :sid LIMIT 1");
        $stmt->execute(['sid' => $studentId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function getSf9Indicators(): array {
        $path = dirname(__DIR__, 2) . '/config/sf9_indicators.php';
        if (!file_exists($path)) {
            return [];
        }
        return require $path;
    }

    public function ensureQuarterlyRatingsSeeded(int $studentId, int $pdspRecordId): void {
        $sf9Indicators = $this->getSf9Indicators();
        if (empty($sf9Indicators)) {
            return;
        }

        $ins = $this->db->prepare("
            INSERT INTO student_quarterly_ratings
                (student_id, pdsp_record_id, domain, indicator_text, quarter, rating, source)
            SELECT :student_id, :pdsp_id, :domain, :indicator_text, :quarter, NULL, 'manual'
            WHERE NOT EXISTS (
                SELECT 1 FROM student_quarterly_ratings
                WHERE student_id = :student_id2
                  AND pdsp_record_id = :pdsp_id2
                  AND indicator_text = :indicator_text2
                  AND quarter = :quarter2
            )
        ");

        foreach ($sf9Indicators as $domain => $indicators) {
            foreach ($indicators as $indicatorText) {
                $indicatorText = trim($indicatorText);
                if ($indicatorText === '') {
                    continue;
                }
                for ($q = 1; $q <= 4; $q++) {
                    $ins->execute([
                        'student_id'      => $studentId,
                        'pdsp_id'         => $pdspRecordId,
                        'domain'          => $domain,
                        'indicator_text'  => $indicatorText,
                        'quarter'         => $q,
                        'student_id2'     => $studentId,
                        'pdsp_id2'        => $pdspRecordId,
                        'indicator_text2' => $indicatorText,
                        'quarter2'        => $q,
                    ]);
                }
            }
        }
    }

    public function getQuarterlyRatingsMap(int $studentId, ?int $pdspRecordId): array {
        if (!$pdspRecordId) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT indicator_text, quarter, rating
            FROM student_quarterly_ratings
            WHERE student_id = :sid AND pdsp_record_id = :pid
        ");
        $stmt->execute(['sid' => $studentId, 'pid' => $pdspRecordId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $map = [];
        foreach ($rows as $row) {
            $ind = $row['indicator_text'];
            $q = (int) $row['quarter'];
            if (!isset($map[$ind])) {
                $map[$ind] = [1 => null, 2 => null, 3 => null, 4 => null];
            }
            $map[$ind][$q] = $row['rating'];
        }
        return $map;
    }

    public function saveQuarterlyRatings(int $studentId, int $pdspRecordId, array $ratingsByDomain): bool {
        $allowed = ['P', 'AP', 'D', 'B', 'NA', ''];

        $upsert = $this->db->prepare("
            INSERT INTO student_quarterly_ratings
                (student_id, pdsp_record_id, domain, indicator_text, quarter, rating, source)
            VALUES (:sid, :pid, :domain, :ind, :q, :rating, 'manual')
            ON DUPLICATE KEY UPDATE
                rating = VALUES(rating),
                source = 'manual',
                updated_at = NOW()
        ");

        foreach ($ratingsByDomain as $domain => $indicators) {
            if (!is_array($indicators)) {
                continue;
            }
            foreach ($indicators as $indicatorText => $quarters) {
                if (!is_array($quarters)) {
                    continue;
                }
                $indicatorText = trim((string) $indicatorText);
                if ($indicatorText === '') {
                    continue;
                }
                foreach ($quarters as $q => $rating) {
                    $qNum = (int) $q;
                    if ($qNum < 1 || $qNum > 4) {
                        continue;
                    }
                    $rating = strtoupper(trim((string) $rating));
                    if (!in_array($rating, $allowed, true)) {
                        continue;
                    }
                    $upsert->execute([
                        'sid'    => $studentId,
                        'pid'    => $pdspRecordId,
                        'domain' => $domain,
                        'ind'    => $indicatorText,
                        'q'      => $qNum,
                        'rating' => $rating === '' ? null : $rating,
                    ]);
                }
            }
        }

        return true;
    }

    public function getQuarterlyRatings(int $studentId, int $pdspRecordId): array {
        $stmt = $this->db->prepare("
            SELECT domain, indicator_text, quarter, rating, observation 
            FROM student_quarterly_ratings 
            WHERE student_id = :sid AND pdsp_record_id = :pid
            ORDER BY domain ASC, id ASC
        ");
        $stmt->execute(['sid' => $studentId, 'pid' => $pdspRecordId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getIepSignatories(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT signatory_role, signatory_name, signature_image_path, signed_at 
            FROM iep_signatories 
            WHERE iep_id = :iep_id
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getDraftingSpedTeacherName(int $userId): string {
        $stmt = $this->db->prepare("SELECT name FROM users WHERE id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: '—';
    }

    public function getActivePrincipalName(): string {
        $stmt = $this->db->prepare("SELECT name FROM users WHERE role = 'principal' AND status = 'active' ORDER BY id ASC LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn() ?: 'DAISY LYN A. BUENAFE';
    }
}
