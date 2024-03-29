import requests
import json
import time

from hashtopolis import HashtopolisConnector, HashtopolisConfig, HashtopolisError
from hashtopolis import User
from utils import BaseTest


class AttributeTypeTest(BaseTest):
    def test_patch_read_only(self):
        # Test to verify that we cannot patch a read_only field
        stamp = int(time.time() * 1000)
        username = f'test-{stamp}'
        user = User(
            name=username,
            email='test@example.com',
            globalPermissionGroupId=1,
        )
        user.save()

        config = HashtopolisConfig()
        conn = HashtopolisConnector('/ui/users', config)
        conn.authenticate()

        headers = conn._headers

        uri = conn._api_endpoint + conn._model_uri + f'/{user.id}'
        payload = {}
        payload['passwordHash'] = 'test'
        r = requests.patch(uri, headers=headers, data=json.dumps(payload))

        self.assertEqual(r.status_code, 500)
        self.assertIn('immutable', r.json().get('exception')[0].get('message'))
        user.delete()

    def test_create_protected(self):
        stamp = int(time.time() * 1000)
        username = f'test-{stamp}'

        user = User(
            name=username,
            email='test@example.com',
            globalPermissionGroupId=1,
            passwordHash='test',
        )
        with self.assertRaises(HashtopolisError) as e:
            user.save()
        self.assertEqual(e.exception.status_code, 500)
        self.assertIn(' not valid input ', e.exception.exception_details[0]['message'])

    def test_get_private(self):
        stamp = int(time.time() * 1000)
        username = f'test-{stamp}'
        user = User(
            name=username,
            email='test@example.com',
            globalPermissionGroupId=1,
        )
        user.save()

        with self.assertRaises(AttributeError):
            user.passwordHash
        user.delete()
