import pytest

from hashtopolis import Agent, Config, Hash, Hashlist, Task, TaskWrapperDisplay
from utils import BaseTest


@pytest.mark.realworld
class RealWorldDumpSmokeTest(BaseTest):
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

    def test_existing_hash_pagination(self):
        hashes = Hash.objects.paginate(size=5).filter(hashlistId=4).order_by('hashId').get_pagination()

        self.assertEqual(len(hashes), 5)
        self.assertEqual([hash_obj.id for hash_obj in hashes], sorted(hash_obj.id for hash_obj in hashes))

    def test_existing_taskwrapperdisplay_row(self):
        task_wrapper = TaskWrapperDisplay.objects.get(taskWrapperId=1004)

        self.assertEqual(task_wrapper.hashlistId, 4)
        self.assertEqual(task_wrapper.hashlistName, 'hashlist_sha1_1')
        self.assertEqual(task_wrapper.taskName, 'Sha1_4')
