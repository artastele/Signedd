<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit('CLI only.');}
require_once __DIR__.'/FinanceSandboxPlan.php';
require_once __DIR__.'/../../app/Models/UserModel.php';
require_once __DIR__.'/../../app/Models/SchoolModel.php';
require_once __DIR__.'/../../app/Models/RoleRequestModel.php';
require_once __DIR__.'/../../app/Models/TeacherAssignmentModel.php';
require_once __DIR__.'/../../app/Models/ResourceHubModel.php';

/** CLI fixture adapter: existing Hub storage/sanitization, no fabricated learner/IEP source. */
final class FinanceSandboxHubModel extends ResourceHubModel {
    private PDO $fixtureDb;
    public function __construct(PDO $db){parent::__construct();$this->fixtureDb=$db;}
    public function createSandboxPack(int $creator, int $admin, string $nonce, array $pack): array {
        if(PHP_SAPI!=='cli'||!$this->fixtureDb->inTransaction()||strpos($pack['title'],'SANDBOX TEST DATA - ')!==0)throw new FinanceSandboxSafetyException('Sandbox pack creation requires the scoped fixture transaction.');
        $finance=new FinanceService($this->fixtureDb);
        $page=['title'=>$pack['title'],'content'=>HtmlSanitizer::cleanLessonHtml($pack['content']),'media_type'=>'none','media_path'=>null];
        $finance->validatePremiumSnapshot($pack['price'],[$page],[]);
        $tags=ResourceTagService::approvedTags(['tags'=>'addition, within 10','tags_approved'=>true]);
        $s=$this->fixtureDb->prepare("INSERT INTO resource_hub_packs(source_lesson_plan_id,created_by,author_name,title,description,pdsp_domain,learning_level,learning_competency,language,fsl_supported,fsl_sign_count,page_count,activity_count,tags,status,clone_count) VALUES(NULL,?,?,?,?, 'perceptuo_cognitive','SANDBOX TEST DATA','Addition within 10','English',0,0,1,0,?,'published',0)");
        $s->execute([$creator,FinanceSandboxPlan::accounts()['creator']['name'],$pack['title'],FinanceSandboxPlan::DATASET.'; nonce='.$nonce.'; SANDBOX TEST DATA',$tags]);
        $id=(int)$this->fixtureDb->lastInsertId();
        if($id<1)throw new FinanceSandboxSafetyException('Invalid newly inserted pack identity.');
        $s=$this->fixtureDb->prepare('INSERT INTO resource_hub_pages(pack_id,page_number,title,content,media_type,media_path) VALUES(?,1,?,?,?,NULL)');
        $s->execute([$id,$page['title'],$page['content'],$page['media_type']]);$pageId=(int)$this->fixtureDb->lastInsertId();
        $free=$pack['price']===0;
        $s=$this->fixtureDb->prepare('INSERT INTO finance_pack_pricing(pack_id,creator_id,proposed_amount,approved_amount,review_status,reviewed_by,reviewed_at,updated_at) VALUES(?,?,?,?,?,?,?,UTC_TIMESTAMP())');
        $s->execute([$id,$creator,$pack['price'],$free?0:null,$pack['review'],$free?$admin:null,$free?gmdate('Y-m-d H:i:s'):null]);
        return ['id'=>$id,'page_id'=>$pageId];
    }
}

