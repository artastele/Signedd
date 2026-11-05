<?php
// View-only context. Disabled Finance never creates a database connection or inspects finance tables.
require_once __DIR__.'/../../../Services/FinanceService.php';
if (!isset($financeContext)) {
    $financeContext=['enabled'=>false,'school'=>null,'subscription'=>null,'pending_order'=>null];
    if (FinanceService::enabled() && !empty($_SESSION['user_id'])) {
        try { $financeContext=(new FinanceService(Database::getInstance()->getConnection()))->customerContext((int)$_SESSION['user_id']); }
        catch (Throwable $e) { $financeContext['unavailable']=true; }
    }
}
$_SESSION['finance_csrf']=$_SESSION['finance_csrf']??bin2hex(random_bytes(32));
