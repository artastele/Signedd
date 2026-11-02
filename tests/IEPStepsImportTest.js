'use strict';
const assert = require('node:assert/strict');
const {validateRows, appendRows, availableEmptyRows, domains} = require('../public/js/iep-steps-import.js');
let checks = 0;
const row = {step_objective:'Identify signs', pdsp_indicator_text:'Language Development', duration_lp:'2 weeks', instructional_evaluation:'Teacher observation'};
function check(action) { action(); checks++; }
check(() => assert.deepEqual(validateRows([row], [], 0), [row]));
check(() => assert.throws(() => validateRows([], [], 1), /at least one/));
check(() => assert.throws(() => validateRows([row], [' IDENTIFY  signs '], 1), /duplicated/));
check(() => assert.throws(() => validateRows([row, {...row, step_objective:' identify SIGNS '}], [], 0), /duplicated/));
check(() => assert.throws(() => validateRows([{...row, step_objective:'  '}], [], 0), /needs a Step Objective/));
check(() => assert.throws(() => validateRows([{...row, pdsp_indicator_text:''}], [], 0), /Select a developmental domain/));
check(() => assert.throws(() => validateRows([{...row, pdsp_indicator_text:'Invented domain'}], [], 0), /Select a developmental domain/));
check(() => assert.throws(() => validateRows([{...row, duration_lp:'x'.repeat(5001)}], [], 0), /5,000/));
check(() => assert.throws(() => validateRows([row], [], 10), /maximum of 10/));
check(() => assert.equal(validateRows([row], ['Other objective'], 9).length, 1));
check(() => {
    const existing = ['Keep this teacher objective'];
    const imported = [{...row, id:99, iep_id:666, observation:'Do not import', rating:'P'}];
    const result = validateRows(imported, existing, 1);
    assert.deepEqual(existing, ['Keep this teacher objective']);
    assert.deepEqual(result, [row]);
    assert.equal(imported[0].id, 99);
});
check(() => {
    const corrected = {...row, step_objective:'Corrected by teacher', duration_lp:'4 weeks'};
    assert.equal(validateRows([corrected], [row.step_objective], 1)[0].step_objective, 'Corrected by teacher');
});
check(() => assert.equal(domains.length, 8));
function fakeForm() {
    const rows = [];
    const table = {querySelectorAll:() => rows, get lastElementChild() { return rows.at(-1); }};
    const add = {click() {
        const values = {};
        ['.step-objective','.step-pdsp-indicator','.step-strategies','.step-evaluation'].forEach(selector => { values[selector] = {value:''}; });
        rows.push({getAttribute:() => null, querySelector:selector => values[selector], remove() { rows.splice(rows.indexOf(this), 1); }});
    }};
    add.click();
    rows[0].querySelector('.step-objective').value = 'Keep teacher objective';
    return {rows, table, add};
}
check(() => {
    const {rows, table, add} = fakeForm();
    appendRows([row], table, add);
    assert.equal(rows.length, 2);
    assert.equal(rows[0].querySelector('.step-objective').value, 'Keep teacher objective');
    assert.equal(rows[1].querySelector('.step-objective').value, row.step_objective);
    assert.equal(rows[1].querySelector('.step-evaluation').value, row.instructional_evaluation);
});
check(() => {
    const {rows, table} = fakeForm();
    assert.throws(() => appendRows([row], table, {click() {}}), /row builder is unavailable/);
    assert.equal(rows.length, 1);
    assert.equal(rows[0].querySelector('.step-objective').value, 'Keep teacher objective');
});
check(() => {
    const {rows, table} = fakeForm();
    rows[0].querySelector('.step-objective').value = '';
    rows[0].getAttribute = name => name === 'data-import-placeholder' ? '1' : '55';
    assert.equal(availableEmptyRows(table).length, 1);
    appendRows([row], table, {click() { throw new Error('Blank placeholder must be reused'); }});
    assert.equal(rows.length, 1);
    assert.equal(rows[0].querySelector('.step-objective').value, row.step_objective);
    assert.equal(rows[0].getAttribute('data-step-id'), '55', 'Existing placeholder identity preserved');
});
check(() => {
    const {rows, table} = fakeForm();
    rows[0].getAttribute = () => '1';
    rows[0].querySelector('.step-objective').value = '';
    rows[0].querySelector('.step-strategies').value = 'Teacher entered duration';
    assert.equal(availableEmptyRows(table).length, 0, 'Partially completed steps cannot be reused');
});
check(() => {
    const {rows, table, add} = fakeForm();
    let calls = 0;
    assert.throws(() => appendRows([row, row], table, {click() { if (++calls === 1) add.click(); }}), /row builder/);
    assert.equal(rows.length, 1, 'Partial append rolled back without touching existing rows');
    assert.equal(rows[0].querySelector('.step-objective').value, 'Keep teacher objective');
});
console.log(`PASS: ${checks} import form validation checks.`);
