#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import json
import unittest
import datetime
from pathlib import Path
from io import BytesIO

from hashtopolis import FileImport
from hashtopolis import File


class TasksTest(unittest.TestCase):
    def test_do_upload(self):
        stamp = datetime.datetime.now().isoformat()
        filename = f'test-{stamp}.txt'
        file_import = FileImport()
        text = '12345678\n123456\nprincess\n'.encode('utf-8')
        fs = BytesIO(text)
        file_import.do_upload(filename, fs)

        p = Path(__file__).parent.joinpath('create_file_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        payload['sourceData'] = filename
        payload['filename'] = filename
        file_obj = File(**payload)
        file_obj.save()

        file_obj2 = File()

        assert len(file_obj2.objects.filter(filename=filename)) == 1
        file_obj.delete()
