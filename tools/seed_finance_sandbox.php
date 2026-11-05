<?php
declare(strict_types=1);
// PREPARED ONLY. No writes without explicit future --apply and migration/backup prerequisites.
// Never invoke the legacy demo seeders or change existing account credentials.
if(PHP_SAPI!=='cli'){http_response_code(403);exit('CLI only.');}
require_once __DIR__.'/fixtures/FinanceSandboxPlan.php';

function sandboxPrivateJson(string $path): array {
    if(!is_file($path)||is_link($path)||(fileperms($path)&0077)!==0||filesize($path)>1048576)throw new FinanceSandboxSafetyException('Expected an owner-only private JSON file.');
    $data=json_decode((string)file_get_contents($path),true,64,JSON_THROW_ON_ERROR);
    if(!is_array($data))throw new FinanceSandboxSafetyException('Invalid private JSON file.');return $data;
}
function sandboxWriteJson(string $path,array $value,bool $replace=false): void {
    if(is_link($path)||(!$replace&&file_exists($path)))throw new FinanceSandboxSafetyException('Refusing to overwrite an existing private file.');
    $temp=tempnam(dirname($path),'.sandbox-');if($temp===false)throw new FinanceSandboxSafetyException('Cannot write private dataset state.');
    try{chmod($temp,0600);$json=json_encode($value,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
        if(file_put_contents($temp,$json,LOCK_EX)!==strlen($json)||!rename($temp,$path))throw new FinanceSandboxSafetyException('Cannot persist private dataset state.');
    }finally{if(is_file($temp))unlink($temp);}
}
$options=getopt('',['apply','migration-approved','backup-report:','state-dir:','admin-email:','help']);
if(!isset($options['apply'])){
    echo "Prepared SignED sandbox fixture seeder. No database connection or writes performed.\n";
    echo "Future use AFTER migration approval/application and verified backup:\n";
    echo "php tools/seed_finance_sandbox.php --apply --migration-approved --backup-report=/private/path/verification.json --state-dir=/private/path/finance-sandbox --admin-email=admin@spedlms.local\n";
    exit(0);
}
$lock=null;
try{
    if(PHP_VERSION_ID<80300)throw new FinanceSandboxSafetyException('Use the reviewed MAMP PHP 8.3 runtime.');
    if(!isset($options['migration-approved'],$options['backup-report'],$options['state-dir']))throw new FinanceSandboxSafetyException('Explicit migration approval, verified backup report, and private state directory are required.');
    foreach(array_slice($argv,1) as $argument)if(!preg_match('/^--(?:apply|migration-approved|help|(?:backup-report|state-dir|admin-email)(?:=.*)?)$/D',$argument)&&strpos($argument,'--')===0)throw new FinanceSandboxSafetyException('Unknown option; cleanup/reset/password arguments are not supported.');
    require_once __DIR__.'/../config/env.php'; // Configuration only; never start HTTP/session/application bootstrap.
    $env=[];foreach(['APP_ENV','DB_HOST','DB_PORT','DB_NAME','SIGNED_FINANCE_ENABLED','SIGNED_SCHEMA_MAINTENANCE','PAYMONGO_SECRET_KEY'] as $key)$env[$key]=env($key,$key==='SIGNED_FINANCE_ENABLED'?'false':'');
    FinanceSandboxPlan::environment($env);
    $webRoots=[dirname(__DIR__),'/Applications/MAMP/htdocs'];
    $state=FinanceSandboxPlan::privateDirectory((string)$options['state-dir'],$webRoots);
    $reportPath=realpath((string)$options['backup-report']);
    if(!$reportPath)throw new FinanceSandboxSafetyException('Verified backup report is missing.');
    foreach($webRoots as $root)if(strpos($reportPath,realpath($root).'/')===0)throw new FinanceSandboxSafetyException('Backup reports must remain outside web roots.');
    $backup=sandboxPrivateJson($reportPath);
    FinanceSandboxPlan::backup($backup,$env['DB_NAME'],hash_file('sha256',__DIR__.'/../database/migrations/20261103_sandbox_finance.sql'));
    if(!is_file($backup['backup_file'])||is_link($backup['backup_file'])||filesize($backup['backup_file'])===0||!hash_equals($backup['backup_sha256'],hash_file('sha256',$backup['backup_file'])))throw new FinanceSandboxSafetyException('The verified SQL backup is missing, empty or changed.');
    if(is_link($state.'/dataset.lock'))throw new FinanceSandboxSafetyException('Invalid private dataset lock.');
    $lock=fopen($state.'/dataset.lock','c');if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))throw new FinanceSandboxSafetyException('Another sandbox preparation is running.');chmod($state.'/dataset.lock',0600);
    $manifestFile=$state.'/dataset.json';$credentialsFile=$state.'/credentials.json';
    $existing=file_exists($manifestFile)?sandboxPrivateJson($manifestFile):null;
    if(!$existing&&file_exists($credentialsFile))throw new FinanceSandboxSafetyException('Orphan prepared credentials require manual ownership/rollback review; they will not be overwritten.');
    if($existing){
        FinanceSandboxPlan::manifest($existing,$env['DB_NAME']);$stored=sandboxPrivateJson($credentialsFile);
        if(($stored['dataset']??'')!==FinanceSandboxPlan::DATASET||($stored['nonce']??'')!==$existing['nonce'])throw new FinanceSandboxSafetyException('Private credential ownership mismatch.');
    }
    require_once __DIR__.'/fixtures/FinanceSandboxDataset.php';
    $db=Database::getInstance()->getConnection();
    $dataset=new FinanceSandboxDataset($db);$dataset->prerequisites($env['DB_NAME'],$backup);
    $admin=$dataset->admin((string)($options['admin-email']??'admin@spedlms.local'));
    if($existing){
        if((int)$admin['id']!==$existing['admin_id'])throw new FinanceSandboxSafetyException('The manifest refers to a different reused admin; no ownership reassignment is permitted.');
        $history=$dataset->verify($existing);
        $manifest=$existing;$manifest['state']='COMMITTED';
        if($existing['state']!=='COMMITTED')sandboxWriteJson($manifestFile,$manifest,true);
        $result='EXISTING_DATASET_VERIFIED_NO_RECORD_CHANGES';
    }else{
        $manifest=$dataset->create($admin,bin2hex(random_bytes(16)),function(array $prepared,array $credentials)use($manifestFile,$credentialsFile):void{
            sandboxWriteJson($credentialsFile,$credentials);sandboxWriteJson($manifestFile,$prepared);
        });
        $manifest['state']='COMMITTED';sandboxWriteJson($manifestFile,$manifest,true);
        $history=['buyer_or_school_has_finance_history'=>false];$result='NEW_SANDBOX_DATASET_CREATED';
    }
    // Only synthetic identities/roles and file locations; never print passwords, hashes, keys or credential contents.
    echo json_encode(['result'=>$result,'school'=>FinanceSandboxPlan::SCHOOL_NAME,'school_code'=>FinanceSandboxPlan::SCHOOL_CODE,
        'accounts'=>FinanceSandboxPlan::accounts(),'system_admin'=>'Existing active admin reused without modification',
        'packs'=>['free'=>'Approved free fixture','premium'=>'PHP 79 proposal; pending admin approval'],
        'finance_enabled'=>$env['SIGNED_FINANCE_ENABLED']==='true','private_credentials_file'=>$credentialsFile,'ownership_manifest'=>$manifestFile,
        'financial_history_preserved_on_rerun'=>$history['buyer_or_school_has_finance_history']],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
}catch(Throwable $e){
    // Only our literal guard messages are safe to report; never emit provider/PDO/model traces or parameters.
    if($e instanceof FinanceSandboxSafetyException)fwrite(STDERR,"Safety check: ".$e->getMessage()."\n");
    fwrite(STDERR,"Sandbox preparation stopped. No existing account passwords, roles, or educational records were reset. Review prerequisites, collisions, schema and the private ownership state; do not run another seeder or remove records blindly.\n");
    exit(1);
}finally{if(is_resource($lock)){flock($lock,LOCK_UN);fclose($lock);}}
