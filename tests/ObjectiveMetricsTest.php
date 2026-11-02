<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/Models/ObjectiveMetricsModel.php';

$checks = 0;
function check($condition, string $message): void {
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
$empty = ['plans'=>0, 'dll'=>0, 'classroom_finalized'=>0, 'legacy_finalized'=>0,
    'proficient'=>0, 'resources'=>0, 'certificates'=>0, 'signed_ieps'=>0, 'sip'=>false];
$pilot = array_replace($empty, ['plans'=>16, 'certificates'=>1, 'signed_ieps'=>5, 'sip'=>true]);
$m = ObjectiveMetricsModel::calculate($pilot);
check($m['so1']['score'] == 87.5 && !$m['so1']['passed'], '87.5 is readiness, not an approved individual pass');
check($m['so2']['score'] == 75 && !$m['so2']['passed'], '75 is readiness, not an approved individual pass');
check(!$m['so1']['evaluated'] && !$m['so2']['evaluated'], 'Scheduled/missing COT must not fabricate evaluations');
check($m['so1']['met'] === 3 && $m['so1']['status'] === 'Pending Evidence', 'All four SO1 criteria remain required');
$certOnly = ObjectiveMetricsModel::calculate(array_replace($empty, ['certificates'=>1]));
check(!$certOnly['so2']['passed'] && !$certOnly['so2']['evaluated'], 'A certificate alone cannot establish adoption');
$complete = ObjectiveMetricsModel::calculate(array_replace($pilot, ['classroom_finalized'=>1, 'proficient'=>1]));
check($complete['so1']['passed'] && $complete['so2']['passed'], 'Existing complete evidence qualifies automatically');
$incomplete = ObjectiveMetricsModel::calculate(array_replace($empty, ['classroom_finalized'=>1, 'proficient'=>1]));
check($incomplete['so1']['status'] === 'Not compliant' && $incomplete['so2']['status'] === 'Not adopted', 'Existing assessed failures must remain failures');
$legacy = ObjectiveMetricsModel::calculate(array_replace($pilot, ['legacy_finalized'=>1]));
check($legacy['so1']['passed'] && !$legacy['so2']['evaluated'], 'Legacy COT counts consistently for SO1; no invented SO2 rating');
$dll = ObjectiveMetricsModel::calculate(array_replace($pilot, ['plans'=>0, 'dll'=>1, 'resources'=>1, 'legacy_finalized'=>1]));
check($dll['so1']['passed'] && $dll['so1']['score'] == 100, 'Traditional DLL evidence supported consistently');
$zero = ObjectiveMetricsModel::aggregate([], 'so1');
check($zero['rate'] === null && $zero['evaluated'] === 0, 'No evaluations means unavailable, not zero percent');
$summary = ObjectiveMetricsModel::aggregate([$m, $complete, $incomplete], 'so1');
check($summary === ['evaluated'=>2, 'passed'=>1, 'pending'=>1, 'rate'=>50.0], 'Aggregate denominator excludes pending schools and includes assessed failures');
// Render only objective fragments; never bootstrap application models or migrations.
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
try {
    $allSysSchools = [['id'=>1, 'school_name'=>'Test <School>']];
    $objectiveSchools = [1=>$m];
    $so1Summary = ObjectiveMetricsModel::aggregate($objectiveSchools, 'so1');
    $so2Summary = ObjectiveMetricsModel::aggregate($objectiveSchools, 'so2');
    $so1Rate = $so2Rate = null;
    $so1CompliantSchools = $so1EvaluatedSchools = $so2AdoptedPrograms = $so2EvaluatedPrograms = 0;
    $source = file_get_contents(__DIR__.'/../app/Views/dashboard/admin.php');
    $start = strpos($source, '        <!-- Card 2: Specific Objective 1');
    $end = strpos($source, '    <!-- General SPED Enrollment Registry Overview', $start);
    ob_start();
    eval('?>'.substr($source,$start,$end-$start));
    $html = ob_get_clean();
    check(strpos($html,'87.5/100 points') !== false && strpos($html,'Pending Evidence') !== false, 'Admin renders readiness separately from unavailable achievement');
    check(strpos($html,'Test &lt;School&gt;') !== false, 'School names escaped in objective table');
    $objectiveMetrics = $m;
    $source = file_get_contents(__DIR__.'/../app/Views/dashboard/principal.php');
    $start = strpos($source, "                            <?php foreach (['so2'");
    $end = strpos($source, '<?php endforeach; ?>', $start) + strlen('<?php endforeach; ?>');
    ob_start();
    eval('?>'.substr($source,$start,$end-$start));
    $html = ob_get_clean();
    check(strpos($html,'87.5/100 points') !== false && strpos($html,'3/4 indicators') !== false, 'Principal renders the same school and program scores');
} finally {
    restore_error_handler();
}
// Exercise every evidence combination: proposed score thresholds must never bypass criteria.
for ($mask=0; $mask<256; $mask++) {
    $e = $empty;
    foreach (['plans','dll','classroom_finalized','resources','certificates','signed_ieps','sip','proficient'] as $bit=>$field) {
        $e[$field] = ($mask >> $bit) & 1;
    }
    $r = ObjectiveMetricsModel::calculate($e);
    foreach (['so1','so2'] as $key) {
        check(!$r[$key]['passed'] || ($r[$key]['evaluated'] && $r[$key]['met']===4), 'Passing requires evaluation and all four criteria');
    }
}
if (in_array('--local', $argv, true)) {
    // Bypass Database and all model constructors that run schema self-healing.
    require_once __DIR__ . '/../config/env.php';
    $db = new PDO('mysql:host='.env('DB_HOST','localhost').';dbname='.env('DB_NAME','sped_lms').';charset=utf8mb4', env('DB_USER','root'), env('DB_PASS',''), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $db->exec('SET SESSION TRANSACTION READ ONLY');
    $db->beginTransaction();
    try {
        $schools = $db->query('SELECT * FROM schools ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        $model = new ObjectiveMetricsModel($db);
        $admin = [];
        foreach ($schools as $school) {
            $admin[$school['id']] = $model->forSchool($school);
            $principal = $model->forSchool($school);
            check($admin[$school['id']] === $principal, 'Admin and Principal must receive identical metrics');
            echo $school['school_name'].': SO1 '.$principal['so1']['score'].'/100 '.$principal['so1']['status'].'; SO2 '.$principal['so2']['met'].'/4 '.$principal['so2']['status'].PHP_EOL;
        }
        foreach (['so1','so2'] as $key) {
            echo $key.' aggregate: '.json_encode(ObjectiveMetricsModel::aggregate($admin,$key)).PHP_EOL;
        }
    } finally {
        $db->rollBack();
    }
}
echo "PASS: $checks checks".PHP_EOL;
