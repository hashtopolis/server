#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import json
import requests
from pathlib import Path

import logging

import http
import confidence
import tusclient.client

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
    pass


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


class HashtopolisConnector(object):
    # Cache authorisation token per endpoint
    token = {}
    token_expires = {}

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

            HashtopolisConnector.token[self._api_endpoint] = r.json()['token']
            HashtopolisConnector.token_expires[self._api_endpoint] = r.json()['token']

        self._token = HashtopolisConnector.token[self._api_endpoint]
        self._token_expires = HashtopolisConnector.token_expires[self._api_endpoint]

        self._headers = {
            'Authorization': 'Bearer ' + self._token,
            'Content-Type': 'application/json'
        }

    def validate_status_code(self, r, expected_status_code, error_msg):
        if r.status_code != expected_status_code:
            raise HashtopolisError("%s (status_code: %s != %s): %s", error_msg, r.status_code,
                                   expected_status_code, r.text)

    def filter(self, expand, filter):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri
        headers = self._headers

        filter_list = []
        cast = {
            '__gt': '>',
            '__gte': '>=',
            '__lt': '<',
            '__lte': '<=',
        }
        for k, v in filter.items():
            filter_item = None
            for k2, v2 in cast.items():
                if k.endswith(k2):
                    filter_item = f'{k[:-len(k2)]}{v2}{v}'
                    break
            # Default to equal assignment
            if filter_item is None:
                filter_item = f'{k}={v}'
            filter_list.append(filter_item)

        payload = {
            'filter': filter_list,
            'expand': expand,
            'maxResults': 999,
        }

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, 200, "Filtering failed")
        return r.json().get('values')

    def get_one(self, pk, expand):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri + f'/{pk}'
        headers = self._headers

        payload = {
            'expand': expand,
        }

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, 200, "Filtering failed")
        return r.json()

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

        r = requests.patch(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, 201, "Patching failed")

        # TODO: Validate if return objects matches digital twin
        obj.set_initial(r.json().copy())

    def create(self, obj):
        # Check if object to be created is new
        assert not hasattr(obj, '_self')

        self.authenticate()
        uri = self._api_endpoint + self._model_uri
        headers = self._headers
        payload = dict([(k, v[1]) for (k, v) in obj.diff().items()])

        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, 201, "Creation of object failed")

        # TODO: Validate if return objects matches digital twin
        obj.set_initial(r.json().copy())

    def delete(self, obj):
        """ Delete object from database """
        # TODO: Check if object to be deleted actually exists
        assert hasattr(obj, '_self')

        self.authenticate()
        uri = self._hashtopolis_uri + obj._self
        headers = self._headers
        payload = {}

        r = requests.delete(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, 204, "Deletion of object failed")

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
    def all(cls, expand=None):
        """
        Retrieve all backend objects
        TODO: Make iterator supporting loading of objects via pages
        """
        return cls.filter(expand)

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
    def get(cls, expand=None, **kwargs):
        if 'pk' in kwargs:
            api_obj = cls.get_conn().get_one(kwargs['pk'], expand)
            new_obj = cls._model(**api_obj)
            return new_obj
        else:
            objs = cls.filter(expand, **kwargs)
            assert len(objs) == 1
            return objs[0]

    @classmethod
    def filter(cls, expand=None, **kwargs):
        # Get all objects
        api_objs = cls.get_conn().filter(expand, kwargs)

        # Convert into class
        objs = []
        if api_objs:
            for api_obj in api_objs:
                new_obj = cls._model(**api_obj)
                objs.append(new_obj)
        return objs


# Build Django ORM style 'ModelName.objects' interface
class ModelBase(type):
    def __new__(cls, clsname, bases, attrs, uri=None, **kwargs):
        parents = [b for b in bases if isinstance(b, ModelBase)]
        if not parents:
            return super().__new__(cls, clsname, bases, attrs)

        new_class = super().__new__(cls, clsname, bases, attrs)

        setattr(new_class, 'objects', type('Manager', (ManagerBase,), {'_model_uri': uri}))
        setattr(new_class.objects, '_model', new_class)
        cls_registry[clsname] = new_class

        return new_class


