<?php // Shared by both existing publishing dialogs; tags persist in resource_hub_packs.tags.
$hubTagDraft = $hubTagPrefix === 'sharePack' ? ($_SESSION['resource_drafts'][$lpId ?? 0] ?? []) : [];
?>
<div class="col-12">
    <label class="form-label small fw-semibold" for="<?= $hubTagPrefix ?>Area">Learning Area</label>
    <input id="<?= $hubTagPrefix ?>Area" class="form-control form-control-sm" placeholder="e.g. Mathematics, Reading, Language, Science" maxlength="100">
</div>
<div class="col-12">
    <label class="form-label small fw-semibold" for="<?= $hubTagPrefix ?>Competency">Skill / Competency</label>
    <input id="<?= $hubTagPrefix ?>Competency" class="form-control form-control-sm" placeholder="e.g. Add numbers within 10" maxlength="1000" value="<?= htmlspecialchars($hubTagDraft['learning_competency'] ?? '') ?>">
</div>
<div class="col-12" data-resource-tags="<?= $hubTagPrefix ?>" data-base="<?= htmlspecialchars($basePath ?? '') ?>">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <label class="form-label small fw-semibold mb-0">Suggested Tags</label>
        <button type="button" class="btn btn-sm btn-outline-primary" data-suggest-tags>Suggest Tags</button>
    </div>
    <input type="hidden" id="<?= $hubTagPrefix ?>Tags" value="<?= htmlspecialchars($hubTagDraft['tags'] ?? '') ?>">
    <div class="d-flex flex-wrap gap-2 mb-2" data-tag-chips aria-label="Editable resource tags"></div>
    <div class="input-group input-group-sm">
        <input class="form-control" data-add-tag placeholder="Add a tag" aria-label="New resource tag" maxlength="60">
        <button type="button" class="btn btn-outline-secondary" data-add-tag-button>Add</button>
    </div>
    <p class="small text-muted my-2" data-tag-message role="status">Suggestions use only these resource details. Edit or remove tags before approving.</p>
    <label class="small d-flex align-items-start gap-2">
        <input type="checkbox" id="<?= $hubTagPrefix ?>TagsApproved" class="form-check-input mt-1" required>
        <span>Teacher review: I approve these tags and resource details for publishing.</span>
    </label>
</div>
<script src="<?= htmlspecialchars($basePath ?? '') ?>/js/resource-tags.js" defer></script>
