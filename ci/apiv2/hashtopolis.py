#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import copy
import json
import requests
from pathlib import Path

import logging

import http
import confidence
import tusclient.client
from tusclient.exceptions import TusCommunicationError

logger = logging.getLogger(__name__)

HTTP_DEBUG = False

# Monkey patching to allow http debugging
if HTTP_DEBUG:
    http_logger = logging.getLogger('http.client')
    http.client.HTTPConnection.debuglevel = 0
    def print_to_log(*args):  # noqa:E301
        http_logger.debug(" ".join(args))
    http.client.print = print_to_log

cls_registry = {}


class HashtopolisError(Exception):
    def __init__(self, *args, **kwargs):
        super().__init__(*args)
        self.exception_details = kwargs.get('exception_details', [])
        self.message = kwargs.get('message', '')
        self.status_code = kwargs.get('status_code', None)


class HashtopolisConfig(object):
    def __init__(self):
        # Request access TOKEN, used throughout the test
        load_order = (str(Path(__file__).parent.joinpath('{name}-defaults.{extension}')),) \
                     + confidence.DEFAULT_LOAD_ORDER
        self._cfg = confidence.load_name('hashtopolis-test', load_order=load_order)
        self._hashtopolis_uri = self._cfg['hashtopolis_uri']
        self._api_endpoint = self._hashtopolis_uri + '/api/v2'
        self.username = self._cfg['username']
        self.password = self._cfg['password']


class HashtopolisResponseError(HashtopolisError):
    pass


class HashtopolisConnector(object):
    # Cache authorisation token per endpoint
    token = {}
    token_expires = {}

    @staticmethod
    def resp_to_json(response):
        content_type_header = response.headers.get('Content-Type', '')
        if 'application/json' in content_type_header:
            return response.json()
        else:
            raise HashtopolisResponseError("Response type '%s' is not valid JSON document, text='%s'" %
                                           (content_type_header, response.text),
                                           status_code=response.status_code)

    def __init__(self, model_uri, config):
        self._model_uri = model_uri
        self._api_endpoint = config._api_endpoint
        self._hashtopolis_uri = config._hashtopolis_uri
        self.config = config

    def authenticate(self):
        if self._api_endpoint not in HashtopolisConnector.token:
            # Request access TOKEN, used throughout the test

            logger.info("Start authentication")
            auth_uri = self._api_endpoint + '/auth/token'
            auth = (self.config.username, self.config.password)
            r = requests.post(auth_uri, auth=auth)
            self.validate_status_code(r, [201], "Authentication failed")

            r_json = self.resp_to_json(r)
            HashtopolisConnector.token[self._api_endpoint] = r_json['token']
            HashtopolisConnector.token_expires[self._api_endpoint] = r_json['token']

        self._token = HashtopolisConnector.token[self._api_endpoint]
        self._token_expires = HashtopolisConnector.token_expires[self._api_endpoint]

        self._headers = {
            'Authorization': 'Bearer ' + self._token,
            'Content-Type': 'application/json'
        }

    def validate_status_code(self, r, expected_status_code, error_msg):
        """ Validate response and convert to python exception """
        # Status code 204 is special and should have no JSON output
        if r.status_code == 204:
            assert (r.text == '')
            return

        # Expected responses below should be valid JSON
        r_json = self.resp_to_json(r)

        # Application hits a problem
        if r.status_code not in expected_status_code:
            raise HashtopolisError(
                "%s (status_code=%s): %s" % (error_msg, r.status_code, r.text),
                status_code=r.status_code,
                exception_details=r_json.get('exception', []),
                message=r_json.get('message', None))

    def filter(self, expand, max_results, ordering, filter):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri
        headers = self._headers

        filter_list = [f'{k}={v}' for k, v in filter.items()]
        payload = {
            'filter': filter_list,
            'maxResults': max_results if max_results is not None else 999,
        }
        if expand is not None:
            payload['expand'] = expand
        if ordering is not None:
            if type(ordering) is not list:
                payload['ordering'] = [ordering]
            else:
                payload['ordering'] = ordering

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [200], "Filtering failed")
        return self.resp_to_json(r).get('values')

    def get_one(self, pk, expand):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri + f'/{pk}'
        headers = self._headers

        payload = {}
        if expand is not None:
            payload['expand'] = expand

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [200], "Get single object failed")
        return self.resp_to_json(r)

    def patch_one(self, obj):
        if not obj.has_changed():
            logger.debug("Object '%s' has not changed, no PATCH required", obj)
            return

        self.authenticate()
        uri = self._hashtopolis_uri + obj._self
        headers = self._headers
        payload = {}

        for k, v in obj.diff().items():
            logger.debug("Going to patch object '%s' property '%s' from '%s' to '%s'", obj, k, v[0], v[1])
            payload[k] = v[1]

        logger.debug("Sending PATCH payload: %s to %s", json.dumps(payload), uri)
        r = requests.patch(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [201], "Patching failed")

        # TODO: Validate if return objects matches digital twin
        obj.set_initial(self.resp_to_json(r).copy())

    def create(self, obj):
        # Check if object to be created is new
        assert not hasattr(obj, '_self')

        self.authenticate()
        uri = self._api_endpoint + self._model_uri
        headers = self._headers
        payload = obj.get_fields()

        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [201], "Creation of object failed")

        # TODO: Validate if return objects matches digital twin
        obj.set_initial(self.resp_to_json(r).copy())

    def delete(self, obj):
        """ Delete object from database """
        # TODO: Check if object to be deleted actually exists
        assert hasattr(obj, '_self')

        self.authenticate()
        uri = self._hashtopolis_uri + obj._self
        headers = self._headers
        payload = {}

        r = requests.delete(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [204], "Deletion of object failed")

        # TODO: Cleanup object to allow re-creation


