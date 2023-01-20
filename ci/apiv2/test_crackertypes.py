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


class CrackerTypes(utils.TestBase):
    def getBaseURI(self):
        return "/ui/crackertypes"
        
    def do_get(self):
        uri = self.getURI()
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201)

        return r.json()

    def do_create(self, payload, retval):
        uri = self._uri
        headers = self._headers

        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, retval, msg=r.text)
        return r.json()


    def do_delete(self, obj_uri, retval):
        uri = self.getURI(obj_uri)
        headers = self._headers
        payload = {}

        r = requests.delete(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, retval, msg=r.text)


    def test_get(self):
        uri = self.getURI()
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201)


    def test_get_first_one(self):
        uri = self.getURI(self.do_get()['values'][0])
        headers = self._headers
        payload = {}

        r = requests.get(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201, msg=uri)


    def test_patch(self):
        payload = { "typeName": "generic", "isChunkingAvailable": True}
        obj = self.do_create(payload, 201)

        uri = self.getURI(obj)
        headers = self._headers
        
        payload = {
            'typeName': f'hashcat2'
        }
        r = requests.patch(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201, msg=r.text)
        for key in payload.keys():
            self.assertEqual(r.json()[key], payload[key], msg=r.text)

        self.do_delete(obj, 204)
        

    def test_add_version_delete_object(self):
        """ Cascasing delete """
        # Create new crackertype
        payload = { "typeName": "generic", "isChunkingAvailable": True}
        obj = self.do_create(payload, 201)

        # Add cracker binary
        uri = self._cfg['hashtopolis_uri'] + '/api/v2/ui/crackers'
        headers = self._headers
        stamp = datetime.datetime.now().isoformat()
        payload = {
                "crackerBinaryTypeId": obj['_id'],
                "version": "0.0.1",
                "downloadUrl": "https://example.org/files/cracker-0.0.1.7z",
                "binaryName": f"dummy - {stamp}"
                }
        r = requests.post(uri, headers=headers, data=json.dumps(payload))
        self.assertEqual(r.status_code, 201, msg=r.text)

        # Delete cracker type
        self.do_delete(obj, 204)


    def test_create_wildcard(self):
        for p in sorted(Path(__file__).parent.glob('create_crackertypes_*.json')):
            payload = json.loads(p.read_text('UTF-8'))
            obj = self.do_create(payload, 201)
            self.do_delete(obj, 204)


if __name__ == '__main__':
    unittest.main()
