#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import copy
import json
import logging
from pathlib import Path
import requests
import sys
import urllib

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


class IncludedCache(object):
    """
    Cast (potentially) included objects to object structure which
    allows for caching and easier retrival
    """
    def __init__(self, included_objects):
        self._cache = {}
        for included_obj in included_objects:
            self._cache[self.get_object_uuid(included_obj)] = included_obj

    @staticmethod
    def get_object_uuid(obj):
        """ Generate unique key identifier for object """
        return "%s.%i" % (obj['type'], obj['id'])

    def get(self, obj):
        return self._cache[self.get_object_uuid(obj)]


class HashtopolisConnector(object):
    # Cache authorisation token per endpoint
    token = {}
    token_expires = {}

    @staticmethod
    def resp_to_json(response):
        content_type_header = response.headers.get('Content-Type', '')
        if any([x in content_type_header for x in ('application/vnd.api+json', 'application/json')]):
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
            'Authorization': 'Bearer ' + self._token
        }
    
    def create_payload(self, obj, attributes, id=None):
        payload = {"data": {
            "type": type(obj).__name__,
            "attributes": attributes
        }}
        if id is not None:
            payload["data"]["id"] = id
        return payload

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
            raise HashtopolisResponseError(
                "%s (status_code=%s): %s" % (error_msg, r.status_code, r.text),
                status_code=r.status_code,
                exception_details=r_json.get('exception', []),
                message=r_json.get('message', None))
    
    def validate_pagination_links(self, response, page):
        """Validate all the links that are used for paginated data"""
        data = response["data"]
        highest_id = max(data, key=lambda obj: obj['id'])['id']
        lowest_id = min(data, key=lambda obj: obj['id'])['id']

        links = response["links"]
        query_params = urllib.parse.parse_qs(urllib.parse.urlparse(links["next"]).query)
        assert (int(query_params["page[size]"][0]) == page["size"])
        assert (int(query_params["page[after]"][0]) == highest_id)
        query_params = urllib.parse.parse_qs(urllib.parse.urlparse(links["prev"]).query)
        assert (int(query_params["page[size]"][0]) == page["size"])
        assert (int(query_params["page[before]"][0]) == lowest_id)
        query_params = urllib.parse.parse_qs(urllib.parse.urlparse(links["first"]).query)
        assert (int(query_params["page[size]"][0]) == page["size"])
        assert (int(query_params["page[after]"][0]) == 0)
        # query_params = urllib.parse.parse_qs(urllib.parse.urlparse(links["last"]).query)
        # TODO not really a straightforward way to validate the last link
    
    def get_single_page(self, page, filter):
        """Gets a single page by using the page parameters"""
        self.authenticate()
        headers = self._headers
        request_uri = self._api_endpoint + self._model_uri
        payload = {}

        for k, v in page.items():
            payload[f"page[{k}]"] = v
        if filter:
            for k, v in filter.items():
                payload[f"filter[{k}]"] = v

        request_uri = self._api_endpoint + self._model_uri + '?' + urllib.parse.urlencode(payload)
        r = requests.get(request_uri, headers=headers)
        logger.debug("Request URI: %s", urllib.parse.unquote(r.url))
        self.validate_status_code(r, [200], "paging failed")
        response = self.resp_to_json(r)
        logger.debug("Response %s", json.dumps(response, indent=4))

        # validate page links
        self.validate_pagination_links(response, page)
        return response["data"]

    # todo refactor start_offset into page variable
    def filter(self, include, ordering, filter, start_offset):
        self.authenticate()
        headers = self._headers

        payload = {'page[after]': start_offset}
        if filter:
            for k, v in filter.items():
                payload[f"filter[{k}]"] = v

        if include:
            payload['include'] = ','.join(include) if type(include) in (list, tuple) else include
        if ordering:
            payload['sort'] = ','.join(ordering) if type(ordering) in (list, tuple) else ordering

        request_uri = self._api_endpoint + self._model_uri + '?' + urllib.parse.urlencode(payload)
        while True:
            r = requests.get(request_uri, headers=headers)
            logger.debug("Request URI: %s", urllib.parse.unquote(r.url))
            self.validate_status_code(r, [200], "Filtering failed")
            response = self.resp_to_json(r)
            logger.debug("Response %s", json.dumps(response, indent=4))

            # Buffer all included objects
            included_cache = IncludedCache(response.get('included', []))

            # Iterate over response objects
            for obj in response['data']:
                yield (obj, included_cache)

            if 'links' not in response or 'next' not in response['links'] or not response['links']['next']:
                break
            request_uri = self._hashtopolis_uri + response['links']['next']

    def get_one(self, pk, include):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri + f'/{pk}'
        headers = self._headers

        payload = {}
        if include is not None:
            payload['include'] = ','.join(include) if type(include) in (list, tuple) else include

        r = requests.get(uri, headers=headers, data=payload)
        self.validate_status_code(r, [200], "Get single object failed")
        return self.resp_to_json(r)

    def patch_one(self, obj):
        if not obj.has_changed():
            logger.debug("Object '%s' has not changed, no PATCH required", obj)
            return

        self.authenticate()
        uri = self._hashtopolis_uri + obj.uri
        headers = self._headers
        headers['Content-Type'] = 'application/json'
        attributes = {}

        for k, v in obj.diff().items():
            logger.debug("Going to patch object '%s' property '%s' from '%s' to '%s'", obj, k, v[0], v[1])
            attributes[k] = v[1]
        
        payload = self.create_payload(obj, attributes, id=obj.id)
        logger.debug("Sending PATCH payload: %s to %s", json.dumps(payload), uri)
        r = requests.patch(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [201], "Patching failed")

        # TODO: Validate if return objects matches digital twin
        obj.set_initial(self.resp_to_json(r)['data'].copy())

    def create(self, obj):
        # Check if object to be created is new
        assert obj._new_model is True

        self.authenticate()
        uri = self._api_endpoint + self._model_uri
        headers = self._headers
        headers['Content-Type'] = 'application/json'

        attributes = obj.get_fields()
        payload = self.create_payload(obj, attributes)

        logger.debug("Sending POST payload: %s to %s", json.dumps(payload), uri)
        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [201], "Creation of object failed")

        # TODO: Validate if return objects matches digital twin
        obj.set_initial(self.resp_to_json(r)['data'].copy())

    def delete(self, obj):
        """ Delete object from database """
        # TODO: Check if object to be deleted actually exists
        assert obj._new_model is False

        self.authenticate()
        uri = self._hashtopolis_uri + obj.uri
        headers = self._headers
        payload = {}

        r = requests.delete(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [204], "Deletion of object failed")

        # TODO: Cleanup object to allow re-creation


