<?php
require_once __DIR__ . '/ResourceTagService.php';
class InterventionDraftService {
    public static function blank(): array {
        return ['actionable'=>false, 'skill_verified'=>false, 'requires_target_skill'=>true,
            'title'=>'New intervention', 'learning_competency'=>'', 'tags'=>'', 'description'=>'',
            'pdsp_domain'=>'communication_language'];
    }
    public static function fromFactors(array $activity, array $factors): array {
        $accuracy = $factors['accuracy'] ?? [];
        $score = $accuracy['latest_result_accuracy'] ?? null;
        $items = $accuracy['latest_incorrect_items'] ?? [];
        if ($score === null || $score >= 100 || !$items) return self::blank();
        $text = '';
        foreach ($items as $item) $text .= ' ' . ($item['expected'] ?? '');
        $concepts = ResourceTagService::safeConcepts($text);
        if (!$concepts) $concepts = ResourceTagService::safeConcepts(($activity['competency'] ?? '') . ' ' . ($activity['title'] ?? ''));
        if (!$concepts) return array_merge(self::blank(), ['actionable'=>true]);
        $skill = $concepts[0];
        $title = 'Practice: ' . ucfirst($skill);
        return ['actionable'=>true, 'skill_verified'=>true, 'requires_target_skill'=>false, 'title'=>$title, 'learning_competency'=>$skill,
            'tags'=>implode(', ', $concepts ?: ['targeted practice']),
            'description'=>'Teacher-reviewed practice for ' . $skill . '.',
            'pdsp_domain'=>in_array($skill,['addition','subtraction','multiplication','division','counting'],true) ? 'perceptuo_cognitive' : 'communication_language'];
    }

    public static function reviewedMetadata(array $body): array {
        if (($body['draft_reviewed'] ?? false) !== true) throw new InvalidArgumentException('Review the editable intervention details before saving a private draft.');
        $draft = $body['resource_draft'] ?? [];
        if (!is_array($draft)) throw new InvalidArgumentException('Draft metadata must be an object.');
        $out = [];
        foreach (['title','description','learning_competency'] as $field) {
            if (!is_string($draft[$field] ?? '')) throw new InvalidArgumentException('Draft details must be text.');
            $out[$field] = mb_substr(trim(strip_tags($draft[$field] ?? '')), 0, $field === 'title' ? 255 : 2000);
        }
        if ($out['learning_competency'] === '') throw new InvalidArgumentException('Select the target skill or competency before saving the intervention.');
        $out['tags'] = implode(', ', ResourceTagService::normalizeTags($draft['tags'] ?? ''));
        return $out;
    }
}
