<?php
/**
 * Regression Test Suite: Multiple Choice Answer Evaluation & Logging Compatibility
 * 
 * Tests cover:
 * - Standard index-based submissions (correct & incorrect)
 * - Legacy text-based submissions (correct & incorrect)
 * - Duplicate option label disambiguation
 * - Legacy Activity Builder format (choices + correct_answer)
 * - Legacy warm-up seed format (plain string options + correct index)
 * - Malformed / edge-case inputs (null, empty, negative, out-of-bounds, array)
 * - Frontend player helper normalization (getQuestionOptions & getCorrectMcIndex)
 */

declare(strict_types=1);

class MultipleChoiceEvaluationTest {

    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function runAll(): void {
        echo "========================================================\n";
        echo "SignED Multiple-Choice Evaluation & Logging Test Suite\n";
        echo "========================================================\n\n";

        $this->testStandardIndexSubmissions();
        $this->testLegacyTextSubmissions();
        $this->testDuplicateOptionLabels();
        $this->testLegacyActivityBuilderFormat();
        $this->testLegacyWarmUpFormat();
        $this->testMalformedInputs();
        $this->testFrontendPlayerHelpers();

        echo "\n--------------------------------------------------------\n";
        echo "Test Results: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "--------------------------------------------------------\n";

        if ($this->failed > 0) {
            echo "\nFailures:\n";
            foreach ($this->failures as $f) {
                echo "  - $f\n";
            }
            exit(1);
        } else {
            echo "All regression tests passed successfully!\n\n";
        }
    }

