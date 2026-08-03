import base64
import json
import time

from hashtopolis import HashType

from utils import BaseTest, request_with_api_token


def _resource_payload(resource_type, attributes, resource_id=None):
    data = {
        'type': resource_type,
        'attributes': attributes,
    }
    if resource_id is not None:
        data['id'] = resource_id
    return {'data': data}


def _hashtype_attributes(hash_type_id, description='Permission Test HashType'):
    return {
        'hashTypeId': hash_type_id,
        'description': description,
        'isSalted': False,
        'isSlowHash': False,
    }


def _decode_jwt_scope(token):
    payload_b64 = token.split('.')[1]
    payload_b64 += '=' * (-len(payload_b64) % 4)
    payload = json.loads(base64.urlsafe_b64decode(payload_b64))
    return json.loads(payload['scope'])


class PermissionsTest(BaseTest):
    def test_api_token_agent_read_scope(self):
        allowed_token = self.create_apitoken(extra_payload={'scopes': ['permAgentRead']})
        allowed_response = request_with_api_token(
            allowed_token.token,
            '/ui/agents?page[size]=1',
        )
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        self.assertIn('data', allowed_response.json())

        denied_token = self.create_apitoken(extra_payload={'scopes': ['permHashlistRead']})
        denied_response = request_with_api_token(
            denied_token.token,
            '/ui/agents?page[size]=1',
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permAgentRead', denied_response.text)

    def test_api_token_user_read_scope_public_attributes(self):
        allowed_token = self.create_apitoken(extra_payload={'scopes': ['permUserRead']})
        allowed_response = request_with_api_token(
            allowed_token.token,
            '/ui/users?page[size]=1',
        )
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        allowed_attributes = allowed_response.json()['data'][0]['attributes']
        self.assertIn('name', allowed_attributes)
        self.assertIn('email', allowed_attributes)

        public_only_token = self.create_apitoken(extra_payload={'scopes': ['permHashlistRead']})
        public_only_response = request_with_api_token(
            public_only_token.token,
            '/ui/users?page[size]=1',
        )
        self.assertEqual(public_only_response.status_code, 200, public_only_response.text)
        public_only_attributes = public_only_response.json()['data'][0]['attributes']
        self.assertEqual(set(public_only_attributes), {'name'})

    def test_api_token_user_public_attributes_with_denied_global_permission_group_include(self):
        scope_template = _decode_jwt_scope(self.create_apitoken(extra_payload={'scopes': []}).token)
        scopes = [
            permission for permission in scope_template
            if permission not in ['permUserRead', 'permRightGroupRead']
        ]
        token = self.create_apitoken(extra_payload={'scopes': scopes})

        response = request_with_api_token(
            token.token,
            '/ui/users?include=globalPermissionGroup&page[size]=1',
        )
        self.assertEqual(response.status_code, 200, response.text)
        body = response.json()
        user_attributes = body['data'][0]['attributes']
        self.assertEqual(set(user_attributes), {'name'})
        self.assertNotIn('included', body)
        self.assertIn('permRightGroupRead', json.dumps(body['meta']))

    def test_api_token_hashtype_create_scope(self):
        hash_type_id = 90000 + int(time.time() * 1000) % 900
        payload = _resource_payload('HashType', _hashtype_attributes(hash_type_id))

        allowed_token = self.create_apitoken(extra_payload={'scopes': ['permHashTypeCreate']})
        allowed_response = request_with_api_token(
            allowed_token.token,
            '/ui/hashtypes',
            method='POST',
            payload=payload,
        )
        self.assertEqual(allowed_response.status_code, 201, allowed_response.text)
        self.delete_after_test(HashType.objects.get(pk=hash_type_id))

        denied_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeRead']}).token,
            '/ui/hashtypes',
            method='POST',
            payload=_resource_payload('HashType', _hashtype_attributes(hash_type_id + 1)),
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permHashTypeCreate', denied_response.text)

    def test_api_token_hashtype_update_scope(self):
        hashtype = self.create_hashtype()
        payload = _resource_payload(
            'HashType',
            {'description': 'Permission Test HashType Updated'},
            resource_id=hashtype.id,
        )

        allowed_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeUpdate']}).token,
            f'/ui/hashtypes/{hashtype.id}',
            method='PATCH',
            payload=payload,
        )
        self.assertEqual(allowed_response.status_code, 200, allowed_response.text)
        self.assertEqual(allowed_response.json()['data']['attributes']['description'], 'Permission Test HashType Updated')

        denied_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeRead']}).token,
            f'/ui/hashtypes/{hashtype.id}',
            method='PATCH',
            payload=payload,
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permHashTypeUpdate', denied_response.text)

    def test_api_token_hashtype_delete_scope(self):
        allowed_hashtype = self.create_hashtype(delete=False)
        allowed_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeDelete']}).token,
            f'/ui/hashtypes/{allowed_hashtype.id}',
            method='DELETE',
        )
        self.assertEqual(allowed_response.status_code, 204, allowed_response.text)

        denied_hashtype = self.create_hashtype()
        denied_response = request_with_api_token(
            self.create_apitoken(extra_payload={'scopes': ['permHashTypeRead']}).token,
            f'/ui/hashtypes/{denied_hashtype.id}',
            method='DELETE',
        )
        self.assertEqual(denied_response.status_code, 403, denied_response.text)
        self.assertIn('permHashTypeDelete', denied_response.text)
