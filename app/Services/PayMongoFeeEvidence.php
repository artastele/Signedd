<?php
declare(strict_types=1);

/** Only payment resources retrieved with the server's test key can confirm fees. */
final class PayMongoFeeEvidence {
    public static function verify(array $order, string $paymentId, array $payment): array {
        $a=$payment['attributes']??[];
        if (($payment['id']??'')!==$paymentId || ($payment['type']??'')!=='payment' ||
            ($a['livemode']??null)!==false || ($a['status']??'')!=='paid' ||
            ($a['currency']??'')!=='PHP' || !is_int($a['amount']??null) || $a['amount']!==(int)$order['amount']) {
            throw new DomainException('Retrieved payment identity, mode, status or amount mismatch.');
        }
        $fee=$a['fee']??null; $net=$a['net_amount']??null;
        $confirmed=is_int($fee) && is_int($net) && $fee>=0 && $net>=0 && $fee<=$a['amount'] && $fee+$net===$a['amount'];
        // Additional taxes/withholdings/foreign fees need a verified ledger breakdown.
        // Do not silently call gross-minus-net a provider processing fee.
        foreach(['foreign_fee','tax_amount','digital_withholding_vat_amount'] as $field) {
            if (isset($a[$field]) && $a[$field]!==0) $confirmed=false;
        }
        return ['fee'=>$confirmed?$fee:null,'net'=>$confirmed?$net:null,
            'status'=>$confirmed?'VERIFIED':'PENDING_FEES','source'=>'GET /v1/payments/'.$paymentId,
            'payment_id'=>$paymentId,'payload_hash'=>hash('sha256',json_encode($payment,JSON_THROW_ON_ERROR)),
            'provider_updated_at'=>is_int($a['updated_at']??null)?$a['updated_at']:null,
            'balance_transaction_id'=>is_string($a['balance_transaction_id']??null)?$a['balance_transaction_id']:null,
            'reason'=>$confirmed?'fee + net_amount = gross':'Missing or unreconciled provider deduction breakdown'];
    }
    public static function retrieve(array $order, string $paymentId, PayMongoSandbox $provider): array {
        if (!preg_match('/^pay_[A-Za-z0-9]+$/D',$paymentId)) throw new DomainException('Invalid payment identifier.');
        try { $p=$provider->request('GET','/v1/payments/'.$paymentId); }
        catch (RuntimeException|JsonException $e) {
            return ['fee'=>null,'net'=>null,'status'=>'PENDING_FEES','source'=>'GET /v1/payments/'.$paymentId,
                'payment_id'=>$paymentId,'payload_hash'=>null,'provider_updated_at'=>null,'balance_transaction_id'=>null,
                'reason'=>'Individual payment retrieval unavailable; retry reconciliation.'];
        }
        return self::verify($order,$paymentId,$p);
    }
}
