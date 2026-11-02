'use strict';
// Minimal DOM fixtures exercise the real shared widget; no browser/network dependencies.
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
class Element {
    constructor() { this.children = []; this.events = {}; this.value = ''; this.checked = false; this.dataset = {}; this.style = {}; }
    addEventListener(type, listener) { (this.events[type] ||= []).push(listener); }
    async fire(type, properties = {}) {
        const event = {currentTarget: this, preventDefault() { this.prevented = true; }, stopImmediatePropagation() { this.stopped = true; }, ...properties};
        for (const listener of this.events[type] || []) await listener(event);
        return event;
    }
    append(...children) { children.forEach(child => { child.parent = this; this.children.push(child); }); }
    replaceChildren() { this.children = []; }
    remove() { this.parent.children = this.parent.children.filter(child => child !== this); }
    setAttribute() {}
    querySelectorAll() { return this.children.flatMap(child => child.type === 'text' ? [child] : child.querySelectorAll()); }
}
async function main() {
    const fields = Object.fromEntries(['Title','Desc','Area','Competency','Domain','Level','MasteryTitle','MasteryUrl','Tags','TagsApproved'].map(key => [key,new Element()]));
    fields.Title.value = 'Basic Addition Within 10'; fields.Area.value = 'Mathematics';
    const chips = new Element(), add = new Element(), message = new Element(), suggest = new Element(), addButton = new Element();
    const form = new Element(), modal = new Element(), root = new Element();
    root.dataset = {resourceTags:'sharePack',base:'/Signedd/public'};
    root.querySelector = selector => ({'[data-tag-chips]':chips,'[data-add-tag]':add,'[data-tag-message]':message,
        '[data-suggest-tags]':suggest,'[data-add-tag-button]':addButton})[selector];
    root.closest = selector => selector === 'form' ? form : modal;
    let request, resolvePending;
    let responder = async () => ({ok:true,json:async()=>({success:true,tags:['addition','within 10','addition']})});
    const context = {document:{readyState:'complete',querySelectorAll:()=>[root],createElement:()=>new Element(),
        getElementById:id=>fields[id.replace('sharePack','')]},
        fetch:async (url,options) => { request = {url,body:JSON.parse(options.body)}; return responder(); }};
    vm.runInNewContext(fs.readFileSync('public/js/resource-tags.js','utf8'),context);
    let checks = 0;
    const check = (condition,message) => { checks++; assert.ok(condition,message); };
    await suggest.fire('click');
    check(fields.Tags.value === 'addition, within 10', 'Suggestions become deduplicated editable chips');
    check(!fields.TagsApproved.checked, 'Suggestions never approve themselves');
    check(request.url === '/Signedd/public/iep/implementation/resource-hub/suggest-tags', 'Uses existing Resource Hub namespace');
    check(Object.keys(request.body).join(',') === 'title,description,learning_area,learning_competency', 'Only resource metadata is sent');
    fields.TagsApproved.checked = true;
    const accepted = await form.fire('submit');
    check(!accepted.prevented, 'Reviewed metadata can submit');
    const firstInput = chips.children[0].children[0];
    firstInput.value = 'number bonds'; await firstInput.fire('input');
    check(fields.Tags.value === 'number bonds, within 10' && !fields.TagsApproved.checked, 'Editing chip syncs tags and revokes approval');
    fields.TagsApproved.checked = true;
    await chips.children[1].children[1].fire('click');
    check(fields.Tags.value === 'number bonds' && !fields.TagsApproved.checked, 'Removing chip revokes approval');
    add.value = 'addition'; await addButton.fire('click');
    check(fields.Tags.value === 'number bonds, addition', 'Teacher can add custom tags');
    fields.TagsApproved.checked = true;
    await fields.Competency.fire('input');
    check(!fields.TagsApproved.checked, 'Changing competency requires review again');
    const blocked = await form.fire('submit');
    check(blocked.prevented && blocked.stopped, 'Unapproved metadata never reaches publishing handler');
    fields.TagsApproved.checked = true; add.value = 'pending';
    check((await form.fire('submit')).prevented, 'Pending unreviewed tag blocks publication');
    add.value = '';
    responder = () => new Promise(resolve => { resolvePending = resolve; });
    const pending = suggest.fire('click');
    await fields.Title.fire('input');
    resolvePending({ok:true,json:async()=>({success:true,tags:['stale tag']})});
    await pending;
    check(!fields.Tags.value.includes('stale') && message.textContent.includes('Details changed'), 'Stale suggestion response is ignored');
    responder = async () => { throw new Error('Offline'); };
    await suggest.fire('click');
    check(message.textContent.includes('manually') && !suggest.disabled, 'Unavailable endpoint leaves manual review usable');
    fields.Tags.value = ''; await modal.fire('show.bs.modal');
    fields.TagsApproved.checked = true; await fields.TagsApproved.fire('change');
    check(!fields.TagsApproved.checked, 'Empty tags cannot be approved');
    console.log(`Resource tag widget: ${checks} checks passed; no browser or network used.`);
}
main().catch(error => { console.error(error); process.exitCode = 1; });