class ManagerBase(type):
    conn = {}
    # Cache configuration values
    config = None

    @classmethod
    def get_conn(cls):
        if cls.config is None:
            cls.config = HashtopolisConfig()

        if cls._model_uri not in cls.conn:
            cls.conn[cls._model_uri] = HashtopolisConnector(cls._model_uri, cls.config)
        return cls.conn[cls._model_uri]

    @classmethod
    def all(cls, expand=None, max_results=None, ordering=None):
        """
        Retrieve all backend objects
        TODO: Make iterator supporting loading of objects via pages
        """
        return cls.filter(expand, max_results, ordering)

    @classmethod
    def patch(cls, obj):
        cls.get_conn().patch_one(obj)

    @classmethod
    def create(cls, obj):
        cls.get_conn().create(obj)

    @classmethod
    def delete(cls, obj):
        cls.get_conn().delete(obj)

    @classmethod
    def get_first(cls):
        """
        Retrieve first object
        TODO: Error handling if first object does not exists
        TODO: Request object with limit parameter instead
        """
        return cls.all()[0]

    @classmethod
    def get(cls, expand=None, ordering=None, **kwargs):
        if 'pk' in kwargs:
            try:
                api_obj = cls.get_conn().get_one(kwargs['pk'], expand)
            except HashtopolisError as e:
                if e.status_code == 404:
                    raise cls._model.DoesNotExist
                else:
                    # Re-raise error if generic failure took place
                    raise
            new_obj = cls._model(**api_obj)
            return new_obj
        else:
            objs = cls.filter(expand, ordering, **kwargs)
            if len(objs) == 0:
                raise cls._model.DoesNotExist
            elif len(objs) > 1:
                raise cls._model.MultipleObjectsReturned
            return objs[0]

    @classmethod
    def filter(cls, expand=None, max_results=None, ordering=None, **kwargs):
        # Get all objects
        api_objs = cls.get_conn().filter(expand, max_results, ordering, kwargs)

        # Convert into class
        objs = []
        if api_objs:
            for api_obj in api_objs:
                new_obj = cls._model(**api_obj)
                objs.append(new_obj)
        return objs


class ObjectDoesNotExist(Exception):
    """The requested object does not exist"""


class MultipleObjectsReturned(Exception):
    """The query returned multiple objects when only one was expected."""


# Build Django ORM style 'ModelName.objects' interface
class ModelBase(type):
    def __new__(cls, clsname, bases, attrs, uri=None, **kwargs):
        parents = [b for b in bases if isinstance(b, ModelBase)]
        if not parents:
            return super().__new__(cls, clsname, bases, attrs)

        new_class = super().__new__(cls, clsname, bases, attrs)

        setattr(new_class, 'objects', type('Manager', (ManagerBase,), {'_model_uri': uri}))
        setattr(new_class.objects, '_model', new_class)

        def add_to_class(class_name, class_type):
            setattr(new_class,
                    class_name,
                    type(class_name, (class_type,), {
                        "__qualname__": "%s.%s" % (new_class.__qualname__, class_name),
                        '__module__': "%s.%s" % (__name__, new_class.__name__)
                        }))
        add_to_class('DoesNotExist', ObjectDoesNotExist)
        add_to_class('MultipleObjectsReturned', MultipleObjectsReturned)

        cls_registry[clsname] = new_class

        # Insert Meta properties
        if hasattr(new_class, 'Meta'):
            META_FIELDS = ['verbose_name', 'verbose_name_plural']
            for field in META_FIELDS:
                if hasattr(new_class.Meta, field):
                    setattr(new_class, field, getattr(new_class.Meta, field))

        if not hasattr(new_class, 'verbose_name'):
            new_class.verbose_name = new_class.__name__

        if not hasattr(new_class, 'verbose_name_plural'):
            new_class.verbose_name_plural = new_class.verbose_name + 's'

        return new_class


