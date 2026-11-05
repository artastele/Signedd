<?php
declare(strict_types=1);
require_once __DIR__.'/FinanceService.php';

/** Private blobs are outside every served root. Tokens identify assets, never authorize access. */
final class PremiumMediaService {
    private FinanceService $finance;
    private string $root;
    public function __construct(FinanceService $finance, ?string $root=null, ?array $servedRoots=null, bool $requireStorage=true) {
        $this->finance=$finance;
        $this->root=$requireStorage?self::storageRoot($root??(string)getenv('SIGNED_PREMIUM_STORAGE'),$servedRoots??[
            dirname(__DIR__,2),$_SERVER['DOCUMENT_ROOT']??dirname(__DIR__,2).'/public']):'';
    }
    public static function storageRoot(string $root, array $servedRoots): string {
        $real=realpath($root);
        if (!$real || !is_dir($real) || !is_writable($real) || $root==='' || $root[0]!=='/') throw new RuntimeException('Configure an existing private media directory outside web roots.');
        foreach($servedRoots as $served) {
            $base=realpath($served);
            if ($base && ($real===$base || strpos($real,$base.DIRECTORY_SEPARATOR)===0)) throw new RuntimeException('Private storage cannot be inside a served root.');
        }
        return $real;
    }
    public static function validToken(string $token): bool { return (bool)preg_match('/^[a-f0-9]{64}$/D',$token); }
    public static function tokens(array $pages, array $activities): array {
        $tokens=[];
        $walk=function($value) use (&$walk,&$tokens): void {
            if(is_array($value)) { foreach($value as $v) $walk($v); return; }
            if(!is_string($value)) return;
            $json=json_decode($value,true);
            if(is_array($json)) $walk($json);
            for($i=0;$i<3;$i++) $value=rawurldecode(html_entity_decode(str_replace('\\/','/',$value),ENT_QUOTES|ENT_HTML5,'UTF-8'));
            preg_match_all('#finance/media/([a-f0-9]{64})(?![a-f0-9])#',$value,$m);
            foreach($m[1] as $token) $tokens[$token]=true;
        };
        $walk([$pages,$activities]);
        return array_keys($tokens);
    }

