<?php
require_once __DIR__ . '/ActivityEvidence.php';
require_once __DIR__ . '/ResourceTagService.php';

/** Matches published instructional snapshots, never learner records or a subject alone. */
class ResourceHubContentMatcher {
    /** Compare whole instructional snapshots; database IDs and pack labels do not define content. */
    public static function fingerprint(array $pages, array $activities): ?string {
        if (!$pages && !$activities) return null;
        $text = static fn($value) => trim(str_replace(["\r\n", "\r"], "\n", (string)($value ?? '')));
        $snapshot = ['pages'=>[], 'activities'=>[]];
        foreach ($pages as $page) {
            $row=[];
            foreach (['title','content','guide_questions','media_type','media_path'] as $field) $row[$field]=$text($page[$field]??'');
            $snapshot['pages'][]=$row;
        }
        foreach ($activities as $activity) {
            $row=[];
            foreach (['title','activity_type','instructions'] as $field) $row[$field]=$text($activity[$field]??'');
            $raw=$text($activity['activity_data']??'');
            $data=json_decode($raw,true);
            $row['activity_data']=json_last_error()===JSON_ERROR_NONE ? self::canonicalData($data) : $raw;
            $row['max_score']=isset($activity['max_score']) ? (float)$activity['max_score'] : null;
            $row['is_f2f']=(int)($activity['is_f2f']??0);
            $snapshot['activities'][]=$row;
        }
        return hash('sha256',serialize($snapshot));
    }

    private static function canonicalData($value) {
        if (!is_array($value)) return $value;
        // Object key order is irrelevant; sequence, question and option order is preserved.
        if ($value && array_keys($value)!==range(0,count($value)-1)) ksort($value);
        foreach ($value as &$entry) $entry=self::canonicalData($entry);
        unset($entry);
        return $value;
    }

    public static function rank(array $pack, array $pages, array $activities, array $items, array $contextSkills): ?array {
        $pages = array_values(array_filter($pages, function ($page): bool {
            $content = $page['content'] ?? '';
            if (!str_contains($content,'resource-material-link')) return true;
            if (!preg_match('/href=["\']([^"\']+)["\']/ui',$content,$link)) return false;
            $href = html_entity_decode($link[1],ENT_QUOTES,'UTF-8');
            if (preg_match('~(?:^|/)(uploads/materials/[0-9]+/[a-zA-Z0-9_.-]+)$~D',$href,$path)) {
                return is_file(__DIR__ . '/../../public/' . $path[1]);
            }
            // External-only material availability is unverified; never fetch remote documents here.
            return false;
        }));
        $best = null;
        $offer = function (int $rank, string $skill, string $reason, string $source) use (&$best): void {
            if (!$best || $rank > $best['rank']) $best = compact('rank','skill','reason','source');
        };
        foreach ($items as $item) {
            $expected = (string)($item['expected'] ?? '');
            if (str_starts_with($expected, '[option ')) continue; // Duplicate labels do not establish a concept error.
            $skills = ResourceTagService::skills($expected);
            foreach ($activities as $activity) {
                $data = json_decode($activity['activity_data'] ?? '{}', true);
                if (!is_array($data)) continue;
                foreach (is_array($data['questions'] ?? null) ? $data['questions'] : [] as $question) {
                    if (!is_array($question)) continue;
                    $index = ActivityEvidence::correctIndex($question);
                    if ($index === null) continue;
                    $answer = ActivityEvidence::optionText(ActivityEvidence::options($question)[$index]);
                    $prompt = ActivityEvidence::text($question['question'] ?? $question['text'] ?? '');
                    $sameAnswer = ActivityEvidence::normalize($answer) === ActivityEvidence::normalize($expected);
                    if ($sameAnswer && !empty($item['prompt']) && self::questionKey($prompt) === self::questionKey($item['prompt'])) {
                        $offer(100, $contextSkills[0] ?? $expected, 'Same captured question and expected answer in a published Hub activity.', 'resource_hub_activities#' . $activity['id']);
                    }
                    foreach ($skills as $skill) {
                        if (in_array($skill, ResourceTagService::skills($answer), true)) {
                            $offer(80, $skill, 'The recorded concept is explicitly tested as a correct answer in this Hub activity.', 'resource_hub_activities#' . $activity['id']);
                        }
                    }
                }
                foreach (ActivityEvidence::teachingItems($activity['activity_type'] ?? '',$data) as $teachingItem) {
                    if (!empty($item['prompt']) && self::questionKey($teachingItem['prompt'])===self::questionKey($item['prompt'])
                        && ActivityEvidence::normalize($teachingItem['expected'])===ActivityEvidence::normalize($expected)) {
                        $offer(100,$contextSkills[0]??$expected,'Same captured task and expected answer in a published Hub activity.','resource_hub_activities#'.$activity['id']);
                    }
                    foreach ($skills as $skill) if (in_array($skill,ResourceTagService::skills($teachingItem['expected']),true)) {
                        $offer(80,$skill,'The recorded concept is explicitly practiced in this Hub activity.','resource_hub_activities#'.$activity['id']);
                    }
                }
            }
            foreach ($pages as $page) {
                // Explicit sign/word teaching markers, not incidental words in prose.
                preg_match_all('/data-word=["\']([^"\']+)["\']/ui', $page['content'] ?? '', $markers);
                foreach ($markers[1] ?? [] as $marker) foreach ($skills as $skill) {
                    if (in_array($skill, ResourceTagService::skills(html_entity_decode($marker, ENT_QUOTES, 'UTF-8')), true)) {
                        $offer(75, $skill, 'This Hub slide explicitly teaches the recorded concept.', 'resource_hub_pages#' . $page['id']);
                    }
                }
            }
        }
        $metadata = implode(' ', [$pack['title'] ?? '', $pack['learning_competency'] ?? '', $pack['tags'] ?? '']);
        foreach ($contextSkills as $skill) {
            if (!in_array($skill, ResourceTagService::skills($metadata), true) && !ResourceTagService::contains($metadata, $skill)) continue;
            // A misleading title alone is not evidence: corroborate the skill in instructional content.
            foreach ($pages as $page) {
                $text = ($page['title'] ?? '') . ' ' . strip_tags($page['content'] ?? '') . ' ' . ($page['guide_questions'] ?? '');
                $instructionalSkill = in_array($skill,['color recognition','addition','subtraction','multiplication','division','counting','letter sounds','letter recognition','reading comprehension','sight words','rhyming words','hand washing'],true);
                $explicitHeading = ResourceTagService::contains($page['title'] ?? '',$skill);
                if (($instructionalSkill || $explicitHeading) && (in_array($skill, ResourceTagService::skills($text), true) || ResourceTagService::contains($text, $skill))) {
                    $offer(50, $skill, 'Specific skill metadata is supported by the published instructional content.', 'resource_hub_pages#' . $page['id']);
                }
            }
        }
        return $best;
    }
    private static function questionKey(string $prompt): string {
        return preg_replace('/\s*([+\-*\/=])\s*/u', '$1', ActivityEvidence::normalize($prompt));
    }
}