class Model(metaclass=ModelBase):
    def __init__(self, *args, **kwargs):
        self.set_initial(kwargs)
        super().__init__()

    def __repr__(self):
        return self._self

    def __eq__(self, other):
        return (self.get_fields() == other.get_fields())

    def _dict2obj(self, dict):
        # Function to convert a dict to an object.
        uri = dict.get('_self')
        # Loop through all the registers classes
        for _, model in cls_registry.items():
            model_uri = model.objects._model_uri
            # Check if part of the uri is in the model uri
            if model_uri in uri:
                return model(**dict)
        # If we are here, it means that no uri matched, thus we don't know the object.
        raise TypeError('Object not valid model')

    def set_initial(self, kv):
        self.__fields = []
        self.__expanded = []
        # Store fields allowing us to detect changed values
        if '_self' in kv:
            self.__initial = copy.deepcopy(kv)
        else:
            # New model
            self.__initial = {}

        # Create attribute values
        for k, v in kv.items():
            # In case expand is true, there can be a attribute which also is an object.
            # Example: Users in AccessGroups. This part will convert the returned data.
            # Into proper objects.
            if type(v) is list and len(v) > 0:
                # Many-to-Many relation
                obj_list = []
                # Loop through all the values in the list and convert them to objects.
                for dict_v in v:
                    if type(dict_v) is dict and dict_v.get('_self'):
                        # Double check that it really is an object
                        obj = self._dict2obj(dict_v)
                        obj_list.append(obj)
                # Set the attribute of the current object to a set object (like Django)
                # Also check if it really were objects
                if len(obj_list) > 0:
                    setattr(self, f"{k}_set", obj_list)
                    self.__expanded.append(f"{k}_set")
                    continue
            # This does the same as above, only one-to-one relations
            if type(v) is dict and v.get('_self'):
                setattr(self, f"{k}", self._dict2obj(v))
                self.__expanded.append(f"{k}")
                continue

            # Skip over field 'id', as it is automatic property of model itself.
            # This should be removed if there is a concensus on the full model.
            # Example: not rightgroupName but name, and not rightgroupId but id
            if k != 'id':
                setattr(self, k, v)

            if not k.startswith('_'):
                self.__fields.append(k)

    def get_fields(self):
        return dict([(k, getattr(self, k)) for k in sorted(self.__fields)])

    def diff(self):
        # Stored database values
        d_initial = self.__initial
        # Possible changes values
        d_current = self.get_fields()
        diffs = []
        for key, v_current in d_current.items():
            v_innitial = d_initial[key]
            if v_current != v_innitial:
                diffs.append((key, (v_innitial, v_current)))

        # Find expandables sets which have changed
        for expand in self.__expanded:
            if expand.endswith('_set'):
                innitial_name = expand[:-4]
                # Retrieve innitial keys
                v_innitial = self.__initial[innitial_name]
                v_innitial_ids = [x['_id'] for x in v_innitial]
                # Retrieve new/current keys
                v_current = getattr(self, expand)
                v_current_ids = [x.id for x in v_current]
                # Use ID of ojbects as new current/update identifiers
                if sorted(v_innitial_ids) != sorted(v_current_ids):
                    diffs.append((innitial_name, (v_innitial_ids, v_current_ids)))

        return dict(diffs)

    def has_changed(self):
        return bool(self.diff())

    def save(self):
        if hasattr(self, '_self'):
            self.objects.patch(self)
        else:
            self.objects.create(self)
        return self

    def delete(self):
        if hasattr(self, '_self'):
            self.objects.delete(self)

    def serialize(self):
        retval = dict([(x, getattr(self, x)) for x in self.__fields] + [('_self', self._self), ('_id', self._id)])
        for expandable in self.__expanded:
            if expandable.endswith('_set'):
                retval[expandable] = [x.serialize() for x in getattr(self, expandable)]
            else:
                retval[expandable] = getattr(self, expandable).serialize()
        return retval

    @property
    def id(self):
        return self._id


##
# Begin of API objects
#
class AccessGroup(Model, uri="/ui/accessgroups"):
    pass


class Agent(Model, uri="/ui/agents"):
    pass


class AgentStat(Model, uri="/ui/agentstats"):
    pass


class AgentBinary(Model, uri="/ui/agentbinaries"):
    class Meta:
        verbose_name_plural = 'AgentBinaries'


class AgentAssignment(Model, uri="/ui/agentassignments"):
    pass


class Chunk(Model, uri="/ui/chunks"):
    pass


class Config(Model, uri="/ui/configs"):
    pass


class ConfigSection(Model, uri="/ui/configsections"):
    pass


