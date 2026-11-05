<?php
declare(strict_types=1);
// Pure specification/guard checks only. Does NOT load or execute the seeder, models or database.
require_once __DIR__.'/../tools/fixtures/FinanceSandboxPlan.php';
$checks=0;
function sandboxCheck(bool $condition,string $message):void{global $checks;++$checks;if(!$condition)throw new RuntimeException($message);}
function sandboxReject(callable $fn,string $message):void{try{$fn();}catch(FinanceSandboxSafetyException $e){sandboxCheck(true,$message);return;}sandboxCheck(false,$message);}
$accounts=FinanceSandboxPlan::accounts();$permissions=require __DIR__.'/../config/permissions.php';
sandboxCheck(count($accounts)===3,'Exactly three new identities');
sandboxCheck(array_column($accounts,'role')===['principal','sped_teacher','sped_teacher'],'Native principal/two SPED Teacher roles');
sandboxCheck(count(array_unique(array_column($accounts,'email')))===3,'Distinct sandbox emails');
foreach($accounts as $a){
    sandboxCheck(substr($a['email'],-13)==='@example.test','Reserved synthetic email domain');
    sandboxCheck(strpos($a['name'],'SANDBOX TEST DATA')===0,'Clearly marked synthetic identity');
    sandboxCheck(!isset($a['password'])&&!isset($a['password_hash']),'No credentials embedded in specification or report');
    sandboxCheck($a['role']!=='admin'&&!in_array('*',$permissions[$a['role']],true),'No new administrator or unrestricted role');
}
sandboxCheck(in_array('staff.approve',$permissions['principal'],true),'Principal retains native staff approval');
sandboxCheck(in_array('iep.implement',$permissions['sped_teacher'],true),'Teachers retain existing Hub permission');
sandboxCheck(in_array('*',$permissions['admin'],true),'Existing System Admin can approve pricing/licenses');
$packs=FinanceSandboxPlan::packs();
sandboxCheck(count($packs)===2&&$packs['free']['price']===0&&$packs['free']['review']==='APPROVED','One approved free pack');
sandboxCheck($packs['premium']['price']===7900&&$packs['premium']['review']==='PENDING','Premium price remains a proposal, not an entitlement');
$env=['APP_ENV'=>'development','DB_HOST'=>'127.0.0.1','DB_PORT'=>'8889','DB_NAME'=>'sped_lms','SIGNED_FINANCE_ENABLED'=>'false'];
FinanceSandboxPlan::environment($env);sandboxCheck(true,'Explicit local Finance configuration accepted');$enabled=$env;$enabled['SIGNED_FINANCE_ENABLED']='true';FinanceSandboxPlan::environment($enabled);sandboxCheck(true,'Enabled local sandbox after migration accepted');
foreach([['APP_ENV','production'],['DB_HOST','example.com'],['DB_NAME','db; DROP DATABASE x'],['DB_PORT','not-a-port'],['SIGNED_FINANCE_ENABLED','invalid'],['SIGNED_SCHEMA_MAINTENANCE','approved'],['PAYMONGO_SECRET_KEY','sk_live_fixture']] as [$key,$value]){$bad=$env;$bad[$key]=$value;sandboxReject(fn()=>FinanceSandboxPlan::environment($bad),'Unsafe environment rejected');}
$hash=str_repeat('a',64);$report=['result'=>'VERIFIED','local_database'=>'sped_lms','restore_exit_code'=>0,'source_after_dump_matches'=>true,'restored_sql_semantically_matches'=>true,'finance_migration_sha256'=>$hash,'backup_file'=>'/private/fixture.sql','backup_sha256'=>$hash,'local_server'=>['port'=>8889,'datadir'=>'/Applications/MAMP/db/mysql80/']];
FinanceSandboxPlan::backup($report,'sped_lms',$hash);sandboxCheck(true,'Matching verified restore/migration evidence accepted');
foreach([['result','FAILED'],['local_database','production'],['restore_exit_code',1],['source_after_dump_matches',false],['restored_sql_semantically_matches',false],['finance_migration_sha256',str_repeat('b',64)],['backup_file','']] as [$key,$value]){$bad=$report;$bad[$key]=$value;sandboxReject(fn()=>FinanceSandboxPlan::backup($bad,'sped_lms',$hash),'Unverified/mismatched backup evidence rejected');}
$m=['dataset'=>FinanceSandboxPlan::DATASET,'database'=>'sped_lms','state'=>'COMMITTED','nonce'=>str_repeat('c',32),'school_id'=>200,'admin_id'=>1,'accounts'=>['principal'=>['id'=>201,'role_request_id'=>301],'creator'=>['id'=>202,'role_request_id'=>302],'buyer'=>['id'=>203,'role_request_id'=>303]],'packs'=>['free'=>['id'=>401,'page_id'=>501],'premium'=>['id'=>402,'page_id'=>502]]];
FinanceSandboxPlan::manifest($m,'sped_lms');sandboxCheck(true,'Exact owned fixture identity manifest accepted');
foreach([['dataset','other'],['database','other'],['nonce',''],['state','RESET'],['school_id',0]] as [$key,$value]){$bad=$m;$bad[$key]=$value;sandboxReject(fn()=>FinanceSandboxPlan::manifest($bad,'sped_lms'),'Manifest ownership mismatch rejected');}
$bad=$m;$bad['accounts']['creator']['id']=1;sandboxReject(fn()=>FinanceSandboxPlan::manifest($bad,'sped_lms'),'Reused admin cannot become a seeded identity');
$bad=$m;$bad['accounts']['buyer']['id']=202;sandboxReject(fn()=>FinanceSandboxPlan::manifest($bad,'sped_lms'),'Duplicate user ownership rejected');
echo "PASS: $checks sandbox specification/safety checks; seeder not loaded or executed; no database or network used.\n";
