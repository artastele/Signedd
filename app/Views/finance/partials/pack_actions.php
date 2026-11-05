<?php
// $pack is server-decorated Hub metadata. Backend routes independently recheck every entitlement.
$canUse=!isset($pack['access_allowed']) || !empty($pack['access_allowed']);
$viewerRole=$userRole??($financeContext['role']??($_SESSION['role']??''));
$canBuy=!$canUse && !empty($financeContext['enabled']) && ($pack['finance_review']??'')==='APPROVED' && ($pack['access_label']??'')==='Requires Purchase' &&
    (int)($pack['price_centavos']??0)>=5900 && (int)$pack['price_centavos']<=9900 &&
    in_array($financeContext['role']??$_SESSION['role']??'', ['teacher','sped_teacher','master_teacher'],true);
?>
<a href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/iep/implementation/resource-hub/preview/<?= (int)$pack['id'] ?>" class="btn btn-outline-secondary btn-hub-action" title="Preview permitted resource content"><i class="ph-bold ph-eye me-1"></i> Preview</a>
<?php if(!empty($financeContext['enabled']) && $viewerRole !== 'general_teacher'): ?><a href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance/pack/<?= (int)$pack['id'] ?>" class="btn btn-outline-primary btn-hub-action">View Pack</a><?php endif ?>
<?php if($canUse && $viewerRole !== 'general_teacher'): ?>
<button type="button" class="btn btn-primary btn-hub-action" onclick="openUseAsTemplateModal(<?= (int)$pack['id'] ?>, <?= htmlspecialchars(json_encode($pack['title']),ENT_QUOTES,'UTF-8') ?>)"><i class="ph-bold ph-copy me-1"></i> Use as Template</button>
<?php elseif($canBuy): ?>
<a class="btn btn-primary btn-hub-action" href="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance/pack/<?= (int)$pack['id'] ?>">Buy Pack — ₱<?= number_format((int)$pack['price_centavos']/100,2) ?></a>
<?php else: ?><span class="small text-muted"><?= htmlspecialchars($pack['access_label']??'Awaiting review',ENT_QUOTES,'UTF-8') ?></span><?php endif ?>
