import requests
import json
import time

from hashtopolis import HashtopolisConnector, HashtopolisConfig
from utils import BaseTest



class HttpMethodsTest(BaseTest):
    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.config = HashtopolisConfig()        
    
    def test_empty_body(self):
        conn = HashtopolisConnector('/ui/users', self.config)
        conn.authenticate()

        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri

        r = requests.get(uri, headers=headers)
        values = r.json().get('jsonapi')

        self.assertGreaterEqual(len(values), 1)

    # TODO: Test for non-empty body which should fail
    # TODO: Test for invalid parameters
    
    def test_get_one_response_should_not_duplicate_self_reference_in_data(self):
        conn = HashtopolisConnector('/ui/users', self.config)
        conn.authenticate()
        resource_path = conn._model_uri + "/1"
        uri = conn._api_endpoint + resource_path

        response = requests.get(uri, headers=conn._headers)
        r = response.json()
        
        self.assertIsNotNone(r['links']['self'], "Top level self reference should be present in all responses.")
        self.assertIn(response.request.path_url, r['links']['self'], "Self reference for a single resource should be its path.")
        self.assertTrue(isinstance(r['data'], dict), "A single resource should be represented as an object.")
        self.assertNotIn('self', r['data'].get('links', {}), "A single resource should not include a self reference.")

    def test_get_many_response_should_include_self_reference_for_every_resource(self):
        conn = HashtopolisConnector('/ui/hashtypes', self.config)
        conn.authenticate()
        resource_path = conn._model_uri
        uri = conn._api_endpoint + resource_path

        response = requests.get(uri, headers=conn._headers)
        r = response.json()
        
        self.assertIsNotNone(r['links']['self'])
        self.assertIn(response.request.path_url, r['links']['self'], "Self reference for a resource collection should be its path.")

        resources = r.get('data')
        self.assertIsInstance(resources, list)
        self.assertGreater(len(resources), 0)

        for resource in resources:
            self.assertIsInstance(resource, dict)
            self.assertIn('self', resource.get('links', {}), "A resource in a collection should contain a self refrence")
            self.assertEqual(f"{response.request.path_url}/{resource.get('id')}", resource['links']['self'])

    def test_post_user_should_return_getone_uri_in_location(self):
        conn = HashtopolisConnector('/ui/users', self.config)
        conn.authenticate()
        uri = f"{conn._api_endpoint}{conn._model_uri}"
        stamp = int(time.time() * 1000)

        payload = {
            "data": {
                "type": "user",
                "attributes": {
                    "name": f"test-{stamp}",
                    "email": f"test-{stamp}@example.com",
                    "globalPermissionGroupId": 1,
                    "isValid": True,
                    "sessionLifetime": 3600,
                },
            },
        }

        headers = dict(conn._headers)
        headers["Content-Type"] = "application/json"
        response = requests.post(uri, headers=headers, data=json.dumps(payload))

        self.assertEqual(response.status_code, 201)
        self.assertIsNotNone(response.headers.get('Location'), "Missing Location header in created resource")
        
        resource_response = requests.get(f"{conn._hashtopolis_uri}{response.headers.get('Location')}", headers=conn._headers)
        self.assertEqual(resource_response.status_code, 200, "Unable to find the created resource")

