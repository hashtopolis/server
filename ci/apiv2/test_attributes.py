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
            sessionLifetime=6000,
        )
        user.save()

        config = HashtopolisConfig()
        conn = HashtopolisConnector('/ui/users', config)
        conn.authenticate()

        headers = conn._headers
        headers['Content-Type'] = 'application/json'

        uri = conn._api_endpoint + conn._model_uri + f'/{user.id}'
        attributes = {}
        attributes['passwordHash'] = 'test'
        payload = conn.create_payload(user, attributes, id=user.id)
        r = requests.patch(uri, headers=headers, data=json.dumps(payload))

        self.assertEqual(r.status_code, 403)
        self.assertIn('immutable', r.json().get('title'))
        user.delete()

    def test_create_protected(self):
        stamp = int(time.time() * 1000)
        username = f'test-{stamp}'

        user = User(
            name=username,
            email='test@example.com',
            globalPermissionGroupId=1,
            passwordHash='test',
            sessionLifetime=6000,
        )
        with self.assertRaises(HashtopolisError) as e:
            user.save()
        
        self.assertEqual(e.exception.status_code, 403)
        self.assertIn(' not valid input ', e.exception.title)

    def test_get_private(self):
        stamp = int(time.time() * 1000)
        username = f'test-{stamp}'
        user = User(
            name=username,
            email='test@example.com',
            globalPermissionGroupId=1,
            sessionLifetime=6000,
        )
        user.save()

        with self.assertRaises(AttributeError):
            user.passwordHash
        user.delete()
