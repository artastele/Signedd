<?php
// Part of: SignED — Learning Gap Innovation (Process 10.1), Phase 2
// Resource Recommendation Service:
// Generates evidence-based, advisory learning material recommendations for SPED teacher review.
//
// Sources (existing repositories only):
//   - fsl_vocabulary        : verified FSL sign videos/entries matching specific learner vocabulary gaps
//   - resource_hub_packs    : published accessibility packs matching verified IEP objectives or curriculum domains
//   - lesson_plans          : teacher-designated published remedial modules
//   - lms_activities        : published supplementary practice activities within the same competency
//
// STRICT GUARANTEES:
//   - Zero AI hallucinations: never invents FSL videos, packs, or unsupported competency links.
//   - Advisory only: never modifies IEP mastery, grades, or lesson assignments.
//   - Deduplication: each resource appears at most once.
//   - Evidence transparency: every recommendation cites exact performance evidence and repository reference.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/LearningGapAnalyzer.php';
require_once __DIR__ . '/ResourceTagService.php';
require_once __DIR__ . '/ResourceHubContentMatcher.php';
require_once __DIR__ . '/InterventionDraftService.php';

class ResourceRecommendationService {

    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW    = 'low';

    private PDO $db;
    private LearningGapAnalyzer $analyzer;

    public function __construct(?PDO $db = null, ?LearningGapAnalyzer $analyzer = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
        $this->analyzer = $analyzer ?? new LearningGapAnalyzer($this->db);
    }

