<?php
declare(strict_types=1);
// Synthetic view rendering only; no database, provider, HTTP, or record writes.
if (($argv[1] ?? '') === '--render') {
    $reviewOnly = ($argv[2] ?? '') === 'admin';
    define('BASE_PATH', '/Signedd/public');
    $_SERVER['REQUEST_URI'] = BASE_PATH . '/iep/implementation/resource-hub/preview/77';
    $_SESSION = ['user_id' => $reviewOnly ? 1 : 7, 'role' => $reviewOnly ? 'admin' : 'sped_teacher', 'user_name' => 'Synthetic reviewer'];
    $basePath = BASE_PATH;
    $pack = ['id' => 77, 'title' => 'Synthetic pending pack', 'pdsp_domain' => 'communication_language', 'learning_level' => 'SPED', 'fsl_supported' => 0, 'fsl_sign_count' => 0, 'author_name' => 'Synthetic creator', 'learning_competency' => 'Greetings', 'mainstream_mastery_url' => null];
    $pages = [['title' => 'Greeting slide', 'content' => '<p>Hello</p>', 'media_type' => 'none', 'media_path' => null, 'guide_questions' => null]];
    $activities = [['title' => 'Practice', 'activity_type' => 'multiple_choice', 'instructions' => 'Choose one', 'activity_data' => '[]']];
    $myIeps = [];
    require __DIR__ . '/../app/Views/iep_implementation/resource_hub_preview.php';
    exit;
}
function renderReviewPreview(string $role): string {
    $pipes = [];
    $process = proc_open([PHP_BINARY, __FILE__, '--render', $role], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Preview fixture failed to start');
    fclose($pipes[0]);
    $html = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $errors = stream_get_contents($pipes[2]); fclose($pipes[2]);
    if (proc_close($process) !== 0 || $errors !== '') throw new RuntimeException('Preview fixture failed: ' . $errors);
    return $html;
}
$admin = renderReviewPreview('admin');
$teacher = renderReviewPreview('teacher');
if (!str_contains($admin, 'Admin review preview') || !str_contains($admin, 'Back to Finance review') || !str_contains($admin, 'Greeting slide') || !str_contains($admin, 'Practice')) throw new RuntimeException('Admin cannot inspect synthetic pack content');
if (str_contains($admin, 'id="useAsTemplateModal"') || str_contains($admin, 'id="targetIepSelect"')) throw new RuntimeException('Admin review must not show learner adaptation controls');
if (!str_contains($teacher, 'id="useAsTemplateModal"')) throw new RuntimeException('Teacher template flow must remain available');
echo "PASS: admin review preview is read-only; teacher template controls remain available.\n";
