import requests

from hashtopolis import Helper
from utils import BaseTest, create_restricted_user


class NotificationActionsHelperTest(BaseTest):
    def test_returns_actions(self):
        actions = Helper().get_notification_actions()
        self.assertGreater(len(actions), 0)
        for action in actions:
            self.assertIsInstance(action['action'], str)
            self.assertIn(action['objectType'], ['Hashlist', 'Agent', 'User', 'Task', 'NONE'])
            self.assertIsInstance(action['requiredPermission'], list)
            self.assertTrue(len(action['requiredPermission']) > 0)
            self.assertIsInstance(action['allowed'], bool)

    def test_contains_known_actions(self):
        actions = {action['action']: action for action in Helper().get_notification_actions()}
        self.assertIn('taskComplete', actions)
        self.assertEqual(actions['taskComplete']['objectType'], 'Task')
        self.assertIn('userLoginFailed', actions)
        self.assertEqual(actions['userLoginFailed']['objectType'], 'User')
        self.assertIn('agentError', actions)
        self.assertEqual(actions['agentError']['objectType'], 'Agent')
        self.assertIn('newAgent', actions)
        self.assertEqual(actions['newAgent']['objectType'], 'NONE')

    def test_admin_is_allowed_all_actions(self):
        actions = Helper().get_notification_actions()
        self.assertTrue(all(action['allowed'] for action in actions),
                        "Admin should be allowed to use all notification actions")

    def test_restricted_user_only_allowed_permitted_actions(self):
        """'allowed' reflects the permissions of the requesting user."""
        auth = create_restricted_user(self, {'permAgentRead': True})
        helper = Helper()
        helper.authenticate(auth=auth)
        response = requests.get(helper._api_endpoint + helper._model_uri + 'getNotificationActions',
                                headers=helper._headers)
        self.assertEqual(response.status_code, 200, response.text)

        actions = {action['action']: action for action in response.json()['meta']['actions']}
        self.assertTrue(actions['agentError']['allowed'],
                        "User with agent read permission should be allowed to use agent actions")
        self.assertFalse(actions['taskComplete']['allowed'],
                          "User without task permission should not be allowed to use task actions")


class NotificationChannelsHelperTest(BaseTest):
    def test_returns_channels(self):
        channels = Helper().get_notification_channels()
        self.assertGreater(len(channels), 0)
        for channel in channels:
            self.assertIsInstance(channel['name'], str)

    def test_contains_known_channels(self):
        channels = [channel['name'] for channel in Helper().get_notification_channels()]
        for expected in ['ChatBot', 'Discord Webhook', 'Email', 'Example', 'Slack', 'Telegram']:
            self.assertIn(expected, channels)