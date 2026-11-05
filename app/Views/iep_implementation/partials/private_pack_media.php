<?php if (getenv('SIGNED_FINANCE_ENABLED') === 'true'): ?>
<style>
.private-pack-media { padding: 1rem; border: 1px solid #dbe3ed; border-radius: 10px; background: #f8fafc; }
.private-pack-media-controls, .private-pack-media-url { display: flex; gap: .75rem; }
.private-pack-media-controls .form-control, .private-pack-media-url .form-control { flex: 1; min-width: 0; }
.private-pack-media-controls .btn, .private-pack-media-url .btn { flex: 0 0 auto; }
.private-pack-media-url[hidden] { display: none; }
@media (max-width: 767px) { .private-pack-media-controls, .private-pack-media-url { flex-direction: column; } }
</style>
<section class="col-12 private-pack-media" aria-label="Private pack media">
    <h3 class="h6 mb-1">Private pack media <span class="text-muted fw-normal">(optional)</span></h3>
    <p class="small text-muted mb-2">Existing local images and FSL videos in your lesson are protected automatically when you submit a Premium pack. No re-upload is needed. This optional upload is for adding a new file to your lesson.</p>
    <div class="private-pack-media-controls">
        <input class="form-control" type="file" id="<?= $hubTagPrefix ?>MediaFile" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,video/mp4,video/webm,video/quicktime,audio/mpeg,audio/ogg,audio/wav" aria-label="Choose private pack media file">
        <button class="btn btn-outline-primary" type="button" id="<?= $hubTagPrefix ?>MediaUpload">Upload privately</button>
    </div>
    <p class="small mt-2 mb-0" id="<?= $hubTagPrefix ?>MediaStatus" role="status" aria-live="polite"></p>
    <div class="private-pack-media-url mt-2" id="<?= $hubTagPrefix ?>MediaResult" hidden>
        <input class="form-control" id="<?= $hubTagPrefix ?>MediaUrl" type="text" readonly aria-label="Protected media URL">
        <button class="btn btn-outline-secondary" type="button" id="<?= $hubTagPrefix ?>MediaCopy">Copy URL</button>
    </div>
</section>
<script>
(() => {
    const prefix = <?= json_encode($hubTagPrefix) ?>;
    const byId = suffix => document.getElementById(prefix + suffix);
    byId('MediaUpload').addEventListener('click', async () => {
        const file = byId('MediaFile').files[0];
        const status = byId('MediaStatus');
        if (!file) { status.textContent = 'Choose a file first.'; return; }
        const button = byId('MediaUpload');
        const form = new FormData();
        form.append('file', file);
        form.append('_finance_csrf', <?= json_encode($_SESSION['finance_csrf']) ?>);
        button.disabled = true;
        status.textContent = 'Uploading privately…';
        try {
            const response = await fetch(<?= json_encode((defined('BASE_PATH') ? BASE_PATH : $basePath) . '/finance/media/upload') ?>, {method: 'POST', body: form});
            if (!response.ok) throw new Error('Upload rejected');
            const data = await response.json();
            if (!data.success || !data.url) throw new Error('Upload failed');
            byId('MediaUrl').value = data.url;
            byId('MediaResult').hidden = false;
            status.textContent = 'Uploaded. Add this URL to your lesson, save the slide, then submit the pack.';
        } catch (error) {
            status.textContent = 'Upload failed. Check the file type, 50 MB limit, private storage, and your session.';
        } finally { button.disabled = false; }
    });
    byId('MediaCopy').addEventListener('click', async () => {
        const input = byId('MediaUrl');
        try { await navigator.clipboard.writeText(input.value); byId('MediaStatus').textContent = 'Protected URL copied.'; }
        catch (error) { input.select(); byId('MediaStatus').textContent = 'Select and copy the URL shown below.'; }
    });
})();
</script>
<?php endif; ?>
