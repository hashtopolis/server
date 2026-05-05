import requests
from unittest.mock import patch, MagicMock

from hashtopolis import HashtopolisConnector, HashtopolisConfig
from utils import BaseTest


class TaskWrapperDisplaysTest(BaseTest):
    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.config = HashtopolisConfig()        
    
    def test_empty_body(self):
        conn = HashtopolisConnector('/ui/taskwrapperdisplays', self.config)
        conn.authenticate()

        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri

        r = requests.get(uri, headers=headers)
        values = r.json().get('jsonapi')
        
        self.assertGreaterEqual(len(values), 1)
    @patch('requests.get')
    @patch('requests.post')
    def test_color(self, mock_post, mock_get):
        mock_post_response = MagicMock()
        mock_post_response.status_code = 201
        mock_post_response.headers = {'Content-Type': 'application/json'}
        mock_post_response.text = '{"token": "fake-token", "token_expires": "never"}'
        mock_post_response.json.return_value = {
            'token': 'fake-token',
            'token_expires': 'never'
        }
        mock_post.return_value = mock_post_response

        mock_response = MagicMock()
        mock_response.status_code = 200
        mock_response.json.return_value = {
            'jsonapi': {'version': '1.1'},
            'data': [
                {
                    'attributes': {
                        'color': '#913cce'
                    }
                }
            ]
        }
        mock_get.return_value = mock_response

        conn = HashtopolisConnector('/ui/taskwrapperdisplays', self.config)
        conn.authenticate()

        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri

        r = requests.get(uri, headers=headers)
        response_json = r.json()
        values = response_json.get('jsonapi')
        color_value = None
        data_items = response_json.get('data') or []
        if data_items:
            color_value = data_items[0].get('attributes', {}).get('color')

        #print(f"\nResponse status: {r.status_code}")
        #print(f"Color field value: {color_value}")
        self.assertEqual(200, r.status_code)
        self.assertEqual("#913cce", color_value)