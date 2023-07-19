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

from hashtopolis import CrackerType
from hashtopolis import HashtopolisError


class CrackerTypes(unittest.TestCase):
    def test_create_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)
        crackertype.save()

        obj = CrackerType.objects.get(crackerBinaryTypeId=crackertype.id)
        assert obj.typeName == payload.get('typeName')

        crackertype.delete()

    def test_patch_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)
        crackertype.save()

        stamp = datetime.datetime.now().day
        obj_name = f'hashcat{stamp}'
        crackertype.typeName = obj_name
        crackertype.save()

        obj = CrackerType.objects.get(crackerBinaryTypeId=crackertype.id)
        assert obj.typeName == obj_name

        crackertype.delete()

    def test_delete_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)
        crackertype.save()

        id = crackertype.id

        crackertype.delete()

        objs = CrackerType.objects.filter(crackerBinaryTypeId=id)

        assert objs == []

    def test_exception_crackertype(self):
        p = Path(__file__).parent.joinpath('create_crackertype_002.json')
        payload = json.loads(p.read_text('UTF-8'))
        crackertype = CrackerType(**payload)

        with self.assertRaises(HashtopolisError) as e:
            crackertype.save()
        assert e.exception.args[1] == 'Creation of object failed'
        assert 'is not of type string' in e.exception.args[4]


if __name__ == '__main__':
    unittest.main()
