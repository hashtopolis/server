import time

import requests

from utils import BaseTest, get_test_config, get_hashtopolis_uri

AUTH_URI = get_hashtopolis_uri() + '/api/v2/auth/token'
REFRESH_URI = get_hashtopolis_uri() + '/api/v2/auth/refresh'

COOKIE_NAME = 'refreshToken'
COOKIE_PATH = '/api/v2/auth/refresh'


def _credentials():
    cfg = get_test_config()
    return cfg['username'], cfg['password']


def _login(session=None):
    """Log in and return the response; the refresh token lands in the session's cookie jar."""
    session = session or requests.Session()
    response = session.post(AUTH_URI, auth=_credentials())
    return session, response


def _refresh_token_of(session):
    return session.cookies.get(COOKIE_NAME, path=COOKIE_PATH)


class RefreshTokenTest(BaseTest):
    def test_login_sets_refresh_cookie(self):
        session, response = _login()
        self.assertEqual(response.status_code, 201, msg=response.text)

        cookie = response.headers['Set-Cookie']
        self.assertIn(COOKIE_NAME + '=', cookie)
        self.assertIn('HttpOnly', cookie)
        self.assertIn('SameSite=', cookie)
        self.assertIn('Path=' + COOKIE_PATH, cookie)
        self.assertIsNotNone(_refresh_token_of(session))

    def test_cookie_is_secure_over_https(self):
        """Secure follows the scheme, so that a plain HTTP deployment does not lose the cookie."""
        _, response = _login()
        cookie = response.headers['Set-Cookie']

        if get_hashtopolis_uri().startswith('https://'):
            self.assertIn('Secure', cookie)
        else:
            self.assertNotIn('Secure', cookie)

    def test_login_body_never_carries_the_refresh_token(self):
        """The token is HttpOnly-cookie-only on purpose, so that XSS cannot read it."""
        session, response = _login()
        body = response.json()

        self.assertIn('token', body)
        self.assertIn('expires', body)
        self.assertNotIn('refreshToken', body)
        self.assertNotIn(_refresh_token_of(session), response.text)

    def test_refresh_returns_a_usable_access_token(self):
        session, login = _login()

        response = session.post(REFRESH_URI)
        self.assertEqual(response.status_code, 201, msg=response.text)

        body = response.json()
        self.assertIn('token', body)
        self.assertIn('expires', body)
        self.assertNotEqual(body['token'], login.json()['token'])

        probe = requests.get(get_hashtopolis_uri() + '/api/v2/ui/users',
                             headers={'Authorization': 'Bearer ' + body['token']})
        self.assertEqual(probe.status_code, 200, msg=probe.text)

    def test_refresh_needs_no_access_token(self):
        """The endpoint has to work once the access token is gone, so it must not require one."""
        session, _ = _login()

        response = session.post(REFRESH_URI, headers={'Authorization': 'Bearer not-a-token'})
        self.assertEqual(response.status_code, 201, msg=response.text)

    def test_refresh_rotates_the_cookie(self):
        session, _ = _login()
        before = _refresh_token_of(session)

        session.post(REFRESH_URI)
        after = _refresh_token_of(session)

        self.assertIsNotNone(after)
        self.assertNotEqual(before, after)

    def test_refresh_without_cookie_is_unauthorized(self):
        response = requests.post(REFRESH_URI)
        self.assertEqual(response.status_code, 401, msg=response.text)

    def test_refresh_with_unknown_token_is_unauthorized(self):
        response = requests.post(REFRESH_URI, cookies={COOKIE_NAME: 'not-a-token'})
        self.assertEqual(response.status_code, 401, msg=response.text)

    def test_replaying_a_consumed_token_kills_the_session(self):
        session, _ = _login()
        stolen = _refresh_token_of(session)

        self.assertEqual(session.post(REFRESH_URI).status_code, 201)
        successor = _refresh_token_of(session)

        # Outside the grace window a second exchange of the same token means it leaked
        time.sleep(11)
        replay = requests.post(REFRESH_URI, cookies={COOKIE_NAME: stolen})
        self.assertEqual(replay.status_code, 401, msg=replay.text)

        # ... which takes the untouched successor down with it
        after = requests.post(REFRESH_URI, cookies={COOKIE_NAME: successor})
        self.assertEqual(after.status_code, 401, msg=after.text)

    def test_sessions_are_independent(self):
        first, _ = _login()
        second, _ = _login()

        self.assertEqual(first.post(REFRESH_URI).status_code, 201)
        self.assertEqual(second.post(REFRESH_URI).status_code, 201)

    def test_logout_ends_only_its_own_session(self):
        kept, _ = _login()
        ended, _ = _login()

        response = ended.delete(REFRESH_URI)
        self.assertEqual(response.status_code, 204, msg=response.text)
        self.assertIn('Max-Age=0', response.headers['Set-Cookie'])

        self.assertEqual(requests.post(REFRESH_URI, cookies={COOKIE_NAME: _refresh_token_of(ended)}).status_code, 401)
        self.assertEqual(kept.post(REFRESH_URI).status_code, 201)

    def test_logout_without_cookie_is_not_an_error(self):
        self.assertEqual(requests.delete(REFRESH_URI).status_code, 204)
