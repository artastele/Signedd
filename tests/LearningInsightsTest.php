<?php
declare(strict_types=1);
// Entirely synthetic fixtures: no PDO connection, bootstrap, migrations, or network.
require_once __DIR__ . '/../app/Services/ResourceRecommendationService.php';

class InsightsStatement extends PDOStatement {
    private array $rows;
    public function __construct(array $rows) { $this->rows = $rows; }
    public function execute(?array $params = null): bool { return true; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->rows; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $orientation = PDO::FETCH_ORI_NEXT, int $offset = 0): mixed { return $this->rows[0] ?? false; }
    public function fetchColumn(int $column = 0): mixed { return 0; }
}
class InsightsDatabase extends PDO {
    public array $attempts = [];
    public array $logs = [];
    public array $vocabulary = [];
    public array $iepEvaluations = [['id'=>1, 'instructional_evaluation'=>'B']];
    public array $reportCardRatings = [['domain'=>'communication_language', 'indicator_text'=>'Teacher indicator', 'quarter'=>1, 'rating'=>'B']];
    public int $writeAttempts = 0;
    public function __construct() {}
    public function exec(string $statement): int|false { $this->writeAttempts++; throw new RuntimeException('Database writes forbidden'); }
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
        return $this->prepare($query);
    }
    public function prepare(string $query, array $options = []): PDOStatement|false {
        if (!preg_match('/^\s*SELECT/i', $query)) {
            $this->writeAttempts++;
            throw new RuntimeException('Non-read query forbidden');
        }
        if (str_contains($query, 'FROM csrf_tokens')) return new InsightsStatement([['token'=>'synthetic-test-token']]);
        if (str_contains($query, 'FROM student_quarterly_ratings')) return new InsightsStatement($this->reportCardRatings);
        if (str_contains($query, 'FROM iep_steps')) return new InsightsStatement($this->iepEvaluations);
        if (str_contains($query, 'FROM lms_activities la')) return new InsightsStatement([[
            'id'=>7, 'title'=>'Greetings', 'activity_type'=>'multiple_choice', 'activity_data'=>'{}',
            'max_score'=>10, 'lesson_plan_id'=>3, 'lesson_title'=>'Greetings', 'score_threshold'=>70, 'lp_iep_id'=>1,
        ]]);
        if (str_contains($query, 'FROM lms_activity_attempts')) return new InsightsStatement($this->attempts);
        if (str_contains($query, 'FROM activity_attempt_log')) return new InsightsStatement($this->logs);
        if (str_contains($query, 'FROM iep_step_lesson_plans')) return new InsightsStatement([]);
        if (str_contains($query, 'FROM fsl_vocabulary')) return new InsightsStatement($this->vocabulary);
        if (str_contains($query, 'FROM resource_hub_pages rp JOIN')) return new InsightsStatement([
            ['id'=>9,'pack_id'=>42,'title'=>'Hello practice','content'=>'<span data-word="hello">Hello</span>','guide_questions'=>''],
        ]);
        if (str_contains($query, 'FROM resource_hub_activities ra JOIN')) return new InsightsStatement([]);
        if (str_contains($query, 'FROM resource_hub_packs')) return new InsightsStatement([
            ['id'=>42,'title'=>'Hello signs','description'=>'Practice','pdsp_domain'=>'communication_language','learning_level'=>'Primary','learning_competency'=>'hello','tags'=>'greetings','fsl_supported'=>1,'status'=>'published'],
            ['id'=>43,'title'=>'Counting','description'=>'hello','pdsp_domain'=>'communication_language','learning_level'=>'Primary','learning_competency'=>'numbers','tags'=>'mathematics','fsl_supported'=>1,'status'=>'published'],
            ['id'=>44,'title'=>'Shellology','description'=>'','pdsp_domain'=>'communication_language','learning_level'=>'Primary','learning_competency'=>'shellology','tags'=>'','fsl_supported'=>0,'status'=>'published'],
        ]);
        throw new RuntimeException('Unexpected query: ' . $query);
    }
}
$checks = 0;
function verify(bool $ok, string $message): void {
    global $checks;
    $checks++;
    if (!$ok) throw new RuntimeException($message);
}
foreach ([[100,'P'],[90,'P'],[89.999,'AP'],[75,'AP'],[74.999,'D'],[50,'D'],[49.999,'B'],[0,'B'],[null,null],[-1,null],[101,null]] as [$score,$expected]) {
    verify(LearningGapAnalyzer::suggestedRating($score) === $expected, 'Rating boundary');
}
$db = new InsightsDatabase();
// The existing CSRF helper uses the singleton. Replace it before any view render,
// so neither its token lookup nor the self-healing Database constructor can run.
$databaseClass = new ReflectionClass(Database::class);
$fakeSingleton = $databaseClass->newInstanceWithoutConstructor();
$connectionProperty = $databaseClass->getProperty('connection');
$connectionProperty->setValue($fakeSingleton, $db);
$instanceProperty = $databaseClass->getProperty('instance');
$instanceProperty->setValue(null, $fakeSingleton);
$analyzer = new LearningGapAnalyzer($db);
$service = new ResourceRecommendationService($db, $analyzer);
$storedIepBefore = $db->query('SELECT instructional_evaluation FROM iep_steps')->fetchAll();
$storedReportBefore = $db->query('SELECT rating FROM student_quarterly_ratings')->fetchAll();
$reportCardView = __DIR__ . '/../app/Views/report-card/sf9.php';
$reportCardHashBefore = hash_file('sha256', $reportCardView);
function attempt(int $id, mixed $score): array {
    return ['id'=>$id,'attempt_number'=>$id,'score'=>$score,'max_score'=>10,'accuracy_percentage'=>99,
        'attempted_at'=>'2026-01-01', 'is_remedial_attempt'=>0,'needs_remediation'=>0];
}
function item(int $id, int $correct = 0, string $expected = 'hello'): array {
    return ['attempt_id'=>$id,'question_index'=>0,'selected_value'=>$correct ? $expected : 'goodbye','correct_value'=>$expected,'is_correct'=>$correct];
}
foreach ([[10,'P'],[9,'P'],[8,'AP'],[7.5,'AP'],[7,'D'],[5,'D'],[4,'B'],[0,'B'],[null,null]] as [$rawScore,$rating]) {
    $db->attempts = [attempt(1, $rawScore)];
    $result = $service->recommendForActivity(1, 7);
    verify($result['suggested_rating'] === $rating, 'Activity suggestion uses actual score in every proficiency range');
}
$db->attempts = [];
$result = $service->recommendForActivity(1, 7);
verify($result['suggested_rating'] === null && !$result['recommendations'], 'No attempts means no suggestion or recommendation');
$db->attempts = [attempt(1, 5)];
$db->logs = [item(1), item(1)]; // Duplicate log rows must not invent multiple attempts.
$a = $analyzer->analyzeActivity(1, 7);
$r = $service->recommendForActivity(1, 7, $a);
verify($r['suggested_rating'] === 'D' && $r['performance_summary']['accuracy'] == 50, 'Raw score overrides stale cached percentage');
verify(str_contains($r['observation'], 'Only one graded attempt'), 'One attempt has no trend');
verify(!$a['factors']['consistency']['recurring_incorrect_items'], 'Duplicate logs do not establish recurrence');
verify(array_column($r['recommendations'], 'source_id') === [42], 'Only exact skill pack matches, not shared domain, description, or substring');
$db->vocabulary = [['id'=>88,'word'=>'hello','category'=>'Greetings','video_path'=>'/uploads/hello.mp4','description'=>'Hello sign']];
$r = $service->recommendForActivity(1, 7);
verify(array_column($r['recommendations'], 'source_id') === [88,42], 'Existing exact FSL skill video is recommended');
$db->vocabulary[0]['video_path'] = '';
$r = $service->recommendForActivity(1, 7);
verify(array_column($r['recommendations'], 'source_id') === [42], 'Dictionary entry without video is not presented as a video');
$db->vocabulary = [];
$db->attempts[] = attempt(2, 6);
$db->logs[] = item(2);
$a = $analyzer->analyzeActivity(1, 7);
verify(count($a['factors']['consistency']['recurring_incorrect_items']) === 1, 'Two recorded incorrect attempts establish recurrence');
$db->attempts[] = attempt(3, 9);
$db->logs[] = item(3, 1);
$a = $analyzer->analyzeActivity(1, 7);
$r = $service->recommendForActivity(1, 7, $a);
verify(!$r['recommendations'], 'Resolved errors do not trigger resources');
verify(str_contains($r['observation'], 'improved across 3'), 'Improvement uses comparable recorded results');
$db->attempts[] = attempt(4, null);
$r = $service->recommendForActivity(1, 7);
verify($r['suggested_rating'] === null && $r['performance_summary']['accuracy'] === null, 'Latest ungraded result does not inherit prior rating');
$db->attempts = [attempt(1, -1), attempt(2, 11), attempt(3, null)];
$db->logs = [];
$r = $service->recommendForActivity(1, 7);
verify($r['suggested_rating'] === null && !$r['recommendations'], 'Invalid and ungraded scores have no rating or recommendations');
$db->attempts = [attempt(1, 4), attempt(2, 8)];
$a = $analyzer->analyzeActivity(1, 7);
verify($a['factors']['performance_trend']['classification'] === LearningGapAnalyzer::INSUFFICIENT, 'Missing item identity does not invent comparable trend');
$db->logs = [item(1), item(2, 0, 'different skill')];
$a = $analyzer->analyzeActivity(1, 7);
verify(!$a['factors']['consistency']['recurring_incorrect_items'], 'Changed question identity cannot establish recurring mistakes');
verify($a['factors']['performance_trend']['classification'] === LearningGapAnalyzer::INSUFFICIENT, 'Changed question identity cannot establish a trend');
$db->attempts = [attempt(1, 9), attempt(2, 6)];
$db->logs = [item(1, 1), item(2)];
$r = $service->recommendForActivity(1, 7);
verify($r['performance_summary']['trend'] === LearningGapAnalyzer::TREND_DECLINING, 'Decline uses comparable actual scores');
verify(str_contains($r['observation'], 'Scores decreased across 2'), 'Decline observation stays factual');
$db->logs = [item(1, 1, 'unmatched skill'), item(2, 0, 'unmatched skill')];
$r = $service->recommendForActivity(1, 7);
verify(!$r['recommendations'], 'Unrelated existing resources are rejected');
$learningInsights = [$r]; $basePath = '/Signedd/public';
ob_start();
require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php';
$noMatchHtml = ob_get_clean();
verify(str_contains($noMatchHtml, 'No verified resource match yet'), 'No-match state is visible');
// Render the actual teacher view with synthetic results, without application bootstrap.
$db->attempts = [attempt(1, 5)]; $db->logs = [item(1)];
$learningInsights = [$service->recommendForActivity(1, 7)];
$basePath = '/Signedd/public';
ob_start();
require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php';
$html = ob_get_clean();
verify(str_contains($html, '/Signedd/public/iep/implementation/resource-hub/preview/42'), 'Resource opens existing authorized preview route');
verify(str_contains($html, '<details') && str_contains($html, 'Suggested Rating:'), 'History collapsed and rating labeled');
verify(str_contains($html, 'teacher determines final IEP') && str_contains($html, 'does not establish practical FSL'), 'Teacher authority and task limitations visible');
verify(str_contains($html, '5 / 10 (50%)'), 'Activity card displays the actual numerical score');
verify(str_contains($html, 'Expected answer: hello'), 'Teacher can identify the item needing practice');
verify(str_contains($html, '?tab=browse') && str_contains($html, 'Browse Existing Resources'), 'Browse action opens the existing shared resource tab');
verify(str_contains($html, 'Preview Resource'), 'Pack recommendation has an explicit preview action');
verify(strpos($html, 'Recorded attempts:') > strpos($html, '<details'), 'Detailed metrics remain expandable');
$db->attempts = [attempt(1, null)];
$learningInsights = [$service->recommendForActivity(1, 7)];
ob_start();
require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php';
$ungradedHtml = ob_get_clean();
verify(str_contains($ungradedHtml, 'Ungraded') && str_contains($ungradedHtml, 'No suggested rating'), 'Ungraded activity card has no rating');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
verify((bool)preg_match("~route\('GET',\s*'/iep/implementation/resource-hub/preview/\{id\}'.*'iep.implement'~", $routes), 'Preview route retains authorization');
verify($db->query('SELECT instructional_evaluation FROM iep_steps')->fetchAll() === $storedIepBefore,
    'Teacher IEP evaluations stay unchanged after all suggestions and view renders');
verify($db->query('SELECT rating FROM student_quarterly_ratings')->fetchAll() === $storedReportBefore,
    'Stored final report card ratings stay unchanged after all suggestions and view renders');
verify(hash_file('sha256', $reportCardView) === $reportCardHashBefore, 'Final SF9 report card template remains unchanged');
verify($db->writeAttempts === 0, 'Analysis, recommendations, and teacher view never attempt database writes');
echo "Learning Insights: {$checks} checks passed; no database connection or network used.\n";
