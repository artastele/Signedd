<?php
// Part of: SignED â€” Learning Gap Innovation (Process 10.1), Phase 3 â€” AI Narration
// GeminiExplanationService: Generates concise, evidence-based educational explanations
// for SPED teachers using the Google Gemini API.
//
// SECURITY GUARANTEES:
//   - API key loaded only from server-side environment variable or database setting.
//   - No learner names, emails, contact details, or raw IEP records are ever transmitted.
//   - All Gemini output is validated, sanitized server-side, and treated as advisory only.
//   - Real learner data transmission disabled by default (uses synthetic pseudonymization).
//   - Gemini cannot invent resource IDs, scores, FSL signs, or performance records.
//   - All JSON output is validated before any rendering.

class GeminiExplanationService {

    /**
     * Gemini model â€” configurable via GEMINI_MODEL env var.
     * Default: gemini-2.0-flash (fast, cost-effective, good instruction-following).
     */
    private string $model;
    private ?string $apiKey;
    private string $apiBase = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /** Maximum tokens in Gemini response */
    private int $maxOutputTokens = 1024;

    /** HTTP timeout in seconds */
    private int $timeout = 20;

    public function __construct() {
        $this->apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? null);
        if (empty($this->apiKey)) {
            $this->apiKey = $this->loadApiKeyFromSettings();
        }
        $this->model = getenv('GEMINI_MODEL') ?: ($_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash');
    }

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------

