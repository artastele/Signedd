// Exercises the actual share handler with isolated UI/network fixtures.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../app/Views/iep_implementation/lesson_slide_builder.php', 'utf8');
const start = source.indexOf('async function submitShareToHub(');
const end = source.indexOf('/**', start);
const handler = source.slice(start, end).replace(/<\?=[\s\S]*?\?>/g, '"fixture-csrf"');
function fixture(saveOk, response) {
    const fields = {};
    for (const id of ['sharePackError', 'btnSubmitShareHub', 'sharePackTitle', 'sharePackDesc', 'sharePackDomain', 'sharePackLevel', 'sharePackCompetency', 'sharePackTags', 'sharePackPrice', 'sharePackTagsApproved', 'slideTitleInput', 'saveStatusIndicator']) {
        fields[id] = {value: 'Colors', innerHTML: 'Submit', textContent: '', hidden: true, disabled: false, checked: true, scrollIntoView() {}};
    }
    fields.sharePackPrice.value = '5900';
    const calls = [];
    let hidden = false;
    let popup;
    const context = vm.createContext({
        document: {getElementById: id => fields[id]},
        $: () => ({summernote: () => '<p>Latest edit</p>'}),
        BASE: '/Signedd/public', LP_ID: 16, isAutoSaving: false, slideSaveFinished: Promise.resolve(),
        saveActiveSlide: async () => {calls.push('save'); return saveOk;},
        fetch: async (url, options) => {calls.push('share'); const data = JSON.parse(options.body); assert.equal(data.proposed_amount, 5900); assert.equal(options.headers['X-Finance-CSRF'], 'fixture-csrf'); return {json: async () => response};},
        bootstrap: {Modal: {getInstance: () => ({hide() {hidden = true;}})}},
        Swal: {fire: data => {popup = data; return Promise.resolve({isConfirmed: false});}},
        console: {error() {}}, window: {location: {}},
    });
    vm.runInContext(handler, context);
    return {context, fields, calls, get modalHidden() {return hidden;}, get popup() {return popup;}};
}
async function run() {
    const success = fixture(true, {success: true, pack_id: 12});
    await success.context.submitShareToHub({preventDefault() {}});
    assert.deepEqual(success.calls, ['save', 'share']);
    assert.equal(success.modalHidden, true);
    assert.equal(success.popup.title, 'Sent for admin review');
    assert.equal(success.fields.btnSubmitShareHub.disabled, false);
    const failure = fixture(true, {success: false, message: 'Slide 1: A lesson media file is missing.'});
    await failure.context.submitShareToHub({preventDefault() {}});
    assert.equal(failure.fields.sharePackError.hidden, false);
    assert.match(failure.fields.sharePackError.textContent, /Slide 1:/);
    assert.equal(failure.modalHidden, false);
    assert.equal(failure.fields.btnSubmitShareHub.innerHTML, 'Submit');
    const unsaved = fixture(false, null);
    await unsaved.context.submitShareToHub({preventDefault() {}});
    assert.deepEqual(unsaved.calls, ['save']);
    assert.match(unsaved.fields.sharePackError.textContent, /could not be saved/);
    const waiting = fixture(true, {success: true, pack_id: 12});
    let finish;
    waiting.context.isAutoSaving = true;
    waiting.context.slideSaveFinished = new Promise(resolve => {finish = resolve;});
    const pending = waiting.context.submitShareToHub({preventDefault() {}});
    assert.deepEqual(waiting.calls, []);
    waiting.context.isAutoSaving = false;
    finish();
    await pending;
    assert.deepEqual(waiting.calls, ['save', 'share']);
    console.log('PASS: Premium sharing saves latest edits, waits for autosave, keeps errors inline, and restores submit controls.');
}
run().catch(error => {console.error(error); process.exitCode = 1;});
