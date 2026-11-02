<?php
declare(strict_types=1);
// Real publisher, analyzer, recommender, and view; entirely in-memory PDO fixtures.
require_once __DIR__ . '/../app/Models/ResourceHubModel.php';
require_once __DIR__ . '/../app/Services/ResourceRecommendationService.php';

class TagWorkflowStatement extends PDOStatement {
    private $executeRows;
    private array $rows = [];
    public function __construct(callable $executeRows) { $this->executeRows = $executeRows; }
    public function execute(?array $params = null): bool { $this->rows = ($this->executeRows)($params ?? []); return true; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->rows; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $orientation = PDO::FETCH_ORI_NEXT, int $offset = 0): mixed { return $this->rows[0] ?? false; }
    public function fetchColumn(int $column = 0): mixed { return 0; }
}
class TagWorkflowDatabase extends PDO {
    public array $packs = [], $pages = [], $activities = [], $writes = [];
    public array $ratings = ['iep'=>'B', 'report_card'=>'AP'];
    public array $activity = ['id'=>77,'title'=>'Basic Addition Within 10','activity_type'=>'multiple_choice',
        'activity_data'=>'{"learning_competency":"Add numbers within 10"}', 'max_score'=>10,
        'lesson_plan_id'=>20,'lesson_title'=>'Mathematics','score_threshold'=>70,'lp_iep_id'=>8];
    public array $attempt = ['id'=>500,'attempt_number'=>1,'score'=>4,'max_score'=>10,'accuracy_percentage'=>40,
        'is_remedial_attempt'=>0,'needs_remediation'=>1,'attempted_at'=>'2026-11-02'];
    public array $log = ['attempt_id'=>500,'question_index'=>0,'selected_value'=>'3','correct_value'=>'4','is_correct'=>0];
    public array $links = [];
    private bool $transaction = false;
    public function __construct() {}
    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function commit(): bool { $this->transaction = false; return true; }
    public function rollBack(): bool { $this->transaction = false; return true; }
    public function lastInsertId(?string $name = null): string|false { return (string)array_key_last($this->packs); }
    public function exec(string $statement): int|false { throw new RuntimeException('Schema/direct writes forbidden'); }
    public function prepare(string $sql, array $options = []): PDOStatement|false {
        return new TagWorkflowStatement(function (array $params) use ($sql): array {
            if (str_contains($sql, 'INSERT INTO resource_hub_packs')) {
                $this->writes[] = 'pack'; $id = 101 + count($this->packs);
                $this->packs[$id] = ['id'=>$id,'title'=>$params['title'],'description'=>$params['description'],
                    'pdsp_domain'=>$params['domain'],'learning_level'=>$params['level'],
                    'learning_competency'=>$params['competency'],'tags'=>$params['tags'],
                    'created_by'=>$params['created_by'],'status'=>'published','fsl_supported'=>0];
                return [];
            }
            if (str_contains($sql, 'INSERT INTO resource_hub_pages')) {
                $this->writes[] = 'page'; $this->pages[] = $params; return [];
            }
            if (str_contains($sql, 'INSERT INTO resource_hub_activities')) {
                $this->writes[] = 'activity'; $this->activities[] = $params; return [];
            }
            if (!preg_match('/^\s*SELECT/i', $sql)) throw new RuntimeException('Unexpected write: '.$sql);
            if (str_contains($sql, 'FROM csrf_tokens')) return [['token'=>'fixture-token']];
            if (str_contains($sql, 'FROM system_settings') || str_contains($sql,'SELECT 1 FROM student_records')) return [];
            if (str_contains($sql, 'FROM lms_activities la')) return [$this->activity];
            if (str_contains($sql, 'FROM lms_activity_attempts')) return [$this->attempt];
            if (str_contains($sql, 'FROM activity_attempt_log')) return str_contains($sql, 'COUNT(*)') ? [] : [$this->log];
            if (str_contains($sql, 'FROM iep_step_lesson_plans')) return $this->links;
            if (str_contains($sql, 'FROM fsl_vocabulary')) {
                return in_array($params['w'] ?? '', ['mother','nanay'], true)
                    ? [['id'=>97,'word'=>'mother','category'=>'Family','description'=>'Nanay / Ina - Mother','video_path'=>'videos/fsl/53.mov']] : [];
            }
            if (str_contains($sql, 'FROM resource_hub_pages rp JOIN')) return array_map(fn($page,$index) => array_merge(['id'=>$index+1,'title'=>'','content'=>'','guide_questions'=>''],$page),$this->pages,array_keys($this->pages));
            if (str_contains($sql, 'FROM resource_hub_activities ra JOIN')) return $this->activities;
            if (str_contains($sql, 'FROM resource_hub_packs')) {
                return array_values(array_filter($this->packs, function ($pack) use ($sql, $params) {
                    if (isset($params['id'])) return $pack['id'] === $params['id'];
                    if (($pack['status'] ?? '') !== 'published' || (int)$pack['id'] <= 0 || trim($pack['title']) === '') return false;
                    return !str_contains($sql, 'EXISTS') || count(array_filter($this->pages, fn($p) => $p['pack_id']===$pack['id'])) > 0;
                }));
            }
            throw new RuntimeException('Unexpected query: '.$sql);
        });
    }
}
class TagWorkflowLesson extends LessonPlanModel {
    public function __construct() {}
    public function findById($id) { return ['id'=>$id,'iep_id'=>8,'created_by'=>11,'title'=>'Basic Addition Within 10','pdsp_domain'=>'perceptuo_cognitive']; }
    public function getPagesByLessonPlan(int $lessonPlanId): array { return [['page_number'=>1,'title'=>'Addition practice','content'=>'<p>1 + 1 = 2</p>']]; }
    public function getActivitiesByIepId($iepId) { return []; }
    public function getMaterials($lessonPlanId) { return []; }
}
$checks = 0;
function tagCheck(bool $ok, string $message): void { global $checks; $checks++; if (!$ok) throw new RuntimeException($message); }
function tagReject(callable $action, string $message): void {
    try { $action(); } catch (InvalidArgumentException $e) { tagCheck(true, $message); return; }
    tagCheck(false, $message);
}
$db = new TagWorkflowDatabase();
$hubReflection = new ReflectionClass(ResourceHubModel::class);
$hub = $hubReflection->newInstanceWithoutConstructor();
$hubReflection->getProperty('db')->setValue($hub, $db);
$hubReflection->getProperty('lpModel')->setValue($hub, new TagWorkflowLesson());
$databaseClass = new ReflectionClass(Database::class);
$singleton = $databaseClass->newInstanceWithoutConstructor();
$databaseClass->getProperty('connection')->setValue($singleton, $db);
$databaseClass->getProperty('instance')->setValue(null, $singleton);
$meta = ['title'=>'Basic Addition Within 10','description'=>'Practice adding small groups.',
    'learning_area'=>'Mathematics','learning_competency'=>'Add numbers within 10'];