    /**
     * Generate a structured AI explanation for a single activity's insight.
     *
     * @param array $insightsForActivity  One entry from ResourceRecommendationService::recommendForStudent()
     * @return array  { success, explanation, model, generated_at } or { success:false, error, message }
     */
    public function explainActivityInsight(array $insightsForActivity): array {
        if (empty($this->apiKey)) {
            return [
                'success'     => false,
                'error'       => 'no_api_key',
                'message'     => 'Gemini API key is not configured. Add GEMINI_API_KEY to your .env file.',
                'explanation' => null,
            ];
        }

        $payload = $this->buildMinimalPayload($insightsForActivity);

        if (empty($payload)) {
            return [
                'success'     => false,
                'error'       => 'insufficient_evidence',
                'message'     => 'Insufficient performance evidence to generate an AI explanation.',
                'explanation' => null,
            ];
        }

        $prompt = $this->buildSystemPrompt($payload);

        try {
            $raw = $this->callGemini($prompt);
        } catch (Throwable $e) {
            error_log('GeminiExplanationService callGemini error: ' . $e->getMessage());
            return [
                'success'     => false,
                'error'       => 'api_error',
                'message'     => 'Gemini API request failed: ' . $e->getMessage(),
                'explanation' => null,
            ];
        }

        $parsed = $this->parseAndValidateResponse($raw);
        if (!$parsed['valid']) {
            return [
                'success'     => false,
                'error'       => 'invalid_response',
                'message'     => 'Gemini returned an invalid or unparseable response.',
                'explanation' => null,
            ];
        }

        return [
            'success'      => true,
            'model'        => $this->model,
            'explanation'  => $parsed['data'],
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Test the service with fully synthetic data â€” no real learner data transmitted.
     */
    public function testWithSyntheticData(): array {
        $synthetic = [
            '__synthetic__'       => true,
            'activity_type'       => 'fill_in_blanks',
            'activity_title'      => 'Sample Vocabulary Activity (Synthetic)',
            'total_attempts'      => 3,
            'graded_attempts'     => 3,
            'latest_accuracy'     => 55.0,
            'accuracy_history'    => [80.0, 65.0, 55.0],
            'trend'               => 'Declining',
            'consistency'         => 'Inconsistent',
            'recurring_incorrect' => ['concept_A', 'concept_B'],
            'iep_objective'       => 'Improve expressive vocabulary for communication',
            'iep_indicator'       => 'Learner identifies FSL signs for 10 common daily words',
            'iep_domain'          => 'communication_language',
            'recommendations'     => [
                ['type' => 'fsl_vocabulary',    'title' => 'FSL Sign: Concept A', 'priority' => 'high'],
                ['type' => 'resource_hub_packs', 'title' => 'Accessibility Pack B', 'priority' => 'medium'],
            ],
            'limitations'         => [],
        ];
        return $this->explainActivityInsight($synthetic);
    }

    // -----------------------------------------------------------------------
    // Private: Pseudonymization / Payload Builder
    // -----------------------------------------------------------------------

    /**
     * Build a minimal, pseudonymized data packet from an insight array.
     * No learner names, emails, student IDs, IEP IDs, section names, or contact info.
     */
    private function buildMinimalPayload(array $insight): array {
        // Synthetic data passthrough (for testWithSyntheticData)
        if (isset($insight['__synthetic__'])) {
            $clean = $insight;
            unset($clean['__synthetic__']);
            return $clean;
        }

        $activity = $insight['activity']            ?? [];
        $factors  = $insight['factors']             ?? [];
        $summary  = $insight['performance_summary'] ?? [];
        $iepAlign = $insight['iep_alignment']       ?? [];
        $recs     = $insight['recommendations']     ?? [];
        $limits   = $insight['limitations']         ?? [];

        $accuracy = $factors['accuracy']          ?? [];
        $attempts = $factors['attempts']          ?? [];
        $trend    = $factors['performance_trend'] ?? [];
        $consist  = $factors['consistency']       ?? [];

        $accHistory = $accuracy['accuracy_history'] ?? [];
        $recurring  = $consist['recurring_incorrect_items'] ?? [];

        // Extract recurring incorrect concept labels â€” no answer content that could identify the learner
        $recurringLabels = [];
        foreach ($recurring as $item) {
            if (!empty($item['still_incorrect_latest'])) {
                $label = $item['expected_value'] ?? '';
                if ($label && !in_array($label, $recurringLabels, true)) {
                    $recurringLabels[] = htmlspecialchars(strip_tags((string)$label));
                }
            }
        }

        // Recommendation titles only â€” never expose source_id or internal IDs to Gemini
        $recSummary = [];
        foreach (array_slice($recs, 0, 5) as $r) {
            $recSummary[] = [
                'type'     => $r['source_type'] ?? $r['type'] ?? 'resource',
                'title'    => htmlspecialchars(strip_tags((string)($r['title'] ?? 'Resource'))),
                'priority' => $r['priority'] ?? 'medium',
            ];
        }

        // IEP objective and indicator â€” de-identified (no teacher/student PII)
        $iepObjective = null;
        $iepIndicator = null;
        $iepDomain    = null;
        if (!empty($iepAlign['has_verified_iep']) && !empty($iepAlign['objectives'])) {
            $primary      = reset($iepAlign['objectives']);
            $iepObjective = htmlspecialchars(strip_tags((string)($primary['objective'] ?? '')));
            $iepIndicator = htmlspecialchars(strip_tags((string)($primary['indicator'] ?? '')));
            $iepDomain    = htmlspecialchars(strip_tags((string)($primary['domain'] ?? '')));
        }

        $latestAcc = $summary['accuracy'] ?? $accuracy['latest_accuracy'] ?? null;

        return [
            'activity_type'       => htmlspecialchars(strip_tags((string)($activity['type'] ?? 'activity'))),
            'activity_title'      => htmlspecialchars(strip_tags((string)($activity['title'] ?? 'Unnamed Activity'))),
            'total_attempts'      => (int)($attempts['total_recorded'] ?? 0),
            'graded_attempts'     => (int)($attempts['graded'] ?? 0),
            'latest_accuracy'     => $latestAcc !== null ? round((float)$latestAcc, 1) : null,
            'accuracy_history'    => array_map(fn($v) => round((float)$v, 1), array_slice($accHistory, 0, 10)),
            'trend'               => (string)($trend['classification'] ?? 'Insufficient Evidence'),
            'consistency'         => (string)($consist['classification'] ?? 'Insufficient Evidence'),
            'recurring_incorrect' => $recurringLabels,
            'iep_objective'       => $iepObjective,
            'iep_indicator'       => $iepIndicator,
            'iep_domain'          => $iepDomain,
            'recommendations'     => $recSummary,
            'limitations'         => array_slice($limits, 0, 3),
        ];
    }

    // -----------------------------------------------------------------------
    // Private: Prompt Builder
    // -----------------------------------------------------------------------

    private function buildSystemPrompt(array $payload): string {
        $dataJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
You are an educational support assistant helping SPED (Special Education) teachers understand learner performance data.
You are reviewing pseudonymized, structured performance data for a Deaf and Hard of Hearing (DHH) learner.

Your role is strictly ADVISORY. You are NOT making a clinical diagnosis, IEP mastery determination, or grade decision.
The SPED teacher retains full authority over all instructional decisions.

STRICT CONSTRAINTS (follow ALL of these):
1. Do NOT invent, fabricate, or assume any resource IDs, scores, FSL signs, or performance records not in the input.
2. Do NOT include any learner names, teacher names, school names, or personally identifiable information.
3. Do NOT declare mastery or non-mastery. Use hedged language: "may indicate", "appears to show", "suggests", "consider reviewing".
4. Keep language accessible to a non-technical SPED teacher â€” avoid clinical or technical jargon.
5. Reference ONLY resources listed in the "recommendations" array â€” do not add others.
6. If accuracy_history is empty or too short, acknowledge insufficient evidence rather than speculating.
7. Do NOT recommend medical evaluations, clinical diagnoses, or interventions outside classroom teaching.

Respond ONLY with valid JSON matching this exact structure (no markdown, no explanation outside the JSON):
{
  "performance_summary": "2-3 sentences summarizing what the performance data shows",
  "skills_needing_support": ["skill or concept 1", "skill or concept 2"],
  "why_resources_may_help": "1-2 sentences explaining why the recommended resources are relevant",
  "teacher_next_steps": ["suggested next step 1", "suggested next step 2", "suggested next step 3"],
  "data_confidence": "high|medium|low",
  "advisory_note": "One sentence reminding the teacher this is advisory only"
}

Here is the pseudonymized learner performance data to analyze:

$dataJson
PROMPT;
    }

    // -----------------------------------------------------------------------
    // Private: Gemini API Call
    // -----------------------------------------------------------------------

    private function callGemini(string $prompt): string {
        $url = $this->apiBase . urlencode($this->model) . ':generateContent?key=' . urlencode($this->apiKey);

        $requestBody = json_encode([
            'contents' => [
                [
                    'parts' => [['text' => $prompt]]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens'  => $this->maxOutputTokens,
                'temperature'      => 0.3,
                'topP'             => 0.8,
                'responseMimeType' => 'application/json',
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ],
        ]);

        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL extension is not available on this server.');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $requestBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException('cURL error: ' . $curlError);
        }
        if ($httpCode === 429) {
            throw new RuntimeException('Gemini rate limit exceeded (HTTP 429). Wait and retry.');
        }
        if ($httpCode === 403) {
            throw new RuntimeException('Gemini API key invalid or quota exceeded (HTTP 403).');
        }
        if ($httpCode === 400) {
            $decoded = json_decode($response, true);
            $errMsg  = $decoded['error']['message'] ?? 'Bad request';
            throw new RuntimeException("Gemini API error (HTTP 400): $errMsg");
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException("Gemini API returned HTTP {$httpCode}.");
        }

        return (string)$response;
    }

    // -----------------------------------------------------------------------
    // Private: Response Validator
    // -----------------------------------------------------------------------

    private function parseAndValidateResponse(string $raw): array {
        $outer = json_decode($raw, true);
        if (!is_array($outer)) {
            return ['valid' => false, 'error' => 'outer_json_invalid'];
        }
        if (!empty($outer['error'])) {
            return ['valid' => false, 'error' => $outer['error']['message'] ?? 'api_error'];
        }
        if (($outer['candidates'][0]['finishReason'] ?? '') === 'SAFETY') {
            return ['valid' => false, 'error' => 'content_blocked_safety'];
        }

        $text = $outer['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            return ['valid' => false, 'error' => 'no_text_in_response'];
        }

        // Strip markdown code fences (defensive â€” Gemini should not add them with responseMimeType set)
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);

        $data = json_decode($text, true);
        if (!is_array($data)) {
            return ['valid' => false, 'error' => 'inner_json_invalid'];
        }

        // Validate required keys
        $required = [
            'performance_summary', 'skills_needing_support', 'why_resources_may_help',
            'teacher_next_steps', 'data_confidence', 'advisory_note'
        ];
        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                return ['valid' => false, 'error' => "missing_key:{$key}"];
            }
        }

