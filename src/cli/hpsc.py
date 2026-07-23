#!/usr/bin/env python3
"""Hashtopolis CLI

Ensure hpsc.yaml is created at confidence location.
"""
from argparse import ArgumentParser, FileType, RawDescriptionHelpFormatter
from base64 import b64encode
from collections import defaultdict
from distutils.util import strtobool
import json
import logging
import os
import pprint
import re
from sys import argv, stderr

import confidence
import requests

logger = logging.getLogger(__name__)

__version__ = '2.2.3'

DEFAULT_ACCESGROUP_ID = 1

API = {
    'test': {
        'access': {},
        'connection': {},
        },
    'agent': {
        'listAgents': {},
        'get': {
            'agentId': {'type': int},
               },
        },
    'cracker': {
        'listCrackers': {},
        'getCracker': {
            'crackerTypeId': {'type': int},
                      },
        'createCracker': {
            'crackerName': {'type': str},
                         },
        'addVersion': {
            'crackerTypeId':  {'type': int},
            'crackerBinaryVersion': {'type': str},
            'crackerBinaryBasename': {'type': str},
            'crackerBinaryUrl': {'type': str},
                      },
        'updateVersion': {
            'crackerTypeId': {'type': int},
            'crackerBinaryVersion': {'type': str},
            'crackerBinaryBasename':  {'type':  str},
            'crackerBinaryUrl': {'type': str},
                         },
        },
    'hashlist': {
        'listHashlists': {},
        'getHashlist': {
            'hashlistId': {'type': int},
                       },
        'createHashlist': {
                    'name': {'type': str},
                    'isSalted': {'type': bool},
                    'isSecret': {'type': bool},
                    'isHexSalt': {'type': bool},
                    'separator': {'type': str},
                    'format': {'type': int},
                    'hashtypeId': {'type': int},
                    'accessGroupId': {'type': int},
                    'data': {'type': str},
                    'useBrain': {'type': bool},
                    'brainFeatures': {'type': int},
                          },
        'getCracked': {
            'hashlistId': {'type': int},
            },
        },
    'supertask': {
        'listSupertasks': {},
        'getSupertask': {
            'supertaskId': {'type': int},
        },
        'createSupertask': {
            'name': {'type': str},
            'pretasks': {'type': list, 'subtype': int},
        },
    },
    'pretask': {
        'listPretasks': {},
        'getPretask': {
            'pretaskId': {'type': int},
        },
        'createPretask': {
            'name': {'type': str},
            'attackCmd': {'type': str},
            'chunksize': {'type': int},
            'statusTimer': {'type': int},
            'benchmarkType': {'type': str},
            'color': {'type': str},
            'isCpuOnly': {'type': bool},
            'isSmall': {'type': bool},
            'priority': {'type': int},
            'crackerTypeId': {'type': int},
            'files': {'type': list, 'subtype': int},
        },
    },
    'file': {
        'listFiles': {},
        'addFile': {
            'source': {'type': str},
            'fileType': {'type': int},
            'filename': {'type': str},
            'source': {'type': str},
            'accessGroupId': {'type': str},
            'data': {'type': str},
        },
        'getFile': {
            'fileId': {'type': int},
        },
        'setSecret': {
            'fileId': {'type': int},
            'isSecret': {'type': bool},
        },
    },
}


