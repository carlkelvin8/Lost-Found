const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup({ width = 4032, height = 3024, decode = true, sizes = [200000] } = {}) {
    const revoked = [], canvases = [], qualities = [], progress = [];
    class Image {
        naturalWidth = width;
        naturalHeight = height;
        set src(value) { if (value) queueMicrotask(() => decode ? this.onload() : this.onerror()); }
    }
    const root = {};
    const context = {
        window: root, File, Image, WeakMap,
        URL: { createObjectURL() { return 'blob:fixture'; }, revokeObjectURL(url) { revoked.push(url); } },
        document: { createElement() {
            const canvas = {
                width: 0, height: 0,
                getContext() { return { fillRect() {}, drawImage() {} }; },
                toBlob(callback, type, quality) { qualities.push(quality); callback(new Blob([new Uint8Array(sizes.shift() || 200000)], { type })); },
            };
            canvases.push(canvas);
            return canvas;
        } },
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/photo-optimizer.js'), 'utf8'), context);
    return { ...root.ReportPhotos, revoked, canvases, qualities, progress };
}

test('large phone image becomes a JPEG and releases object URL and canvas memory', async () => {
    const app = setup();
    const result = await app.optimize(new File(['source'], 'phone.heic', { type: 'image/heic' }));
    assert.equal(result.type, 'image/jpeg');
    assert.equal(result.name, 'phone.jpg');
    assert.equal(result.size, 200000);
    assert.equal(app.canvases[0].width, 0);
    assert.deepEqual(app.revoked, ['blob:fixture']);
});

test('compression reduces quality only when necessary and caches corrected retries', async () => {
    const app = setup({ sizes: [1900000, 1200000] });
    const file = new File(['source'], 'phone.webp', { type: 'image/webp' });
    const first = await app.prepare([file]);
    const retry = await app.prepare([file]);
    assert.equal(first[0], retry[0]);
    assert.deepEqual(app.qualities, [0.84, 0.72]);
});

test('unsupported HEIC decoding returns an actionable error and releases resources', async () => {
    const app = setup({ decode: false });
    await assert.rejects(app.optimize(new File(['heic'], 'phone.heic', { type: 'image/heic' })), /Most Compatible/);
    assert.equal(app.revoked.length, 1);
});

test('corrupt, tiny, excessive pixel counts, unsupported formats, and more than five files are rejected', async () => {
    await assert.rejects(setup({ decode: false }).optimize(new File(['bad'], 'bad.jpg')), /corrupted/);
    await assert.rejects(setup({ width: 20, height: 20 }).optimize(new File(['small'], 'small.png')), /at least 100/);
    await assert.rejects(setup({ width: 12000, height: 12000 }).optimize(new File(['bomb'], 'huge.png')), /48 megapixels/);
    await assert.rejects(setup().optimize(new File(['svg'], 'photo.svg', { type: 'image/svg+xml' })), /choose JPEG/);
    await assert.rejects(setup().prepare(Array(6).fill(new File(['source'], 'phone.jpg'))), /maximum of 5/);
});
