#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import enum
import json
import logging
import requests
from pathlib import Path
import time

import http
import confidence

logger = logging.getLogger(__name__)

HTTP_DEBUG = False 

# Monkey patching to allow http debugging
if HTTP_DEBUG:
    http_logger = logging.getLogger('http.client')
    http.client.HTTPConnection.debuglevel = 0
    def print_to_log(*args):
        http_logger.debug(" ".join(args))
    http.client.print = print_to_log

class ProcessState(enum.IntEnum):
    """ See src/inc/defines/hashcat.php for mapping"""
    INIT = 0
    AUTOTUNE = 1
    RUNNING = 2
    PAUSED = 3
    EXHAUSTED = 4
    CRACKED = 5
    ABORTED = 6
    QUIT = 7
    BYPASS = 8
    ABORTED_CHECKPOINT = 9
    STATUS_ABORTED_RUNTIME = 10

class HashtopolisConfig(object):
    def __init__(self):
        # Request access TOKEN, used throughout the test
        load_order = (str(Path(__file__).parent.joinpath('{name}-default.{extension}')),) + confidence.DEFAULT_LOAD_ORDER
        self._cfg = confidence.load_name('hashtopolis-test', load_order=load_order)
        self._hashtopolis_uri = self._cfg['hashtopolis_uri']
        self._api_endpoint = self._hashtopolis_uri + '/api/v2'
        self.username = self._cfg['username']
        self.password = self._cfg['password']


class DummyAgent(object):
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
        self.name = name
        # Register agent 
        payload = {
            "action": "register",
            "voucher": voucher,
            "name": name,
            "cpu-only": cpu_only,
        }
        retval = self._do_request(payload)
        self.token = retval['token']
        logger.debug("Token: %s", self.token)

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
        r = requests.get(self._hashtopolis_uri + '/' + retval['url'])
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
        logger.debug(self.chunk)

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


    def send_benchmark(self, benchmark_type="run", result=674):
        # type=speed || result = 674:674.74
        assert(self.task and self.task['taskId'])
        token = self.authenticate()
        payload = {
            "action": "sendBenchmark",
            "token": token,
            "taskId": self.task['taskId'],
            "type": benchmark_type,
            "result": result,
        }
        retval = self._do_request(payload)
        self.benchmark = retval


    def send_process(self, progress=50, state=ProcessState.RUNNING, speed=5700):
        assert(self.task and self.task['taskId'])
        assert(self.chunk and self.chunk['chunkId'])

        token = self.authenticate()

        payload = {
            "action": "sendProgress",
            "token": token,
            "chunkId": self.chunk['chunkId'],
            "keyspaceProgress": self.chunk['skip'] + int(self.chunk['length'] / 100 * progress),
            "relativeProgress": int(progress * 100),
            "speed": speed,
            "state": state,
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
        retval = self._do_request(payload)
        self.process = retval