    public static function privateUrl(string $value): bool {
        $path=parse_url($value,PHP_URL_PATH);
        return strpos($value,'://')===false && strpos($value,'//')!==0 && is_string($path) &&
            (bool)preg_match('#^(?:/[A-Za-z0-9_.-]+)*/?finance/media/[a-f0-9]{64}$#D',$path) &&
            strpos($path,'..')===false && parse_url($value,PHP_URL_QUERY)===null && parse_url($value,PHP_URL_FRAGMENT)===null;
    }
    /** Reject external/public media, including HTML/CSS/FSL attributes and JSON activity assets. */
    public static function validateSnapshot(array $pages, array $activities): void {
        $check=function($value) use (&$check): void {
            if (is_array($value)) { foreach($value as $v) $check($v); return; }
            if (!is_string($value) || $value==='') return;
            $decoded=html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');
            if (preg_match('#(?:https?:|data:|file:|blob:|//|uploads/|url\s*\()#i',$decoded)) throw new DomainException('Premium assets must use private uploads; public/external media is not protected.');
            if (strpos($decoded,'<')!==false) {
                $dom=new DOMDocument(); $old=libxml_use_internal_errors(true);
                $dom->loadHTML('<?xml encoding="UTF-8"><div>'.$decoded.'</div>',LIBXML_NONET|LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
                libxml_clear_errors(); libxml_use_internal_errors($old);
                foreach($dom->getElementsByTagName('*') as $node) {
                    if (in_array(strtolower($node->nodeName),['iframe','object','embed','script','svg','style'],true)) throw new DomainException('Unsupported embedded premium content.');
                    foreach($node->attributes??[] as $attribute) {
                        $name=strtolower($attribute->name); $v=$attribute->value;
                        if ($name==='style' && (strpos($v,chr(92))!==false || preg_match('/@|image-set|cross-fade|var\s*\(|attr\s*\(/i',$v))) throw new DomainException('Unsupported premium CSS media reference.');
                        if (strpos($name,'on')===0 || in_array($name,['srcset','srcdoc','background'],true)) throw new DomainException('Unsupported premium media attribute.');
                        if (in_array($name,['src','href','poster','data-video'],true) && $v!=='' && !self::privateUrl($v)) throw new DomainException('Use a private asset URL in premium content.');
                    }
                }
            }
        };
        foreach($pages as $page) {
            if (!empty($page['media_path']) && !self::privateUrl($page['media_path'])) throw new DomainException('Use private media for premium pages.');
            $check([$page['content']??'', $page['guide_questions']??'']);
        }
        foreach($activities as $activity) {
            $data=$activity['activity_data']??[];
            if (is_string($data)) $data=json_decode($data,true,64,JSON_THROW_ON_ERROR);
            $walk=function($v,$key='') use (&$walk,$check): void {
                if(is_array($v)) { foreach($v as $k=>$item) $walk($item,(string)$k); return; }
                if(is_string($v) && $v!=='' && preg_match('/(?:path|url|src|image|video|audio|poster|file)$/i',$key) && !self::privateUrl($v)) throw new DomainException('Use private activity media URLs.');
                $check($v);
            };
            $walk($data); $check($activity['instructions']??'');
        }
    }
    public function validateOwnedReferences(array $pages, array $activities, int $owner, bool $strict=true): array {
        if ($strict) self::validateSnapshot($pages,$activities); $tokens=self::tokens($pages,$activities);
        foreach($tokens as $token) {
            $asset=$this->finance->query('SELECT * FROM finance_media_assets WHERE token=?',[$token])->fetch();
            if (!$asset || (int)$asset['owner_id']!==$owner) throw new DomainException('Only your own private assets can be published.');
            $this->assetPath($asset);
        }
        return $tokens;
    }
    public function bindNewPack(int $pack, array $tokens): void {
        foreach($tokens as $token) $this->finance->query('INSERT INTO finance_pack_media(pack_id,token) VALUES(?,?)',[$pack,$token]);
    }
    public function upload(array $file, int $actor): array {
        $u=$this->finance->user($actor);
        if(!in_array($u['role'],['sped_teacher','teacher','master_teacher','admin'],true)) throw new DomainException('Teacher upload required.');
        if(($file['error']??-1)!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']??'')) throw new DomainException('A valid uploaded file is required.');
        $size=filesize($file['tmp_name']);
        if(!$size || $size>50*1024*1024) throw new DomainException('Private media must be between 1 byte and 50 MB.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        self::requireMime($mime);
        $token=bin2hex(random_bytes(32)); $target=$this->root.'/'.$token;
        if (!move_uploaded_file($file['tmp_name'],$target)) throw new RuntimeException('Unable to store private media.');
        chmod($target,0600);
        try {
            $this->finance->query('INSERT INTO finance_media_assets(token,owner_id,original_name,mime_type,byte_size,sha256,created_at) VALUES(?,?,?,?,?,?,UTC_TIMESTAMP())',[$token,$actor,substr(basename((string)$file['name']),0,255),$mime,$size,hash_file('sha256',$target)]);
        } catch(Throwable $e) { unlink($target); throw $e; }
        return ['token'=>$token,'path'=>'finance/media/'.$token,'url'=>(defined('BASE_PATH')?BASE_PATH:'').'/finance/media/'.$token];
    }
    public static function requireMime(string $mime): void {
        if(!in_array($mime,['image/jpeg','image/png','image/gif','image/webp','application/pdf','video/mp4','video/webm','video/quicktime','audio/mpeg','audio/ogg','audio/wav'],true)) throw new DomainException('Unsupported media type. SVG, HTML and executable formats are forbidden.');
    }
    private function assetPath(array $asset): string {
        if (!self::validToken($asset['token']??'')) throw new DomainException('Invalid asset.');
        $path=$this->root.'/'.$asset['token']; $real=realpath($path);
        if (!$real || is_link($path) || dirname($real)!==$this->root || !is_file($real) || filesize($real)!==(int)$asset['byte_size'] || !hash_equals($asset['sha256'],hash_file('sha256',$real))) throw new DomainException('Private asset unavailable or integrity check failed.');
        self::requireMime($asset['mime_type']); return $real;
    }
    /** Asset request always authorizes again, even for HEAD, Range and download. */
    public function resolve(string $token, int $actor): array {
        if (!self::validToken($token)) throw new DomainException('Invalid asset.');
        $asset=$this->finance->query('SELECT * FROM finance_media_assets WHERE token=?',[$token])->fetch();
        if (!$asset) throw new DomainException('Asset unavailable.');
        $u=$this->finance->user($actor);
        $allowed=$u['role']==='admin' || (int)$asset['owner_id']===$actor;
        $packs=$this->finance->query('SELECT pack_id FROM finance_pack_media WHERE token=?',[$token])->fetchAll(PDO::FETCH_COLUMN);
        foreach($packs as $pack) if(!$allowed) $allowed=$this->canUsePack((int)$pack,$u);
        if (!$allowed) throw new DomainException('Premium media access denied.');
        return ['path'=>$this->assetPath($asset),'mime'=>$asset['mime_type'],'size'=>(int)$asset['byte_size'],'name'=>$asset['original_name']];
    }
    private function canUsePack(int $pack, array $u): bool {
        if (in_array($u['role'],['admin','sped_teacher','teacher','master_teacher'],true)) return $this->finance->packAccess($pack,(int)$u['id'])['allowed'];
        if (!in_array($u['role'],['learner','parent'],true)) return false;
        // Existing lesson assignments, not request-supplied learner or buyer IDs.
        $teachers=$this->finance->query("SELECT DISTINCT lp.created_by FROM lesson_plans lp JOIN lesson_assignments la ON la.lesson_plan_id=lp.id JOIN student_records sr ON sr.id=la.student_id LEFT JOIN enrollment_submissions es ON es.id=sr.enrollment_id WHERE lp.cloned_from_pack_id=? AND lp.status='published' AND ".($u['role']==='learner'?'sr.learner_user_id=?':'es.parent_id=?'),[$pack,$u['id']])->fetchAll(PDO::FETCH_COLUMN);
        foreach($teachers as $teacher) if($this->finance->packAccess($pack,(int)$teacher)['allowed']) return true;
        return false;
    }
    public static function canonicalLegacy(string $value): string {
        // Normalize aliases before path confinement and premium-reference matching.
        for($i=0;$i<3;$i++) { $decoded=rawurldecode(html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8')); if($decoded===$value) break; $value=$decoded; }
        $value=str_replace('\\/','/',$value);
        if (preg_match('/[\x00-\x1F\x7F]/',$value) || preg_match('#(?:^|/)\.{1,2}(?:/|$)#',$value)) throw new DomainException('Invalid media path.');
        $path=parse_url($value,PHP_URL_PATH);
        if(!is_string($path)) throw new DomainException('Invalid media path.');
        $at=strpos($path,'uploads/'); if($at===false) throw new DomainException('Invalid media path.');
        $path=substr($path,$at);
        if(!preg_match('#^uploads/(materials|lesson_pages|lesson_editor|activities|fsl_custom_videos|lesson_plans)/[A-Za-z0-9_./-]+$#D',$path) || preg_match('#(?:^|/)\.{1,2}(?:/|$)#',$path) || strpos($path,'//')!==false) throw new DomainException('Invalid media path.');
        return $path;
    }
    public static function referencesLegacy(array $row, string $canonical): bool {
        $walk=function($value) use (&$walk,$canonical): bool {
            if(is_array($value)) { foreach($value as $v) if($walk($v)) return true; return false; }
            if(!is_string($value)) return false;
            $json=json_decode($value,true);
            if(is_array($json) && $walk($json)) return true;
            for($i=0;$i<3;$i++) $value=rawurldecode(html_entity_decode(str_replace('\\/','/',$value),ENT_QUOTES|ENT_HTML5,'UTF-8'));
            return stripos($value,$canonical)!==false;
        };
        return $walk($row);
    }
    public function legacy(string $path, int $actor, string $publicRoot): array {
        $canonical=self::canonicalLegacy($path);
        $root=realpath($publicRoot.'/uploads'); $real=realpath($publicRoot.'/'.$canonical);
        if(!$root || !$real || strpos($real,$root.'/')!==0 || !is_file($real)) throw new DomainException('File unavailable.');
        $canonical=self::canonicalLegacy(substr($real,strlen((string)realpath($publicRoot))+1));
        $u=$this->finance->user($actor); $premium=[];
        // Scan legacy snapshots without changing records; handles JSON-escaped and encoded URLs.
        $rows=$this->finance->query("SELECT p.id AS pack_id,r.content,r.guide_questions,r.media_path FROM resource_hub_packs p JOIN finance_pack_pricing f ON f.pack_id=p.id JOIN resource_hub_pages r ON r.pack_id=p.id WHERE COALESCE(f.approved_amount,f.proposed_amount)>0 UNION ALL SELECT p.id,a.instructions,a.activity_data,NULL FROM resource_hub_packs p JOIN finance_pack_pricing f ON f.pack_id=p.id JOIN resource_hub_activities a ON a.pack_id=p.id WHERE COALESCE(f.approved_amount,f.proposed_amount)>0")->fetchAll();
        foreach($rows as $row) if(self::referencesLegacy($row,$canonical)) $premium[(int)$row['pack_id']]=true;
        if($premium && $u['role']!=='admin') {
            $allowed=false;
            foreach(array_keys($premium) as $pack) if($this->canUsePack($pack,$u)) { $allowed=true; break; }
            if(!$allowed) throw new DomainException('Premium media access denied.');
        }
        // Free legacy files retain authenticated access. No direct anonymous static delivery.
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($real); $attachment=false;
        try { self::requireMime($mime); } catch(DomainException $e) {
            if ($premium) throw $e;
            $mime='application/octet-stream'; $attachment=true;
        }
        return ['path'=>$real,'mime'=>$mime,'size'=>filesize($real),'name'=>basename($real),'force_download'=>$attachment];
    }
    public static function range(?string $header, int $size): array {
        if($size<1) throw new DomainException('Empty media.');
        if($header===null || $header==='') return [0,$size-1,false];
        if(!preg_match('/^bytes=(\d*)-(\d*)$/D',$header,$m) || ($m[1]==='' && $m[2]==='')) throw new OutOfRangeException('Invalid range.');
        if($m[1]==='') { $length=(int)$m[2]; if($length<1) throw new OutOfRangeException('Invalid range.'); $start=max(0,$size-$length); $end=$size-1; }
        else { $start=(int)$m[1]; $end=$m[2]===''?$size-1:min((int)$m[2],$size-1); }
        if($start>=$size || $end<$start) throw new OutOfRangeException('Unsatisfiable range.');
        return [$start,$end,true];
    }
}