# Build Django ORM style django.query interface
class QuerySet():
    def __init__(self, cls, include=None, ordering=None, filters=None, pages=None):
        self.cls = cls
        self.include = include
        self.ordering = ordering
        self.filters = filters
        self.pages = pages

    def __iter__(self):
        yield from self.__getitem__(slice(None, None, 1))

    def __getitem__(self, k):
        if isinstance(k, int):
            return list(self.filter_(k, k + 1, 1))[0]

        if isinstance(k, slice):
            return self.filter_(k.start or 0, k.stop or sys.maxsize, k.step or 1)
    
    def get_pagination(self):
        objs = self.cls.get_conn().get_single_page(self.pages, self.filters)
        parsed_objs = []
        for obj in objs:
            parsed_objs.append(self.cls._model(**obj))
        return parsed_objs

    def filter_(self, start, stop, step):
        index = start or 0
        cursor = index

        # pk field is special and should be translated
        if self.filters is None:
            filters = None
        else:
            filters = self.filters.copy()
            if 'pk' in filters:
                filters['_id'] = filters['pk']
                del filters['pk']

        filter_generator = self.cls.get_conn().filter(self.include, self.ordering, filters, start_offset=cursor)

        while index < stop:
            # Fetch new entries in chunks default to server
            try:
                (obj, included_cache) = next(filter_generator)
            except StopIteration:
                return

            # Return value
            model_obj = self.cls._model(**obj)
            model_obj.set_prefetched_relationships(included_cache)
            yield model_obj

            index += 1

            # Remove items skipped by step
            for _ in range(step - 1):
                try:
                    _ = next(filter_generator)
                except StopIteration:
                    return

    def order_by(self, *ordering):
        self.ordering = ordering
        return self

    def filter(self, **filters):
        self.filters = filters
        return self
    
    def page(self, **pages):
        self.pages = pages
        return self

    def all(self):
        # yield from self
        return self

    def get(self, **filters):
        if filters:
            self.filters = filters

        # Generiek retrival, only need two entries to find out failures
        objs = list(self.__getitem__(slice(0, 2, 1)))
        if len(objs) == 0:
            raise self.cls._model.DoesNotExist
        elif len(objs) > 1:
            raise self.cls._model.MultipleObjectsReturned
        return objs[0]

    def __len__(self):
        return len(list(iter(self)))


