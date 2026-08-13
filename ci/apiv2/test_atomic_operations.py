import datetime
import json

import requests

from hashtopolis import AccessGroup, HashtopolisConfig, HashtopolisConnector
from utils import BaseTest

# JSON:API 1.1 requires the extension to be named in the media type of both the
# request and the response (https://jsonapi.org/ext/atomic/).
ATOMIC_MEDIA_TYPE = 'application/vnd.api+json;ext="https://jsonapi.org/ext/atomic"'


class AtomicOperationsTest(BaseTest):
    """The JSON:API atomic operations endpoint of a collection, POST .../operations."""

    model_class = AccessGroup

    def create_test_object(self, *nargs, **kwargs):
        return self.create_accessgroup(*nargs, **kwargs)

    def _unique_name(self, suffix=''):
        return f'Testing Group {datetime.datetime.now().isoformat()}{suffix}'

    def _post(self, operations, content_type=ATOMIC_MEDIA_TYPE):
        conn = HashtopolisConnector('/ui/accessgroups', HashtopolisConfig())
        conn.authenticate()
        headers = dict(conn._headers)
        headers['Content-Type'] = content_type
        uri = conn._api_endpoint + conn._model_uri + '/operations'
        return requests.post(uri, headers=headers, data=json.dumps({'atomic:operations': operations}))

    def test_add_and_update_report_the_written_objects(self):
        existing = self.create_test_object()
        added_name = self._unique_name('-added')
        updated_name = self._unique_name('-updated')

        r = self._post([
            {'op': 'add', 'data': {'type': 'accessGroup', 'attributes': {'groupName': added_name}}},
            {'op': 'update', 'data': {'type': 'accessGroup', 'id': str(existing.id),
                                      'attributes': {'groupName': updated_name}}},
        ])

        self.assertEqual(r.status_code, 200)
        self.assertIn('ext="https://jsonapi.org/ext/atomic"', r.headers.get('Content-Type'))

        results = r.json()['atomic:results']
        self.assertEqual(len(results), 2)
        self.assertEqual(results[0]['data']['type'], 'accessGroup')
        self.assertEqual(results[0]['data']['attributes']['groupName'], added_name)
        self.assertEqual(results[1]['data']['id'], str(existing.id))
        self.assertEqual(results[1]['data']['attributes']['groupName'], updated_name)

        added = AccessGroup.objects.get(pk=int(results[0]['data']['id']))
        self.delete_after_test(added)
        self.assertEqual(added.groupName, added_name)
        self.assertEqual(AccessGroup.objects.get(pk=existing.id).groupName, updated_name)

    def test_removals_only_are_answered_without_a_body(self):
        group = self.create_test_object(delete=False)

        r = self._post([{'op': 'remove', 'ref': {'type': 'accessGroup', 'id': str(group.id)}}])

        self.assertEqual(r.status_code, 204)
        self.assertEqual(r.text, '')
        self.assertEqual(list(AccessGroup.objects.filter(id=group.id)), [])

    def test_a_failing_operation_undoes_the_preceding_ones(self):
        groups_before = len(list(AccessGroup.objects.all()))

        r = self._post([
            {'op': 'add', 'data': {'type': 'accessGroup', 'attributes': {'groupName': self._unique_name()}}},
            {'op': 'update', 'data': {'type': 'accessGroup', 'id': '99999999',
                                      'attributes': {'groupName': self._unique_name()}}},
        ])

        self.assertEqual(r.status_code, 404)
        # The object created by the first operation must be gone again
        self.assertEqual(len(list(AccessGroup.objects.all())), groups_before)

    def test_operations_must_address_the_type_of_the_collection(self):
        r = self._post([{'op': 'remove', 'ref': {'type': 'agent', 'id': '1'}}])

        self.assertEqual(r.status_code, 400)
        self.assertIn('accessGroup', r.json()['errors'][0]['title'])

    def test_unknown_operations_are_rejected(self):
        r = self._post([{'op': 'replace', 'data': {'type': 'accessGroup', 'attributes': {}}}])

        self.assertEqual(r.status_code, 400)

    def test_the_extension_must_be_named_in_the_content_type(self):
        for content_type in ['application/vnd.api+json', 'application/json']:
            r = self._post([{'op': 'remove', 'ref': {'type': 'accessGroup', 'id': '1'}}], content_type=content_type)
            self.assertEqual(r.status_code, 415, f'Content-Type: {content_type}')

    def test_an_unusable_media_type_parameter_is_rejected(self):
        """Content negotiation of JSON:API 1.1: only ext and profile may modify the media type."""
        r = self._post([], content_type='application/vnd.api+json;charset=utf-8')
        self.assertEqual(r.status_code, 415)

        r = self._post([], content_type='application/vnd.api+json;ext="https://example.com/ext/unknown"')
        self.assertEqual(r.status_code, 415)
