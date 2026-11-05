<?php
declare(strict_types=1);

/** Pure rules; no configuration, connections, clock side effects, or floating point. */
final class FinanceRules {
    public const SCHOOL_PRICE = 1500000;
    public const SUBSCRIPTION_PRICE = 19900;
    public static function split(int $gross, int $fee, string $kind): array {
        if ($gross < 1 || $fee < 0 || $fee > $gross) throw new InvalidArgumentException('Invalid verified amounts.');
        $net = $gross - $fee;
        $pool = $kind === 'institutional' ? 0 : intdiv($net * 70, 100);
        return ['gross'=>$gross,'fee'=>$fee,'net'=>$net,'signed_share'=>$net-$pool,
            'creator_pool'=>$pool,'undistributed'=>$kind === 'subscription' ? $pool : 0,
            'rounding_numerator'=>$kind === 'institutional' ? 0 : ($net*70)%100];
    }
    public static function extend(string $start, int $months): string {
        $d = new DateTimeImmutable($start, new DateTimeZone('UTC'));
        $target = $d->modify('first day of this month')->modify('+'.$months.' months');
        return $target->setDate((int)$target->format('Y'), (int)$target->format('m'),
            min((int)$d->format('d'), (int)$target->format('t')))->format('Y-m-d H:i:s');
    }
    public static function schoolStart(?string $expiry, string $now): string {
        if ($expiry && strtotime($expiry.' UTC') + 30*86400 >= strtotime($now.' UTC')) return $expiry;
        return $now;
    }
    /** Read-only status calculation; legacy schools never acquire a debt/expiry state. */
    public static function schoolAccess(bool $enrolled, ?string $expiry, string $now): array {
        if (!$enrolled) return ['state'=>'LEGACY','expires_at'=>null,'allowed'=>true,'enrolled'=>false];
        if (!$expiry) return ['state'=>'LICENSE_REVIEW','expires_at'=>null,'allowed'=>false,'enrolled'=>true];
        $end=new DateTimeImmutable($expiry,new DateTimeZone('UTC'));
        $clock=new DateTimeImmutable($now,new DateTimeZone('UTC'));
        $state=$end>$clock?'ACTIVE':($clock<=$end->modify('+30 days')?'GRACE':'EXPIRED');
        return ['state'=>$state,'expires_at'=>$expiry,'allowed'=>$state!=='EXPIRED','enrolled'=>true];
    }
    /** Summarize active/queued terms without suggesting access across a suspended-term gap. */
    public static function subscriptionSummary(array $periods, string $now): array {
        usort($periods,function($a,$b){return strcmp($a['starts_at'],$b['starts_at']);});
        $end=null; $next=null; $expired=null;
        foreach ($periods as $p) {
            if (empty($p['ends_at']) || $p['starts_at']>=$p['ends_at']) continue;
            if ($p['ends_at']<=$now) { $expired=max($expired??'', $p['ends_at']); continue; }
            if ($p['starts_at']<=$now || ($end!==null && $p['starts_at']<=$end)) $end=max($end??'', $p['ends_at']);
            elseif ($next===null) $next=$p['starts_at'];
        }
        return ['active'=>$end!==null,'state'=>$end!==null?'ACTIVE':($next!==null?'SCHEDULED':($expired!==null?'EXPIRED':'NONE')),
            'expires_at'=>$end??$expired,'starts_at'=>$next];
    }
    public static function signature(string $body, string $header, string $secret, ?int $now = null): void {
        $parts = [];
        foreach (explode(',', $header) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) !== 2 || isset($parts[$pair[0]])) throw new DomainException('Invalid signature header.');
            $parts[$pair[0]] = $pair[1];
        }
        $t = $parts['t'] ?? '';
        if (!$secret || !ctype_digit($t) || abs(($now ?? time()) - (int)$t) > 300 || !empty($parts['li']) ||
            !preg_match('/^[a-f0-9]{64}$/', $parts['te'] ?? '') ||
            !hash_equals(hash_hmac('sha256', $t.'.'.$body, $secret), $parts['te'])) {
            throw new DomainException('Webhook authentication failed.');
        }
    }
    public static function paidSession(array $order, array $session): array {
        $a = $session['attributes'] ?? [];
        if (($session['id'] ?? '') !== $order['checkout_id'] || ($a['livemode'] ?? null) !== false ||
            ($a['reference_number'] ?? '') !== $order['reference'] ||
            ($a['metadata']['order_reference'] ?? '') !== $order['reference'] ||
            ($a['metadata']['buyer_id'] ?? '') !== (string)$order['buyer_id']) {
            throw new DomainException('Checkout identity or mode mismatch.');
        }
        $paid = array_values(array_filter($a['payments'] ?? [], function ($p) {
            return ($p['attributes']['status'] ?? '') === 'paid';
        }));
        if (count($paid) !== 1) throw new DomainException('Expected exactly one paid payment.');
        $p = $paid[0]; $v = $p['attributes'];
        if (!preg_match('/^pay_[A-Za-z0-9]+$/', $p['id'] ?? '') || ($v['livemode'] ?? null) !== false ||
            ($v['currency'] ?? '') !== 'PHP' || !is_int($v['amount'] ?? null) || $v['amount'] !== (int)$order['amount']) {
            throw new DomainException('Payment amount, currency or mode mismatch.');
        }
        $fee = $v['fee'] ?? null; $net = $v['net_amount'] ?? null;
        if (!is_int($fee) || !is_int($net) || $fee < 0 || $net < 0 || $fee+$net !== $v['amount']) $fee = null;
        return ['id'=>$p['id'],'fee'=>$fee,'paid_at'=>isset($v['paid_at']) && is_int($v['paid_at']) ? gmdate('Y-m-d H:i:s',$v['paid_at']) : null];
    }
}
