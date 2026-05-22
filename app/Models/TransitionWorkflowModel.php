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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (iep_record_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (progress_report_id) REFERENCES progress_reports(id) ON DELETE SET NULL,
                FOREIGN KEY (cot_observation_id) REFERENCES cot_observations(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_transition_iep (iep_record_id),
                INDEX idx_transition_result (readiness_result)
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
                FOREIGN KEY (itp_id) REFERENCES individual_transition_plans(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_inclusive_original_iep (original_iep_record_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itgp_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                inclusive_iep_id INT NOT NULL,
                transition_readiness_id INT NOT NULL,
                itp_id INT NOT NULL,
                created_by INT NOT NULL,
                learner_name VARCHAR(255) NULL,
                disability VARCHAR(255) NULL,
                entry_point VARCHAR(255) NULL,
                recommendations TEXT NULL,
                status ENUM('draft','for_signature','signed') NOT NULL DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (inclusive_iep_id) REFERENCES inclusive_iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
                FOREIGN KEY (itp_id) REFERENCES individual_transition_plans(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_itgp_inclusive (inclusive_iep_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS itgp_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                itgp_id INT NOT NULL,
                goal TEXT NULL,
                learning_packages TEXT NULL,
                competency_skill TEXT NULL,
                activities TEXT NULL,
                time_frame VARCHAR(255) NULL,
                person_responsible VARCHAR(255) NULL,
                remarks TEXT NULL,
                recommendations TEXT NULL,
                display_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE,
                INDEX idx_itgp_items (itgp_id, display_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS placement_notices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                inclusive_iep_id INT NOT NULL,
                itgp_id INT NOT NULL,
                transition_readiness_id INT NOT NULL,
                receiving_teacher_id INT NOT NULL,
                generated_by INT NOT NULL,
                receiving_teacher_name VARCHAR(255) NULL,
                receiving_teacher_role VARCHAR(100) NULL,
                target_grade_section VARCHAR(255) NULL,
                effective_date DATE NULL,
                support_needed TEXT NULL,
                placement_status ENUM('Draft','For Approval','Approved','Notice Sent','Placed') NOT NULL DEFAULT 'Draft',
                approval_status ENUM('draft','for_approval','approved') NOT NULL DEFAULT 'draft',
                notification_sent_at TIMESTAMP NULL,
                parent_notification_sent_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
                FOREIGN KEY (inclusive_iep_id) REFERENCES inclusive_iep_records(id) ON DELETE CASCADE,
                FOREIGN KEY (itgp_id) REFERENCES itgp_records(id) ON DELETE CASCADE,
                FOREIGN KEY (transition_readiness_id) REFERENCES transition_readiness(id) ON DELETE CASCADE,
                FOREIGN KEY (receiving_teacher_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_placement_teacher (receiving_teacher_id),
                INDEX idx_placement_student (student_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];

        foreach ($tables as $sql) {
            try {
                $this->db->exec($sql);
            } catch (Throwable $e) {
                error_log('TransitionWorkflowModel::ensureTables: ' . $e->getMessage());
            }
        }
    }

    public function getIepContext(int $iepId): ?array {
        $stmt = $this->db->prepare("
            SELECT ir.*, sr.student_name, sr.lrn, sr.enrollment_id,
                   es.parent_id, es.grade_level_to_enroll, es.school_year,
                   es.guardian_name, es.relationship_to_learner,
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
        return [
            'progress_report' => $this->latest('progress_reports', 'iep_record_id', $iepId),
            'cot' => $this->latest('cot_observations', 'iep_record_id', $iepId),
            'readiness' => $this->latest('transition_readiness', 'iep_record_id', $iepId),
            'itp' => $this->latest('individual_transition_plans', 'iep_record_id', $iepId),
            'inclusive_iep' => $this->latest('inclusive_iep_records', 'original_iep_record_id', $iepId),
            'itgp' => $this->latestItgp($iepId),
            'placement' => $this->latestPlacement($iepId),
        ];
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
        ];
        if ($existing) {
            $payload['id'] = (int)$existing['id'];
            $this->db->prepare("
                UPDATE transition_readiness SET student_id=:student_id, iep_record_id=:iep_record_id,
                    progress_report_id=:progress_report_id, cot_observation_id=:cot_observation_id, created_by=:created_by,
                    readiness_result=:readiness_result, evidence_summary=:evidence_summary,
                    teacher_recommendation=:teacher_recommendation, status=:status, updated_at=NOW()
                WHERE id=:id
            ")->execute($payload);
            return (int)$existing['id'];
        }
        $this->db->prepare("
            INSERT INTO transition_readiness
            (student_id, iep_record_id, progress_report_id, cot_observation_id, created_by, readiness_result, evidence_summary, teacher_recommendation, status)
            VALUES (:student_id, :iep_record_id, :progress_report_id, :cot_observation_id, :created_by, :readiness_result, :evidence_summary, :teacher_recommendation, :status)
        ")->execute($payload);
        return (int)$this->db->lastInsertId();
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
}
