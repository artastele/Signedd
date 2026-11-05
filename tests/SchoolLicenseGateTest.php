<?php
declare(strict_types=1);
// Real gate, finance service and renewal rules; no bootstrap, env loader, schema or real connection.
require_once __DIR__.'/../app/Middleware/SchoolLicenseMiddleware.php';
class LicenseStatement extends PDOStatement {
    private $handler; private array $rows=[];
    public function __construct(callable $handler){$this->handler=$handler;}
    public function execute(?array $params=null):bool{$this->rows=($this->handler)($params??[]);return true;}
    public function fetch(int $mode=PDO::FETCH_DEFAULT,int $orientation=PDO::FETCH_ORI_NEXT,int $offset=0):mixed{return $this->rows[0]??false;}
    public function fetchColumn(int $column=0):mixed{return $this->rows?array_values($this->rows[0])[$column]??false:false;}
}
class LicenseDatabase extends PDO {
    public array $orders=[],$audits=[],$terms=[],$queries=[];
    public array $users=[1=>['role'=>'principal','school_id'=>10],2=>['role'=>'admin','school_id'=>10],3=>['role'=>'sped_teacher','school_id'=>10],4=>['role'=>'parent','school_id'=>10],5=>['role'=>'learner','school_id'=>10],6=>['role'=>'sped_teacher','school_id'=>20],7=>['role'=>'principal','school_id'=>20],8=>['role'=>'admin','school_id'=>10,'status'=>'inactive'],10=>['role'=>'parent','school_id'=>20],11=>['role'=>'user','school_id'=>20]];
    private bool $transaction=false; private array $saved=[]; private int $lastId=0;
    public function __construct(){}
    public function beginTransaction():bool{$this->saved=[$this->orders,$this->audits,$this->terms,$this->lastId];$this->transaction=true;return true;}
    public function commit():bool{$this->transaction=false;return true;}
    public function rollBack():bool{[$this->orders,$this->audits,$this->terms,$this->lastId]=$this->saved;$this->transaction=false;return true;}
    public function inTransaction():bool{return $this->transaction;}
    public function lastInsertId(?string $name=null):string|false{return (string)$this->lastId;}
    public function query(string $query,?int $fetchMode=null,mixed ...$args):PDOStatement|false{$s=$this->prepare($query);$s->execute();return $s;}
    public function prepare(string $sql,array $options=[]):PDOStatement|false {
        return new LicenseStatement(function(array $p)use($sql):array{
            $this->queries[]=$sql;
            if(strpos($sql,'SELECT id FROM finance_orders LIMIT 0')!==false)return [];
            if(strpos($sql,'FROM users')!==false){$u=$this->users[$p[0]]??null;return $u?[array_merge(['id'=>$p[0],'name'=>'Fixture','status'=>'active'],$u)]:[];}
            if(strpos($sql,'SELECT school_name FROM schools')!==false)return [['school_name'=>'Fixture School']];
            if(strpos($sql,'SELECT id FROM schools')!==false)return [['id'=>$p[0]]];
            if(strpos($sql,"action='SCHOOL_LICENSE_REQUIRED'")!==false){foreach($this->audits as $a){$o=$this->orders[$a[0]]??null;if($o && $o['school_id']===$p[0] && $a[2]==='SCHOOL_LICENSE_REQUIRED')return [['id'=>1]];}return [];}
            if(strpos($sql,'SELECT a.id FROM finance_audit a JOIN finance_orders')!==false){foreach($this->audits as $a){$o=$this->orders[$a[0]]??null;if($o && $o['school_id']===$p[0] && $a[2]==='SCHOOL_LICENSE_ENROLLED')return [['id'=>1]];}return [];}
            if(strpos($sql,"action='LICENSE_APPLICATION_ACCEPTED'")!==false){foreach($this->audits as $a)if($a[0]===$p[0]&&$a[2]==='LICENSE_APPLICATION_ACCEPTED')return [['id'=>1]];return [];}
            if(strpos($sql,'SELECT MAX(ends_at)')!==false){$ends=[];foreach($this->terms as $t)if($t['school_id']===$p[0]&&$t['state']==='ACTIVE'&&(!isset($p[1])||$t['starts_at']<=$p[1]))$ends[]=$t['ends_at'];return [['expiry'=>$ends?max($ends):null]];}
            if(strpos($sql,'SELECT * FROM finance_orders WHERE id=')!==false)return isset($this->orders[$p[0]])?[$this->orders[$p[0]]]:[];
            if(strpos($sql,"status IN ('PENDING','PAID')")!==false){foreach(array_reverse($this->orders,true) as $id=>$o)if($o['school_id']===$p[0]&&$o['kind']==='institutional'&&in_array($o['status'],['PENDING','PAID'],true))return [['id'=>$id]];return [];}
            if(strpos($sql,"status='PENDING'")!==false&&strpos($sql,'SELECT')===0){foreach($this->orders as $id=>$o)if($o['kind']===$p[0]&&$o['school_id']===$p[1]&&$o['status']==='PENDING')return [['id'=>$id]];return [];}
            if(strpos($sql,"INSERT INTO finance_orders(reference,buyer_id,school_id,kind")===0){$id=++$this->lastId;$this->orders[$id]=['id'=>$id,'reference'=>$p[0],'buyer_id'=>$p[1],'school_id'=>$p[2],'pack_id'=>null,'creator_id'=>null,'kind'=>'institutional','buyer_name'=>$p[3],'item_name'=>$p[4],'amount'=>$p[5],'status'=>'PENDING','payment_id'=>null,'access_status'=>'PENDING'];return [];}
            if(strpos($sql,'INSERT INTO finance_orders')===0){$id=++$this->lastId;$this->orders[$id]=['id'=>$id,'reference'=>$p[0],'buyer_id'=>$p[1],'school_id'=>$p[2],'pack_id'=>$p[3],'creator_id'=>$p[4],'kind'=>$p[5],'buyer_name'=>$p[6],'item_name'=>$p[7],'amount'=>$p[8],'status'=>'PENDING','payment_id'=>null,'access_status'=>'PENDING'];return [];}
            if(strpos($sql,'INSERT INTO finance_audit')===0){$this->audits[]=$p;return [];}
            if(strpos($sql,'INSERT INTO finance_entitlements')===0){$this->terms[]=['order_id'=>$p[0],'buyer_id'=>$p[1],'school_id'=>$p[2],'kind'=>'institutional','starts_at'=>$p[3],'ends_at'=>$p[4],'activated_by'=>$p[5],'state'=>'ACTIVE'];return [];}
            if(strpos($sql,"UPDATE finance_orders SET access_status='ACTIVE'")===0){$this->orders[$p[0]]['access_status']='ACTIVE';return [];}
            throw new RuntimeException('Unexpected fixture query: '.$sql);
        });
    }
    public function paid(int $id):void{
        $o=&$this->orders[$id];$o['status']='PAID';$o['payment_id']='pay_fixture'.$id;$o['access_status']='ACTIVE';
        $expiry=null;foreach($this->terms as $term)if($term['school_id']===$o['school_id']&&$term['state']==='ACTIVE')$expiry=max($expiry??'',$term['ends_at']);
        $start=FinanceRules::schoolStart($expiry?:null,gmdate('Y-m-d H:i:s'));
        $this->terms[]=['order_id'=>$id,'buyer_id'=>$o['buyer_id'],'school_id'=>$o['school_id'],'kind'=>'institutional','starts_at'=>$start,'ends_at'=>FinanceRules::extend($start,12),'state'=>'ACTIVE'];
        $enrolled=false;foreach($this->audits as $audit)if($audit[2]==='SCHOOL_LICENSE_ENROLLED')$enrolled=true;
        if(!$enrolled)$this->audits[]=[$id,null,'SCHOOL_LICENSE_ENROLLED','Verified payment'];
        $this->audits[]=[$id,null,'LICENSE_ACTIVATED','Automatic verified payment activation'];
    }
}
// Check the real middleware's terminal response in an isolated CLI child.
if(($argv[1]??'')==='--denied'){
    putenv('SIGNED_FINANCE_ENABLED=true');$db=new LicenseDatabase();$f=new FinanceService($db);
    $db->orders[1]=['school_id'=>10];$db->audits[]=[1,2,'SCHOOL_LICENSE_ENROLLED',''];
    $db->terms[]=['school_id'=>10,'starts_at'=>'2020-01-01 00:00:00','ends_at'=>'2021-01-01 00:00:00','state'=>'ACTIVE'];
    $_SESSION=['user_id'=>3,'role'=>'admin']; // Spoofing the session role cannot bypass the database role.
    http_response_code(200);ob_start();register_shutdown_function(function(){echo json_encode(['status'=>http_response_code(),'body'=>ob_get_clean()]);});
    SchoolLicenseMiddleware::check('/learning/lesson/7',$f);echo 'UNEXPECTED_ROUTE_EXECUTION';exit;
}
$prior=getenv('SIGNED_FINANCE_ENABLED');$checks=0;
function licenseCheck(bool $ok,string $message):void{global $checks;++$checks;if(!$ok)throw new RuntimeException($message);}
function licenseReject(callable $fn,string $message):void{try{$fn();}catch(DomainException|RuntimeException $e){licenseCheck(true,$message);return;}licenseCheck(false,$message);}
try{
    $db=new LicenseDatabase();$f=new FinanceService($db);putenv('SIGNED_FINANCE_ENABLED=false');
    foreach([1,2,3,4,5,6,999] as $id)licenseCheck(SchoolLicenseMiddleware::decision('/learning/lesson/1',$id,$f)['allowed'],'Disabled gate preserves all current route authorization');
    licenseCheck($f->schoolStatus(10)['allowed']&&!$db->queries,'Disabled status and middleware never query finance/schema/user tables');
    licenseCheck(!$db->queries,'Disabled Finance performs no licensing DB work');
    putenv('SIGNED_FINANCE_ENABLED=true');
    foreach(['/login','/logout','/register','/auth/google/callback','/finance','/finance/action','/finance/document/1/billing','/finance/webhook','/role/select','/role/submit-staff','/profile','/notifications/get'] as $path)licenseCheck(SchoolLicenseMiddleware::decision($path,3,$f)['allowed'],'Auth/setup/billing/renewal/webhook route remains outside gate: '.$path);
    licenseCheck(!$db->queries,'Exempt routes do not consult license/account state');
    licenseCheck(SchoolLicenseMiddleware::decision('/learning/lesson/1',0,$f)['state']==='AUTH_REQUIRED','Unauthenticated callers retain existing route login enforcement');
    foreach([1,3,4,5,6] as $id){$s=SchoolLicenseMiddleware::decision('/iep/implementation',$id,$f);licenseCheck($s['allowed']&&$s['state']==='LEGACY','Legacy school keeps access after installation and enablement '.$id);}
    $newSchoolDb=new LicenseDatabase();$newSchoolFinance=new FinanceService($newSchoolDb);$newOrder=$newSchoolFinance->prepareNewSchoolLicense(7,2);
    licenseCheck($newOrder>0&&$newSchoolDb->orders[$newOrder]['amount']===FinanceRules::SCHOOL_PRICE,'Principal approval creates one server-priced annual license request');
    licenseCheck($newSchoolFinance->prepareNewSchoolLicense(7,2)===$newOrder&&count($newSchoolDb->orders)===1,'Repeated approval preparation reuses the pending license request');
    $required=$newSchoolFinance->schoolStatus(20);licenseCheck(!$required['allowed']&&$required['state']==='PAYMENT_REQUIRED'&&$required['required'],'New approved school waits for verified annual payment');
    licenseCheck(!SchoolLicenseMiddleware::decision('/iep/implementation',6,$newSchoolFinance)['allowed'],'Payment-required new school is blocked on teaching routes');
    licenseCheck(SchoolLicenseMiddleware::decision('/finance',7,$newSchoolFinance)['allowed'],'Payment-required Principal can reach billing and checkout');
    foreach(['/dashboard','/principal/staff-requests','/principal/register-staff','/principal/assign-teacher','/enrollment','/progress-reports','/cot/observations'] as $path)licenseCheck(!SchoolLicenseMiddleware::decision($path,7,$newSchoolFinance)['allowed'],'Payment-required new school closes operational route '.$path);
    foreach([6,10,11] as $id)licenseCheck(!SchoolLicenseMiddleware::decision('/dashboard',$id,$newSchoolFinance)['allowed'],'Unpaid school dashboard is hidden from faculty, parent, and ordinary user '.$id);
    licenseCheck(SchoolLicenseMiddleware::decision('/role/submit-staff',11,$newSchoolFinance)['allowed'],'Faculty application submission remains open before payment');
    $newSchoolDb->paid($newOrder);licenseCheck($newSchoolFinance->schoolStatus(20)['state']==='ACTIVE','Verified payment automatically activates the new school license');
    $historicalDb=new LicenseDatabase();$historicalDb->orders[90]=['school_id'=>20];$historicalDb->audits[]=[90,2,'LICENSE_ACTIVATED','Historical activation'];$historicalDb->terms[]=['school_id'=>20,'starts_at'=>'2020-01-01 00:00:00','ends_at'=>'2021-01-01 00:00:00','state'=>'ACTIVE'];
    licenseCheck((new FinanceService($historicalDb))->schoolStatus(20)['state']==='LEGACY','Historical activation/expired terms do not retroactively enroll a school');
    $db->users[9]=['role'=>'sped_teacher','school_id'=>null];licenseCheck(SchoolLicenseMiddleware::decision('/learning/lesson/1',9,$f)['state']==='NO_SCHOOL','Existing users without school linkage keep normal role-based access');
    licenseReject(fn()=>$f->createOrder(1,'institutional'),'Principal application requires explicit opt-in');
    licenseCheck(!$db->orders&&!$db->audits,'Rejected opt-in creates no financial state');
    licenseReject(fn()=>$f->createOrder(3,'institutional',null,true),'Teacher cannot enroll a school');
    $order=$f->createOrder(1,'institutional',null,true);
    licenseCheck($f->schoolStatus(10)['state']==='LEGACY','Pending application does not revoke legacy access');
    licenseCheck($f->createOrder(1,'institutional',null,true)===$order&&count($db->orders)===1,'Repeated principal application reuses pending order');
    $consents=array_filter($db->audits,fn($a)=>$a[2]==='LICENSE_APPLICATION_ACCEPTED');licenseCheck(count($consents)===1,'Opt-in audit is idempotent');
    licenseCheck($f->schoolStatus(10)['state']==='LEGACY','Unpaid application does not activate or enroll');
    $db->paid($order);
    licenseCheck(count($db->terms)===1&&$f->schoolStatus(10)['state']==='ACTIVE','Verified payment immediately activates and enrolls the school');
    $f->admin(2);licenseCheck(true,'Active System Admin retains financial authorization');
    licenseReject(fn()=>$f->admin(3),'Unrelated teacher cannot administer finance');
    licenseCheck($f->schoolStatus(20)['state']==='LEGACY','Another existing school is not enrolled');
    $markers=array_filter($db->audits,fn($a)=>$a[2]==='SCHOOL_LICENSE_ENROLLED');licenseCheck(count($markers)===1,'Explicit enrollment recorded once');
    $db->terms[0]['ends_at']=gmdate('Y-m-d H:i:s',time()+90*86400);$oldEnd=$db->terms[0]['ends_at'];
    $renew=$f->createOrder(1,'institutional',null,true);$db->paid($renew);
    licenseCheck($db->terms[1]['starts_at']===$oldEnd&&$db->terms[1]['ends_at']===FinanceRules::extend($oldEnd,12),'Early renewal extends existing expiry');
    licenseCheck(count(array_filter($db->audits,fn($a)=>$a[2]==='SCHOOL_LICENSE_ENROLLED'))===1,'Renewal does not reenroll or duplicate school identity');
    $clock='2026-11-03 12:00:00';
    foreach([['2026-11-03 12:00:01','ACTIVE',true],['2026-11-03 12:00:00','GRACE',true],['2026-10-04 12:00:00','GRACE',true],['2026-10-04 11:59:59','EXPIRED',false]] as [$end,$state,$allowed]){
        $db->terms=[['school_id'=>10,'starts_at'=>'2025-01-01 00:00:00','ends_at'=>$end,'state'=>'ACTIVE']];$s=$f->schoolStatus(10,$clock);licenseCheck($s['state']===$state&&$s['allowed']===$allowed,'Active/grace/expiration boundary '.$end);
    }
    $db->terms=[['school_id'=>10,'starts_at'=>'2020-01-01 00:00:00','ends_at'=>'2021-01-01 00:00:00','state'=>'ACTIVE']];
    foreach(['/iep/implementation','/iep/implementation/save-activity','/teacher/resource-hub/preview/7','/learning/lesson/7','/teacher-fsl-training'] as $path)licenseCheck(!SchoolLicenseMiddleware::decision($path,3,$f)['allowed'],'Expired enrolled school blocked in backend '.$path);
    licenseCheck(SchoolLicenseMiddleware::decision('/learning/lesson/7',2,$f)['state']==='ADMIN','System Admin bypass uses authoritative server role');
    foreach(['/finance','/finance/action','/finance/webhook','/login'] as $path)licenseCheck(SchoolLicenseMiddleware::decision($path,1,$f)['allowed'],'Expired principal can reach renewal/payment/auth '.$path);
    $late=$f->createOrder(1,'institutional',null,true);$before=gmdate('Y-m-d H:i:s');$db->paid($late);$last=end($db->terms);
    licenseCheck($last['starts_at']>=$before&&$last['starts_at']<=gmdate('Y-m-d H:i:s'),'After-grace renewal starts on verified payment');
    licenseCheck(FinanceRules::schoolStart('2026-10-10 12:00:00',$clock)==='2026-10-10 12:00:00','Renewal within grace extends old expiry');
    $graceExpiry=gmdate('Y-m-d H:i:s',time()-2*86400);$db->terms=[['school_id'=>10,'starts_at'=>'2025-01-01 00:00:00','ends_at'=>$graceExpiry,'state'=>'ACTIVE']];
    $graceRenew=$f->createOrder(1,'institutional',null,true);$db->paid($graceRenew);$graceTerm=end($db->terms);licenseCheck($graceTerm['starts_at']===$graceExpiry&&$graceTerm['ends_at']===FinanceRules::extend($graceExpiry,12),'Verified renewal within grace extends previous expiry');
    $db->terms=[];licenseCheck(!$f->schoolStatus(10)['allowed']&&$f->schoolStatus(20)['allowed'],'Missing/revoked enrolled term requires review without restricting legacy schools');
    putenv('SIGNED_FINANCE_ENABLED=false');$queryCount=count($db->queries);
    licenseCheck(SchoolLicenseMiddleware::decision('/learning/lesson/7',3,$f)['allowed']&&count($db->queries)===$queryCount,'Turning Finance off restores school access without DB writes/queries');
    licenseCheck(!array_filter($db->queries,fn($sql)=>(bool)preg_match('/^\s*(?:UPDATE|INSERT INTO|DELETE FROM)\s+(?:schools|users|student_records|iep_records|lms_grades|resource_hub_packs)\b/i',$sql)),'Enrollment/expiry/renewal never modify educational or account records');
    $process=proc_open([PHP_BINARY,__FILE__,'--denied'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);if(!is_resource($process))throw new RuntimeException('Cannot start isolated gate check');fclose($pipes[0]);$out=stream_get_contents($pipes[1]);fclose($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[2]);$code=proc_close($process);$response=json_decode($out,true);
    licenseCheck($code===0&&$err===''&&$response['status']===403&&strpos($response['body'],'UNEXPECTED_ROUTE_EXECUTION')===false,'Real middleware stops expired requests before protected route execution');
    licenseCheck(strpos($response['body'],'/finance')!==false,'Denied response offers billing link without redirect loop');
    echo "PASS: $checks institutional license gate checks; no database, configuration, or network modified.\n";
}finally{if($prior===false)putenv('SIGNED_FINANCE_ENABLED');else putenv('SIGNED_FINANCE_ENABLED='.$prior);}