def setup():
    """ Setup environment based on CLI input.

    Returns:
        argparse.NameSpace: CLI parsed arguments
        dict: API Input parameters
    """

    section_choices = API.keys()
    request_choices = []
    for y in [x.keys() for x in API.values()]:
        request_choices.extend(y)

    parser = {}
    subparser = {}
    parser['root'] = ArgumentParser(formatter_class=RawDescriptionHelpFormatter)

    subparser['root'] = parser['root'].add_subparsers(help='sub-command help', dest='action', required=True)
    epilog = 'combinations:\n' + pprint.pformat(API)

    # run sub-commands
    action = 'run'
    parser[action] = subparser['root'].add_parser(action,
            help='Perform common hashtopolis tasks')
    subparser[action] = parser[action].add_subparsers(dest='run_action', required=True)

    action = 'cloneSupertask'
    parser[action] = subparser['run'].add_parser(action, help="Clone supertask to new version with same pretasks")
    parser[action].add_argument('supertaskId', type=int)
    parser[action].set_defaults(func=do_clonesupertask)

    action = 'dictRelease'
    parser[action] = subparser['run'].add_parser(action, help='Release new version of dictonary updating all supertasks')
    parser[action].add_argument('target', type=str)
    parser[action].set_defaults(func=do_dict_release)

    action = 'addDictFile'
    parser[action] = subparser['run'].add_parser(action, help='Add dictonary file')
    parser[action].add_argument('source', type=FileType('rb'))
    parser[action].add_argument('--access-group-id', default=DEFAULT_ACCESGROUP_ID, type=int)
    parser[action].set_defaults(func=do_add_dictfile)

    action = 'appendPretask'
    parser[action] = subparser['run'].add_parser(action, help='Add pretask to all supertasks')
    parser[action].add_argument('pretaskId', type=int)
    parser[action].set_defaults(func=do_appendtask)

    # API sub-commands
    action = 'api'
    parser[action] = subparser['root'].add_parser(action,
            help='Direct API access calls',
            formatter_class=RawDescriptionHelpFormatter,
            epilog=epilog)

    parser[action].add_argument('section', choices=section_choices)
    parser[action].add_argument('request', choices=request_choices)
    parser[action].add_argument('params', nargs='*', metavar='k=v',
                       help="Required parameters <key>=<value>",
                        )

    for k in parser.keys():
        parser[k].add_argument('-g', '--debug', action='store_true',
                            help="enable debug logging")
        parser[k].add_argument('--log', default=stderr, type=FileType('a'),
                            help="the file where the log should be written")
        parser[k].add_argument('--commit', default=False, action='store_true',
                               help="Changes are comitted/sent to hashtopolis. Default: read-only")

    args = parser['root'].parse_args()

    if args.action == 'api':
        if args.request not in API[args.section]:
            selected_choices = API[args.section].keys()
            parser.error(f"request '{args.request}' invalid, valid choices for section "
                         f"'{args.section}': {{{','.join(selected_choices)}}}")

        params = {}
        for item in args.params:
            k, v = item.split('=', 1)
            if k in API[args.section][args.request]:
                t = API[args.section][args.request][k]['type']
                if t == bool:
                    params[k] = strtobool(v)
                elif t == list:
                    subtype = API[args.section][args.request][k]['subtype']
                    params[k] = [subtype(x) for x in v.split(',')]
                else:
                    params[k] = t(v)

        valid_params = API[args.section][args.request].keys()
        invalid_params = list(set(params.keys()) - set(valid_params))
        if invalid_params:
            parser['api'].error(f"param(s) '{','.join(invalid_params)}' invalid, required "
                         f"for section '{args.section}' and request '{args.request}' "
                         f"are '{{{','.join(valid_params)}}}'")

        missing_params = list(set(valid_params) - set(params.keys()))
        if missing_params:
            parser['api'].error(f"param(s) '{','.join(missing_params)}' missing, required "
                         f"for section '{args.section}' and request '{args.request}' "
                         f"are '{{{','.join(valid_params)}}}'")

    else:
        params = {}

    level = logging.DEBUG if args.debug else logging.INFO
    logging.basicConfig(level=level,
                        stream=args.log,
                        format='[%(levelname)-5s] %(message)s')

    logger.info(f"Starting {argv[0]} - {__version__}")
    logger.info(f"Commit to database: {args.commit}")
    return (args, params)


class HashtopolisApiException(Exception):
    pass


class HashtopolisUserAPI(object):
    def __init__(self, endpoint, accesskey, commit):
        logger.info("Connecting to %s", endpoint)
        self._endpoint = endpoint
        self._accesskey = accesskey
        self._commit = commit

    def _do_request(self, section, request, payload_extra={}, soft_fail=False):
        payload = dict()
        payload['section'] = section
        payload['request'] = request
        payload['accessKey'] = self._accesskey
        payload.update(payload_extra)

        if args.commit == False and any([payload['request'].startswith(x) for x in ['create', 'set', 'add']]):
            # Quirck to avoid printing very large entries
            disp_payload = payload.copy()
            if 'data' in disp_payload:
                disp_payload['data'] = disp_payload['data'][:10] + '...' + disp_payload['data'][-10:]
            logger.warning("Payload scheduled to be sent %s", json.dumps(disp_payload))
            if soft_fail == True:
                logger.warning("Writing disabled use '--commit' to apply changes")
                return {}
            else:
                raise HashtopolisApiException("Writing disabled use '--commit' to apply changes")

        logger.debug("Payload %s", json.dumps(payload))
        r = requests.post(self._endpoint, data=json.dumps(payload))
        r.raise_for_status()
        return r.json()

    def request(self, section, request, payload={}, soft_fail=False):
        rv = self._do_request(section, request, payload, soft_fail)
        if not 'response' in rv or rv['response'] not in ('OK', 'SUCCESS'):
            logger.error("Response %s", rv)
            if soft_fail == False:
                raise HashtopolisApiException(rv)
        return rv


    def test_connection(self):
        r = self._do_request('test', 'connection')
        return r['response'] == 'SUCCESS'

    def test_access(self):
        r = self._do_request('test', 'access')
        return r['response'] == 'OK'

    def get_latest_supertasks(self):
        sl = defaultdict(list)
        name2id  = {}
        rv = self.request('supertask', 'listSupertasks')

        for obj in rv['supertasks']:
            m = re.search(r'-v([0-9]+)$', obj['name'])
            if m:
                suffix = m.group(1)
                prefix = obj['name'][:-(len(suffix))]
                sl[prefix].append(int(suffix))
                name2id[obj['name']] = obj['supertaskId']
            else:
                logger.warning("Ignoring supertask '%s', no version number in suffix", obj['name'])
        supertask_ids_latest = []
        for k,v in sl.items():
            supertask_ids_latest.append(name2id['%s%s' % (k, max(v))])

        return supertask_ids_latest


