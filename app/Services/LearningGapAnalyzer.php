<?php
// Part of: SignED — Learning Gap Innovation (Process 10.1), Phase 1
// Four-Factor Learning Gap Analyzer: Accuracy, Attempts, Consistency, Performance Trend.
//
// READ-ONLY service. It never writes to the database and never declares IEP mastery.
// Observations are evidence summaries for SPED teacher review only.
//
// Data sources (verified v68 architecture):
//   - lms_activity_attempts        : one row per learner attempt (authoritative history)
//   - activity_attempt_log         : item-level results linked by exact attempt_id
//   - iep_step_lesson_plans        : teacher-defined IEP objective <-> lesson plan link
//   - lesson_plans.score_threshold : lesson context only (NOT an IEP indicator)

require_once __DIR__ . '/../../config/db.php';

class LearningGapAnalyzer {
    public const TREND_IMPROVING    = 'Improving';
    public const TREND_STABLE       = 'Stable';
    public const TREND_DECLINING    = 'Declining';
    public const INSUFFICIENT       = 'Insufficient Evidence';

    public const CONSISTENCY_STABLE   = 'Stable';
    public const CONSISTENCY_VARIABLE = 'Inconsistent';

    /** Activity types whose log row represents a whole evaluated sequence, not individual items. */
    private const SEQUENCE_LEVEL_TYPES = ['drag_drop_sort', 'sequencing'];

    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    // ----------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------

