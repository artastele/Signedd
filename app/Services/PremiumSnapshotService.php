<?php
declare(strict_types=1);
require_once __DIR__.'/PremiumMediaService.php';
require_once __DIR__.'/../Helpers/HtmlSanitizer.php';

/** Builds private pack copies from an already authorized lesson. Never edits its source. */
final class PremiumSnapshotService {
    private FinanceService $finance;
    private string $publicRoot;
    private string $storage;
    private array $created=[];
    private array $copied=[];
    private int $bytes=0;
    private ?array $otherPremiumMedia=null;
    private ?int $owner=null;

    public function __construct(FinanceService $finance, ?string $publicRoot=null, ?string $storage=null) {
        $this->finance=$finance;
        $this->publicRoot=realpath($publicRoot??dirname(__DIR__,2).'/public')?:'';
        if($this->publicRoot==='') throw new RuntimeException('Lesson media directory is unavailable.');
        $this->storage=PremiumMediaService::storageRoot($storage??(string)getenv('SIGNED_PREMIUM_STORAGE'),[
            $this->publicRoot,dirname(__DIR__,2),$_SERVER['DOCUMENT_ROOT']??dirname(__DIR__,2).'/public']);
    }

    public function prepare(array $pages,array $activities,int $owner): array {
        if($owner<1||($this->owner!==null&&$this->owner!==$owner))throw new DomainException('A snapshot must belong to one authenticated creator.');
        $this->owner=$owner;
        foreach($pages as &$page) {
            try {
                $page['content']=$this->html((string)($page['content']??''),$owner);
                if(!empty($page['media_path'])) $page['media_path']=$this->asset($page['media_path'],$owner);
                PremiumMediaService::validateSnapshot([$page],[]);
            } catch(DomainException $e) {
                throw new DomainException('Slide '.($page['page_number']??'?').' ('.($page['title']??'Untitled').'): '.$e->getMessage(),0,$e);
            }
        }
        unset($page);
        foreach($activities as &$activity) {
            $data=$activity['activity_data']??[];
            $wasJson=is_string($data);
            if($wasJson) $data=json_decode($data,true,64,JSON_THROW_ON_ERROR);
            $walk=function($value,string $key='') use (&$walk,$owner) {
                if(is_array($value)) { foreach($value as $k=>$v) $value[$k]=$walk($v,(string)$k); return $value; }
                if(!is_string($value)||$value==='') return $value;
                if(strpos($value,'<')!==false) return $this->html($value,$owner);
                if(preg_match('/(?:path|url|src|image|video|audio|poster|file)$/i',$key)) return $this->asset($value,$owner);
                return $value;
            };
            $data=$walk($data);
            $activity['activity_data']=$wasJson?json_encode($data,JSON_THROW_ON_ERROR):$data;
            if(!empty($activity['instructions'])) $activity['instructions']=$this->html($activity['instructions'],$owner);
        }
        unset($activity);
        PremiumMediaService::validateSnapshot($pages,$activities);
        return [$pages,$activities];
    }

