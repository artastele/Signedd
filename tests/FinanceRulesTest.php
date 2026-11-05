<?php
declare(strict_types=1);
// Isolated rules and security checks: no env loader, bootstrap, SQL, or provider calls.
require_once __DIR__.'/../app/Services/FinanceRules.php';
require_once __DIR__.'/../app/Services/FinancePdf.php';
require_once __DIR__.'/../app/Services/FinanceService.php';
$checks=0;
function checkFinance(bool $value,string $message): void { global $checks; ++$checks; if(!$value) throw new RuntimeException($message); }
function rejectsFinance(callable $f,string $message): void {
    try { $f(); } catch(DomainException|InvalidArgumentException|RuntimeException $e) { checkFinance(true,$message); return; }
    checkFinance(false,$message);
}
foreach(['institutional'=>1500000,'subscription'=>19900,'pack'=>7900] as $kind=>$gross) {
    foreach([0,1,199,$gross] as $fee) {
        $a=FinanceRules::split($gross,$fee,$kind);
        checkFinance($a['gross']===$a['fee']+$a['net'],'gross reconciles');
        checkFinance($a['net']===$a['signed_share']+$a['creator_pool'],'net reconciles');
        checkFinance($a['creator_pool']===($kind==='institutional'?0:intdiv(($gross-$fee)*70,100)),'70% exact split');
        checkFinance($a['undistributed']===($kind==='subscription'?$a['creator_pool']:0),'undistributed remains liability');
    }
}
rejectsFinance(fn()=>FinanceRules::split(7900,-1,'pack'),'negative fee');
rejectsFinance(fn()=>FinanceRules::split(7900,7901,'pack'),'excessive fee');
checkFinance(FinanceRules::extend('2026-01-31 12:30:00',1)==='2026-02-28 12:30:00','month-end clamped');
checkFinance(FinanceRules::extend('2024-01-31 12:30:00',1)==='2024-02-29 12:30:00','leap month');
checkFinance(FinanceRules::extend('2024-02-29 12:30:00',12)==='2025-02-28 12:30:00','leap year licensing');
checkFinance(FinanceRules::schoolStart('2026-10-01 00:00:00','2026-10-31 00:00:00')==='2026-10-01 00:00:00','inclusive 30 day grace');
checkFinance(FinanceRules::schoolStart('2026-10-01 00:00:00','2026-10-31 00:00:01')==='2026-10-31 00:00:01','after grace starts now');
checkFinance(FinanceRules::schoolStart('2027-01-01 00:00:00','2026-11-03 00:00:00')==='2027-01-01 00:00:00','early renewal extends expiry');
$raw='{"hello":"world"}'; $secret='isolated-test-endpoint-secret'; $now=1700000000;
$header='t='.$now.',te='.hash_hmac('sha256',$now.'.'.$raw,$secret).',li=';
FinanceRules::signature($raw,$header,$secret,$now); checkFinance(true,'valid signature');
rejectsFinance(fn()=>FinanceRules::signature($raw.' ',$header,$secret,$now),'exact raw body');
rejectsFinance(fn()=>FinanceRules::signature($raw,$header,'wrong',$now),'wrong endpoint secret');
rejectsFinance(fn()=>FinanceRules::signature($raw,$header,$secret,$now+301),'stale replay');
rejectsFinance(fn()=>FinanceRules::signature($raw,$header.',t='.$now,$secret,$now),'duplicate timestamp');
rejectsFinance(fn()=>FinanceRules::signature($raw,str_replace('li=','li=abc',$header),$secret,$now),'live signature');
$o=['checkout_id'=>'cs_test','reference'=>'SIGNED-T-TEST','buyer_id'=>5,'amount'=>7900];
$session=['id'=>'cs_test','attributes'=>['livemode'=>false,'reference_number'=>'SIGNED-T-TEST','metadata'=>['order_reference'=>'SIGNED-T-TEST','buyer_id'=>'5'],
    'payments'=>[['id'=>'pay_test','attributes'=>['status'=>'paid','livemode'=>false,'currency'=>'PHP','amount'=>7900,'fee'=>300,'net_amount'=>7600]]]]];
