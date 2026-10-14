const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function input(value = '') {
    return { value, required: false, validity: '', listeners: {}, addEventListener(type, handler) { this.listeners[type] = handler; }, setCustomValidity(value) { this.validity = value; } };
}

function setup(fetch) {
    const elements = { report_type: input('found'), contact_override: input(), reporter_name: input('Test Finder'), submission_id: input('stable-submission-id'), photos: { files: [new File(['camera-bytes'], 'camera.jpg', { type: 'image/jpeg' })] } };
    const button = { disabled: false, innerHTML: 'Create Report', textContent: '' };
    const classList = { toggle() {}, add() {}, remove() {} };
    const feedback = { classList, textContent: '', focus() {} };
    const form = { elements, action: '/reports', valid: true, querySelector() { return button; }, reportValidity() { return this.valid && !elements.reporter_name.validity; }, addEventListener(type, handler) { this[type] = handler; } };
    const redirects = [];
    const bodies = [];
    const document = { addEventListener(type, handler) { handler(); }, getElementById(id) { if (id === 'studentSurrenderCheckbox') return null; return id === 'reportCreateForm' ? form : id === 'reportSubmitFeedback' ? feedback : { classList }; } };
    class FakeFormData {
        constructor(source) {
            this.file = source.elements.photos.files[0];
            this.id = source.elements.submission_id.value;
            bodies.push(this);
        }
    }
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/report-create.js'), 'utf8'), { document, fetch, FormData: FakeFormData, window: { location: { assign(url) { redirects.push(url); } } } });
    return { elements, button, feedback, form, redirects, bodies, submit: () => form.submit({ preventDefault() {} }) };
}

test('two submissions while a request is pending send only one request', async () => {
    let resolve;
    let calls = 0;
    const app = setup(() => { calls++; return new Promise(done => { resolve = done; }); });
    const first = app.submit();
    await app.submit();
    assert.equal(calls, 1);
    assert.equal(app.button.disabled, true);
    resolve({ ok: true, json: async () => ({ redirect: '/reports/1' }) });
    await first;
    assert.deepEqual(app.redirects, ['/reports/1']);
});

test('server validation preserves the camera file and identifier for corrected retry', async () => {
    let calls = 0;
    const app = setup(async () => ++calls === 1
        ? { ok: false, json: async () => ({ errors: { item_description: ['Description too short.'] } }) }
        : { ok: true, json: async () => ({ redirect: '/reports/1' }) });
    const photo = app.elements.photos.files[0];
    await app.submit();
    assert.equal(app.feedback.textContent, 'Description too short.');
    assert.equal(app.button.disabled, false);
    assert.equal(app.elements.photos.files[0], photo);
    await app.submit();
    assert.equal(app.bodies[0].file, app.bodies[1].file);
    assert.equal(app.bodies[0].id, app.bodies[1].id);
    assert.deepEqual(app.redirects, ['/reports/1']);
});

test('ordinary client validation keeps photos and sends no request', async () => {
    let calls = 0;
    const app = setup(() => calls++);
    app.form.valid = false;
    const photo = app.elements.photos.files[0];
    await app.submit();
    assert.equal(calls, 0);
    assert.equal(app.elements.photos.files[0], photo);
});

test('Found requires a name without Contact Override, Lost disables it', async () => {
    let calls = 0;
    const app = setup(async () => { calls++; return { ok: true, json: async () => ({ redirect: '/reports/1' }) }; });
    app.elements.contact_override.value = '';
    app.elements.reporter_name.value = '   ';
    await app.submit();
    assert.equal(calls, 0);
    assert.equal(app.elements.reporter_name.required, true);
    app.elements.report_type.value = 'lost';
    await app.submit();
    assert.equal(calls, 1);
    assert.equal(app.elements.reporter_name.required, false);
    assert.equal(app.elements.reporter_name.disabled, true);
});

test('network errors keep the camera file and allow retry', async () => {
    const app = setup(async () => { throw new Error('Network failed'); });
    const photo = app.elements.photos.files[0];
    await app.submit();
    assert.equal(app.elements.photos.files[0], photo);
    assert.equal(app.button.disabled, false);
    assert.equal(app.feedback.textContent, 'Network failed');
});

test('camera Done copies captured bytes to the upload input before cleaning camera URLs', async () => {
    const selected = { files: [], dispatchEvent() {} };
    const context = {
        module: { exports: {} },
        document: { querySelector() { return selected; } },
        DataTransfer: class { constructor() { this.files = []; this.items = { add: file => this.files.push(file) }; } },
        File, Event, URL,
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/camera-capture.js'), 'utf8'), context);
    const camera = new context.module.exports.CameraCapture();
    camera.capturedPhotos = [{ blob: new Blob(['captured-bytes'], { type: 'image/jpeg' }), timestamp: new Date(), url: 'blob:test' }];
    camera.done();
    assert.equal(selected.files.length, 1);
    assert.equal(await selected.files[0].text(), 'captured-bytes');
    assert.equal(camera.capturedPhotos.length, 0);
});
