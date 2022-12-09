#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2 upload TUS
#
# Nice helper: $sudo justniffer -i lo -r

import json
import requests
import unittest
import datetime
import logging
from io import BytesIO
from pathlib import Path
import string
import random

import confidence
import tusclient.client

#logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

class Files(unittest.TestCase):
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
            'Authorization': 'Bearer ' + cls._token
        }

    def test_upload(self):
        uri = self._cfg['api_endpoint'] + '/ui/files/upload'
        my_client = tusclient.client.TusClient(uri)
        my_client.set_headers(self._headers)
        
        N = 1000
        #res = ''.join(random.choices(string.ascii_uppercase + string.digits, k=N))
        res = '\n'.join([f'Line-{i}' for i in range(N)])
        fs = BytesIO(res.encode('UTF-8'))
        metadata = {"filename": "foo.csv",
                    "filetype": "application/text"}
        uploader = my_client.uploader(
                file_stream=fs,
                chunk_size=4000,
                upload_checksum=True,
                metadata=metadata
                )
        logger.debug(uploader.get_headers())
        logger.debug(uploader.encode_metadata())
        uploader.upload()
        logger.debug(vars(uploader))
        self.assertEqual(uploader.stop_at, uploader.offset)



if __name__ == '__main__':
    unittest.main()