    /**
     * Generate structured, evidence-based recommendations for a single activity attempt analysis.
     *
     * @param int $studentId
     * @param int $activityId
     * @param array|null $precomputedAnalysis Optional pre-run analysis from LearningGapAnalyzer
     * @return array
     */
    public function recommendForActivity(int $studentId, int $activityId, ?array $precomputedAnalysis = null): array {
        $analysis = $precomputedAnalysis ?? $this->analyzer->analyzeActivity($studentId, $activityId);

        if (!empty($analysis['error'])) {
            return [
                'student_id'            => $studentId,
                'activity_id'           => $activityId,
                'error'                 => $analysis['error'],
                'recommendations'       => [],
                'recommendations_count' => 0,
                'explanation'           => 'Unable to analyze activity: ' . $analysis['error'],
                'generated_at'          => date('Y-m-d H:i:s'),
            ];
        }

        $factors    = $analysis['factors'] ?? [];
        $attempts   = $factors['attempts'] ?? [];
        $accuracy   = $factors['accuracy'] ?? [];
        $trend      = $factors['performance_trend'] ?? [];
        $consistency = $factors['consistency'] ?? [];
        $iepLinks   = $analysis['iep_objectives'] ?? [];
        $lessonCtx  = $analysis['lesson_context'] ?? [];
        $activity   = $analysis['activity'] ?? [];

        $gradedCount   = (int)($attempts['graded'] ?? 0);
        $latestAcc     = $accuracy['latest_result_accuracy'] ?? null;
        $trendClass    = $trend['classification'] ?? LearningGapAnalyzer::INSUFFICIENT;
        $recurring     = $consistency['recurring_incorrect_items'] ?? [];
        $activeRecurring = array_filter($recurring, fn($item) => !empty($item['still_incorrect_latest']) && ($item['incorrect_in_attempts'] ?? 0) >= 2);
        $difficultyItems = $accuracy['latest_incorrect_items'] ?? [];
        $threshold     = (int)($lessonCtx['lesson_score_threshold'] ?? 70);

        $summary = [
            'accuracy'                  => $latestAcc,
            'attempt_count'             => (int)($attempts['total_recorded'] ?? 0),
            'graded_count'              => $gradedCount,
            'consistency'               => $consistency['classification'] ?? LearningGapAnalyzer::INSUFFICIENT,
            'trend'                     => $trendClass,
            'recurring_incorrect_count' => count($recurring),
            'active_recurring_count'    => count($activeRecurring),
            'teacher_review_required'   => (bool)($analysis['teacher_review']['required'] ?? false),
        ];

        // ── Rule 1: Insufficient attempt evidence ────────────────────────────
        if ($gradedCount === 0 || $latestAcc === null || $latestAcc >= 100) {
            return $this->buildResponse(
                $studentId,
                $activity,
                $summary,
                $iepLinks,
                [],
                $gradedCount === 0 || $latestAcc === null
                    ? 'No valid graded latest result is available for targeted recommendations.'
                    : 'The latest attempt has no scored difficulty requiring a targeted recommendation.',
                $factors,
                $analysis['limitations'] ?? []
            );
        }

        // ── Rule 2: Mastery / Improving Performance with no active gaps ──────
        $recommendations = [];
        $seenKeys = []; // To enforce deduplication: source_table . ':' . source_id

        // ── Rule 3: Targeted FSL Vocabulary Recommendations (from fsl_vocabulary) ──
        $this->matchFslVocabulary($difficultyItems, $analysis, $recommendations, $seenKeys);

        // ── Rule 4: Teacher-Designated Remedial Lesson (from lesson_plans) ────
        // A shared lesson or domain alone does not verify the specific skill.
        // Recommend only exact recorded item matches, using existing public resources.

        // ── Rule 5: Resource Hub Accessibility Packs (from resource_hub_packs) ─
        if ($difficultyItems) {
            $this->matchResourceHubPacks($activity, $iepLinks, $lessonCtx, $difficultyItems, $recommendations, $seenKeys);
        }

        // ── Rule 6: Supplementary Practice Activities (from lms_activities) ──

        // Sort by priority: high -> medium -> low
        usort($recommendations, function ($a, $b) {
            $rank = ($b['match_rank'] ?? 0) <=> ($a['match_rank'] ?? 0);
            if ($rank !== 0) return $rank;
            $pMap = [self::PRIORITY_HIGH => 1, self::PRIORITY_MEDIUM => 2, self::PRIORITY_LOW => 3];
            $priority = ($pMap[$a['priority']] ?? 99) <=> ($pMap[$b['priority']] ?? 99);
            return $priority !== 0 ? $priority : (($a['source_id'] ?? 0) <=> ($b['source_id'] ?? 0));
        });
        // Keep the strongest eligible preview for identical full Hub snapshots.
        // Different resources with the same title or skill remain separate suggestions.
        $unique=[]; $seenContent=[];
        foreach ($recommendations as $recommendation) {
            $fingerprint=$recommendation['_content_fingerprint']??null;
            if ($fingerprint!==null && isset($seenContent[$fingerprint])) continue;
            if ($fingerprint!==null) $seenContent[$fingerprint]=true;
            unset($recommendation['_content_fingerprint']);
            $unique[]=$recommendation;
        }
        $recommendations=$unique;

        $explanation = empty($recommendations)
            ? 'No existing FSL videos or published Resource Hub resources matched the latest recorded incorrect answers.'
            : sprintf(
                'Identified %d relevant resource(s) from existing SignED repositories to address observed performance patterns (latest accuracy: %s%%, trend: %s).',
                count($recommendations),
                $latestAcc !== null ? $latestAcc : 'N/A',
                $trendClass
            );

        return $this->buildResponse(
            $studentId,
            $activity,
            $summary,
            $iepLinks,
            $recommendations,
            $explanation,
            $factors,
            $analysis['limitations'] ?? []
        );
    }

    /**
     * Generate recommendations for all activities a learner has attempted.
     */
    public function recommendForStudent(int $studentId, ?int $lessonPlanId = null): array {
        $studentAnalyses = $this->analyzer->analyzeStudent($studentId, $lessonPlanId);
        $results = [];
        foreach ($studentAnalyses as $analysis) {
            $actId = (int)($analysis['activity']['id'] ?? 0);
            if ($actId > 0) {
                $results[] = $this->recommendForActivity($studentId, $actId, $analysis);
            }
        }
        return $results;
    }

    // ----------------------------------------------------------------
    // Matching Engines (Strict - Existing Repositories Only)
    // ----------------------------------------------------------------