$suggested = ResourceTagService::suggest($meta);
tagCheck(in_array('addition', $suggested, true) && in_array('within 10', $suggested, true), 'A/B: Addition skill and range are suggested');
tagCheck(in_array('mathematics', $suggested, true), 'Learning area contributes to suggestions');
$meta['tags'] = implode(', ', array_merge($suggested, ['number bonds','ADDITION']));
tagReject(fn() => $hub->shareLessonToHub(20, 11, 'Teacher A', $meta), 'Unapproved tags cannot publish');
tagCheck(!$db->writes, 'Rejected approval performs no writes');
$meta['tags_approved'] = true;
$packId = $hub->shareLessonToHub(20, 11, 'Teacher A', $meta);
$pack = $hub->getPackById($packId);
tagCheck($pack['status'] === 'published' && $pack['created_by'] === 11, 'C/D: Teacher A approved metadata is published');
tagCheck(str_contains($pack['tags'], 'number bonds') && substr_count($pack['tags'], 'addition,') === 1, 'Teacher edits persist and duplicate tags are removed');
tagCheck($hub->getAllPacks()[0]['id'] === $packId, 'Published resource is available in existing shared browser');
$originalRatings = $db->ratings; $publishWrites = $db->writes;
$analyzer = new LearningGapAnalyzer($db);
$service = new ResourceRecommendationService($db, $analyzer);
$result = $service->recommendForActivity(22, 77); // Teacher B learner, not pack author.
tagCheck(array_column($result['recommendations'], 'source_id') === [$packId], 'E/F: Another learner with verified addition difficulty receives the resource');
tagCheck(str_contains($result['recommendations'][0]['evidence']['performance_evidence'], 'instructional content'), 'Specific skill evidence explains the recommendation');
$learningInsights = [$result]; $basePath = '/Signedd/public';
ob_start(); require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php'; $html = ob_get_clean();
tagCheck(str_contains($html, '/resource-hub/preview/'.$packId) && str_contains($html, 'Why it matches:'), 'G: Correct direct Preview link and reason are shown');
tagCheck(str_contains($html, '/resource-hub?tab=browse'), 'General Browse action stays separate');
// Published but unrelated mathematics/language resources and unavailable packs.
foreach ([
    ['id'=>201,'title'=>'Basic Subtraction Within 10','tags'=>'mathematics, subtraction'],
    ['id'=>202,'title'=>'Reading Comprehension','tags'=>'language, reading comprehension'],
    ['id'=>203,'title'=>'Addition Within 100','tags'=>'mathematics, addition, within 100'],
    ['id'=>204,'title'=>'Addition','status'=>'archived'],
    ['id'=>205,'title'=>'Addition','status'=>'draft'],
    ['id'=>206,'title'=>'Addition','no_pages'=>true],
    ['id'=>0,'title'=>'Addition'],
    ['id'=>207,'title'=>''],
] as $extra) {
    $extra = array_merge($pack, ['learning_competency'=>''], $extra);
    $db->packs[$extra['id']] = $extra;
    if (empty($extra['no_pages'])) $db->pages[] = ['pack_id'=>$extra['id'],'title'=>$extra['title'],'content'=>$extra['title']];
}
$result = $service->recommendForActivity(22, 77);
tagCheck(array_column($result['recommendations'], 'source_id') === [$packId], 'Unrelated skills/ranges, unpublished, invalid and empty resources are excluded');
$db->attempt['score'] = 10; $db->log['is_correct'] = 1;
tagCheck(!$service->recommendForActivity(22, 77)['recommendations'], 'No scored gap means no recommendations');
$db->attempt['score'] = null;
tagCheck(!$service->recommendForActivity(22, 77)['recommendations'], 'Ungraded means no recommendations');
$db->attempt['score'] = 4; $db->log['is_correct'] = 0;
$db->activity['title'] = 'Reading'; $db->activity['activity_data'] = '{"learning_competency":"Reading comprehension"}';
$db->log['correct_value'] = 'A';
$reading = $service->recommendForActivity(22,77);
tagCheck(array_column($reading['recommendations'], 'source_id') === [202], 'Reading competency finds reading resources and rejects mathematics');
tagCheck(in_array('letter sounds', ResourceTagService::suggest(['title'=>'Letter Sounds','learning_area'=>'Reading']), true), 'Reading suggestions use specific phonics skill');
$db->activity['activity_data'] = '{}'; $db->activity['title'] = 'Mathematics'; $db->log['correct_value'] = '4';
tagCheck(!$service->recommendForActivity(22,77)['recommendations'], 'Shared subject and numerical answer alone cannot justify a resource');
$db->activity['title'] = 'Practice'; $db->activity['activity_data'] = '{}';
$db->links = [['iep_step_id'=>1,'iep_id'=>8,'step_number'=>1,'step_domain'=>'perceptuo_cognitive',
    'step_objective'=>'Basic addition within 10','pdsp_indicator_text'=>'Teacher indicator','instructional_evaluation'=>'B']];
