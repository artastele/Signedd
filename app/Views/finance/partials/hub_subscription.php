<?php require __DIR__.'/context.php'; ?>
<section class="finance-panel mb-3" aria-label="Premium Resource Hub"><div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
<div><span class="finance-kicker">Explore Premium · Optional teacher plan</span><h2 class="h5 mb-1">Premium Resource Hub · ₱199/month</h2>
<?php $subscription=$financeContext['subscription']??null; $pendingSubscription=$financeContext['pending_order']??null; ?>
<?php if(!empty($subscription['active'])): ?><p class="small mb-0">Active · Expires <?= htmlspecialchars($subscription['expires_at'],ENT_QUOTES,'UTF-8') ?> UTC. Eligible premium packs are included.</p>
<?php elseif(($subscription['state']??'')==='SCHEDULED'): ?><p class="small mb-0">Your next term starts <?= htmlspecialchars($subscription['starts_at'],ENT_QUOTES,'UTF-8') ?> UTC.</p>
<?php elseif(($subscription['state']??'')==='EXPIRED'): ?><p class="small mb-0">Subscription expired <?= htmlspecialchars($subscription['expires_at'],ENT_QUOTES,'UTF-8') ?> UTC. Individually purchased packs remain owned.</p>
<?php else: ?><p class="small text-muted mb-0">All eligible premium packs · Manual monthly renewal · No automatic charges.</p><?php endif ?>
<?php if(empty($financeContext['enabled'])): ?><p class="small text-muted mb-0">Payments are not enabled yet.</p><?php endif ?></div>
<div class="d-flex flex-wrap align-items-center gap-2">
<?php if(in_array($financeContext['role']??$_SESSION['role']??'', ['teacher','sped_teacher','master_teacher'],true)):
if($pendingSubscription): ?><a class="btn btn-primary btn-sm" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance/payment/<?= (int)$pendingSubscription['id'] ?>">Continue Premium Payment</a>
<?php else: ?><a class="btn btn-primary btn-sm <?= empty($financeContext['enabled'])?'disabled':'' ?>" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance/premium" <?= empty($financeContext['enabled'])?'aria-disabled="true"':'' ?>><?= !empty($subscription['active'])?'Renew Premium — ₱199/month':'Upgrade to Premium — ₱199/month' ?></a><?php endif;
endif ?>
<a class="finance-inline-link" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance?tab=earnings"><?= ($financeContext['role']??$_SESSION['role']??'')==='admin'?'Creator earnings':'My earnings' ?></a>
<a class="finance-inline-link" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance">Billing history</a>
</div></div></section>
