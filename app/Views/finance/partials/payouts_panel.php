<section class="finance-section-heading"><div><span class="finance-kicker"><?= $admin?'System Admin':'Resource creator' ?></span><h2 class="h4 mb-1"><?= $admin?'Creator payments':'Payments I received' ?></h2><p class="finance-muted mb-0"><?= $admin?'Use this only after SignED has actually sent the creator’s money through GCash, Maya, or a bank. Recording here does not send money.':'This page shows the creator earnings that SignED has already sent to you.' ?></p></div></section>
<?php if($admin && $outstandingEarnings):
$creators=[];foreach($outstandingEarnings as $earning)$creators[(int)$earning['creator_id']]=$earning['creator_name']; ?>
<div class="finance-status-callout mb-3"><strong>Simple steps:</strong><span>1. Send the money outside SignED.</span><span>2. Copy the reference number from the transfer receipt.</span><span>3. Record it below.</span></div>
<section class="finance-panel mb-3"><h3 class="h5 mb-1">Record money already sent</h3><p class="small finance-muted mb-3">Do not submit this form before the GCash, Maya, or bank transfer succeeds.</p>
<?php $formStart('payout'); ?>
<div class="finance-form-grid">
<label class="finance-form-field"><span>Creator</span><select name="creator_id" class="form-select" required><option value="">Select creator</option><?php foreach($creators as $id=>$name): ?><option value="<?= $id ?>"><?= $esc($name) ?></option><?php endforeach ?></select></label>
<fieldset class="finance-form-field finance-form-wide"><legend>Money being paid</legend><div class="finance-earning-options"><?php foreach($outstandingEarnings as $earning): ?><label data-creator="<?= (int)$earning['creator_id'] ?>"><input type="checkbox" name="earning_ids[]" value="<?= (int)$earning['id'] ?>"> <span><?= $esc($earning['creator_name']) ?> · <?= $money($earning['amount']) ?> · <?= $esc($basisLabel($earning['basis'])) ?></span></label><?php endforeach ?></div><small>Tick the creator earnings included in this one transfer.</small></fieldset>
<label class="finance-form-field"><span>Amount actually transferred (₱)</span><input class="form-control" name="amount" inputmode="decimal" placeholder="0.00" required></label>
<label class="finance-form-field"><span>Transfer method</span><input class="form-control" name="method" placeholder="Bank transfer, e-wallet…" maxlength="100" required></label>
<label class="finance-form-field"><span>Receipt / transaction reference</span><input class="form-control" name="reference" placeholder="Copy from the GCash, Maya, or bank receipt" maxlength="100" required><small>Where to find it: open the successful transfer in your app’s transaction history and copy its Reference No., Transaction ID, or Trace No.</small></label>
<label class="finance-form-field"><span>Date and time transferred</span><input class="form-control" name="transferred_at" type="datetime-local" required><small>Enter Manila local time.</small></label>
</div>
<label class="finance-confirm"><input type="checkbox" name="transfer_confirmed" value="yes" required> I confirm that the creator already received this money.</label>
<div class="finance-form-actions"><button class="btn btn-primary" type="submit">Save creator payment record</button></div></form></section>
<script>
const payoutCreator = document.querySelector('select[name="creator_id"]');
payoutCreator?.addEventListener('change', () => {
    document.querySelectorAll('.finance-earning-options label[data-creator]').forEach(label => {
        const matches = label.dataset.creator === payoutCreator.value;
        label.hidden = !!payoutCreator.value && !matches;
        const checkbox = label.querySelector('input[type="checkbox"]');
        if (!matches && checkbox) checkbox.checked = false;
    });
});
</script>
<?php elseif($admin): ?><div class="finance-status-callout mb-3">No outstanding verified creator earnings are available to transfer.</div><?php endif ?>
<?php if(!$payouts): ?><section class="finance-empty-state"><div class="finance-empty-icon"><i class="ph-bold ph-hand-coins" aria-hidden="true"></i></div><div><h3 class="h5 mb-1">No creator payments recorded</h3><p class="finance-muted mb-0">Money sent to creators will appear here.</p></div></section>
<?php else: ?><section class="finance-panel"><h3 class="h5 mb-3">Creator payment history</h3><div class="finance-record-list"><?php foreach($payouts as $p): ?><article class="finance-record"><div><strong><?= $money($p['amount']) ?></strong><span class="finance-muted small d-block"><?= $esc($p['method']) ?> · Receipt/reference: <?= $esc($p['reference']) ?></span></div><div class="small finance-muted"><?= $esc($p['transferred_at']) ?> UTC<br><?= $esc(ucfirst(strtolower((string)$p['status']))) ?></div></article><?php endforeach ?></div></section><?php endif ?>