    private function html(string $html,int $owner): string {
        if(trim($html)==='') return '';
        $dom=new DOMDocument();$previous=libxml_use_internal_errors(true);
        try { $dom->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>',LIBXML_NONET|LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD); }
        finally { libxml_clear_errors();libxml_use_internal_errors($previous); }
        foreach($dom->getElementsByTagName('*') as $node) {
            foreach(['src','href','poster','data-video'] as $attr) {
                if($node->hasAttribute($attr)&&$node->getAttribute($attr)!=='') $node->setAttribute($attr,$this->asset($node->getAttribute($attr),$owner));
            }
        }
        $out='';foreach($dom->getElementsByTagName('div')->item(0)->childNodes as $child)$out.=$dom->saveHTML($child);
        // Normalize legacy upload URLs only after their original host/path was checked.
        return HtmlSanitizer::cleanLessonHtml($out);
    }

    private function asset(string $url,int $owner): string {
        if(PremiumMediaService::privateUrl($url)) return $url;
        if(isset($this->copied[$url])) return $this->copied[$url];
        // No network downloads: an external URL cannot make the server fetch arbitrary hosts.
        $decoded=html_entity_decode($url,ENT_QUOTES|ENT_HTML5,'UTF-8');
        $parts=parse_url($decoded);
        if($parts===false||isset($parts['query'])||isset($parts['fragment'])) throw new DomainException('Use an uploaded file instead of a media link with query parameters.');
        if(isset($parts['scheme'])||isset($parts['host'])) {
            $app=parse_url((string)getenv('APP_URL'));
            if(!is_array($app)||!in_array(strtolower($parts['scheme']??''),['http','https'],true)||
                strtolower($parts['host']??'')!==strtolower($app['host']??'')||($parts['port']??null)!==($app['port']??null)||isset($parts['user'])||isset($parts['pass'])) {
                throw new DomainException('This pack contains an external media link. Download the file and insert it using the editor upload button before sharing as Premium.');
            }
        }
        $path=rawurldecode($parts['path']??'');
        $base=defined('BASE_PATH')?rtrim(BASE_PATH,'/'):rtrim((string)(parse_url((string)getenv('APP_URL'),PHP_URL_PATH)??''),'/');
        if($base!==''&&strpos($path,$base.'/')===0)$path=substr($path,strlen($base)+1);
        $path=ltrim($path,'/');
        if(!preg_match('#^(?:uploads/(?:materials|lesson_pages|lesson_editor|activities|fsl_custom_videos|lesson_plans)|videos/fsl)/[A-Za-z0-9_./-]+$#D',$path)||preg_match('#(?:^|/)\.{1,2}(?:/|$)#',$path)||strpos($path,'//')!==false) {
            throw new DomainException('A media item cannot be prepared automatically. Reinsert it using the editor upload button and save the slide.');
        }
        $file=$this->publicRoot.'/'.$path;
        $real=realpath($file);
        if(!$real||!is_file($real)||strpos($real,$this->publicRoot.'/')!==0)throw new DomainException('A lesson media file is missing. Upload it again before sharing.');
        $segment=$this->publicRoot;
        foreach(explode('/',$path) as $part) { $segment.='/'.$part;if(is_link($segment))throw new DomainException('Linked media files cannot be shared as Premium. Upload a regular file instead.'); }
        if(strpos($path,'uploads/')===0) {
            if($this->otherPremiumMedia===null) {
                $this->otherPremiumMedia=$this->finance->query("SELECT r.content,r.guide_questions,r.media_path FROM resource_hub_packs p JOIN finance_pack_pricing f ON f.pack_id=p.id JOIN resource_hub_pages r ON r.pack_id=p.id WHERE COALESCE(f.approved_amount,f.proposed_amount)>0 AND p.created_by<>? UNION ALL SELECT a.instructions,a.activity_data,NULL FROM resource_hub_packs p JOIN finance_pack_pricing f ON f.pack_id=p.id JOIN resource_hub_activities a ON a.pack_id=p.id WHERE COALESCE(f.approved_amount,f.proposed_amount)>0 AND p.created_by<>?",[$owner,$owner])->fetchAll();
            }
            foreach($this->otherPremiumMedia as $reference)if(PremiumMediaService::referencesLegacy($reference,$path))throw new DomainException('This media belongs to another creator’s Premium pack. Replace it with your own upload before sharing.');
        }
        $size=filesize($real);
        if(!$size||$size>50*1024*1024)throw new DomainException('Each premium media file must be between 1 byte and 50 MB.');
        $this->bytes+=$size;
        if($this->bytes>200*1024*1024||count($this->copied)>=100)throw new DomainException('This pack has too much media for one submission. Split it into smaller packs.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($real);PremiumMediaService::requireMime($mime);
        $hash=hash_file('sha256',$real);
        // Reuse a matching private upload owned by this teacher, including uploads from the share dialog.
        $existing=$this->finance->query('SELECT * FROM finance_media_assets WHERE owner_id=? AND sha256=?',[$owner,$hash])->fetchAll();
        foreach($existing as $asset) {
            $token=$asset['token'];$target=$this->storage.'/'.$token;
            if(PremiumMediaService::validToken($token)&&!is_link($target)&&is_file($target)&&
                (int)$asset['byte_size']===$size&&filesize($target)===$size&&hash_equals($hash,hash_file('sha256',$target))) {
                return $this->copied[$url]='finance/media/'.$token;
            }
        }
        $token=bin2hex(random_bytes(32));$target=$this->storage.'/'.$token;
        $handle=fopen($target,'xb');
        if(!$handle)throw new RuntimeException('Unable to prepare private media. Check private storage.');
        $this->created[]=$target;chmod($target,0600);
        $source=fopen($real,'rb');
        try { if(!$source||stream_copy_to_stream($source,$handle)!==$size)throw new RuntimeException('Unable to copy lesson media.'); }
        finally { if(is_resource($source))fclose($source);fclose($handle); }
        if(!hash_equals($hash,hash_file('sha256',$target)))throw new RuntimeException('Lesson media changed during sharing. Please try again.');
        $this->finance->query('INSERT INTO finance_media_assets(token,owner_id,original_name,mime_type,byte_size,sha256,created_at) VALUES(?,?,?,?,?,?,UTC_TIMESTAMP())',[$token,$owner,basename($real),$mime,$size,$hash]);
        return $this->copied[$url]='finance/media/'.$token;
    }

    /** Called only after the enclosing DB transaction has rolled back. */
    public function discard(): void { foreach($this->created as $path)if(is_file($path))unlink($path);$this->created=[]; }
}
