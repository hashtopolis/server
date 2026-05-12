import base64
import json
import time

import requests

from hashtopolis import ApiToken, HashtopolisConfig, HashtopolisConnector, HashtopolisError
from utils import BaseTest, create_restricted_user


def _decode_jwt_scope(jwt_token):
    """Decode a JWT and return its `scope` claim as a parsed dict.

    The `scope` claim is a JSON-encoded string keyed by modern CRUD
    permission names (e.g. `permHashlistRead`).
    """
    payload_b64 = jwt_token.split('.')[1]
    padded = payload_b64 + '=' * (-len(payload_b64) % 4)
    payload = json.loads(base64.urlsafe_b64decode(padded))
    return json.loads(payload['scope'])


class ApiTokenTest(BaseTest):
    model_class = ApiToken

    def create_test_object(self, *nargs, **kwargs):
        return self.create_apitoken(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object(delete=False)
        self._test_create(model_obj)

    def test_token_returned_on_create(self):
        model_obj = self.create_test_object(delete=False)
        # The JWT token string is only present in the POST response
        self.assertTrue(hasattr(model_obj, 'token'))
        self.assertIsNotNone(model_obj.token)
        self.assertIsInstance(model_obj.token, str)
        self.assertGreater(len(model_obj.token), 0)

    def test_token_not_in_get(self):
        model_obj = self.create_test_object(delete=False)
        # Retrieve the object via GET and verify the token field is absent
        obj = self.model_class.objects.get(pk=model_obj.id)
        self.assertFalse(hasattr(obj, 'token') and obj.token is not None)

    def test_revoke(self):
        model_obj = self.create_test_object(delete=False)
        self._test_patch(model_obj, 'isRevoked', True)

    def test_expand_user(self):
        model_obj = self.create_test_object(delete=False)
        self._test_expandables(model_obj, ['user'])

    def test_patch_readonly_startValid(self):
        model_obj = self.create_test_object(delete=False)
        model_obj.startValid = 0
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 403)
        self.assertIn('startValid', e.exception.title)

    def test_patch_readonly_endValid(self):
        model_obj = self.create_test_object(delete=False)
        model_obj.endValid = 9999999999
        with self.assertRaises(HashtopolisError) as e:
            model_obj.save()
        self.assertEqual(e.exception.status_code, 403)
        self.assertIn('endValid', e.exception.title)

    def test_acl(self):
        # Admin's token should not be visible to a different user
        model_obj = self.create_test_object(delete=False)
        self._test_acl_list(model_obj, {'permJwtApiKeyRead': True})

    def test_scope_admin_grants_requested_crud_perm(self):
        # Admin requests one CRUD scope; the JWT scope must grant exactly that
        # perm (and nothing else) and must NOT be empty (the original admin bug).
        model_obj = self.create_test_object(
            delete=False,
            extra_payload={'scopes': ['permHashlistRead']},
        )
        scope = _decode_jwt_scope(model_obj.token)

        self.assertIn('permHashlistRead', scope)
        self.assertTrue(scope['permHashlistRead'])

        # Other CRUD perms must be False
        self.assertFalse(scope['permAgentCreate'])
        self.assertFalse(scope['permFileUpdate'])
        self.assertFalse(scope['permJwtApiKeyRead'])

        # And the scope dict is NOT empty (the bug symptom for admin)
        self.assertTrue(any(scope.values()), "Admin scope must not be empty after requesting a valid CRUD perm")

    def test_scope_multiple_scopes_union(self):
        # Multiple CRUD scopes are all granted
        model_obj = self.create_test_object(
            delete=False,
            extra_payload={'scopes': ['permHashlistRead', 'permAgentCreate']},
        )
        scope = _decode_jwt_scope(model_obj.token)

        self.assertTrue(scope['permHashlistRead'])
        self.assertTrue(scope['permAgentCreate'])
        self.assertFalse(scope['permFileUpdate'])
        self.assertFalse(scope['permJwtApiKeyRead'])

    def test_scope_unknown_scope_yields_no_grants(self):
        # An unrecognised scope must not error and must not flip any key
        model_obj = self.create_test_object(
            delete=False,
            extra_payload={'scopes': ['definitelyNotARealPermission']},
        )
        scope = _decode_jwt_scope(model_obj.token)

        # Every key is False — and the dict still has the full keyset
        self.assertGreater(len(scope), 0)
        self.assertTrue(all(v is False for v in scope.values()),
                        f"Unknown scope must not grant anything, got: {scope}")

    def test_scope_drops_perms_user_lacks(self):
        # A restricted user with only permHashlistRead must NOT be able to mint
        # a token that grants permAgentCreate, even if they ask for it.
        auth = create_restricted_user(self, {'permHashlistRead': True})

        config = HashtopolisConfig()
        conn = HashtopolisConnector('/auth/token', config)
        r = requests.post(conn._api_endpoint + '/auth/token', auth=auth)
        self.assertEqual(r.status_code, 200, f"Login failed: {r.text}")
        user_jwt = r.json()['token']

        # Create an API token as this restricted user requesting BOTH a perm
        # they have AND a perm they don't.
        now = int(time.time())
        payload = {'scopes': ['permHashlistRead', 'permAgentCreate'], 'startValid': now, 'endValid': now + 3600}
        r = requests.post(
            conn._api_endpoint + '/ui/apiTokens',
            headers={'Authorization': 'Bearer ' + user_jwt, 'Content-Type': 'application/json'},
            data=json.dumps(payload),
        )
        self.assertEqual(r.status_code, 201, f"Restricted-user token creation failed: {r.text}")
        body = r.json()
        token = body['data']['attributes']['token'] if 'data' in body else body.get('token')
        self.assertIsNotNone(token, f"Expected token in response, got: {body}")

        scope = _decode_jwt_scope(token)
        # Hashlist-read is granted (user has it)
        self.assertTrue(scope['permHashlistRead'])
        # Agent-create is NOT granted (user lacks it)
        self.assertFalse(scope['permAgentCreate'])

    def test_scope_intersection_token_works_as_bearer(self):
        # End-to-end: a token issued for permHashlistRead must successfully
        # authorise GET /ui/hashlists for the admin caller.
        model_obj = self.create_test_object(
            delete=False,
            extra_payload={'scopes': ['permHashlistRead']},
        )
        config = HashtopolisConfig()
        conn = HashtopolisConnector('/ui/hashlists', config)
        r = requests.get(
            conn._api_endpoint + '/ui/hashlists',
            headers={'Authorization': 'Bearer ' + model_obj.token},
        )
        self.assertEqual(r.status_code, 200,
                         f"Bearer with permHashlistRead should authorise listing hashlists; got {r.status_code}: {r.text}")
