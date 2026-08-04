import base64
import json

from hashtopolis import Model

from utils import ApiToken, BaseTest, create_apitoken_raw, create_restricted_user


def _decode_jwt_scope(token):
    """Decode the JWT payload (without signature verification) and return the parsed scope dict."""
    payload_b64 = token.split('.')[1]
    payload_b64 += '=' * (-len(payload_b64) % 4)
    payload = json.loads(base64.urlsafe_b64decode(payload_b64))
    return json.loads(payload['scope'])


class ApiTokenTest(BaseTest):
    model_class = ApiToken

    def create_test_object(self, *nargs, **kwargs):
        return self.create_apitoken(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['user']
        self._test_expandables(model_obj, expandables)

    def test_acl(self):
        model_obj = self.create_test_object()
        self._test_acl_list(model_obj, {'permJwtApiKeyRead': True})

    def test_token_scope_admin_grants_requested(self):
        """Admin holds every legacy permission, so any requested scope must be granted in the JWT."""
        model_obj = self.create_test_object()
        scope = _decode_jwt_scope(model_obj.token)
        self.assertTrue('permHashlistRead' in scope)

    def test_token_scope_intersection_grants_permitted(self):
        """A restricted user is granted a requested scope they hold via the legacy permission mapping."""
        auth = create_restricted_user(self, {
            'permHashlistRead': True,
            'permJwtApiKeyCreate': True,
        })
        model_obj = create_apitoken_raw(self, auth, ['permHashlistRead'])
        scope = _decode_jwt_scope(model_obj.token)
        self.assertTrue('permHashlistRead' in scope)

    def test_token_scope_intersection_denies_unpermitted(self):
        """A restricted user must NOT receive a scope they do not have, even if they request it."""
        auth = create_restricted_user(self, {
            'permHashlistRead': True,
            'permJwtApiKeyCreate': True,
        })
        model_obj = create_apitoken_raw(self, auth, ['permHashlistRead', 'permFileRead'])
        scope = _decode_jwt_scope(model_obj.token)
        self.assertTrue('permHashlistRead' in scope)
        self.assertFalse('permFileRead' not in scope)