class Model(metaclass=ModelBase):
    def __init__(self, *args, **kwargs):
        self.set_initial(kwargs)
        super().__init__()

    def _dict2obj(self, dict):
        # Function to convert a dict to an object.
        uri = dict.get('_self')

        # Loop through all the registers classes
        for modelname, model in cls_registry.items():
            model_uri = model.objects._model_uri
            # Check if part of the uri is in the model uri
            if model_uri in uri:

                obj = model()

                # Set all the attributes of the object.
                for k2, v2 in dict.items():
                    # See set_initial why the if statement is here
                    if k2 != 'id':
                        setattr(obj, k2, v2)
                if not k2.startswith('_'):
                    obj.__fields.append(k2)
                return obj
        # If we are here, it means that no uri matched, thus we don't know the object.
        raise TypeError('Object not valid model')

    def set_initial(self, kv):
        self.__fields = []
        # Store fields allowing us to detect changed values
        if '_self' in kv:
            self.__initial = kv.copy()
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
                    continue
            # This does the same as above, only one-to-one relations
            if type(v) is dict and v.get('_self'):
                setattr(self, f"{k}_set", self._dict2obj(v))
                continue

            # Skip over ID, as it is also something from the model itself.
            # This should be removed if there is a concensus on the full model.
            # Example: not rightgroupName but name, and not rightgroupId but id
            if k != 'id':
                setattr(self, k, v)

            if not k.startswith('_'):
                self.__fields.append(k)

    def diff(self):
        d1 = self.__initial
        d2 = dict([(k, getattr(self, k)) for k in self.__fields])
        diffs = [(k, (v, d2[k])) for k, v in d2.items() if v != d1.get(k, None)]
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
        return dict([(x, getattr(self, x)) for x in self.__fields] + [('_self', self._self), ('_id', self._id)])

    @property
    def id(self):
        return self._id


class Agent(Model, uri="/ui/agents"):
    def __repr__(self):
        return self._self


class Task(Model, uri="/ui/tasks"):
    def __repr__(self):
        return self._self


class Pretask(Model, uri="/ui/pretasks"):
    def __repr__(self):
        return self._self


class User(Model, uri="/ui/users"):
    def __repr__(self):
        return self._self


class Hashlist(Model, uri="/ui/hashlists"):
    def __repr__(self):
        return self._self


class HashType(Model, uri="/ui/hashtypes"):
    def __repr__(self):
        return self._self


class Hash(Model, uri="/ui/hashes"):
    def __repr__(self):
        return self._self


class AccessGroup(Model, uri="/ui/accessgroups"):
    def __repr__(self):
        return self._self


class Cracker(Model, uri="/ui/crackers"):
    def __repr__(self):
        return self._self


class CrackerType(Model, uri="/ui/crackertypes"):
    def __repr__(self):
        return self._self


class Config(Model, uri="/ui/configs"):
    def __repr__(self):
        return self._self


class File(Model, uri="/ui/files"):
    def __repr__(self):
        return self._self


class GlobalPermissionGroup(Model, uri="/ui/globalpermissiongroups"):
    def __repr__(self):
        return self._self


class FileImport(HashtopolisConnector):
    def __init__(self):
        super().__init__("/ui/files/import", HashtopolisConfig())

    def __repr__(self):
        return self._self

    def do_upload(self, filename, file_stream):
        self.authenticate()

        uri = self._api_endpoint + self._model_uri

        my_client = tusclient.client.TusClient(uri)
        del self._headers['Content-Type']
        my_client.set_headers(self._headers)

        metadata = {"filename": filename,
                    "filetype": "application/text"}
        uploader = my_client.uploader(
                file_stream=file_stream,
                chunk_size=1000000000,
                upload_checksum=True,
                metadata=metadata
                )
        uploader.upload()


class Voucher(Model, uri="/ui/vouchers"):
    def __repr__(self):
        return self._self


class Speed(Model, uri="/ui/speeds"):
    def __repr__(self):
        return self._self
