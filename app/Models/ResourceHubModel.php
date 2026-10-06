<?php
// SignED — Accessibility Pack Resource Hub Model (MVP)
// Manages reusable, sanitized educational packs isolated from learner IEP records.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/LessonPlanModel.php';
require_once __DIR__ . '/../Helpers/HtmlSanitizer.php';

class ResourceHubModel {

    private PDO $db;
    private LessonPlanModel $lpModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->lpModel = new LessonPlanModel();
    }

    /**
     * Get all active Resource Hub packs with optional filters and search
     */
    public function getAllPacks(array $filters = []): array {
        $sql = "
            SELECT p.*,
                   u.name AS creator_user_name
            FROM resource_hub_packs p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.status = 'published'
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $sql .= " AND (p.title LIKE :s1 OR p.description LIKE :s2 OR p.learning_competency LIKE :s3 OR p.tags LIKE :s4)";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
            $params['s4'] = $term;
        }

        if (!empty($filters['domain'])) {
            $sql .= " AND p.pdsp_domain = :domain";
            $params['domain'] = $filters['domain'];
        }

        if (!empty($filters['level'])) {
            $sql .= " AND p.learning_level = :level";
            $params['level'] = $filters['level'];
        }

        if (!empty($filters['fsl_only'])) {
            $sql .= " AND p.fsl_supported = 1 AND p.fsl_sign_count > 0";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get a single pack by ID
     */
    public function getPackById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   u.name AS creator_user_name,
                   u.email AS creator_user_email
            FROM resource_hub_packs p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all pages for a resource pack
     */
    public function getPackPages(int $packId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM resource_hub_pages
            WHERE pack_id = :pack_id
            ORDER BY page_number ASC, id ASC
        ");
        $stmt->execute(['pack_id' => $packId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get all activities for a resource pack
     */
    public function getPackActivities(int $packId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM resource_hub_activities
            WHERE pack_id = :pack_id
            ORDER BY display_order ASC, id ASC
        ");
        $stmt->execute(['pack_id' => $packId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count FSL signs across an array of HTML slide contents
     */
    public static function countFslSignsInHtml(array $pagesHtml): int {
        $totalSigns = 0;
        foreach ($pagesHtml as $html) {
            if (empty($html)) continue;
            // Match .fsl-word spans
            if (preg_match_all('/class=["\'][^"\']*fsl-word[^"\']*["\']/i', $html, $matches)) {
                $totalSigns += count($matches[0]);
            }
        }
        return $totalSigns;
    }

    /**
     * Publish/share an existing lesson plan as a reusable Resource Hub Accessibility Pack
     * Ensures complete privacy by omitting all learner PII, IEP IDs, student IDs, and assessment logs.
     */
    public function shareLessonToHub(int $lessonPlanId, int $teacherId, string $teacherName, array $meta): int {
        $lp = $this->lpModel->findById($lessonPlanId);
        if (!$lp) {
            throw new InvalidArgumentException("Lesson plan not found.");
        }

        $pages = $this->lpModel->getPagesByLessonPlan($lessonPlanId);
        $activities = $this->lpModel->getActivitiesByIepId((int)$lp['iep_id']);
        // Filter activities specifically linked to this lesson plan
        $lessonActivities = array_filter($activities, function($a) use ($lessonPlanId) {
            return (int)($a['lesson_plan_id'] ?? 0) === $lessonPlanId;
        });

        // Compute FSL sign count from pages
        $pageContents = array_column($pages, 'content');
        $fslSignCount = self::countFslSignsInHtml($pageContents);

        // Sanitize and normalize inputs
        $title = trim($meta['title'] ?? $lp['title'] ?? 'Accessible Lesson Pack');
        $description = trim($meta['description'] ?? '');
        $domain = $meta['pdsp_domain'] ?? $lp['pdsp_domain'] ?? 'communication_language';
        $level = trim($meta['learning_level'] ?? 'Primary SPED / Deaf Ed');
        $competency = trim($meta['learning_competency'] ?? '');
        $language = trim($meta['language'] ?? 'Filipino / FSL');
        $tags = trim($meta['tags'] ?? '');
        
        $masteryUrl = trim($meta['mainstream_mastery_url'] ?? $lp['mainstream_mastery_url'] ?? '');
        $masteryTitle = trim($meta['mainstream_mastery_title'] ?? $lp['mainstream_mastery_title'] ?? '');
        if (!empty($masteryUrl) && !filter_var($masteryUrl, FILTER_VALIDATE_URL)) {
            $masteryUrl = null;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO resource_hub_packs (
                    source_lesson_plan_id, created_by, author_name, title, description,
                    pdsp_domain, learning_level, learning_competency, language,
                    fsl_supported, fsl_sign_count, page_count, activity_count, tags,
                    mainstream_mastery_url, mainstream_mastery_title, status, clone_count
                ) VALUES (
                    :source_lp_id, :created_by, :author_name, :title, :description,
                    :domain, :level, :competency, :language,
                    :fsl_supported, :fsl_sign_count, :page_count, :activity_count, :tags,
                    :mastery_url, :mastery_title, 'published', 0
                )
            ");

            $stmt->execute([
                'source_lp_id'    => $lessonPlanId,
                'created_by'      => $teacherId,
                'author_name'     => $teacherName,
                'title'           => $title,
                'description'     => $description,
                'domain'          => $domain,
                'level'           => $level,
                'competency'      => $competency,
                'language'        => $language,
                'fsl_supported'   => ($fslSignCount > 0 ? 1 : 0),
                'fsl_sign_count'  => $fslSignCount,
                'page_count'      => count($pages),
                'activity_count'  => count($lessonActivities),
                'tags'            => $tags,
                'mastery_url'     => $masteryUrl ?: null,
                'mastery_title'   => $masteryTitle ?: null,
            ]);

            $packId = (int)$this->db->lastInsertId();

            // Insert snapshot pages (retaining sanitized .fsl-word spans)
            $stmtPage = $this->db->prepare("
                INSERT INTO resource_hub_pages (
                    pack_id, page_number, title, content, guide_questions, media_type, media_path
                ) VALUES (
                    :pack_id, :page_number, :title, :content, :guide_questions, :media_type, :media_path
                )
            ");

            foreach ($pages as $p) {
                // Ensure HTML is clean and safe
                $cleanedContent = HtmlSanitizer::cleanLessonHtml($p['content'] ?? '');
                $stmtPage->execute([
                    'pack_id'         => $packId,
                    'page_number'     => (int)($p['page_number'] ?? 1),
                    'title'           => trim($p['title'] ?? 'Slide'),
                    'content'         => $cleanedContent,
                    'guide_questions' => $p['guide_questions'] ?? null,
                    'media_type'      => $p['media_type'] ?? 'none',
                    'media_path'      => $p['media_path'] ?? null
                ]);
            }

            // Insert snapshot activities (questions/choices only; NO student responses or attempts)
            if (!empty($lessonActivities)) {
                $stmtAct = $this->db->prepare("
                    INSERT INTO resource_hub_activities (
                        pack_id, title, instructions, activity_type, activity_data, max_score, display_order, is_f2f
                    ) VALUES (
                        :pack_id, :title, :instructions, :activity_type, :activity_data, :max_score, :display_order, :is_f2f
                    )
                ");

                foreach ($lessonActivities as $act) {
                    $rawJson = is_array($act['activity_data'] ?? null)
                        ? json_encode($act['activity_data'])
                        : ($act['activity_data'] ?? '[]');

                    $stmtAct->execute([
                        'pack_id'       => $packId,
                        'title'         => trim($act['title'] ?? 'Activity'),
                        'instructions'  => $act['instructions'] ?? '',
                        'activity_type' => $act['activity_type'] ?? 'multiple_choice',
                        'activity_data' => $rawJson,
                        'max_score'     => (int)($act['max_score'] ?? 0),
                        'display_order' => (int)($act['display_order'] ?? 0),
                        'is_f2f'        => (int)($act['is_f2f'] ?? 0),
                    ]);
                }
            }

            $this->db->commit();
            return $packId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Clone a Resource Hub pack into a teacher's chosen IEP context
     * Creates an independent DRAFT lesson plan owned by $teacherId.
     * Transactional: If any step fails, entire clone is rolled back.
     */
    public function clonePackToIep(int $packId, int $targetIepId, int $teacherId, ?int $targetStudentId = null): int {
        $pack = $this->getPackById($packId);
        if (!$pack || $pack['status'] !== 'published') {
            throw new InvalidArgumentException("Accessibility Pack not found or not published.");
        }

        // Verify that target IEP exists and is accessible
        $iep = $this->lpModel->getIepById($targetIepId);
        if (!$iep) {
            throw new InvalidArgumentException("Target learner IEP not found.");
        }

        // Auto-resolve student_id if not explicitly provided
        if (!$targetStudentId && !empty($iep['student_id'])) {
            $targetStudentId = (int)$iep['student_id'];
        }

        $pages = $this->getPackPages($packId);
        $activities = $this->getPackActivities($packId);

        $this->db->beginTransaction();
        try {
            // 1. Create independent lesson_plans row owned by $teacherId
            $stmtLp = $this->db->prepare("
                INSERT INTO lesson_plans (
                    iep_id, student_id, created_by, title, pdsp_domain,
                    assignment_type, cloned_from_pack_id, mainstream_mastery_url, mainstream_mastery_title,
                    status, created_at, updated_at
                ) VALUES (
                    :iep_id, :student_id, :created_by, :title, :pdsp_domain,
                    'individual', :cloned_from_pack_id, :mastery_url, :mastery_title,
                    'draft', NOW(), NOW()
                )
            ");

            $newTitle = $pack['title'] . ' (Adapted)';
            $stmtLp->execute([
                'iep_id'              => $targetIepId,
                'student_id'          => $targetStudentId,
                'created_by'          => $teacherId,
                'title'               => $newTitle,
                'pdsp_domain'         => $pack['pdsp_domain'],
                'cloned_from_pack_id' => $packId,
                'mastery_url'         => $pack['mainstream_mastery_url'] ?? null,
                'mastery_title'       => $pack['mainstream_mastery_title'] ?? null
            ]);

            $newLessonPlanId = (int)$this->db->lastInsertId();

            // 2. Clone pages into lesson_plan_pages
            $stmtPage = $this->db->prepare("
                INSERT INTO lesson_plan_pages (
                    lesson_plan_id, page_number, title, content, guide_questions, media_type, media_path
                ) VALUES (
                    :lesson_plan_id, :page_number, :title, :content, :guide_questions, :media_type, :media_path
                )
            ");

            foreach ($pages as $p) {
                // Keep .fsl-word spans exactly as authored in the template
                $cleanContent = HtmlSanitizer::cleanLessonHtml($p['content'] ?? '');
                $stmtPage->execute([
                    'lesson_plan_id'  => $newLessonPlanId,
                    'page_number'     => (int)$p['page_number'],
                    'title'           => $p['title'],
                    'content'         => $cleanContent,
                    'guide_questions' => $p['guide_questions'] ?? null,
                    'media_type'      => $p['media_type'] ?? 'none',
                    'media_path'      => $p['media_path'] ?? null
                ]);
            }

            // 3. Clone activities into lms_activities (clean educational template questions only)
            if (!empty($activities)) {
                $stmtAct = $this->db->prepare("
                    INSERT INTO lms_activities (
                        lesson_plan_id, title, instructions, activity_type, activity_data, max_score, display_order, is_f2f
                    ) VALUES (
                        :lesson_plan_id, :title, :instructions, :activity_type, :activity_data, :max_score, :display_order, :is_f2f
                    )
                ");

                foreach ($activities as $act) {
                    $stmtAct->execute([
                        'lesson_plan_id' => $newLessonPlanId,
                        'title'          => $act['title'],
                        'instructions'   => $act['instructions'] ?? '',
                        'activity_type'  => $act['activity_type'],
                        'activity_data'  => $act['activity_data'],
                        'max_score'      => (int)($act['max_score'] ?? 0),
                        'display_order'  => (int)($act['display_order'] ?? 0),
                        'is_f2f'         => (int)($act['is_f2f'] ?? 0),
                    ]);
                }
            }

            // 4. Increment clone counter on template
            $stmtCount = $this->db->prepare("UPDATE resource_hub_packs SET clone_count = clone_count + 1 WHERE id = :id");
            $stmtCount->execute(['id' => $packId]);

            $this->db->commit();
            return $newLessonPlanId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Unshare/archive a resource pack (only by owner teacher or admin)
     */
    public function unsharePack(int $packId, int $teacherId, bool $isAdmin = false): bool {
        $pack = $this->getPackById($packId);
        if (!$pack) return false;

        if (!$isAdmin && (int)$pack['created_by'] !== $teacherId) {
            throw new RuntimeException("Access denied: You are not the author of this Accessibility Pack.");
        }

        $stmt = $this->db->prepare("UPDATE resource_hub_packs SET status = 'archived' WHERE id = :id");
        return $stmt->execute(['id' => $packId]);
    }

    /**
     * Get signed IEPs manageable by teacher for cloning
     */
    public function getManageableIepsForTeacher(int $teacherId): array {
        return $this->lpModel->getSignedIEPsForTeacher($teacherId);
    }
}
