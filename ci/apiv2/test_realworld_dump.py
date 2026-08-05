import json
import pytest
import requests
from base64 import b64encode

from hashtopolis import Agent, Chunk, Config, Hash, Hashlist, HashtopolisConfig, HashtopolisConnector, Task, TaskWrapperDisplay
from utils import BaseTest


@pytest.mark.realworld
class RealWorldDumpSmokeTest(BaseTest):
    def _cursor_after(self, field, value):
        return b64encode(json.dumps({'primary': {field: value}}).encode('utf-8')).decode('utf-8')

    def _api_get(self, path, params):
        connector = HashtopolisConnector(path, HashtopolisConfig())
        connector.authenticate()
        response = requests.get(connector._api_endpoint + connector._model_uri, headers=connector._headers, params=params)

        self.assertEqual(response.status_code, 200, response.text)
        return response.json()

    def test_known_dump_counts(self):
        self.assertEqual(Hash.objects.count(hashId__lte=30129)['count'], 28957)
        self.assertEqual(Hashlist.objects.count(hashlistId__lte=272)['count'], 267)
        self.assertEqual(Agent.objects.count(agentId__lte=18)['count'], 7)
        self.assertEqual(Task.objects.count(taskId__lte=1336)['count'], 304)
        self.assertEqual(Hash.objects.count(hashlistId=4)['count'], 10346)
        self.assertEqual(Task.objects.count(taskWrapperId=1004)['count'], 1)

    def test_known_config_values(self):
        self.assertEqual(Config.objects.get(item='baseHost').value, 'http://localhost:8080/')
        self.assertEqual(Config.objects.get(item='blacklistChars').value, '&|`"\'{}()[]$<>;')

    def test_sanitized_agent_rows_are_available(self):
        expected_agents = {
            'linux': 3,
            'laptopX': 1,
            'machineX': 1,
            'machineY': 1,
            'hashtopolis-agent1': 1,
        }

        for agent_name, expected_count in expected_agents.items():
            self.assertEqual(Agent.objects.count(agentName=agent_name)['count'], expected_count)

        for last_ip in ['192.168.56.15', '192.168.56.17', '192.168.56.18']:
            self.assertEqual(Agent.objects.count(lastIp=last_ip)['count'], 1)

    def test_hash_pagination_on_large_hashlist(self):
        first_page = Hash.objects.paginate(size=50).filter(hashlistId=4).order_by('hashId').get_pagination()

        self.assertEqual(len(first_page), 50)
        first_ids = [hash_obj.id for hash_obj in first_page]
        self.assertEqual(first_ids[:5], [112, 122, 124, 139, 141])
        self.assertEqual(first_ids[-5:], [327, 344, 350, 361, 362])
        self.assertTrue(all(hash_obj.hashlistId == 4 for hash_obj in first_page))
        self.assertEqual(first_ids, sorted(first_ids))

        second_page = Hash.objects.paginate(size=50, after=self._cursor_after('hashId', first_ids[-1])) \
            .filter(hashlistId=4).order_by('hashId').get_pagination()
        second_ids = [hash_obj.id for hash_obj in second_page]

        self.assertEqual(len(second_page), 50)
        self.assertEqual(second_ids[:5], [366, 369, 377, 381, 389])
        self.assertEqual(second_ids[-5:], [555, 556, 557, 558, 559])
        self.assertTrue(set(first_ids).isdisjoint(second_ids))
        self.assertTrue(all(hash_obj.hashlistId == 4 for hash_obj in second_page))

        last_page = Hash.objects.paginate(size=50, after=self._cursor_after('hashId', 10812)) \
            .filter(hashlistId=4).order_by('hashId').get_pagination()
        last_ids = [hash_obj.id for hash_obj in last_page]

        self.assertEqual(len(last_page), 46)
        self.assertEqual(last_ids, list(range(10813, 10859)))
        self.assertTrue(set(first_ids).isdisjoint(last_ids))
        self.assertTrue(set(second_ids).isdisjoint(last_ids))
        self.assertTrue(all(hash_obj.hashlistId == 4 for hash_obj in last_page))

    def test_hash_pagination_walks_large_hashlist_without_duplicates(self):
        page_size = 1000
        after = None
        seen_ids = []

        while True:
            query = Hash.objects.paginate(size=page_size, after=after) if after else Hash.objects.paginate(size=page_size)
            page = query.filter(hashlistId=4).order_by('hashId').get_pagination()
            page_ids = [hash_obj.id for hash_obj in page]

            self.assertEqual(page_ids, sorted(page_ids))
            self.assertEqual(len(page_ids), len(set(page_ids)))
            self.assertTrue(all(hash_obj.hashlistId == 4 for hash_obj in page))

            seen_ids.extend(page_ids)
            if len(page) < page_size:
                break

            after = self._cursor_after('hashId', page_ids[-1])

        self.assertEqual(len(seen_ids), 10346)
        self.assertEqual(len(seen_ids), len(set(seen_ids)))
        self.assertEqual(seen_ids[0], 112)
        self.assertEqual(seen_ids[-1], 10858)

    def test_existing_taskwrapperdisplay_row(self):
        task_wrapper = TaskWrapperDisplay.objects.get(taskWrapperId=1004)

        self.assertEqual(task_wrapper.hashlistId, 4)
        self.assertEqual(task_wrapper.hashlistName, 'hashlist_sha1_1')
        self.assertEqual(task_wrapper.taskName, 'Sha1_4')

    def test_known_task_hashlist_relationships(self):
        hashlist = Hashlist.objects.get(hashlistId=4)
        task = Task.objects.get(taskId=1004)
        task_wrapper = TaskWrapperDisplay.objects.get(taskWrapperId=1004)

        self.assertEqual(hashlist.name, 'hashlist_sha1_1')
        self.assertEqual(hashlist.hashTypeId, 100)
        self.assertEqual(hashlist.hashCount, 10346)
        self.assertEqual(hashlist.cracked, 10346)
        self.assertFalse(hashlist.isArchived)

        self.assertEqual(task.taskWrapperId, task_wrapper.id)
        self.assertEqual(task.taskName, task_wrapper.taskName)
        self.assertEqual(task.attackCmd, '#HL# wordlist1.txt')
        self.assertEqual(task.keyspace, 2473033115)
        self.assertEqual(task.keyspaceProgress, task.keyspace)
        self.assertFalse(task.isArchived)

        self.assertEqual(task_wrapper.hashlistId, hashlist.id)
        self.assertEqual(task_wrapper.hashlistName, hashlist.name)
        self.assertEqual(task_wrapper.hashTypeId, hashlist.hashTypeId)
        self.assertEqual(task_wrapper.hashTypeDescription, 'SHA1')
        self.assertEqual(task_wrapper.groupName, 'Default Group')

    def test_chunk_pagination_on_large_task(self):
        first_page = Chunk.objects.paginate(size=10).filter(taskId=1308).order_by('chunkId').get_pagination()

        self.assertEqual(len(first_page), 10)
        first_ids = [chunk.id for chunk in first_page]
        self.assertEqual(first_ids, list(range(1157, 1167)))
        self.assertTrue(all(chunk.taskId == 1308 for chunk in first_page))
        self.assertTrue(all(chunk.state == 4 for chunk in first_page))

        second_page = Chunk.objects.paginate(size=10, after=self._cursor_after('chunkId', first_ids[-1])) \
            .filter(taskId=1308).order_by('chunkId').get_pagination()
        second_ids = [chunk.id for chunk in second_page]

        self.assertEqual(len(second_page), 10)
        self.assertEqual(second_ids, list(range(1167, 1177)))
        self.assertTrue(set(first_ids).isdisjoint(second_ids))
        self.assertTrue(all(chunk.taskId == 1308 for chunk in second_page))

    def test_taskwrapperdisplay_pagination_with_aggregates(self):
        params = {
            'filter[taskWrapperIsArchived__eq]': 'false',
            'aggregate[taskwrapperdisplay]': 'totalAssignedAgents,searched,dispatched,status,currentSpeed',
            'page[size]': '20',
            'sort': 'taskWrapperId',
        }

        first_page = self._api_get('/ui/taskwrapperdisplays', params)
        first_ids = [item['id'] for item in first_page['data']]

        self.assertEqual(first_ids, list(range(1004, 1024)))
        self.assertIsNotNone(first_page['links']['next'])

        first_attrs = first_page['data'][0]['attributes']
        for aggregate in ['totalAssignedAgents', 'searched', 'dispatched', 'status', 'currentSpeed']:
            self.assertIn(aggregate, first_attrs)
        self.assertFalse(any(item['attributes']['taskWrapperIsArchived'] for item in first_page['data']))

        second_params = dict(params)
        second_params['page[after]'] = self._cursor_after('taskWrapperId', first_ids[-1])
        second_page = self._api_get('/ui/taskwrapperdisplays', second_params)
        second_ids = [item['id'] for item in second_page['data']]

        self.assertEqual(second_ids, list(range(1024, 1044)))
        self.assertTrue(set(first_ids).isdisjoint(second_ids))

    def test_hashlist_pagination_with_includes(self):
        params = {
            'filter[format__nin]': '3',
            'filter[isArchived__eq]': 'false',
            'include': 'hashType,accessGroup',
            'page[size]': '20',
            'sort': 'hashlistId',
        }

        first_page = self._api_get('/ui/hashlists', params)
        first_ids = [item['id'] for item in first_page['data']]

        self.assertEqual(first_ids, list(range(4, 24)))
        self.assertIsNotNone(first_page['links']['next'])
        self.assertTrue({'hashType', 'accessGroup'}.issubset({item['type'] for item in first_page['included']}))
        self.assertFalse(any(item['attributes']['isArchived'] for item in first_page['data']))
        self.assertFalse(any(item['attributes']['format'] == 3 for item in first_page['data']))

        second_params = dict(params)
        second_params['page[after]'] = self._cursor_after('hashlistId', first_ids[-1])
        second_page = self._api_get('/ui/hashlists', second_params)
        second_ids = [item['id'] for item in second_page['data']]

        self.assertEqual(second_ids, list(range(24, 44)))
        self.assertTrue(set(first_ids).isdisjoint(second_ids))