$p=FinanceRules::paidSession($o,$session); checkFinance($p['fee']===300,'actual verified fee');
$mutations=[
 fn(&$s)=>$s['id']='cs_wrong',fn(&$s)=>$s['attributes']['livemode']=true,
 fn(&$s)=>$s['attributes']['reference_number']='wrong',fn(&$s)=>$s['attributes']['metadata']['buyer_id']='6',
 fn(&$s)=>$s['attributes']['metadata']['order_reference']='wrong',
 fn(&$s)=>$s['attributes']['payments'][0]['attributes']['amount']=7800,
 fn(&$s)=>$s['attributes']['payments'][0]['attributes']['amount']='7900',
 fn(&$s)=>$s['attributes']['payments'][0]['attributes']['currency']='USD',
 fn(&$s)=>$s['attributes']['payments'][0]['attributes']['livemode']=true,
 fn(&$s)=>$s['attributes']['payments'][0]['attributes']['status']='failed',
 fn(&$s)=>$s['attributes']['payments'][]=$s['attributes']['payments'][0],
];
foreach($mutations as $mutate) { $s=$session; $mutate($s); rejectsFinance(fn()=>FinanceRules::paidSession($o,$s),'tampered checkout/payment rejected'); }
$s=$session; unset($s['attributes']['payments'][0]['attributes']['fee']);
checkFinance(FinanceRules::paidSession($o,$s)['fee']===null,'missing fee remains pending');
$s=$session; $s['attributes']['payments'][0]['attributes']['net_amount']=7900;
checkFinance(FinanceRules::paidSession($o,$s)['fee']===null,'unreconciled fee remains pending');
$s=$session; $s['attributes']['payments'][]=['id'=>'pay_failed','attributes'=>['status'=>'failed']];
checkFinance(FinanceRules::paidSession($o,$s)['id']==='pay_test','retry array selects paid payment');
$doc=['reference'=>'SIGNED-T-TEST','buyer_name'=>'Test (Buyer)','item_name'=>'Pack \\ test','amount'=>7900,'created_at'=>'2026-11-03 00:00:00','status'=>'PENDING','access_status'=>'PENDING','payment_id'=>null];
$pdf=FinancePdf::render($doc,false);
checkFinance(strpos($pdf,'%PDF-1.4')===0 && strpos($pdf,'TEST MODE')!==false && strpos($pdf,'BIR')!==false,'billing PDF labels');
rejectsFinance(fn()=>FinancePdf::render($doc,true),'confirmation requires verified payment');
$doc['payment_id']='pay_test'; $doc['paid_at']='2026-11-03 00:01:00'; $doc['status']='PAID';
checkFinance(strpos(FinancePdf::render($doc,true),'pay_test')!==false,'confirmation identifies provider payment');
class FinanceNoConnection extends PDO { public function __construct() {} }
$f=new FinanceService(new FinanceNoConnection());
$f->validatePremiumSnapshot(7900,[['content'=>'<p>Text learning pack</p>']],[]); checkFinance(true,'text premium eligible');
rejectsFinance(fn()=>$f->validatePremiumSnapshot(5800,[],[]),'price range enforced');
rejectsFinance(fn()=>$f->validatePremiumSnapshot(7900,[['media_path'=>'uploads/test.pdf']],[]),'public media rejected');
rejectsFinance(fn()=>$f->validatePremiumSnapshot(7900,[['content'=>'<img src="/uploads/test.jpg">']],[]),'embedded public media rejected');
rejectsFinance(fn()=>$f->validatePremiumSnapshot(7900,[],[['activity_data'=>'{"image_url":"https://example.com/pic.jpg"}']]),'activity media rejected');
putenv('SIGNED_FINANCE_ENABLED=false'); checkFinance(!$f->ready(),'disabled finance never touches DB');
rejectsFinance(fn()=>new PayMongoSandbox('sk_live_notallowed'),'live credential rejected');
echo "PASS: $checks isolated finance checks (not provider/database end-to-end acceptance).\n";
