import abc
import datetime
from io import BytesIO
import json
from pathlib import Path
import requests
import tempfile
import time
import unittest
import zipfile

import confidence

from hashtopolis import AccessGroup
from hashtopolis import Agent
from hashtopolis import AgentAssignment
from hashtopolis import AgentBinary
from hashtopolis import Cracker
from hashtopolis import CrackerType
from hashtopolis import File
from hashtopolis import FileImport
from hashtopolis import GlobalPermissionGroup
from hashtopolis import Hashlist
from hashtopolis import HashtopolisError
from hashtopolis import HashType
from hashtopolis import HealthCheck
from hashtopolis import Notification
from hashtopolis import Preprocessor
from hashtopolis import Pretask
from hashtopolis import Supertask
from hashtopolis import Task
from hashtopolis import User
from hashtopolis import Voucher

from hashtopolis_agent import DummyAgent


def _do_create_obj_from_file(model_class, file_prefix, extra_payload={}, **kwargs):
    file_id = kwargs.get('file_id') or '001'
    p = Path(__file__).parent.joinpath(f'testfiles/{file_prefix}_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    final_payload = {**payload, **extra_payload}
    obj = model_class(**final_payload)
    obj.save()
    return obj


def do_create_dummy_agent():
    stamp = datetime.datetime.now().isoformat()
    voucher = do_create_voucher()

    dummy_agent = DummyAgent()
    dummy_agent.register(voucher=voucher.voucher, name=f'test-agent-{stamp}')
    dummy_agent.login()

    # Add some dummy GPUs
    dummy_agent.update_information()

    # Validate automatically deleted when an test-agent claims the voucher
    assert list(Voucher.objects.filter(id=voucher.id)) == []

    agent = Agent.objects.get(agentName=dummy_agent.name)
    return (dummy_agent, agent)


def do_create_agent():
    return do_create_dummy_agent()[1]


def do_create_agent_with_task(gpu_temperatures=None, gpu_utilisations=None,
                              cpu_utilisations=None, **kwargs):
    dummy_agent, agent = do_create_dummy_agent()
    hashlist = do_create_hashlist()
    task = do_create_task(hashlist=hashlist)
    # Assign agent to task created
    _ = do_create_agentassignent(agent, task)

    # agent: Retrieve required Task
    dummy_agent.get_task()
    dummy_agent.get_hashlist()
    # agent: Calculate Keyspace
    dummy_agent.get_chunk()
    dummy_agent.send_keyspace()
    # agent: Send benchmark information
    dummy_agent.get_chunk()
    dummy_agent.send_benchmark()
    # agent: Send progress information
    dummy_agent.get_chunk()

    dummy_agent.send_process(progress=50, gpu_temperatures=gpu_temperatures,
                             gpu_utilisations=gpu_utilisations,
                             cpu_utilisations=cpu_utilisations)

    return dict(dummy_agent=dummy_agent, agent=agent, hashlist=hashlist, task=task)


def do_create_agentassignent(agent, task):
    return AgentAssignment(agentId=agent.id, taskId=task.id).save()


def do_create_agentbinary(**kwargs):
    return _do_create_obj_from_file(AgentBinary, 'create_agentbinary', **kwargs)


def do_create_accessgroup(**kwargs):
    return _do_create_obj_from_file(AccessGroup, 'create_accessgroup', **kwargs)


def do_create_cracker(**kwargs):
    return _do_create_obj_from_file(Cracker, 'create_cracker', **kwargs)


def do_create_crackertype(**kwargs):
    return _do_create_obj_from_file(CrackerType, 'create_crackertype', **kwargs)


def do_create_file(content='12345678\n123456\nprincess\n'.encode('utf-8'), extra_payload={}, **kwargs):
    stamp = datetime.datetime.now().isoformat()
    fname_base = kwargs.get('filename', f'test-{stamp}.txt')

    file_import = FileImport()
    if kwargs.get('compress', False):
        with tempfile.NamedTemporaryFile() as fh:
            with zipfile.ZipFile(fh.name, mode='w') as myzip:
                myzip.writestr(fname_base, content)
            fh.seek(0)
            filename = f'{fname_base}.zip'
            content_final = fh.read()
    else:
        filename = f'{fname_base}'
        content_final = content

    file_import.do_upload(filename, BytesIO(content_final))

    extra_payload['sourceData'] = filename
    extra_payload['filename'] = filename
    return _do_create_obj_from_file(File, 'create_file', extra_payload, **kwargs)


def do_create_globalpermissiongroup(permissions={'permHashlistRead': True}, **kwargs):
    stamp = int(time.time() * 1000)
    payload = dict(
        name=f'group-{stamp}',
        permissions=permissions,
    )
    obj = GlobalPermissionGroup(**payload)
    obj.save()
    return obj


def do_create_hashlist(**kwargs):
    return _do_create_obj_from_file(Hashlist, 'create_hashlist', **kwargs)


def do_create_hashtype(**kwargs):
    return _do_create_obj_from_file(HashType, 'create_hashtype', **kwargs)


def do_create_healthcheck(**kwargs):
    return _do_create_obj_from_file(HealthCheck, 'create_healthcheck', **kwargs)


def do_create_notification(**kwargs):
    return _do_create_obj_from_file(Notification, 'create_notification', **kwargs)


def do_create_preprocessor(**kwargs):
    return _do_create_obj_from_file(Preprocessor, 'create_preprocessor', **kwargs)


def do_create_pretask(files=[], extra_payload={}, **kwargs):
    extra_payload['files'] = [file.id for file in files]
    return _do_create_obj_from_file(Pretask, 'create_pretask', extra_payload, **kwargs)


def do_create_supertask(pretasks=[], extra_payload={}, **kwargs):
    extra_payload['pretasks'] = [pretask.id for pretask in pretasks]
    return _do_create_obj_from_file(Supertask, 'create_supertask', extra_payload, **kwargs)


def do_create_task(hashlist, extra_payload={}, **kwargs):
    extra_payload['hashlistId'] = int(hashlist.id)
    return _do_create_obj_from_file(Task, 'create_task', extra_payload, **kwargs)


def do_create_user(global_permission_group_id=1):
    stamp = int(time.time() * 1000)
    payload = dict(
        name=f'test-{stamp}',
        email='test@example.com',
        globalPermissionGroupId=global_permission_group_id,
        isValid=True,
    )
    obj = User(**payload)
    obj.save()
    return obj


def do_create_voucher():
    stamp = int(time.time() * 1000)
    return Voucher(voucher=f'dummy-test-{stamp}').save()


def find_stale_test_objects():
    # Order matters, for example a Task needs to be removed before Hashlist can be removed
    # Note: we are not removing default database objects
    test_objs = []
    test_objs.extend(HashType.objects.filter(hashTypeId=98765))
    test_objs.extend(HashType.objects.filter(hashTypeId__gte=90000, hashTypeId__lte=91000))
    test_objs.extend(AccessGroup.objects.filter(groupName="Testing Group"))
    test_objs.extend(Notification.objects.all())
    test_objs.extend(HealthCheck.objects.all())
    test_objs.extend(Agent.objects.all())
    test_objs.extend(Voucher.objects.all())
    test_objs.extend(Supertask.objects.all())
    test_objs.extend(Task.objects.all())
    test_objs.extend(Pretask.objects.all())
    test_objs.extend(Hashlist.objects.all())
    test_objs.extend(File.objects.all())
    test_objs.extend(User.objects.filter(id__gt=1))
    test_objs.extend(GlobalPermissionGroup.objects.filter(id__gt=1))
    test_objs.extend(Cracker.objects.filter(id__gt=1))
    test_objs.extend(CrackerType.objects.filter(id__gt=1))
    return test_objs


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

    def _create_test_object(self, model_create_func, *nargs, delete=True, **kwargs):
        model_obj = model_create_func(*nargs, **kwargs)
        if delete:
            self.delete_after_test(model_obj)
        self.assertIsNotNone(model_obj)
        return model_obj

    def create_test_object(self, *nargs, **kwargs):
        raise NotImplementedError("Implement class specific create_test_object mapping function")

    def create_accessgroup(self, **kwargs):
        return self._create_test_object(do_create_accessgroup, **kwargs)

    def create_agent(self, **kwargs):
        return self._create_test_object(do_create_agent, **kwargs)

    def create_agentbinary(self, **kwargs):
        return self._create_test_object(do_create_agentbinary, **kwargs)

    def create_agentassignment(self, **kwargs):
        agent = self.create_agent()
        hashlist = self.create_hashlist()
        task = self.create_task(hashlist)
        return self._create_test_object(do_create_agentassignent, agent, task, **kwargs)

    def create_cracker(self, **kwargs):
        return self._create_test_object(do_create_cracker, **kwargs)

    def create_crackertype(self, **kwargs):
        return self._create_test_object(do_create_crackertype, **kwargs)

    def create_agent_with_task(self, *nargs, **kwargs):
        retval = do_create_agent_with_task(*nargs, **kwargs)
        self.assertEqual(retval['dummy_agent'].task['hashlistId'], retval['hashlist'].id,
                         "Hashlist created is not being working on by agent!")

        if kwargs.get('delete', True):
            self.delete_after_test(retval['agent'])
            self.delete_after_test(retval['hashlist'])
            self.delete_after_test(retval['task'])
        return retval

    def create_file(self, **kwargs):
        return self._create_test_object(do_create_file, **kwargs)

    def create_globalpermissiongroup(self, **kwargs):
        return self._create_test_object(do_create_globalpermissiongroup, **kwargs)

    def create_healthcheck(self, **kwargs):
        return self._create_test_object(do_create_healthcheck, **kwargs)

    def create_hashlist(self, **kwargs):
        return self._create_test_object(do_create_hashlist, **kwargs)

    def create_hashtype(self, **kwargs):
        return self._create_test_object(do_create_hashtype, **kwargs)

    def create_notification(self, **kwargs):
        return self._create_test_object(do_create_notification, **kwargs)

    def create_preprocessor(self, **kwargs):
        return self._create_test_object(do_create_preprocessor, **kwargs)

    def create_pretask(self, **kwargs):
        return self._create_test_object(do_create_pretask, **kwargs)

    def create_supertask(self, pretasks, **kwargs):
        return self._create_test_object(do_create_supertask, pretasks, **kwargs)

    def create_task(self, hashlist, **kwargs):
        return self._create_test_object(do_create_task, hashlist, **kwargs)

    def create_user(self, **kwargs):
        return self._create_test_object(do_create_user, **kwargs)

    def create_voucher(self, **kwargs):
        return self._create_test_object(do_create_voucher, **kwargs)

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
        obj = self.model_class.objects.prefetch_related(*expandables).get(pk=model_obj.id)
        self.assertIsNotNone(obj)
        for expandable in expandables:
            self.assertTrue(hasattr(obj, expandable) or hasattr(obj, f"{expandable}_set"),
                            f"Expand attribute '{expandable}' not found in model")

    def _test_exception(self, func_create, *args, **kwargs):
        with self.assertRaises(HashtopolisError) as e:
            _ = func_create(*args, **kwargs)
        self.assertIn(e.exception.status_code,  [403, 500, 400])
        # checks len of both old and new exceptions style, TODO: old can be removed when ervything has been refactored.
        self.assertTrue(len(e.exception.exception_details) >= 1 or len(e.exception.title) >= 1)

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

    def tearDown(self):
        while len(self.obj_heap) > 0:
            obj = self.obj_heap.pop()
            obj.delete()

        # Testing the internal test framework
        # TODO: This is potentially really confusing since it will cause all tests to fail if database consist of
        # TODO: stale (test) objects. Potential workaround would be to run the following command on an empty database
        # TODO: to find out which test is creating test entries and not removing them:
        # TODO:     for T in test_*.py; do pytest $T && ./htcli.py run delete-test-data --commit || exit 1; done
        #
        # test_objs = find_stale_test_objects()
        # self.assertEqual(len(test_objs), 0, msg=f"Created objects are not marked for removal! {test_objs}")

    def delete_after_test(self, obj):
        self.obj_heap.append(obj)
