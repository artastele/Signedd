<?php
/** Scoring identity helpers shared by new submissions and read-only intervention matching. */
class ActivityEvidence {
    /** Retry counts are learner-reported; final correctness is evaluated by the server. */
    public static function practiceSummary(array $logs, $reportedAttempts, string $type, array $data): array {
        $reportedAttempts = is_array($reportedAttempts) ? $reportedAttempts : [];
        $groupForItem = [];
        if ($type === 'matching') {
            $sets = $data['sets'] ?? $data['matching_sets'] ?? [['pairs'=>$data['pairs']??[]]];
            $index = 0;
            foreach ($sets as $step => $set) foreach ($set['pairs'] ?? [] as $_) $groupForItem[$index++] = $step;
        }
        $groups = [];
        foreach ($logs as $item) {
            $step = $type === 'image_label' ? 0 : ($groupForItem[$item['qi']] ?? $item['qi']);
            $groups[$step] = ($groups[$step] ?? true) && (bool)$item['ic'];
        }
        $steps = []; $total = 0; $successful = 0; $complete = true;
        foreach ($groups as $step => $solved) {
            $raw = $reportedAttempts[$step] ?? null;
            $valid = (is_int($raw) || (is_string($raw) && ctype_digit($raw))) && (int)$raw >= 1 && (int)$raw <= 100000;
            $count = $valid ? (int)$raw : null;
            if ($count === null) $complete = false;
            else $total += $count;
            if ($solved) $successful++;
            $steps[(string)$step] = ['attempts'=>$count, 'solved'=>$solved,
                'accuracy_percentage'=>$count === null ? null : ($solved ? round(100 / $count, 2) : 0)];
        }
        return ['source'=>'learner_reported_checks', 'steps'=>$steps, 'successful_checks'=>$successful,
            'total_attempts'=>$complete ? $total : null,
            'accuracy_percentage'=>$complete && $total > 0 ? round(100 * $successful / $total, 2) : null];
    }

