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

from hashtopolis import Cracker


class Crackers(unittest.TestCase):
    def test_create_cracker(self):
        p = Path(__file__).parent.joinpath('create_cracker_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        cracker = Cracker(**payload)
        cracker.save()

        obj = Cracker.objects.get(crackerBinaryId=cracker.id)
        assert obj.binaryName == payload.get('binaryName')

        cracker.delete()

    def test_patch_cracker(self):
        p = Path(__file__).parent.joinpath('create_cracker_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        cracker = Cracker(**payload)
        cracker.save()

        stamp = datetime.datetime.now().isoformat()
        obj_name = f'Dummy Cracker - {stamp}'
        cracker.binaryName = obj_name
        cracker.save()

        obj = cracker.objects.get(crackerBinaryId=cracker.id)
        assert obj.binaryName == obj_name

        cracker.delete()

    def test_delete_cracker(self):
        p = Path(__file__).parent.joinpath('create_cracker_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        cracker = Cracker(**payload)
        cracker.save()

        id = cracker.id

        cracker.delete()

        objs = Cracker.objects.filter(crackerBinaryId=id)

        assert objs == []

    def test_exception_cracker(self):
        p = Path(__file__).parent.joinpath('create_cracker_002.json')
        payload = json.loads(p.read_text('UTF-8'))
        cracker = Cracker(**payload)
        cracker.save()

        with self.assertRaises(AttributeError):
            cracker.id


if __name__ == '__main__':
    unittest.main()
