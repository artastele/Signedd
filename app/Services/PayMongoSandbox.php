<?php
declare(strict_types=1);

/** Server-only client. Never logs request/response bodies or authentication headers. */
class PayMongoSandbox {
    private string $key;
    public function __construct(?string $key = null) {
        $this->key = $key ?? (string)getenv('PAYMONGO_SECRET_KEY');
        if (!preg_match('/^sk_test_[A-Za-z0-9]+$/', $this->key)) throw new RuntimeException('PayMongo test credentials are required.');
        if (!extension_loaded('curl')) throw new RuntimeException('PHP cURL is required.');
    }
    public function request(string $method, string $path, ?array $attributes = null): array {
        if (!preg_match('#^/v[12]/(checkout_sessions|payments|refunds)(/[A-Za-z0-9_]+)?(/expire)?$#', $path)) throw new LogicException('Unsupported provider path.');
        $ch = curl_init('https://api.paymongo.com'.$path);
        $options = [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,
            CURLOPT_USERPWD=>$this->key.':',CURLOPT_HTTPAUTH=>CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'],
            CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_FOLLOWLOCATION=>false,
            CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2];
        if ($attributes !== null) $options[CURLOPT_POSTFIELDS] = json_encode(['data'=>['attributes'=>$attributes]], JSON_THROW_ON_ERROR);
        curl_setopt_array($ch,$options); $body=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        if ($body === false || $status < 200 || $status >= 300) throw new RuntimeException('Provider request uncertain or rejected; reconcile before retrying.');
        $data=json_decode($body,true,512,JSON_THROW_ON_ERROR)['data'] ?? null;
        if (!is_array($data) || ($data['attributes']['livemode'] ?? null) !== false) throw new DomainException('Provider response is not test mode.');
        return $data;
    }
    public function createCheckout(array $order): array {
        $base=rtrim((string)getenv('SIGNED_FINANCE_BASE_URL'),'/');
        $url=parse_url($base);
        if (!$url || !in_array($url['scheme'] ?? '', ['http','https'],true) || empty($url['host']) || isset($url['user']) || isset($url['query']) || isset($url['fragment'])) throw new RuntimeException('Configure the absolute finance base URL.');
        $methods=array_values(array_filter(array_map('trim',explode(',',getenv('PAYMONGO_PAYMENT_METHODS') ?: 'card,gcash'))));
        if (!$methods || array_diff($methods,['card','gcash','paymaya','qrph','dob','dob_ubp'])) throw new RuntimeException('Unsupported payment method configuration.');
        return $this->request('POST','/v2/checkout_sessions',[
            'line_items'=>[['name'=>$order['item_name'],'amount'=>(int)$order['amount'],'currency'=>'PHP','quantity'=>1]],
            'reference_number'=>$order['reference'],'metadata'=>['order_reference'=>$order['reference'],'buyer_id'=>(string)$order['buyer_id']],
            'payment_method_types'=>$methods,'pass_on_fees'=>false,'send_email_receipt'=>false,
            'success_url'=>$base.'/finance/payment/'.$order['id'].'?payment_return=success',
            'cancel_url'=>$base.'/finance/payment/'.$order['id'].'?payment_return=cancelled']);
    }
}
