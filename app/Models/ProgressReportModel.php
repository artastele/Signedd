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

        $statements = [
            "CREATE TABLE IF NOT EXISTS attendance_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                date DATE NOT NULL,
                status ENUM('present', 'absent') NOT NULL DEFAULT 'present',
                source ENUM('manual', 'auto_activity') NOT NULL DEFAULT 'manual',
                recorded_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_student_date_src (student_id, date, source)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "ALTER TABLE progress_reports 
                ADD COLUMN IF NOT EXISTS document_path VARCHAR(255) NULL AFTER status,
                ADD COLUMN IF NOT EXISTS finalized_at DATETIME NULL AFTER document_path",

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
                    updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute($payload);
            return $existing['id'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO progress_reports
                (student_id, iep_record_id, created_by, school_year, quarter, attendance_summary, progress_summary, teacher_remarks, ratings, status)
             VALUES
                (:student_id, :iep_record_id, :created_by, :school_year, :quarter, :attendance_summary, :progress_summary, :teacher_remarks, :ratings, :status)"
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

    public function saveAttendanceRecord(int $studentId, string $date, string $status, string $source, int $recordedBy): bool {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
}
