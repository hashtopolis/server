import requests
from unittest.mock import patch, MagicMock

from hashtopolis import HashtopolisConnector, HashtopolisConfig
from utils import BaseTest


class TaskWrapperDisplaysTest(BaseTest):
    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.config = HashtopolisConfig()        
    
    def test_color(self):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist, extra_payload={'color': '#8000ff'})
        conn = HashtopolisConnector('/ui/taskwrapperdisplays', self.config)
        conn.authenticate()

        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri

        r = requests.get(uri, headers=headers)
        values = r.json()
        data_items = values.get('data') or []

        color_value = None
        color_value = data_items[0].get('attributes', {}).get('color')
        self.assertEqual(200, r.status_code)
        self.assertIsNotNone(color_value)
        self.assertEqual("#8000ff", color_value)
        self.assertNotEqual("#ff0000", color_value)

    def test_no_color(self):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist, extra_payload={'color': '#FFFFFF'})
        conn = HashtopolisConnector('/ui/taskwrapperdisplays', self.config)
        conn.authenticate()

        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri

        r = requests.get(uri, headers=headers)
        values = r.json()
        data_items = values.get('data') or []

        color_value = None
        color_value = data_items[0].get('attributes', {}).get('color')
        self.assertEqual(200, r.status_code)
        self.assertIsNone(color_value)
        self.assertNotEqual("#FFFFFF", color_value)