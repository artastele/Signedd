<?php
/** Local, deterministic resource metadata rules. Never reads learner data or calls an API. */
class ResourceTagService {
    private const CONCEPTS = [
        'addition' => ['addition', 'adding', 'add numbers', 'pagdaragdag', 'pagsasama ng bilang'],
        'subtraction' => ['subtraction', 'subtract', 'pagbabawas'],
        'multiplication' => ['multiplication', 'multiply', 'pagpaparami'],
        'division' => ['division', 'divide', 'paghahati'],
        'counting' => ['counting', 'count objects', 'pagbibilang'],
        'letter sounds' => ['letter sounds', 'letter sound', 'phonics', 'tunog ng titik'],
        'letter recognition' => ['letter recognition', 'identify letters', 'pagkilala sa titik'],
        'reading comprehension' => ['reading comprehension', 'understanding a story', 'pag unawa sa binasa'],
        'sight words' => ['sight words', 'sight word'],
        'rhyming words' => ['rhyming words', 'rhyming', 'magkatugmang salita'],
        'hand washing' => ['hand washing', 'handwashing', 'paghuhugas ng kamay'],
        'mother' => ['mother', 'nanay', 'ina'],
        'father' => ['father', 'tatay', 'ama'],
        'greetings' => ['greetings', 'greeting', 'pagbati'],
        'teacher' => ['teacher', 'guro', 'magtutudlo'],
    ];
    private const GENERIC = ['mathematics','math','reading','language','english','filipino','fsl','science',
        'communication','communication language','perceptuo cognitive','daily living','daily living skills',
        'biology','chemistry','physics','history','geography','social studies','music','art','arts',
        'psychomotor','psychosocial','socio emotional','beginner','basic','practice','learning','skill',
        'skills','competency','objective','lesson','activity','resource','pack','primary','sped','deaf ed',
        'true','false','yes','no','correct','incorrect','within','up','to','the','and','of','a','an',
        'in','for','with','learn','learner','learners','student','students','identify','recognize',
        'understand','demonstrate','use','using','review','introduction','worksheet','worksheets',
        'numbers','number','words','word','letters','letter','video','videos','sign','signs',
        'test','quiz','ng','mga','ang','sa','at','pagkilala'];

    public static function clean(string $text): string {
        $text = mb_strtolower(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text));
    }

    /** Standard instructional concepts only: safe defaults and Gemini input contain no raw answers/names. */
    public static function safeConcepts(string $text): array {
        $out = [];
        foreach (self::CONCEPTS as $concept => $aliases) foreach ($aliases as $alias) {
            if (self::contains($text, $alias)) { $out[] = $concept; break; }
        }
        return array_values(array_unique($out));
    }

    public static function contains(string $text, string $phrase): bool {
        return $phrase !== '' && strpos(' ' . self::clean($text) . ' ', ' ' . self::clean($phrase) . ' ') !== false;
    }

    /** Split bilingual labels, but never treat arbitrary numbers or subject names as skills. */
    public static function skills(string $text): array {
        $clean = self::clean($text);
        $skills = [];
        foreach (self::CONCEPTS as $concept => $aliases) {
            foreach ($aliases as $alias) {
                if (self::contains($clean, $alias)) { $skills[] = $concept; break; }
            }
        }
        foreach (preg_split('/[(),;\/]+/u', strip_tags($text)) as $part) {
            $part = self::clean($part);
            if (in_array($part, self::GENERIC, true)) continue;
            $meaningful = array_values(array_filter(explode(' ', $part), fn($word) =>
                preg_match('/\p{L}/u', $word) && !in_array($word, self::GENERIC, true)));
            if (!$meaningful) continue;
            if (mb_strlen($part) >= 3 && mb_strlen($part) <= 80 && !in_array($part, self::GENERIC, true)
                && preg_match('/\p{L}/u', $part)) $skills[] = $part;
            $phrase = implode(' ', $meaningful);
            if (mb_strlen($phrase) >= 3 && mb_strlen($phrase) <= 60) $skills[] = $phrase;
        }
        return array_values(array_unique($skills));
    }

    public static function normalizeTags($tags): array {
        if (!is_string($tags) && !is_array($tags)) throw new InvalidArgumentException('Tags must be text.');
        $out = [];
        foreach (is_array($tags) ? $tags : explode(',', $tags) as $tag) {
            if (!is_string($tag)) throw new InvalidArgumentException('Tags must be text.');
            $tag = self::clean($tag);
            if ($tag === '') continue;
            if (mb_strlen($tag) > 60) throw new InvalidArgumentException('Keep each tag within 60 characters.');
            $out[$tag] = true;
        }
        $tags = array_keys($out);
        if (count($tags) > 20 || mb_strlen(implode(', ', $tags)) > 500) {
            throw new InvalidArgumentException('Use up to 20 tags and 500 characters in total.');
        }
        return $tags;
    }

    public static function approvedTags(array $metadata): string {
        if (($metadata['tags_approved'] ?? false) !== true) {
            throw new InvalidArgumentException('Review and approve the resource tags before publishing.');
        }
        $tags = self::normalizeTags($metadata['tags'] ?? '');
        if (!$tags) throw new InvalidArgumentException('Add at least one relevant tag, then approve it.');
        return implode(', ', $tags);
    }

    public static function suggest(array $metadata): array {
        $phrases = [];
        foreach (['title','description','learning_area','learning_competency'] as $field) {
            if (!is_string($metadata[$field] ?? '')) throw new InvalidArgumentException('Resource metadata must be text.');
            $phrases[] = mb_substr($metadata[$field] ?? '', 0, 3000);
        }
        $text = implode(' ', $phrases);
        $tags = [];
        foreach (self::CONCEPTS as $concept => $aliases) {
            foreach ($aliases as $alias) {
                if (self::contains($text, $alias)) { $tags[] = $concept; break; }
            }
        }
        if (preg_match('/\b(?:within|up to)\s+(\d{1,3})\b/ui', $text, $match)) $tags[] = 'within ' . $match[1];
        // Keep uncommon subject skills discoverable without a fixed subject taxonomy.
        foreach ([$metadata['learning_competency'] ?? '', $metadata['title'] ?? ''] as $phrase) {
            $clean = self::clean($phrase);
            if ($clean !== '' && mb_strlen($clean) <= 60) $tags[] = $clean;
        }
        foreach (self::skills($metadata['description'] ?? '') as $skill) {
            if (mb_strlen($skill) <= 60) $tags[] = $skill;
        }
        $area = self::clean($metadata['learning_area'] ?? '');
        if ($area !== '' && mb_strlen($area) <= 60) $tags[] = $area;
        return array_slice(array_values(array_unique($tags)), 0, 12);
    }
}
