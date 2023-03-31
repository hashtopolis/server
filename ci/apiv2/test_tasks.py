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

class TasksTest(unittest.TestCase):
    def test_create_task(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        for p in sorted(Path(__file__).parent.glob('create_task_001.json')):
            payload = json.loads(p.read_text('UTF-8'))
            payload['hashlistId'] = int(hashlist._id)
            obj = Task(**payload)
            obj.save()


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

        obj_test = Task().objects.filter(taskId=obj.id,expand='hashlist')[0]
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

        id = task.id

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

        id = task.id

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
