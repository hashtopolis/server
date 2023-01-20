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


class Hashlists(utils.TestBase):
    def getBaseURI(self):
        return '/ui/hashlists'

    def do_create(self, payload, required_status_code):
        uri = self.getURI()
        headers = self._headers

        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, required_status_code, msg=r.text)

        if required_status_code < 300:
            retval = r.json()
            self.assertGreater(retval['hashlistId'], 0)
            return r.json()
        else:
            return None


    def do_create_one(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        return self.do_create(payload, 201)


    def test_create_wildcard(self):
        for p in sorted(Path(__file__).parent.glob('create_hashlist_*.json')):
            payload = json.loads(p.read_text('UTF-8'))
            self.do_create(payload, 201)


    def test_create_binary_without_body(self):
        """ Should fail, since no sourceData is present """
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


    def test_get(self):
        uri = self.getURI()
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201, msg=r.text)


    def test_get_one(self):
        obj = self.do_create_one()
        uri = self.getURI(obj)
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201, msg=r.text)


    def do_patch(self, payload, required_status_code, hashlist_id=1):
        obj = self.do_create_one()
        uri = self.getURI(obj)
        headers = self._headers

        r = requests.patch(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, required_status_code, msg=r.text)

        if required_status_code != 500:
            for key in payload.keys():
                self.assertEqual(r.json()[key], payload[key], msg=r.text)


    def test_patch(self):
        stamp = datetime.datetime.now().isoformat()
        payload = {
            'name': f'MyList-{stamp}',
        }
        self.do_patch(payload, 201)


    def test_patch_null(self):
        # Change to null 
        payload = {
                'name': None,
        }
        self.do_patch(payload, 500)




if __name__ == '__main__':
    unittest.main()
