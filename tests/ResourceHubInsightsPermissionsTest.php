<?php
declare(strict_types=1);
// Exercise real controller permission/publication checks in isolated CLI children.
// Constructors are bypassed so schema self-healing and real connections cannot run.
require_once __DIR__ . '/../app/Controllers/IEPImplementationController.php';

class InsightsHubFixture extends ResourceHubModel {
    public array $calls = [];
    public function __construct() {}
    public function getPackById(int $id): ?array {
        $this->calls[] = 'pack';
        if ($id === 0) return null;
        return ['id' => $id, 'title' => 'Hello signs', 'created_by' => 11, 'status' => $id === 42 ? 'published' : 'archived'];
    }
    public function getPackPages(int $packId): array { $this->calls[] = 'pages'; return []; }
    public function getPackActivities(int $packId): array { $this->calls[] = 'activities'; return []; }
    public function getManageableIepsForTeacher(int $teacherId): array { $this->calls[] = 'ieps'; return []; }
    public function shareLessonToHub(int $lessonPlanId, int $teacherId, string $teacherName, array $meta): int {
        $this->calls[] = 'publish';
        if (ResourceTagService::approvedTags($meta) !== 'addition') throw new RuntimeException('Unexpected approved tags');
        return 42;
    }
}
class InsightsShareLessonFixture extends LessonPlanModel {
    public function __construct() {}
    public function findById($id) {
        return $id === 0 ? null : ['id'=>$id,'created_by'=>$id===43 ? 99 : 1,'title'=>'Addition','pdsp_domain'=>'perceptuo_cognitive'];
    }
    public function getPagesByLessonPlan(int $lessonPlanId): array { return [['content'=>'<p>Addition practice</p>']]; }
}

if (($argv[1] ?? '') === '--case') {
    $role = $argv[2]; $action = $argv[3]; $id = (int)$argv[4];
    $_SESSION = ['user_id' => 1, 'role' => $role];
    $_GET = ['format' => 'json'];
    $_POST = ['title'=>'Basic Addition Within 10','learning_area'=>'Mathematics', 'tags'=>'addition', 'tags_approved'=>$action !== 'share-unapproved'];
    $fixture = new InsightsHubFixture();
    $reflection = new ReflectionClass(IEPImplementationController::class);
    $controller = $reflection->newInstanceWithoutConstructor();
    foreach (['userId' => 1, 'userRole' => $role, 'basePath' => '/Signedd/public', 'hubModel' => $fixture,
        'model'=>new InsightsShareLessonFixture()] as $property => $value) {
        $reflection->getProperty($property)->setValue($controller, $value);
    }
    http_response_code(200);
    ob_start();
    register_shutdown_function(function () use ($fixture): void {
        $body = ob_get_clean();
        echo json_encode(['status' => http_response_code(), 'body' => json_decode($body, true),
            'calls' => $fixture->calls, 'error' => $_SESSION['error'] ?? null]);
    });
    if ($action === 'preview') $controller->previewResourcePack($id);
    elseif ($action === 'suggest') $controller->suggestResourceTags();
    elseif (str_starts_with($action, 'share')) $controller->shareToResourceHub($id);
    else $controller->resourceHub();
    exit;
}

$checks = 0;
function hubCheck(bool $condition, string $message): void {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function hubCase(string $role, string $action = 'preview', int $id = 42): array {
    $process = proc_open([PHP_BINARY, __FILE__, '--case', $role, $action, (string)$id],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Could not launch isolated permission check');
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0 || $error !== '') throw new RuntimeException('Permission check failed: ' . $error);
    return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
}
foreach (['sped_teacher', 'teacher', 'master_teacher', 'admin'] as $role) {
    $result = hubCase($role);
    hubCheck($result['status'] === 200 && ($result['body']['success'] ?? false), "$role can preview published resources");
    hubCheck($result['calls'] === ['pack', 'pages', 'activities', 'ieps'], 'Preview performs read operations only');
    $suggested = hubCase($role, 'suggest');
    hubCheck($suggested['status'] === 200 && in_array('addition', $suggested['body']['tags'], true) && !$suggested['calls'], 'Authorized teacher can suggest without publishing');
    $shared = hubCase($role, 'share');
    hubCheck($shared['status'] === 200 && ($shared['body']['pack_id'] ?? 0) === 42, 'Authorized owner can publish approved tags');
}
foreach (['parent', 'student', 'user', 'principal', ''] as $role) {
    foreach (['preview', 'browse'] as $action) {
        $result = hubCase($role, $action);
        // Existing controllers redirect denied requests to login (Location sets 302).
        hubCheck($result['status'] === 302 && $result['error'] !== null && $result['body'] === null && !$result['calls'],
            'Unauthorized role is redirected without reading resources through ' . $action);
    }
    foreach (['suggest','share'] as $action) {
        $result = hubCase($role, $action);
        hubCheck($result['status'] === 403 && !$result['calls'], 'Unauthorized role cannot suggest or publish resources');
    }
}
hubCheck(hubCase('sped_teacher', 'share-unapproved')['status'] === 422, 'Publication rejects unapproved tags');
$foreign = hubCase('sped_teacher', 'share',43);
hubCheck($foreign['status'] === 403 && !$foreign['calls'], 'Teacher cannot publish another teacher lesson');
$missing = hubCase('sped_teacher', 'share',0);
hubCheck($missing['status'] === 404 && !$missing['calls'], 'Missing lesson cannot be published');
foreach ([0, 43] as $id) {
    $result = hubCase('sped_teacher', 'preview', $id);
    hubCheck($result['body'] === null && $result['calls'] === ['pack'] && $result['error'] !== null,
        'Missing/archived resource content is not exposed');
}
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
foreach (['', '/preview/\{id\}'] as $suffix) {
    hubCheck((bool)preg_match("~route\('GET',\s*'/iep/implementation/resource-hub" . $suffix . "'.*'iep.implement'~", $routes),
        'Resource Hub route requires iep.implement permission');
}
hubCheck((bool)preg_match("~route\('POST',\s*'/iep/implementation/resource-hub/suggest-tags'.*'iep.implement'~", $routes),
    'Suggestion route retains iep.implement authorization');
echo "Resource Hub permissions: {$checks} checks passed; no database connection or network used.\n";