class ManagerBase(type):
    conn = {}
    # Cache configuration values
    config = None

    @classmethod
    def prefetch_related(cls, *include):
        return QuerySet(cls, include=include)

    @classmethod
    def get_conn(cls):
        if cls.config is None:
            cls.config = HashtopolisConfig()

        if cls._model_uri not in cls.conn:
            cls.conn[cls._model_uri] = HashtopolisConnector(cls._model_uri, cls.config)
        return cls.conn[cls._model_uri]

    @classmethod
    def all(cls):
        """
        Retrieve all backend objects
        """
        return cls.filter()

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
        """
        return cls.all()[0]

    @classmethod
    def get(cls, **filters):
        return QuerySet(cls, filters=filters).get()

    @classmethod
    def paginate(cls, **pages):
        return QuerySet(cls, pages=pages)

    @classmethod
    def filter(cls, **filters):
        return QuerySet(cls, filters=filters)


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
                        '__module__': "%s" % (__name__)
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
        if 'links' in kwargs:
            # Loading of existing model
            self.set_initial(kwargs)
        else:
            self.set_initial({'attributes': kwargs})
        super().__init__()

    def __repr__(self):
        return self.__uri

    def __eq__(self, other):
        return (self.get_fields() == other.get_fields())

    def _dict2obj(self, dict):
        """
        Convert resource object dictionary to an model Object
        """
        uri = dict['links']['self']
        uri_without_id = '/'.join(uri.split('/')[:-1])
        # Loop through all the registers classes
        for _, model in cls_registry.items():
            model_uri = model.objects._model_uri
            # Check if part of the uri is in the model uri
            if uri_without_id.endswith(model_uri):
                return model(**dict)
        # If we are here, it means that no uri matched, thus we don't know the object.
        raise TypeError(f"Object identifier '{uri}' not valid/defined model")

    def set_initial(self, kv):
        self.__fields = []
        self.__included = []
        self._new_model = True
        # Store fields allowing us to detect changed values
        if 'links' in kv:
            self.__initial = copy.deepcopy(kv)
            self.__uri = kv['links']['self']
            self.__id = kv['id']
            self._new_model = False
        else:
            # New model
            self.__initial = {}

        self.__relationships = kv.get('relationships', {})

        # Create attribute values
        for k, v in kv['attributes'].items():
            setattr(self, k, v)
            self.__fields.append(k)

    def set_prefetched_relationships(self, included_cache):
        """
        Populate prefetched relationships
        """
        for relationship_name, resource_identifier_object in self.__relationships.items():
            if 'data' not in resource_identifier_object:
                # TODO Deal with 'link' type related relationships
                continue

            resource_identifier_object_data_type = type(resource_identifier_object['data'])
            if resource_identifier_object_data_type is type(None):
                # Empty to-one relationship
                setattr(self, relationship_name, None)
                self.__included.append(relationship_name)
            elif resource_identifier_object_data_type is dict:
                # Non-empty to-one relationship
                to_one_relation_obj = self._dict2obj(included_cache.get(resource_identifier_object['data']))
                setattr(self, relationship_name, to_one_relation_obj)
                self.__included.append(relationship_name)
            elif resource_identifier_object_data_type is list:
                to_many_relation_objs = []
                # to-many relationship
                for obj in resource_identifier_object['data']:
                    to_many_relation_objs.append(self._dict2obj(included_cache.get(obj)))
                setattr(self, relationship_name + '_set', to_many_relation_objs)
                self.__included.append(relationship_name + "_set")
            else:
                raise AssertionError("Invalid resource indentifier object class type=%s" %
                                     resource_identifier_object_data_type)

    def get_fields(self):
        return dict([(k, getattr(self, k)) for k in sorted(self.__fields)])

    def diff(self):
        # Stored database values
        d_initial = self.__initial['attributes']
        # Possible changes values
        d_current = self.get_fields()
        diffs = []
        for key, v_current in d_current.items():
            v_innitial = d_initial[key]
            if v_current != v_innitial:
                diffs.append((key, (v_innitial, v_current)))

        # Find includeables sets which have changed
        for include in self.__included:
            if include.endswith('_set'):
                innitial_name = include[:-4]
                # Retrieve innitial keys
                v_innitial = self.__initial['relationships'][innitial_name]['data']
                v_innitial_ids = [x['id'] for x in v_innitial]
                # Retrieve new/current keys
                v_current = getattr(self, include)
                v_current_ids = [x.id for x in v_current]
                # Use ID of ojbects as new current/update identifiers
                if sorted(v_innitial_ids) != sorted(v_current_ids):
                    diffs.append((innitial_name, (v_innitial_ids, v_current_ids)))

        return dict(diffs)

    def has_changed(self):
        return bool(self.diff())

    def save(self):
        if self._new_model:
            self.objects.create(self)
        else:
            self.objects.patch(self)
        return self

    def delete(self):
        if not self._new_model:
            self.objects.delete(self)

    def serialize(self):
        retval = dict([(x, getattr(self, x)) for x in self.__fields] + [('_self', self.__uri), ('_id', self.__id)])
        for includeable in self.__included:
            if includeable.endswith('_set'):
                retval[includeable] = [x.serialize() for x in getattr(self, includeable)]
            else:
                retval[includeable] = getattr(self, includeable).serialize()
        return retval

    @property
    def id(self):
        return self.__id

    @property
    def pk(self):
        return self.__id

    @property
    def uri(self):
        return self.__uri


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
        headers['Content-Type'] = 'application/json'

        logging.debug(f"Makeing POST request to {uri}, headers={headers} payload={payload}")
        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.validate_status_code(r, [200], f"Helper request at {uri} failed")
        if r.status_code == 204:
            return None
        else:
            return self.resp_to_json(r)

    def _helper_get_request_file(self, helper_uri, payload, range=None):
        self.authenticate()
        uri = self._api_endpoint + self._model_uri + helper_uri
        headers = self._headers
        if range:
            headers["Range"] = range

        logging.debug(f"Sending GET request to {uri}, with params:{payload}")
        r = requests.get(uri, headers=headers, params=payload)
        if range is None:
            assert r.status_code == 200
        else:
            assert r.status_code == 206
        logging.debug(f"received file contents: \n {r.text}")
        return r.text

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

    def export_left_hashes(self, hashlist):
        payload = {
            'hashlistId': hashlist.id,
        }
        response = self._helper_request("exportLeftHashes", payload)
        return File(**response['data'])

    def export_wordlist(self, hashlist):
        payload = {
            'hashlistId': hashlist.id,
        }
        response = self._helper_request("exportWordlist", payload)
        return File(**response['data'])

    def import_cracked_hashes(self, hashlist, source_data, separator):
        payload = {
            'hashlistId': hashlist.id,
            'sourceData': source_data,
            'separator': separator,
        }
        response = self._helper_request("importCrackedHashes", payload)
        return response['data']

    def get_file(self, file, range=None):
        payload = {
            'file': file.id
        }
        return self._helper_get_request_file("getFile", payload, range)

    def recount_file_lines(self, file):
        payload = {
            'fileId': file.id,
        }
        response = self._helper_request("recountFileLines", payload)
        return File(**response['data'])

    def unassign_agent(self, agent):
        payload = {
            'agentId': agent.id,
        }
        response = self._helper_request("unassignAgent", payload)
        return response['data']

    def assign_agent(self, agent, task):
        payload = {
            'agentId': agent.id,
            'taskId': task.id,
        }
        response = self._helper_request("assignAgent", payload)
        return response['data']