def incr_supertask_name(name):
    m = re.search(r'-v([0-9]+)$', name)
    assert m != None

    suffix = m.group(1)
    prefix = name[:-(len(suffix))]
    new_name = prefix + str(int(suffix) + 1)

    return new_name


def do_dict_release(api, args):
    rv = api.request('file', 'listFiles')

    base, version = args.target.split('.')
    source = "%s.%i" % (base, int(version) - 1)
    target = "%s.%i" % (base, int(version))
    logger.info("Upgrading from '%s' to '%s'", source, target)


    # Retrieve fileId
    sourceId = None
    targetId = None

    suffix = ".dict"
    source_file = source + suffix
    target_file = target + suffix
    for obj in rv['files']:
        if obj['filename'] == source_file:
            sourceId = obj['fileId']
        elif obj['filename'] == target_file:
            targetId = obj['fileId']

    if sourceId is None:
        logger.error("File %s not found", source_file)
        return 1
    if targetId is None:
        logger.error("File %s not found", target_file)
        return 1

    # Hack to check for duplicate names at insert at later stage
    pretask_names = []
    # Prepare mapping for later use updating supertasks (pretaskId, newName, newCmd, newPretaskId)
    s2t = {}

    # Find pretasks which are using source file
    pretask_todo = []
    rv = api.request('pretask', 'listPretasks')
    for obj in rv['pretasks']:
        rv2 = api.request('pretask', 'getPretask', {'pretaskId': obj['pretaskId']})
        pretask_names.append(rv2['name'])
        if any([x['fileId'] == sourceId for x in rv2['files']]):
            # Quirk missing field from getPretask
            if not 'crackerTypeId' in rv2:
                rv2['crackerTypeId'] = 1
            pretask_todo.append(rv2)

    # Copy pretasks with new file and name
    for obj in pretask_todo:
            logger.info("Start upgrading pretask '%s' (id=%s)", obj['name'], obj['pretaskId'])
            payload = {}
            # Rename description and attack command
            for k in ['name', 'attackCmd']:
                payload[k] = re.sub(r'\b' + source + r'\b', target, obj[k])
            # Copy-Paste values
            for k in ['chunksize', 'color', 'benchmarkType', 'statusTimer', 'priority', 'isCpuOnly', 'isSmall', 'crackerTypeId']:
                # Quirk for empty str values represented as None
                if API['pretask']['createPretask'][k]['type'] == str:
                    payload[k] = '' if obj[k] == None else obj[k]
                else:
                    payload[k] = obj[k]
            # Replace sourceId with targetId
            payload['files'] = [x['fileId'] for x in obj['files'] if x['fileId'] != sourceId] + [targetId]

            for k in payload.keys():
                logger.info("[%-15s] Source '%s' -> Target: '%s'", k, obj[k], payload[k])

            if not payload['name'] in pretask_names:
                rv = api.request('pretask', 'createPretask', payload, soft_fail=True)
            logger.warning("Not creating pretask '%s', already exists!", payload['name'])

            # Quirk to retreive mapping at later stage, since new pretaskId is not returned
            s2t[obj['pretaskId']] = {
                'source': {
                    'pretaskId': obj['pretaskId'],
                },
                'target': {
                    'name': payload['name'],
                    'attackCmd': payload['attackCmd'],
                    'pretaskId': None,
                },
            }

    # Retrieve all pretasks and identify mapping
    rv = api.request('pretask', 'listPretasks')
    for obj in rv['pretasks']:
        rv2 = api.request('pretask', 'getPretask', {'pretaskId': obj['pretaskId']})

        for k in s2t.keys():
            if rv2['attackCmd'] == s2t[k]['target']['attackCmd'] and \
                    rv2['name'] == s2t[k]['target']['name']:
                # Safe-guard against existing duplicated mappings, since neither name nor attackCmd is unique in DB
                assert s2t[k]['target']['pretaskId'] == None
                s2t[k]['target']['pretaskId'] = rv2['pretaskId']

    # Identify those which are using the old pretasks
    for supertask_id in api.get_latest_supertasks():
        rv = api.request('supertask', 'getSupertask', {'supertaskId': supertask_id})

        pretask_ids = [x['pretaskId'] for x in rv['pretasks']]
        if not any([x in s2t.keys() for x in pretask_ids]):
            logger.info("Supertask '%s' does not use pretask(s) '%s'", obj['name'], list(s2t.keys()))
            continue

        # Create new supertask with updated pretask listing
        new_pretask_ids = []
        for k in pretask_ids:
            if k in s2t:
                new_id = s2t[k]['target']['pretaskId']
                if new_id in pretask_ids:
                    logger.warning("Supertask '%s' already contains pretaskId %i, ignoring", obj['name'], new_id)
                else:
                    new_pretask_ids.append(s2t[k]['target']['pretaskId'])
            else:
                new_pretask_ids.append(k)

        if len(set(new_pretask_ids) - set(pretask_ids)) == 0:
            logger.warning("Supertask '%s' has no new pretasks (currently: %s), not creating new version", obj['name'], pretask_ids)
            continue
        upgrade_supertask(api, rv, new_pretask_ids)


