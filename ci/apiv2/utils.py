import abc
import datetime
from io import BytesIO
import json
from pathlib import Path
import requests
import time
import unittest

import confidence

from hashtopolis import Agent
from hashtopolis import Cracker
from hashtopolis import CrackerType
from hashtopolis import File
from hashtopolis import FileImport
from hashtopolis import GlobalPermissionGroup
from hashtopolis import Hashlist
from hashtopolis import HashtopolisError
from hashtopolis import HashType
from hashtopolis import Pretask
from hashtopolis import Task
from hashtopolis import User
from hashtopolis import Voucher

from hashtopolis_agent import DummyAgent


def _do_create_obj_from_file(model_class, file_prefix, file_id='001', extra_payload={}):
    p = Path(__file__).parent.joinpath(f'{file_prefix}_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    final_payload = {**payload, **extra_payload}
    obj = model_class(**final_payload)
    obj.save()
    return obj


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
    return _do_create_obj_from_file(Cracker, 'create_cracker', file_id)


def do_create_crackertype(file_id='001'):
    return _do_create_obj_from_file(CrackerType, 'create_crackertype', file_id)


def do_create_file(file_id='001', content='12345678\n123456\nprincess\n'.encode('utf-8')):
    stamp = datetime.datetime.now().isoformat()
    filename = f'test-{stamp}.txt'

    file_import = FileImport()
    file_import.do_upload(filename, BytesIO(content))

    extra_payload = dict(sourceData=filename, filename=filename)
    return _do_create_obj_from_file(File, 'create_file', file_id, extra_payload)


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
    return _do_create_obj_from_file(Hashlist, 'create_hashlist', file_id)


def do_create_hashtype(file_id='001'):
    return _do_create_obj_from_file(HashType, 'create_hashtype', file_id)


def do_create_pretask(file_id='001', files=[]):
    extra_payload = dict(files=[file.id for file in files])
    return _do_create_obj_from_file(Pretask, 'create_pretask', file_id, extra_payload)


def do_create_task(hashlist, file_id='001'):
    extra_payload = dict(hashlistId=int(hashlist.id))
    return _do_create_obj_from_file(Task, 'create_task', file_id, extra_payload)


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

    def create_test_object(self, delete=True):
        raise NotImplementedError("Implement create_test_object(self, delete=True) to allow using generic testing")

    def _test_create(self, model_obj):
        """ Generic test worker to CREATE object"""
        # Retrieve object via created ID and check if exists
        obj = self.model_class.objects.get(pk=model_obj.id)
        self.assertIsNotNone(obj)

    def _test_delete(self, model_obj):
        """ Generic test worker to DELETE object"""
        # Retrieve object via created ID and check if exists
        obj = self.model_class.objects.get(pk=model_obj.id)
        self.assertIsNotNone(obj)

        # DELETE object
        model_obj.delete()

        # Check if object is deleted
        with self.assertRaises(self.model_class.DoesNotExist):
            _ = self.model_class.objects.get(pk=model_obj.id)

    def _test_expandables(self, model_obj, expandables):
        """ Generic test worker to test expandables"""
        # Retrieve object expanded and check if exists
        obj = self.model_class.objects.get(pk=model_obj.id, expand=expandables)
        self.assertIsNotNone(obj)
        for expandable in expandables:
            self.assertTrue(hasattr(obj, expandable) or hasattr(obj, f"{expandable}_set"),
                            f"Expand attribute '{expandable}' not found in model")

    def _test_exception(self, func_create, *args, **kwargs):
        with self.assertRaises(HashtopolisError) as e:
            _ = func_create(*args, **kwargs)
        self.assertEqual(e.exception.status_code,  500)
        self.assertGreaterEqual(len(e.exception.exception_details), 1)

    def _test_patch(self, model_obj, attr, new_attr_value=None):
        """ Generic test worker to PATCH object"""
        # Create new value
        if new_attr_value is None:
            stamp = datetime.datetime.now().isoformat()
            new_attr_value = getattr(model_obj, attr) + f' - {stamp}'

        # PATCH value
        setattr(model_obj, attr, new_attr_value)
        model_obj.save()

        # Retrieve object again and check if updated
        obj = self.model_class.objects.get(pk=model_obj.id)
        self.assertEqual(getattr(obj, attr), new_attr_value)

    @classmethod
    def tearDown(cls):
        while len(cls.obj_heap) > 0:
            obj = cls.obj_heap.pop()
            obj.delete()

    def delete_after_test(self, obj):
        self.obj_heap.append(obj)
