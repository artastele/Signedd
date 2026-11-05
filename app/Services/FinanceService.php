<?php
declare(strict_types=1);
require_once __DIR__.'/FinanceRules.php';
require_once __DIR__.'/PayMongoSandbox.php';
require_once __DIR__.'/PayMongoFeeEvidence.php';

final class FinanceService {
    private PDO $db;
    public function __construct(PDO $db) { $this->db=$db; }
    public static function enabled(): bool { return getenv('SIGNED_FINANCE_ENABLED') === 'true'; }
    public static function accessEnabled(PDO $db): bool {
        if (self::enabled()) return true;
        // Turning off checkout must never make previously sold content free.
        static $installed = [];
        $key = spl_object_hash($db);
        if (!array_key_exists($key,$installed)) {
            $q=$db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='finance_pack_pricing'");
            $installed[$key]=(int)$q->fetchColumn()>0;
        }
        return $installed[$key];
    }
    public function ready(): bool {
        if (!self::enabled()) return false;
        $this->db->query('SELECT id FROM finance_orders LIMIT 0');
        return true;
    }
    public function query(string $sql, array $params=[]): PDOStatement {
        $s=$this->db->prepare($sql); $s->execute($params); return $s;
    }
    public function atomic(callable $fn) {
        if ($this->db->inTransaction()) throw new LogicException('Unexpected nested finance transaction.');
        $this->db->beginTransaction();
        try { $r=$fn(); $this->db->commit(); return $r; }
        catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function user(int $id, bool $lock=false): array {
        $u=$this->query("SELECT id,name,role,school_id,status FROM users WHERE id=? AND deleted_at IS NULL".($lock?' FOR UPDATE':''),[$id])->fetch();
        if (!$u || $u['status'] !== 'active') throw new DomainException('Active account required.');
        return $u;
    }
    public function admin(int $id): void {
        if ($this->user($id)['role'] !== 'admin') throw new DomainException('System Admin only.');
    }
    public function audit(?int $order, ?int $actor, string $action, string $detail): void {
        $this->query('INSERT INTO finance_audit(order_id,actor_id,action,detail,created_at) VALUES(?,?,?,?,UTC_TIMESTAMP())',[$order,$actor,$action,substr($detail,0,500)]);
    }
    public function order(int $id, int $actor): array {
        $u=$this->user($actor);
        $o=$this->query('SELECT * FROM finance_orders WHERE id=?',[$id])->fetch();
        if (!$o || ($u['role'] !== 'admin' && (int)$o['buyer_id'] !== $actor && !($u['role']==='principal' && $o['kind']==='institutional' && (int)$u['school_id']===(int)$o['school_id']))) throw new DomainException('Order access denied.');
        return $o;
    }
    public function packAccess(int $packId, int $userId): array {
        if (!self::accessEnabled($this->db)) return ['allowed'=>true,'label'=>'Free','period'=>null];
        $u=$this->user($userId);
        if (!in_array($u['role'],['admin','sped_teacher','general_teacher','teacher','master_teacher'],true)) return ['allowed'=>false,'label'=>'Teachers only','period'=>null];
        $p=$this->query('SELECT p.*,f.review_status,f.approved_amount FROM resource_hub_packs p LEFT JOIN finance_pack_pricing f ON f.pack_id=p.id WHERE p.id=?',[$packId])->fetch();
        if (!$p) throw new DomainException('Pack not found.');
        if ((int)$p['created_by']===$userId || $u['role']==='admin') return ['allowed'=>true,'label'=>'Owned','period'=>null];
        if ($this->query("SELECT id FROM finance_entitlements WHERE buyer_id=? AND pack_id=? AND kind='pack' AND state='ACTIVE' LIMIT 1",[$userId,$packId])->fetchColumn()) return ['allowed'=>true,'label'=>'Owned','period'=>null];
        if ($p['status']!=='published' || ($p['review_status']!==null && $p['review_status']!=='APPROVED')) return ['allowed'=>false,'label'=>'Awaiting review','period'=>null];
        if ($p['review_status']==='APPROVED' && $p['approved_amount']===null) return ['allowed'=>false,'label'=>'Price unavailable','period'=>null];
        if ((int)$p['approved_amount']===0) return ['allowed'=>true,'label'=>'Free','period'=>null];
        $period=$this->query("SELECT * FROM finance_entitlements WHERE buyer_id=? AND kind='subscription' AND state='ACTIVE' AND starts_at<=UTC_TIMESTAMP() AND ends_at>UTC_TIMESTAMP() ORDER BY starts_at LIMIT 1",[$userId])->fetch();
        return ['allowed'=>(bool)$period,'label'=>$period?'Included in Subscription':'Requires Purchase','period'=>$period?:null];
    }
    public function requirePack(int $pack, int $user): array {
        $access=$this->packAccess($pack,$user);
        if (!$access['allowed']) throw new DomainException('Premium pack requires a verified purchase or active subscription.');
        return $access;
    }
    /** Read-only customer-page state. All purchase amounts/ownership are still checked by createOrder(). */
    public function customerContext(int $actor, ?string $now=null): array {
        if (!self::enabled()) return ['enabled'=>false,'school'=>null,'subscription'=>null,'pending_order'=>null];
        if (!$this->ready()) throw new RuntimeException('Finance is unavailable.');
        $user=$this->user($actor); $now=$now??gmdate('Y-m-d H:i:s');
        $result=['enabled'=>true,'school'=>null,'subscription'=>null,'pending_order'=>null,'role'=>$user['role']];
        if ($user['role']==='principal') {
            if (!$user['school_id']) return $result;
            $school=(int)$user['school_id'];
            $result['school']=$this->schoolStatus($school,$now);
            $result['school']['activation']=$this->query("SELECT activated_at,starts_at,ends_at FROM finance_entitlements WHERE school_id=? AND kind='institutional' AND state='ACTIVE' ORDER BY ends_at DESC LIMIT 1",[$school])->fetch()?:null;
            $result['pending_order']=$this->query("SELECT id,status,access_status FROM finance_orders WHERE school_id=? AND kind='institutional' AND status='PENDING' ORDER BY id DESC LIMIT 1",[$school])->fetch()?:null;
        } elseif (in_array($user['role'],['teacher','sped_teacher','master_teacher'],true)) {
            $periods=$this->query("SELECT starts_at,ends_at FROM finance_entitlements WHERE buyer_id=? AND kind='subscription' AND state='ACTIVE' ORDER BY starts_at,ends_at",[$actor])->fetchAll();
            $result['subscription']=FinanceRules::subscriptionSummary($periods,$now);
            $result['pending_order']=$this->query("SELECT id,status,access_status FROM finance_orders WHERE buyer_id=? AND kind='subscription' AND status='PENDING' ORDER BY id DESC LIMIT 1",[$actor])->fetch()?:null;
        } elseif ($user['role']!=='admin') throw new DomainException('Customer finance access denied.');
        return $result;
    }
    public function createOrder(int $actor, string $kind, ?int $packId=null, bool $schoolConsent=false): int {
        if (!$this->ready()) throw new RuntimeException('Sandbox finance is not installed/enabled.');
        return $this->atomic(function () use ($actor,$kind,$packId,$schoolConsent) {
            $u=$this->user($actor,true); $school=null; $creator=null;
            if ($kind==='institutional') {
                if ($u['role']!=='principal' || !$u['school_id']) throw new DomainException('A principal with a registered school must apply.');
                if (!$schoolConsent) throw new DomainException('Confirm your school’s institutional licensing application before proceeding.');
                $school=(int)$u['school_id'];
                $s=$this->query('SELECT school_name FROM schools WHERE id=? FOR UPDATE',[$school])->fetchColumn();
                if (!$s) throw new DomainException('Registered school required.');
                $amount=FinanceRules::SCHOOL_PRICE; $item='Institutional license / one year / '.$s;
            } elseif ($kind==='subscription' || $kind==='pack') {
                if (!in_array($u['role'],['sped_teacher','teacher','master_teacher'],true)) throw new DomainException('Teachers only.');
                $amount=FinanceRules::SUBSCRIPTION_PRICE; $item='Premium Resource Hub / one calendar month';
                if ($kind==='pack') {
                    $p=$this->query("SELECT p.*,f.approved_amount,f.review_status FROM resource_hub_packs p JOIN finance_pack_pricing f ON f.pack_id=p.id WHERE p.id=? FOR UPDATE",[$packId])->fetch();
                    if (!$p || $p['status']!=='published' || $p['review_status']!=='APPROVED' || (int)$p['approved_amount']<5900) throw new DomainException('Premium pack is unavailable.');
                    if ((int)$p['created_by']===$actor || $this->query("SELECT id FROM finance_entitlements WHERE buyer_id=? AND pack_id=? AND kind='pack' AND state='ACTIVE'",[$actor,$packId])->fetchColumn()) throw new DomainException('This pack is already owned.');
                    $amount=(int)$p['approved_amount']; $item=$p['title']; $creator=(int)$p['created_by'];
                }
            } else throw new DomainException('Invalid product.');
            $pending=$this->query("SELECT id FROM finance_orders WHERE kind=? AND status='PENDING' AND ".($kind==='institutional'?'school_id=?':'buyer_id=?')." AND COALESCE(pack_id,0)=? ORDER BY id DESC LIMIT 1",[$kind,$school??$actor,$kind==='pack'?$packId:0])->fetchColumn();
            if ($pending) { if ($kind==='institutional') $this->acceptSchoolApplication((int)$pending,$actor); return (int)$pending; }
            $reference='SIGNED-T-'.gmdate('Ymd').'-'.strtoupper(bin2hex(random_bytes(8)));
            $this->query('INSERT INTO finance_orders(reference,buyer_id,school_id,pack_id,creator_id,kind,buyer_name,item_name,amount,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())',[$reference,$actor,$school,$kind==='pack'?$packId:null,$creator,$kind,$u['name'],substr($item,0,255),$amount]);
            $id=(int)$this->db->lastInsertId(); $this->audit($id,$actor,'ORDER_CREATED','Sandbox order created from server prices.');
            if ($kind==='institutional') $this->acceptSchoolApplication($id,$actor);
            return $id;
        });
    }
    /** Create the unpaid annual license request when a new school/Principal application is approved. */
    public function prepareNewSchoolLicense(int $principalId, int $adminId): int {
        if (!self::enabled()) return 0;
        $create = function () use ($principalId,$adminId): int {
            $this->admin($adminId);
            $u=$this->user($principalId,true);
            if ($u['role']!=='principal' || !$u['school_id']) throw new DomainException('Approved Principal with a registered school required.');
            $school=(int)$u['school_id'];
            $name=$this->query('SELECT school_name FROM schools WHERE id=? FOR UPDATE',[$school])->fetchColumn();
            if (!$name) throw new DomainException('Registered school required.');
            $existing=$this->query("SELECT id FROM finance_orders WHERE school_id=? AND kind='institutional' AND status IN ('PENDING','PAID') ORDER BY id DESC LIMIT 1",[$school])->fetchColumn();
            if ($existing) return (int)$existing;
            $reference='SIGNED-T-'.gmdate('Ymd').'-'.strtoupper(bin2hex(random_bytes(8)));
            $item='Institutional license / one year / '.$name;
            $this->query('INSERT INTO finance_orders(reference,buyer_id,school_id,kind,buyer_name,item_name,amount,created_at,updated_at) VALUES(?,?,?,\'institutional\',?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())',[$reference,$principalId,$school,$u['name'],substr($item,0,255),FinanceRules::SCHOOL_PRICE]);
            $id=(int)$this->db->lastInsertId();
            $this->audit($id,$adminId,'ORDER_CREATED','Annual school license payment request created after new school and Principal approval.');
            $this->audit($id,$principalId,'LICENSE_APPLICATION_ACCEPTED','New school application requires independently verified institutional payment before faculty activation.');
            $this->audit($id,$adminId,'SCHOOL_LICENSE_REQUIRED','New school approved; teaching access and faculty activation await verified annual license payment.');
            return $id;
        };
        return $this->db->inTransaction() ? $create() : $this->atomic($create);
    }
    /** Called only under the application transaction after explicit principal consent. */
    private function acceptSchoolApplication(int $order, int $actor): void {
        if (!$this->query("SELECT id FROM finance_audit WHERE order_id=? AND action='LICENSE_APPLICATION_ACCEPTED' LIMIT 1",[$order])->fetchColumn()) {
            $this->audit($order,$actor,'LICENSE_APPLICATION_ACCEPTED','Principal explicitly applied to institutional licensing while Finance was enabled; activation requires independently verified payment.');
        }
    }
    public function checkout(int $id, int $actor, PayMongoSandbox $provider): string {
        $order=$this->order($id,$actor);
        // Serialize claims in MySQL; no network inside a DB transaction.
        if ($order['status']!=='PENDING') throw new DomainException('Order is not pending.');
        if ($order['checkout_id']) return (string)$order['checkout_url'];
        $claimed=$this->query("UPDATE finance_orders SET checkout_state='CREATING',updated_at=UTC_TIMESTAMP() WHERE id=? AND checkout_state='NEW' AND status='PENDING'",[$id])->rowCount();
        if (!$claimed) throw new DomainException('Checkout creation is uncertain. Ask an administrator to reconcile; do not pay a second order.');
        try {
            $session=$provider->createCheckout($order); $url=$session['attributes']['checkout_url']??'';
            if (!preg_match('#^https://checkout\.paymongo\.com/#',$url) || !preg_match('/^cs_[A-Za-z0-9]+$/',$session['id']??'')) throw new RuntimeException('Unexpected checkout response.');
            $this->query("UPDATE finance_orders SET checkout_id=?,checkout_url=?,checkout_state='READY',updated_at=UTC_TIMESTAMP() WHERE id=?",[$session['id'],$url,$id]);
            return $url;
        } catch (Throwable $e) {
            $this->query("UPDATE finance_orders SET checkout_state='UNCERTAIN',updated_at=UTC_TIMESTAMP() WHERE id=?",[$id]);
            throw $e;
        }
    }
    public function webhook(string $raw, string $signature, PayMongoSandbox $provider): void {
        FinanceRules::signature($raw,$signature,(string)getenv('PAYMONGO_WEBHOOK_SECRET'));
        $event=json_decode($raw,true,64,JSON_THROW_ON_ERROR)['data']??[]; $a=$event['attributes']??[];
        if (($a['livemode']??null)!==false || !preg_match('/^evt_[A-Za-z0-9]+$/',$event['id']??'')) throw new DomainException('Test event required.');
        $type=$a['type']??'';
        if (!in_array($type,['checkout_session.payment.paid','payment.refunded','payment.refund.updated'],true)) throw new DomainException('Unsupported event type.');
        $hash=hash('sha256',$raw);
        $seen=$this->query('SELECT body_hash FROM finance_events WHERE event_id=?',[$event['id']])->fetchColumn();
        if ($seen) { if (!hash_equals($seen,$hash)) throw new DomainException('Event identifier conflict.'); return; }
        if ($type==='checkout_session.payment.paid') {
            $data=$a['data']??[];
            $order=$this->query('SELECT * FROM finance_orders WHERE checkout_id=?',[$data['id']??''])->fetch();
            if (!$order) throw new DomainException('Unknown checkout session.');
            FinanceRules::paidSession($order,$data);
            // Reconcile signed event against current provider state, not browser redirects.
            $session=$provider->request('GET','/v1/checkout_sessions/'.$order['checkout_id']);
            $this->settle($order,$session,$provider,$event['id'],$hash,$type);
        } else {
            // Signed refund events trigger a fresh retrieval of all known refunds for this payment.
            $data=$a['data']??[];
            $payment=$data['attributes']['payment_id']??($data['type']==='payment'?($data['id']??''):'');
            $refunds=$this->query('SELECT r.id,r.order_id FROM finance_refunds r JOIN finance_orders o ON o.id=r.order_id WHERE o.payment_id=? AND r.provider_id IS NOT NULL',[$payment])->fetchAll();
            if (!$refunds) throw new DomainException('Refund requires a recorded admin-reviewed request.');
            foreach ($refunds as $r) $this->reconcileRefund((int)$r['id'],$provider);
            $this->query('INSERT IGNORE INTO finance_events(event_id,body_hash,event_type,received_at,order_id) VALUES(?,?,?,UTC_TIMESTAMP(),?)',[$event['id'],$hash,$type,$refunds[0]['order_id']]);
        }
    }
    private function settle(array $order, array $session, PayMongoSandbox $provider, ?string $eventId=null, ?string $hash=null, string $type='RECONCILE'): void {
        $payment=FinanceRules::paidSession($order,$session);
        $evidence=PayMongoFeeEvidence::retrieve($order,$payment['id'],$provider);
        $this->atomic(function () use ($order,$payment,$evidence,$eventId,$hash,$type) {
            // One lock order everywhere: buyer -> school -> order.
            $this->user((int)$order['buyer_id'],true);
            if ($order['school_id']) $this->query('SELECT id FROM schools WHERE id=? FOR UPDATE',[$order['school_id']]);
            $o=$this->query('SELECT * FROM finance_orders WHERE id=? FOR UPDATE',[$order['id']])->fetch();
            if ($eventId) {
                $seen=$this->query('SELECT body_hash FROM finance_events WHERE event_id=?',[$eventId])->fetchColumn();
                if ($seen) { if (!hash_equals($seen,$hash)) throw new DomainException('Event conflict.'); return; }
            }
            if ($o['payment_id'] && $o['payment_id']!==$payment['id']) throw new DomainException('Order has a different payment; admin review required.');
            if (!$o['payment_id']) {
                if ($o['status']!=='PENDING') throw new DomainException('Closed order requires manual reconciliation.');
                $this->query("UPDATE finance_orders SET status='PAID',payment_id=?,paid_at=?,access_status='ACTIVE',updated_at=UTC_TIMESTAMP() WHERE id=?",[$payment['id'],$payment['paid_at']??gmdate('Y-m-d H:i:s'),$o['id']]);
                $this->query('INSERT INTO finance_accounting(order_id,gross,updated_at) VALUES(?,?,UTC_TIMESTAMP())',[$o['id'],$o['amount']]);
                if ($o['kind']==='institutional') {
                    if (!$this->query("SELECT id FROM finance_audit WHERE order_id=? AND action='LICENSE_APPLICATION_ACCEPTED' LIMIT 1",[$o['id']])->fetchColumn()) throw new DomainException('Explicit principal license application required.');
                    $expiry=$this->query("SELECT MAX(ends_at) FROM finance_entitlements WHERE school_id=? AND kind='institutional' AND state='ACTIVE'",[$o['school_id']])->fetchColumn();
                    $start=FinanceRules::schoolStart($expiry?:null,gmdate('Y-m-d H:i:s')); $end=FinanceRules::extend($start,12);
                    $this->query("INSERT INTO finance_entitlements(order_id,buyer_id,school_id,kind,starts_at,ends_at,activated_at) VALUES(?,?,?,'institutional',?,?,UTC_TIMESTAMP())",[$o['id'],$o['buyer_id'],$o['school_id'],$start,$end]);
                    if (!$this->schoolEnrolled((int)$o['school_id'])) $this->audit((int)$o['id'],null,'SCHOOL_LICENSE_ENROLLED','First independently verified institutional payment enrolled this school in licensing and grace rules.');
                    $this->audit((int)$o['id'],null,'LICENSE_ACTIVATED','Automatically activated after independently verified payment: '.$start.' to '.$end);
                } else {
                    $start=gmdate('Y-m-d H:i:s'); $end=null;
                    if ($o['kind']==='subscription') {
                        $latest=$this->query("SELECT MAX(ends_at) FROM finance_entitlements WHERE buyer_id=? AND kind='subscription' AND state='ACTIVE'",[$o['buyer_id']])->fetchColumn();
                        if ($latest && $latest>$start) $start=$latest;
                        $end=FinanceRules::extend($start,1);
                    }
                    $this->query('INSERT INTO finance_entitlements(order_id,buyer_id,pack_id,kind,starts_at,ends_at) VALUES(?,?,?,?,?,?)',[$o['id'],$o['buyer_id'],$o['pack_id'],$o['kind'],$start,$end]);
                }
                $this->audit((int)$o['id'],null,'PAYMENT_VERIFIED','Provider payment '.$payment['id']);
            }
            // Compatibility for an older paid order that was waiting for the removed admin approval step.
            if ($o['kind']==='institutional' && $o['payment_id'] && !$this->query("SELECT id FROM finance_entitlements WHERE order_id=? AND kind='institutional' LIMIT 1",[$o['id']])->fetchColumn()) {
                if (!$this->query("SELECT id FROM finance_audit WHERE order_id=? AND action='LICENSE_APPLICATION_ACCEPTED' LIMIT 1",[$o['id']])->fetchColumn()) throw new DomainException('Explicit principal license application required.');
                $expiry=$this->query("SELECT MAX(ends_at) FROM finance_entitlements WHERE school_id=? AND kind='institutional' AND state='ACTIVE'",[$o['school_id']])->fetchColumn();
                $start=FinanceRules::schoolStart($expiry?:null,gmdate('Y-m-d H:i:s')); $end=FinanceRules::extend($start,12);
                $this->query("INSERT INTO finance_entitlements(order_id,buyer_id,school_id,kind,starts_at,ends_at,activated_at) VALUES(?,?,?,'institutional',?,?,UTC_TIMESTAMP())",[$o['id'],$o['buyer_id'],$o['school_id'],$start,$end]);
                $this->query("UPDATE finance_orders SET access_status='ACTIVE',updated_at=UTC_TIMESTAMP() WHERE id=?",[$o['id']]);
                if (!$this->schoolEnrolled((int)$o['school_id'])) $this->audit((int)$o['id'],null,'SCHOOL_LICENSE_ENROLLED','First independently verified institutional payment enrolled this school in licensing and grace rules.');
                $this->audit((int)$o['id'],null,'LICENSE_ACTIVATED','Automatically activated after independently verified payment: '.$start.' to '.$end);
            }
            $this->recordFeeEvidence($o,$evidence);
            if ($evidence['fee']!==null) $this->postFees($o,$evidence['fee'],$evidence);
            if ($eventId) $this->query('INSERT INTO finance_events(event_id,body_hash,event_type,received_at,order_id) VALUES(?,?,?,UTC_TIMESTAMP(),?)',[$eventId,$hash,$type,$o['id']]);
        });
    }
    private function recordFeeEvidence(array $order, array $evidence): void {
        $fingerprint=hash('sha256',$evidence['source'].'|'.($evidence['payload_hash']??'UNAVAILABLE'));
        $this->query('INSERT IGNORE INTO finance_fee_evidence(order_id,payment_id,evidence_hash,payload_hash,source,status,fee,net,balance_transaction_id,provider_updated_at,reason,retrieved_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP())',[
            $order['id'],$evidence['payment_id'],$fingerprint,$evidence['payload_hash'],$evidence['source'],$evidence['status'],$evidence['fee'],$evidence['net'],$evidence['balance_transaction_id'],$evidence['provider_updated_at'],$evidence['reason']]);
    }
    private function postFees(array $order, int $fee, array $evidence): void {
        $a=$this->query('SELECT * FROM finance_accounting WHERE order_id=? FOR UPDATE',[$order['id']])->fetch();
        if ($a['status']!=='PENDING_FEES') {
            if ((int)$a['fee']!==$fee) {
                $this->query("UPDATE finance_accounting SET status='FEE_REVIEW',updated_at=UTC_TIMESTAMP() WHERE order_id=?",[$order['id']]);
                $this->audit((int)$order['id'],null,'PROVIDER_FEE_CONFLICT','Retrieved provider fee differs from the posted evidence; payouts held for review.');
            }
            return;
        }
        // Do not post retroactive fees across already confirmed refunds without review.
        if ($this->query("SELECT id FROM finance_refunds WHERE order_id=? AND status='CONFIRMED' LIMIT 1",[$order['id']])->fetchColumn()) {
            $this->query("UPDATE finance_accounting SET status='REFUND_FEE_REVIEW',updated_at=UTC_TIMESTAMP() WHERE order_id=?",[$order['id']]);
            $this->audit((int)$order['id'],null,'REFUND_FEE_REVIEW','Retrieved fees retained as evidence; refund accounting must be reconciled before allocating proceeds.');
            return;
        }
        $v=FinanceRules::split((int)$order['amount'],$fee,$order['kind']);
        $this->query("UPDATE finance_accounting SET fee=?,net=?,signed_share=?,creator_pool=?,undistributed=?,rounding_numerator=?,fee_basis=?,status='VERIFIED',updated_at=UTC_TIMESTAMP() WHERE order_id=?",[$fee,$v['net'],$v['signed_share'],$v['creator_pool'],$v['undistributed'],$v['rounding_numerator'],$evidence['source'].'; SHA256 '.$evidence['payload_hash'],$order['id']]);
        if ($order['kind']==='pack') $this->query('INSERT INTO finance_earnings(order_id,creator_id,amount,basis,created_at) VALUES(?,?,?,?,UTC_TIMESTAMP())',[$order['id'],$order['creator_id'],$v['creator_pool'],'PACK_NET_70']);
    }
    public function cancel(int $id, int $actor, PayMongoSandbox $provider): void {
        $o=$this->order($id,$actor);
        if ($o['status']!=='PENDING') throw new DomainException('Only pending orders can be cancelled.');
        if ($o['checkout_id']) {
            $session=$provider->request('POST','/v1/checkout_sessions/'.$o['checkout_id'].'/expire');
            if (($session['id']??'')!==$o['checkout_id'] || ($session['attributes']['status']??'')!=='expired') throw new DomainException('Checkout expiry must be confirmed before cancellation.');
        } elseif ($o['checkout_state']!=='NEW') throw new DomainException('Recover uncertain checkout creation before cancelling.');
        $this->atomic(function () use($id,$actor) {
            $changed=$this->query("UPDATE finance_orders SET status='CANCELLED',access_status='PENDING',updated_at=UTC_TIMESTAMP() WHERE id=? AND status='PENDING'",[$id])->rowCount();
            if (!$changed) throw new DomainException('Order changed; reconcile provider state.');
            $this->audit($id,$actor,'ORDER_CANCELLED','No payable checkout remains.');
        });
    }
    public function reconcile(int $id, int $actor, PayMongoSandbox $provider): void {
        $this->admin($actor); $o=$this->order($id,$actor);
        $this->reconcileOrder($o,$provider);
    }
    /** Buyer-initiated provider check for localhost, where PayMongo cannot deliver a webhook. */
    public function reconcileOwnOrder(int $id, int $actor, PayMongoSandbox $provider): void {
        $o=$this->order($id,$actor);
        if ((int)$o['buyer_id'] !== $actor) throw new DomainException('Only the buyer can check this payment.');
        $this->reconcileOrder($o,$provider);
    }
    private function reconcileOrder(array $o, PayMongoSandbox $provider): void {
        if (!$o['checkout_id']) throw new DomainException('Obtain the checkout session ID from PayMongo; use attach checkout to recover an uncertain creation.');
        $session=$provider->request('GET','/v1/checkout_sessions/'.$o['checkout_id']);
        if (($session['attributes']['status']??'')==='expired' && $o['status']==='PENDING') {
            $this->query("UPDATE finance_orders SET status='CANCELLED',updated_at=UTC_TIMESTAMP() WHERE id=? AND status='PENDING'",[$o['id']]);
            $this->audit((int)$o['id'],null,'EXPIRED_SESSION_RECONCILED',$o['checkout_id']);
        } else $this->settle($o,$session,$provider);
    }
    public function attachCheckout(int $id, string $checkout, int $actor, PayMongoSandbox $provider): void {
        $this->admin($actor); $o=$this->order($id,$actor);
        if ($o['checkout_id'] || $o['status']!=='PENDING' || !preg_match('/^cs_[A-Za-z0-9]+$/',$checkout)) throw new DomainException('Cannot attach this checkout.');
        $s=$provider->request('GET','/v1/checkout_sessions/'.$checkout); $a=$s['attributes'];
        if (($a['reference_number']??'')!==$o['reference'] || ($a['metadata']['buyer_id']??'')!==(string)$o['buyer_id'] || ($a['metadata']['order_reference']??'')!==$o['reference'] || !preg_match('#^https://checkout\.paymongo\.com/#',$a['checkout_url']??'')) throw new DomainException('Checkout identity mismatch.');
        $this->query("UPDATE finance_orders SET checkout_id=?,checkout_url=?,checkout_state='READY',updated_at=UTC_TIMESTAMP() WHERE id=? AND checkout_id IS NULL",[$checkout,$a['checkout_url'],$id]);
        $this->audit($id,$actor,'CHECKOUT_RECOVERED',$checkout);
    }
    private function schoolEnrolled(int $school): bool {
        return (bool)$this->query("SELECT a.id FROM finance_audit a JOIN finance_orders o ON o.id=a.order_id WHERE o.school_id=? AND o.kind='institutional' AND a.action='SCHOOL_LICENSE_ENROLLED' LIMIT 1",[$school])->fetchColumn();
    }
    public function schoolStatus(int $school, ?string $now=null): array {
        // School enrollment enforcement is separate from persistent premium-content protection.
        if (!self::enabled()) return ['state'=>'FINANCE_DISABLED','expires_at'=>null,'allowed'=>true,'enrolled'=>false];
        if (!$this->schoolEnrolled($school)) {
            $required=$this->query("SELECT a.id FROM finance_audit a JOIN finance_orders o ON o.id=a.order_id WHERE o.school_id=? AND o.kind='institutional' AND a.action='SCHOOL_LICENSE_REQUIRED' LIMIT 1",[$school])->fetchColumn();
            if ($required) return ['state'=>'PAYMENT_REQUIRED','expires_at'=>null,'allowed'=>false,'enrolled'=>false,'required'=>true];
            return FinanceRules::schoolAccess(false,null,$now??gmdate('Y-m-d H:i:s'));
        }
        $now=$now??gmdate('Y-m-d H:i:s');
        $end=$this->query("SELECT MAX(ends_at) FROM finance_entitlements WHERE school_id=? AND kind='institutional' AND state='ACTIVE' AND starts_at<=?",[$school,$now])->fetchColumn();
        return FinanceRules::schoolAccess(true,$end?:null,$now);
    }
    /** Called in the successful clone transaction, under the period row lock. Never on preview. */
    public function recordUse(int $pack, int $user): void {
        if (!self::accessEnabled($this->db)) return;
        if (!$this->db->inTransaction()) throw new LogicException('Usage must be atomic with successful template use.');
        $access=$this->requirePack($pack,$user); $period=$access['period'];
        if (!$period) return;
        $p=$this->query('SELECT * FROM finance_entitlements WHERE id=? FOR UPDATE',[$period['id']])->fetch();
        if ($p['state']!=='ACTIVE' || $p['ends_at']<=gmdate('Y-m-d H:i:s')) throw new DomainException('Subscription expired during use.');
        $closed=$this->query('SELECT pool_closed_at FROM finance_accounting WHERE order_id=?',[$p['order_id']])->fetchColumn();
        if ($closed) throw new DomainException('Subscription period is closed.');
        $creator=(int)$this->query('SELECT created_by FROM resource_hub_packs WHERE id=?',[$pack])->fetchColumn();
        if ($creator!==$user) $this->query('INSERT IGNORE INTO finance_usage(period_id,pack_id,creator_id,used_at) VALUES(?,?,?,UTC_TIMESTAMP())',[$p['id'],$pack,$creator]);
    }
    public function closePool(int $id, int $actor): void {
        $this->admin($actor);
        $this->atomic(function () use($id,$actor) {
            $e=$this->query("SELECT * FROM finance_entitlements WHERE order_id=? AND kind='subscription' FOR UPDATE",[$id])->fetch();
            $a=$this->query('SELECT * FROM finance_accounting WHERE order_id=? FOR UPDATE',[$id])->fetch();
            if (!$e || $e['ends_at']>gmdate('Y-m-d H:i:s') || !$a || $a['status']!=='VERIFIED' || $a['pool_closed_at']) throw new DomainException('An ended period with verified fees is required.');
            if ($this->query("SELECT id FROM finance_refunds WHERE order_id=? AND status NOT IN ('REJECTED','FAILED')",[$id])->fetchColumn()) throw new DomainException('Resolve refunds before allocating this pool.');
            $usage=$this->query('SELECT creator_id,COUNT(*) AS uses FROM finance_usage WHERE period_id=? GROUP BY creator_id ORDER BY creator_id',[$e['id']])->fetchAll();
            $total=array_sum(array_column($usage,'uses')); $allocated=0;
            foreach ($usage as $row) {
                $amount=intdiv((int)$a['creator_pool']*(int)$row['uses'],(int)$total);
                $allocated+=$amount;
                $this->query('INSERT INTO finance_earnings(order_id,creator_id,amount,basis,created_at) VALUES(?,?,?,?,UTC_TIMESTAMP())',[$id,$row['creator_id'],$amount,'SUBSCRIPTION_USAGE']);
            }
            $this->query('UPDATE finance_accounting SET undistributed=?,pool_closed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE order_id=?',[(int)$a['creator_pool']-$allocated,$id]);
            $this->audit($id,$actor,'POOL_CLOSED','Allocated '.$allocated.' centavos; remainder remains creator liability.');
        });
    }
    public function validatePremiumSnapshot(int $amount, array $pages, array $activities): void {
        if ($amount !== 0 && ($amount < 5900 || $amount > 9900)) throw new DomainException('Premium price must be PHP 59–99.');
        if ($amount === 0) return;
        require_once __DIR__.'/PremiumMediaService.php';
        PremiumMediaService::validateSnapshot($pages,$activities);
    }

    public function proposePrice(int $pack, int $amount, int $actor): void {
        if ($amount!==0 && ($amount<5900 || $amount>9900)) throw new DomainException('Premium prices must be PHP 59–99.');
        $this->atomic(function () use($pack,$amount,$actor) {
            $u=$this->user($actor);
            $p=$this->query('SELECT * FROM resource_hub_packs WHERE id=? FOR UPDATE',[$pack])->fetch();
            if (!$p || (int)$p['created_by']!==$actor || !in_array($u['role'],['sped_teacher','teacher','master_teacher'],true)) throw new DomainException('Only the creator can propose pricing.');
            if ($amount > 0 && (int)$p['clone_count'] > 0) throw new DomainException('An already adapted free pack cannot be converted to premium. Publish a new reviewed pack.');
            $pages=$this->query('SELECT * FROM resource_hub_pages WHERE pack_id=?',[$pack])->fetchAll();
            $activities=$this->query('SELECT * FROM resource_hub_activities WHERE pack_id=?',[$pack])->fetchAll();
            $this->validatePremiumSnapshot($amount,$pages,$activities);
            if ($amount>0 && PremiumMediaService::tokens($pages,$activities)) {
                $media=new PremiumMediaService($this);
                $tokens=$media->validateOwnedReferences($pages,$activities,$actor);
                foreach($tokens as $token) $this->query('INSERT IGNORE INTO finance_pack_media(pack_id,token) VALUES(?,?)',[$pack,$token]);
            }
            // Prevent changing prices/ownership of already sold packs through a new proposal.
            if ($this->query('SELECT id FROM finance_orders WHERE pack_id=? LIMIT 1',[$pack])->fetchColumn()) throw new DomainException('Pricing for packs with orders requires separate admin review.');
            $this->query("INSERT INTO finance_pack_pricing(pack_id,creator_id,proposed_amount,review_status,updated_at) VALUES(?,?,?,'PENDING',UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE proposed_amount=VALUES(proposed_amount),review_status='PENDING',approved_amount=NULL,reviewed_by=NULL,reviewed_at=NULL,updated_at=UTC_TIMESTAMP()",[$pack,$actor,$amount]);
            $this->audit(null,$actor,'PRICE_PROPOSED','Pack '.$pack.' / '.$amount.' centavos');
        });
    }
    public function reviewPrice(int $pack, bool $approve, int $actor): void {
        $this->admin($actor);
        $this->atomic(function () use($pack,$approve,$actor) {
            $p=$this->query("SELECT f.* FROM finance_pack_pricing f JOIN resource_hub_packs p ON p.id=f.pack_id AND p.created_by=f.creator_id WHERE f.pack_id=? FOR UPDATE",[$pack])->fetch();
            if (!$p || $p['review_status']!=='PENDING') throw new DomainException('Pending pricing proposal required.');
            $this->query('UPDATE finance_pack_pricing SET review_status=?,approved_amount=?,reviewed_by=?,reviewed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE pack_id=?',[$approve?'APPROVED':'REJECTED',$approve?$p['proposed_amount']:null,$actor,$pack]);
            $this->audit(null,$actor,'PRICE_REVIEWED','Pack '.$pack.' / '.($approve?'approved':'rejected'));
        });
    }
    public function requestRefund(int $id, int $amount, string $reason, int $actor): int {
        $o=$this->order($id,$actor);
        if (!$reason || strlen($reason)>255) throw new DomainException('Provide a reason up to 255 characters.');
        return $this->atomic(function () use($o,$id,$amount,$reason,$actor) {
            $o=$this->query('SELECT * FROM finance_orders WHERE id=? FOR UPDATE',[$id])->fetch();
            $reserved=(int)$this->query("SELECT COALESCE(SUM(amount),0) FROM finance_refunds WHERE order_id=? AND status NOT IN ('REJECTED','FAILED')",[$id])->fetchColumn();
            if ($o['status']!=='PAID' || $amount<1 || $amount+$reserved>(int)$o['amount']) throw new DomainException('Refund amount exceeds available paid balance.');
            $this->query('INSERT INTO finance_refunds(order_id,amount,reason,requested_by,created_at,updated_at) VALUES(?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())',[$id,$amount,$reason,$actor]);
            $rid=(int)$this->db->lastInsertId(); $this->audit($id,$actor,'REFUND_REQUESTED','Request '.$rid.' / '.$amount.' centavos'); return $rid;
        });
    }
    public function reviewRefund(int $refund, bool $approve, string $note, int $actor): void {
        $this->admin($actor);
        if (!$note || strlen($note)>255) throw new DomainException('An admin decision note is required.');
        $changed=$this->query("UPDATE finance_refunds SET status=?,reviewed_by=?,decision_note=?,updated_at=UTC_TIMESTAMP() WHERE id=? AND status='REQUESTED'",[$approve?'APPROVED':'REJECTED',$actor,$note,$refund])->rowCount();
        if (!$changed) throw new DomainException('Refund is not awaiting review.');
        $r=$this->query('SELECT order_id FROM finance_refunds WHERE id=?',[$refund])->fetch();
        $this->audit((int)$r['order_id'],$actor,'REFUND_REVIEWED',($approve?'Approved':'Rejected').' request '.$refund);
    }
    public function submitRefund(int $refund, int $actor, PayMongoSandbox $provider): void {
        $this->admin($actor);
        $r=$this->query('SELECT r.*,o.payment_id FROM finance_refunds r JOIN finance_orders o ON o.id=r.order_id WHERE r.id=?',[$refund])->fetch();
        if (!$r || !$this->query("UPDATE finance_refunds SET status='SUBMITTING',updated_at=UTC_TIMESTAMP() WHERE id=? AND status='APPROVED'",[$refund])->rowCount()) throw new DomainException('Approved refund required; uncertain submissions must be reconciled before retrying.');
        try {
            $p=$provider->request('POST','/v1/refunds',['amount'=>(int)$r['amount'],'payment_id'=>$r['payment_id'],'reason'=>'others','notes'=>'SignED sandbox refund '.$refund,'metadata'=>['signed_refund_id'=>(string)$refund]]);
            if (!preg_match('/^ref_[A-Za-z0-9]+$/',$p['id']??'') || ($p['attributes']['payment_id']??'')!==$r['payment_id']) throw new DomainException('Refund identity mismatch.');
            $this->query("UPDATE finance_refunds SET provider_id=?,status='PROCESSING',updated_at=UTC_TIMESTAMP() WHERE id=?",[$p['id'],$refund]);
            $this->reconcileRefund($refund,$provider);
        } catch(Throwable $e) {
            $this->query("UPDATE finance_refunds SET status='UNCERTAIN',updated_at=UTC_TIMESTAMP() WHERE id=? AND status='SUBMITTING'",[$refund]); throw $e;
        }
    }
    public function attachRefund(int $refund, string $providerId, int $actor, PayMongoSandbox $provider): void {
        $this->admin($actor);
        if (!preg_match('/^ref_[A-Za-z0-9]+$/',$providerId)) throw new DomainException('Invalid provider refund identifier.');
        $r=$this->query('SELECT r.*,o.payment_id FROM finance_refunds r JOIN finance_orders o ON o.id=r.order_id WHERE r.id=?',[$refund])->fetch();
        if (!$r || $r['provider_id'] || !in_array($r['status'],['UNCERTAIN','SUBMITTING','APPROVED'],true)) throw new DomainException('Refund cannot be attached.');
        $p=$provider->request('GET','/v1/refunds/'.$providerId); $a=$p['attributes'];
        if (($a['payment_id']??'')!==$r['payment_id'] || ($a['amount']??null)!==(int)$r['amount'] || ($a['metadata']['signed_refund_id']??'')!==(string)$refund) throw new DomainException('Refund identity mismatch.');
        $this->query("UPDATE finance_refunds SET provider_id=?,status='PROCESSING',updated_at=UTC_TIMESTAMP() WHERE id=? AND provider_id IS NULL",[$providerId,$refund]);
        $this->audit((int)$r['order_id'],$actor,'REFUND_RECOVERED',$providerId);
        $this->reconcileRefund($refund,$provider);
    }
    public function reconcileRefund(int $refund, PayMongoSandbox $provider): void {
        $r=$this->query('SELECT * FROM finance_refunds WHERE id=?',[$refund])->fetch();
        if (!$r || !$r['provider_id']) throw new DomainException('Recorded provider refund required.');
        $p=$provider->request('GET','/v1/refunds/'.$r['provider_id']); $v=$p['attributes'];
        $this->atomic(function () use($refund,$r,$p,$v) {
            $o=$this->query('SELECT * FROM finance_orders WHERE id=? FOR UPDATE',[$r['order_id']])->fetch();
            $r=$this->query('SELECT * FROM finance_refunds WHERE id=? FOR UPDATE',[$refund])->fetch();
            if (($p['id']??'')!==$r['provider_id'] || ($v['payment_id']??'')!==$o['payment_id'] || ($v['amount']??null)!==(int)$r['amount'] || ($v['currency']??'')!=='PHP') throw new DomainException('Refund verification mismatch.');
            if ($r['status']==='CONFIRMED') return;
            $status=$v['status']??'';
            if (!in_array($status,['pending','processing','succeeded','failed'],true)) throw new DomainException('Unknown refund status.');
            $this->query('UPDATE finance_refunds SET provider_status=?,status=?,updated_at=UTC_TIMESTAMP() WHERE id=?',[$status,$status==='succeeded'?'CONFIRMED':($status==='failed'?'FAILED':'PROCESSING'),$refund]);
            if ($status!=='succeeded') return;
            $total=(int)$this->query("SELECT SUM(amount) FROM finance_refunds WHERE order_id=? AND status='CONFIRMED'",[$o['id']])->fetchColumn();
            if ($total>(int)$o['amount']) throw new DomainException('Refund total exceeds paid gross.');
            $this->query("UPDATE finance_orders SET status=?,access_status=?,updated_at=UTC_TIMESTAMP() WHERE id=?",[$total===(int)$o['amount']?'REFUNDED':'PAID',$total===(int)$o['amount']?'REVOKED':'REFUND_REVIEW',$o['id']]);
            $this->query('UPDATE finance_entitlements SET state=? WHERE order_id=?',[$total===(int)$o['amount']?'REVOKED':'SUSPENDED',$o['id']]);
            // Preserve original fees/shares/payouts. Reverse creator allocations proportionally;
            // retained processing cost is borne by SignED. Never invent a returned provider fee.
            $account=$this->query('SELECT * FROM finance_accounting WHERE order_id=? FOR UPDATE',[$o['id']])->fetch();
            if ($account && $account['status']==='VERIFIED') {
                $earnings=$this->query('SELECT * FROM finance_earnings WHERE order_id=? AND refund_id IS NULL ORDER BY creator_id,id FOR UPDATE',[$o['id']])->fetchAll();
                $creatorDelta=0;
                foreach($earnings as $earning) {
                    $this->query('SELECT id FROM users WHERE id=? FOR UPDATE',[$earning['creator_id']]);
                    $target=intdiv((int)$earning['amount']*$total,(int)$o['amount']);
                    $prior=-(int)$this->query('SELECT COALESCE(SUM(amount),0) FROM finance_earnings WHERE order_id=? AND creator_id=? AND refund_id IS NOT NULL',[$o['id'],$earning['creator_id']])->fetchColumn();
                    $delta=-($target-$prior); $creatorDelta+=$delta;
                    $this->query('INSERT INTO finance_earnings(order_id,creator_id,amount,basis,refund_id,created_at) VALUES(?,?,?,?,?,UTC_TIMESTAMP())',[$o['id'],$earning['creator_id'],$delta,'REFUND_'.$refund,$refund]);
                }
                $priorUndistributed=-(int)$this->query("SELECT COALESCE(SUM(undistributed_adjustment),0) FROM finance_refunds WHERE order_id=? AND id<>? AND status='CONFIRMED'",[$o['id'],$refund])->fetchColumn();
                $undistributedDelta=-(intdiv((int)$account['undistributed']*$total,(int)$o['amount'])-$priorUndistributed);
                $signedDelta=-(int)$r['amount']-$creatorDelta-$undistributedDelta;
                $this->query("UPDATE finance_refunds SET signed_adjustment=?,creator_adjustment=?,undistributed_adjustment=?,accounting_status='FEE_RETURN_UNVERIFIED' WHERE id=?",[$signedDelta,$creatorDelta,$undistributedDelta,$refund]);
            }
            $this->audit((int)$o['id'],null,'REFUND_CONFIRMED','Refund '.$refund.'; original provider fees retained in historical accounting; any fee return requires reconciliation.');
        });
    }
    public function payout(int $creator, array $earningIds, string $method, string $reference, string $date, int $actor, int $confirmedAmount): void {
        $this->admin($actor); $earningIds=array_values(array_unique(array_map('intval',$earningIds))); sort($earningIds);
        $transfer=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$date,new DateTimeZone('Asia/Manila'));
        if (!$earningIds || min($earningIds)<1 || !$method || strlen($method)>100 || !$reference || strlen($reference)>100 || !$transfer || $transfer->format('Y-m-d\TH:i')!==$date || $transfer->getTimestamp()>time()) throw new DomainException('Confirmed transfer method, unique reference, valid past date and earning IDs required.');
        $this->atomic(function () use($creator,$earningIds,$method,$reference,$transfer,$actor,$confirmedAmount) {
            $this->user($creator,true);
            $rows=$this->query('SELECT e.* FROM finance_earnings e LEFT JOIN finance_payout_items i ON i.earning_id=e.id WHERE e.creator_id=? AND i.earning_id IS NULL ORDER BY e.id FOR UPDATE',[$creator])->fetchAll();
            $selected=[]; $amount=0;
            foreach($rows as $row) {
                if ((int)$row['amount']<0 && !in_array((int)$row['id'],$earningIds,true)) throw new DomainException('Include all outstanding negative adjustments in the transfer.');
                if(in_array((int)$row['id'],$earningIds,true)) { $selected[]=$row; $amount+=(int)$row['amount']; }
            }
            if (count($selected)!==count($earningIds) || $amount<1 || $amount!==$confirmedAmount) throw new DomainException('Earnings are unavailable or the net amount is not positive.');
            foreach($selected as $e) {
                if ($this->query('SELECT status FROM finance_accounting WHERE order_id=?',[$e['order_id']])->fetchColumn()!=='VERIFIED') throw new DomainException('Verified fee accounting is required before payout.');
                if ($this->query("SELECT id FROM finance_refunds WHERE order_id=? AND status NOT IN ('REJECTED','FAILED') AND accounting_status<>'RECONCILED' LIMIT 1",[$e['order_id']])->fetchColumn()) throw new DomainException('Resolve refund accounting before paying affected earnings.');
            }
            $this->query('INSERT INTO finance_payouts(creator_id,amount,method,reference,transferred_at,recorded_by,created_at) VALUES(?,?,?,?,?,?,UTC_TIMESTAMP())',[$creator,$amount,$method,$reference,$transfer->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),$actor]);
            $id=(int)$this->db->lastInsertId();
            foreach($selected as $e) $this->query('INSERT INTO finance_payout_items(payout_id,earning_id) VALUES(?,?)',[$id,$e['id']]);
            $this->audit(null,$actor,'PAYOUT_CONFIRMED','Transfer '.$reference.' / '.$amount.' centavos to creator '.$creator);
        });
    }
}