class Cracker(Model, uri="/ui/crackers"):
    pass


class CrackerType(Model, uri="/ui/crackertypes"):
    pass


class File(Model, uri="/ui/files"):
    pass


class GlobalPermissionGroup(Model, uri="/ui/globalpermissiongroups"):
    pass


class Hash(Model, uri="/ui/hashes"):
    class Meta:
        verbose_name_plural = 'Hashes'


class Hashlist(Model, uri="/ui/hashlists"):
    pass


class HashType(Model, uri="/ui/hashtypes"):
    pass


class HealthCheck(Model, uri="/ui/healthchecks"):
    pass


class HealthCheckAgent(Model, uri="/ui/healthcheckagents"):
    pass


class LogEntry(Model, uri="/ui/logentries"):
    class Meta:
        verbose_name_plural = 'LogEntries'


class Notification(Model, uri="/ui/notifications"):
    pass


class Preprocessor(Model, uri="/ui/preprocessors"):
    pass


class Pretask(Model, uri="/ui/pretasks"):
    pass


class Speed(Model, uri="/ui/speeds"):
    pass


class Supertask(Model, uri="/ui/supertasks"):
    pass


class Task(Model, uri="/ui/tasks"):
    pass


class TaskWrapper(Model, uri="/ui/taskwrappers"):
    pass


class User(Model, uri="/ui/users"):
    pass


class Voucher(Model, uri="/ui/vouchers"):
    pass
#
# End of API objects
##


class FileImport(HashtopolisConnector):
    def __init__(self):
        super().__init__("/helper/importFile", HashtopolisConfig())

    def __repr__(self):
        return self._self

    def do_upload(self, filename, file_stream, chunk_size=1000000000):
        self.authenticate()

        uri = self._api_endpoint + self._model_uri

        my_client = tusclient.client.TusClient(uri)
        del self._headers['Content-Type']
        my_client.set_headers(self._headers)

        metadata = {"filename": filename,
                    "filetype": "application/text"}
        uploader = my_client.uploader(
                file_stream=file_stream,
                chunk_size=chunk_size,
                upload_checksum=True,
                metadata=metadata
                )
        try:
            uploader.upload()
        except TusCommunicationError as e:
            response_content = e.response_content.decode('utf-8')
            raise HashtopolisResponseError(f"{e}: {response_content}",
                                           exception_details=response_content,
                                           status_code=e.status_code)


class Meta(HashtopolisConnector):
    def __init__(self):
        super().__init__("/openapi.json", HashtopolisConfig())

    def get_meta(self):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri
        r = requests.get(uri)
        self.validate_status_code(r, [200], "Unable to retrieve Meta definitions")
        return self.resp_to_json(r)


class Helper(HashtopolisConnector):
    def __init__(self):
        super().__init__("/helper/", HashtopolisConfig())

    def _helper_request(self, helper_uri, payload):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri + helper_uri
        headers = self._headers

        logging.debug(f"Makeing POST request to {uri}, headers={headers} payload={payload}")
        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [200], f"Helper request at {uri} failed")
        if r.status_code == 204:
            return None
        else:
            return self.resp_to_json(r)

    def _test_authentication(self, username, password):
        auth_uri = self._api_endpoint + '/auth/token'
        auth = (username, password)
        r = requests.post(auth_uri, auth=auth)
        self.validate_status_code(r, [201], "Authentication failed")

    def abort_chunk(self, chunk):
        payload = {
            'chunkId': chunk.id,
        }
        return self._helper_request("abortChunk", payload)

    def create_supertask(self, supertask, hashlist, cracker):
        payload = {
          'supertaskTemplateId': supertask.id,
          'hashlistId': hashlist.id,
          'crackerVersionId': cracker.id,
        }
        # Response is JSON:API type
        response = self._helper_request("createSupertask", payload)
        return TaskWrapper(**response['data'])

    def create_superhashlist(self, name, hashlists):
        payload = {
          'name': name,
          'hashlistIds': [x.id for x in hashlists],
        }
        # Response is JSON:API type
        response = self._helper_request("createSuperHashlist", payload)
        return Hashlist(**response['data'])

    def set_user_password(self, user, password):
        payload = {
            'userId': user.id,
            'password': password,
        }
        return self._helper_request("setUserPassword", payload)

    def reset_chunk(self, chunk):
        payload = {
            'chunkId': chunk.id,
        }
        return self._helper_request("resetChunk", payload)

    def purge_task(self, task):
        payload = {
            'taskId': task.id,
        }
        return self._helper_request("purgeTask", payload)

    def export_cracked_hashes(self, hashlist):
        payload = {
            'hashlistId': hashlist.id,
        }
        response = self._helper_request("exportCrackedHashes", payload)
        return File(**response['data'])
