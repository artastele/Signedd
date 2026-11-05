<?php
$confirmedGross=(int)($totals['gross']??0);
$confirmedFees=(int)($totals['known_fees']??0);
$confirmedNet=(int)($totals['verified_net']??0);
$signedRevenue=(int)($totals['signed_share']??0)+(int)($totals['signed_adjustments']??0);
$creatorShare=(int)($totals['creator_liability']??0)+(int)($totals['creator_adjustments']??0);
?>
<section class="finance-section-heading"><div><span class="finance-kicker">System Admin only</span><h2 class="h4 mb-1">Revenue overview</h2><p class="finance-muted mb-0">Amounts below use payments with verified provider fees. Refund adjustments are shown in the final shares.</p></div></section>
<div class="finance-metric-grid mb-3">
<div class="finance-metric"><span>Confirmed gross payments</span><strong><?= $money($confirmedGross) ?></strong><small><?= (int)($totals['verified_payments']??0) ?> fee-verified payment<?= (int)($totals['verified_payments']??0)===1?'':'s' ?></small></div>
<div class="finance-metric"><span>Verified PayMongo fees</span><strong><?= $money($confirmedFees) ?></strong><small>Only fees confirmed from provider evidence</small></div>
<div class="finance-metric"><span>Net after fees</span><strong><?= $money($confirmedNet) ?></strong><small>Before refund adjustments</small></div>
<div class="finance-metric finance-metric-primary"><span>SignED share</span><strong><?= $money($signedRevenue) ?></strong><small>After confirmed refund adjustments</small></div>
</div>
<div class="finance-metric-grid finance-metric-grid-secondary">
<div class="finance-metric"><span>Creator share allocated</span><strong><?= $money($creatorShare) ?></strong><small>After confirmed refund adjustments</small></div>
<div class="finance-metric"><span>Outstanding creator earnings</span><strong><?= $money((int)($totals['outstanding_earnings']??0)) ?></strong><small>Includes unpaid adjustments</small></div>
<div class="finance-metric"><span>Recorded creator transfers</span><strong><?= $money((int)($totals['payouts']??0)) ?></strong><small>Completed manual transfers only</small></div>
<div class="finance-metric"><span>Confirmed refunds</span><strong><?= $money((int)($totals['refunds']??0)) ?></strong><small>Provider-confirmed refunds</small></div>
</div>
<?php if((int)($totals['pending_fee_orders']??0)>0 || (int)($totals['fee_review_orders']??0)>0 || (int)($totals['refund_reviews']??0)>0): ?><div class="finance-status-callout mt-3"><strong>Reconciliation needed</strong><span><?= (int)($totals['pending_fee_orders']??0) ?> payment<?= (int)($totals['pending_fee_orders']??0)===1?'':'s' ?> awaiting fee data · <?= (int)($totals['fee_review_orders']??0) ?> fee review<?= (int)($totals['fee_review_orders']??0)===1?'':'s' ?> · <?= (int)($totals['refund_reviews']??0) ?> refund review<?= (int)($totals['refund_reviews']??0)===1?'':'s' ?>. Unverified amounts are excluded from the cards.</span></div><?php endif ?>
