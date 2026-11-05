<?php require __DIR__.'/context.php'; ?>
<?php $license=$financeContext['school']??null; $pending=$financeContext['pending_order']??null; $state=$license['state']??'UNAVAILABLE'; ?>
<section class="finance-panel institutional-license-card mb-4" aria-label="Institutional license">
    <div class="institutional-license-icon" aria-hidden="true"><i class="bi bi-building-check"></i></div>
    <div class="institutional-license-content">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="finance-kicker">SignED Annual Institutional License</span>
            <span class="finance-state <?= in_array($state,['ACTIVE','GRACE'],true)?'good':($state==='PAYMENT_REQUIRED'?'bad':'wait') ?>"><?= htmlspecialchars($state,ENT_QUOTES,'UTF-8') ?></span>
            <span class="finance-mode-pill">Sandbox</span>
        </div>
        <h2>Activate your complete school workspace</h2>
        <p class="institutional-license-lead">Unlock staff and section management, room assignments, IEP approval, enrollment setup, learning tools, and school reports.</p>
        <div class="institutional-license-benefits" aria-label="License inclusions">
            <span><i class="bi bi-people-fill"></i> 10 active faculty seats</span>
            <span><i class="bi bi-person-check-fill"></i> Learner &amp; parent accounts included</span>
            <span><i class="bi bi-shield-check"></i> 30-day grace period</span>
        </div>
        <?php if(!empty($license['activation']['activated_at'])): ?>
            <p class="institutional-license-meta mb-0">Activated <?= htmlspecialchars($license['activation']['activated_at'],ENT_QUOTES,'UTF-8') ?> UTC</p>
        <?php endif ?>
        <?php if(empty($financeContext['enabled'])): ?>
            <p class="institutional-license-note mb-0">Finance is not enabled yet. Existing school access is preserved.</p>
        <?php elseif(!$license): ?>
            <p class="institutional-license-note mb-0">Associate your principal account with a registered school before activation.</p>
        <?php elseif($pending): ?>
            <p class="institutional-license-note mb-0"><strong>Payment request ready and awaiting payment verification.</strong> School modules activate only after PayMongo independently verifies payment.</p>
        <?php elseif($state==='PAYMENT_REQUIRED'): ?>
            <p class="institutional-license-note mb-0"><strong>Payment required.</strong> Operational school modules remain locked until payment is verified.</p>
        <?php elseif(!empty($license['expires_at'])): ?>
            <p class="institutional-license-note mb-0">Current access expires <strong><?= htmlspecialchars($license['expires_at'],ENT_QUOTES,'UTF-8') ?> UTC</strong>.</p>
        <?php endif ?>
    </div>
    <aside class="institutional-license-action">
        <div class="institutional-license-price">₱15,000 <small>per school / year</small></div>
        <?php if($pending): ?>
            <a class="btn btn-primary btn-lg w-100" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance/payment/<?= (int)$pending['id'] ?>"><i class="bi bi-credit-card me-2"></i><?= $pending['status']==='PAID'?'View Payment Confirmation':'Continue License Payment' ?></a>
        <?php else: ?>
            <a class="btn btn-primary btn-lg w-100 <?= $license===null || empty($financeContext['enabled'])?'disabled':'' ?>" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance/license" <?= $license===null || empty($financeContext['enabled'])?'aria-disabled="true"':'' ?>><i class="bi bi-shield-check me-2"></i><?= !empty($license['enrolled'])?'Renew License':'Activate School License' ?></a>
        <?php endif ?>
        <a class="institutional-license-history" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance"><i class="bi bi-receipt me-1"></i> View billing history</a>
        <small>Premium Resource Hub plans and packs are billed separately.</small>
    </aside>
</section>
