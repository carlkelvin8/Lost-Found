const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

async function setup(responses) {
    const alerts = [], urls = [];
    const badge = { textContent: '', style: {} };
    const link = { classList: { toggle() {} }, setAttribute() {} };
    const toast = { info(message) { alerts.push(message); } };
    const window = { appUrl: p => '/admin' + p, location: { pathname: '/admin/dashboard' }, toast, addEventListener() {} };
    const context = {
        window, toast, navigator: {}, setInterval() { return 1; }, clearInterval() {},
        document: {
            addEventListener() {}, querySelector() { return null; },
            querySelectorAll(selector) { return selector === '.notification-badge' ? [badge] : selector.startsWith('.navbar-icon-btn') ? [link] : []; },
        },
        async fetch(url) {
            if (!url.includes('/notifications/check')) return { ok: true };
            urls.push(url);
            const data = responses.shift();
            return { ok: true, json: async () => data };
        },
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/realtime.js'), 'utf8'), context);
    await new Promise(resolve => setImmediate(resolve));
    return { app: window.realTime, alerts, urls, badge };
}

test('existing notifications establish a baseline and unchanged polls stay silent', async () => {
    const state = await setup([{ count: 2, latest: 64, new_count: 0 }, { count: 2, latest: 64, new_count: 0 }]);
    await state.app.checkNotifications();
    assert.deepEqual(state.alerts, []);
    assert.equal(state.urls[1], '/admin/notifications/check?after_id=64');
});

test('a new event displays its actual count once, and zero unread hides the badge', async () => {
    const state = await setup([
        { count: 0, latest: 0, new_count: 0 },
        { count: 1, latest: 65, new_count: 1 },
        { count: 1, latest: 65, new_count: 0 },
        { count: 0, latest: 65, new_count: 0 },
    ]);
    assert.equal(state.badge.style.display, 'none');
    await state.app.checkNotifications();
    assert.equal(state.badge.style.display, 'flex');
    await state.app.checkNotifications();
    await state.app.checkNotifications();
    assert.deepEqual(state.alerts, ['You have 1 new notification']);
    assert.equal(state.badge.style.display, 'none');
});
