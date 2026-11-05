// Isolated browser-function regression test. No application bootstrap, database, or HTTP requests.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync(__dirname + '/../app/Views/iep_implementation/lesson_slide_builder.php', 'utf8');
const start = source.indexOf('async function saveActiveSlide(');
const end = source.indexOf('// ── Silent Refresh of Left Deck', start);
assert.ok(start > 0 && end > start, 'Save function found');
const saveSource = source.slice(start, end);
const statusSource = source.slice(source.indexOf('function setSlideSaveStatus('), source.indexOf('function triggerAutoSave('));

function fixture(fetch) {
    const elements = new Map();
    const buttonLabel = {textContent: 'Save Slide'};
    for (const id of ['slideTitleInput', 'slideGuideInput', 'slideId', 'saveSlideSubmitBtn', 'saveSlideTopBtn', 'saveStatusIndicator', 'saveStatusFooterIndicator', 'activeSlideHeaderTitle', 'deleteSlideBtn']) {
        elements.set(id, {value: '', disabled: false, textContent: '', dataset: {}, style: {}, classList: {add() {}, remove() {}}, focus() {}, querySelector() { return buttonLabel; }});
    }
    elements.get('slideTitleInput').value = 'Synthetic slide';
    let deckRefreshes = 0;
    const preview = {opener: {}, closed: false, url: null, location: {replace(url) { preview.url = url; }}, close() { this.closed = true; }};
    const context = vm.createContext({
        document: {getElementById: id => elements.get(id)},
        $: () => ({summernote: () => '<p>Synthetic content</p>'}),
        FormData, fetch, clearTimeout, setTimeout, Date,
        BASE: '/Signedd/public', LP_ID: 77, LESSON_PAGES: [],
        autoSaveTimer: null, isAutoSaving: false, pendingManualSave: false,
        slideEditRevision: 0, slideContextVersion: 0,
        slideSaveFinished: Promise.resolve(),
        window: {open: () => preview},
        triggerAutoSave() {}, silentRefreshDeck() { deckRefreshes++; },
        console: {error() {}}
    });
    vm.runInContext(statusSource + saveSource, context);
    return {context, elements, buttonLabel, preview, get deckRefreshes() { return deckRefreshes; }};
}

async function run() {
    const success = fixture(async () => ({ok: true, status: 200, json: async () => ({success: true, page_id: 42})}));
    await success.context.saveActiveSlide(true);
    assert.equal(success.elements.get('slideId').value, 42);
    assert.match(success.elements.get('saveStatusIndicator').textContent, /^Saved at /);
    assert.equal(success.elements.get('saveStatusFooterIndicator').textContent, success.elements.get('saveStatusIndicator').textContent);
    assert.equal(success.buttonLabel.textContent, 'Saved ✓');
    assert.equal(success.deckRefreshes, 1);

    const failed = fixture(async () => ({ok: false, status: 500, json: async () => ({success: false, message: 'Synthetic failure'})}));
    await failed.context.saveActiveSlide(true);
    assert.match(failed.elements.get('saveStatusIndicator').textContent, /Slide not saved: Synthetic failure/);
    assert.equal(failed.elements.get('saveSlideTopBtn').disabled, false);
    assert.equal(failed.elements.get('saveStatusIndicator').dataset.state, 'error');
    assert.equal(failed.buttonLabel.textContent, 'Save Slide');

    const invalid = fixture(() => { throw new Error('Must not send without a title'); });
    invalid.elements.get('slideTitleInput').value = '';
    assert.equal(await invalid.context.saveActiveSlide(true), false);
    assert.match(invalid.elements.get('saveStatusIndicator').textContent, /please enter a Slide Title/);

    let finishFirst;
    let requests = 0;
    const racing = fixture(async () => {
        requests++;
        if (requests === 1) return new Promise(resolve => { finishFirst = resolve; });
        return {ok: true, status: 200, json: async () => ({success: true, page_id: 42})};
    });
    const first = racing.context.saveActiveSlide(false);
    assert.equal(racing.elements.get('saveStatusIndicator').dataset.state, 'saving');
    assert.equal(racing.buttonLabel.textContent, 'Saving…');
    await racing.context.saveActiveSlide(true);
    assert.equal(requests, 1, 'manual save queues while autosave is running');
    finishFirst({ok: true, status: 200, json: async () => ({success: true, page_id: 42})});
    await first;
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(requests, 2, 'queued manual save runs after autosave');
    assert.match(racing.elements.get('saveStatusIndicator').textContent, /^Saved at /);

    let finishPreviewSave;
    const pending = fixture(() => new Promise(resolve => { finishPreviewSave = resolve; }));
    const event = {preventDefault() {}, currentTarget: {href: '/learning/lesson/77'}};
    const opening = pending.context.previewSavedSlide(event);
    assert.equal(pending.preview.url, null, 'preview cannot show the old saved version during a save');
    finishPreviewSave({ok: true, status: 200, json: async () => ({success: true, page_id: 42})});
    await opening;
    assert.equal(pending.preview.url, event.currentTarget.href);
    assert.equal(pending.preview.opener, null);

    await failed.context.previewSavedSlide(event);
    assert.equal(failed.preview.closed, true, 'a save error cancels preview');
    assert.equal(failed.preview.url, null);

    let finishOldSave;
    let previewRequests = 0;
    const inFlight = fixture(async () => {
        previewRequests++;
        if (previewRequests === 1) return new Promise(resolve => { finishOldSave = resolve; });
        return {ok: true, status: 200, json: async () => ({success: true, page_id: 42})};
    });
    const oldSave = inFlight.context.saveActiveSlide(false);
    const waitingPreview = inFlight.context.previewSavedSlide(event);
    assert.equal(previewRequests, 1, 'preview waits for the existing autosave');
    finishOldSave({ok: true, status: 200, json: async () => ({success: true, page_id: 42})});
    await Promise.all([oldSave, waitingPreview]);
    assert.equal(previewRequests, 2, 'preview saves the latest editor content after autosave finishes');
    assert.equal(inFlight.preview.url, event.currentTarget.href);

    let finishStaleSave;
    const stale = fixture(() => new Promise(resolve => { finishStaleSave = resolve; }));
    const staleRequest = stale.context.saveActiveSlide(true);
    stale.context.slideEditRevision++;
    stale.context.setSlideSaveStatus('dirty', 'Unsaved changes — waiting to save…');
    finishStaleSave({ok: true, status: 200, json: async () => ({success: true, page_id: 42})});
    assert.equal(await staleRequest, false);
    assert.equal(stale.elements.get('saveStatusIndicator').dataset.state, 'dirty', 'an older response cannot claim new edits were saved');

    let retryCount = 0;
    const retry = fixture(async () => ++retryCount === 1
        ? {ok: false, status: 500, json: async () => ({success: false, message: 'Try again'})}
        : {ok: true, status: 200, json: async () => ({success: true, page_id: 42})});
    assert.equal(await retry.context.saveActiveSlide(true), false);
    assert.equal(await retry.context.saveActiveSlide(true), true);
    assert.equal(retry.elements.get('saveStatusIndicator').dataset.state, 'saved', 'successful retry replaces the error state');
    console.log('PASS: Save feedback, validation, autosave/manual race, and save-before-preview success/failure; isolated mocks only.');
}

run().catch(error => { console.error(error); process.exitCode = 1; });
