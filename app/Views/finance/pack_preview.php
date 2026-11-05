<?php
$pageTitle='Preview: '.htmlspecialchars((string)$pack['title'],ENT_QUOTES,'UTF-8');
$financeStyles=true;
require __DIR__.'/../layouts/header.php';
$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
?>
<body data-logged-in="true"><?php require __DIR__.'/../layouts/sidebar.php';require __DIR__.'/../layouts/topbar.php'; ?>
<main class="main-content"><div class="container-fluid py-4 finance-shell">
<nav class="finance-breadcrumb mb-3"><a href="<?= $e($basePath) ?>/iep/implementation/resource-hub">Resource Hub</a> / Preview</nav>
<header class="finance-hero mb-3"><span class="finance-kicker">Pack preview</span><h1 class="h2 mt-2"><?= $e($pack['title']) ?></h1><p class="finance-muted mb-0">By <?= $e($pack['author_name']??'Resource creator') ?></p></header>
<div class="finance-grid"><section class="finance-panel"><h2 class="h5">About this pack</h2><?php if(!empty($pack['description'])): ?><p><?= nl2br($e($pack['description'])) ?></p><?php endif ?><?php if(!empty($pack['learning_competency'])): ?><p><strong>Competency:</strong> <?= $e($pack['learning_competency']) ?></p><?php endif ?><p class="small finance-muted mb-0"><?= (int)($pack['page_count']??0) ?> slides · <?= (int)($pack['activity_count']??0) ?> activities. Full premium content is available after verified payment or through an active subscription.</p></section>
<aside class="finance-summary finance-sticky align-self-start"><span class="finance-kicker">Access</span><p class="finance-price mt-2"><?= (int)($pack['price_centavos']??0)>0?'₱'.number_format((int)$pack['price_centavos']/100,2):'Free' ?></p><p><span class="finance-state wait"><?= $e($pack['access_label']??'Awaiting review') ?></span></p><div class="finance-actions"><a class="btn btn-primary" href="<?= $e($basePath) ?>/finance/pack/<?= (int)$pack['id'] ?>">View Pack</a><a class="btn btn-outline-secondary" href="<?= $e($basePath) ?>/iep/implementation/resource-hub">Back to Resource Hub</a></div></aside></div>
</div></main><?php require __DIR__.'/../layouts/footer.php'; ?>
