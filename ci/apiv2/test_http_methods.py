from hashtopolis import HashtopolisConnector, HashtopolisConfig
import unittest
import requests
import json

class HttpMethods(unittest.TestCase):          
    def test_empty_body(self):
        config = HashtopolisConfig()
        conn = HashtopolisConnector('/ui/users', config)
        conn.authenticate()

        headers = conn._headers
        del headers['Content-Type']

        uri = conn._api_endpoint + conn._model_uri
        
        r = requests.get(uri, headers=headers)
        values = r.json().get('values')

        assert len(values) >= 1
        
