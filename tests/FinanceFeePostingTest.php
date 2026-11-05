<?php
declare(strict_types=1);
require_once __DIR__.'/../app/Services/FinanceService.php';
class PostingStatement extends PDOStatement {
    private $handler; private array $rows=[];
    public function __construct(callable $handler) {$this->handler=$handler;}
    public function execute(?array $params=null): bool {$this->rows=($this->handler)($params??[]);return true;}
    public function fetch(int $mode=PDO::FETCH_DEFAULT,int $orientation=PDO::FETCH_ORI_NEXT,int $offset=0):mixed{return $this->rows[0]??false;}
    public function fetchColumn(int $column=0):mixed{return $this->rows?array_values($this->rows[0])[$column]??false:false;}
}
class PostingDatabase extends PDO {
    public array $order=['id'=>1,'buyer_id'=>22,'creator_id'=>11,'school_id'=>null,'pack_id'=>7,'kind'=>'pack','checkout_id'=>'cs_fixture','reference'=>'SIGNED-T-FIXTURE','amount'=>7900,'status'=>'PENDING','payment_id'=>null];
    public array $account=[],$earnings=[],$entitlements=[],$evidence=[],$audits=[]; private array $snapshot=[]; private bool $transaction=false; public bool $confirmedRefund=false;
    public function __construct(){}
    public function inTransaction():bool{return $this->transaction;}
    public function beginTransaction():bool{$this->snapshot=[$this->order,$this->account,$this->earnings,$this->entitlements,$this->evidence,$this->audits];$this->transaction=true;return true;}
    public function commit():bool{$this->transaction=false;return true;}
    public function rollBack():bool{[$this->order,$this->account,$this->earnings,$this->entitlements,$this->evidence,$this->audits]=$this->snapshot;$this->transaction=false;return true;}
    public function prepare(string $sql,array $options=[]):PDOStatement|false{
        return new PostingStatement(function(array $p)use($sql):array{
            if(strpos($sql,'FROM users')!==false)return [['id'=>22,'name'=>'Buyer','role'=>'sped_teacher','school_id'=>null,'status'=>'active']];
            if(strpos($sql,'SELECT * FROM finance_orders')!==false)return [$this->order];
            if(strpos($sql,"UPDATE finance_orders SET status='PAID'")!==false){$this->order['status']='PAID';$this->order['payment_id']=$p[0];return [];}
            if(strpos($sql,'INSERT INTO finance_accounting')!==false){$this->account=['order_id'=>1,'gross'=>7900,'fee'=>null,'status'=>'PENDING_FEES'];return [];}
            if(strpos($sql,'INSERT INTO finance_entitlements')!==false){$this->entitlements[]=$p;return [];}
            if(strpos($sql,'INSERT INTO finance_audit')!==false){$this->audits[]=$p;return [];}
            if(strpos($sql,'INSERT IGNORE INTO finance_fee_evidence')!==false){$this->evidence[$p[2]]=$p;return [];}
            if(strpos($sql,'SELECT * FROM finance_accounting')!==false)return [$this->account];
            if(strpos($sql,'FROM finance_refunds')!==false)return $this->confirmedRefund?[['id'=>1]]:[];
            if(strpos($sql,'UPDATE finance_accounting SET fee=')!==false){$this->account=['order_id'=>1,'gross'=>7900,'fee'=>$p[0],'net'=>$p[1],'signed_share'=>$p[2],'creator_pool'=>$p[3],'status'=>'VERIFIED'];return [];}
            if(strpos($sql,"SET status='REFUND_FEE_REVIEW'")!==false){$this->account['status']='REFUND_FEE_REVIEW';return [];}
            if(strpos($sql,"SET status='FEE_REVIEW'")!==false){$this->account['status']='FEE_REVIEW';return [];}
            if(strpos($sql,'INSERT INTO finance_earnings')!==false){$this->earnings[]=$p;return [];}
            throw new RuntimeException('Unexpected posting fixture query: '.$sql);
        });
    }
}
class PostingProvider extends PayMongoSandbox {
    public bool $unavailable=true; public array $payment;
    public function __construct(){$this->payment=['id'=>'pay_fixture','type'=>'payment','attributes'=>['livemode'=>false,'status'=>'paid','currency'=>'PHP','amount'=>7900,'fee'=>305,'net_amount'=>7595]];}
    public function request(string $method,string $path,?array $attributes=null):array{if($method!=='GET'||$path!=='/v1/payments/pay_fixture')throw new LogicException('Unexpected provider call.');if($this->unavailable)throw new RuntimeException('No fee data yet');return $this->payment;}
}
$db=new PostingDatabase();$service=new FinanceService($db);$provider=new PostingProvider();
$session=['id'=>'cs_fixture','attributes'=>['livemode'=>false,'reference_number'=>'SIGNED-T-FIXTURE','metadata'=>['order_reference'=>'SIGNED-T-FIXTURE','buyer_id'=>'22'],'payments'=>[['id'=>'pay_fixture','attributes'=>['livemode'=>false,'status'=>'paid','currency'=>'PHP','amount'=>7900,'fee'=>1,'net_amount'=>7899]]]]];
$settle=new ReflectionMethod(FinanceService::class,'settle');$settle->setAccessible(true);$checks=0;
$check=function(bool $ok,string $message)use(&$checks){++$checks;if(!$ok)throw new RuntimeException($message);};
$settle->invoke($service,$db->order,$session,$provider);
$check($db->order['status']==='PAID' && count($db->entitlements)===1,'Verified checkout grants access while fees unavailable');
$check($db->account['status']==='PENDING_FEES' && $db->account['fee']===null && !$db->earnings,'Checkout-embedded fee cannot allocate earnings');
$check(count($db->evidence)===1,'Pending retrieval audited');
$provider->unavailable=false;$settle->invoke($service,$db->order,$session,$provider);
$check($db->account['fee']===305 && $db->account['status']==='VERIFIED','Reconciliation uses individual payment fee');
$check($db->account['creator_pool']===5316 && $db->account['signed_share']===2279,'Confirmed distributable split posted');
$check(count($db->earnings)===1 && count($db->entitlements)===1,'Reconciliation has exactly one earning/access effect');
$settle->invoke($service,$db->order,$session,$provider);
$check(count($db->earnings)===1 && count($db->evidence)===2,'Repeated reconciliation deduplicates evidence and allocation');
$provider->payment['attributes']['fee']=400;$provider->payment['attributes']['net_amount']=7500;
$settle->invoke($service,$db->order,$session,$provider);
$check($db->account['status']==='FEE_REVIEW' && $db->account['fee']===305,'Changed fee holds payouts without rewriting history');
$check(count($db->earnings)===1,'Changed fee creates no duplicate creator earning');
$provider->payment['attributes']['amount']=9999;$before=[$db->order,$db->account,$db->earnings];
try{$settle->invoke($service,$db->order,$session,$provider);throw new RuntimeException('Wrong gross accepted');}catch(DomainException $e){$check([$db->order,$db->account,$db->earnings]===$before,'Mismatched retrieved payment has no effects');}
$refundedDb=new PostingDatabase(); $refundedService=new FinanceService($refundedDb); $refundedProvider=new PostingProvider();
$settle->invoke($refundedService,$refundedDb->order,$session,$refundedProvider);
$refundedDb->confirmedRefund=true; $refundedDb->order['status']='REFUNDED'; $refundedProvider->unavailable=false;
$settle->invoke($refundedService,$refundedDb->order,$session,$refundedProvider);
$check($refundedDb->account['status']==='REFUND_FEE_REVIEW' && !$refundedDb->earnings && $refundedDb->account['fee']===null,'Late fees across a confirmed refund cannot allocate proceeds');
$check(count($refundedDb->evidence)===2 && count($refundedDb->entitlements)===1,'Late refund fee evidence persists without duplicate access');
echo "PASS: $checks fee posting/reconciliation checks; in-memory fixtures only.\n";
