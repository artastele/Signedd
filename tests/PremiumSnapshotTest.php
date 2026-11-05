<?php
declare(strict_types=1);
require_once __DIR__.'/../app/Services/PremiumSnapshotService.php';
$checks=0;
function snapshotCheck(bool $ok,string $name): void { global $checks;++$checks;if(!$ok)throw new RuntimeException($name); }
function snapshotReject(callable $fn,string $name): void { try{$fn();}catch(DomainException $e){snapshotCheck(true,$name);return;}throw new RuntimeException($name); }
final class SnapshotStatement extends PDOStatement {
    private $handler;private array $rows=[];
    public function __construct(callable $handler){$this->handler=$handler;}
    public function execute(?array $params=null):bool{$this->rows=($this->handler)($params??[]);return true;}
    public function fetchAll(int $mode=PDO::FETCH_DEFAULT,mixed ...$args):array{return $this->rows;}
}
final class SnapshotDatabase extends PDO {
    public array $assets=[];
    public array $otherPremium=[];
    public function __construct(){}
    public function prepare(string $sql,array $options=[]):PDOStatement|false {
        return new SnapshotStatement(function(array $p)use($sql):array{
            if(strpos($sql,'FROM resource_hub_packs p JOIN finance_pack_pricing')!==false)return $this->otherPremium;
            if(strpos($sql,'SELECT * FROM finance_media_assets WHERE owner_id=')!==false)return array_values(array_filter($this->assets,fn($a)=>$a['owner_id']===$p[0]&&$a['sha256']===$p[1]));
            if(strpos($sql,'INSERT INTO finance_media_assets')!==false){$this->assets[]=['token'=>$p[0],'owner_id'=>$p[1],'original_name'=>$p[2],'mime_type'=>$p[3],'byte_size'=>$p[4],'sha256'=>$p[5]];return [];}
            throw new RuntimeException('Unexpected SQL in isolated snapshot test');
        });
    }
}
$dir=sys_get_temp_dir().'/signed-snapshot-'.bin2hex(random_bytes(5));
mkdir($dir.'/public/uploads/lesson_editor',0700,true);mkdir($dir.'/public/videos/fsl',0700,true);mkdir($dir.'/private',0700,true);
$png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6yTQAAAAASUVORK5CYII=');
file_put_contents($dir.'/public/uploads/lesson_editor/colors.png',$png);
// QuickTime header exercises built-in .mov support without using any actual library media.
file_put_contents($dir.'/public/videos/fsl/3.mov',pack('N',20).'ftypqt  '.pack('N',0).'qt  ');
$db=new SnapshotDatabase();$finance=new FinanceService($db);
$make=fn()=>new PremiumSnapshotService($finance,$dir.'/public',$dir.'/private');
$originalEnv=getenv('APP_URL');putenv('APP_URL=http://localhost:8888/Signedd/public');
try{
 $pages=[['page_number'=>1,'title'=>'Colors','content'=>'<p style="text-align:center"><img style="width:50%" src="/Signedd/public/uploads/lesson_editor/colors.png"><span class="fsl-word" data-word="hello" data-video="videos/fsl/3.mov">Hello</span></p>']];
 $source=$pages;$service=$make();
 [$prepared,$activities]=$service->prepare($pages,[['activity_data'=>json_encode(['image_path'=>'uploads/lesson_editor/colors.png'])]],6);
 snapshotCheck($pages===$source,'Original lesson stays unchanged');
 snapshotCheck(count($db->assets)===2,'Local image and built-in FSL video copied once');
 snapshotCheck(strpos($prepared[0]['content'],'uploads/')===false&&strpos($prepared[0]['content'],'videos/fsl/')===false,'Public paths removed from shared snapshot');
 snapshotCheck(strpos($prepared[0]['content'],'text-align:center')!==false&&strpos($prepared[0]['content'],'width:50%')!==false,'Slide layout preserved');
 snapshotCheck(strpos($prepared[0]['content'],'data-word="hello"')!==false&&strpos($prepared[0]['content'],'data-video="finance/media/')!==false,'FSL interactive word retained with private video');
 snapshotCheck(count(PremiumMediaService::tokens($prepared,$activities))===2,'Page, FSL, and activity private tokens discoverable for entitlement binding');
 PremiumMediaService::validateSnapshot($prepared,$activities);snapshotCheck(true,'Strict premium validator accepts prepared snapshot');
 snapshotCheck(in_array('video/quicktime',array_column($db->assets,'mime_type'),true),'Built-in MOV file supported');
 foreach($db->assets as $asset){$path=$dir.'/private/'.$asset['token'];snapshotCheck(is_file($path)&&hash_file('sha256',$path)===$asset['sha256'],'Copied bytes retain integrity');snapshotCheck((fileperms($path)&0077)===0,'Private file is owner-only');}
 $again=$make();$again->prepare($pages,[],6);snapshotCheck(count($db->assets)===2,'Existing matching private upload reused');$again->discard();snapshotCheck(count(glob($dir.'/private/*'))===2,'Rollback never deletes reused assets');
 $other=$make();$other->prepare($pages,[],7);snapshotCheck(count($db->assets)===4,'Another creator gets separate owned copies');$other->discard();snapshotCheck(count(glob($dir.'/private/*'))===2,'Failed submission cleanup removes only newly created copies');
 $bad=$make();
 foreach(['https://example.com/picture.png','//example.com/picture.png','http://localhost:9999/Signedd/public/uploads/lesson_editor/colors.png','uploads/lesson_editor/../colors.png','uploads/lesson_editor/%2e%2e/colors.png','uploads/lesson_editor/%252e%252e/colors.png','/etc/passwd','uploads/lesson_editor/missing.png'] as $url){snapshotReject(fn()=>$bad->prepare([['content'=>'<img src="'.htmlspecialchars($url,ENT_QUOTES).'">']],[],6),'Unsafe/external/missing source rejected: '.$url);}
 symlink($dir.'/public/uploads/lesson_editor/colors.png',$dir.'/public/uploads/lesson_editor/alias.png');
 snapshotReject(fn()=>$bad->prepare([['content'=>'<img src="uploads/lesson_editor/alias.png">']],[],6),'Symlink aliases rejected');
 $private='finance/media/'.str_repeat('a',64);[$kept]=$bad->prepare([['content'=>'<img src="'.$private.'">']],[],6);snapshotCheck(strpos($kept[0]['content'],$private)!==false,'Existing private URLs preserved for subsequent owner validation');
 $service->discard();snapshotCheck(glob($dir.'/private/*')===[],'Transaction rollback removes staged files');
 $db->otherPremium=[['content'=>'<img src="/Signedd/public/uploads/lesson_editor/colors.png">']];
 $borrowed=$make();snapshotReject(fn()=>$borrowed->prepare($pages,[],6),'Cannot copy another creator’s legacy premium media into a new owned asset');
 snapshotCheck(file_get_contents($dir.'/public/uploads/lesson_editor/colors.png')===$png,'Public source file unchanged');
 echo "PASS: $checks private snapshot checks; real temporary files, in-memory database, no network.\n";
}finally{
 if($originalEnv===false)putenv('APP_URL');else putenv('APP_URL='.$originalEnv);
 foreach(glob($dir.'/private/*') as $file)unlink($file);
 foreach([$dir.'/public/uploads/lesson_editor/alias.png',$dir.'/public/uploads/lesson_editor/colors.png',$dir.'/public/videos/fsl/3.mov'] as $file)if(is_file($file)||is_link($file))unlink($file);
 foreach(['/public/uploads/lesson_editor','/public/uploads','/public/videos/fsl','/public/videos','/public','/private',''] as $folder)rmdir($dir.$folder);
}
