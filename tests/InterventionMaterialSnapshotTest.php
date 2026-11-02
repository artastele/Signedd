<?php
declare(strict_types=1);
ob_start();require __DIR__.'/ResourceTagWorkflowTest.php';ob_end_clean();
class SnapshotMaterialLesson extends TagWorkflowLesson {
    public string $path;
    public function __construct(string $path){$this->path=$path;}
    public function getPagesByLessonPlan(int $lessonPlanId):array{return [];}
    public function getMaterials($lessonPlanId){return [['title'=>'Addition counters PDF','file_path'=>$this->path,'external_url'=>null],
        ['title'=>'Invalid link','file_path'=>'','external_url'=>'javascript:alert(1)']];}
}
if(!defined('BASE_PATH'))define('BASE_PATH','/Signedd/public');
$directory=dirname(__DIR__).'/public/uploads/materials/20';$createdDirectory=!is_dir($directory);
if($createdDirectory&&!mkdir($directory,0755,true))throw new RuntimeException('Cannot create isolated temporary fixture directory');
$filename='signed_intervention_test_'.bin2hex(random_bytes(8)).'.pdf';$absolute=$directory.'/'.$filename;
$file=fopen($absolute,'x');if(!$file)throw new RuntimeException('Cannot create unique fixture file');fwrite($file,"%PDF-1.4\n% isolated temporary fixture\n");fclose($file);
try{
    $db=new TagWorkflowDatabase();$ref=new ReflectionClass(ResourceHubModel::class);$hub=$ref->newInstanceWithoutConstructor();
    $ref->getProperty('db')->setValue($hub,$db);$ref->getProperty('lpModel')->setValue($hub,new SnapshotMaterialLesson('uploads/materials/20/'.$filename));
    if($db->writes)throw new RuntimeException('Unexpected publication before review');
    $hub->shareLessonToHub(20,11,'Teacher A',['title'=>'Addition practice','learning_competency'=>'addition','tags'=>'addition','tags_approved'=>true]);
    if(count($db->pages)!==1)throw new RuntimeException('Only the available permitted PDF should be shared');
    if(!str_contains($db->pages[0]['content'],'/Signedd/public/uploads/materials/20/'.$filename))throw new RuntimeException('Snapshot must open the actual existing uploaded file');
    if(str_contains($db->pages[0]['content'],'javascript:'))throw new RuntimeException('Unsafe URL should not be shared');
    if($db->packs[101]['status']!=='published')throw new RuntimeException('Explicit reviewed sharing must use existing publisher');
    $page=$db->pages[0];$page['id']=1;
    if(!ResourceHubContentMatcher::rank($db->packs[101],[$page],[],[['expected'=>'5']],['addition']))throw new RuntimeException('Available reviewed local material should support a skill match');
    unlink($absolute);
    if(ResourceHubContentMatcher::rank($db->packs[101],[$page],[],[['expected'=>'5']],['addition'])!==null)throw new RuntimeException('Missing material must not support a recommendation');
    echo "Intervention uploaded-material snapshots: 7 checks passed; temporary file removed; no database connection or network used.\n";
}finally{
    if(is_file($absolute))unlink($absolute);
    if($createdDirectory)rmdir($directory);
}
