<?php
// Shared native entry point; no browser-supplied price, buyer, entitlement or redirect target.
$purchaseAllowed=!empty($financeContext['enabled']) && !empty($purchaseEnabled);
?>
<form method="post" action="<?= htmlspecialchars($basePath,ENT_QUOTES,'UTF-8') ?>/finance/action" class="d-inline-block m-0">
<input type="hidden" name="_finance_csrf" value="<?= htmlspecialchars($_SESSION['finance_csrf'],ENT_QUOTES,'UTF-8') ?>">
<input type="hidden" name="action" value="create"><input type="hidden" name="kind" value="<?= htmlspecialchars($purchaseKind,ENT_QUOTES,'UTF-8') ?>">
<?php if($purchaseKind==='pack'): ?><input type="hidden" name="pack_id" value="<?= (int)$purchasePackId ?>"><?php endif ?>
<?php if($purchaseKind==='institutional'): ?><label class="small d-block mb-2"><input type="checkbox" name="school_license_consent" value="yes" required <?= $purchaseAllowed?'':'disabled' ?>> I apply for my school’s license. Existing access continues until first admin activation.</label><?php endif ?>
<button type="submit" class="btn btn-primary btn-sm" <?= $purchaseAllowed?'':'disabled aria-disabled="true"' ?>><?= htmlspecialchars($purchaseLabel,ENT_QUOTES,'UTF-8') ?></button>
</form>
