from hashtopolis import ApiToken, HashtopolisError
from utils import BaseTest


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