    public static function text($value): string {
        return is_scalar($value) ? trim((string)$value) : '';
    }
    public static function normalize($value): string {
        if (is_bool($value)) return $value ? 'true' : 'false';
        return mb_strtolower(preg_replace('/\s+/u', ' ', self::text($value)));
    }
    public static function options(array $question): array {
        return !empty($question['options']) && is_array($question['options']) ? $question['options'] : (is_array($question['choices'] ?? null) ? $question['choices'] : []);
    }
    public static function booleanAnswer($value): string {
        if (in_array($value,[true,1,'1','true','TRUE'],true)) return 'true';
        if (in_array($value,[false,0,'0','false','FALSE'],true)) return 'false';
        return in_array(self::normalize($value),['true','false'],true) ? self::normalize($value) : '';
    }
    public static function optionText($option): string {
        return self::text(is_array($option) ? ($option['text'] ?? $option['label'] ?? '') : $option);
    }
    public static function correctIndex(array $question): ?int {
        $options = self::options($question);
        foreach (['correct_answer','correct'] as $key) {
            $value = $question[$key] ?? null;
            if ((is_int($value) || (is_string($value) && preg_match('/^\d+$/D', $value))) && isset($options[(int)$value])) return (int)$value;
        }
        foreach ($options as $index => $option) {
            $flag = is_array($option) ? ($option['is_correct'] ?? $option['isCorrect'] ?? $option['correct_answer'] ?? null) : null;
            if (in_array($flag, [true,1,'1','true'], true)) return (int)$index;
        }
        // Some legacy builders store the answer text rather than its option index.
        $answer = self::normalize($question['correct_answer'] ?? '');
        if ($answer !== '') foreach ($options as $index => $option) {
            if (self::normalize(self::optionText($option)) === $answer) return (int)$index;
        }
        return null;
    }
    public static function multipleChoice(array $question, $answer): array {
        $options = self::options($question);
        $correctIndex = self::correctIndex($question);
        $selectedIndex = null;
        $selected = self::text($answer);
        if ((is_int($answer) || (is_string($answer) && preg_match('/^\d+$/D', $answer))) && isset($options[(int)$answer])) {
            $selectedIndex = (int)$answer;
            $selected = self::optionText($options[$selectedIndex]);
        } elseif (is_string($answer) && $selected !== '') {
            foreach ($options as $index => $option) {
                if (self::normalize(self::optionText($option)) === self::normalize($selected)) {
                    $selectedIndex = (int)$index;
                    if ($selectedIndex === $correctIndex) break;
                }
            }
            if ($selectedIndex !== null) $selected = self::optionText($options[$selectedIndex]);
        }
        $expected = $correctIndex !== null ? self::optionText($options[$correctIndex]) : '';
        // Distinguish duplicate option labels without inventing a vocabulary error.
        if ($selectedIndex !== null && $selectedIndex !== $correctIndex && self::normalize($selected) === self::normalize($expected)) {
            $selected = '[option ' . $selectedIndex . '] ' . $selected;
            $expected = '[option ' . $correctIndex . '] ' . $expected;
        }
        return ['selected'=>$selected,'expected'=>$expected,'correct'=>$correctIndex !== null && $selectedIndex === $correctIndex];
    }
    public static function prompt(array $data, int $index, string $type = ''): string {
        $question = $data['questions'][$index] ?? null;
        if (is_array($question)) return self::text($question['question'] ?? $question['text'] ?? $question['statement'] ?? '');
        return self::teachingItems($type,$data)[$index]['prompt'] ?? '';
    }
    public static function teachingItems(string $type, array $data): array {
        $out=[];
        if ($type==='fill_in_blanks') {
            foreach (is_array($data['sentences']??null) ? $data['sentences'] : [] as $sentence) foreach (is_array($sentence['answers']??null) ? $sentence['answers'] : [] as $answer) {
                $out[]=['prompt'=>self::text($sentence['sentence'] ?? ''),'expected'=>self::text($answer)];
            }
            if (!$out) foreach (is_array($data['answers']??null) ? $data['answers'] : [] as $answer) $out[]=['prompt'=>'','expected'=>self::text($answer)];
        } elseif ($type==='matching') {
            $sets=$data['sets'] ?? $data['matching_sets'] ?? [['pairs'=>$data['pairs']??[]]];
            foreach (is_array($sets) ? $sets : [] as $set) foreach (is_array($set['pairs']??null) ? $set['pairs'] : [] as $pair) {
                if (!is_array($pair)) continue;
                $left=self::text($pair['left']??'');$out[]=['prompt'=>$left,'expected'=>$left.' = '.self::text($pair['right']??'')];
            }
        } elseif ($type==='image_label') {
            $labels=$data['labels'] ?? $data['markers'] ?? [];
            foreach (is_array($labels) ? $labels : [] as $label) if (is_array($label)) $out[]=['prompt'=>self::text($label['label']??$label['text']??''),'expected'=>self::text($label['answer']??'')];
        } elseif ($type==='true_false') {
            $questions=$data['questions'] ?? [$data];
            foreach (is_array($questions) ? $questions : [] as $question) if (is_array($question)) $out[]=['prompt'=>self::text($question['question']??$question['statement']??''),'expected'=>self::booleanAnswer($question['answer']??$question['correct_answer']??null)];
        } elseif (in_array($type,['sequencing','drag_drop_sort'],true)) {
            $sets=$data['sets'] ?? $data['sort_sets'] ?? $data['sequence_sets'] ?? [$data];
            foreach (is_array($sets) ? $sets : [] as $set) {
                if (!is_array($set)) continue;
                $items=$set['items'] ?? $set['steps'] ?? [];
                if (!is_array($items)) continue;
                $order=$set['correct_order'] ?? array_keys($items);
                if (!is_array($items) || !is_array($order)) continue;
                $out[]=['prompt'=>'Order sequence: '.self::sequenceText($items,array_keys($items)), 'expected'=>self::sequenceText($items,$order)];
            }
        }
        return $out;
    }
    public static function sequenceText(array $items, array $order): string {
        if (!$order) return '(Empty)';
        $parts=[];
        foreach ($order as $index) {
            if (!is_int($index) && !is_string($index)) return '';
            $value=$items[$index] ?? $index;
            $parts[]=is_array($value) ? self::text($value['text']??$value['title']??$value['label']??$index) : self::text($value);
        }
        $text=implode(' -> ',$parts);
        return mb_strlen($text)>250 ? mb_substr($text,0,247).'...' : $text;
    }
    public static function ambiguous(array $log): bool {
        $expected = self::normalize($log['correct_value'] ?? '');
        $selected = preg_replace('/\s*\(Attempts: \d+\)$/u', '', self::text($log['selected_value'] ?? ''));
        return $expected === '' || str_starts_with($expected,'[option ')
            || ((int)($log['is_correct'] ?? 1) === 0 && self::normalize($selected) === $expected);
    }
}
