<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../Services/PremiumMediaService.php';

final class PremiumMediaController {
    private function finance(): FinanceService { return new FinanceService(Database::getInstance()->getConnection()); }
    private function actor(): int {
        $id=(int)($_SESSION['user_id']??0);
        if(!$id) throw new DomainException('Authentication required.');
        return $id;
    }
    private function error(Throwable $e): void {
        http_response_code($e instanceof DomainException?403:503);
        header('Cache-Control: private, no-store, max-age=0'); header('Content-Type: text/plain; charset=utf-8');
        echo $e instanceof DomainException?'Media access denied.':'Private media unavailable; check storage and schema configuration.';
    }
    public function upload(): void {
        try {
            $actor=$this->actor(); $f=$this->finance();
            if(!$f->ready()) throw new RuntimeException('Finance installation required.');
            $token=$_POST['_finance_csrf']??$_SERVER['HTTP_X_FINANCE_CSRF']??'';
            if(!is_string($token) || empty($_SESSION['finance_csrf']) || !hash_equals($_SESSION['finance_csrf'],$token)) throw new DomainException('Invalid upload CSRF.');
            $media=new PremiumMediaService($f); $result=$media->upload($_FILES['file']??$_FILES['image']??[],$actor);
            header('Cache-Control: no-store'); header('Content-Type: application/json');
            echo json_encode(['success'=>true]+$result,JSON_THROW_ON_ERROR);
        } catch(Throwable $e) { $this->error($e); }
    }
    public function view($token): void {
        try {
            $actor=$this->actor(); $f=$this->finance();
            $media=new PremiumMediaService($f);
            $this->stream($media->resolve((string)$token,$actor));
        } catch(Throwable $e) { $this->error($e); }
    }
    public function legacy($category,$tail): void {
        try {
            $actor=$this->actor(); $f=$this->finance();
            // Legacy guard must work even before private storage is configured.
            $service=new PremiumMediaService($f,null,null,false);
            $db=Database::getInstance()->getConnection();
            if(!FinanceService::accessEnabled($db)) {
                // Pre-migration: preserve authenticated free educational files without finance queries.
                $path=PremiumMediaService::canonicalLegacy('uploads/'.$category.'/'.$tail);
                $root=realpath(__DIR__.'/../../public/uploads'); $file=realpath(__DIR__.'/../../public/'.$path);
                if(!$root || !$file || strpos($file,$root.'/')!==0 || !is_file($file)) throw new DomainException('Unavailable.');
                $f->user($actor); $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file); $attachment=false;
                try { PremiumMediaService::requireMime($mime); } catch(DomainException $e) { $mime='application/octet-stream'; $attachment=true; }
                $info=['path'=>$file,'size'=>filesize($file),'mime'=>$mime,'name'=>basename($file),'force_download'=>$attachment];
            } else $info=$service->legacy('uploads/'.$category.'/'.$tail,$actor,dirname(__DIR__,2).'/public');
            $this->stream($info);
        } catch(Throwable $e) { $this->error($e); }
    }
    private function stream(array $info): void {
        header('Cache-Control: private, no-store, max-age=0'); header('Pragma: no-cache'); header('Vary: Cookie');
        header('X-Content-Type-Options: nosniff'); header("Content-Security-Policy: sandbox; default-src 'none'");
        header('Cross-Origin-Resource-Policy: same-origin'); header('Referrer-Policy: no-referrer');
        header('Accept-Ranges: bytes');
        try { [$start,$end,$partial]=PremiumMediaService::range($_SERVER['HTTP_RANGE']??null,$info['size']); }
        catch(OutOfRangeException $e) { http_response_code(416); header('Content-Range: bytes */'.$info['size']); return; }
        if($partial) { http_response_code(206); header('Content-Range: bytes '.$start.'-'.$end.'/'.$info['size']); }
        header('Content-Type: '.$info['mime']); header('Content-Length: '.($end-$start+1));
        // Filename is never used to locate files or echoed into a header unsanitized.
        $name=preg_replace('/[^A-Za-z0-9_.-]/','_',basename($info['name']));
        header('Content-Disposition: '.(isset($_GET['download']) || !empty($info['force_download'])?'attachment':'inline').'; filename="'.$name.'"');
        if(($_SERVER['REQUEST_METHOD']??'GET')==='HEAD') return;
        $fp=fopen($info['path'],'rb'); if(!$fp) throw new RuntimeException('Unavailable.');
        if(session_status()===PHP_SESSION_ACTIVE) session_write_close();
        fseek($fp,$start); $remaining=$end-$start+1;
        while($remaining>0 && !feof($fp) && !connection_aborted()) { $chunk=fread($fp,min(65536,$remaining)); if($chunk===false || $chunk==='') break; echo $chunk; $remaining-=strlen($chunk); }
        fclose($fp);
    }
}
