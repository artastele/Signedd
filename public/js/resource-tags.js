/* Local metadata suggestions only. No learner/IEP data or documents are collected. */
(() => {
    function init(root) {
        if (root.dataset.initialized) return;
        root.dataset.initialized = 'true';
        const prefix = root.dataset.resourceTags;
        const field = suffix => document.getElementById(prefix + suffix);
        const hidden = field('Tags'), approval = field('TagsApproved');
        const chips = root.querySelector('[data-tag-chips]');
        const add = root.querySelector('[data-add-tag]');
        const message = root.querySelector('[data-tag-message]');
        let revision = 0;
        const clean = value => value.toLowerCase().replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
        const values = () => [...chips.querySelectorAll('input')].map(input => clean(input.value)).filter(Boolean);
        function invalidate() { approval.checked = false; revision++; }
        function sync() { hidden.value = [...new Set(values())].join(', '); }
        function render(tags) {
            chips.replaceChildren();
            [...new Set(tags.map(clean).filter(Boolean))].forEach(tag => {
                const chip = document.createElement('span');
                chip.className = 'd-inline-flex align-items-center border rounded px-1 bg-light';
                const input = document.createElement('input');
                input.type = 'text'; input.value = tag; input.maxLength = 60;
                input.className = 'form-control form-control-sm border-0 bg-transparent';
                input.style.width = Math.min(28, Math.max(8, tag.length + 2)) + 'ch';
                input.setAttribute('aria-label', 'Edit tag ' + tag);
                input.addEventListener('input', () => { invalidate(); sync(); });
                const remove = document.createElement('button');
                remove.type = 'button'; remove.className = 'btn btn-sm px-1'; remove.textContent = '×';
                remove.setAttribute('aria-label', 'Remove tag ' + tag);
                remove.addEventListener('click', () => { chip.remove(); invalidate(); sync(); });
                chip.append(input, remove); chips.append(chip);
            });
            invalidate(); sync();
        }
        function addTag() { render([...values(), ...add.value.split(',')]); add.value = ''; }
        root.querySelector('[data-add-tag-button]').addEventListener('click', addTag);
        add.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); addTag(); } });
        add.addEventListener('input', invalidate);
        ['Title', 'Desc', 'Area', 'Competency', 'Domain', 'Level', 'MasteryTitle', 'MasteryUrl'].forEach(suffix => {
            field(suffix)?.addEventListener('input', invalidate);
            field(suffix)?.addEventListener('change', invalidate);
        });
        root.closest('.modal')?.addEventListener('show.bs.modal', () => {
            render(hidden.value.split(',')); add.value = '';
        });
        approval.addEventListener('change', () => {
            if (add.value.trim()) { addTag(); message.textContent = 'Review your added tag, then approve again.'; }
            if (!values().length) { approval.checked = false; message.textContent = 'Suggest or add at least one relevant tag first.'; }
        });
        root.closest('form').addEventListener('submit', event => {
            sync();
            if (!approval.checked || !hidden.value || add.value.trim()) {
                event.preventDefault(); event.stopImmediatePropagation();
                message.textContent = 'Add pending tags and approve the final tags before publishing.';
            }
        }, true);
        root.querySelector('[data-suggest-tags]').addEventListener('click', async event => {
            const button = event.currentTarget;
            invalidate(); const requestedRevision = revision;
            button.disabled = true; message.textContent = 'Suggesting tags from your resource details…';
            try {
                const response = await fetch(root.dataset.base + '/iep/implementation/resource-hub/suggest-tags', {
                    method: 'POST', headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ title: field('Title').value, description: field('Desc').value,
                        learning_area: field('Area').value, learning_competency: field('Competency').value })
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Suggestions are unavailable.');
                if (revision !== requestedRevision) { message.textContent = 'Details changed. Select Suggest Tags again.'; return; }
                render([...values(), ...data.tags]);
                message.textContent = data.tags.length ? 'Review, edit or remove the suggested tags, then approve.' : 'Add a specific skill or enter your own tags.';
            } catch (error) {
                message.textContent = error.message + ' You can still add and approve tags manually.';
            } finally { button.disabled = false; }
        });
    }
    const start = () => document.querySelectorAll('[data-resource-tags]').forEach(init);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
