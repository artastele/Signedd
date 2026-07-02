<?php
// app/Helpers/ScoreHelper.php
// Created for SPED LMS Activity System Overhaul
// Provides scoring algorithms for tolerant string matching and order checks (scoring).

class ScoreHelper {
    /**
     * Compare submitted order array against correct order.
     * Used by drag_drop_sort and sequencing.
     *
     * @param array $submitted  Learner's submitted order (array of item IDs or strings)
     * @param array $correct    Teacher's correct order
     * @param int   $tolerance  Number of out-of-position items still counted correct (default 0)
     * @return int              Score: 1 (pass) or 0 (fail)
     */
    public static function compareOrder(array $submitted, array $correct, int $tolerance = 0): int {
        if (count($submitted) !== count($correct)) return 0;
        $wrong = 0;
        foreach ($correct as $i => $val) {
            if (!isset($submitted[$i]) || $submitted[$i] !== $val) {
                $wrong++;
            }
        }
        return ($wrong <= $tolerance) ? 1 : 0;
    }

    /**
     * Levenshtein-tolerant string match for fill_in_blanks free type mode.
     *
     * @param string $input     Learner's typed input
     * @param string $correct   Correct answer
     * @param int    $threshold Max edit distance to count as correct (default 2)
     * @return bool
     */
    public static function fuzzyMatch(string $input, string $correct, int $threshold = 2): bool {
        $a = strtolower(trim($input));
        $b = strtolower(trim($correct));
        if ($a === $b) return true;
        return levenshtein($a, $b) <= $threshold;
    }
}
