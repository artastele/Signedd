<?php
declare(strict_types=1);
ob_start(); require __DIR__ . '/ResourceTagWorkflowTest.php'; ob_end_clean();
require_once __DIR__ . '/../app/Controllers/IEPImplementationController.php';
class DraftPermissionLesson extends LessonPlanModel {
    public array $created=[], $pages=[], $assigned=[]; public bool $foreign=false;
    public function __construct(bool $foreign=false){$this->foreign=$foreign;}
    public function getIepById($id){return ['id'=>8,'student_id'=>22,'drafted_by'=>$this->foreign?99:1,'status'=>'signed'];}
    public function findById($id){return ['id'=>20,'iep_id'=>8,'title'=>'Addition','pdsp_domain'=>'perceptuo_cognitive','created_by'=>$this->foreign?99:1];}
    public function getActivityById($id){return ['id'=>77,'lesson_plan_id'=>20];}
    public function create($data){$this->created[]=$data;return 901;}
    public function createPage(array $data):int{$this->pages[]=$data;return 1;}
    public function assignToStudent($lessonPlanId,$studentId,$assignedBy){$this->assigned[]='student';return true;}
    public function assignToAllLearners($lessonPlanId,$iepId,$assignedBy){$this->assigned[]='all';return true;}
}
if(($argv[1]??'')==='--case'){
    $case=$argv[2];$role=$argv[3];$db=new TagWorkflowDatabase();
    if(in_array($case,['ambiguous','manual','missing-skill'],true))$db->log['selected_value']=$db->log['correct_value'];
    $reflection=new ReflectionClass(Database::class);$singleton=$reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('connection')->setValue($singleton,$db);$reflection->getProperty('instance')->setValue(null,$singleton);
    $model=new DraftPermissionLesson($case==='foreign');
    $ref=new ReflectionClass(IEPImplementationController::class);$controller=$ref->newInstanceWithoutConstructor();
    foreach(['userId'=>1,'userRole'=>$role,'basePath'=>'/Signedd/public','model'=>$model]as$key=>$value)$ref->getProperty($key)->setValue($controller,$value);
    $_SESSION=['user_id'=>1,'role'=>$role];$_FILES=[];
    $_POST=['title'=>'Edited Addition Practice','pdsp_domain'=>'perceptuo_cognitive','assignment_type'=>'individual','iep_id'=>8,'student_id'=>22,
        'activity_id'=>77,'source_activity_id'=>77,'intervention_draft'=>true,'draft_reviewed'=>$case!=='unreviewed',
        'resource_draft'=>['title'=>'Edited Addition Practice','learning_competency'=>'addition','description'=>'Reviewed counter practice.','tags'=>'addition, counters']];
    if($case==='missing-skill')$_POST['resource_draft']['learning_competency']='';
    putenv('GEMINI_API_KEY=');$_ENV['GEMINI_API_KEY']='';putenv('GEMINI_MODEL=fixture-model');$_ENV['GEMINI_MODEL']='fixture-model';
    http_response_code(200);ob_start();
    register_shutdown_function(function()use($model){$out=ob_get_clean();echo json_encode(['status'=>http_response_code(),'body'=>json_decode($out,true),'created'=>$model->created,'pages'=>$model->pages,'assigned'=>$model->assigned,'drafts'=>$_SESSION['resource_drafts']??[]]);});
    if(in_array($case,['ideas','ambiguous'],true))$controller->interventionIdeas();
    elseif($case==='upload-denied'){$_POST=['lesson_plan_id'=>20,'material_type'=>'file','title'=>'Practice'];$model->foreign=true;$controller->addMaterial();}
    else $controller->createLessonPlan();
    exit;
}
$count=0;
function draftCheck(bool $ok,string $message):void{global$count;$count++;if(!$ok)throw new RuntimeException($message);}
function draftCase(string $case,string $role='sped_teacher'):array{
    $process=proc_open([PHP_BINARY,__FILE__,'--case',$case,$role],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);
    $out=stream_get_contents($pipes[1]);fclose($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[2]);$code=proc_close($process);
    // The existing controller logs rejected invalid metadata; this is expected for this negative case.
    if($code!==0||($error!==''&&!str_contains($error,'Review the editable intervention details')&&!str_contains($error,'Select the target skill')))throw new RuntimeException($error);
    return json_decode($out,true,512,JSON_THROW_ON_ERROR);
}
$saved=draftCase('create');
draftCheck(($saved['body']['success']??false)&&$saved['created'][0]['status']==='draft','Reviewed intervention saves through existing lesson route as a private draft');
draftCheck(!$saved['assigned'],'Private draft creates no learner assignments');
draftCheck(($saved['drafts'][901]['learning_competency']??'')==='addition'&&($saved['drafts'][901]['tags']??'')==='addition, counters','Editable skill and tag defaults survive builder/upload handoff');
draftCheck(str_contains($saved['pages'][0]['content'],'Reviewed counter practice.'),'Teacher-reviewed ideas persist as editable draft content');
foreach(['parent','learner']as$role){$r=draftCase('create',$role);draftCheck($r['status']===403&&!$r['created'],'Unauthorized role cannot create intervention draft');$r=draftCase('ideas',$role);draftCheck($r['status']===403,'Unauthorized role cannot request AI ideas');}
$r=draftCase('foreign');draftCheck($r['status']===403&&!$r['created'],'Other teacher learner IEP cannot be targeted');
$r=draftCase('unreviewed');draftCheck($r['status']===422&&!$r['created'],'Unreviewed metadata cannot save');
$r=draftCase('ambiguous');draftCheck($r['status']===422,'Ambiguous evidence cannot trigger AI suggestions');
$r=draftCase('manual');draftCheck($r['status']===200&&!$r['assigned']&&$r['created'][0]['status']==='draft','Teacher-selected target skill can create a private intervention despite unverified historical evidence');
$r=draftCase('missing-skill');draftCheck($r['status']===422&&!$r['created'],'Backend requires manual target skill before saving');
$r=draftCase('ideas');draftCheck($r['status']===200&&$r['body']['source']==='local_rules','Authorized verified difficulty offers safe offline AI fallback');
$r=draftCase('upload-denied');draftCheck($r['status']===403&&!$r['created'],'Upload material backend enforces lesson ownership');
$routes=file_get_contents(__DIR__.'/../routes/web.php');
draftCheck((bool)preg_match("~route\('POST',\s*'/iep/implementation/intervention-ideas'.*'iep.implement'~",$routes),'AI idea route requires existing teaching permission');
echo "Intervention permissions/drafts: {$count} checks passed; no real database, uploads, or network used.\n";