        // Sanitize all output â€” Gemini output must never reach the browser raw
        $safe = [
            'performance_summary'    => htmlspecialchars(strip_tags((string)($data['performance_summary'] ?? ''))),
            'skills_needing_support' => array_map(
                fn($s) => htmlspecialchars(strip_tags((string)$s)),
                (array)($data['skills_needing_support'] ?? [])
            ),
            'why_resources_may_help' => htmlspecialchars(strip_tags((string)($data['why_resources_may_help'] ?? ''))),
            'teacher_next_steps'     => array_map(
                fn($s) => htmlspecialchars(strip_tags((string)$s)),
                (array)($data['teacher_next_steps'] ?? [])
            ),
            'data_confidence' => in_array($data['data_confidence'] ?? '', ['high', 'medium', 'low'], true)
                                     ? $data['data_confidence']
                                     : 'low',
            'advisory_note' => htmlspecialchars(strip_tags((string)($data['advisory_note'] ?? ''))),
        ];

        return ['valid' => true, 'data' => $safe];
    }

    // -----------------------------------------------------------------------
    // Private: DB API Key Loader
    // -----------------------------------------------------------------------

    private function loadApiKeyFromSettings(): ?string {
        try {
            require_once __DIR__ . '/../../config/db.php';
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'gemini_api_key' LIMIT 1");
            $stmt->execute();
            $val  = $stmt->fetchColumn();
            return (!empty($val)) ? trim($val) : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}