"""Run against prepare-browser-audit.php fixtures and the isolated :8765 server."""
import concurrent.futures
import http.cookiejar
import json
import re
import sqlite3
import urllib.error
import urllib.parse
import urllib.request
import uuid
from pathlib import Path

BASE = 'http://127.0.0.1:8765'
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))


def get(path):
    return client.open(BASE + path).read().decode()


def csrf(html):
    return re.search(r'name="_token" value="([^"]+)"', html).group(1)


def post(path, data, wants_json=False):
    headers = {'Accept': 'application/json'} if wants_json else {}
    request = urllib.request.Request(BASE + path, urllib.parse.urlencode(data).encode(), headers=headers)
    try:
        with client.open(request) as response:
            return response.status, response.read().decode()
    except urllib.error.HTTPError as error:
        return error.code, error.read().decode()


token = csrf(get('/admin/login'))
status, html = post('/admin/login', {'_token': token, 'email': 'audit.admin@gmail.com', 'password': 'Audit-Test-2026!'})
assert status == 200 and 'Audit Admin' in html
user_token = csrf(get('/login'))
status, html = post('/login', {'_token': user_token, 'email': 'audit.user@gmail.com', 'password': 'Audit-Test-2026!'})
assert status == 200 and 'Audit User' in html
assert 'Audit Admin' in get('/admin/dashboard')
assert 'Audit User' in get('/dashboard')
assert any(cookie.name == 'client_audit_session_admin' and cookie.path == '/admin' for cookie in jar)
assert any(cookie.name == 'client_audit_session' and cookie.path == '/' for cookie in jar)
print('PASS: shared cookie jar retains two independent identities after refresh')

form = get('/reports/create')
token = csrf(form)
identifier = str(uuid.uuid4())
payload = {'_token': token, 'submission_id': identifier, 'report_type': 'lost', 'item_description': 'Concurrent HTTP audit test of a distinctive umbrella.'}
assert post('/reports', dict(payload, _token='invalid'), True)[0] == 419
assert post('/admin/reports', payload, True)[0] == 419
print('PASS: missing/incorrect and cross-boundary CSRF tokens are rejected')

# Snapshot the same cookies for all overlapping requests, like one browser's retries.
cookie_header = '; '.join(f'{cookie.name}={cookie.value}' for cookie in jar if cookie.path == '/')


def retry(_):
    request = urllib.request.Request(BASE + '/reports', urllib.parse.urlencode(payload).encode(), headers={'Accept': 'application/json', 'Cookie': cookie_header})
    with urllib.request.urlopen(request) as response:
        return response.status, json.loads(response.read())['redirect']


with concurrent.futures.ThreadPoolExecutor(max_workers=8) as executor:
    replies = list(executor.map(retry, range(8)))
assert sum(status == 201 for status, _ in replies) == 1, replies
assert len(set(url for _, url in replies)) == 1, replies
db = Path(__file__).resolve().parents[2] / 'storage/framework/testing/client-audit.sqlite'
with sqlite3.connect(f'file:{db.as_posix()}?mode=ro', uri=True) as connection:
    assert connection.execute('SELECT count(*) FROM item_reports WHERE submission_id = ?', (identifier,)).fetchone()[0] == 1
print('PASS: 8 overlapping HTTP retries return one report (1 created, 7 replayed)')

assert post('/logout', {'_token': token})[0] == 200
assert 'Audit Admin' in get('/admin/dashboard')
assert 'Sign in to' in get('/dashboard')
print('PASS: user logout leaves the admin session authenticated')
