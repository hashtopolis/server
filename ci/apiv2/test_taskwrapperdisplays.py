import requests

from hashtopolis import HashtopolisConnector, HashtopolisConfig
from utils import BaseTest


class TaskWrapperDisplaysTest(BaseTest):
    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.config = HashtopolisConfig()        
    
    def test_taskwrapperdisplays_returns_color_field(self):
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        conn = HashtopolisConnector('/ui/taskwrapperdisplays', self.config)
        conn.authenticate()

        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri

        r = requests.get(uri, headers=headers)
        values = r.json()
        data_items = values.get('data') or []
        expected_id = str(task.taskWrapperId)
        color_value = None
        expected_color_value = str(task.color)
        for item in data_items:
            if str(item.get('id')) == expected_id:
                color_value = item.get('attributes', {}).get('color')
                break
        self.assertEqual(200, r.status_code)
        self.assertIsNotNone(color_value)
        self.assertEqual(expected_color_value, color_value)
        self.assertNotEqual("ff0000", color_value)