    /**
     * Match recurring incorrect vocabulary items against verified fsl_vocabulary entries.
     */
    private function matchFslVocabulary(array $recurring, array $analysis, array &$recommendations, array &$seenKeys): void {
        foreach ($recurring as $item) {
            $expected = trim((string)($item['expected'] ?? ''));
            $unit = $item['unit'] ?? 'item';

            // Skip whole sequences since they are multi-sentence steps, not single vocabulary concepts
            if ($unit === 'sequence' || mb_strlen($expected) > 60 || empty($expected)) {
                continue;
            }

            // Look up exact or high-confidence match in fsl_vocabulary
            $fslRow = $this->findFslEntry($expected);
            if (!$fslRow || empty(trim((string)($fslRow['video_path'] ?? '')))) {
                continue;
            }

            $dedupKey = 'fsl_vocabulary:' . $fslRow['id'];
            if (isset($seenKeys[$dedupKey])) {
                continue;
            }
            $seenKeys[$dedupKey] = true;

            $wrongAnswers = array_keys($item['wrong_answers'] ?? []);
            $wrongAnswersStr = !empty($wrongAnswers) ? implode(', ', array_map(fn($w) => "\"$w\"", $wrongAnswers)) : 'incorrect answers';

            $recommendations[] = [
                'resource_type' => 'fsl_vocabulary',
                'source_table'  => 'fsl_vocabulary',
                'source_id'     => (int)$fslRow['id'],
                'lookup_word'   => $fslRow['word'],
                'match_rank'    => 80,
                'title'         => 'FSL Sign: ' . $fslRow['word'],
                'description'   => $fslRow['description'] ?: ('Filipino Sign Language demonstration for "' . $fslRow['word'] . '"'),
                'category'      => $fslRow['category'] ?? 'General',
                'media_url'     => !empty($fslRow['video_path']) ? $fslRow['video_path'] : null,
                'has_video'     => !empty($fslRow['video_path']),
                'domain'        => 'communication_language',
                'priority'      => self::PRIORITY_HIGH,
                'evidence'      => [
                    'gap_type'                  => 'recorded_incorrect_item',
                    'related_concept_or_skill'  => $expected,
                    'relevant_iep_objective'    => $this->getPrimaryIepText($analysis['iep_objectives'] ?? []),
                    'performance_evidence'      => sprintf(
                        'Learner answered question index %d incorrectly in %d attempt(s) (observed: %s; expected: "%s").',
                        $item['question_index'],
                        $item['incorrect_in_attempts'] ?? 1,
                        $wrongAnswersStr,
                        $expected
                    ),
                    'verified_source_reference' => sprintf(
                        'fsl_vocabulary#%d (Word: "%s", Category: "%s", Video: %s)',
                        $fslRow['id'],
                        $fslRow['word'],
                        $fslRow['category'] ?? 'General',
                        !empty($fslRow['video_path']) ? 'Available' : 'None'
                    ),
                ],
            ];
        }
    }

    /**
     * Find a verified row in fsl_vocabulary by exact (case-insensitive) word match.
     */
    private function findFslEntry(string $term): ?array {
        // A bilingual answer label is one concept, not an exact dictionary spelling.
        foreach (ResourceTagService::skills($term) as $label) {
            $row = $this->findExactFslEntry($label);
            if ($row && !empty(trim((string)($row['video_path'] ?? '')))) return $row;
        }
        return null;
    }

