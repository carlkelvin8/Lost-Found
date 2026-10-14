const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function input(value = '') {
    return { value, checked: false, disabled: false, required: false, validity: '', listeners: {}, addEventListener(type, handler) { this.listeners[type] = handler; }, setCustomValidity(message) { this.validity = message; } };
}

function setup() {
    const type = input('found');
    const checkbox = input();
    const defaultInput = input('0');
    const name = input();
    const studentId = input();
    const hidden = {};
    const form = { elements: { report_type: type, finder_name: name, finder_student_id: studentId }, addEventListener() {} };
    const section = { closest() { return form; }, classList: { toggle(className, value) { hidden.section = value; } } };
    const nodes = {
        studentFinderSection: section, studentSurrenderCheckbox: checkbox, studentSurrenderDefault: defaultInput,
        studentFinderFields: { classList: { toggle(className, value) { hidden.fields = value; } } },
    };
    const document = { addEventListener(event, handler) { handler(); }, getElementById(id) { return nodes[id]; } };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/finder-details.js'), 'utf8'), { document });
    return { type, checkbox, defaultInput, name, studentId, hidden, refresh: () => checkbox.listeners.input() };
}

test('student surrender requires name and student ID, including whitespace validation', () => {
    const app = setup();
    app.checkbox.checked = true;
    app.name.value = '   ';
    app.studentId.value = '   ';
    app.refresh();
    assert.equal(app.name.required, true);
    assert.equal(app.studentId.required, true);
    assert.notEqual(app.name.validity, '');
    assert.notEqual(app.studentId.validity, '');
    app.name.value = 'Juan Dela Cruz';
    app.studentId.value = '001-2026';
    app.refresh();
    assert.equal(app.name.validity, '');
    assert.equal(app.studentId.validity, '');
});

test('staff self-found reports do not require or submit student details', () => {
    const app = setup();
    assert.equal(app.name.required, false);
    assert.equal(app.studentId.disabled, true);
    assert.equal(app.hidden.fields, true);
});

test('Lost reports disable all surrender fields and preserve entered details on switching back', () => {
    const app = setup();
    app.checkbox.checked = true;
    app.name.value = 'Juan';
    app.studentId.value = '001';
    app.type.value = 'lost';
    app.refresh();
    assert.equal(app.checkbox.disabled, true);
    assert.equal(app.defaultInput.disabled, true);
    assert.equal(app.name.disabled, true);
    assert.equal(app.hidden.section, true);
    app.type.value = 'found';
    app.refresh();
    assert.equal(app.name.value, 'Juan');
    assert.equal(app.studentId.value, '001');
    assert.equal(app.studentId.required, true);
    assert.equal(app.name.disabled, false);
});

test('unchecking student surrender clears validation and omits its fields from submission', () => {
    const app = setup();
    app.checkbox.checked = true;
    app.refresh();
    app.checkbox.checked = false;
    app.refresh();
    assert.equal(app.name.validity, '');
    assert.equal(app.studentId.validity, '');
    assert.equal(app.name.disabled, true);
    assert.equal(app.studentId.disabled, true);
});
