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

import hashtopolis
from hashtopolis import Hashlist 
from hashtopolis import Task

def do_create_hashlist():
    p = Path(__file__).parent.joinpath('create_hashlist_001.json')
    payload = json.loads(p.read_text('UTF-8'))
    hashlist = Hashlist(**payload)
    obj = hashlist.save()

    obj = Hashlist.objects.get(hashlistId=hashlist.id)

    assert obj.name == payload.get('name')
    return obj


class HashlistTest(unittest.TestCase):
    def test_create_hashlist(self):
        hashlist = do_create_hashlist()
        hashlist.delete()
    
    def test_patch_hashlist(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        stamp = datetime.datetime.now().isoformat()
        obj_name = f'Dummy Hashlist - {stamp}'
        hashlist.name = obj_name
        hashlist.save()

        obj = Hashlist.objects.get(hashlistId=hashlist.id)
        assert obj.name == obj_name

        hashlist.delete()

    def test_delete_hashlist(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        id = hashlist.id

        hashlist.delete()
        objs = Hashlist.objects.filter(hashlistId=id)

        assert objs == []

    def test_exception_hashlist(self):
        p = Path(__file__).parent.joinpath('create_hashlist_002.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        with self.assertRaises(hashtopolis.HashtopolisError):
            hashlist.save()

        with self.assertRaises(AttributeError):
            hashlist.id

        objs = Hashlist.objects.filter(name=payload.get('name'))
        assert objs == []

    def test_filter_archived(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        id = hashlist.id

        obj = Hashlist.objects.get(hashlistId=id, isArchived=False, expand=['hashType', 'accessGroup', 'hashes'])

        assert obj.id == id
        hashlist.delete()

    def test_expand_hashlist(self):
        p = Path(__file__).parent.joinpath('create_hashlist_001.json')
        payload = json.loads(p.read_text('UTF-8'))
        hashlist = Hashlist(**payload)
        hashlist.save()

        obj = Hashlist.objects.get(hashlistId=hashlist.id, expand='hashes')

        assert len(obj.hashes_set) == 1
        hashlist.delete()

if __name__ == '__main__':
    unittest.main()
