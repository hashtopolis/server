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
        expected_id = str(task.taskWrapperId)
        for item in data_items:
            if str(item.get('id')) == expected_id:
                color_value = item.get('attributes', {}).get('color')
                break

        #print(f"\nResponse status: {r.status_code}")
        #print(f"Color field value: {color_value}")
        self.assertEqual(200, r.status_code)
        self.assertIsNotNone(color_value)
        self.assertEqual("#8000ff", color_value)