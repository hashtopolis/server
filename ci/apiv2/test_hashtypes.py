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

import utils


class Hashtypes(utils.TestBase):
    def getBaseURI(self):
        return '/ui/hashtypes'

    def do_get(self):
        uri = self.getURI()
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 200, msg=uri)

        return r.json()

    def test_get_one(self):
        # TODO: Boring to only request the first one
        obj = self.do_get()['values'][0]

        uri = self.getURI(obj)
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 200, msg=uri)

    def test_patch(self):
        # TODO: Boring to only request the first one
        stamp = datetime.datetime.now().isoformat()

        obj = self.do_get()['values'][0]
        uri = uri = self.getURI(obj)
        headers = self._headers

        payload = {
            'description': f'MD5 - {stamp}',
            'isSalted': False,
            'isSlowHash': False
        }

        r = requests.patch(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201, msg=r.text)
        for key in payload.keys():
            self.assertEqual(r.json()[key], payload[key], msg=r.text)

    def do_create(self, payload, retval):
        uri = uri = self.getURI()
        headers = self._headers

        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, retval, msg=r.text)

    def test_create_wildcard(self):
        for p in sorted(Path(__file__).parent.glob('create_hashtype_*.json')):
            payload = json.loads(p.read_text('UTF-8'))
            self.do_create(payload, 201)

    def test_create_binary_without_body(self):
        """ Should fail, since invalid parameters are invalid """
        payload = json.loads('''
        {
          "name": "Hashlist-with-binary-format",
          "hashTypeId": 10,
          "format": 2,
          "separator": ";",
          "isSalted": false,
          "isHexSalt": false,
          "accessGroupId": 1,
          "useBrain": false,
          "brainFeatures": 3,
          "notes": "gj",
          "sourceType": "paste",
          "sourceData": "",
          "hashCount": 0,
          "cracked": 0,
          "isArchived": false,
          "isSecret": true
        }
        ''')
        self.do_create(payload, 500)


if __name__ == '__main__':
    unittest.main()
