<?php
// Session-only CSRF protection: document preview never creates database tokens.
$importIepId = (int)$iep['id'];
if (empty($_SESSION['iep_part3_import_csrf'][$importIepId])) {
    $_SESSION['iep_part3_import_csrf'][$importIepId] = bin2hex(random_bytes(32));
}
?>
<div class="border rounded p-3 mb-3" id="iepStepsImport"
     data-iep-id="<?= $importIepId ?>"
     data-url="<?= htmlspecialchars($basePath . '/iep/import-steps/preview') ?>"
     data-csrf="<?= htmlspecialchars($_SESSION['iep_part3_import_csrf'][$importIepId]) ?>">
    <div class="fw-semibold small mb-2">Import File &rarr; Preview &rarr; Confirm Import</div>
    <p class="small text-muted mb-2">Word (.docx) or Excel (.xlsx), up to 5 MB. Match columns by headings. Imported steps fill unused blank rows, then add rows to this learner's form. Existing completed steps stay unchanged.</p>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <label class="visually-hidden" for="iepStepsImportFile">Completed IEP Part III file</label>
        <input class="form-control form-control-sm" style="max-width:360px" type="file" id="iepStepsImportFile" accept=".docx,.xlsx">
        <button class="btn btn-sm btn-outline-primary rounded" type="button" id="iepStepsImportPreview">Preview</button>
    </div>
    <div class="small mt-2" id="iepStepsImportMessage" role="status" aria-live="polite"></div>
    <div id="iepStepsImportReview" hidden>
        <p class="small mt-3">Review and correct the extracted values. Confirm Import fills unused blank rows and adds the remaining steps below; use <strong>Save steps</strong> to save your changes. Existing completed steps are preserved. Lesson plans, materials, observations, and mastery ratings are not imported.</p>
        <div class="table-responsive">
            <table class="table table-sm small align-middle">
                <thead><tr><th>Step Objective</th><th>Developmental Domain</th><th>Duration of LP</th><th>Planned Instructional Evaluation</th><th></th></tr></thead>
                <tbody id="iepStepsImportRows"></tbody>
            </table>
        </div>
        <button class="btn btn-sm btn-primary rounded" type="button" id="iepStepsImportConfirm">Confirm Import</button>
        <button class="btn btn-sm btn-outline-secondary rounded" type="button" id="iepStepsImportCancel">Cancel</button>
    </div>
</div>
<script src="<?= htmlspecialchars($basePath) ?>/js/iep-steps-import.js" defer></script>
