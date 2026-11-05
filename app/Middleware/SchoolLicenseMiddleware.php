<?php
declare(strict_types=1);
require_once __DIR__.'/../Services/FinanceService.php';
/** Licensing gates new-workflow payment-required and enrolled schools while Finance is enabled. */
final class SchoolLicenseMiddleware {
    private static function paymentSetupPath(string $path): bool {
        return (bool)preg_match('#^/(?:$|login(?:/|$)|logout(?:/|$)|register(?:/|$)|auth(?:/|$)|finance(?:/|$)|role/(?:select|select-parent|submit-staff)(?:/|$)|profile(?:/|$)|notifications(?:/|$)|api(?:/|$)|api-[^/]+\.php$|services(?:/|$))#',$path);
    }

    /** Disabled Finance and setup/payment routes return before any license lookup. */
    public static function decision(string $path, int $userId, ?FinanceService $finance=null): array {
        if (!FinanceService::enabled()) return ['allowed'=>true,'state'=>'FINANCE_DISABLED'];
        if (self::paymentSetupPath($path)) return ['allowed'=>true,'state'=>'PAYMENT_SETUP'];
        if ($userId<1) return ['allowed'=>true,'state'=>'AUTH_REQUIRED']; // Existing route authorization handles login.
        $finance=$finance??new FinanceService(Database::getInstance()->getConnection());
        $user=$finance->user($userId); // Active account and role come from the server, not session role claims.
        if ($user['role']==='admin') return ['allowed'=>true,'state'=>'ADMIN'];
        if (!$user['school_id']) return ['allowed'=>true,'state'=>'NO_SCHOOL'];
        $status=$finance->schoolStatus((int)$user['school_id']);
        // A newly approved school receives no operational modules before verified payment.
        // Faculty applications remain available through the explicit setup-path allowlist above.
        if (($status['state']??'')==='PAYMENT_REQUIRED') return $status+['role'=>$user['role']];
        if (preg_match('#^/(?:iep/implementation|teacher/resource-hub|learning|teacher-fsl(?:-training)?)(?:/|$)#',$path)) return $status;
        return ['allowed'=>true,'state'=>'OUTSIDE_LICENSE_SCOPE'];
    }
    public static function check(string $path, ?FinanceService $finance=null): void {
        $decision=[];
        try {
            $decision=self::decision($path,(int)($_SESSION['user_id']??0),$finance);
            if ($decision['allowed']) return;
            http_response_code(403);
            $message=($decision['state']??'')==='PAYMENT_REQUIRED'
                ? 'Your school registration is approved, but operational modules remain closed until the annual institutional license payment is verified.'
                : 'Your school’s institutional license needs renewal or review. Educational records are preserved.';
        } catch (DomainException $e) {
            http_response_code(403); $message='Access denied. Please sign in with an active account.';
        } catch (Throwable $e) {
            http_response_code(503); $message='School license verification is temporarily unavailable.';
        }
        header('Content-Type: text/html; charset=utf-8'); header('Cache-Control: private, no-store');
        // A 403 with a link, never an automatic redirect that can trap a renewal/payment request.
        echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');
        if (($decision['state']??'')!=='PAYMENT_REQUIRED' || ($decision['role']??'')==='principal') {
            echo ' <a href="'.htmlspecialchars((defined('BASE_PATH')?BASE_PATH:'').'/finance',ENT_QUOTES,'UTF-8').'">Billing and access</a>';
        } elseif (($decision['state']??'')==='PAYMENT_REQUIRED') {
            echo ' Please contact your Principal to complete the school license payment.';
        }
        exit;
    }
}
