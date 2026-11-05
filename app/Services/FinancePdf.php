<?php
declare(strict_types=1);
/** Small dependency-free text PDF. All content is literal, escaped, and length limited. */
final class FinancePdf {
    public static function render(array $order, bool $confirmation): string {
        if ($confirmation && empty($order['payment_id'])) throw new DomainException('Verified payment required.');
        $lines=['SignED - TEST MODE / SANDBOX', $confirmation?'Payment Confirmation':'Billing Document / Payment Request',
            'System-generated document; not a BIR tax invoice or official tax receipt.',
            'Reference: '.$order['reference'],'Buyer: '.$order['buyer_name'],'Item: '.$order['item_name'],
            'Amount: PHP '.number_format((int)$order['amount']/100,2,'.',','),
            'Created (UTC): '.$order['created_at'],'Financial status: '.$order['status'],
            'Access / approval status: '.$order['access_status']];
        if ($confirmation) { $lines[]='Verified payment: '.$order['payment_id']; $lines[]='Paid (UTC): '.$order['paid_at']; }
        $stream="BT /F1 11 Tf 40 800 Td 16 TL\n";
        foreach($lines as $line) {
            $line=preg_replace('/[^\x20-\x7E]/','?',(string)$line);
            foreach(str_split($line,90) as $chunk) $stream.='('.str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$chunk).") Tj T*\n";
        }
        $stream.='ET';
        $objects=['<< /Type /Catalog /Pages 2 0 R >>','<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',"<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream"];
        $pdf="%PDF-1.4\n"; $offsets=[0];
        foreach($objects as $i=>$object) { $offsets[]=strlen($pdf); $pdf.=($i+1)." 0 obj\n".$object."\nendobj\n"; }
        $xref=strlen($pdf); $pdf.="xref\n0 6\n0000000000 65535 f \n";
        foreach(array_slice($offsets,1) as $offset) $pdf.=sprintf("%010d 00000 n \n",$offset);
        return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF\n";
    }
}