    private function findExactFslEntry(string $term): ?array {
        $clean = mb_strtolower(trim($term));
        if ($clean === '') {
            return null;
        }

        try {
            // 1. Exact match by word
            $stmt = $this->db->prepare("
                SELECT id, word, category, video_path, description 
                FROM fsl_vocabulary 
                WHERE LOWER(word) = :w 
                ORDER BY (CASE WHEN video_path IS NOT NULL AND TRIM(video_path) != '' THEN 0 ELSE 1 END) ASC
                LIMIT 1
            ");
            $stmt->execute(['w' => $clean]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return $row;
            }

            // 2. Exact match on primary Tagalog keyword before hyphen in description
            $stmtDesc = $this->db->prepare("
                SELECT id, word, category, video_path, description 
                FROM fsl_vocabulary 
                WHERE LOWER(description) LIKE :w_like
                ORDER BY (CASE WHEN video_path IS NOT NULL AND TRIM(video_path) != '' THEN 0 ELSE 1 END) ASC
                LIMIT 5
            ");
            $stmtDesc->execute(['w_like' => '%' . $clean . '%']);
            $candidates = $stmtDesc->fetchAll(PDO::FETCH_ASSOC);
            foreach ($candidates as $cand) {
                $descParts = explode('-', $cand['description'] ?? '');
                $tagalogWord = mb_strtolower(trim($descParts[0] ?? ''));
                if ($tagalogWord === $clean || in_array($clean, array_map('trim', explode('/', $tagalogWord)), true)) {
                    return $cand;
                }
            }
        } catch (\Throwable $e) {
            error_log('ResourceRecommendationService::findFslEntry error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Match teacher-designated remedial lesson plans for the parent lesson plan.
     */
    private function matchRemedialLessons(array $activity, ?float $latestAcc, int $threshold, array $analysis, array &$recommendations, array &$seenKeys): void {
        $parentLpId = (int)($activity['lesson_plan_id'] ?? 0);
        if ($parentLpId <= 0) {
            return;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT id, title, pdsp_domain, score_threshold, mainstream_mastery_title, status
                FROM lesson_plans
                WHERE is_remedial = 1 
                  AND remedial_for_lesson_id = :parent_id 
                  AND status = 'published'
                ORDER BY id ASC
                LIMIT 3
            ");
            $stmt->execute(['parent_id' => $parentLpId]);
            $remedials = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($remedials as $rem) {
                $dedupKey = 'lesson_plans:' . $rem['id'];
                if (isset($seenKeys[$dedupKey])) {
                    continue;
                }
                $seenKeys[$dedupKey] = true;

                $recommendations[] = [
                    'resource_type' => 'remedial_lesson',
                    'source_table'  => 'lesson_plans',
                    'source_id'     => (int)$rem['id'],
                    'title'         => 'Remedial Module: ' . $rem['title'],
                    'description'   => 'Teacher-configured adaptive remedial lesson for lesson plan #' . $parentLpId,
                    'category'      => 'Adaptive Remediation',
                    'media_url'     => null,
                    'has_video'     => false,
                    'domain'        => $rem['pdsp_domain'],
                    'priority'      => self::PRIORITY_HIGH,
                    'evidence'      => [
                        'gap_type'                  => 'lesson_threshold_shortfall',
                        'related_concept_or_skill'  => 'Curriculum remediation for ' . ($activity['title'] ?? 'activity'),
                        'relevant_iep_objective'    => $this->getPrimaryIepText($analysis['iep_objectives'] ?? []),
                        'performance_evidence'      => sprintf(
                            'Learner accuracy (%s%%) is below the lesson plan score threshold (%d%%).',
                            $latestAcc !== null ? $latestAcc : '0',
                            $threshold
                        ),
                        'verified_source_reference' => 'lesson_plans#' . $rem['id'] . ' (remedial_for_lesson_id=' . $parentLpId . ')',
                    ],
                ];
            }
        } catch (\Throwable $e) {
            error_log('ResourceRecommendationService::matchRemedialLessons error: ' . $e->getMessage());
        }
    }

    /** Match specific recorded skills to published packs, never domain alone. */
    private function matchResourceHubPacks(array $activity, array $iepLinks, array $lessonCtx, array $incorrectItems, array &$recommendations, array &$seenKeys): void {
        // Only the valid, incomplete latest result reaches this method.
        // Prioritize teacher-authored competency and verified IEP context, then item concepts.
        $evidence = [];
        $addSkills = function (string $text, string $source) use (&$evidence): void {
            foreach (ResourceTagService::skills($text) as $skill) $evidence[$skill] = $evidence[$skill] ?? $source;
        };
        $addSkills($activity['competency'] ?? '', 'activity competency');
        $itemSkills = [];
        foreach ($incorrectItems as $item) $itemSkills = array_merge($itemSkills, ResourceTagService::skills((string)($item['expected'] ?? '')));
        // Numerical/short answer labels cannot identify a skill by themselves.
        if (!$evidence && !$itemSkills) $addSkills($activity['title'] ?? '', 'assessed activity');
        $contextSkills = array_keys($evidence);
        foreach ($iepLinks as $link) {
            if (empty($link['verified'])) continue;
            foreach (ResourceTagService::skills($link['objective'] ?? '') as $skill) {
                if (!$contextSkills || in_array($skill, $contextSkills, true)) {
                    $evidence[$skill] = $evidence[$skill] ?? 'linked teacher-defined objective';
                }
            }
        }
        foreach ($incorrectItems as $item) $addSkills((string)($item['expected'] ?? ''), 'latest incorrect answer');
        $skills = array_keys($evidence);
        if (!$skills) return;
        try {
            $stmt = $this->db->prepare("SELECT id, title, description, pdsp_domain, learning_level, learning_competency, tags, fsl_supported, status
                FROM resource_hub_packs WHERE status = 'published'
                AND id > 0 AND TRIM(title) <> ''
                AND (EXISTS (SELECT 1 FROM resource_hub_pages rp WHERE rp.pack_id = resource_hub_packs.id)
                     OR EXISTS (SELECT 1 FROM resource_hub_activities ra WHERE ra.pack_id = resource_hub_packs.id))
                ORDER BY fsl_supported DESC, clone_count DESC, id ASC");
            $stmt->execute();
            $packs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pageStmt = $this->db->prepare("SELECT rp.id, rp.pack_id, rp.title, rp.content, rp.guide_questions, rp.media_type, rp.media_path
                FROM resource_hub_pages rp JOIN resource_hub_packs p ON p.id = rp.pack_id WHERE p.status = 'published'
                ORDER BY rp.pack_id, rp.page_number, rp.id");
            $pageStmt->execute();
            $pagesByPack = [];
            foreach ($pageStmt->fetchAll(PDO::FETCH_ASSOC) as $row) $pagesByPack[(int)$row['pack_id']][] = $row;
            $activityStmt = $this->db->prepare("SELECT ra.id, ra.pack_id, ra.title, ra.activity_type, ra.activity_data, ra.instructions, ra.max_score, ra.is_f2f
                FROM resource_hub_activities ra JOIN resource_hub_packs p ON p.id = ra.pack_id WHERE p.status = 'published'
                ORDER BY ra.pack_id, ra.display_order, ra.id");
            $activityStmt->execute();
            $activitiesByPack = [];
            foreach ($activityStmt->fetchAll(PDO::FETCH_ASSOC) as $row) $activitiesByPack[(int)$row['pack_id']][] = $row;
            foreach ($packs as $pack) {
                if ((int)($pack['id'] ?? 0) <= 0 || trim($pack['title'] ?? '') === '' || ($pack['status'] ?? '') !== 'published') continue;
                // Do not substitute a different explicitly stated numerical range.
                $contextText = ($activity['competency'] ?? '') . ' ' . ($activity['title'] ?? '');
                foreach ($iepLinks as $link) {
                    if (!empty($link['verified'])) $contextText .= ' ' . ($link['objective'] ?? '');
                }
                $resourceText = ($pack['learning_competency'] ?? '') . ' ' . ($pack['title'] ?? '') . ' ' . ($pack['tags'] ?? '');
                if (preg_match('/\b(?:within|up to)\s+(\d+)\b/i', $contextText, $contextRange)
                    && preg_match('/\b(?:within|up to)\s+(\d+)\b/i', $resourceText, $resourceRange)
                    && $contextRange[1] !== $resourceRange[1]) continue;
                $match = ResourceHubContentMatcher::rank($pack, $pagesByPack[(int)$pack['id']] ?? [], $activitiesByPack[(int)$pack['id']] ?? [], $incorrectItems, $skills);
                $key = 'resource_hub_packs:' . $pack['id'];
                if (!$match || isset($seenKeys[$key])) continue;
                $seenKeys[$key] = true;
                $recommendations[] = [
                    'resource_type' => 'resource_hub_pack', 'source_table' => 'resource_hub_packs',
                    'source_id' => (int)$pack['id'], 'title' => $pack['title'],
                    'description' => $pack['description'] ?? '', 'category' => $pack['learning_level'] ?? '',
                    'media_url' => null, 'has_video' => false, 'domain' => $pack['pdsp_domain'],
                    'priority' => self::PRIORITY_MEDIUM,
                    'match_rank' => $match['rank'],
                    '_content_fingerprint' => ResourceHubContentMatcher::fingerprint($pagesByPack[(int)$pack['id']]??[], $activitiesByPack[(int)$pack['id']]??[]),
                    'evidence' => [
                        'gap_type' => 'recorded_skill_match',
                        'related_concept_or_skill' => $match['skill'],
                        'relevant_iep_objective' => $this->getPrimaryIepText($iepLinks),
                        'performance_evidence' => $match['reason'] . ' Teacher review is required.',
                        'verified_source_reference' => $match['source'] . ' in resource_hub_packs#' . $pack['id'],
                    ],
                ];
            }
        } catch (\Throwable $e) {
            error_log('ResourceRecommendationService::matchResourceHubPacks error: ' . $e->getMessage());
        }
    }
    /**
     * Match published supplementary activities in the same lesson or domain.
     */
    private function matchSupplementaryActivities(array $activity, ?float $latestAcc, int $threshold, array &$recommendations, array &$seenKeys): void {
        $actId = (int)($activity['id'] ?? 0);
        $lpId  = (int)($activity['lesson_plan_id'] ?? 0);
        if ($actId <= 0 || $lpId <= 0 || ($latestAcc !== null && $latestAcc >= $threshold)) {
            return;
        }

        try {
            // Check other published activities in the same lesson plan
            $stmt = $this->db->prepare("
                SELECT id, title, activity_type, max_score
                FROM lms_activities
                WHERE lesson_plan_id = :lid AND id != :aid
                ORDER BY display_order ASC
                LIMIT 2
            ");
            $stmt->execute(['lid' => $lpId, 'aid' => $actId]);
            $suppActs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($suppActs as $sa) {
                $dedupKey = 'lms_activities:' . $sa['id'];
                if (isset($seenKeys[$dedupKey])) {
                    continue;
                }
                $seenKeys[$dedupKey] = true;

                $recommendations[] = [
                    'resource_type' => 'supplementary_activity',
                    'source_table'  => 'lms_activities',
                    'source_id'     => (int)$sa['id'],
                    'title'         => 'Practice Activity: ' . $sa['title'],
                    'description'   => 'Alternative format activity (' . $sa['activity_type'] . ') within the same lesson module.',
                    'category'      => 'Practice & Reinforcement',
                    'media_url'     => null,
                    'has_video'     => false,
                    'domain'        => null,
                    'priority'      => self::PRIORITY_MEDIUM,
                    'evidence'      => [
                        'gap_type'                  => 'alternative_practice_modality',
                        'related_concept_or_skill'  => $sa['title'],
                        'relevant_iep_objective'    => null,
                        'performance_evidence'      => sprintf('Reinforces lesson concepts via alternative format (%s) following low score (%s%%).', $sa['activity_type'], $latestAcc ?? '0'),
                        'verified_source_reference' => 'lms_activities#' . $sa['id'] . ' (Type: ' . $sa['activity_type'] . ')',
                    ],
                ];
            }
        } catch (\Throwable $e) {
            error_log('ResourceRecommendationService::matchSupplementaryActivities error: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Normalize domain string to match resource_hub_packs pdsp_domain enum.
     */
    public static function normalizeDomain(?string $domain): ?string {
        if (!$domain) {
            return null;
        }
        $clean = mb_strtolower(trim($domain));
        $clean = str_replace(['&', '/', '-'], ' ', $clean);
        $clean = preg_replace('/\s+/', '_', $clean);
        $clean = trim($clean, '_');

        $map = [
            'daily_living_skills'    => 'daily_living_skills',
            'daily_living'           => 'daily_living_skills',
            'perceptuo_cognitive'    => 'perceptuo_cognitive',
            'perceptuo'              => 'perceptuo_cognitive',
            'cognitive'              => 'perceptuo_cognitive',
            'socio_emotional'        => 'socio_emotional',
            'socio'                  => 'socio_emotional',
            'emotional'              => 'socio_emotional',
            'communication_language' => 'communication_language',
            'communication'          => 'communication_language',
            'language'               => 'communication_language',
            'psychomotor'            => 'psychomotor',
            'motor'                  => 'psychomotor',
            'psychosocial'           => 'psychosocial',
            'social'                 => 'psychosocial',
        ];

        return $map[$clean] ?? null;
    }

    /**
     * Extract meaningful keyword tokens from phrases for tag/title matching.
     */
    private function extractKeywords(array $phrases): array {
        $stopWords = [
            'the','and','for','with','within','this','that','from','into','about','using','basic',
            'lesson','plan','activity','test','quiz','module','ang','mga','sa','ng','at','o',
            'aralin','para','na','si','ni','kay','ay','may','isa','dalawa'
        ];
        $tokens = [];
        foreach ($phrases as $p) {
            $words = preg_split('/[\s,\.\:\;\(\)\-\/]+/', mb_strtolower($p));
            foreach ($words as $w) {
                $w = trim($w);
                if (mb_strlen($w) >= 3 && !in_array($w, $stopWords, true)) {
                    $tokens[$w] = true;
                }
            }
        }
        return array_keys($tokens);
    }

    /**
     * Score pack relevance against extracted keywords.
     */
    private function scorePackRelevance(array $pack, array $keywords): int {
        if (empty($keywords)) {
            return 0;
        }
        $haystack = implode(' ', [
            $pack['title'] ?? '',
            '' ,
            $pack['tags'] ?? '',
            $pack['learning_competency'] ?? '',
        ]);
        $resourceSkills = ResourceTagService::skills($haystack);
        foreach (explode(',', $pack['tags'] ?? '') as $tag) {
            $resourceSkills = array_merge($resourceSkills, ResourceTagService::skills($tag));
        }

        $score = 0;
        foreach ($keywords as $kw) {
            if (in_array($kw, $resourceSkills, true) || ResourceTagService::contains($haystack, $kw)) {
                $score++;
            }
        }
        return $score;
    }

    /**
     * Return formatted text of primary verified IEP objective, if any.
     */
    private function getPrimaryIepText(array $iepObjectives): ?string {
        foreach ($iepObjectives as $obj) {
            if (!empty($obj['verified'])) {
                return sprintf(
                    'Step #%d: %s (Indicator: %s)',
                    $obj['step_number'] ?? 1,
                    $obj['objective'] ?? 'Objective',
                    $obj['indicator'] ?? 'N/A'
                );
            }
        }
        return null;
    }

    /**
     * Assemble final structured, immutable response envelope.
     */
    private function buildResponse(int $studentId, array $activity, array $summary, array $iepLinks, array $recommendations, string $explanation, array $factors = [], array $limitations = []): array {
        return [
            'student_id'            => $studentId,
            'activity'              => [
                'id'             => (int)($activity['id'] ?? 0),
                'title'          => $activity['title'] ?? '',
                'type'           => $activity['type'] ?? '',
                'lesson_plan_id' => (int)($activity['lesson_plan_id'] ?? 0),
            ],
            'performance_summary'   => $summary,
            'suggested_rating' => LearningGapAnalyzer::suggestedRating($summary['accuracy'] ?? null),
            'intervention' => InterventionDraftService::fromFactors($activity, $factors),
            'observation' => LearningGapAnalyzer::observation($factors),
            'factors'               => $factors,
            'limitations'           => $limitations,
            'iep_alignment'         => [
                'has_verified_iep' => (bool)count(array_filter($iepLinks, fn($l) => !empty($l['verified']))),
                'objectives'       => $iepLinks,
            ],
            'recommendations'       => $recommendations,
            'recommendations_count' => count($recommendations),
            'explanation'           => $explanation,
            'advisory_notice'       => 'Recommendations are advisory for SPED teacher review only. No lessons have been assigned, no IEP records altered, and no grades modified.',
            'generated_at'          => date('Y-m-d H:i:s'),
        ];
    }
}
