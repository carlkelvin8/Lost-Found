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
import sys
from pathlib import Path

BASE = 'http://127.0.0.1:8765'
DB_NAME = sys.argv[1] if len(sys.argv) > 1 else 'client-audit.sqlite'
assert DB_NAME in ['client-audit.sqlite', 'client-followup.sqlite']
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
db = Path(__file__).resolve().parents[2] / 'storage/framework/testing' / DB_NAME
with sqlite3.connect(f'file:{db.as_posix()}?mode=ro', uri=True) as connection:
    assert connection.execute('SELECT count(*) FROM item_reports WHERE submission_id = ?', (identifier,)).fetchone()[0] == 1
print('PASS: 8 overlapping HTTP retries return one report (1 created, 7 replayed)')

assert post('/logout', {'_token': token})[0] == 200
assert 'Audit Admin' in get('/admin/dashboard')
assert 'Sign in to' in get('/dashboard')
print('PASS: user logout leaves the admin session authenticated')


# Three simultaneous account contexts, including two accounts with the same role.
prefixes = []
for email in ['audit.user@gmail.com', 'audit.user.two@gmail.com', 'audit.admin@gmail.com']:
    with client.open(BASE + '/account/new-session') as response:
        login_url = response.geturl()
        login_html = response.read().decode()
    prefix = urllib.parse.urlparse(login_url).path.removesuffix('/login')
    assert re.fullmatch(r'/session/[a-f0-9-]{36}', prefix)
    token = csrf(login_html)
    status, html = post(prefix + '/login', {'_token': token, 'email': email, 'password': 'Audit-Test-2026!', 'remember': '1'})
    assert status == 200 and prefix + '/logout' in html
    prefixes.append(prefix)
assert len(set(prefixes)) == 3
assert 'Good evening, Audit User' in get(prefixes[0] + '/dashboard') or 'Audit User' in get(prefixes[0] + '/dashboard')
assert 'Audit User Two' in get(prefixes[1] + '/dashboard')
assert 'Audit Admin' in get(prefixes[2] + '/dashboard')
scoped_remember = [cookie for cookie in jar if cookie.name.startswith('remember_isolated_')]
assert len(scoped_remember) == 3
assert all(cookie.path in prefixes for cookie in scoped_remember)
print('PASS: 3 scoped accounts, including 2 students, retain separate identities and remember cookies')

first_token = csrf(get(prefixes[0] + '/reports/create'))
second_token = csrf(get(prefixes[1] + '/reports/create'))
assert post(prefixes[1] + '/reports', dict(payload, _token=first_token), True)[0] == 419
try:
    client.open(urllib.request.Request(BASE + prefixes[0] + '/matches', headers={'Accept': 'application/json'}))
    raise AssertionError('Student reached staff matching endpoint')
except urllib.error.HTTPError as error:
    assert error.code == 403
anonymous = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
try:
    anonymous.open(urllib.request.Request(BASE + prefixes[2] + '/dashboard', headers={'Accept': 'application/json'}))
    raise AssertionError('Context UUID authenticated without a session cookie')
except urllib.error.HTTPError as error:
    assert error.code == 401
print('PASS: scoped CSRF, staff permissions and non-credential context UUID are enforced')

assert post(prefixes[1] + '/logout', {'_token': second_token})[0] == 200
assert 'Audit User' in get(prefixes[0] + '/dashboard')
assert 'Audit Admin' in get(prefixes[2] + '/dashboard')
assert 'Sign in to' in get(prefixes[1] + '/dashboard')
print('PASS: logging out one student preserves the other student and admin contexts')

# Use the scoped Admin account to create a surrendered report without Contact Override.
admin_token = csrf(get(prefixes[2] + '/reports/create'))
surrender = {'_token': admin_token, 'submission_id': str(uuid.uuid4()), 'report_type': 'found', 'item_description': 'A student surrendered this distinctive blue wallet.', 'surrendered_by_student': '1', 'finder_name': '  Audit Student Finder  ', 'finder_student_id': '  001-2026-42  '}
invalid = dict(surrender, finder_student_id='   ')
assert post(prefixes[2] + '/reports', invalid, True)[0] == 422
status, body = post(prefixes[2] + '/reports', surrender, True)
assert status == 201
report_url = json.loads(body)['redirect']
assert report_url.startswith(BASE + prefixes[2] + '/reports/')
report_html = client.open(report_url).read().decode()
assert 'Audit Student Finder' in report_html and '001-2026-42' in report_html
assert post(prefixes[2] + '/reports', surrender, True)[0] == 200
print('PASS: scoped Admin validates, saves, displays and deduplicates student finder details')
