import pytest
from unittest import mock
import unittest
from unittest.mock import MagicMock
import os
import subprocess
import shutil
import requests
import json
from pathlib import Path
from argparse import Namespace
import sys

from hashtopolis import Hashlist as Hashlist_v2
from hashtopolis import Task as Task_v2
from hashtopolis import AccessGroup as AccessGroup_v2

class Expand(unittest.TestCase):

    def test_task_crackerbinary_o2o(self):
        # Create hashlist
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist_v2 = Hashlist_v2(**payload)
        hashlist_v2.save()

        # Create Task
        p = Path(__file__).parent.joinpath('create_task_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        payload['hashlistId'] = int(hashlist_v2._id)
        task_v2 = Task_v2(**payload)
        task_v2.save()

        to_check = task_v2.id

        # One-to-one casting
        obj = Task_v2()
        objects = obj.objects.filter(taskId=to_check,expand='crackerBinary')
        assert objects[0].crackerBinary_set.binaryName == 'hashcat'

        hashlist_v2.delete()
        task_v2.delete()
    
    def test_accessgroups_usermembers_m2m(self):
        # Many-to-many casting
        obj = AccessGroup_v2()
        objects = obj.objects.all(expand='userMembers')

        # Check the default account
        assert objects[0].userMembers_set[0].name == 'root'