    private function assert(bool $condition, string $message): void {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] $message\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "  [FAIL] $message\n";
        }
    }

    /**
     * Evaluates multiple choice question answers using the exact logic implemented in LearningController.php
     */
    private function evaluateQuestion(array $q, mixed $userAnswer): array {
        $extractOptionText = static function ($opt): string {
            if (is_array($opt)) {
                return (string)($opt['text'] ?? $opt['label'] ?? '');
            }
            return (string)$opt;
        };

        $options = !empty($q['options']) && is_array($q['options'])
            ? $q['options']
            : (!empty($q['choices']) && is_array($q['choices']) ? $q['choices'] : []);

        $correctIndex = null;
        if (isset($q['correct_answer']) && is_numeric($q['correct_answer'])) {
            $correctIndex = (int)$q['correct_answer'];
        } elseif (isset($q['correct']) && is_numeric($q['correct'])) {
            $correctIndex = (int)$q['correct'];
        } else {
            foreach ($options as $oi => $opt) {
                $isCorrectOpt = is_array($opt) 
                    ? ($opt['is_correct'] ?? $opt['isCorrect'] ?? $opt['correct_answer'] ?? null) 
                    : null;
                if (!empty($isCorrectOpt)) {
                    $correctIndex = (int)$oi;
                    break;
                }
            }
        }

        $isCorrect = false;
        $userAnswerText = '';

        if ($userAnswer !== null && is_numeric($userAnswer) && isset($options[(int)$userAnswer])) {
            $userIndex = (int)$userAnswer;
            $userAnswerText = $extractOptionText($options[$userIndex]);
            if ($correctIndex !== null && $userIndex === $correctIndex) {
                $isCorrect = true;
            }
        } elseif ($userAnswer !== null && is_string($userAnswer) && trim($userAnswer) !== '') {
            $trimmedAnswer = trim($userAnswer);
            $matchingIndices = [];
            foreach ($options as $oi => $opt) {
                if (strcasecmp(trim($extractOptionText($opt)), $trimmedAnswer) === 0) {
                    $matchingIndices[] = (int)$oi;
                }
            }

            if (!empty($matchingIndices)) {
                if ($correctIndex !== null && in_array($correctIndex, $matchingIndices, true)) {
                    $isCorrect = true;
                    $userAnswerText = $extractOptionText($options[$correctIndex]);
                } else {
                    $isCorrect = false;
                    $userAnswerText = $extractOptionText($options[$matchingIndices[0]]);
                }
            } else {
                $isCorrect = false;
                $userAnswerText = $trimmedAnswer;
            }
        } else {
            $isCorrect = false;
            $userAnswerText = is_scalar($userAnswer) ? (string)$userAnswer : '';
        }

        $correctAnswerText = ($correctIndex !== null && isset($options[$correctIndex]))
            ? $extractOptionText($options[$correctIndex])
            : ($correctIndex !== null ? (string)$correctIndex : '');

        return [
            'is_correct' => $isCorrect ? 1 : 0,
            'selected_value' => $userAnswerText,
            'correct_value' => $correctAnswerText,
            'correct_index' => $correctIndex
        ];
    }

    private function testStandardIndexSubmissions(): void {
        echo "1. Testing Standard Index Submissions (Activities 6, 7, 9 format)...\n";

        // Activity 6, Question 1
        $q = [
            'question' => 'Sino ang nag-aalaga at naghahanda ng pagkain sa bahay kasama si Tatay?',
            'options' => [
                ['text' => 'Nanay (Mother)', 'is_correct' => 1],
                ['text' => 'Pulis', 'is_correct' => 0],
                ['text' => 'Doktor', 'is_correct' => 0],
            ]
        ];

        // 1A. Correct index 0
        $res = $this->evaluateQuestion($q, 0);
        $this->assert($res['is_correct'] === 1, "Index 0 is marked correct (is_correct = 1)");
        $this->assert($res['selected_value'] === 'Nanay (Mother)', "selected_value matches option 0 text");
        $this->assert($res['correct_value'] === 'Nanay (Mother)', "correct_value matches option 0 text");

        // 1B. Incorrect index 1 (Pulis)
        $res = $this->evaluateQuestion($q, 1);
        $this->assert($res['is_correct'] === 0, "Index 1 (Pulis) is marked incorrect (is_correct = 0)");
        $this->assert($res['selected_value'] === 'Pulis', "selected_value correctly reflects 'Pulis'");
        $this->assert($res['correct_value'] === 'Nanay (Mother)', "correct_value reflects 'Nanay (Mother)'");
        $this->assert($res['selected_value'] !== $res['correct_value'], "selected_value and correct_value are distinct for wrong answer");

        // 1C. String numeric index '0' and '1'
        $res = $this->evaluateQuestion($q, '0');
        $this->assert($res['is_correct'] === 1, "String index '0' is evaluated as correct");
        $res = $this->evaluateQuestion($q, '1');
        $this->assert($res['is_correct'] === 0, "String index '1' is evaluated as incorrect");
    }

    private function testLegacyTextSubmissions(): void {
        echo "\n2. Testing Legacy Text Submissions...\n";

        $q = [
            'question' => 'Sino ang nag-aalaga at naghahanda ng pagkain sa bahay kasama si Tatay?',
            'options' => [
                ['text' => 'Nanay (Mother)', 'is_correct' => 1],
                ['text' => 'Pulis', 'is_correct' => 0],
                ['text' => 'Doktor', 'is_correct' => 0],
            ]
        ];

        // 2A. Exact text of correct answer (Root cause of ID 50, 53, 59)
        $res = $this->evaluateQuestion($q, 'Nanay (Mother)');
        $this->assert($res['is_correct'] === 1, "Text 'Nanay (Mother)' is now evaluated as correct (is_correct = 1)");
        $this->assert($res['selected_value'] === 'Nanay (Mother)', "selected_value is 'Nanay (Mother)'");
        $this->assert($res['correct_value'] === 'Nanay (Mother)', "correct_value is 'Nanay (Mother)'");

        // 2B. Case-insensitive and trimmed text
        $res = $this->evaluateQuestion($q, '  nanay (mother)  ');
        $this->assert($res['is_correct'] === 1, "Trimmed/case-insensitive text is evaluated as correct");
        $this->assert($res['selected_value'] === 'Nanay (Mother)', "selected_value normalized to canonical option text");

        // 2C. Exact text of wrong answer
        $res = $this->evaluateQuestion($q, 'Pulis');
        $this->assert($res['is_correct'] === 0, "Wrong option text 'Pulis' is marked incorrect");
        $this->assert($res['selected_value'] === 'Pulis', "selected_value is 'Pulis'");
        $this->assert($res['correct_value'] === 'Nanay (Mother)', "correct_value is 'Nanay (Mother)'");
    }

    private function testDuplicateOptionLabels(): void {
        echo "\n3. Testing Duplicate Option Labels...\n";

        $q = [
            'question' => 'Choose the target item',
            'options' => [
                ['text' => 'Apple', 'is_correct' => 0],
                ['text' => 'Banana', 'is_correct' => 0],
                ['text' => 'Apple', 'is_correct' => 1], // Duplicate label, but index 2 is the correct one
            ]
        ];

        // 3A. Index 0 chosen (wrong index, duplicate label)
        $res = $this->evaluateQuestion($q, 0);
        $this->assert($res['is_correct'] === 0, "Index 0 is marked incorrect despite sharing label 'Apple'");
        $this->assert($res['selected_value'] === 'Apple', "selected_value is 'Apple'");
        $this->assert($res['correct_value'] === 'Apple', "correct_value is 'Apple'");

        // 3B. Index 2 chosen (correct index)
        $res = $this->evaluateQuestion($q, 2);
        $this->assert($res['is_correct'] === 1, "Index 2 is marked correct (is_correct = 1)");

        // 3C. Text 'Apple' submitted
        $res = $this->evaluateQuestion($q, 'Apple');
        $this->assert($res['is_correct'] === 1, "Text 'Apple' correctly recognizes that one of the matching options is correct");
    }

    private function testLegacyActivityBuilderFormat(): void {
        echo "\n4. Testing Activity Builder Format (choices + correct_answer)...\n";

        $q = [
            'question' => 'What is the color of the sky on a clear day?',
            'choices' => ['Green', 'Blue', 'Yellow', 'Red'],
            'correct_answer' => 1,
            'points' => 10
        ];

        // 4A. Correct index 1
        $res = $this->evaluateQuestion($q, 1);
        $this->assert($res['is_correct'] === 1, "Builder choices: Index 1 is marked correct");
        $this->assert($res['selected_value'] === 'Blue', "Builder choices: selected_value is 'Blue'");
        $this->assert($res['correct_value'] === 'Blue', "Builder choices: correct_value is 'Blue'");

        // 4B. Incorrect index 0
        $res = $this->evaluateQuestion($q, 0);
        $this->assert($res['is_correct'] === 0, "Builder choices: Index 0 is marked incorrect");
        $this->assert($res['selected_value'] === 'Green', "Builder choices: selected_value is 'Green'");
        $this->assert($res['correct_value'] === 'Blue', "Builder choices: correct_value is 'Blue'");

        // 4C. Text 'Blue'
        $res = $this->evaluateQuestion($q, 'Blue');
        $this->assert($res['is_correct'] === 1, "Builder choices: Text 'Blue' is marked correct");
    }

    private function testLegacyWarmUpFormat(): void {
        echo "\n5. Testing Legacy Warm-Up Seed Format (Activity 1: string options + correct)...\n";

        $q = [
            'question' => 'Ilan ang 3 + 2?',
            'options' => ['4', '5', '6'],
            'correct' => 1,
            'points' => 10
        ];

        // 5A. Correct index 1 ('5')
        $res = $this->evaluateQuestion($q, 1);
        $this->assert($res['is_correct'] === 1, "Warm-up seed: Index 1 ('5') is marked correct");
        $this->assert($res['selected_value'] === '5', "Warm-up seed: selected_value is '5'");
        $this->assert($res['correct_value'] === '5', "Warm-up seed: correct_value is '5'");

        // 5B. Incorrect index 0 ('4')
        $res = $this->evaluateQuestion($q, 0);
        $this->assert($res['is_correct'] === 0, "Warm-up seed: Index 0 ('4') is marked incorrect");
        $this->assert($res['selected_value'] === '4', "Warm-up seed: selected_value is '4'");
        $this->assert($res['correct_value'] === '5', "Warm-up seed: correct_value is '5'");

        // 5C. Text '5'
        $res = $this->evaluateQuestion($q, '5');
        $this->assert($res['is_correct'] === 1, "Warm-up seed: Text '5' resolves and evaluates to correct");
    }

    private function testMalformedInputs(): void {
        echo "\n6. Testing Malformed & Edge-Case Inputs...\n";

        $q = [
            'question' => 'Test Question',
            'options' => [
                ['text' => 'Alpha', 'is_correct' => 1],
                ['text' => 'Beta', 'is_correct' => 0],
            ]
        ];

        // 6A. null input
        $res = $this->evaluateQuestion($q, null);
        $this->assert($res['is_correct'] === 0, "null input evaluates to is_correct = 0");
        $this->assert($res['selected_value'] === '', "null input yields empty selected_value");

        // 6B. empty string
        $res = $this->evaluateQuestion($q, '');
        $this->assert($res['is_correct'] === 0, "Empty string input evaluates to is_correct = 0");

        // 6C. Negative index -1
        $res = $this->evaluateQuestion($q, -1);
        $this->assert($res['is_correct'] === 0, "Negative index -1 evaluates to is_correct = 0");

        // 6D. Out of bounds index 999
        $res = $this->evaluateQuestion($q, 999);
        $this->assert($res['is_correct'] === 0, "Out-of-bounds index 999 evaluates to is_correct = 0");

        // 6E. Array input
        $res = $this->evaluateQuestion($q, ['foo' => 'bar']);
        $this->assert($res['is_correct'] === 0, "Array input evaluates to is_correct = 0 without error");

        // 6F. Non-matching string
        $res = $this->evaluateQuestion($q, 'Zeta');
        $this->assert($res['is_correct'] === 0, "Unrecognized string 'Zeta' evaluates to is_correct = 0");
        $this->assert($res['selected_value'] === 'Zeta', "Unrecognized string is logged cleanly as 'Zeta'");
    }

    private function testFrontendPlayerHelpers(): void {
        echo "\n7. Testing Frontend Player Helper Functions...\n";

        $getQuestionOptions = static function (array $question): array {
            if (!empty($question['options']) && is_array($question['options'])) {
                return $question['options'];
            }
            if (!empty($question['choices']) && is_array($question['choices'])) {
                return $question['choices'];
            }
            return [];
        };

        $getCorrectMcIndex = static function (array $question) use ($getQuestionOptions): ?int {
            if (isset($question['correct_answer']) && is_numeric($question['correct_answer'])) {
                return (int)$question['correct_answer'];
            }
            if (isset($question['correct']) && is_numeric($question['correct'])) {
                return (int)$question['correct'];
            }
            $options = $getQuestionOptions($question);
            foreach ($options as $index => $option) {
                if (!is_array($option)) {
                    continue;
                }
                $isCorrect = $option['is_correct'] ?? $option['isCorrect'] ?? $option['correct_answer'] ?? false;
                if (!empty($isCorrect)) {
                    return (int)$index;
                }
            }
            return null;
        };

        // 7A. options format
        $q1 = ['options' => [['text' => 'A', 'is_correct' => 0], ['text' => 'B', 'is_correct' => 1]]];
        $this->assert(count($getQuestionOptions($q1)) === 2, "getQuestionOptions extracts options array");
        $this->assert($getCorrectMcIndex($q1) === 1, "getCorrectMcIndex resolves index 1 via is_correct");

        // 7B. choices + correct_answer format
        $q2 = ['choices' => ['A', 'B', 'C'], 'correct_answer' => 2];
        $this->assert(count($getQuestionOptions($q2)) === 3, "getQuestionOptions falls back to choices array");
        $this->assert($getCorrectMcIndex($q2) === 2, "getCorrectMcIndex resolves correct_answer index");

        // 7C. plain options + correct format
        $q3 = ['options' => ['4', '5', '6'], 'correct' => 1];
        $this->assert($getCorrectMcIndex($q3) === 1, "getCorrectMcIndex resolves legacy 'correct' field");
    }
}

// Execute the test suite
$test = new MultipleChoiceEvaluationTest();
$test->runAll();
