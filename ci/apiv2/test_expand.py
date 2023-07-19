import unittest
import requests
import json
from pathlib import Path

from hashtopolis import Hashlist as Hashlist_v2
from hashtopolis import Task as Task_v2
from hashtopolis import AccessGroup as AccessGroup_v2
from hashtopolis import HashtopolisConnector, HashtopolisConfig


class Expand(unittest.TestCase):

    def test_task_crackerbinary_o2o(self):
        # Create hashlist
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist_v2 = Hashlist_v2(**payload)
        hashlist_v2.save()

        # Create Task
        p = Path(__file__).parent.joinpath('create_task_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        payload['hashlistId'] = int(hashlist_v2._id)
        task_v2 = Task_v2(**payload)
        task_v2.save()

        to_check = task_v2.id

        # One-to-one casting
        obj = Task_v2()
        objects = obj.objects.filter(taskId=to_check, expand='crackerBinary')
        assert objects[0].crackerBinary_set.binaryName == 'hashcat'

        task_v2.delete()
        hashlist_v2.delete()

    def test_accessgroups_usermembers_m2m(self):
        # Many-to-many casting
        obj = AccessGroup_v2()
        objects = obj.objects.all(expand='userMembers')

        # Check the default account
        assert objects[0].userMembers_set[0].name == 'admin'

    def test_individual_object_expanding(self):
        # The individual object expanding is broken in the API.
        # Because the normal python bindings uses filtering, this test
        # forces the use of HTTP methods to test the expand functionality.

        # Create hashlist
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist_v2 = Hashlist_v2(**payload)
        hashlist_v2.save()

        config = HashtopolisConfig()
        conn = HashtopolisConnector('/ui/hashlists', config)
        conn.authenticate()
        headers = conn._headers
        uri = conn._api_endpoint + conn._model_uri
        uri = uri + '/' + str(hashlist_v2._id) + '?expand=hashes'

        r = requests.get(uri, headers=headers, data=json.dumps({}))
        assert r.json().get('hashes')[0].get('hash') == 'cc03e747a6afbbcbf8be7668acfebee5'

        hashlist_v2.delete()
