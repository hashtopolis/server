import abc
import datetime
from io import BytesIO
import json
from pathlib import Path
import requests
import time
import unittest

import confidence

from hashtopolis import Agent, Cracker, Hashlist, GlobalPermissionGroup, File, FileImport, User, Voucher, Task
from hashtopolis_agent import DummyAgent


def do_create_agent():
    stamp = int(time.time() * 1000)
    voucher = Voucher(voucher=f'dummy-test-{stamp}').save()

    dummy_agent = DummyAgent()
    dummy_agent.register(voucher=voucher.voucher, name=f'test-agent-{stamp}')
    dummy_agent.login()

    # Validate automatically deleted when an test-agent claims the voucher
    assert Voucher.objects.filter(_id=voucher.id) == []

    agent = Agent.objects.get(agentName=dummy_agent.name)
    return (dummy_agent, agent)


def do_create_cracker(file_id='001'):
    p = Path(__file__).parent.joinpath(f'create_cracker_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    cracker = Cracker(**payload)
    obj = cracker.save()
    return obj


def do_create_file(file_id='001', content='12345678\n123456\nprincess\n'.encode('utf-8')):
    stamp = datetime.datetime.now().isoformat()
    filename = f'test-{stamp}.txt'

    file_import = FileImport()
    file_import.do_upload(filename, BytesIO(content))

    p = Path(__file__).parent.joinpath(f'create_file_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    payload['sourceData'] = filename
    payload['filename'] = filename
    obj = File(**payload)
    obj.save()

    return obj


def do_create_globalpermissiongroup(permissions={'permHashlistRead': True}):
    stamp = int(time.time() * 1000)
    payload = dict(
        name=f'group-{stamp}',
        permissions=permissions,
    )
    obj = GlobalPermissionGroup(**payload)
    obj.save()
    return obj


def do_create_hashlist(file_id='001'):
    p = Path(__file__).parent.joinpath(f'create_hashlist_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    hashlist = Hashlist(**payload)
    obj = hashlist.save()
    return obj


def do_create_task(hashlist, file_id='001'):
    p = Path(__file__).parent.joinpath(f'create_task_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    payload['hashlistId'] = int(hashlist.id)
    obj = Task(**payload)
    obj.save()
    return obj


def do_create_user(global_permission_group_id=1):
    stamp = int(time.time() * 1000)
    payload = dict(
        name=f'test-{stamp}',
        email='test@example.com',
        globalPermissionGroupId=global_permission_group_id,
    )
    obj = User(**payload)
    obj.save()
    return obj


class TestBase(unittest.TestCase, abc.ABC):
    @abc.abstractmethod
    def getBaseURI(self):
        pass

    def getURI(self, obj_id=None):
        if obj_id is None:
            # Base object
            return self._uri
        elif type(obj_id) is dict and '_self' in obj_id:
            # Identification by Uniform URI indentification (_self)
            return self._cfg['hashtopolis_uri'] + obj_id['_self']
        elif type(obj_id) is int:
            # Identification by ID
            return f'{self._uri}/{obj_id}'
        else:
            # Identification by Uniform URI (string)
            return self._cfg['hashtopolis_uri'] + obj_id

    @classmethod
    def setUpClass(cls):
        # Request access TOKEN, used throughout the test
        load_order = (str(Path(__file__).parent.joinpath('{name}-defaults.{extension}')),) \
                     + confidence.DEFAULT_LOAD_ORDER
        cls._cfg = confidence.load_name('hashtopolis-test', load_order=load_order)
        cls._api_endpoint = cls._cfg['hashtopolis_uri'] + '/api/v2'
        cls._uri = cls._api_endpoint + cls.getBaseURI(cls)

        auth_uri = cls._api_endpoint + '/auth/token'
        auth = (cls._cfg['username'], cls._cfg['password'])
        r = requests.post(auth_uri, auth=auth)

        cls._token = r.json()['token']
        cls._token_expires = r.json()['expires']

        cls._headers = {
            'Authorization': 'Bearer ' + cls._token,
            'Content-Type': 'application/json'
        }


class BaseTest(unittest.TestCase):
    @classmethod
    def setUp(cls):
        cls.obj_heap = []

    def _test_expandables(self, model_class, model_obj, expandables):
        self.delete_after_test(model_obj)
        obj = model_class.objects.get(pk=model_obj.id, expand=expandables)
        self.assertIsNotNone(obj)

    @classmethod
    def tearDown(cls):
        while len(cls.obj_heap) > 0:
            obj = cls.obj_heap.pop()
            obj.delete()

    def delete_after_test(self, obj):
        self.obj_heap.append(obj)
