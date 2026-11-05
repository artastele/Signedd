<?php
declare(strict_types=1);
final class FinanceSandboxSafetyException extends RuntimeException {}

/** Pure specification and guards. Loading this file never opens a database. */
final class FinanceSandboxPlan {
    public const DATASET = 'signed-finance-sandbox-v1';
    public const SCHOOL_CODE = 'SIGNED-FINANCE-SANDBOX-V1';
    public const SCHOOL_NAME = 'SANDBOX TEST DATA - SignED Finance Academy';
    public static function accounts(): array {
        return [
            'principal'=>['name'=>'SANDBOX TEST DATA - Principal Nova','email'=>'principal.finance@example.test','role'=>'principal'],
            'creator'=>['name'=>'SANDBOX TEST DATA - Teacher Aster (Creator)','email'=>'creator.finance@example.test','role'=>'sped_teacher'],
            'buyer'=>['name'=>'SANDBOX TEST DATA - Teacher Beryl (Buyer)','email'=>'buyer.finance@example.test','role'=>'sped_teacher'],
        ];
    }
    public static function packs(): array {
        return [
            'free'=>['title'=>'SANDBOX TEST DATA - Free Addition Practice','price'=>0,'review'=>'APPROVED',
                'content'=>'<p>SANDBOX TEST DATA</p><p>Count two objects, then three more. Two plus three equals five.</p>'],
            'premium'=>['title'=>'SANDBOX TEST DATA - Premium Addition Pack','price'=>7900,'review'=>'PENDING',
                'content'=>'<p>SANDBOX TEST DATA</p><p>Add four and five. Four plus five equals nine. Practise with counters.</p>'],
        ];
    }
    public static function environment(array $env): void {
        if (!in_array(strtolower((string)($env['APP_ENV']??'')),['development','local','testing'],true) ||
            !in_array($env['DB_HOST']??'', ['127.0.0.1','localhost','::1'],true) ||
            !preg_match('/^[A-Za-z0-9_]+$/D',(string)($env['DB_NAME']??'')) ||
            !ctype_digit((string)($env['DB_PORT']??''))) throw new FinanceSandboxSafetyException('Confirmed local development database configuration is required.');
        if (!in_array($env['SIGNED_FINANCE_ENABLED']??'', ['false','true'],true)) throw new FinanceSandboxSafetyException('An explicit local Finance flag is required for sandbox fixtures.');
        if (($env['SIGNED_SCHEMA_MAINTENANCE']??'')==='approved') throw new FinanceSandboxSafetyException('Legacy schema maintenance must remain disabled.');
        if (strpos((string)($env['PAYMONGO_SECRET_KEY']??''),'sk_live_')===0) throw new FinanceSandboxSafetyException('Live provider configuration is forbidden.');
    }
    public static function backup(array $report, string $database, string $migrationHash): void {
        if (($report['result']??'')!=='VERIFIED' || ($report['local_database']??'')!==$database ||
            ($report['restore_exit_code']??-1)!==0 || !($report['source_after_dump_matches']??false) ||
            !(($report['restored_sql_semantically_matches']??false)||($report['full_restored_dump_matches']??false)) ||
            ($report['finance_migration_sha256']??'')!==$migrationHash || empty($report['backup_file']) || empty($report['backup_sha256']) ||
            empty($report['local_server']['port']) || empty($report['local_server']['datadir'])) {
            throw new FinanceSandboxSafetyException('A verified backup/restore report for this database and exact migration is required.');
        }
    }
    public static function manifest(array $manifest, string $database): void {
        if (($manifest['dataset']??'')!==self::DATASET || ($manifest['database']??'')!==$database ||
            !preg_match('/^[a-f0-9]{32}$/D',(string)($manifest['nonce']??'')) ||
            !in_array($manifest['state']??'',['PREPARED','COMMITTED'],true)) throw new FinanceSandboxSafetyException('Dataset ownership manifest is invalid; nothing will be adopted or reset.');
        $ids=[$manifest['school_id']??0,$manifest['admin_id']??0];
        foreach(array_keys(self::accounts()) as $key) array_push($ids,$manifest['accounts'][$key]['id']??0,$manifest['accounts'][$key]['role_request_id']??0);
        foreach(array_keys(self::packs()) as $key) array_push($ids,$manifest['packs'][$key]['id']??0,$manifest['packs'][$key]['page_id']??0);
        foreach($ids as $id) if(!is_int($id)||$id<1) throw new FinanceSandboxSafetyException('Manifest contains invalid record IDs.');
        $users=array_column($manifest['accounts'],'id');
        if(count($users)!==3 || count(array_unique($users))!==3 || in_array($manifest['admin_id'],$users,true)) throw new FinanceSandboxSafetyException('Sandbox accounts must be distinct and must exclude the reused admin.');
    }
    public static function privateDirectory(string $directory, array $webRoots): string {
        if($directory==='' || $directory[0]!=='/' || is_link($directory)) throw new FinanceSandboxSafetyException('An absolute private state directory is required.');
        $resolved=realpath($directory);
        if(!$resolved || !is_dir($resolved) || (fileperms($resolved)&0077)!==0) throw new FinanceSandboxSafetyException('Create the private state directory with mode 0700 first.');
        foreach($webRoots as $root){$root=realpath($root);if($root&&($resolved===$root||strpos($resolved,$root.'/')===0))throw new FinanceSandboxSafetyException('State and credentials must be outside the project and all web roots.');}
        return $resolved;
    }
}
