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

from hashtopolis import Pretask
from hashtopolis import File
from hashtopolis import FileImport


class PretaskTest(unittest.TestCase):
    def test_create_pretask(self):
        p = Path(__file__).parent.joinpath('create_pretask_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        pretask = Pretask(**payload)
        obj = pretask.save()

        obj = Pretask.objects.get(pretaskId=pretask.id)

        assert obj.taskName == payload.get('taskName')

        pretask.delete()

    def test_patch_pretask(self):
        p = Path(__file__).parent.joinpath('create_pretask_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        pretask = Pretask(**payload)
        pretask.save()

        stamp = datetime.datetime.now().isoformat()
        obj_name = f'Dummy Pretask - {stamp}'
        pretask.taskName = obj_name
        pretask.save()

        obj = Pretask.objects.get(pretaskId=pretask.id)
        assert obj.taskName == obj_name

        pretask.delete()

    def test_delete_pretask(self):
        p = Path(__file__).parent.joinpath('create_pretask_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        pretask = Pretask(**payload)
        pretask.save()

        id = pretask.id

        pretask.delete()
        objs = Pretask.objects.filter(pretaskId=id)

        assert objs == []

    def test_expand_pretask_files(self):
        # Create file
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

        # Create pretask
        p = Path(__file__).parent.joinpath('create_pretask_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        payload['files'] = [file_obj.id]
        pretask = Pretask(**payload)
        pretask.save()

        to_check = pretask.id

        objects = Pretask.objects.filter(pretaskId=to_check, expand='pretaskFiles')
        assert objects[0].pretaskFiles_set[0].filename == filename

        pretask.delete()
        file_obj.delete()


if __name__ == '__main__':
    unittest.main()
