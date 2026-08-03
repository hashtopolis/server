from utils import BaseTest, request_with_api_token


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
