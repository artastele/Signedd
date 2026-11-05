<?php
$financeStyles=true;
require __DIR__.'/../layouts/header.php';
$e=static fn($value)=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
$sub=$context['subscription']??null;
$pending=$context['pending_order']??null;
?>
<?php require __DIR__.'/../layouts/sidebar.php'; require __DIR__.'/../layouts/topbar.php'; ?>
<main class="main-content finance-marketplace-page"><div class="container-fluid finance-shell">
<div class="finance-page-top"><a class="finance-back-button" href="<?= $e($basePath) ?>/iep/implementation/resource-hub"><i class="ph-bold ph-arrow-left" aria-hidden="true"></i> Back to Resource Hub</a><span class="finance-mode-pill">Sandbox / Test Mode</span></div>
<header class="finance-hero finance-page-hero mb-3"><span class="finance-kicker">Resource Hub membership</span><h1 class="h2 mt-2 mb-1">Premium Resource Hub</h1><p class="finance-muted mb-0">Choose free access or a manually renewed monthly plan. Review the amount before creating a payment request.</p></header>
<?php if($sub && $sub['state']!=='NONE'): ?><section class="finance-status-callout mb-3"><strong>Your subscription: <?= $e(ucfirst(strtolower($sub['state']))) ?></strong><?php if($sub['expires_at']): ?><span>Current term ends <?= $e($sub['expires_at']) ?> UTC. Packs purchased individually remain yours afterward.</span><?php endif ?></section><?php endif ?>
<div class="row g-3 mb-3"><div class="col-md-6"><section class="finance-product finance-choice"><div><span class="finance-kicker">Free</span><h2 class="h4 mt-2 mb-1">Browse and share</h2><p class="finance-price mb-0">₱0</p></div><ul class="finance-list"><li>Browse and use approved free packs</li><li>Preview available pack details</li><li>Submit your own packs for review</li></ul><a class="btn btn-outline-primary mt-auto" href="<?= $e($basePath) ?>/iep/implementation/resource-hub">Browse free packs</a></section></div>
<div class="col-md-6 finance-plan-premium"><section class="finance-product finance-choice selected"><div><span class="finance-kicker">Premium</span><h2 class="h4 mt-2 mb-1">Monthly access</h2><p class="finance-price mb-0">₱199 <small>/ month</small></p></div><ul class="finance-list"><li>Use eligible, admin-approved Premium packs during your term</li><li>Manual renewal by the teacher</li><li>No automatic recurring charges</li></ul><p class="small finance-muted mb-0"><?= $packCount>0?$packCount.' approved Premium pack'.($packCount===1?'':'s').' currently available.':'No Premium packs are approved for browsing yet.' ?> Individual pack purchases are separate.</p><div class="finance-actions mt-auto"><?php if($pending): ?><a class="btn btn-primary" href="<?= $e($basePath) ?>/finance/payment/<?= (int)$pending['id'] ?>">Continue existing payment</a><?php else: ?><a class="btn btn-primary" href="<?= $e($basePath) ?>/finance/review?kind=subscription"><?= !empty($sub['active'])?'Review renewal':'Review Premium plan' ?></a><?php endif ?></div></section></div></div>
<p class="small finance-muted mb-0">Payment is handled by PayMongo Hosted Checkout. Access starts only after independent payment verification.</p>
</div></main><?php require __DIR__.'/../layouts/footer.php'; ?>
