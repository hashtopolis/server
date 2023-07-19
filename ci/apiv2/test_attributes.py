from hashtopolis import HashtopolisConnector, HashtopolisConfig
import unittest
import requests
import json
import time

from hashtopolis import User
from hashtopolis import HashtopolisError


class AttributeTypes(unittest.TestCase):
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

        assert r.status_code == 500
        assert 'immutable' in r.json().get('exception')[0].get('message')
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
        assert e.exception.args[1] == 'Creation of object failed'
        assert 'is not valid input key' in e.exception.args[4]

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
