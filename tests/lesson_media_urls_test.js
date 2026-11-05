const assert = require('node:assert/strict');
const {resolveUrl} = require('../public/js/lesson-media.js');
const token = 'a'.repeat(64);
const path = 'finance/media/' + token;
assert.equal(resolveUrl(path, '/Signedd/public'), '/Signedd/public/' + path);
assert.equal(resolveUrl(path, '/Signedd/public/'), '/Signedd/public/' + path);
assert.equal(resolveUrl('./' + path, '/Signedd/public'), '/Signedd/public/' + path);
assert.equal(resolveUrl(path, ''), '/' + path);
assert.equal(resolveUrl(path, 'http://localhost:8888/Signedd/public'), 'http://localhost:8888/Signedd/public/' + path);
for (const value of ['/Signedd/public/' + path, '/' + path, 'https://example.com/' + path, 'data:image/png;base64,AAAA', 'uploads/lesson_editor/colors.jpg', 'finance/media/../bad', 'finance/media/short']) {
    assert.equal(resolveUrl(value, '/Signedd/public'), value, 'Only valid relative private paths are rewritten');
}
const editor = 'http://localhost:8888/Signedd/public/iep/implementation/lesson/17/builder';
assert.equal(new URL(resolveUrl(path, '/Signedd/public'), editor).pathname, '/Signedd/public/' + path);
assert.notEqual(new URL(path, editor).pathname, '/Signedd/public/' + path, 'Raw relative path reproduces the broken nested editor URL');
console.log('PASS: Private media URLs resolve under the app root in nested editor/preview pages and root hosting.');
