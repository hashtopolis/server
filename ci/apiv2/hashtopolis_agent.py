#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import json
import requests
import time
from pathlib import Path

import requests
import logging
from pathlib import Path

import http
import confidence

logging.basicConfig(level=logging.DEBUG)

logger = logging.getLogger(__name__)

HTTP_DEBUG = True

# Monkey patching to allow http debugging
if HTTP_DEBUG:
    http_logger = logging.getLogger('http.client')
    http.client.HTTPConnection.debuglevel = 0
    def print_to_log(*args):
        http_logger.debug(" ".join(args))
    http.client.print = print_to_log


class HashtopolisConfig(object):
    def __init__(self):
        # Request access TOKEN, used throughout the test
        load_order = (str(Path(__file__).parent.joinpath('{name}-default.{extension}')),) + confidence.DEFAULT_LOAD_ORDER
        self._cfg = confidence.load_name('hashtopolis-test', load_order=load_order)
        self._hashtopolis_uri = self._cfg['hashtopolis_uri']
        self._api_endpoint = self._hashtopolis_uri + '/api/v2'
        self.username = self._cfg['username']
        self.password = self._cfg['password']


class TestAgent(object):
    # Mock Agent behaviour for usage in unit testing
    # State: Early Alpha
    def __init__(self, token=None, voucher=None):
        # Request access TOKEN, used throughout the test
        load_order = (str(Path(__file__).parent.joinpath('{name}-default.{extension}')),) + confidence.DEFAULT_LOAD_ORDER
        self._cfg = confidence.load_name('hashtopolis-test', load_order=load_order)
        self._hashtopolis_uri = self._cfg['hashtopolis_uri']
        self._api_endpoint = self._hashtopolis_uri + '/api/server.php'

        self.token = token
        self.voucher = voucher
        self.timeout = None
        self.task = None
        self.chunk = None
        

    def _do_request(self, payload):
        r = requests.post(self._api_endpoint, data=json.dumps(payload))
        if r.status_code != 200:
            logger.exception("Request failed (status_code != 200 ): %s", r.text)

        retval = r.json()
        if retval['response'] != 'SUCCESS':
            logger.exception("Request failed: %s", r.text)

        return retval

    def test_connection(self):
        # Test connection
        payload = {
            "action": "testConnection"
        }
        retval = self._do_request(payload)
        return retval['response'] == 'SUCCESS'

    def register(self, voucher, name, cpu_only=False):
        # Register agent 
        payload = {
            "action": "register",
            "voucher": voucher,
            "name": name,
            "cpu-only": cpu_only,
        }
        retval = self._do_request(payload)
        self.token = retval['token']

    def update_information(self):
        token = self.authenticate()
        payload = {
            "action": "updateInformation",
            "token": token,
            "uid": "230-34-345-345",
            "os": 0,
            "devices":[
                "ATI HD7970",
                "ATI HD7970"
            ]
        }
        self._do_request(payload)

    def login(self):
        token = self.authenticate()
        payload = {
            "action": "login",
            "clientSignature":"generic-python",
            "token": token,
            }
        retval = self._do_request(payload)
        self.timeout = retval['timeout']


    def authenticate(self):
        if self.token == None:
            stamp = int(time.time() * 1000)
            self.register(self.voucher, f'test-agent-{stamp}')
        return self.token


    def get_task(self):
        token = self.authenticate()
        payload = {
            "action": "getTask",
            "token": token,
        }
        retval = self._do_request(payload)
        self.task = retval
        print(self.task)
        return self.task['taskId']

    def get_hashlist(self):
        assert(self.task and self.task['taskId'])
        token = self.authenticate()

        payload = {
            "action":"getHashlist",
            "token": token,
            "hashlistId": self.task['hashlistId']
            }
        retval = self._do_request(payload)
        print(retval)
        r = requests.get(self._hashtopolis_uri + '/' + retval['url'])
        print(r.status_code, r.text)
        self.hashes = r.text.split()


    def get_chunk(self, taskId=None):
        token = self.authenticate()
        if taskId == None:
            self.taskId = self.get_task()
        payload = {
            "action": "getChunk",
            "token": token,
            "taskId": self.taskId,
        }   
        retval = self._do_request(payload)
        self.chunk = retval
        print(self.chunk)

        # The server will send then a chunk to work on.
        # {
        # "action":"getChunk",
        # "response":"SUCCESS",
        # "status":"OK",
        # 8
        # "chunkId": 13,
        # "skip": 45000,
        # "length": 10000
        # }
        # In case it is required to measure the keyspace of the corresponding task.
        # {
        # "action":"getChunk",
        # "response":"SUCCESS",
        # "status":"keyspace_required"
        # }
        # In case the current task is already fully dispatched and the agent should request for a new task.
        # {
        # "action":"getChunk",
        # "response":"SUCCESS",
        # "status":"fully_dispatched"
        # }
        # In case there is an update of the cracker binary available, the server sends this:
        # {
        # "action":"getChunk",
        # "response":"SUCCESS",
        # "status":"cracker_update"
        # }
        # In case there is no benchmark for this task on the current agent. So it should start benchmarking then.
        # {
        # "action":"getChunk",
        # "response":"SUCCESS",
        # "status":"benchmark"
        # }
        # In case there was a health check requested on the server, it will notify the client.
        # {
        # "action":"getChunk",
        # "response":"SUCCESS",
        # "status":"health_check"
        # }
    def send_keyspace(self, keyspace=56800):
        assert(self.task and self.task['taskId'])
        token = self.authenticate()
        payload = {
            "action": "sendKeyspace",
            "token": token,
            "taskId": self.task['taskId'],
            "keyspace":keyspace
        }
        retval = self._do_request(payload)
        self.chunk = retval


    def send_benchmark(self, type="run", result=674):
        # type=speed || result = 674:674.74
        assert(self.task and self.task['taskId'])
        token = self.authenticate()
        payload = {
            "action": "sendBenchmark",
            "token": token,
            "taskId": self.task['taskId'],
            "type": type,
            "result": result,
        }
        retval = self._do_request(payload)
        self.chunk = retval        


    def send_process(self):
        assert(self.task and self.task['taskId'])
        assert(self.chunk and self.chunk['chunkId'])

        token = self.authenticate()

        payload = {
            "action": "sendProgress",
            "token": token,
            "chunkId": self.chunk['chunkId'],
            "keyspaceProgress": 568000,
            "relativeProgress": "4545",
            "speed": 570000,
            "state": 3,
            "cracks": [
                [
                    "7fde65673fd28736423f23423786f",
                    "thisisplain",
                    "746869736973706c61696e",
                    "787523889"
                ],
                [
                    "7fde65673f28987f7423f2342378f",
                    "otherplain",
                    "6f74686572706c61696e",
                    "78652"
                ]
            ],
            "gpuTemp": [
                67
            ],
            "gpuUtil": [
                99
            ]
        }