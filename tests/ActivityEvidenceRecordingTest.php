<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/Controllers/LearningController.php';
class RecordingStatement extends PDOStatement {
    private $run; private array $rows=[];
    public function __construct(callable $run) { $this->run=$run; }
    public function execute(?array $params=null): bool { $this->rows=($this->run)($params??[]);return true; }
    public function fetch(int $mode=PDO::FETCH_DEFAULT,int $orientation=PDO::FETCH_ORI_NEXT,int $offset=0): mixed { return $this->rows[0]??false; }
    public function fetchColumn(int $column=0): mixed { return $this->rows ? array_values($this->rows[0])[$column] : false; }
}
class RecordingDatabase extends PDO {
    public array $attempts=[], $logs=[], $otherWrites=[]; public string $case; private bool $transaction=false;
    public function __construct(string $case) { $this->case=$case; }
    public function beginTransaction(): bool {$this->transaction=true;return true;}
    public function commit(): bool {$this->transaction=false;return true;}
    public function inTransaction(): bool {return $this->transaction;}
    public function rollBack(): bool {$this->transaction=false;return true;}
    public function lastInsertId(?string $name=null): string|false {return '700';}
    public function exec(string $statement): int|false {throw new RuntimeException('Direct writes/schema changes forbidden');}
    public function prepare(string $sql,array $options=[]): PDOStatement|false {
        return new RecordingStatement(function($params)use($sql){
            if(str_contains($sql,'SELECT id FROM student_records'))return [['id'=>5]];
            if(str_contains($sql,'SELECT score_threshold'))return [['score_threshold'=>70,'remedial_for_lesson_id'=>null]];
            if(str_contains($sql,'SELECT id, answers FROM lms_activity_attempts'))return $this->case==='rapid'?[['id'=>600,'answers'=>'[1]']]:[];
            if(str_contains($sql,'COALESCE(MAX(attempt_number)'))return [['next'=>2]];
            if(str_contains($sql,'INSERT INTO lms_activity_attempts')){$this->attempts[]=$params;return [];}
            if(str_contains($sql,'INSERT INTO activity_attempt_log')){$this->logs[]=$params;return [];}
            if(preg_match('/^\s*SELECT/i',$sql))return [];
            if(str_contains($sql,'UPDATE lms_submissions')||str_contains($sql,'INSERT INTO lms_logs')){$this->otherWrites[]=$sql;return [];}
            throw new RuntimeException('Unexpected write or query: '.$sql);
        });
    }
}
class RecordingLesson extends LessonPlanModel {
    public array $activity; public array $submissions=[];
    public function __construct(array $activity){$this->activity=$activity;}
    public function getActivityById($id){return $this->activity;}
    public function isAssignedToStudent(int $lessonPlanId,int $studentId):bool{return true;}
    public function saveSubmission($activityId,$studentId,$submittedBy,$answers,$autoScore,$filePath=null){$this->submissions[]=compact('answers','autoScore');return 1;}
    public function getLessonProgress($lessonPlanId,$studentId){return ['completed'=>0,'total'=>0];}
}
class RecordingGamification extends GamificationModel {
    public function __construct(){}
    public function xpAlreadyAwarded(int $studentId,string $sourceType,int $sourceId):bool{return true;}
    public function awardBadge(int $studentId,string $badgeKey):bool{return false;}
    public function getTotalStars(int $studentId):int{return 0;}
    public function getEarnedBadgeKeys(int $studentId):array{return [];}
}
if(($argv[1]??'')==='--case'){
    $case=$argv[2];$type='multiple_choice';$data=['questions'=>[['question'=>'Ilan ang 3 + 2?','options'=>['4','5','6'],'correct'=>1,'points'=>10]]];$answers=[0];
    if($case==='correct')$answers=[1];
    if($case==='text')$answers=['4'];
    if($case==='malformed')$answers=[['not'=>'an answer']];
    if($case==='missing')unset($data['questions'][0]['correct']);
    if($case==='malformed-definition')$data['questions']='invalid';
    if($case==='boolean'){$type='true_false';$data=['questions'=>[['statement'=>'A statement','answer'=>false,'points'=>10]]];$answers=[false];}
    if($case==='fill'){$type='fill_in_blanks';$data=['sentences'=>[['sentence'=>'A [blank].','answers'=>['mother'],'points'=>10]],'answer_mode'=>'free_type'];$answers=['father'];}
    if($case==='matching'){$type='matching';$data=['pairs'=>[['left'=>'Ina','right'=>'mother']],'points'=>10];$answers=['father'];}
    if($case==='sequence'){$type='sequencing';$data=['sets'=>[['steps'=>['Wash','Rinse'],'correct_order'=>[0,1],'points'=>10]]];$answers=[[1,0]];}
    if($case==='invalid-sequence'){$type='sequencing';$data=['sets'=>[['steps'=>['Wash','Rinse'],'correct_order'=>[0,1],'points'=>10]]];$answers=[[99,0]];}
    $activity=['id'=>19,'lesson_plan_id'=>26,'activity_type'=>$type,'activity_data'=>json_encode($data),'max_score'=>10,'title'=>'Practice'];
    $db=new RecordingDatabase($case);$model=new RecordingLesson($activity);
    $reflection=new ReflectionClass(Database::class);$singleton=$reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('connection')->setValue($singleton,$db);$reflection->getProperty('instance')->setValue(null,$singleton);
    $controllerReflection=new ReflectionClass(LearningController::class);$controller=$controllerReflection->newInstanceWithoutConstructor();
    foreach(['model'=>$model,'gamification'=>new RecordingGamification(),'userId'=>12,'userRole'=>'learner','basePath'=>'']as$key=>$value)$controllerReflection->getProperty($key)->setValue($controller,$value);
    $_POST=['answers'=>$answers+['_evidence_v1'=>['forged'=>'client evidence']],'attempts'=>[0=>9]];$_FILES=[];http_response_code(200);ob_start();
    register_shutdown_function(function()use($db,$model){$body=ob_get_clean();echo json_encode(['status'=>http_response_code(),'body'=>json_decode($body,true),'attempts'=>$db->attempts,'logs'=>$db->logs,'submissions'=>$model->submissions]);});
    $controller->submitActivity('19');exit;
}
$checks=0;
function recordCheck(bool $ok,string $message):void{global $checks;$checks++;if(!$ok)throw new RuntimeException($message);}
function recordCase(string $case):array{
    $process=proc_open([PHP_BINARY,__FILE__,'--case',$case],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);
    $out=stream_get_contents($pipes[1]);fclose($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[2]);$code=proc_close($process);
    if($code!==0||$error!=='')throw new RuntimeException($error);return json_decode($out,true,512,JSON_THROW_ON_ERROR);
}
foreach(['wrong','correct','text','rapid','boolean','fill','matching','sequence']as$case){
    $r=recordCase($case);
    recordCheck(count($r['attempts'])===1 && count($r['logs'])===1,'New '.$case.' submission saves one linked item');
    $log=$r['logs'][0];
    recordCheck($log['att_id']===700 && $log['qi']===0 && $log['cv']!=='' && !str_contains($log['sv'],'Attempts:'),'Attempt id, question identity and clean answer evidence recorded for '.$case);
    $snapshot=json_decode($r['attempts'][0]['answers'],true)['_evidence_v1'][0];
    recordCheck($snapshot['expected']===$log['cv'] && $snapshot['selected']===$log['sv'] && $snapshot['correct']===(bool)$log['ic'],'Server-owned snapshot agrees with logs for '.$case);
    recordCheck(!isset(json_decode($r['submissions'][0]['answers'],true)['_evidence_v1']),'Submission answer shape is preserved for '.$case);
    if(in_array($case,['wrong','text','rapid'],true))recordCheck($log['sv']==='4'&&$log['cv']==='5'&&$log['ic']===0,'Actual addition answer values saved');
}
foreach(['missing','malformed','invalid-sequence','malformed-definition']as$case){$r=recordCase($case);recordCheck($r['status']===422&&!$r['attempts']&&!$r['logs'],'Invalid '.$case.' data is rejected before any writes');}
echo "Activity evidence recording: {$checks} checks passed through real submission controller; no real database or network used.\n";