    /**
     * Analyze every activity the learner has attempted (optionally within one lesson plan).
     */
    public function analyzeStudent(int $studentId, ?int $lessonPlanId = null): array {
        $sql = "SELECT DISTINCT a.activity_id
                FROM lms_activity_attempts a
                JOIN lms_activities la ON la.id = a.activity_id
                WHERE a.student_id = :s" . ($lessonPlanId ? " AND la.lesson_plan_id = :lp" : "") . "
                ORDER BY a.activity_id ASC";
        $stmt = $this->db->prepare($sql);
        $params = ['s' => $studentId];
        if ($lessonPlanId) $params['lp'] = $lessonPlanId;
        $stmt->execute($params);

        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $activityId) {
            $results[] = $this->analyzeActivity($studentId, (int)$activityId);
        }
        return $results;
    }

    /**
     * Four-factor analysis for one learner on one activity.
     */
    public function analyzeActivity(int $studentId, int $activityId): array {
        $activity = $this->fetchActivity($activityId);
        if (!$activity) {
            return ['error' => 'Activity not found.', 'activity_id' => $activityId, 'student_id' => $studentId];
        }

        $attempts    = $this->fetchAttempts($studentId, $activityId);
        $logsByAtt   = $this->fetchLogsByAttempt(array_column($attempts, 'id'));
        $limitations = [];

        // ── Factor 2: Attempts ─────────────────────────────────────
        $graded   = [];
        $ungraded = 0;
        foreach ($attempts as $att) {
            $acc = $this->attemptAccuracy($att);
            if ($acc === null) { $ungraded++; continue; }
            $att['_accuracy'] = $acc;
            $graded[] = $att;
        }
        if ($ungraded > 0) {
            $limitations[] = "{$ungraded} attempt(s) have no auto-score (ungraded or NULL score); treated as insufficient evidence, not poor performance.";
        }

        // ── Comparability: same max score and same verified item set as latest graded attempt ──
        [$comparable, $excludedCount] = $this->comparableAttempts($graded, $logsByAtt);
        if ($excludedCount > 0) {
            $limitations[] = "{$excludedCount} graded attempt(s) excluded from trend/consistency because the max score or item set differs from the latest attempt (activity likely modified).";
        }

        // ── Factor 1: Accuracy ─────────────────────────────────────
        $accuracyHistory = array_map(fn($a) => [
            'attempt_id'     => (int)$a['id'],
            'attempt_number' => (int)$a['attempt_number'],
            'score'          => $a['score'] !== null ? (int)$a['score'] : null,
            'max_score'      => $a['max_score'] !== null ? (int)$a['max_score'] : null,
            'accuracy'       => $this->attemptAccuracy($a),
            'graded'         => $this->attemptAccuracy($a) !== null,
            'comparable'     => in_array((int)$a['id'], array_map(fn($c) => (int)$c['id'], $comparable), true),
            'attempted_at'   => $a['attempted_at'],
        ], $attempts);

        $latestGraded   = $graded ? end($graded) : null;
        $latestAccuracy = $latestGraded ? $latestGraded['_accuracy'] : null;

        // Smallest detectable change = one raw point of the activity's max score (data-derived, not universal).
        $maxScoreRef  = $latestGraded ? (int)$latestGraded['max_score'] : (int)($activity['max_score'] ?? 0);
        $minDetectable = $maxScoreRef > 0 ? round(100 / $maxScoreRef, 2) : null;

        // ── Factor 4: Performance Trend ────────────────────────────
        $trend = $this->computeTrend($comparable, $minDetectable);

        // ── Factor 3: Consistency + recurring incorrect items ──────
        $consistency = $this->computeConsistency($comparable, $minDetectable);
        $itemFindings = $this->computeRecurringItems($activity, $comparable, $logsByAtt, $limitations);

        // ── IEP linkage (verified only) ────────────────────────────
        $iepLinks = $this->fetchIepLinks((int)$activity['lesson_plan_id'], (int)($activity['lp_iep_id'] ?? 0), $limitations);

        // ── Lesson context (never a mastery criterion) ─────────────
        $lessonThreshold = isset($activity['score_threshold']) ? (int)$activity['score_threshold'] : null;
        $lessonContext = [
            'lesson_plan_id'         => (int)$activity['lesson_plan_id'],
            'lesson_title'           => $activity['lesson_title'],
            'lesson_score_threshold' => $lessonThreshold,
            'latest_below_lesson_threshold' => ($latestAccuracy !== null && $lessonThreshold)
                ? ($latestAccuracy < $lessonThreshold) : null,
            'note' => 'Lesson threshold is contextual only; it is not a teacher-defined IEP indicator.',
        ];

        if (count($comparable) < 2) {
            $limitations[] = 'Fewer than two comparable graded attempts; no performance trend can be established.';
        }
        if (in_array($activity['activity_type'], self::SEQUENCE_LEVEL_TYPES, true)) {
            $limitations[] = 'Sorting/sequencing is scored as a whole sequence; per-item correctness is not available.';
        }
        $legacyUnlinked = $this->countLegacyUnlinkedLogs($studentId, $activityId);
        if ($legacyUnlinked > 0) {
            $limitations[] = "{$legacyUnlinked} legacy item log(s) without attempt_id were excluded (no exact attempt linkage).";
        }
        $limitations[] = 'Question identity is verified by matching question index AND expected value across attempts; lms_activities has no version history.';

        $teacherReview = $this->teacherReviewStatus($trend, $consistency, $itemFindings, $iepLinks, $lessonContext, count($graded));

        return [
            'student_id'     => $studentId,
            'activity'       => [
                'id'             => (int)$activity['id'],
                'title'          => $activity['title'],
                'type'           => $activity['activity_type'],
                'max_score'      => $activity['max_score'] !== null ? (int)$activity['max_score'] : null,
                'lesson_plan_id' => (int)$activity['lesson_plan_id'],
            ],
            'iep_objectives' => $iepLinks,
            'factors' => [
                'accuracy' => [
                    'latest_accuracy'  => $latestAccuracy,
                    'accuracy_history' => $accuracyHistory,
                ],
                'attempts' => [
                    'total_recorded'   => count($attempts),
                    'graded'           => count($graded),
                    'ungraded'         => $ungraded,
                    'comparable'       => count($comparable),
                ],
                'consistency' => $consistency + [
                    'recurring_incorrect_items' => $itemFindings['recurring'],
                    'resolved_items'            => $itemFindings['resolved'],
                    'identity_status'           => $itemFindings['identity_status'],
                ],
                'performance_trend' => $trend,
            ],
            'lesson_context'   => $lessonContext,
            'limitations'      => array_values(array_unique($limitations)),
            'teacher_review'   => $teacherReview,
            'mastery_decision' => 'Not determined by the system. IEP mastery is a SPED teacher decision.',
            'generated_at'     => date('Y-m-d H:i:s'),
        ];
    }

    // ----------------------------------------------------------------
    // Data access (read-only)
    // ----------------------------------------------------------------

    private function fetchActivity(int $activityId): ?array {
        $stmt = $this->db->prepare("
            SELECT la.id, la.title, la.activity_type, la.activity_data, la.max_score, la.lesson_plan_id,
                   lp.title AS lesson_title, lp.score_threshold, lp.iep_id AS lp_iep_id
            FROM lms_activities la
            LEFT JOIN lesson_plans lp ON lp.id = la.lesson_plan_id
            WHERE la.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $activityId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchAttempts(int $studentId, int $activityId): array {
        $stmt = $this->db->prepare("
            SELECT id, attempt_number, score, max_score, accuracy_percentage,
                   is_remedial_attempt, needs_remediation, attempted_at
            FROM lms_activity_attempts
            WHERE student_id = :s AND activity_id = :a
            ORDER BY attempt_number ASC, id ASC
        ");
        $stmt->execute(['s' => $studentId, 'a' => $activityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Item logs keyed by exact attempt_id. Never matches by timestamp. */
    private function fetchLogsByAttempt(array $attemptIds): array {
        if (empty($attemptIds)) return [];
        $in = implode(',', array_fill(0, count($attemptIds), '?'));
        $stmt = $this->db->prepare("
            SELECT attempt_id, question_index, selected_value, correct_value, is_correct
            FROM activity_attempt_log
            WHERE attempt_id IN ($in)
            ORDER BY attempt_id ASC, question_index ASC, id ASC
        ");
        $stmt->execute(array_map('intval', $attemptIds));
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(int)$row['attempt_id']][] = $row;
        }
        return $out;
    }

    private function countLegacyUnlinkedLogs(int $studentId, int $activityId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM activity_attempt_log WHERE student_id = :s AND activity_id = :a AND attempt_id IS NULL");
        $stmt->execute(['s' => $studentId, 'a' => $activityId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Verified IEP objective links: lesson plan must be linked through iep_step_lesson_plans
     * AND the step must belong to the same IEP as the lesson plan.
     */
    private function fetchIepLinks(int $lessonPlanId, int $lpIepId, array &$limitations): array {
        $stmt = $this->db->prepare("
            SELECT s.id AS iep_step_id, s.iep_id, s.step_number, s.step_domain,
                   s.step_objective, s.pdsp_indicator_text, s.instructional_evaluation
            FROM iep_step_lesson_plans l
            JOIN iep_steps s ON s.id = l.iep_step_id
            WHERE l.lesson_plan_id = :lp
            ORDER BY s.step_number ASC
        ");
        $stmt->execute(['lp' => $lessonPlanId]);
        $links = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $verified = $lpIepId > 0 && (int)$row['iep_id'] === $lpIepId;
            if (!$verified) {
                $limitations[] = "IEP step #{$row['iep_step_id']} is linked but belongs to a different IEP than the lesson plan; not treated as verified.";
            }
            $links[] = [
                'iep_step_id'        => (int)$row['iep_step_id'],
                'iep_id'             => (int)$row['iep_id'],
                'step_number'        => (int)$row['step_number'],
                'domain'             => $row['step_domain'],
                'objective'          => $row['step_objective'],
                'indicator'          => $row['pdsp_indicator_text'],
                'evaluation_method'  => $row['instructional_evaluation'],
                'verified'           => $verified,
            ];
        }
        if (empty($links)) {
            $limitations[] = 'Activity lesson plan is not linked to any teacher-defined IEP objective.';
        }
        return $links;
    }

    // ----------------------------------------------------------------
    // Factor computations
    // ----------------------------------------------------------------

    /** Accuracy for a graded attempt; NULL when ungraded. */
    private function attemptAccuracy(array $att): ?float {
        if ($att['score'] === null || $att['max_score'] === null || (int)$att['max_score'] <= 0) {
            return null;
        }
        if ($att['accuracy_percentage'] !== null) {
            return round((float)$att['accuracy_percentage'], 2);
        }
        return round(((int)$att['score'] / (int)$att['max_score']) * 100, 2);
    }

    /** Fingerprint of an attempt's item set: question_index + expected value. */
    private function itemFingerprint(array $logs): ?string {
        if (empty($logs)) return null;
        $parts = [];
        foreach ($logs as $l) {
            $parts[] = (int)$l['question_index'] . '=' . mb_strtolower(trim((string)$l['correct_value']));
        }
        sort($parts);
        return md5(implode('|', $parts));
    }

    /**
     * Attempts comparable to the latest graded attempt: same max_score and, when both have
     * item logs, the same item fingerprint.
     */
    private function comparableAttempts(array $graded, array $logsByAtt): array {
        if (empty($graded)) return [[], 0];
        $ref   = end($graded);
        $refMax = (int)$ref['max_score'];
        $refFp  = $this->itemFingerprint($logsByAtt[(int)$ref['id']] ?? []);

        $comparable = [];
        foreach ($graded as $att) {
            if ((int)$att['max_score'] !== $refMax) continue;
            $fp = $this->itemFingerprint($logsByAtt[(int)$att['id']] ?? []);
            if ($refFp !== null && $fp !== null && $fp !== $refFp) continue;
            $comparable[] = $att;
        }
        return [$comparable, count($graded) - count($comparable)];
    }

    private function computeTrend(array $comparable, ?float $minDetectable): array {
        $series = array_map(fn($a) => (float)$a['_accuracy'], $comparable);
        $n = count($series);

        if ($n < 2) {
            return [
                'classification' => self::INSUFFICIENT,
                'reason'         => $n === 1 ? 'Only one comparable graded attempt; accuracy reported without a trend.' : 'No comparable graded attempts.',
                'series'         => $series,
            ];
        }

        $first = $series[0];
        $last  = $series[$n - 1];
        $delta = round($last - $first, 2);

        // Least-squares slope (accuracy points per attempt) when 3+ attempts are available.
        $slope = null;
        if ($n >= 3) {
            $xMean = ($n - 1) / 2;
            $yMean = array_sum($series) / $n;
            $num = 0.0; $den = 0.0;
            foreach ($series as $i => $y) {
                $num += ($i - $xMean) * ($y - $yMean);
                $den += ($i - $xMean) ** 2;
            }
            $slope = $den > 0 ? round($num / $den, 2) : 0.0;
        }

        // Direction based on first->last change, confirmed by slope when available.
        $tol = $minDetectable ?? 0.0;
        $signal = $slope !== null ? $slope * ($n - 1) : $delta; // total fitted change
        if (abs($delta) < $tol && abs($signal) < $tol) {
            $class = self::TREND_STABLE;
        } elseif ($delta > 0 && $signal > 0) {
            $class = self::TREND_IMPROVING;
        } elseif ($delta < 0 && $signal < 0) {
            $class = self::TREND_DECLINING;
        } else {
            // Endpoints and overall fit disagree: no clear direction.
            $class = self::TREND_STABLE;
        }

        return [
            'classification'      => $class,
            'first_accuracy'      => $first,
            'latest_accuracy'     => $last,
            'change_points'       => $delta,
            'slope_per_attempt'   => $slope,
            'min_detectable_change' => $minDetectable,
            'series'              => $series,
            'reason'              => sprintf('%d comparable attempts: %s%% → %s%% (change %+.2f points).', $n, $first, $last, $delta),
        ];
    }

    private function computeConsistency(array $comparable, ?float $minDetectable): array {
        $series = array_map(fn($a) => (float)$a['_accuracy'], $comparable);
        $n = count($series);
        if ($n < 2) {
            return [
                'classification' => self::INSUFFICIENT,
                'reason'         => 'At least two comparable graded attempts are required to judge consistency.',
            ];
        }
        $mean = array_sum($series) / $n;
        $var  = 0.0;
        foreach ($series as $y) $var += ($y - $mean) ** 2;
        $stdDev = round(sqrt($var / $n), 2);
        $range  = round(max($series) - min($series), 2);

        // Direction reversals (up then down or vice versa), ignoring sub-detectable changes.
        $tol = $minDetectable ?? 0.0;
        $reversals = 0; $prevDir = 0;
        for ($i = 1; $i < $n; $i++) {
            $d = $series[$i] - $series[$i - 1];
            $dir = abs($d) < $tol ? 0 : ($d > 0 ? 1 : -1);
            if ($dir !== 0) {
                if ($prevDir !== 0 && $dir !== $prevDir) $reversals++;
                $prevDir = $dir;
            }
        }

        if ($range < $tol) {
            $class  = self::CONSISTENCY_STABLE;
            $reason = 'Results are effectively identical across attempts.';
        } elseif ($n < 3) {
            // Two differing results cannot reveal reversals; avoid claiming stability.
            $class  = self::INSUFFICIENT;
            $reason = "Two comparable attempts differ by {$range} points; at least three are needed to judge consistency.";
        } elseif ($reversals === 0) {
            $class  = self::CONSISTENCY_STABLE;
            $reason = 'Results move in one direction without reversals.';
        } else {
            $class  = self::CONSISTENCY_VARIABLE;
            $reason = "Results fluctuate ({$reversals} direction reversal(s), range {$range} points).";
        }

        return [
            'classification'  => $class,
            'std_dev'         => $stdDev,
            'range_points'    => $range,
            'reversals'       => $reversals,
            'reason'          => $reason,
        ];
    }

    /**
     * Recurring incorrect items across comparable attempts. Items are identified by
     * question_index + expected value; indexes whose expected value changed are excluded.
     */
    private function computeRecurringItems(array $activity, array $comparable, array $logsByAtt, array &$limitations): array {
        $isSequence = in_array($activity['activity_type'], self::SEQUENCE_LEVEL_TYPES, true);
        $attIds = array_map(fn($a) => (int)$a['id'], $comparable);
        $withLogs = array_values(array_filter($attIds, fn($id) => !empty($logsByAtt[$id])));

        if (empty($withLogs)) {
            return [
                'recurring'       => [],
                'resolved'        => [],
                'identity_status' => 'unavailable',
            ];
        }
        if (count($withLogs) < count($attIds)) {
            $limitations[] = (count($attIds) - count($withLogs)) . ' comparable attempt(s) have no item-level logs.';
        }

        // Verify identity per question_index: the expected value must be identical in every attempt.
        $expectedByQi = [];
        foreach ($withLogs as $id) {
            foreach ($logsByAtt[$id] as $l) {
                $expectedByQi[(int)$l['question_index']][mb_strtolower(trim((string)$l['correct_value']))] = true;
            }
        }
        $conflicts = array_keys(array_filter($expectedByQi, fn($v) => count($v) > 1));
        if ($conflicts) {
            $limitations[] = 'Question index(es) ' . implode(', ', $conflicts) . ' had different expected values across attempts; excluded from recurring-error analysis.';
        }

        $items = [];
        $latestId = end($withLogs);
        foreach ($withLogs as $id) {
            foreach ($logsByAtt[$id] as $l) {
                $qi = (int)$l['question_index'];
                if (in_array($qi, $conflicts, true)) continue;
                $items[$qi] ??= [
                    'question_index' => $qi,
                    'unit'           => $isSequence ? 'sequence' : 'item',
                    'expected'       => (string)$l['correct_value'],
                    'incorrect_count'=> 0,
                    'observed_count' => 0,
                    'wrong_answers'  => [],
                    'latest_correct' => null,
                ];
                $items[$qi]['observed_count']++;
                if (!(int)$l['is_correct']) {
                    $items[$qi]['incorrect_count']++;
                    $sel = $this->cleanSelected((string)$l['selected_value']);
                    $items[$qi]['wrong_answers'][$sel] = ($items[$qi]['wrong_answers'][$sel] ?? 0) + 1;
                }
                if ($id === $latestId) $items[$qi]['latest_correct'] = (bool)(int)$l['is_correct'];
            }
        }

        $recurring = []; $resolved = [];
        foreach ($items as $it) {
            arsort($it['wrong_answers']);
            $repeatedWrong = array_keys(array_filter($it['wrong_answers'], fn($c) => $c >= 2));
            $row = [
                'question_index'        => $it['question_index'],
                'unit'                  => $it['unit'],
                'expected'              => $it['expected'],
                'incorrect_in_attempts' => $it['incorrect_count'],
                'observed_in_attempts'  => $it['observed_count'],
                'wrong_answers'         => $it['wrong_answers'],
                'same_wrong_answer_repeated' => $repeatedWrong,
                'still_incorrect_latest'=> $it['latest_correct'] === false,
                'fsl_hint'              => $isSequence ? null : $this->fslHint($it['expected']),
            ];
            if ($it['incorrect_count'] >= 2) {
                $recurring[] = $row;
            } elseif ($it['incorrect_count'] >= 1 && $it['latest_correct'] === true) {
                $resolved[] = $row;
            }
        }

        return [
            'recurring'       => $recurring,
            'resolved'        => $resolved,
            'identity_status' => $conflicts ? 'partially_verified' : 'verified',
        ];
    }

    /** Remove the client-side "(Attempts: N)" suffix appended by the submission logger. */
    private function cleanSelected(string $v): string {
        return trim(preg_replace('/\s*\(Attempts:\s*\d+\)\s*$/', '', $v));
    }

    /** FSL hint only on an exact (case-insensitive) dictionary match — no fuzzy guessing. */
    private function fslHint(string $expected): ?array {
        $word = mb_strtolower(trim($expected));
        if ($word === '' || mb_strlen($word) > 100) return null;
        try {
            $stmt = $this->db->prepare("SELECT id, word, video_path FROM fsl_vocabulary WHERE LOWER(word) = :w LIMIT 1");
            $stmt->execute(['w' => $word]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? ['fsl_id' => (int)$row['id'], 'word' => $row['word'], 'video_path' => $row['video_path'], 'match' => 'exact'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function teacherReviewStatus(array $trend, array $consistency, array $items, array $iepLinks, array $lessonContext, int $gradedCount): array {
        $reasons = [];
        if ($gradedCount === 0) {
            $reasons[] = 'No graded evidence; teacher observation required.';
        }
        if ($trend['classification'] === self::TREND_DECLINING) {
            $reasons[] = 'Performance trend is declining across comparable attempts.';
        }
        if (($consistency['classification'] ?? null) === self::CONSISTENCY_VARIABLE) {
            $reasons[] = 'Results are inconsistent across attempts.';
        }
        if (!empty($items['recurring'])) {
            $reasons[] = count($items['recurring']) . ' item(s) answered incorrectly in more than one attempt.';
        }
        if ($lessonContext['latest_below_lesson_threshold'] === true) {
            $reasons[] = 'Latest accuracy is below the lesson score threshold (context only).';
        }
        $verifiedIep = array_filter($iepLinks, fn($l) => $l['verified']);
        if ($verifiedIep && $trend['classification'] === self::INSUFFICIENT) {
            $reasons[] = 'Activity supports a teacher-defined IEP objective but evidence is insufficient for a trend.';
        }

        return [
            'required' => !empty($reasons),
            'reasons'  => $reasons,
            'note'     => 'Observations only. The SPED teacher evaluates evidence against IEP indicators.',
        ];
    }
}
