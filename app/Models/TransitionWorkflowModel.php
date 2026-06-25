<?php
// Part of: SignED - Unified Transition + IEP Workflow

require_once __DIR__ . '/../../config/db.php';

class TransitionWorkflowModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTables();
    }

    private function ensureTables(): void {
        $tables = [
            "CREATE TABLE IF NOT EXISTS progress_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                iep_record_id INT NOT NULL,
                created_by INT NOT NULL,
                school_year VARCHAR(20) NULL,
                quarter VARCHAR(50) NULL,
                attendance_summary TEXT NULL,
                progress_summary TEXT NULL,
                teacher_remarks TEXT NULL,
                ratings JSON NULL,
                status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_progress_reports_iep (iep_record_id),
                INDEX idx_progress_reports_student (student_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS cot_observations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                iep_record_id INT NOT NULL,
                observed_teacher_id INT NOT NULL,
                created_by INT NOT NULL,
                lesson_plan_id INT NULL,
                school_year VARCHAR(20) NULL,
                quarter VARCHAR(50) NULL,
                observation_date DATE NULL,
                ratings JSON NULL,
                strengths TEXT NULL,
                recommendations TEXT NULL,
                status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
                notification_sent_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (observed_teacher_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_cot_iep (iep_record_id),
                INDEX idx_cot_teacher (observed_teacher_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS transition_readiness (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                iep_record_id INT NOT NULL,
                progress_report_id INT NULL,
                cot_observation_id INT NULL,
                created_by INT NOT NULL,
                readiness_result ENUM('Ready for Inclusion','Needs More Support','Not Yet Ready','For Re-evaluation') NOT NULL DEFAULT 'For Re-evaluation',
                evidence_summary TEXT NULL,
                teacher_recommendation TEXT NULL,
                status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
                finalized_at DATETIME NULL,
                overall_status ENUM('ready','partial','not_ready') NOT NULL DEFAULT 'partial',
                overall_status_overridden BOOLEAN NOT NULL DEFAULT FALSE,
                overall_remarks TEXT NULL,
                evaluated_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (progress_report_id) REFERENCES progress_reports(id) ON DELETE SET NULL,
                FOREIGN KEY (cot_observation_id) REFERENCES cot_observations(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (evaluated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_transition_iep (iep_record_id),
                INDEX idx_transition_result (readiness_result),
                INDEX idx_transition_overall_status (overall_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS individual_transition_plans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                iep_record_id INT NOT NULL,
                transition_readiness_id INT NOT NULL,
                created_by INT NOT NULL,
                entry_point VARCHAR(255) NULL,
                learner_information JSON NULL,
                transition_services TEXT NULL,
                support_needed TEXT NULL,
                team_responsibilities TEXT NULL,
                status ENUM('draft','completed') NOT NULL DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_itp_iep (iep_record_id),
                INDEX idx_itp_readiness (transition_readiness_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS transition_readiness_goals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transition_readiness_id INT NOT NULL,
                iep_step_id INT NOT NULL,
                goal_text TEXT NOT NULL,
                pdsp_domain VARCHAR(191) NOT NULL,
                suggested_status ENUM('ready','partial','not_ready') NOT NULL DEFAULT 'partial',
                final_status ENUM('ready','partial','not_ready') NOT NULL DEFAULT 'partial',
                status_overridden BOOLEAN NOT NULL DEFAULT FALSE,
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
                FOREIGN KEY (iep_step_id) REFERENCES iep_steps(id) ON DELETE CASCADE,
                UNIQUE KEY unique_readiness_goal (transition_readiness_id, iep_step_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS inclusive_iep_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                original_iep_record_id INT NOT NULL,
                transition_readiness_id INT NOT NULL,
                itp_id INT NOT NULL,
                created_by INT NOT NULL,
                generated_summary TEXT NULL,
                progress_remarks TEXT NULL,
                cot_recommendations TEXT NULL,
                sped_teacher_signed_by INT NULL,
                sped_teacher_signed_at TIMESTAMP NULL,
                master_teacher_signed_by INT NULL,
                master_teacher_signed_at TIMESTAMP NULL,
                status ENUM('draft','for_signature','signed') NOT NULL DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (original_iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
                FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_inclusive_original_iep (original_iep_record_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itgp_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                itp_id INT NOT NULL,
                general_teacher_id INT NOT NULL,
                goal TEXT NULL,
                entry_point VARCHAR(255) NULL,
                learning_packages TEXT NULL,
                recommendations TEXT NULL,
                status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
                finalized_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
                FOREIGN KEY (general_teacher_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itgp_activities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itgp_id INT NOT NULL,
                competency_skill TEXT NULL,
                activities TEXT NULL,
                time_frame VARCHAR(255) NULL,
                person_responsible VARCHAR(255) NULL,
                remarks TEXT NULL,
                display_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itgp_comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itgp_id INT NOT NULL,
                posted_by INT NOT NULL,
                comment_text TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE,
                FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS general_teacher_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                general_teacher_id INT NOT NULL,
                assigned_by INT NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (general_teacher_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_assignment (student_id, general_teacher_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS class_placements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                itgp_id INT NOT NULL,
                reviewed_by INT NOT NULL,
                status ENUM('confirmed','on_hold') NOT NULL DEFAULT 'confirmed',
                hold_reason TEXT NULL,
                confirmed_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE,
                FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itp_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                transition_readiness_id INT NOT NULL,
                school_year VARCHAR(20) NOT NULL,
                point_of_entry VARCHAR(255) NULL,
                learner_information JSON NULL,
                status ENUM('in_progress', 'finalized') NOT NULL DEFAULT 'in_progress',
                drafted_by INT NOT NULL,
                finalized_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
                FOREIGN KEY (drafted_by) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_itp_student (student_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itp_team_members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itp_id INT NOT NULL,
                role ENUM('itp_coordinator', 'school_head', 'sped_teacher', 'parent_guardian', 'learner', 'guidance_teacher', 'linkages') NOT NULL,
                assigned_user_id INT NULL,
                name VARCHAR(255) NULL,
                contact_details VARCHAR(255) NULL,
                date_started DATE NULL,
                status ENUM('pending', 'filled') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
                FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL,
                UNIQUE KEY unique_itp_role (itp_id, role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itp_signatures (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itp_id INT NOT NULL,
                signatory_role ENUM('parent_guardian') NOT NULL DEFAULT 'parent_guardian',
                signature_image_path VARCHAR(255) NOT NULL,
                signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
                UNIQUE KEY unique_itp_signature_role (itp_id, signatory_role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itp_narrative (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itp_id INT NOT NULL,
                section ENUM('strengths', 'interests', 'talents', 'skills', 'needs') NOT NULL,
                item_text TEXT NOT NULL,
                display_order INT NOT NULL DEFAULT 0,
                FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itp_recommendations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itp_id INT NOT NULL,
                timing ENUM('beginning_of_sy', 'end_of_sy') NOT NULL,
                recommendation_text TEXT NOT NULL,
                FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
                UNIQUE KEY unique_itp_recommendation_timing (itp_id, timing)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itp_program_matrix (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itp_id INT NOT NULL,
                row_type INT NOT NULL,
                column_type INT NOT NULL,
                is_checked BOOLEAN NOT NULL DEFAULT FALSE,
                FOREIGN KEY (itp_id) REFERENCES itp_records(id) ON DELETE CASCADE,
                UNIQUE KEY unique_itp_matrix_cell (itp_id, row_type, column_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];

        foreach ($tables as $sql) {
            try {
                $this->db->exec($sql);
            } catch (Throwable $e) {
                error_log('TransitionWorkflowModel::ensureTables: ' . $e->getMessage());
            }
        }

        $this->ensureTransitionReadinessSchema();
        $this->ensureGeneralTeacherAndPlacementSchema();
    }

    private function ensureGeneralTeacherAndPlacementSchema(): void {
        try {
            $this->db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('user','parent','sped_teacher','guidance','principal','master_teacher','learner','admin','general_teacher') DEFAULT 'user'");
        } catch (Throwable $e) {
            error_log('TransitionWorkflowModel::ensureGeneralTeacherAndPlacementSchema (role): ' . $e->getMessage());
        }
        $this->addColumnIfNotExists('student_records', 'status', "ENUM('active','mainstreamed') NOT NULL DEFAULT 'active'");
    }

    private function ensureTransitionReadinessSchema(): void {
        if (!$this->tableExists('transition_readiness')) {
            return;
        }

        $this->addColumnIfNotExists('transition_readiness', 'finalized_at', 'DATETIME NULL AFTER status');
        $this->addColumnIfNotExists('transition_readiness', 'overall_status', "ENUM('ready','partial','not_ready') NOT NULL DEFAULT 'partial' AFTER status");
        $this->addColumnIfNotExists('transition_readiness', 'overall_status_overridden', 'BOOLEAN NOT NULL DEFAULT FALSE AFTER overall_status');
        $this->addColumnIfNotExists('transition_readiness', 'overall_remarks', 'TEXT NULL AFTER overall_status_overridden');
        $this->addColumnIfNotExists('transition_readiness', 'evaluated_by', 'INT NULL AFTER teacher_recommendation');
    }

    private function tableExists(string $tableName): bool {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1'
        );
        $stmt->execute(['table' => $tableName]);
        return (bool)$stmt->fetchColumn();
    }

    private function addColumnIfNotExists(string $tableName, string $columnName, string $definition): void {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column LIMIT 1'
        );
        $stmt->execute(['table' => $tableName, 'column' => $columnName]);
        if ($stmt->fetchColumn()) {
            return;
        }
        try {
            $this->db->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $tableName, $columnName, $definition));
        } catch (Throwable $e) {
            error_log('TransitionWorkflowModel::addColumnIfNotExists: ' . $e->getMessage());
        }
    }

    public function getIepContext(int $iepId): ?array {
        $stmt = $this->db->prepare("
            SELECT ir.*, sr.student_name, sr.lrn, sr.enrollment_id,
                   es.parent_id, es.grade_level_to_enroll, es.school_year,
                   CONCAT_WS(' ', es.guardian_first_name, es.guardian_middle_name, es.guardian_last_name) AS guardian_name,
                   pd.status AS pdsp_status,
                   u.name AS drafted_by_name
            FROM iep_records ir
            JOIN student_records sr ON sr.id = ir.student_id
            LEFT JOIN enrollment_submissions es ON es.id = sr.enrollment_id
            LEFT JOIN pdsp_records pd ON pd.id = ir.pdsp_id
            LEFT JOIN users u ON u.id = ir.drafted_by
            WHERE ir.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $iepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getWorkflow(int $iepId): array {
        $ctx = $this->getIepContext($iepId);
        $studentId = $ctx ? (int)$ctx['student_id'] : 0;
        return [
            'progress_report' => $this->latest('progress_reports', 'iep_record_id', $iepId),
            'cot' => $this->latest('cot_observations', 'iep_record_id', $iepId),
            'readiness' => $this->latest('transition_readiness', 'iep_record_id', $iepId),
            'itp' => $studentId ? $this->getItpByStudent($studentId) : null,
            'itgp' => $studentId ? $this->getItgpByStudent($studentId) : null,
            'assignment' => $studentId ? $this->getAssignmentByStudent($studentId) : null,
            'placement' => $studentId ? $this->getLatestPlacementByStudent($studentId) : null,
        ];
    }

    public function getProgressReportOverview(string $role, int $userId): array {
        if ($role === 'parent') {
            $stmt = $this->db->prepare(
                "SELECT ir.id AS iep_id, sr.id AS student_id, sr.student_name, sr.lrn,
                        pr.id AS progress_report_id, pr.school_year, pr.quarter, pr.status, pr.updated_at
                 FROM iep_records ir
                 JOIN student_records sr ON sr.id = ir.student_id
                 JOIN enrollment_submissions es ON es.id = sr.enrollment_id
                 LEFT JOIN progress_reports pr ON pr.iep_record_id = ir.id
                 WHERE es.parent_id = :parent_id
                 ORDER BY ir.created_at DESC"
            );
            $stmt->execute(['parent_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->db->prepare(
            "SELECT ir.id AS iep_id, sr.id AS student_id, sr.student_name, sr.lrn,
                    pr.id AS progress_report_id, pr.school_year, pr.quarter, pr.status, pr.updated_at
             FROM iep_records ir
             JOIN student_records sr ON sr.id = ir.student_id
             LEFT JOIN progress_reports pr ON pr.iep_record_id = ir.id
             ORDER BY ir.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProgressReportById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT pr.*, sr.student_name, sr.lrn, ir.id AS iep_id
             FROM progress_reports pr
             JOIN student_records sr ON sr.id = pr.student_id
             JOIN iep_records ir ON ir.id = pr.iep_record_id
             WHERE pr.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLatestProgressReportByIepId(int $iepId): ?array {
        return $this->latest('progress_reports', 'iep_record_id', $iepId);
    }

    public function finalizeProgressReport(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE progress_reports SET status = 'finalized', updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function latest(string $table, string $column, int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$column} = :id ORDER BY created_at DESC, id DESC LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function latestItgp(int $iepId): ?array {
        $stmt = $this->db->prepare("
            SELECT itgp.* FROM itgp_records itgp
            JOIN inclusive_iep_records inc ON inc.id = itgp.inclusive_iep_id
            WHERE inc.original_iep_record_id = :id
            ORDER BY itgp.created_at DESC, itgp.id DESC LIMIT 1
        ");
        $stmt->execute(['id' => $iepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function latestPlacement(int $iepId): ?array {
        $stmt = $this->db->prepare("
            SELECT pn.*, u.name AS receiving_teacher_display_name, u.email AS receiving_teacher_email
            FROM placement_notices pn
            JOIN inclusive_iep_records inc ON inc.id = pn.inclusive_iep_id
            JOIN users u ON u.id = pn.receiving_teacher_id
            WHERE inc.original_iep_record_id = :id
            ORDER BY pn.created_at DESC, pn.id DESC LIMIT 1
        ");
        $stmt->execute(['id' => $iepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getEvidence(int $studentId, int $iepId): array {
        $subStmt = $this->db->prepare("
            SELECT sub.*, act.title AS activity_title, act.activity_type, lp.title AS lesson_title
            FROM lms_submissions sub
            JOIN lms_activities act ON act.id = sub.activity_id
            JOIN lesson_plans lp ON lp.id = act.lesson_plan_id
            WHERE sub.student_id = :sid
            ORDER BY sub.submitted_at DESC
            LIMIT 10
        ");
        $subStmt->execute(['sid' => $studentId]);

        $goalStmt = $this->db->prepare("
            SELECT step_number, step_domain, step_objective, instructional_evaluation, observation
            FROM iep_steps
            WHERE iep_id = :iep
            ORDER BY step_number ASC
        ");
        $goalStmt->execute(['iep' => $iepId]);

        $progressStmt = $this->db->prepare("
            SELECT COUNT(*) AS submissions, AVG(auto_score) AS avg_score
            FROM lms_submissions
            WHERE student_id = :sid
        ");
        $progressStmt->execute(['sid' => $studentId]);

        return [
            'submissions' => $subStmt->fetchAll(PDO::FETCH_ASSOC),
            'iep_steps' => $goalStmt->fetchAll(PDO::FETCH_ASSOC),
            'progress_summary' => $progressStmt->fetch(PDO::FETCH_ASSOC) ?: ['submissions' => 0, 'avg_score' => null],
        ];
    }

    public function getProgressSnapshot(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS submissions,
                COUNT(DISTINCT activity_id) AS activities_attempted,
                COALESCE(AVG(auto_score), 0) AS avg_auto_score,
                SUM(CASE WHEN auto_score IS NOT NULL THEN 1 ELSE 0 END) AS completed_submissions
            FROM lms_submissions
            WHERE student_id = :sid
        ");
        $stmt->execute(['sid' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $submissions = (int)($row['submissions'] ?? 0);
        $completed = (int)($row['completed_submissions'] ?? 0);
        $completionRate = $submissions > 0 ? round(($completed / $submissions) * 100, 1) : 0.0;

        return [
            'submissions' => $submissions,
            'activities_attempted' => (int)($row['activities_attempted'] ?? 0),
            'avg_auto_score' => (float)($row['avg_auto_score'] ?? 0),
            'completed_submissions' => $completed,
            'completion_rate' => $completionRate,
        ];
    }

    public function getTeacherAccounts(): array {
        $stmt = $this->db->query("
            SELECT id, name, email, role
            FROM users
            WHERE status = 'active' AND role IN ('sped_teacher','master_teacher')
            ORDER BY name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getParentIdForStudent(int $studentId): ?int {
        $stmt = $this->db->prepare("
            SELECT es.parent_id
            FROM student_records sr
            JOIN enrollment_submissions es ON es.id = sr.enrollment_id
            WHERE sr.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $studentId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function upsertProgressReport(int $iepId, int $studentId, int $userId, array $data): int {
        $existing = $this->latest('progress_reports', 'iep_record_id', $iepId);
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
            $payload['id'] = (int)$existing['id'];
            $this->db->prepare("
                UPDATE progress_reports SET student_id=:student_id, iep_record_id=:iep_record_id, created_by=:created_by,
                    school_year=:school_year, quarter=:quarter,
                    attendance_summary=:attendance_summary, progress_summary=:progress_summary,
                    teacher_remarks=:teacher_remarks, ratings=:ratings, status=:status, updated_at=NOW()
                WHERE id=:id
            ")->execute($payload);
            return (int)$existing['id'];
        }
        $this->db->prepare("
            INSERT INTO progress_reports
            (student_id, iep_record_id, created_by, school_year, quarter, attendance_summary, progress_summary, teacher_remarks, ratings, status)
            VALUES (:student_id, :iep_record_id, :created_by, :school_year, :quarter, :attendance_summary, :progress_summary, :teacher_remarks, :ratings, :status)
        ")->execute($payload);
        return (int)$this->db->lastInsertId();
    }

    public function upsertCot(int $iepId, int $studentId, int $userId, array $data): int {
        $existing = $this->latest('cot_observations', 'iep_record_id', $iepId);
        $payload = [
            'student_id' => $studentId,
            'iep_record_id' => $iepId,
            'observed_teacher_id' => (int)($data['observed_teacher_id'] ?? 0),
            'created_by' => $userId,
            'lesson_plan_id' => !empty($data['lesson_plan_id']) ? (int)$data['lesson_plan_id'] : null,
            'school_year' => $data['school_year'] ?? null,
            'quarter' => $data['quarter'] ?? null,
            'observation_date' => $data['observation_date'] ?: null,
            'ratings' => json_encode($data['ratings'] ?? []),
            'strengths' => $data['strengths'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];
        if ($existing) {
            $payload['id'] = (int)$existing['id'];
            $this->db->prepare("
                UPDATE cot_observations SET student_id=:student_id, iep_record_id=:iep_record_id,
                    observed_teacher_id=:observed_teacher_id, created_by=:created_by, lesson_plan_id=:lesson_plan_id,
                    school_year=:school_year, quarter=:quarter, observation_date=:observation_date,
                    ratings=:ratings, strengths=:strengths, recommendations=:recommendations,
                    status=:status, updated_at=NOW()
                WHERE id=:id
            ")->execute($payload);
            return (int)$existing['id'];
        }
        $this->db->prepare("
            INSERT INTO cot_observations
            (student_id, iep_record_id, observed_teacher_id, created_by, lesson_plan_id, school_year, quarter, observation_date, ratings, strengths, recommendations, status)
            VALUES (:student_id, :iep_record_id, :observed_teacher_id, :created_by, :lesson_plan_id, :school_year, :quarter, :observation_date, :ratings, :strengths, :recommendations, :status)
        ")->execute($payload);
        return (int)$this->db->lastInsertId();
    }

    public function saveReadiness(int $iepId, int $studentId, int $userId, array $data, ?int $progressReportId, ?int $cotId): int {
        $existing = $this->latest('transition_readiness', 'iep_record_id', $iepId);
        $payload = [
            'student_id' => $studentId,
            'iep_record_id' => $iepId,
            'progress_report_id' => $progressReportId,
            'cot_observation_id' => $cotId,
            'created_by' => $userId,
            'readiness_result' => $data['readiness_result'] ?? 'For Re-evaluation',
            'evidence_summary' => $data['evidence_summary'] ?? null,
            'teacher_recommendation' => $data['teacher_recommendation'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'finalized_at' => !empty($data['status']) && $data['status'] === 'finalized' ? date('Y-m-d H:i:s') : null,
            'overall_status' => $data['overall_status'] ?? 'partial',
            'overall_status_overridden' => !empty($data['overall_status_overridden']) ? 1 : 0,
            'overall_remarks' => $data['overall_remarks'] ?? null,
            'evaluated_by' => !empty($data['status']) && $data['status'] === 'finalized' ? $userId : null,
        ];
        if ($existing) {
            $payload['id'] = (int)$existing['id'];
            $this->db->prepare("
                UPDATE transition_readiness SET student_id=:student_id, iep_record_id=:iep_record_id,
                    progress_report_id=:progress_report_id, cot_observation_id=:cot_observation_id, created_by=:created_by,
                    readiness_result=:readiness_result, evidence_summary=:evidence_summary,
                    teacher_recommendation=:teacher_recommendation, status=:status,
                    finalized_at=:finalized_at, overall_status=:overall_status,
                    overall_status_overridden=:overall_status_overridden, overall_remarks=:overall_remarks,
                    evaluated_by=:evaluated_by, updated_at=NOW()
                WHERE id=:id
            ")->execute($payload);
            return (int)$existing['id'];
        }
        $this->db->prepare("
            INSERT INTO transition_readiness
            (student_id, iep_record_id, progress_report_id, cot_observation_id, created_by,
             readiness_result, evidence_summary, teacher_recommendation, status,
             finalized_at, overall_status, overall_status_overridden, overall_remarks, evaluated_by)
            VALUES (:student_id, :iep_record_id, :progress_report_id, :cot_observation_id, :created_by,
                    :readiness_result, :evidence_summary, :teacher_recommendation, :status,
                    :finalized_at, :overall_status, :overall_status_overridden, :overall_remarks, :evaluated_by)
        ")->execute($payload);
        return (int)$this->db->lastInsertId();
    }

    public function getTransitionGoals(int $iepId, ?int $readinessId = null): array {
        if ($readinessId) {
            $stmt = $this->db->prepare(
                "SELECT s.id AS step_id, s.step_number, s.step_domain AS pdsp_domain,
                        s.step_objective AS goal_text, s.observation, trg.id AS goal_id,
                        trg.suggested_status, trg.final_status, trg.status_overridden, trg.remarks
                 FROM iep_steps s
                 LEFT JOIN transition_readiness_goals trg
                   ON trg.iep_step_id = s.id AND trg.transition_readiness_id = :rid
                 WHERE s.iep_id = :iep
                 ORDER BY s.step_number ASC, s.id ASC"
            );
            $stmt->execute(['rid' => $readinessId, 'iep' => $iepId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT s.id AS step_id, s.step_number, s.step_domain AS pdsp_domain,
                        s.step_objective AS goal_text, s.observation,
                        NULL AS goal_id, 'partial' AS suggested_status,
                        'partial' AS final_status, FALSE AS status_overridden, NULL AS remarks
                 FROM iep_steps s
                 WHERE s.iep_id = :iep
                 ORDER BY s.step_number ASC, s.id ASC"
            );
            $stmt->execute(['iep' => $iepId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveReadinessGoals(int $readinessId, array $goals): void {
        $existing = [];
        $stmt = $this->db->prepare("SELECT id, iep_step_id FROM transition_readiness_goals WHERE transition_readiness_id = :rid");
        $stmt->execute(['rid' => $readinessId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $existing[(int)$row['iep_step_id']] = (int)$row['id'];
        }

        $insert = $this->db->prepare(
            "INSERT INTO transition_readiness_goals
             (transition_readiness_id, iep_step_id, goal_text, pdsp_domain,
              suggested_status, final_status, status_overridden, remarks)
             VALUES (:transition_readiness_id, :iep_step_id, :goal_text, :pdsp_domain,
                     :suggested_status, :final_status, :status_overridden, :remarks)"
        );
        $update = $this->db->prepare(
            "UPDATE transition_readiness_goals SET
                    goal_text = :goal_text, pdsp_domain = :pdsp_domain,
                    suggested_status = :suggested_status, final_status = :final_status,
                    status_overridden = :status_overridden, remarks = :remarks
             WHERE id = :id"
        );

        foreach ($goals as $goal) {
            $params = [
                'transition_readiness_id' => $readinessId,
                'iep_step_id' => (int)$goal['iep_step_id'],
                'goal_text' => trim($goal['goal_text'] ?? ''),
                'pdsp_domain' => trim($goal['pdsp_domain'] ?? ''),
                'suggested_status' => $goal['suggested_status'] ?? 'partial',
                'final_status' => $goal['final_status'] ?? 'partial',
                'status_overridden' => !empty($goal['status_overridden']) ? 1 : 0,
                'remarks' => trim($goal['remarks'] ?? ''),
            ];
            if (isset($existing[(int)$goal['iep_step_id']])) {
                $updateParams = [
                    'id' => $existing[(int)$goal['iep_step_id']],
                    'goal_text' => $params['goal_text'],
                    'pdsp_domain' => $params['pdsp_domain'],
                    'suggested_status' => $params['suggested_status'],
                    'final_status' => $params['final_status'],
                    'status_overridden' => $params['status_overridden'],
                    'remarks' => $params['remarks'],
                ];
                $update->execute($updateParams);
            } else {
                $insert->execute($params);
            }
        }
    }

    public function getLatestReadinessByIepId(int $iepId): ?array {
        return $this->latest('transition_readiness', 'iep_record_id', $iepId);
    }

    public function suggestReadiness(array $evidence): string {
        $summary = $evidence['progress_summary'] ?? [];
        $submissions = (int)($summary['submissions'] ?? 0);
        $avg = $summary['avg_score'] !== null ? (float)$summary['avg_score'] : null;
        if ($submissions >= 5 && $avg !== null && $avg >= 75) {
            return 'Ready for Inclusion';
        }
        if ($submissions >= 3) {
            return 'Needs More Support';
        }
        if ($submissions > 0) {
            return 'For Re-evaluation';
        }
        return 'Not Yet Ready';
    }

    public function saveItp(int $iepId, int $studentId, int $userId, int $readinessId, array $data): int {
        $existing = $this->latest('individual_transition_plans', 'iep_record_id', $iepId);
        $payload = [
            'student_id' => $studentId,
            'iep_record_id' => $iepId,
            'transition_readiness_id' => $readinessId,
            'created_by' => $userId,
            'entry_point' => $data['entry_point'] ?? null,
            'learner_information' => json_encode($data['learner_information'] ?? []),
            'transition_services' => $data['transition_services'] ?? null,
            'support_needed' => $data['support_needed'] ?? null,
            'team_responsibilities' => $data['team_responsibilities'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];
        if ($existing) {
            $payload['id'] = (int)$existing['id'];
            $this->db->prepare("
                UPDATE individual_transition_plans SET student_id=:student_id, iep_record_id=:iep_record_id,
                    transition_readiness_id=:transition_readiness_id, created_by=:created_by,
                    entry_point=:entry_point, learner_information=:learner_information,
                    transition_services=:transition_services, support_needed=:support_needed,
                    team_responsibilities=:team_responsibilities, status=:status, updated_at=NOW()
                WHERE id=:id
            ")->execute($payload);
            return (int)$existing['id'];
        }
        $this->db->prepare("
            INSERT INTO individual_transition_plans
            (student_id, iep_record_id, transition_readiness_id, created_by, entry_point, learner_information, transition_services, support_needed, team_responsibilities, status)
            VALUES (:student_id, :iep_record_id, :transition_readiness_id, :created_by, :entry_point, :learner_information, :transition_services, :support_needed, :team_responsibilities, :status)
        ")->execute($payload);
        return (int)$this->db->lastInsertId();
    }

    public function saveInclusiveIepAndItgp(int $studentId, int $iepId, int $readinessId, int $itpId, int $userId, array $data): array {
        $inclusive = $this->latest('inclusive_iep_records', 'original_iep_record_id', $iepId);
        $payload = [
            'student_id' => $studentId,
            'original_iep_record_id' => $iepId,
            'transition_readiness_id' => $readinessId,
            'itp_id' => $itpId,
            'created_by' => $userId,
            'generated_summary' => $data['generated_summary'] ?? null,
            'progress_remarks' => $data['progress_remarks'] ?? null,
            'cot_recommendations' => $data['cot_recommendations'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];
        if ($inclusive) {
            $payload['id'] = (int)$inclusive['id'];
            $this->db->prepare("
                UPDATE inclusive_iep_records SET student_id=:student_id, original_iep_record_id=:original_iep_record_id,
                    transition_readiness_id=:transition_readiness_id, itp_id=:itp_id, created_by=:created_by,
                    generated_summary=:generated_summary, progress_remarks=:progress_remarks,
                    cot_recommendations=:cot_recommendations, status=:status, updated_at=NOW()
                WHERE id=:id
            ")->execute($payload);
            $inclusiveId = (int)$inclusive['id'];
        } else {
            $this->db->prepare("
                INSERT INTO inclusive_iep_records
                (student_id, original_iep_record_id, transition_readiness_id, itp_id, created_by, generated_summary, progress_remarks, cot_recommendations, status)
                VALUES (:student_id, :original_iep_record_id, :transition_readiness_id, :itp_id, :created_by, :generated_summary, :progress_remarks, :cot_recommendations, :status)
            ")->execute($payload);
            $inclusiveId = (int)$this->db->lastInsertId();
        }

        $itgp = $this->latestItgp($iepId);
        $itgpPayload = [
            'student_id' => $studentId,
            'inclusive_iep_id' => $inclusiveId,
            'transition_readiness_id' => $readinessId,
            'itp_id' => $itpId,
            'created_by' => $userId,
            'learner_name' => $data['learner_name'] ?? null,
            'disability' => $data['disability'] ?? null,
            'entry_point' => $data['entry_point'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];
        if ($itgp) {
            $itgpPayload['id'] = (int)$itgp['id'];
            $this->db->prepare("
                UPDATE itgp_records SET student_id=:student_id, inclusive_iep_id=:inclusive_iep_id,
                    transition_readiness_id=:transition_readiness_id, itp_id=:itp_id, created_by=:created_by,
                    learner_name=:learner_name, disability=:disability,
                    entry_point=:entry_point, recommendations=:recommendations, status=:status, updated_at=NOW()
                WHERE id=:id
            ")->execute($itgpPayload);
            $itgpId = (int)$itgp['id'];
        } else {
            $this->db->prepare("
                INSERT INTO itgp_records
                (student_id, inclusive_iep_id, transition_readiness_id, itp_id, created_by, learner_name, disability, entry_point, recommendations, status)
                VALUES (:student_id, :inclusive_iep_id, :transition_readiness_id, :itp_id, :created_by, :learner_name, :disability, :entry_point, :recommendations, :status)
            ")->execute($itgpPayload);
            $itgpId = (int)$this->db->lastInsertId();
        }

        $this->db->prepare("DELETE FROM itgp_items WHERE itgp_id = :id")->execute(['id' => $itgpId]);
        $itemStmt = $this->db->prepare("
            INSERT INTO itgp_items
            (itgp_id, goal, learning_packages, competency_skill, activities, time_frame, person_responsible, remarks, recommendations, display_order)
            VALUES (:itgp_id, :goal, :learning_packages, :competency_skill, :activities, :time_frame, :person_responsible, :remarks, :recommendations, :display_order)
        ");
        foreach (($data['items'] ?? []) as $idx => $item) {
            $itemStmt->execute([
                'itgp_id' => $itgpId,
                'goal' => $item['goal'] ?? null,
                'learning_packages' => $item['learning_packages'] ?? null,
                'competency_skill' => $item['competency_skill'] ?? null,
                'activities' => $item['activities'] ?? null,
                'time_frame' => $item['time_frame'] ?? null,
                'person_responsible' => $item['person_responsible'] ?? null,
                'remarks' => $item['remarks'] ?? null,
                'recommendations' => $item['recommendations'] ?? null,
                'display_order' => $idx,
            ]);
        }
        return ['inclusive_iep_id' => $inclusiveId, 'itgp_id' => $itgpId];
    }

    public function savePlacement(int $studentId, int $inclusiveIepId, int $itgpId, int $readinessId, int $userId, array $data): int {
        $teacher = $this->getUser((int)$data['receiving_teacher_id']);
        if (!$teacher) {
            throw new RuntimeException('Selected receiving teacher account was not found.');
        }
        $existing = $this->latestPlacementByInclusive($inclusiveIepId);
        $payload = [
            'student_id' => $studentId,
            'inclusive_iep_id' => $inclusiveIepId,
            'itgp_id' => $itgpId,
            'transition_readiness_id' => $readinessId,
            'receiving_teacher_id' => (int)$teacher['id'],
            'generated_by' => $userId,
            'receiving_teacher_name' => $teacher['name'],
            'receiving_teacher_role' => $teacher['role'],
            'target_grade_section' => $data['target_grade_section'] ?? null,
            'effective_date' => $data['effective_date'] ?: null,
            'support_needed' => $data['support_needed'] ?? null,
            'placement_status' => $data['placement_status'] ?? 'Draft',
            'approval_status' => $data['approval_status'] ?? 'draft',
        ];
        if ($existing) {
            $payload['id'] = (int)$existing['id'];
            $this->db->prepare("
                UPDATE placement_notices SET student_id=:student_id, inclusive_iep_id=:inclusive_iep_id,
                    itgp_id=:itgp_id, transition_readiness_id=:transition_readiness_id,
                    receiving_teacher_id=:receiving_teacher_id, generated_by=:generated_by,
                    receiving_teacher_name=:receiving_teacher_name, receiving_teacher_role=:receiving_teacher_role,
                    target_grade_section=:target_grade_section, effective_date=:effective_date,
                    support_needed=:support_needed, placement_status=:placement_status,
                    approval_status=:approval_status, updated_at=NOW()
                WHERE id=:id
            ")->execute($payload);
            return (int)$existing['id'];
        }
        $this->db->prepare("
            INSERT INTO placement_notices
            (student_id, inclusive_iep_id, itgp_id, transition_readiness_id, receiving_teacher_id, generated_by,
             receiving_teacher_name, receiving_teacher_role, target_grade_section, effective_date, support_needed, placement_status, approval_status)
            VALUES (:student_id, :inclusive_iep_id, :itgp_id, :transition_readiness_id, :receiving_teacher_id, :generated_by,
             :receiving_teacher_name, :receiving_teacher_role, :target_grade_section, :effective_date, :support_needed, :placement_status, :approval_status)
        ")->execute($payload);
        return (int)$this->db->lastInsertId();
    }

    private function latestPlacementByInclusive(int $inclusiveIepId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM placement_notices WHERE inclusive_iep_id = :id ORDER BY id DESC LIMIT 1");
        $stmt->execute(['id' => $inclusiveIepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getUser(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markPlacementNotificationSent(int $placementId, bool $parent = false): void {
        $column = $parent ? 'parent_notification_sent_at' : 'notification_sent_at';
        $statusSql = $parent ? '' : ", placement_status = 'Notice Sent'";
        $stmt = $this->db->prepare("UPDATE placement_notices SET {$column} = NOW(){$statusSql} WHERE id = :id");
        $stmt->execute(['id' => $placementId]);
    }

    public function markCotNotificationSent(int $cotId): void {
        $stmt = $this->db->prepare("UPDATE cot_observations SET notification_sent_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $cotId]);
    }

    public function getItpByStudent(int $studentId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM itp_records WHERE student_id = :student_id LIMIT 1");
        $stmt->execute(['student_id' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['learner_information'] = $row['learner_information'] ? json_decode($row['learner_information'], true) : [];
        }
        return $row ?: null;
    }

    public function getItpById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM itp_records WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['learner_information'] = $row['learner_information'] ? json_decode($row['learner_information'], true) : [];
        }
        return $row ?: null;
    }

    public function getStudentPersonalInfoForItp(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT sr.student_name, sr.date_of_birth, sr.lrn, sr.disability_type,
                   es.sex AS gender,
                   CONCAT_WS(' ', es.father_first_name, es.father_last_name) AS father_name,
                   CONCAT_WS(' ', es.mother_first_name, es.mother_maiden_last_name) AS mother_name,
                   CONCAT_WS(' ', es.current_house_no, es.current_barangay, es.current_city, es.current_province) AS address,
                   COALESCE(es.guardian_contact_number, es.father_contact_number, es.mother_contact_number) AS contact_no,
                   es.grade_level_to_enroll AS level_of_education,
                   es.previous_school_name AS previous_school
            FROM student_records sr
            LEFT JOIN enrollment_submissions es ON es.id = sr.enrollment_id
            WHERE sr.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $info = $row ?: [];
        
        // Check if has finalized assessment
        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM assessment_records WHERE student_id = :student_id AND status = 'finalized'");
        $stmt2->execute(['student_id' => $studentId]);
        $hasAssessment = (int)$stmt2->fetchColumn() > 0;
        
        $info['exceptionality_type'] = $hasAssessment ? 'With Assessment' : 'Without Assessment';
        $info['exceptionality_assessment'] = '';
        $info['religion'] = '';
        $info['years_in_school'] = '';
        
        return $info;
    }

    public function saveItpPartI(int $studentId, int $readinessId, int $userId, array $data): int {
        $existing = $this->getItpByStudent($studentId);
        
        $payload = [
            'student_id' => $studentId,
            'transition_readiness_id' => $readinessId,
            'school_year' => $data['school_year'],
            'point_of_entry' => $data['point_of_entry'] ?? null,
            'learner_information' => json_encode($data['learner_information'] ?? []),
            'status' => $data['status'] ?? 'in_progress',
            'drafted_by' => $userId,
        ];

        if ($existing) {
            $payload['id'] = (int)$existing['id'];
            unset($payload['student_id']);
            $this->db->prepare("
                UPDATE itp_records SET 
                    transition_readiness_id = :transition_readiness_id,
                    school_year = :school_year,
                    point_of_entry = :point_of_entry,
                    learner_information = :learner_information,
                    status = :status,
                    drafted_by = :drafted_by,
                    updated_at = NOW()
                WHERE id = :id
            ")->execute($payload);
            $itpId = (int)$existing['id'];
        } else {
            $this->db->prepare("
                INSERT INTO itp_records (student_id, transition_readiness_id, school_year, point_of_entry, learner_information, status, drafted_by)
                VALUES (:student_id, :transition_readiness_id, :school_year, :point_of_entry, :learner_information, :status, :drafted_by)
            ")->execute($payload);
            $itpId = (int)$this->db->lastInsertId();

            // Initialize itp_team_members rows
            $roles = [
                'itp_coordinator',
                'school_head',
                'sped_teacher',
                'parent_guardian',
                'learner',
                'guidance_teacher',
                'linkages'
            ];

            $stmt = $this->db->prepare("
                INSERT INTO itp_team_members (itp_id, role, assigned_user_id, status)
                VALUES (:itp_id, :role, :assigned_user_id, 'pending')
            ");

            foreach ($roles as $r) {
                $assignedId = null;
                if ($r === 'sped_teacher') {
                    $assignedId = $userId;
                }
                $stmt->execute([
                    'itp_id' => $itpId,
                    'role' => $r,
                    'assigned_user_id' => $assignedId
                ]);
            }
        }
        return $itpId;
    }

    public function getTeamMembers(int $itpId): array {
        $stmt = $this->db->prepare("
            SELECT tm.*, u.name as user_name, u.email as user_email, u.role as user_role 
            FROM itp_team_members tm 
            LEFT JOIN users u ON u.id = tm.assigned_user_id 
            WHERE tm.itp_id = :itp_id 
            ORDER BY tm.id ASC
        ");
        $stmt->execute(['itp_id' => $itpId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeamMemberById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT tm.*, u.name as user_name, u.email as user_email, u.role as user_role 
            FROM itp_team_members tm 
            LEFT JOIN users u ON u.id = tm.assigned_user_id 
            WHERE tm.id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function assignTeamMember(int $itpId, string $role, ?int $assignedUserId, ?string $notApplicableValue = null): bool {
        if ($notApplicableValue !== null && $notApplicableValue !== '') {
            $stmt = $this->db->prepare("
                UPDATE itp_team_members 
                SET assigned_user_id = NULL,
                    name = 'Not Applicable',
                    contact_details = 'N/A',
                    date_started = NULL,
                    status = 'filled',
                    updated_at = NOW()
                WHERE itp_id = :itp_id AND role = :role
            ");
            return $stmt->execute([
                'itp_id' => $itpId,
                'role' => $role
            ]);
        }

        if ($assignedUserId !== null) {
            $user = $this->getUser($assignedUserId);
            if ($user) {
                $stmt = $this->db->prepare("
                    UPDATE itp_team_members 
                    SET assigned_user_id = :assigned_user_id,
                        name = :name,
                        contact_details = :contact_details,
                        status = 'pending',
                        updated_at = NOW()
                    WHERE itp_id = :itp_id AND role = :role
                ");
                return $stmt->execute([
                    'assigned_user_id' => $assignedUserId,
                    'name' => $user['name'],
                    'contact_details' => $user['email'],
                    'itp_id' => $itpId,
                    'role' => $role
                ]);
            }
        }

        // If both are empty/null (unassigned)
        $stmt = $this->db->prepare("
            UPDATE itp_team_members 
            SET assigned_user_id = NULL,
                name = NULL,
                contact_details = NULL,
                date_started = NULL,
                status = 'pending',
                updated_at = NOW()
            WHERE itp_id = :itp_id AND role = :role
        ");
        return $stmt->execute([
            'itp_id' => $itpId,
            'role' => $role
        ]);
    }

    public function updateTeamMemberDetails(int $id, string $name, string $contactDetails, ?string $dateStarted): bool {
        $stmt = $this->db->prepare("
            UPDATE itp_team_members 
            SET name = :name,
                contact_details = :contact_details,
                date_started = :date_started,
                status = 'filled',
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'name' => $name,
            'contact_details' => $contactDetails,
            'date_started' => !empty($dateStarted) ? $dateStarted : null,
            'id' => $id
        ]);
    }

    public function getActiveUsersByRoles(array $roles): array {
        if (empty($roles)) {
            $stmt = $this->db->query("SELECT id, name, email, role FROM users WHERE status = 'active' ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $inQuery = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $this->db->prepare("SELECT id, name, email, role FROM users WHERE status = 'active' AND role IN ($inQuery) ORDER BY name ASC");
        $stmt->execute($roles);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNarrativeItems(int $itpId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM itp_narrative 
            WHERE itp_id = :itp_id 
            ORDER BY section, display_order ASC
        ");
        $stmt->execute(['itp_id' => $itpId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveNarrativeItems(int $itpId, string $section, array $items): void {
        $stmtDel = $this->db->prepare("DELETE FROM itp_narrative WHERE itp_id = :itp_id AND section = :section");
        $stmtDel->execute(['itp_id' => $itpId, 'section' => $section]);

        if (empty($items)) {
            return;
        }

        $stmtIns = $this->db->prepare("
            INSERT INTO itp_narrative (itp_id, section, item_text, display_order)
            VALUES (:itp_id, :section, :item_text, :display_order)
        ");

        $order = 0;
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            $stmtIns->execute([
                'itp_id' => $itpId,
                'section' => $section,
                'item_text' => $item,
                'display_order' => $order++
            ]);
        }
    }

    public function getRecommendation(int $itpId, string $timing): ?string {
        $stmt = $this->db->prepare("
            SELECT recommendation_text FROM itp_recommendations 
            WHERE itp_id = :itp_id AND timing = :timing 
            LIMIT 1
        ");
        $stmt->execute(['itp_id' => $itpId, 'timing' => $timing]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : null;
    }

    public function saveRecommendation(int $itpId, string $timing, string $text): bool {
        $stmt = $this->db->prepare("
            INSERT INTO itp_recommendations (itp_id, timing, recommendation_text)
            VALUES (:itp_id, :timing, :recommendation_text)
            ON DUPLICATE KEY UPDATE recommendation_text = :recommendation_text_update
        ");
        return $stmt->execute([
            'itp_id' => $itpId,
            'timing' => $timing,
            'recommendation_text' => trim($text),
            'recommendation_text_update' => trim($text)
        ]);
    }

    public function getProgramMatrix(int $itpId): array {
        $stmt = $this->db->prepare("SELECT row_type, column_type, is_checked FROM itp_program_matrix WHERE itp_id = :itp_id");
        $stmt->execute(['itp_id' => $itpId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $matrix = [];
        foreach ($rows as $row) {
            if ($row['is_checked']) {
                $matrix[$row['row_type'] . '-' . $row['column_type']] = true;
            }
        }
        return $matrix;
    }

    public function saveProgramMatrix(int $itpId, array $checkedCells): void {
        $stmtDel = $this->db->prepare("DELETE FROM itp_program_matrix WHERE itp_id = :itp_id");
        $stmtDel->execute(['itp_id' => $itpId]);

        if (empty($checkedCells)) {
            return;
        }

        $stmtIns = $this->db->prepare("
            INSERT INTO itp_program_matrix (itp_id, row_type, column_type, is_checked)
            VALUES (:itp_id, :row_type, :column_type, TRUE)
        ");

        foreach ($checkedCells as $cell) {
            if (isset($cell['row']) && isset($cell['col'])) {
                $stmtIns->execute([
                    'itp_id' => $itpId,
                    'row_type' => (int)$cell['row'],
                    'column_type' => (int)$cell['col']
                ]);
            }
        }
    }

    public function getParentSignature(int $itpId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM itp_signatures 
            WHERE itp_id = :itp_id AND signatory_role = 'parent_guardian' 
            LIMIT 1
        ");
        $stmt->execute(['itp_id' => $itpId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function saveParentSignature(int $itpId, string $imagePath): bool {
        $stmt = $this->db->prepare("
            INSERT INTO itp_signatures (itp_id, signatory_role, signature_image_path, signed_at)
            VALUES (:itp_id, 'parent_guardian', :signature_image_path, NOW())
            ON DUPLICATE KEY UPDATE signature_image_path = :signature_image_path_update, signed_at = NOW()
        ");
        return $stmt->execute([
            'itp_id' => $itpId,
            'signature_image_path' => $imagePath,
            'signature_image_path_update' => $imagePath
        ]);
    }

    public function finalizeItp(int $itpId): bool {
        $stmt = $this->db->prepare("
            UPDATE itp_records 
            SET status = 'finalized', finalized_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $itpId]);
    }

    public function getItgpByStudent(int $studentId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM itgp_records WHERE student_id = :student_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['student_id' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $stmtAct = $this->db->prepare("SELECT * FROM itgp_activities WHERE itgp_id = :itgp_id ORDER BY display_order ASC");
            $stmtAct->execute(['itgp_id' => $row['id']]);
            $row['activities'] = $stmtAct->fetchAll(PDO::FETCH_ASSOC);
        }
        return $row ?: null;
    }

    public function getItgpById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM itgp_records WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $stmtAct = $this->db->prepare("SELECT * FROM itgp_activities WHERE itgp_id = :itgp_id ORDER BY display_order ASC");
            $stmtAct->execute(['itgp_id' => $row['id']]);
            $row['activities'] = $stmtAct->fetchAll(PDO::FETCH_ASSOC);
        }
        return $row ?: null;
    }

    public function getAssignmentByStudent(int $studentId): ?array {
        $stmt = $this->db->prepare("
            SELECT gta.*, u.name AS general_teacher_name, u.email AS general_teacher_email
            FROM general_teacher_assignments gta
            JOIN users u ON u.id = gta.general_teacher_id
            WHERE gta.student_id = :student_id
            ORDER BY gta.assigned_at DESC LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function assignGeneralTeacher(int $studentId, int $teacherId, int $assignedBy): bool {
        $stmt = $this->db->prepare("
            INSERT INTO general_teacher_assignments (student_id, general_teacher_id, assigned_by, assigned_at)
            VALUES (:student_id, :general_teacher_id, :assigned_by, NOW())
            ON DUPLICATE KEY UPDATE general_teacher_id = :general_teacher_id_up, assigned_by = :assigned_by_up, assigned_at = NOW()
        ");
        return $stmt->execute([
            'student_id' => $studentId,
            'general_teacher_id' => $teacherId,
            'assigned_by' => $assignedBy,
            'general_teacher_id_up' => $teacherId,
            'assigned_by_up' => $assignedBy
        ]);
    }

    public function getApprovedGeneralTeachers(): array {
        $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE role = 'general_teacher' AND status = 'active' ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveItgp(int $studentId, int $itpId, int $teacherId, array $data): int {
        $existing = $this->getItgpByStudent($studentId);
        
        $payload = [
            'student_id' => $studentId,
            'itp_id' => $itpId,
            'general_teacher_id' => $teacherId,
            'goal' => $data['goal'] ?? null,
            'entry_point' => $data['entry_point'] ?? null,
            'learning_packages' => $data['learning_packages'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];

        if ($existing) {
            $payload['id'] = (int)$existing['id'];
            $finalizedAtClause = "";
            if ($data['status'] === 'finalized') {
                $finalizedAtClause = ", finalized_at = NOW()";
            }
            $stmt = $this->db->prepare("
                UPDATE itgp_records SET
                    goal = :goal,
                    entry_point = :entry_point,
                    learning_packages = :learning_packages,
                    recommendations = :recommendations,
                    status = :status
                    $finalizedAtClause
                WHERE id = :id
            ");
            $stmt->execute([
                'goal' => $payload['goal'],
                'entry_point' => $payload['entry_point'],
                'learning_packages' => $payload['learning_packages'],
                'recommendations' => $payload['recommendations'],
                'status' => $payload['status'],
                'id' => $payload['id']
            ]);
            $itgpId = (int)$existing['id'];
        } else {
            $finalizedAtVal = ($data['status'] === 'finalized') ? "NOW()" : "NULL";
            $stmt = $this->db->prepare("
                INSERT INTO itgp_records (student_id, itp_id, general_teacher_id, goal, entry_point, learning_packages, recommendations, status, finalized_at)
                VALUES (:student_id, :itp_id, :general_teacher_id, :goal, :entry_point, :learning_packages, :recommendations, :status, $finalizedAtVal)
            ");
            $stmt->execute([
                'student_id' => $studentId,
                'itp_id' => $itpId,
                'general_teacher_id' => $teacherId,
                'goal' => $payload['goal'],
                'entry_point' => $payload['entry_point'],
                'learning_packages' => $payload['learning_packages'],
                'recommendations' => $payload['recommendations'],
                'status' => $payload['status'],
            ]);
            $itgpId = (int)$this->db->lastInsertId();
        }

        // Re-sync activities
        $this->db->prepare("DELETE FROM itgp_activities WHERE itgp_id = :id")->execute(['id' => $itgpId]);
        
        $insAct = $this->db->prepare("
            INSERT INTO itgp_activities (itgp_id, competency_skill, activities, time_frame, person_responsible, remarks, display_order)
            VALUES (:itgp_id, :competency_skill, :activities, :time_frame, :person_responsible, :remarks, :display_order)
        ");
        
        foreach (($data['activities'] ?? []) as $idx => $act) {
            $insAct->execute([
                'itgp_id' => $itgpId,
                'competency_skill' => $act['competency_skill'] ?? null,
                'activities' => $act['activities'] ?? null,
                'time_frame' => $act['time_frame'] ?? null,
                'person_responsible' => $act['person_responsible'] ?? null,
                'remarks' => $act['remarks'] ?? null,
                'display_order' => $idx
            ]);
        }

        return $itgpId;
    }

    public function getItgpComments(int $itgpId): array {
        $stmt = $this->db->prepare("
            SELECT ic.*, u.name AS author_name, u.role AS author_role
            FROM itgp_comments ic
            JOIN users u ON u.id = ic.posted_by
            WHERE ic.itgp_id = :itgp_id
            ORDER BY ic.created_at ASC
        ");
        $stmt->execute(['itgp_id' => $itgpId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addItgpComment(int $itgpId, int $userId, string $text): bool {
        $stmt = $this->db->prepare("
            INSERT INTO itgp_comments (itgp_id, posted_by, comment_text, created_at)
            VALUES (:itgp_id, :posted_by, :comment_text, NOW())
        ");
        return $stmt->execute([
            'itgp_id' => $itgpId,
            'posted_by' => $userId,
            'comment_text' => $text
        ]);
    }

    public function saveClassPlacement(int $studentId, int $itgpId, int $reviewerId, string $status, ?string $holdReason = null): bool {
        $confirmedAt = ($status === 'confirmed') ? 'NOW()' : 'NULL';
        $stmt = $this->db->prepare("
            INSERT INTO class_placements (student_id, itgp_id, reviewed_by, status, hold_reason, confirmed_at, created_at)
            VALUES (:student_id, :itgp_id, :reviewed_by, :status, :hold_reason, $confirmedAt, NOW())
        ");
        $success = $stmt->execute([
            'student_id' => $studentId,
            'itgp_id' => $itgpId,
            'reviewed_by' => $reviewerId,
            'status' => $status,
            'hold_reason' => $holdReason
        ]);

        if ($success && $status === 'confirmed') {
            // Archive/Mainstream the student in student_records
            $stmtSR = $this->db->prepare("UPDATE student_records SET status = 'mainstreamed' WHERE id = :student_id");
            $stmtSR->execute(['student_id' => $studentId]);
        }
        return $success;
    }

    public function getLatestPlacementByStudent(int $studentId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM class_placements WHERE student_id = :student_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['student_id' => $studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getPlacementHistory(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT cp.*, u.name AS reviewer_name, u.role AS reviewer_role
            FROM class_placements cp
            JOIN users u ON u.id = cp.reviewed_by
            WHERE cp.student_id = :student_id
            ORDER BY cp.created_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
