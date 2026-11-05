<?php
$pageTitle = htmlspecialchars((string)$pageTitle, ENT_QUOTES, 'UTF-8');
$financeStyles = true;
require __DIR__ . '/../layouts/header.php';
$e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$review = (string)($pack['review_status'] ?? '');
$proposal = (int)($pack['proposed_amount'] ?? 0);
$approved = $pack['approved_amount'] ?? null;
$approvedPrice = $review === 'APPROVED' && $approved !== null ? (int)$approved : null;
$pending = $review === 'PENDING';
$rejected = $review === 'REJECTED';
$missingApprovedPrice = $review === 'APPROVED' && $approved === null;
$money = static fn(int $centavos): string => '₱' . number_format($centavos / 100, 2);
$packType = $pending ? 'Pricing under review' : ($rejected ? 'Pricing needs revision' : ($missingApprovedPrice ? 'Price unavailable' : ($approvedPrice > 0 ? 'Premium pack' : 'Free pack')));
$priceLabel = ($pending || $rejected)
    ? ($proposal > 0 ? 'Proposed ' . $money($proposal) : 'Free proposal')
    : ($missingApprovedPrice ? 'Price unavailable' : ($approvedPrice > 0 ? $money($approvedPrice) : 'Free'));
?>
<body data-logged-in="true">
<?php require __DIR__ . '/../layouts/sidebar.php'; require __DIR__ . '/../layouts/topbar.php'; ?>
<main class="main-content">
    <div class="container-fluid py-4 finance-shell">
        <nav class="finance-breadcrumb mb-3"><a href="<?= $e($basePath) ?>/iep/implementation/resource-hub">Resource Hub</a> / Pack details</nav>
        <div class="finance-grid">
            <article>
                <header class="finance-hero mb-3">
                    <span class="finance-kicker"><?= $e($packType) ?></span>
                    <h1 class="h2 mt-2"><?= $e($pack['title']) ?></h1>
                    <p class="finance-muted mb-0">By <?= $e($pack['author_name'] ?: ($pack['creator_user_name'] ?? 'Resource creator')) ?></p>
                </header>
                <section class="finance-panel mb-3">
                    <h2 class="h5">About this pack</h2>
                    <?php if ($pack['description']): ?><p><?= nl2br($e($pack['description'])) ?></p><?php endif ?>
                    <div class="finance-meta">
                        <?php foreach (['pdsp_domain' => 'Domain', 'learning_level' => 'Level', 'language' => 'Language'] as $key => $label): if (!empty($pack[$key])): ?>
                            <span><?= $e($label) ?>: <?= $e(str_replace('_', ' ', $pack[$key])) ?></span>
                        <?php endif; endforeach ?>
                    </div>
                    <?php if ($pack['learning_competency']): ?><h3 class="h6 mt-4">Learning competency</h3><p class="mb-0"><?= nl2br($e($pack['learning_competency'])) ?></p><?php endif ?>
                </section>
                <section class="finance-panel">
                    <h2 class="h5">What’s included</h2>
                    <p><?= (int)$pack['page_count'] ?> slide<?= (int)$pack['page_count'] === 1 ? '' : 's' ?> · <?= (int)$pack['activity_count'] ?> activit<?= (int)$pack['activity_count'] === 1 ? 'y' : 'ies' ?><?php if ((int)$pack['fsl_sign_count'] > 0): ?> · <?= (int)$pack['fsl_sign_count'] ?> FSL sign<?= (int)$pack['fsl_sign_count'] === 1 ? '' : 's' ?><?php endif ?></p>
                    <?php if ($access['allowed'] && (!empty($pageSummaries) || !empty($activitySummaries))): ?>
                        <div class="row g-3 mt-1">
                            <?php if (!empty($pageSummaries)): ?><div class="col-sm-6"><h3 class="h6">Slides</h3><ol class="finance-list"><?php foreach ($pageSummaries as $title): ?><li><?= $e($title) ?></li><?php endforeach ?></ol></div><?php endif ?>
                            <?php if (!empty($activitySummaries)): ?><div class="col-sm-6"><h3 class="h6">Activities</h3><ul class="finance-list"><?php foreach ($activitySummaries as $title): ?><li><?= $e($title) ?></li><?php endforeach ?></ul></div><?php endif ?>
                        </div>
                    <?php endif ?>
                    <p class="small finance-muted mt-3 mb-0">Preview shows content permitted by your current access. Full premium materials require a verified purchase or active subscription.</p>
                </section>
            </article>
            <aside class="finance-summary finance-sticky align-self-start">
                <span class="finance-kicker">Pack access</span>
                <p class="finance-price mt-2"><?= $e($priceLabel) ?></p>
                <?php if ($pending): ?><p class="small finance-muted">Awaiting System Admin price and publication approval. This proposal is not available for purchase yet.</p><?php endif ?>
                <?php if ($rejected): ?><p class="small finance-muted">The proposed price needs revision and another admin review before purchase.</p><?php endif ?>
                <p><span class="finance-state <?= $access['allowed'] ? 'good' : 'wait' ?>"><?= $e($access['label']) ?></span></p>
                <?php if ($approvedPrice > 0): ?><p class="small finance-muted">Buy once for permanent pack access after verified payment. A current Premium subscription also includes eligible packs.</p><?php endif ?>
                <div class="finance-actions">
                    <a class="btn btn-outline-primary" href="<?= $e($basePath) ?>/iep/implementation/resource-hub/preview/<?= (int)$pack['id'] ?>">Preview pack</a>
                    <?php if ($access['allowed']): ?>
                        <a class="btn btn-primary" href="<?= $e($basePath) ?>/iep/implementation/resource-hub/preview/<?= (int)$pack['id'] ?>">Use as Template</a>
                    <?php elseif ($access['label'] === 'Requires Purchase' && $approvedPrice > 0): ?>
                        <a class="btn btn-primary" href="<?= $e($basePath) ?>/finance/review?kind=pack&amp;pack_id=<?= (int)$pack['id'] ?>">Buy Pack</a>
                    <?php endif ?>
                </div>
            </aside>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