$linked = $service->recommendForActivity(22,77);
tagCheck(array_column($linked['recommendations'], 'source_id') === [$packId], 'Verified linked objective supplies a specific skill');
$db->links[0]['iep_id'] = 999;
tagCheck(!$service->recommendForActivity(22,77)['recommendations'], 'Unverified IEP links cannot drive recommendations');
$db->links = []; $db->activity['title'] = 'FSL family quiz'; $db->log['correct_value'] = 'Nanay (Mother)';
$fsl = $service->recommendForActivity(22,77);
tagCheck(array_column($fsl['recommendations'], 'source_id') === [97], 'Bilingual Nanay (Mother) resolves to the existing mother video');
$learningInsights = [$fsl];
ob_start(); require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php'; $fslHtml = ob_get_clean();
tagCheck(str_contains($fslHtml, '/fsl-dictionary?search=mother'), 'FSL Browse action searches the actual dictionary word rather than the bilingual label');
tagReject(fn() => ResourceTagService::approvedTags(['tags'=>'addition','tags_approved'=>'true']), 'Approval must be explicit boolean');
tagReject(fn() => ResourceTagService::approvedTags(['tags'=>'','tags_approved'=>true]), 'Empty approved tag list is rejected');
tagReject(fn() => ResourceTagService::normalizeTags(str_repeat('x',61)), 'Oversized tag is rejected');
tagCheck($db->ratings === $originalRatings && $db->writes === $publishWrites, 'Recommendations never write IEP ratings, report cards, assignments or clones');
putenv('GEMINI_API_KEY=');
tagCheck(ResourceTagService::suggest($meta) === $suggested, 'Gemini unavailable: deterministic local suggestions remain available');
echo "Resource tag workflow: {$checks} checks passed; no real database, files, or network modified.\n";