def upgrade_supertask(api, rv, new_pretask_ids):
    new_name = incr_supertask_name(rv['name'])
    payload = { 'name': new_name, 'pretasks': new_pretask_ids }
    logging.info('Upgrade %s into %s (%s)', rv['name'], new_name, payload)
    for k in payload.keys():
        if k == 'pretasks':
            sv = sorted([x['pretaskId'] for x in rv[k]])
            logger.info("[%-15s] Source '%s' -> Target: '%s'", k + '_human', sv, payload[k])
        else:
            logger.info("[%-15s] Source '%s' -> Target: '%s'", k, rv[k], payload[k])

    rv2 = api.request('supertask', 'createSupertask', payload, soft_fail=True)


def do_appendtask(api, args):
    for supertask_id in api.get_latest_supertasks():
        rv = api.request('supertask', 'getSupertask', {'supertaskId': supertask_id})

        pretask_ids = [x['pretaskId'] for x in rv['pretasks']]
        if args.pretaskId in pretask_ids:
            logger.info("Supertask '%s' already has pretask '%s'", rv['name'], args.pretaskId)
            continue

        # Create new supertask with updated pretask listing
        new_pretask_ids = sorted(pretask_ids) + [args.pretaskId]
        upgrade_supertask(api, rv, new_pretask_ids)


def do_clonesupertask(api, args):
    rv = api.request('supertask', 'getSupertask', {'supertaskId': args.supertaskId})

    new_name = incr_supertask_name(rv['name'])
    payload = { 'name': new_name, 'pretasks': [x['pretaskId'] for x in rv['pretasks']] }

    logging.info('Cloning %s into %s (%s)', rv['name'], new_name, payload)
    rv = api.request('supertask', 'createSupertask', payload, soft_fail=True)


def do_add_dictfile(api, args):
    logger.info("Publish %s to hashtopolis", args.source.name)
    filename = os.path.basename(args.source.name)
    data = {
        'source': 'inline',
        'fileType': "0",
        'filename': filename,
        'accessGroupId': args.access_group_id,
        'data': b64encode(args.source.read()).decode('ascii'),
        }
    rv = api.request('file', 'addFile', data)

    logger.info("Set file visibility to not secret")
    rv = api.request('file', 'listFiles')
    file_id = [obj['fileId'] for obj in rv['files'] if obj['filename'] == filename][0]
    rv = api.request('file', 'setSecret', {'fileId': file_id, 'isSecret': False})


def main(args, params):
    config = confidence.load_name('hpsc')
    endpoint = config.get('api_endpoint')
    accesskey = config.get('api_key')
    api = HashtopolisUserAPI(endpoint, accesskey, args.commit)

    if args.action == 'run':
        args.func(api, args)
    elif args.action == 'api':
        rv = api.request(args.section, args.request, params)
        print(json.dumps(rv, indent=4))


if __name__ == '__main__':
    (args, params) = setup()
    main(args, params)
