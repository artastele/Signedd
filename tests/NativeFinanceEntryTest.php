<?php
declare(strict_types=1);
// Actual read-only page context, native view partials and order rules with in-memory PDO fixtures.
// No env loader, application bootstrap, real database or provider calls.
require_once __DIR__.'/../app/Services/FinanceService.php';
class NativeFinanceStatement extends PDOStatement {
    private $handler; private array $rows=[];
    public function __construct(callable $handler){$this->handler=$handler;}
    public function execute(?array $params=null):bool{$this->rows=($this->handler)($params??[]);return true;}
    public function fetch(int $mode=PDO::FETCH_DEFAULT,int $orientation=PDO::FETCH_ORI_NEXT,int $offset=0):mixed{return $this->rows[0]??false;}
    public function fetchAll(int $mode=PDO::FETCH_DEFAULT,mixed ...$args):array{return $this->rows;}
    public function fetchColumn(int $column=0):mixed{return $this->rows?array_values($this->rows[0])[$column]??false:false;}
}
class NativeFinanceDatabase extends PDO {
    public array $queries=[],$writes=[],$periods=[];public ?array $pending=null;public bool $owned=false;private bool $tx=false;private array $save=[];
    public function __construct(){}
    public function query(string $sql,?int $fetchMode=null,mixed ...$args):PDOStatement|false{$s=$this->prepare($sql);$s->execute();return $s;}
    public function beginTransaction():bool{$this->tx=true;$this->save=$this->writes;return true;}
    public function commit():bool{$this->tx=false;return true;}
    public function rollBack():bool{$this->tx=false;$this->writes=$this->save;return true;}
    public function inTransaction():bool{return $this->tx;}
    public function lastInsertId(?string $name=null):string|false{return '101';}
    public function prepare(string $sql,array $options=[]):PDOStatement|false{
        return new NativeFinanceStatement(function(array $p)use($sql):array{
            $this->queries[]=[$sql,$p];
            if(strpos($sql,'SELECT id FROM finance_orders LIMIT 0')!==false)return [];
            if(strpos($sql,'FROM users')!==false)return [['id'=>$p[0],'name'=>'Fixture','status'=>'active','role'=>$p[0]===11?'principal':($p[0]===1?'admin':($p[0]===33?'parent':'sped_teacher')),'school_id'=>10]];
            if(strpos($sql,'SELECT a.id FROM finance_audit')!==false)return [];
            if(strpos($sql,'SELECT activated_at,starts_at,ends_at')!==false)return [];
            if(strpos($sql,'SELECT starts_at,ends_at')!==false)return $this->periods;
            if(strpos($sql,'SELECT id,status,access_status FROM finance_orders')!==false)return $this->pending?[$this->pending]:[];
            if(strpos($sql,'SELECT * FROM finance_orders WHERE id=')!==false)return [['id'=>$p[0],'buyer_id'=>22,'school_id'=>10,'kind'=>'pack']];
            if(strpos($sql,'SELECT p.*,f.approved_amount')!==false)return [['id'=>$p[0],'created_by'=>11,'status'=>'published','review_status'=>'APPROVED','approved_amount'=>7900,'title'=>'Verified server-priced pack']];
            if(strpos($sql,'SELECT id FROM finance_entitlements')!==false)return $this->owned?[['id'=>1]]:[];
            if(strpos($sql,"SELECT id FROM finance_orders WHERE kind=")!==false)return [];
            if(strpos($sql,'INSERT INTO finance_orders')===0){$this->writes[]=['order',$p];return [];}
            if(strpos($sql,'INSERT INTO finance_audit')===0){$this->writes[]=['audit',$p];return [];}
            throw new RuntimeException('Unexpected native-page fixture query: '.$sql);
        });
    }
}
class NativeCheckoutProvider extends PayMongoSandbox {
    public array $attributes=[];
    public function __construct(){}
    public function request(string $method,string $path,?array $attributes=null):array{
        if($method!=='POST'||$path!=='/v2/checkout_sessions')throw new LogicException('Unexpected provider route.');
        $this->attributes=$attributes??[];return ['id'=>'cs_fixture','attributes'=>['livemode'=>false]];
    }
}
$checks=0;
function nativeCheck(bool $ok,string $message):void{global $checks;++$checks;if(!$ok)throw new RuntimeException($message);}
function nativeReject(callable $fn,string $message):void{try{$fn();}catch(DomainException $e){nativeCheck(true,$message);return;}nativeCheck(false,$message);}
function nativeRender(string $name,array $vars=[]):string{
    return (static function()use($name,$vars){$basePath='/Signedd/public';extract($vars,EXTR_SKIP);ob_start();require __DIR__.'/../app/Views/finance/partials/'.$name.'.php';return ob_get_clean();})();
}
function nativeFormsSafe(string $html):void{
    $dom=new DOMDocument();@$dom->loadHTML('<html><body>'.$html.'</body></html>');$xpath=new DOMXPath($dom);
    foreach($xpath->query('//form') as $form){
        nativeCheck($form->getAttribute('method')==='post'&&$form->getAttribute('action')==='/Signedd/public/finance/action','Native form uses centralized POST action');
        nativeCheck($xpath->query('.//input[@name="_finance_csrf"]',$form)->length===1,'Native form has strict financial CSRF token');
        nativeCheck($xpath->query('.//input[@name="price" or @name="amount" or @name="buyer_id" or @name="access_status" or @name="return_url"]',$form)->length===0,'Native form supplies no trusted financial/access fields or redirect target');
    }
}
$previous=getenv('SIGNED_FINANCE_ENABLED');$previousBase=getenv('SIGNED_FINANCE_BASE_URL');$previousMethods=getenv('PAYMONGO_PAYMENT_METHODS');$_SESSION=['user_id'=>22,'role'=>'sped_teacher','finance_csrf'=>str_repeat('a',64)];
try{
    $db=new NativeFinanceDatabase();$f=new FinanceService($db);putenv('SIGNED_FINANCE_ENABLED=false');
    $disabled=$f->customerContext(22);nativeCheck(!$disabled['enabled']&&!$db->queries,'Disabled native context never touches DB/schema');
    $html=nativeRender('hub_subscription');nativeCheck(strpos($html,'Upgrade to Premium — ₱199/month')!==false&&strpos($html,'aria-disabled="true"')!==false,'Disabled setup shows native upgrade without enabling purchases');nativeFormsSafe($html);
    putenv('SIGNED_FINANCE_ENABLED=true');
    $now='2026-11-03 12:00:00';$db->periods=[['starts_at'=>'2026-10-20 00:00:00','ends_at'=>'2026-11-20 00:00:00'],['starts_at'=>'2026-11-20 00:00:00','ends_at'=>'2026-12-20 00:00:00']];
    $teacher=$f->customerContext(22,$now);nativeCheck($teacher['subscription']['active']&&$teacher['subscription']['expires_at']==='2026-12-20 00:00:00','Active subscription includes contiguous prepaid renewal');
    $html=nativeRender('hub_subscription',['financeContext'=>$teacher]);nativeCheck(strpos($html,'Renew Premium — ₱199/month')!==false&&strpos($html,'2026-12-20')!==false,'Native Hub shows expiry and manual renewal');nativeFormsSafe($html);
    nativeCheck(strpos($html,'/finance?tab=earnings')!==false,'Creator earnings links to existing Finance tab');
    $db->periods=[['starts_at'=>'2026-10-01 00:00:00','ends_at'=>$now]];$expired=$f->customerContext(22,$now);nativeCheck(!$expired['subscription']['active']&&$expired['subscription']['state']==='EXPIRED','Subscription expires exactly at end');
    $html=nativeRender('hub_subscription',['financeContext'=>$expired]);nativeCheck(strpos($html,'Individually purchased packs remain owned')!==false,'Expiry keeps ownership explanation');
    $gap=FinanceRules::subscriptionSummary([['starts_at'=>'2026-11-01 00:00:00','ends_at'=>'2026-11-10 00:00:00'],['starts_at'=>'2026-11-20 00:00:00','ends_at'=>'2026-12-20 00:00:00']],$now);nativeCheck($gap['expires_at']==='2026-11-10 00:00:00','Future renewal separated by suspended gap does not overstate current access');
    $scheduled=FinanceRules::subscriptionSummary([['starts_at'=>'2026-11-20 00:00:00','ends_at'=>'2026-12-20 00:00:00']],$now);nativeCheck(!$scheduled['active']&&$scheduled['state']==='SCHEDULED','Future term is not active access');
    $db->pending=['id'=>99,'status'=>'PENDING','access_status'=>'PENDING'];$pending=$f->customerContext(22,$now);$html=nativeRender('hub_subscription',['financeContext'=>$pending]);nativeCheck(strpos($html,'/finance/payment/99')!==false&&strpos($html,'Continue Premium Payment')!==false&&strpos($html,'<form')===false,'Pending subscription resumes central payment request without another purchase form');
    $db->pending=null;$principal=$f->customerContext(11,$now);nativeCheck($principal['school']['state']==='LEGACY'&&$principal['school']['allowed'],'Native principal status preserves legacy school safety');
    $_SESSION['role']='principal';$html=nativeRender('school_license',['financeContext'=>$principal]);nativeCheck(strpos($html,'Activate School License')!==false&&strpos($html,'/finance/license')!==false&&strpos($html,'<form')===false,'Principal dashboard links to read-only license overview');nativeFormsSafe($html);
    foreach(['ACTIVE','GRACE','EXPIRED'] as $state){$context=$principal;$context['school']=['state'=>$state,'enrolled'=>true,'allowed'=>$state!=='EXPIRED','expires_at'=>'2026-11-01 00:00:00','activation'=>['activated_at'=>'2025-11-01 00:00:00']];$html=nativeRender('school_license',['financeContext'=>$context]);nativeCheck(strpos($html,'Renew License')!==false&&strpos($html,'2025-11-01')!==false,'Enrolled principal sees activation, expiry and renewal '.$state);}
    $principal['pending_order']=['id'=>88,'status'=>'PENDING','access_status'=>'PENDING'];$html=nativeRender('school_license',['financeContext'=>$principal]);nativeCheck(strpos($html,'awaiting payment verification')!==false&&strpos($html,'/finance/payment/88')!==false&&strpos($html,'<form')===false,'Pending application resumes payment without another purchase');
    $_SESSION['role']='sped_teacher';$basePack=['id'=>77,'title'=>'Pack <script>alert(1)</script>','price_centavos'=>7900,'finance_review'=>'APPROVED','access_allowed'=>false,'access_label'=>'Requires Purchase'];
    $html=nativeRender('pack_actions',['pack'=>$basePack,'financeContext'=>$teacher]);nativeCheck(strpos($html,'Buy Pack — ₱79.00')!==false&&strpos($html,'/finance/pack/77')!==false&&strpos($html,'<form')===false,'Premium card opens pack details without creating an order');nativeFormsSafe($html);
    foreach(['Owned','Included in Subscription','Free'] as $label){$pack=$basePack;$pack['access_label']=$label;$pack['access_allowed']=true;$html=nativeRender('pack_actions',['pack'=>$pack,'financeContext'=>$teacher]);nativeCheck(strpos($html,'Use as Template')!==false&&strpos($html,'Buy Pack')===false,'Authorized card uses existing clone action '.$label);nativeCheck(strpos($html,'<script>')===false,'Pack title safely escaped in preserved clone handler');}
    $pack=$basePack;$pack['finance_review']='PENDING';$pack['access_label']='Awaiting review';$html=nativeRender('pack_actions',['pack'=>$pack,'financeContext'=>$teacher]);nativeCheck(strpos($html,'Buy Pack')===false,'Unapproved pack is not offered for purchase');
    $html=nativeRender('pack_actions',['pack'=>$basePack,'financeContext'=>$disabled]);nativeCheck(strpos($html,'Buy Pack')===false,'Premium card cannot enable checkout while Finance disabled');
    $admin=$f->customerContext(1,$now);$_SESSION['role']='admin';$html=nativeRender('hub_subscription',['financeContext'=>$admin]);nativeCheck(strpos($html,'name="kind"')===false,'Admin Hub does not offer unsupported customer purchases');
    nativeReject(fn()=>$f->customerContext(33,$now),'Unrelated role cannot load customer finance state');
    nativeCheck(!$db->writes,'All native page/status requests are read only');
    nativeCheck($f->order(101,22)['buyer_id']===22,'Buyer may view centralized payment request');nativeReject(fn()=>$f->order(101,44),'Unrelated teacher cannot view payment request');
    $_POST=['amount'=>1,'price'=>1,'buyer_id'=>999,'access_status'=>'ACTIVE'];$f->createOrder(22,'pack',77);$orders=array_values(array_filter($db->writes,fn($w)=>$w[0]==='order'));nativeCheck($orders[0][1][8]===7900&&$orders[0][1][1]===22,'Existing order service ignores browser price and buyer claims');
    $db->owned=true;$before=count($db->writes);nativeReject(fn()=>$f->createOrder(22,'pack',77),'Already-owned pack cannot be billed again');nativeCheck(count($db->writes)===$before,'Rejected repeat purchase creates no order/accounting effect');
    $provider=new NativeCheckoutProvider();putenv('SIGNED_FINANCE_BASE_URL=http://localhost:8888/Signedd/public');putenv('PAYMONGO_PAYMENT_METHODS=card,gcash');
    $provider->createCheckout(['id'=>101,'item_name'=>'Pack','reference'=>'SIGNED-T-TEST','buyer_id'=>22,'amount'=>7900]);
    nativeCheck($provider->attributes['success_url']==='http://localhost:8888/Signedd/public/finance/payment/101?payment_return=success'&&$provider->attributes['cancel_url']==='http://localhost:8888/Signedd/public/finance/payment/101?payment_return=cancelled','Checkout distinguishes successful return for secure automatic reconciliation');
    nativeCheck($provider->attributes['line_items'][0]['amount']===7900&&$provider->attributes['metadata']['buyer_id']==='22','Checkout continues using persisted server order data');
    require_once __DIR__.'/../app/Middleware/SchoolLicenseMiddleware.php';$before=count($db->queries);
    nativeCheck(SchoolLicenseMiddleware::decision('/finance/payment/101',11,$f)['allowed']&&count($db->queries)===$before,'Expired principal payment-request path remains outside license gate');
    $html=nativeRender('school_license',['financeContext'=>['enabled'=>true,'school'=>null,'pending_order'=>null]]);nativeCheck(strpos($html,'Associate your principal account')!==false&&strpos($html,'aria-disabled="true"')!==false,'Unlinked principal cannot initiate a school application from native UI');
    echo "PASS: $checks native finance entry-point checks; in-memory fixtures/view rendering only.\n";
}finally{if($previous===false)putenv('SIGNED_FINANCE_ENABLED');else putenv('SIGNED_FINANCE_ENABLED='.$previous);if($previousBase===false)putenv('SIGNED_FINANCE_BASE_URL');else putenv('SIGNED_FINANCE_BASE_URL='.$previousBase);if($previousMethods===false)putenv('PAYMONGO_PAYMENT_METHODS');else putenv('PAYMONGO_PAYMENT_METHODS='.$previousMethods);}
