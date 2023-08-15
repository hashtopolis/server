#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import json
import datetime
from pathlib import Path

from utils import BaseTest
from hashtopolis import Task

from test_hashlists import do_create_hashlist


def do_create_task(hashlist, file_id='001'):
    p = Path(__file__).parent.joinpath(f'create_task_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    payload['hashlistId'] = int(hashlist.id)
    obj = Task(**payload)
    obj.save()
    return obj


class TaskTest(BaseTest):
    def test_create_task(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

    def test_expand_hashlists(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

        obj_test = Task().objects.filter(taskId=task.id, expand='hashlist')[0]
        self.assertEqual(obj_test.hashlist_set.name, hashlist.name)

    def test_patch(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

        stamp = datetime.datetime.now().isoformat()

        task_name = f'Dummy Task - {stamp}'
        task.taskName = task_name
        task.save()

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.taskName, task_name)

    def test_patch_color_null(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

        task.color = None
        task.save()

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.color, '')

    def test_runtime(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist, '002')
        self.delete_after_test(task)

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.useNewBench, 0)

    def test_speed(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist, '001')
        self.delete_after_test(task)

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.useNewBench, 1)

    def test_preprocessor(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist, '001')
        self.delete_after_test(task)

        obj = Task.objects.get(taskId=task.id)
        self.assertEqual(obj.preprocessorCommand, "this-is-prepressor")
        self.assertEqual(obj.preprocessorId, 1)

    def test_archive_filter(self):
        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist, '001')
        self.delete_after_test(task)

        task.isArchived = True
        task.save()

        # Use filter to get archived tasks
        test_obj = Task.objects.filter(taskId=task.id, isArchived=True)

        self.assertEqual(len(test_obj), 1)
        self.assertTrue(test_obj[0].isArchived)
