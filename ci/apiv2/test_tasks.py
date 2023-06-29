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

from hashtopolis import Hashlist
from hashtopolis import Task
from hashtopolis import HashtopolisConnector, HashtopolisConfig

import requests

from test_hashlists import do_create_hashlist


def do_create_task(hashlist):
    p = Path(__file__).parent.joinpath('create_task_001.json')
    payload = json.loads(p.read_text('UTF-8'))
    payload['hashlistId'] = int(hashlist._id)
    obj = Task(**payload)
    obj.save()
    return obj


class TaskTest(unittest.TestCase):
    def test_create_task(self):
        hashlist = do_create_hashlist()
        obj = do_create_task(hashlist)

        obj.delete()
        hashlist.delete()

    def test_expand_hashlists(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        for p in sorted(Path(__file__).parent.glob('create_task_001.json')):
            payload = json.loads(p.read_text('UTF-8'))
            payload['hashlistId'] = int(hashlist._id)
            obj = Task(**payload)
            obj.save()

        obj_test = Task().objects.filter(taskId=obj.id, expand='hashlist')[0]
        assert obj_test.hashlist_set.name == hashlist.name

        obj.delete()
        hashlist.delete()

    def test_patch(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        for p in sorted(Path(__file__).parent.glob('create_task_001.json')):
            payload = json.loads(p.read_text('UTF-8'))
            payload['hashlistId'] = int(hashlist._id)
            task = Task(**payload)
            task.save()

        stamp = datetime.datetime.now().isoformat()

        obj = Task.objects.get_first()
        obj.taskName = f'Dummy Task - {stamp}'
        obj.save()

        obj = Task.objects.get_first()

        self.assertEqual(obj.taskName, f'Dummy Task - {stamp}')

    def test_patch_color_null(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        for p in sorted(Path(__file__).parent.glob('create_task_001.json')):
            payload = json.loads(p.read_text('UTF-8'))
            payload['hashlistId'] = int(hashlist._id)
            task = Task(**payload)
            task.save()

        config = HashtopolisConfig()
        conn = HashtopolisConnector(f'/ui/tasks/{task.id}', config)
        conn.authenticate()
        uri = conn._api_endpoint + conn._model_uri
        headers = conn._headers
        payload = {"color": None}
        r = requests.patch(uri, headers=headers, data=json.dumps(payload))

        assert r.status_code == 201

        obj = Task.objects.get(taskId=task.id)

        assert obj.color == ''

        hashlist.delete()
        task.delete()

    def test_runtime(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        p = Path(__file__).parent.joinpath('create_task_002.json')
        payload = json.loads(p.read_text('UTF-8'))
        payload['hashlistId'] = int(hashlist._id)
        task = Task(**payload)
        task.save()

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.useNewBench, 0)

        obj.delete()
        hashlist.delete()

    def test_speed(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        p = Path(__file__).parent.joinpath('create_task_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        payload['hashlistId'] = int(hashlist._id)
        task = Task(**payload)
        task.save()

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.useNewBench, 1)

        obj.delete()
        hashlist.delete()

    def test_preprocessor(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        p = Path(__file__).parent.joinpath('create_task_003.json')
        payload = json.loads(p.read_text('UTF-8'))
        payload['hashlistId'] = int(hashlist._id)
        task = Task(**payload)
        task.save()

        id = task.id

        obj = Task.objects.get(taskId=id)
        self.assertEqual(obj.preprocessorCommand, "this-is-prepressor")
        self.assertEqual(obj.preprocessorId, 1)

        obj.delete()
        hashlist.delete()


if __name__ == '__main__':
    unittest.main()