final class FinanceSandboxDataset {
    private PDO $db;
    private UserModel $users;
    private SchoolModel $schools;
    private RoleRequestModel $roles;
    private TeacherAssignmentModel $staff;
    private FinanceSandboxHubModel $hub;
    public function __construct(PDO $db){
        if(runtime_schema_changes_allowed())throw new FinanceSandboxSafetyException('Automatic migrations must be disabled.');
        $this->db=$db;$this->users=new UserModel();$this->schools=new SchoolModel();
        $this->roles=new RoleRequestModel();$this->staff=new TeacherAssignmentModel();$this->hub=new FinanceSandboxHubModel($db);
    }
    private function query(string $sql,array $p=[]):PDOStatement{$s=$this->db->prepare($sql);$s->execute($p);return $s;}
    public function prerequisites(string $database,array $backup): int {
        $server=$this->db->query('SELECT VERSION() AS version,@@port AS port,@@datadir AS datadir,DATABASE() AS database_name')->fetch(PDO::FETCH_ASSOC);
        if($server['database_name']!==$database || strpos($server['datadir'],'/Applications/MAMP/db/')!==0 ||
            (int)$server['port']!==(int)$backup['local_server']['port'] || $server['datadir']!==$backup['local_server']['datadir']) throw new FinanceSandboxSafetyException('Database is not the backed-up local MAMP instance.');
        $migration=file_get_contents(__DIR__.'/../../database/migrations/20261103_sandbox_finance.sql');
        preg_match_all('/CREATE TABLE IF NOT EXISTS (finance_[a-z_]+)/',$migration,$matches);
        $required=array_merge($matches[1],['users','schools','role_requests','teacher_assignments','resource_hub_packs','resource_hub_pages','resource_hub_activities']);
        foreach($required as $table){
            $engine=$this->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table])->fetchColumn();
            if($engine!=='InnoDB')throw new FinanceSandboxSafetyException('Approved finance schema and transactional existing tables must be installed first.');
        }
        // Verify the actual columns required by all three new evidence/media tables as well.
        foreach(['finance_fee_evidence'=>['order_id','payment_id','evidence_hash','payload_hash','source','status','fee','net','balance_transaction_id','provider_updated_at','reason','retrieved_at'],
                 'finance_media_assets'=>['token','owner_id','original_name','mime_type','byte_size','sha256','created_at'],
                 'finance_pack_media'=>['pack_id','token']] as $table=>$columns){
            $actual=$this->query('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table])->fetchAll(PDO::FETCH_COLUMN);
            if(array_diff($columns,$actual))throw new FinanceSandboxSafetyException('Finance migration is incomplete.');
        }
        $permissions=require __DIR__.'/../../config/permissions.php';
        if(!in_array('iep.implement',$permissions['sped_teacher']??[],true)||!in_array('staff.approve',$permissions['principal']??[],true)||!in_array('*',$permissions['admin']??[],true))throw new FinanceSandboxSafetyException('Expected existing role permissions are unavailable.');
        return count($matches[1]);
    }
    public function admin(string $email): array {
        $u=$this->query("SELECT id,email,role,status,email_verified,deleted_at,locked_until FROM users WHERE email=?",[$email])->fetch(PDO::FETCH_ASSOC);
        if(!$u||$u['role']!=='admin'||$u['status']!=='active'||!$u['email_verified']||$u['deleted_at']!==null||($u['locked_until']&&strtotime($u['locked_until'])>time()))throw new FinanceSandboxSafetyException('A usable existing System Admin is required; no admin will be created or reset.');
        return $u;
    }
    /** All writes are either inserts or model updates of IDs created in this transaction. */
    public function create(array $admin,string $nonce,callable $persistPrepared): array {
        $this->db->beginTransaction();
        try{
            if($this->schools->findBySchoolCode(FinanceSandboxPlan::SCHOOL_CODE))throw new FinanceSandboxSafetyException('Reserved school code already exists without an ownership manifest; refusing adoption.');
            foreach(FinanceSandboxPlan::accounts() as $account)if($this->users->findByEmail($account['email']))throw new FinanceSandboxSafetyException('Reserved sandbox email already exists without an ownership manifest; nothing will be overwritten.');
            $school=(int)$this->schools->createSchool(FinanceSandboxPlan::SCHOOL_CODE,FinanceSandboxPlan::SCHOOL_NAME,'SANDBOX TEST DATA','SANDBOX',FinanceSandboxPlan::DATASET.'; nonce='.$nonce);
            if($school<1)throw new FinanceSandboxSafetyException('Invalid newly inserted school identity.');
            $manifest=['dataset'=>FinanceSandboxPlan::DATASET,'database'=>(string)$this->db->query('SELECT DATABASE()')->fetchColumn(),'state'=>'PREPARED','nonce'=>$nonce,'school_id'=>$school,'admin_id'=>(int)$admin['id'],'accounts'=>[],'packs'=>[]];
            $credentials=['dataset'=>FinanceSandboxPlan::DATASET,'nonce'=>$nonce,'accounts'=>[]];
            foreach(FinanceSandboxPlan::accounts() as $key=>$account){
                $password=bin2hex(random_bytes(24)); // 48 characters; safely below bcrypt's 72-byte limit.
                $id=(int)$this->users->create($account['name'],'SANDBOX',null,ucfirst($key),null,$account['email'],null,$password);
                $created=$id>0?$this->users->findById($id):null;
                if(!$created||$created['email']!==$account['email']||$created['role']!=='user'||$created['school_id']!==null)throw new FinanceSandboxSafetyException('New account identity check failed before role/school updates.');
                // This ID was just returned by INSERT; existing users never reach these mutators.
                $this->users->updateRole($id,$account['role']);$this->users->updateSchoolId($id,$school);$this->users->markEmailVerified($id);
                $request=(int)$this->roles->create($id,$account['role'],['school_id'=>$school,'sandbox_dataset'=>FinanceSandboxPlan::DATASET,'sandbox_nonce'=>$nonce]);
                if($request<1)throw new FinanceSandboxSafetyException('Invalid newly inserted role-request identity.');
                $reviewer=$key==='principal'?(int)$admin['id']:$manifest['accounts']['principal']['id'];
                $this->roles->updateStatus($request,'approved',$reviewer,'SANDBOX TEST DATA - staff/role approval only; no paid license or Hub entitlement.');
                $manifest['accounts'][$key]=['id'=>$id,'role_request_id'=>$request];
                if($key!=='principal')$this->staff->assignTeacher($school,$id,'SPED','SANDBOX TEST DATA','Sandbox Building','Sandbox Room','SANDBOX TEST DATA - '.ucfirst($key),$reviewer);
                $credentials['accounts'][$key]=['email'=>$account['email'],'password'=>$password];
                unset($password);
            }
            foreach(FinanceSandboxPlan::packs() as $key=>$pack)$manifest['packs'][$key]=$this->hub->createSandboxPack($manifest['accounts']['creator']['id'],(int)$admin['id'],$nonce,$pack);
            $this->verify($manifest,true);
            // Save credentials and ownership before commit; a crash can be verified, never reset/adopted blindly.
            $persistPrepared($manifest,$credentials);
            $this->db->commit();return $manifest;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    public function verify(array $m,bool $fresh=false): array {
        FinanceSandboxPlan::manifest($m,(string)$this->db->query('SELECT DATABASE()')->fetchColumn());
        $school=$this->schools->findBySchoolCode(FinanceSandboxPlan::SCHOOL_CODE);
        if(!$school||(int)$school['id']!==$m['school_id']||$school['school_name']!==FinanceSandboxPlan::SCHOOL_NAME||$school['address']!==FinanceSandboxPlan::DATASET.'; nonce='.$m['nonce'])throw new FinanceSandboxSafetyException('School ownership mismatch; no records will be changed.');
        foreach(FinanceSandboxPlan::accounts() as $key=>$spec){
            $u=$this->query('SELECT * FROM users WHERE id=?',[$m['accounts'][$key]['id']])->fetch(PDO::FETCH_ASSOC);
            if(!$u||$u['email']!==$spec['email']||$u['name']!==$spec['name']||$u['role']!==$spec['role']||(int)$u['school_id']!==$m['school_id']||$u['deleted_at']!==null||$u['status']!=='active'||!$u['email_verified'])throw new FinanceSandboxSafetyException('Sandbox account has drifted; it will not be reset.');
            if(!password_get_info($u['password_hash'])['algo'])throw new FinanceSandboxSafetyException('Invalid password hash.');
            $r=$this->query('SELECT * FROM role_requests WHERE id=? AND user_id=?',[$m['accounts'][$key]['role_request_id'],$u['id']])->fetch(PDO::FETCH_ASSOC);
            $docs=$r?json_decode($r['submitted_docs'],true):null;
            $reviewer=$key==='principal'?$m['admin_id']:$m['accounts']['principal']['id'];
            if(!$r||$r['status']!=='approved'||$r['requested_role']!==$spec['role']||(int)$r['reviewed_by']!==$reviewer||($docs['sandbox_nonce']??'')!==$m['nonce']||($docs['sandbox_dataset']??'')!==FinanceSandboxPlan::DATASET)throw new FinanceSandboxSafetyException('Role approval ownership mismatch.');
            if($key!=='principal'){
                $assignment=$this->staff->getByTeacherId($u['id']);
                if(!$assignment||(int)$assignment['school_id']!==$m['school_id']||(int)$assignment['assigned_by']!==$m['accounts']['principal']['id'])throw new FinanceSandboxSafetyException('Teacher school/staff linkage mismatch.');
            }
        }
        foreach(FinanceSandboxPlan::packs() as $key=>$spec){
            $p=$this->query('SELECT * FROM resource_hub_packs WHERE id=?',[$m['packs'][$key]['id']])->fetch(PDO::FETCH_ASSOC);
            if(!$p||(int)$p['created_by']!==$m['accounts']['creator']['id']||$p['title']!==$spec['title']||$p['source_lesson_plan_id']!==null||$p['description']!==FinanceSandboxPlan::DATASET.'; nonce='.$m['nonce'].'; SANDBOX TEST DATA')throw new FinanceSandboxSafetyException('Hub fixture ownership mismatch.');
            $page=$this->query('SELECT id FROM resource_hub_pages WHERE id=? AND pack_id=?',[$m['packs'][$key]['page_id'],$p['id']])->fetchColumn();
            $pricing=$this->query('SELECT * FROM finance_pack_pricing WHERE pack_id=?',[$p['id']])->fetch(PDO::FETCH_ASSOC);
            if(!$page||!$pricing||(int)$pricing['creator_id']!==$m['accounts']['creator']['id'])throw new FinanceSandboxSafetyException('Hub page/pricing fixture is incomplete.');
            if($fresh&&((int)$pricing['proposed_amount']!==$spec['price']||$pricing['review_status']!==$spec['review']||($key==='premium'&&($pricing['approved_amount']!==null||$pricing['reviewed_by']!==null))))throw new FinanceSandboxSafetyException('Fresh premium fixture must await admin approval.');
        }
        $buyer=$m['accounts']['buyer']['id'];
        $rights=(int)$this->query('SELECT COUNT(*) FROM finance_entitlements WHERE buyer_id=? OR school_id=?',[$buyer,$m['school_id']])->fetchColumn();
        $orders=(int)$this->query('SELECT COUNT(*) FROM finance_orders WHERE buyer_id=? OR school_id=?',[$buyer,$m['school_id']])->fetchColumn();
        if($fresh&&($rights!==0||$orders!==0))throw new FinanceSandboxSafetyException('Fresh school and buyer must have no finance orders or entitlements.');
        return ['buyer_or_school_has_finance_history'=>$rights>0||$orders>0];
    }
}
