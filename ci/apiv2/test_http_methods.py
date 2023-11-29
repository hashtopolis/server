import requests

from hashtopolis import HashtopolisConnector, HashtopolisConfig
from utils import BaseTest


class HttpMethodsTest(BaseTest):
    def test_empty_body(self):
        config = HashtopolisConfig()
        conn = HashtopolisConnector('/ui/users', config)
        conn.authenticate()

        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri

        r = requests.get(uri, headers=headers)
        values = r.json().get('jsonapi')

        self.assertGreaterEqual(len(values), 1)

    # TODO: Test for non-empty body which should fail
    # TODO: Test for invalid parameters