#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#

import requests
import unittest
from pathlib import Path
import abc

import confidence

class TestBase(unittest.TestCase, abc.ABC):
    @abc.abstractmethod
    def getBaseURI(self):
        pass

    def getURI(self, obj_id = None):
        if obj_id is None:
            # Base object
            return self._uri
        elif type(obj_id) is dict and '_self' in obj_id:
            # Identification by Uniform URI indentification (_self)
            return self._cfg['hashtopolis_uri'] + obj_id['_self']
        elif type(obj_id) is int:
            # Identification by ID
            return f'{self._uri}/{obj_id}'
        else:
            # Identification by Uniform URI (string)
            return self._cfg['hashtopolis_uri'] + obj_id

    @classmethod
    def setUpClass(cls):
        # Request access TOKEN, used throughout the test
        load_order = confidence.DEFAULT_LOAD_ORDER + (str(Path(__file__).parent.joinpath('{name}.{extension}')),)
        cls._cfg = confidence.load_name('hashtopolis-test', load_order=load_order)
        cls._api_endpoint = cls._cfg['hashtopolis_uri'] + '/api/v2'
        cls._uri = cls._api_endpoint + cls.getBaseURI(cls)
        

        auth_uri = cls._api_endpoint + '/auth/token'
        auth = (cls._cfg['username'], cls._cfg['password'])
        r = requests.post(auth_uri, auth=auth)

        cls._token = r.json()['token']
        cls._token_expires = r.json()['expires']

        cls._headers = {
            'Authorization': 'Bearer ' + cls._token,
            'Content-Type': 'application/json'
        }