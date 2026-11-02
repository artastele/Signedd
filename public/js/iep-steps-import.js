(function () {
    'use strict';
    const domains = ['Daily Living Skills', 'Socio-Emotional', 'Language Development', 'Psychomotor',
        'Cognitive', 'Aesthetic/Creative', 'Behavioral Development', 'Orientation and Mobility'];
    const fields = ['step_objective', 'pdsp_indicator_text', 'duration_lp', 'instructional_evaluation'];
    const selectors = {step_objective:'.step-objective', pdsp_indicator_text:'.step-pdsp-indicator',
        duration_lp:'.step-strategies', instructional_evaluation:'.step-evaluation'};
    const key = value => String(value || '').trim().replace(/\s+/g, ' ').toLocaleLowerCase();

    function validateRows(rows, existingObjectives, existingCount) {
        if (!Array.isArray(rows) || !rows.length) throw new Error('Keep at least one Step Objective to import.');
        if (rows.length + existingCount > 10) throw new Error('A maximum of 10 steps is allowed, including existing rows. Remove extra preview rows.');
        const seen = new Set(existingObjectives.filter(value => key(value)).map(key));
        return rows.map(row => {
            const clean = {};
            fields.forEach(field => {
                clean[field] = String(row[field] || '').trim();
                if (clean[field].length > 5000) throw new Error('Each value must be no longer than 5,000 characters.');
            });
            const objective = key(clean.step_objective);
            if (!objective) throw new Error('Each imported row needs a Step Objective.');
            if (seen.has(objective)) throw new Error('A Step Objective is duplicated in the preview or existing form. Edit or remove the duplicate; existing steps will not be overwritten.');
            seen.add(objective);
            if (!domains.includes(clean.pdsp_indicator_text)) throw new Error('Select a developmental domain for each imported step.');
            return clean;
        });
    }

    function availableEmptyRows(tableBody) {
        return Array.from(tableBody.querySelectorAll('tr')).filter(tr => {
            if (tr.getAttribute('data-import-placeholder') !== '1' && tr.getAttribute('data-step-id') !== '0') return false;
            if (tr.querySelector('.lp-list li')) return false;
            const observation = tr.querySelector('.step-observation');
            if (observation && observation.value.trim()) return false;
            return fields.every(field => {
                const input = tr.querySelector(selectors[field]);
                return input && input.value.trim() === '';
            });
        });
    }

    function appendRows(rows, tableBody, add) {
        const created = [];
        const filled = [];
        const empty = availableEmptyRows(tableBody);
        try {
            rows.forEach(row => {
                let target = empty.shift();
                if (target) {
                    filled.push(target);
                } else {
                    const previous = tableBody.lastElementChild;
                    const count = tableBody.querySelectorAll('tr').length;
                    add.click();
                    target = tableBody.lastElementChild;
                    if (tableBody.querySelectorAll('tr').length !== count + 1 || target === previous) {
                        throw new Error('The manual row builder is unavailable. Reload the page and retry; existing steps were not changed.');
                    }
                    created.push(target);
                }
                const controls = fields.map(field => target.querySelector(selectors[field]));
                if (controls.some(control => !control)) throw new Error('The new step could not be prepared. Reload and retry.');
                fields.forEach((field, i) => { controls[i].value = row[field]; });
            });
        } catch (error) {
            created.forEach(tr => tr.remove());
            filled.forEach(tr => fields.forEach(field => { tr.querySelector(selectors[field]).value = ''; }));
            throw error;
        }
    }

    if (typeof module !== 'undefined' && module.exports) module.exports = {validateRows, appendRows, availableEmptyRows, domains};
    if (typeof document === 'undefined') return;

    function bind() {
        const panel = document.getElementById('iepStepsImport');
        if (!panel) return;
        const fileInput = document.getElementById('iepStepsImportFile');
        const preview = document.getElementById('iepStepsImportPreview');
        const review = document.getElementById('iepStepsImportReview');
        const body = document.getElementById('iepStepsImportRows');
        const message = document.getElementById('iepStepsImportMessage');
        const confirm = document.getElementById('iepStepsImportConfirm');
        const reset = () => { body.replaceChildren(); review.hidden = true; message.textContent = ''; };
        fileInput.addEventListener('change', reset);
        document.getElementById('iepStepsImportCancel').addEventListener('click', reset);

        preview.addEventListener('click', async () => {
            reset();
            const file = fileInput.files[0];
            if (!file || !/\.(docx|xlsx)$/i.test(file.name) || file.size === 0 || file.size > 5 * 1024 * 1024) {
                message.textContent = 'Choose a non-empty .docx or .xlsx file no larger than 5 MB.';
                return;
            }
            preview.disabled = true;
            fileInput.disabled = true;
            message.textContent = 'Reading file locally…';
            try {
                const data = new FormData();
                data.append('iep_id', panel.dataset.iepId);
                data.append('import_csrf', panel.dataset.csrf);
                data.append('import_file', file);
                const response = await fetch(panel.dataset.url, {method:'POST', body:data, credentials:'same-origin', cache:'no-store'});
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'The file could not be read.');
                if (String(result.iep_id) !== panel.dataset.iepId || !Array.isArray(result.steps)) throw new Error('The preview does not belong to this IEP. Reload and try again.');
                result.steps.forEach(row => {
                    const tr = document.createElement('tr');
                    fields.forEach(field => {
                        const td = document.createElement('td');
                        const input = document.createElement(field === 'pdsp_indicator_text' ? 'select' : 'textarea');
                        input.className = 'form-control form-control-sm';
                        input.dataset.field = field;
                        input.setAttribute('aria-label', field.replace(/_/g, ' '));
                        if (field === 'pdsp_indicator_text') {
                            ['', ...domains].forEach(domain => {
                                const option = document.createElement('option');
                                option.value = domain;
                                option.textContent = domain || 'Select domain';
                                input.appendChild(option);
                            });
                        } else { input.rows = 2; input.maxLength = 5000; }
                        input.value = row[field] || '';
                        td.appendChild(input); tr.appendChild(td);
                    });
                    const td = document.createElement('td');
                    const remove = document.createElement('button');
                    remove.type = 'button'; remove.className = 'btn btn-sm btn-outline-secondary rounded';
                    remove.textContent = 'Remove';
                    remove.addEventListener('click', () => tr.remove());
                    td.appendChild(remove); tr.appendChild(td); body.appendChild(tr);
                });
                message.className = 'small mt-2 ' + (result.warnings && result.warnings.length ? 'text-warning fw-semibold' : 'text-success fw-semibold');
                message.textContent = (result.warnings || []).join(' ') || (result.steps.length + ' step objective' + (result.steps.length === 1 ? '' : 's') + ' extracted successfully.');
                review.hidden = false;
            } catch (error) {
                message.className = 'small mt-2 text-danger fw-semibold';
                message.textContent = error.message || 'The file could not be read. Please retry.';
            }
            finally { preview.disabled = false; fileInput.disabled = false; }
        });

        confirm.addEventListener('click', () => {
            try {
                const tableBody = document.getElementById('iepStepsTbody');
                const add = document.getElementById('iepStepsAddRow');
                if (!tableBody || !add) throw new Error('The Step Objectives form is not editable.');
                const imported = Array.from(body.querySelectorAll('tr')).map(tr => {
                    const row = {};
                    fields.forEach(field => { row[field] = tr.querySelector('[data-field="' + field + '"]').value; });
                    return row;
                });
                const rows = validateRows(imported,
                    Array.from(tableBody.querySelectorAll('.step-objective')).map(input => input.value),
                    tableBody.querySelectorAll('tr').length - availableEmptyRows(tableBody).length);
                confirm.disabled = true;
                // Reuse the manual row builder and its existing save/validation handlers.
                appendRows(rows, tableBody, add);
                reset();
                message.textContent = rows.length + ' step(s) added to this form. Review them below, then use Save steps. Nothing has been saved yet.';
            } catch (error) { message.textContent = error.message; }
            finally { confirm.disabled = false; }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();
