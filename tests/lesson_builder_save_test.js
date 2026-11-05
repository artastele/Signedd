// Isolated browser-function regression test. No application bootstrap, database, or HTTP requests.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync(__dirname + '/../app/Views/iep_implementation/lesson_slide_builder.php', 'utf8');
const start = source.indexOf('async function saveActiveSlide(');
const end = source.indexOf('// ── Silent Refresh of Left Deck', start);
assert.ok(start > 0 && end > start, 'Save function found');
const saveSource = source.slice(start, end);

function fixture(fetch) {
    const elements = new Map();
    for (const id of ['slideTitleInput', 'slideGuideInput', 'slideId', 'saveSlideSubmitBtn', 'saveSlideTopBtn', 'saveStatusIndicator', 'activeSlideHeaderTitle', 'deleteSlideBtn']) {
        elements.set(id, {value: '', disabled: false, textContent: '', style: {}, classList: {add() {}, remove() {}}, focus() {}});
    }
    elements.get('slideTitleInput').value = 'Synthetic slide';
    let deckRefreshes = 0;
    const context = vm.createContext({
        document: {getElementById: id => elements.get(id)},
        $: () => ({summernote: () => '<p>Synthetic content</p>'}),
        FormData, fetch, clearTimeout, setTimeout, Date,
        BASE: '/Signedd/public', LP_ID: 77, LESSON_PAGES: [],
        autoSaveTimer: null, isAutoSaving: false, pendingManualSave: false,
        slideEditRevision: 0, slideContextVersion: 0,
        triggerAutoSave() {}, silentRefreshDeck() { deckRefreshes++; },
        console: {error() {}}
    });
    vm.runInContext(saveSource, context);
    return {context, elements, get deckRefreshes() { return deckRefreshes; }};
}

async function run() {
    const success = fixture(async () => ({ok: true, status: 200, json: async () => ({success: true, page_id: 42})}));
    await success.context.saveActiveSlide(true);
    assert.equal(success.elements.get('slideId').value, 42);
    assert.match(success.elements.get('saveStatusIndicator').textContent, /^Saved at /);
    assert.equal(success.deckRefreshes, 1);

    const failed = fixture(async () => ({ok: false, status: 500, json: async () => ({success: false, message: 'Synthetic failure'})}));
    await failed.context.saveActiveSlide(true);
    assert.match(failed.elements.get('saveStatusIndicator').textContent, /Slide not saved: Synthetic failure/);
    assert.equal(failed.elements.get('saveSlideTopBtn').disabled, false);

    let finishFirst;
    let requests = 0;
    const racing = fixture(async () => {
        requests++;
        if (requests === 1) return new Promise(resolve => { finishFirst = resolve; });
        return {ok: true, status: 200, json: async () => ({success: true, page_id: 42})};
    });
    const first = racing.context.saveActiveSlide(false);
    await racing.context.saveActiveSlide(true);
    assert.equal(requests, 1, 'manual save queues while autosave is running');
    finishFirst({ok: true, status: 200, json: async () => ({success: true, page_id: 42})});
    await first;
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(requests, 2, 'queued manual save runs after autosave');
    assert.match(racing.elements.get('saveStatusIndicator').textContent, /^Saved at /);
    console.log('PASS: Save Slide success, visible failure, and autosave/manual race; isolated mocks only.');
}

run().catch(error => { console.error(error); process.exitCode = 1; });
