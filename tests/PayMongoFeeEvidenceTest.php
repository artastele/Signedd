<?php
declare(strict_types=1);
require_once __DIR__.'/../app/Services/PayMongoSandbox.php';
require_once __DIR__.'/../app/Services/PayMongoFeeEvidence.php';
require_once __DIR__.'/../app/Services/FinanceRules.php';
$checks=0;
function feeCheck(bool $ok,string $message): void { global $checks; ++$checks; if(!$ok) throw new RuntimeException($message); }
function feeReject(callable $fn,string $message): void { try {$fn();} catch(DomainException $e) { feeCheck(true,$message); return; } feeCheck(false,$message); }
class FeeProviderFixture extends PayMongoSandbox {
    public array $calls=[]; public array $payment=[]; public bool $unavailable=false; public bool $live=false; public bool $malformed=false;
    public function __construct() {}
    public function request(string $method,string $path,?array $attributes=null): array {
        $this->calls[]=[$method,$path];
        if($this->malformed) throw new JsonException('Malformed response');
        if($this->live) throw new DomainException('Provider response is not test mode.');
        if($this->unavailable) throw new RuntimeException('Unavailable');
        return $this->payment;
    }
}
$o=['amount'=>7900]; $p=['id'=>'pay_fixture','type'=>'payment','attributes'=>['livemode'=>false,'status'=>'paid','currency'=>'PHP','amount'=>7900,'fee'=>305,'net_amount'=>7595,'updated_at'=>1700000000,'balance_transaction_id'=>'bal_txn_test']];
$provider=new FeeProviderFixture(); $provider->payment=$p;
$e=PayMongoFeeEvidence::retrieve($o,'pay_fixture',$provider);
feeCheck($provider->calls===[['GET','/v1/payments/pay_fixture']],'Retrieves individual payment, never browser fees');
feeCheck($e['fee']===305 && $e['net']===7595 && $e['status']==='VERIFIED','Actual integer fee/net confirmed');
feeCheck($e['payload_hash']===hash('sha256',json_encode($p,JSON_THROW_ON_ERROR)),'Provider evidence fingerprint exact');
feeCheck($e['balance_transaction_id']==='bal_txn_test','Ledger correlation retained');
$split=FinanceRules::split(7900,$e['fee'],'pack'); feeCheck($split['creator_pool']===5316 && $split['signed_share']===2279,'Split only confirmed net with centavo rounding');
foreach(['fee','net_amount'] as $field) { $v=$p; unset($v['attributes'][$field]); $e=PayMongoFeeEvidence::verify($o,'pay_fixture',$v); feeCheck($e['fee']===null && $e['net']===null && $e['status']==='PENDING_FEES','Missing fee data pending'); }
foreach([['fee',-1],['fee','305'],['net_amount',7594],['fee',8000],['foreign_fee',10],['tax_amount',1],['digital_withholding_vat_amount',1]] as [$key,$value]) { $v=$p; $v['attributes'][$key]=$value; feeCheck(PayMongoFeeEvidence::verify($o,'pay_fixture',$v)['status']==='PENDING_FEES','Unconfirmed deduction remains pending'); }
foreach([['livemode',true],['amount',7999],['amount','7900'],['currency','USD'],['status','failed']] as [$key,$value]) { $v=$p; $v['attributes'][$key]=$value; feeReject(fn()=>PayMongoFeeEvidence::verify($o,'pay_fixture',$v),'Identity/money mismatch rejected'); }
$v=$p; $v['id']='pay_other'; feeReject(fn()=>PayMongoFeeEvidence::verify($o,'pay_fixture',$v),'Wrong payment rejected');
$v=$p; $v['type']='refund'; feeReject(fn()=>PayMongoFeeEvidence::verify($o,'pay_fixture',$v),'Wrong resource type rejected');
$provider->unavailable=true; $e=PayMongoFeeEvidence::retrieve($o,'pay_fixture',$provider); feeCheck($e['status']==='PENDING_FEES' && $e['fee']===null && $e['payload_hash']===null,'Provider outage cannot fabricate fee or evidence');
$provider->unavailable=false; $provider->live=true; feeReject(fn()=>PayMongoFeeEvidence::retrieve($o,'pay_fixture',$provider),'Live response cannot be swallowed as unavailable fee');
$provider->live=false; $e=PayMongoFeeEvidence::retrieve($o,'pay_fixture',$provider); feeCheck($e['status']==='VERIFIED','Later safe GET reconciliation resolves pending fees');
feeReject(fn()=>PayMongoFeeEvidence::retrieve($o,'../bad',$provider),'Provider path injection rejected');
$provider->malformed=true;
feeCheck(PayMongoFeeEvidence::retrieve($o,'pay_fixture',$provider)['status']==='PENDING_FEES','Unreadable individual payment response remains pending');
$v=$p; $v['attributes']['fee']=0; $v['attributes']['net_amount']=7900;
feeCheck(PayMongoFeeEvidence::verify($o,'pay_fixture',$v)['fee']===0,'Zero fee is accepted only when explicitly confirmed by provider data');
echo "PASS: $checks PayMongo fee checks; no provider/network/database calls.\n";
