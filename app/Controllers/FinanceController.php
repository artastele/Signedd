<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../Services/FinanceService.php';
require_once __DIR__.'/../Services/FinancePdf.php';

final class FinanceController {
    private function service(): FinanceService { return new FinanceService(Database::getInstance()->getConnection()); }
    private function actor(FinanceService $f): int {
        $id=(int)($_SESSION['user_id']??0);
        if (!$id) { http_response_code(401); throw new DomainException('Please sign in.'); }
        $f->user($id); return $id;
    }
    private function enabled(FinanceService $f): void {
        if (!$f->ready()) throw new RuntimeException('Sandbox finance awaits approved schema installation and configuration.');
    }
    private function csrf(): void {
        // Existing helper deliberately bypasses checks in development; financial actions must fail closed.
        $given=$_POST['_finance_csrf']??''; $saved=$_SESSION['finance_csrf']??'';
        if (!is_string($given) || !$saved || !hash_equals($saved,$given)) throw new DomainException('Financial form expired; reload the page.');
    }
    private function respondError(Throwable $e): void {
        http_response_code($e instanceof DomainException?403:503);
        header('Content-Type: text/plain; charset=utf-8'); header('Cache-Control: no-store');
        echo $e instanceof DomainException?$e->getMessage():'Finance operation unavailable. Check configuration or reconcile uncertain provider state before retrying.';
        // Never output exception traces or provider response bodies containing sensitive data.
    }
    private function customerPage(string $view, array $data): void {
        extract($data, EXTR_SKIP);
        header('Cache-Control: private, no-store');
        require __DIR__.'/../Views/finance/'.$view.'.php';
    }
    public function premiumPlans(): void {
        try {
            $f=$this->service(); $actor=$this->actor($f); $this->enabled($f);
            $user=$f->user($actor);
            if (!in_array($user['role'],['teacher','sped_teacher','master_teacher'],true)) throw new DomainException('Teachers only.');
            $context=$f->customerContext($actor);
            $packCount=(int)$f->query("SELECT COUNT(*) FROM resource_hub_packs p JOIN finance_pack_pricing fp ON fp.pack_id=p.id WHERE p.status='published' AND fp.review_status='APPROVED' AND fp.approved_amount>0")->fetchColumn();
            $this->customerPage('premium_plans',['context'=>$context,'packCount'=>$packCount,'basePath'=>defined('BASE_PATH')?BASE_PATH:'','pageTitle'=>'Premium Resource Hub']);
        } catch(Throwable $e) { $this->respondError($e); }
    }
    public function licenseOverview(): void {
        try {
            $f=$this->service(); $actor=$this->actor($f); $this->enabled($f);
            $user=$f->user($actor);
            if ($user['role']!=='principal' || !$user['school_id']) throw new DomainException('A principal with a registered school is required.');
            $schoolName=$f->query('SELECT school_name FROM schools WHERE id=?',[$user['school_id']])->fetchColumn();
            if (!$schoolName) throw new DomainException('Registered school required.');
            $teacherSeats=(int)$f->query("SELECT COUNT(*) FROM users WHERE school_id=? AND role IN ('sped_teacher','general_teacher','master_teacher','guidance') AND status='active' AND deleted_at IS NULL",[(int)$user['school_id']])->fetchColumn();
            $this->customerPage('license_overview',['context'=>$f->customerContext($actor),'schoolName'=>$schoolName,'teacherSeats'=>$teacherSeats,'basePath'=>defined('BASE_PATH')?BASE_PATH:'','pageTitle'=>'Institutional Licensing']);
        } catch(Throwable $e) { $this->respondError($e); }
    }
    public function packDetail($id): void {
        try {
            $f=$this->service(); $actor=$this->actor($f); $this->enabled($f);
            $user=$f->user($actor);
            if (!in_array($user['role'],['teacher','sped_teacher','master_teacher','admin'],true)) throw new DomainException('Resource Hub access denied.');
            $pack=$f->query("SELECT p.*,fp.review_status,fp.approved_amount,fp.proposed_amount FROM resource_hub_packs p LEFT JOIN finance_pack_pricing fp ON fp.pack_id=p.id WHERE p.id=?",[(int)$id])->fetch();
            if (!$pack || ($pack['status']!=='published' && (int)$pack['created_by']!==$actor && $user['role']!=='admin')) throw new DomainException('Pack unavailable.');
            $access=$f->packAccess((int)$id,$actor);
            if ($pack['review_status'] && $pack['review_status']!=='APPROVED' && (int)$pack['created_by']!==$actor && $user['role']!=='admin') throw new DomainException('Pack awaiting review.');
            // Counts come from actual pack rows. Slide and activity titles are content, so expose them only after entitlement.
            $pack['page_count']=(int)$f->query('SELECT COUNT(*) FROM resource_hub_pages WHERE pack_id=?',[(int)$id])->fetchColumn();
            $pack['activity_count']=(int)$f->query('SELECT COUNT(*) FROM resource_hub_activities WHERE pack_id=?',[(int)$id])->fetchColumn();
            $pageSummaries=$activitySummaries=[];
            if ($access['allowed']) {
                $pageSummaries=$f->query('SELECT title FROM resource_hub_pages WHERE pack_id=? ORDER BY page_number,id',[(int)$id])->fetchAll(PDO::FETCH_COLUMN);
                $activitySummaries=$f->query('SELECT title FROM resource_hub_activities WHERE pack_id=? ORDER BY display_order,id',[(int)$id])->fetchAll(PDO::FETCH_COLUMN);
            }
            $this->customerPage('pack_detail',['pack'=>$pack,'access'=>$access,'pageSummaries'=>$pageSummaries,'activitySummaries'=>$activitySummaries,'basePath'=>defined('BASE_PATH')?BASE_PATH:'','pageTitle'=>$pack['title']]);
        } catch(Throwable $e) { $this->respondError($e); }
    }
    public function checkoutReview(): void {
        try {
            $f=$this->service(); $actor=$this->actor($f); $this->enabled($f); $user=$f->user($actor);
            $kind=(string)($_GET['kind']??''); $packId=null; $item=''; $amount=0; $pending=null;
            if ($kind==='subscription' && in_array($user['role'],['teacher','sped_teacher','master_teacher'],true)) {
                $context=$f->customerContext($actor);
                $item='Premium Resource Hub · one month'; $amount=FinanceRules::SUBSCRIPTION_PRICE;
                $pending=$context['pending_order'];
            } elseif ($kind==='institutional' && $user['role']==='principal' && $user['school_id']) {
                $context=$f->customerContext($actor);
                $school=$f->query('SELECT school_name FROM schools WHERE id=?',[$user['school_id']])->fetchColumn();
                if (!$school) throw new DomainException('Registered school required.');
                $item='Institutional license · '.$school; $amount=FinanceRules::SCHOOL_PRICE;
                $pending=$context['pending_order'];
            } elseif ($kind==='pack' && in_array($user['role'],['teacher','sped_teacher','master_teacher'],true)) {
                $packId=filter_var($_GET['pack_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
                if (!$packId) throw new DomainException('Pack unavailable.');
                $p=$f->query("SELECT p.title,p.status,fp.approved_amount,fp.review_status FROM resource_hub_packs p JOIN finance_pack_pricing fp ON fp.pack_id=p.id WHERE p.id=?",[$packId])->fetch();
                $access=$f->packAccess((int)$packId,$actor);
                if (!$p || $p['status']!=='published' || $p['review_status']!=='APPROVED' || (int)$p['approved_amount']<5900 || $access['allowed']) throw new DomainException('Pack is unavailable for purchase.');
                $item=$p['title']; $amount=(int)$p['approved_amount'];
                $pending=$f->query("SELECT id,status FROM finance_orders WHERE buyer_id=? AND kind='pack' AND pack_id=? AND status='PENDING' ORDER BY id DESC LIMIT 1",[$actor,$packId])->fetch()?:null;
            } else throw new DomainException('Purchase unavailable.');
            $_SESSION['finance_csrf']=$_SESSION['finance_csrf']??bin2hex(random_bytes(32));
            $this->customerPage('checkout_review',compact('kind','packId','item','amount','pending','user')+['basePath'=>defined('BASE_PATH')?BASE_PATH:'','pageTitle'=>'Checkout Review']);
        } catch(Throwable $e) { $this->respondError($e); }
    }
    public function index(): void {
        try {
            $f=$this->service(); $actor=$this->actor($f); $user=$f->user($actor);
            $available=$f->ready(); $admin=$user['role']==='admin';
            if (!in_array($user['role'],['admin','principal','teacher','sped_teacher','master_teacher'],true)) throw new DomainException('Finance access denied.');
            $_SESSION['finance_csrf']=$_SESSION['finance_csrf']??bin2hex(random_bytes(32));
            $basePath=defined('BASE_PATH')?BASE_PATH:''; $pageTitle=$admin?'Finance Management':'Billing history';
            $tab=$_GET['tab']??'transactions';
            if (!in_array($tab,['transactions','revenue','earnings','payouts','refunds'],true)) $tab='transactions';
            $allowedTabs=$admin?['transactions','revenue','earnings','payouts','refunds']:($user['role']==='principal'?['transactions','refunds']:['transactions','earnings','payouts','refunds']);
            if (!in_array($tab,$allowedTabs,true)) throw new DomainException('Finance section access denied.');
            $orders=$earnings=$payouts=$refunds=$pricing=$entitlements=$audits=$outstandingEarnings=$refundableOrders=[]; $totals=[]; $detail=null; $schoolStatus=null; $feeEvidence=[];
            if ($available) {
                $params=[]; $where='1=1';
                if (!$admin) {
                    $where='(o.buyer_id=?'.($user['role']==='principal'?' OR (o.kind=\'institutional\' AND o.school_id=?)':'').')';
                    $params[]=$actor; if($user['role']==='principal') $params[]=$user['school_id'];
                }
                $search=trim((string)($_GET['q']??''));
                if($search!=='') { $where.=' AND (o.reference LIKE ? OR o.buyer_name LIKE ? OR o.payment_id=?)'; array_push($params,'%'.$search.'%','%'.$search.'%',$search); }
                $kind=$_GET['kind']??'';
                if(in_array($kind,['institutional','subscription','pack'],true)) { $where.=' AND o.kind=?'; $params[]=$kind; }
                $status=$_GET['status']??'';
                if(in_array($status,['PENDING','PAID','FAILED','CANCELLED','REFUNDED'],true)) { $where.=' AND o.status=?'; $params[]=$status; }
                $orders=$f->query('SELECT o.*,a.fee,a.net,a.status AS fee_status FROM finance_orders o LEFT JOIN finance_accounting a ON a.order_id=o.id WHERE '.$where.' ORDER BY o.id DESC LIMIT 100',$params)->fetchAll();
                if($admin || in_array($tab,['earnings','payouts'],true)) {
                    $earnings=$f->query('SELECT e.*,u.name AS creator_name,i.payout_id FROM finance_earnings e JOIN users u ON u.id=e.creator_id LEFT JOIN finance_payout_items i ON i.earning_id=e.id'.($admin?'':' WHERE e.creator_id=?').' ORDER BY e.id DESC LIMIT 200',$admin?[]:[$actor])->fetchAll();
                    $payouts=$f->query('SELECT * FROM finance_payouts'.($admin?'':' WHERE creator_id=?').' ORDER BY id DESC LIMIT 100',$admin?[]:[$actor])->fetchAll();
                }
                $refunds=$f->query('SELECT r.* FROM finance_refunds r JOIN finance_orders o ON o.id=r.order_id WHERE '.($admin?'1=1':($user['role']==='principal'?"(o.buyer_id=? OR (o.kind='institutional' AND o.school_id=?))":'o.buyer_id=?')).' ORDER BY r.id DESC LIMIT 100',$admin?[]:($user['role']==='principal'?[$actor,$user['school_id']]:[$actor]))->fetchAll();
                $pricing=$f->query('SELECT f.*,p.title FROM finance_pack_pricing f JOIN resource_hub_packs p ON p.id=f.pack_id'.($admin?'':' WHERE f.creator_id=?').' ORDER BY f.updated_at DESC LIMIT 100',$admin?[]:[$actor])->fetchAll();
                $entitlements=$f->query('SELECT e.* FROM finance_entitlements e WHERE '.($user['role']==='principal'?'e.school_id=?':'e.buyer_id=?').' ORDER BY e.id DESC LIMIT 100',[$user['role']==='principal'?$user['school_id']:$actor])->fetchAll();
                if($user['school_id']) $schoolStatus=$f->schoolStatus((int)$user['school_id']);
                if($tab==='refunds') {
                    $refundableOrders=$f->query("SELECT o.id,o.reference,o.item_name,o.amount FROM finance_orders o WHERE o.status='PAID' AND ".($admin?'1=1':($user['role']==='principal'?"(o.buyer_id=? OR (o.kind='institutional' AND o.school_id=?))":'o.buyer_id=?')).' ORDER BY o.id DESC LIMIT 100',$admin?[]:($user['role']==='principal'?[$actor,$user['school_id']]:[$actor]))->fetchAll();
                }
                if($admin) {
                    $totals=$f->query("SELECT SUM(status='VERIFIED') AS verified_payments,COALESCE(SUM(IF(status='VERIFIED',gross,0)),0) AS gross,COALESCE(SUM(IF(status='VERIFIED',fee,0)),0) AS known_fees,COALESCE(SUM(IF(status='VERIFIED',net,0)),0) AS verified_net,COALESCE(SUM(IF(status='VERIFIED',signed_share,0)),0) AS signed_share,COALESCE(SUM(IF(status='VERIFIED',creator_pool,0)),0) AS creator_liability,COALESCE(SUM(IF(status='VERIFIED',undistributed,0)),0) AS undistributed,COALESCE(SUM(status='PENDING_FEES'),0) AS pending_fee_orders,COALESCE(SUM(status IN ('FEE_REVIEW','REFUND_FEE_REVIEW')),0) AS fee_review_orders FROM finance_accounting")->fetch();
                    $totals=array_merge($totals,$f->query("SELECT COALESCE(SUM(amount),0) AS refunds,COALESCE(SUM(signed_adjustment),0) AS signed_adjustments,COALESCE(SUM(creator_adjustment),0) AS creator_adjustments,COALESCE(SUM(undistributed_adjustment),0) AS undistributed_adjustments,SUM(accounting_status<>'RECONCILED') AS refund_reviews FROM finance_refunds WHERE status='CONFIRMED'")->fetch());
                    $totals['payouts']=$f->query('SELECT COALESCE(SUM(amount),0) FROM finance_payouts')->fetchColumn();
                    $totals['outstanding_earnings']=$f->query('SELECT COALESCE(SUM(e.amount),0) FROM finance_earnings e LEFT JOIN finance_payout_items i ON i.earning_id=e.id WHERE i.earning_id IS NULL')->fetchColumn();
                    if($tab==='payouts') $outstandingEarnings=$f->query('SELECT e.id,e.creator_id,e.amount,e.basis,u.name AS creator_name FROM finance_earnings e JOIN users u ON u.id=e.creator_id LEFT JOIN finance_payout_items i ON i.earning_id=e.id WHERE i.earning_id IS NULL ORDER BY u.name,e.id LIMIT 200')->fetchAll();
                }
                if (!empty($_GET['order'])) {
                    $detail=$f->order((int)$_GET['order'],$actor);
                    if ($admin) $feeEvidence=$f->query('SELECT source,status,fee,net,payload_hash,balance_transaction_id,reason,retrieved_at FROM finance_fee_evidence WHERE order_id=? ORDER BY id DESC',[$detail['id']])->fetchAll();
                    $audits=$f->query('SELECT action,detail,created_at FROM finance_audit WHERE order_id=? ORDER BY id',[$detail['id']])->fetchAll();
                }
            }
            header('Cache-Control: no-store');
            require __DIR__.'/../Views/finance/index.php';
        } catch(Throwable $e) { $this->respondError($e); }
    }
    public function action(): void {
        try {
            $f=$this->service(); $actor=$this->actor($f); $this->enabled($f); $this->csrf();
            $action=$_POST['action']??''; $id=(int)($_POST['order_id']??0);
            if ($action==='create') $id=$f->createOrder($actor,(string)($_POST['kind']??''),isset($_POST['pack_id'])?(int)$_POST['pack_id']:null,($_POST['school_license_consent']??'')==='yes');
            elseif($action==='checkout') { $url=$f->checkout($id,$actor,new PayMongoSandbox()); header('Location: '.$url, true,303); return; }
            elseif($action==='cancel') $f->cancel($id,$actor,new PayMongoSandbox());
            elseif($action==='reconcile') $f->reconcile($id,$actor,new PayMongoSandbox());
            elseif($action==='check-payment') $f->reconcileOwnOrder($id,$actor,new PayMongoSandbox());
            elseif($action==='attach-checkout') $f->attachCheckout($id,trim((string)($_POST['provider_id']??'')),$actor,new PayMongoSandbox());
            elseif($action==='close-pool') $f->closePool($id,$actor);
            elseif($action==='propose') $f->proposePrice((int)($_POST['pack_id']??0),$this->amount(),$actor);
            elseif($action==='price-review') $f->reviewPrice((int)($_POST['pack_id']??0),($_POST['decision']??'')==='approve',$actor);
            elseif($action==='refund-request') $f->requestRefund($id,$this->amount(),trim((string)($_POST['reason']??'')),$actor);
            elseif($action==='refund-review') $f->reviewRefund((int)($_POST['refund_id']??0),($_POST['decision']??'')==='approve',trim((string)($_POST['note']??'')),$actor);
            elseif($action==='refund-submit') $f->submitRefund((int)($_POST['refund_id']??0),$actor,new PayMongoSandbox());
            elseif($action==='refund-reconcile') { $f->admin($actor); $f->reconcileRefund((int)($_POST['refund_id']??0),new PayMongoSandbox()); }
            elseif($action==='attach-refund') $f->attachRefund((int)($_POST['refund_id']??0),trim((string)($_POST['provider_id']??'')),$actor,new PayMongoSandbox());
            elseif($action==='payout') {
                if (($_POST['transfer_confirmed']??'')!=='yes') throw new DomainException('Confirm that the transfer actually completed.');
                $selected=$_POST['earning_ids']??[];
                if (is_string($selected)) $selected=explode(',',$selected); // Existing form compatibility.
                if (!is_array($selected) || !$selected || count($selected)>200 || array_filter($selected,static fn($v)=>!is_scalar($v) || !ctype_digit((string)$v) || (int)$v<1)) throw new DomainException('Select valid outstanding creator earnings.');
                $f->payout((int)($_POST['creator_id']??0),$selected,trim((string)($_POST['method']??'')),trim((string)($_POST['reference']??'')),(string)($_POST['transferred_at']??''),$actor,$this->amount());
            } else throw new DomainException('Unknown action.');
            $after=($action==='create' || $action==='check-payment') && $id
                ? '/finance/payment/'.$id
                : '/finance'.($id?'?order='.$id:'');
            header('Location: '.(defined('BASE_PATH')?BASE_PATH:'').$after,true,303);
        } catch(Throwable $e) { $this->respondError($e); }
    }
    public function paymentRequest($id): void {
        try {
            $f=$this->service(); $actor=$this->actor($f); $this->enabled($f);
            $order=$f->order((int)$id,$actor); // Never change payment/access state from GET or query parameters.
            $basePath=defined('BASE_PATH')?BASE_PATH:'';
            $pageTitle=$order['payment_id']?'Payment confirmation':'Payment request';
            $_SESSION['finance_csrf']=$_SESSION['finance_csrf']??bin2hex(random_bytes(32));
            header('Cache-Control: private, no-store');
            require __DIR__.'/../Views/finance/payment_request.php';
        } catch(Throwable $e) { $this->respondError($e); }
    }
    private function amount(): int {
        $v=(string)($_POST['amount']??'');
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/',$v)) throw new DomainException('Enter a valid PHP amount.');
        $parts=explode('.',$v); return (int)$parts[0]*100+(int)str_pad($parts[1]??'',2,'0');
    }
    public function document($id,$type): void {
        try {
            $f=$this->service(); $this->enabled($f); $o=$f->order((int)$id,$this->actor($f));
            if(!in_array($type,['billing','confirmation'],true)) throw new DomainException('Unknown document.');
            $pdf=FinancePdf::render($o,$type==='confirmation');
            header('Content-Type: application/pdf'); header('Cache-Control: private, no-store');
            header('X-Content-Type-Options: nosniff'); header('Content-Disposition: attachment; filename="'.$o['reference'].'-'.$type.'.pdf"'); echo $pdf;
        } catch(Throwable $e) { $this->respondError($e); }
    }
    public function webhook(): void {
        try {
            $f=$this->service(); $this->enabled($f);
            if((int)($_SERVER['CONTENT_LENGTH']??0)>1048576) throw new DomainException('Payload too large.');
            $raw=file_get_contents('php://input',false,null,0,1048577);
            if($raw===false || strlen($raw)>1048576) throw new DomainException('Payload too large.');
            $f->webhook($raw,$_SERVER['HTTP_PAYMONGO_SIGNATURE']??'',new PayMongoSandbox());
            http_response_code(200); echo 'OK';
        } catch(Throwable $e) { $this->respondError($e); }
    }
}
