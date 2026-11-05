<?php
$financeStyles = true;
require __DIR__ . '/../layouts/header.php';
$e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$license = $context['school'];
$pending = $context['pending_order'];
$teacherSeats = (int)($teacherSeats ?? 0);
$state = $license['state'] === 'LEGACY' ? 'Existing school access' : ucwords(strtolower(str_replace('_', ' ', (string)$license['state'])));
?>
<?php require __DIR__ . '/../layouts/sidebar.php'; require __DIR__ . '/../layouts/topbar.php'; ?>
<main class="main-content finance-marketplace-page"><div class="container-fluid finance-shell">
    <div class="finance-page-top"><a class="finance-back-button" href="<?= $e($basePath) ?>/dashboard"><i class="ph-bold ph-arrow-left" aria-hidden="true"></i> Back to Principal Dashboard</a><span class="finance-mode-pill">Sandbox / Test Mode</span></div>
    <header class="finance-hero finance-page-hero mb-3"><span class="finance-kicker">SignED Annual Institutional License</span><h1 class="h2 mt-2 mb-1">School-wide learning access</h1><p class="finance-muted mb-0"><?= $e($schoolName) ?> · Inclusive learning for DHH and SNED programs.</p></header>
    <div class="finance-grid">
        <div>
            <section class="finance-panel mb-3"><span class="finance-kicker">Current access</span><h2 class="h4 mt-2 mb-3"><?= $e($state) ?></h2>
                <?php if ($license['expires_at']): ?><p class="mb-2"><strong>Valid until:</strong> <?= $e($license['expires_at']) ?> UTC</p><?php endif; ?>
                <p class="mb-2"><strong>Active faculty accounts:</strong> <?= (int)$teacherSeats ?><?= $license['enrolled'] ? ' of 10 licensed seats' : (!empty($license['required']) ? ' · activation locked until verified payment' : ' · 10-seat limit begins after verified paid activation') ?></p>
                <p class="finance-muted small mb-0"><?= $license['enrolled'] ? 'Full access continues for 30 days after expiration. The Principal initiates renewal.' : (!empty($license['required']) ? 'Your new school is approved. Complete the pending annual license payment to activate teaching access and faculty approvals.' : 'Existing school access continues. The license starts automatically only after PayMongo independently confirms the payment.') ?></p>
            </section>
            <section class="finance-panel"><span class="finance-kicker">What's included</span><h2 class="h5 mt-2">One license for your school</h2>
                <ul class="finance-list">
                    <li>Up to 10 active SPED Teacher, General Teacher, Master Teacher, and Guidance accounts combined</li>
                    <li>Authorized school administrator and Principal access</li>
                    <li>Learner and parent accounts with no per-user licensing charge</li>
                    <li>IEP development, implementation and progress monitoring</li>
                    <li>Accessible lessons, assessments and learning activities</li>
                    <li>FSL resources and accessibility tools</li>
                    <li>School performance and learner progress reports</li>
                    <li>Free shared Resource Hub materials</li>
                    <li>30-day full-access grace period after license expiration</li>
                </ul>
                <p class="small finance-muted mt-3 mb-0">Premium Resource Hub subscriptions and individual pack purchases are billed separately. License expiry never deletes educational records.</p>
            </section>
        </div>
        <aside class="finance-summary finance-sticky align-self-start"><span class="finance-kicker">Annual institutional license</span><p class="finance-price mt-2 mb-2">₱15,000 <small>/ school / year</small></p><p class="small finance-muted">One year from verified paid activation. Renewal is manual; there are no automatic charges. Payment must be independently confirmed by PayMongo before access starts.</p>
            <div class="finance-actions finance-actions-stack"><?php if ($pending): ?><a class="btn btn-primary" href="<?= $e($basePath) ?>/finance/payment/<?= (int)$pending['id'] ?>">Continue pending payment</a><?php else: ?><a class="btn btn-primary" href="<?= $e($basePath) ?>/finance/review?kind=institutional"><?= $license['enrolled'] ? 'Review renewal' : 'Review license application' ?></a><?php endif; ?><a class="btn btn-outline-primary" href="<?= $e($basePath) ?>/finance">View billing history</a></div>
        </aside>
    </div>
</div></main><?php require __DIR__ . '/../layouts/footer.php'; ?>
