import json

import requests

from hashtopolis import Notification
from utils import BaseTest, create_restricted_user


class NotificationTest(BaseTest):
    model_class = Notification

    def create_test_object(self, *nargs, **kwargs):
        return self.create_notification(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'receiver', 'dummy-user@example.com')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['user']
        self._test_expandables(model_obj, expandables)

    def _create_as(self, auth, action):
        """POST a notification as the given user, deleting it again (as its owner, the only one allowed to) when
        it was created."""
        connector = Notification.objects.get_conn()
        connector.authenticate(auth=auth)
        uri = connector._api_endpoint + '/ui/notifications'
        headers = {**connector._headers, 'Content-Type': 'application/json'}
        payload = {
            'data': {
                'type': 'NotificationSetting',
                'attributes': {
                    'action': action,
                    'actionFilter': 'ALL',
                    'isActive': True,
                    'notification': 'Example',
                    'receiver': 'foobar@example.org',
                },
            },
        }
        response = requests.post(uri, headers=headers, data=json.dumps(payload))
        if response.status_code == 201:
            created_id = response.json()['data']['id']
            deleted = requests.delete(f'{uri}/{created_id}', headers=connector._headers)
            self.assertEqual(204, deleted.status_code, deleted.text)
        return response

    def test_action_type_needs_its_permission(self):
        """Creating a notification checks the action type's permission against the caller's token."""
        notification_perms = {
            'permNotificationSettingCreate': True,
            'permNotificationSettingRead': True,
            'permNotificationSettingDelete': True,
        }

        auth = create_restricted_user(self, notification_perms)
        response = self._create_as(auth, 'agentError')
        self.assertNotEqual(201, response.status_code, response.text)
        self.assertIn('not allowed to use this action type', response.text)

        auth = create_restricted_user(self, {**notification_perms, 'permAgentRead': True})
        response = self._create_as(auth, 'agentError')
        self.assertEqual(201, response.status_code, response.text)
