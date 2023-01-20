#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#

import json
import requests
import unittest
import datetime
from pathlib import Path

import confidence


class Crackers(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        # Request access TOKEN, used throughout the test
        cls._cfg = confidence.load_name('hashtopolis-test')

        uri = cls._cfg['api_endpoint'] + '/auth/token'
        auth = (cls._cfg['username'], cls._cfg['password'])
        r = requests.post(uri, auth=auth)

        cls._token = r.json()['token']
        cls._token_expires = r.json()['expires']

        cls._headers = {
            'Authorization': 'Bearer ' + cls._token,
            'Content-Type': 'application/json'
        }

    def test_get(self):
        uri = self._cfg['api_endpoint'] + '/ui/crackers'
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201)


    def test_get_one(self):
        # TODO: Boring to only request the first one
        uri = self._cfg['api_endpoint'] + '/ui/crackers/1'
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201)


    def test_patch(self):
        # TODO: Boring to only request the first one
        stamp = datetime.datetime.now().isoformat()

        uri = self._cfg['api_endpoint'] + '/ui/crackers/1'
        headers = self._headers
        
        payload = {
            'binaryName': f'Hashcat - {stamp}'
        }
        r = requests.patch(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201, msg=r.text)
        for key in payload.keys():
            self.assertEqual(r.json()[key], payload[key], msg=r.text)
        

    def do_create(self, payload, retval):
        uri = self._cfg['api_endpoint'] + '/ui/crackers'
        headers = self._headers

        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, retval, msg=r.text)


    def test_create_wildcard(self):
        for p in sorted(Path(__file__).parent.glob('create_crackers_*.json')):
            payload = json.loads(p.read_text('UTF-8'))
            self.do_create(payload, 201)


if __name__ == '__main__':
    unittest.main()
