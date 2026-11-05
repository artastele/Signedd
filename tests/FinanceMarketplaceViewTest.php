<?php
declare(strict_types=1);
// Synthetic view fixtures only. No app bootstrap, database, provider, or record writes.
$views=['premium_plans','license_overview','pack_detail','checkout_review','payment_request'];
if (($argv[1]??'')==='--render') {
    $view=$argv[2]??''; $scenario=$argv[3]??''; if (!in_array($view,$views,true)) exit(2);
    define('BASE_PATH','/Signedd/public'); $_SERVER['REQUEST_URI']='/Signedd/public/finance/'.$view;
    $_SESSION=['user_id'=>27,'role'=>'sped_teacher','user_name'=>'SANDBOX TEST DATA','finance_csrf'=>str_repeat('a',64)];
    $basePath=BASE_PATH;$pageTitle=$view==='pack_detail'?'Pack <script>alert(1)</script>':'Finance test';
    $context=['enabled'=>true,'subscription'=>['active'=>false,'state'=>'NONE','expires_at'=>null],'pending_order'=>null,'school'=>['state'=>'LEGACY','enrolled'=>false,'expires_at'=>null]];
    $packCount=0;$schoolName='SANDBOX TEST DATA - School';
    $pack=['id'=>77,'title'=>'Pack <script>alert(1)</script>','author_name'=>'SANDBOX TEST DATA - Creator','description'=>'Synthetic pack','pdsp_domain'=>'communication_language','learning_level'=>'Primary SPED','language'=>'FSL','learning_competency'=>'Recognize signs','page_count'=>2,'activity_count'=>1,'fsl_sign_count'=>0,'approved_amount'=>7900,'review_status'=>'APPROVED'];
    $access=['allowed'=>false,'label'=>'Requires Purchase'];
    if ($view==='pack_detail' && $scenario==='pending') {
        $pack['proposed_amount']=5900; $pack['approved_amount']=null; $pack['review_status']='PENDING';
        $access=['allowed'=>true,'label'=>'Owned'];
    } elseif ($view==='pack_detail' && $scenario==='rejected') {
        $pack['proposed_amount']=5900; $pack['approved_amount']=null; $pack['review_status']='REJECTED';
        $access=['allowed'=>true,'label'=>'Owned'];
    } elseif ($view==='pack_detail' && $scenario==='approved_free') {
        $pack['proposed_amount']=0; $pack['approved_amount']=0; $pack['review_status']='APPROVED';
        $access=['allowed'=>true,'label'=>'Free'];
    }
    $kind='pack';$packId=77;$item=$pack['title'];$amount=7900;$pending=null;$user=['name'=>'SANDBOX TEST DATA - Buyer'];
    $order=['id'=>9,'reference'=>'SIGNED-T-SYNTHETIC','item_name'=>'SANDBOX TEST DATA - Pack','amount'=>7900,'buyer_name'=>'SANDBOX TEST DATA - Buyer','status'=>'PENDING','access_status'=>'PENDING','payment_id'=>null,'kind'=>'pack','pack_id'=>77];
    require __DIR__.'/../app/Views/finance/'.$view.'.php';exit;
}
function renderMarketplace(string $view, string $scenario=''): string {
    $pipes=[];$process=proc_open([PHP_BINARY,__FILE__,'--render',$view,$scenario],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    if (!is_resource($process)) throw new RuntimeException('Could not start fixture renderer.');
    fclose($pipes[0]);$html=stream_get_contents($pipes[1]);fclose($pipes[1]);$errors=stream_get_contents($pipes[2]);fclose($pipes[2]);
    if (proc_close($process)!==0 || $errors!=='') throw new RuntimeException('Fixture rendering failed: '.$view.' '.$errors);
    return $html;
}
$checks=0;
function marketCheck(bool $ok,string $message): void {global $checks;++$checks;if(!$ok)throw new RuntimeException($message);}
foreach($views as $view){$html=renderMarketplace($view);marketCheck(strpos($html,'finance-marketplace.css')!==false,$view.' uses existing SignED shell styles');marketCheck(strpos($html,'Fatal error')===false,$view.' renders cleanly');$out[$view]=$html;}
marketCheck(strpos($out['premium_plans'],'₱199')!==false,'Premium amount');
marketCheck(strpos($out['premium_plans'],'/finance/review?kind=subscription')!==false,'Plans link to read-only review');
marketCheck(strpos($out['premium_plans'],'name="action" value="create"')===false,'Plans create no order');
marketCheck(strpos($out['license_overview'],'₱15,000')!==false,'School license price');
marketCheck(strpos($out['license_overview'],'/finance/review?kind=institutional')!==false,'License links to review');
marketCheck(strpos($out['license_overview'],'name="action" value="create"')===false,'License overview creates no order');
marketCheck(strpos($out['pack_detail'],'Pack &lt;script&gt;alert(1)&lt;/script&gt;')!==false,'Pack title escaped');
marketCheck(strpos($out['pack_detail'],'<script>alert(1)</script>')===false,'Pack script cannot render');
marketCheck(strpos($out['pack_detail'],'/finance/review?kind=pack&amp;pack_id=77')!==false,'Pack detail links to review');
marketCheck(strpos($out['pack_detail'],'name="action" value="create"')===false,'Pack detail creates no order');
$pendingPack=renderMarketplace('pack_detail','pending');
marketCheck(strpos($pendingPack,'Proposed ₱59.00')!==false && strpos($pendingPack,'Awaiting System Admin price and publication approval')!==false,'Pending ₱59 proposal is labeled clearly');
marketCheck(strpos($pendingPack,'Free pack')===false && strpos($pendingPack,'>Free</p>')===false && strpos($pendingPack,'>Buy Pack</a>')===false,'Pending proposal is neither free nor purchasable');
$rejectedPack=renderMarketplace('pack_detail','rejected');
marketCheck(strpos($rejectedPack,'Pricing needs revision')!==false && strpos($rejectedPack,'Proposed ₱59.00')!==false && strpos($rejectedPack,'>Buy Pack</a>')===false,'Rejected proposal stays unpurchasable');
$freePack=renderMarketplace('pack_detail','approved_free');
marketCheck(strpos($freePack,'Free pack')!==false && strpos($freePack,'>Free</p>')!==false,'Approved zero-price pack remains free');
marketCheck(strpos($out['checkout_review'],'name="action" value="create"')!==false,'Explicit confirmation action');
marketCheck(strpos($out['checkout_review'],'name="_finance_csrf"')!==false,'Review has CSRF');
marketCheck(strpos($out['checkout_review'],'name="amount"')===false,'Review does not submit price');
marketCheck(strpos($out['checkout_review'],'₱79.00')!==false,'Review formats PHP');
marketCheck(strpos($out['payment_request'],'name="action" value="checkout"')!==false,'Existing order proceeds to PayMongo');
marketCheck(strpos($out['payment_request'],'₱79.00')!==false,'Payment formats PHP');
echo "PASS: $checks isolated marketplace view checks; synthetic data only.\n";
