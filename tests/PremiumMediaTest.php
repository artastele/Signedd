<?php
declare(strict_types=1);
require_once __DIR__.'/../app/Services/PremiumMediaService.php';
$checks=0;
function mediaCheck(bool $ok,string $name): void { global $checks; ++$checks; if(!$ok) throw new RuntimeException($name); }
function mediaReject(callable $fn,string $name): void { try { $fn(); } catch(DomainException|RuntimeException $e) { mediaCheck(true,$name); return; } mediaCheck(false,$name); }
class MediaStatement extends PDOStatement {
    private $handler; private array $rows=[];
    public function __construct(callable $handler) { $this->handler=$handler; }
    public function execute(?array $params=null): bool { $this->rows=($this->handler)($params??[]); return true; }
    public function fetch(int $mode=PDO::FETCH_DEFAULT,int $orientation=PDO::FETCH_ORI_NEXT,int $offset=0): mixed { return $this->rows[0]??false; }
    public function fetchColumn(int $column=0): mixed { return isset($this->rows[0])?(array_values($this->rows[0])[$column]??false):false; }
    public function fetchAll(int $mode=PDO::FETCH_DEFAULT,mixed ...$args): array { return $mode===PDO::FETCH_COLUMN?array_map(fn($r)=>array_values($r)[0],$this->rows):$this->rows; }
}
class MediaDatabase extends PDO {
    public array $asset=[]; public array $purchase=[22=>true]; public array $subscriptions=[33=>true,44=>false];
    public array $assignments=[66=>22,77=>33]; public array $legacy=[]; public array $queries=[];
    public function __construct() {}
    public function query(string $sql,?int $fetchMode=null,mixed ...$args): PDOStatement|false { $s=$this->prepare($sql); $s->execute(); return $s; }
    public function prepare(string $sql,array $options=[]): PDOStatement|false {
        return new MediaStatement(function(array $p) use($sql): array {
            $this->queries[]=$sql;
            if(strpos($sql,'information_schema.TABLES')!==false) return [['count'=>1]];
            if(strpos($sql,'SELECT * FROM finance_media_assets')!==false) return $p[0]===($this->asset['token']??'')?[$this->asset]:[];
            if(strpos($sql,'SELECT pack_id FROM finance_pack_media')!==false) return [['pack_id'=>1]];
            if(strpos($sql,'SELECT id,name,role,school_id,status FROM users')!==false) return [['id'=>$p[0],'name'=>'Fixture','role'=>$p[0]===99?'admin':($p[0]===66?'parent':(in_array($p[0],[77,78],true)?'learner':'sped_teacher')),'school_id'=>null,'status'=>'active']];
            if(strpos($sql,'SELECT p.*,f.review_status')!==false) return [['id'=>1,'created_by'=>11,'status'=>'published','review_status'=>'APPROVED','approved_amount'=>7900]];
            if(strpos($sql,'SELECT id FROM finance_entitlements')!==false) return !empty($this->purchase[$p[0]])?[['id'=>1]]:[];
            if(strpos($sql,"kind='subscription'")!==false) return !empty($this->subscriptions[$p[0]])?[['id'=>3,'buyer_id'=>$p[0],'starts_at'=>gmdate('Y-m-d H:i:s',time()-3600),'ends_at'=>gmdate('Y-m-d H:i:s',time()+3600)]]:[];
            if(strpos($sql,'SELECT DISTINCT lp.created_by')!==false) return isset($this->assignments[$p[1]])?[['created_by'=>$this->assignments[$p[1]]]]:[];
            if(strpos($sql,'UNION ALL')!==false) return $this->legacy;
            throw new RuntimeException('Unexpected fixture SQL: '.$sql);
        });
    }
}
$dir=sys_get_temp_dir().'/signed-media-'.bin2hex(random_bytes(6)); mkdir($dir,0700); mkdir($dir.'/private',0700); mkdir($dir.'/public',0700); mkdir($dir.'/public/uploads',0700); mkdir($dir.'/public/uploads/materials',0700); mkdir($dir.'/public/uploads/materials/1',0700);
$token=str_repeat('a',64); $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
file_put_contents($dir.'/private/'.$token,$png); file_put_contents($dir.'/public/uploads/materials/1/demo.png',$png);
$db=new MediaDatabase(); $db->asset=['token'=>$token,'owner_id'=>11,'mime_type'=>'image/png','byte_size'=>strlen($png),'sha256'=>hash('sha256',$png),'original_name'=>'demo.png'];
$prior=getenv('SIGNED_FINANCE_ENABLED'); putenv('SIGNED_FINANCE_ENABLED=true');
try {
    $media=new PremiumMediaService(new FinanceService($db),$dir.'/private',[$dir.'/public']);
    foreach([11,22,33,66,77,99] as $actor) mediaCheck($media->resolve($token,$actor)['size']===strlen($png),'Owner/purchase/subscription/assigned learner/admin access '.$actor);
    foreach([44,55,78] as $actor) mediaReject(fn()=>$media->resolve($token,$actor),'Expired/unrelated access denied '.$actor);
    $db->subscriptions[33]=false;
    mediaReject(fn()=>$media->resolve($token,33),'Expiry checked on subsequent request');
    mediaReject(fn()=>$media->resolve($token,77),'Assigned learner loses access when teacher subscription expires');
    $db->purchase[33]=true;
    mediaCheck($media->resolve($token,33)['size']===strlen($png),'Permanent purchase survives subscription expiry');
    putenv('SIGNED_FINANCE_ENABLED=false'); // Access stays protected with an installed schema.
    mediaReject(fn()=>$media->resolve($token,55),'Disabling checkout never exposes private media');
    mediaCheck($media->resolve($token,22)['size']===strlen($png),'Permanent ownership remains checked with checkout disabled');
    putenv('SIGNED_FINANCE_ENABLED=true');
    foreach(['../'.$token,$token.'%00',$token.'/',strtoupper($token)] as $bad) mediaReject(fn()=>$media->resolve($bad,99),'Token/path bypass rejected');
    mediaReject(fn()=>PremiumMediaService::storageRoot($dir.'/public',[$dir.'/public']),'Storage in web root rejected');
    mediaReject(fn()=>PremiumMediaService::storageRoot($dir.'/public/uploads',[$dir.'/public']),'Nested public storage rejected');
    foreach(['text/html','image/svg+xml','application/x-php'] as $mime) mediaReject(fn()=>PremiumMediaService::requireMime($mime),'Executable/active MIME rejected');
    $url='/Signedd/public/finance/media/'.$token;
    $pages=[['content'=>'<p>Test <img src="'.$url.'"></p>','media_path'=>$url]];
    PremiumMediaService::validateSnapshot($pages,[['activity_data'=>json_encode(['image_path'=>$url])]]);
    mediaCheck(PremiumMediaService::tokens($pages,[])===[$token],'Private image/token accepted and deduplicated');
    mediaCheck(PremiumMediaService::tokens([['media_path'=>'finance/media/'.$token]],[['activity_data'=>json_encode(['image_path'=>$url])]])===[$token],'Relative and JSON-escaped asset references bind correctly');
    mediaCheck($media->validateOwnedReferences($pages,[],11)===[$token],'Creator owns private reference');
    mediaReject(fn()=>$media->validateOwnedReferences($pages,[],22),'Another creator cannot publish a private asset');
    foreach(['<img src="/uploads/materials/1/demo.png">','<video src="https://example.com/demo.mp4"></video>','<span data-video="/videos/demo.mp4">Sign</span>','<p style="background:url(/uploads/a.png)">x</p>','<img srcset="/uploads/a.png 1x">','<img src="data:image/png;base64,AAAA">'] as $html) mediaReject(fn()=>PremiumMediaService::validateSnapshot([['content'=>$html]],[]),'Public/embedded/external media rejected');
    mediaReject(fn()=>PremiumMediaService::validateSnapshot([['content'=>'<p style="background:u'.chr(92).'72l('.chr(92).'2f hidden.png)">x</p>']],[]),'Escaped CSS cannot introduce unguarded media');
    foreach(['../demo.png','uploads/materials/../demo.png','uploads/materials/%2e%2e/demo.png','uploads/materials/1/demo.png%00'] as $bad) mediaReject(fn()=>PremiumMediaService::canonicalLegacy($bad),'Legacy traversal rejected');
    $legacy='uploads/materials/1/demo.png';
    mediaCheck(PremiumMediaService::canonicalLegacy('/Signedd/public/'.$legacy)===$legacy,'Base path normalized');
    mediaCheck(PremiumMediaService::referencesLegacy(['activity_data'=>'{"file_path":"uploads\\/materials\\/1\\/demo.png"}'],$legacy),'JSON slash escaping detected');
    mediaCheck(PremiumMediaService::referencesLegacy(['content'=>'<img src="/uploads%2Fmaterials%2F1%2Fdemo.png">'],$legacy),'Encoded legacy URL detected');
    $db->legacy=[['pack_id'=>1,'content'=>'<img src="/'.$legacy.'">']];
    mediaReject(fn()=>$media->legacy($legacy,55,$dir.'/public'),'Direct public URL cannot bypass purchase');
    mediaCheck($media->legacy($legacy,22,$dir.'/public')['size']===strlen($png),'Legacy buyer permitted');
    symlink($dir.'/public/uploads/materials/1/demo.png',$dir.'/public/uploads/materials/1/alias.png');
    mediaReject(fn()=>$media->legacy('uploads/materials/1/alias.png',55,$dir.'/public'),'Symlink alias cannot bypass premium reference');
    mediaCheck(PremiumMediaService::range(null,100)===[0,99,false],'Full response');
    foreach(['bytes=0-9'=>[0,9,true],'bytes=90-'=>[90,99,true],'bytes=-10'=>[90,99,true],'bytes=0-200'=>[0,99,true]] as $header=>$expected) mediaCheck(PremiumMediaService::range($header,100)===$expected,'Range '.$header);
    foreach(['bytes=100-','bytes=9-1','bytes=0-1,4-5','bytes=-0'] as $range) { try { PremiumMediaService::range($range,100); mediaCheck(false,'Invalid range'); } catch(OutOfRangeException $e) { mediaCheck(true,'Invalid range rejected'); } }
    file_put_contents($dir.'/private/'.$token,'tampered');
    mediaReject(fn()=>$media->resolve($token,22),'Modified private blob fails integrity');
    mediaCheck(!array_filter($db->queries,fn($sql)=>(bool)preg_match('/^\s*(INSERT|UPDATE|DELETE|ALTER|CREATE)/i',$sql)),'Delivery performed no database writes');
    echo "PASS: $checks premium media checks; temporary files and in-memory fixtures only.\n";
} finally {
    if($prior===false) putenv('SIGNED_FINANCE_ENABLED'); else putenv('SIGNED_FINANCE_ENABLED='.$prior);
    foreach([$dir.'/public/uploads/materials/1/alias.png',$dir.'/public/uploads/materials/1/demo.png',$dir.'/private/'.$token] as $file) if(is_file($file)||is_link($file)) unlink($file);
    foreach([$dir.'/public/uploads/materials/1',$dir.'/public/uploads/materials',$dir.'/public/uploads',$dir.'/public',$dir.'/private',$dir] as $folder) rmdir($folder);
}
