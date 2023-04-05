#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import json
import unittest
import time
from pathlib import Path

from hashtopolis import GlobalPermissionGroup 

class GlobalPermissionGroupsTest(unittest.TestCase):
    def test_create_globalpermissiongroup(self):
        stamp = int(time.time())
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
            permissions={'viewHashlistAccess':True}
        )
        obj = globalpermissiongroup.save()

        obj = GlobalPermissionGroup.objects.get(id=globalpermissiongroup.id)
        assert obj.name == globalpermissiongroup.name
        globalpermissiongroup.delete()
    
    def test_patch_globalpermissiongroup(self):
        stamp = int(time.time())
        permissions = {'viewHashlistAccess':True}
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
            permissions = permissions
        )
        globalpermissiongroup.save()
        assert globalpermissiongroup.permissions == permissions

        globalpermissiongroup.permissions = {}
        globalpermissiongroup.permissions['viewHashlistAccess'] = False
        globalpermissiongroup.save()

        obj = GlobalPermissionGroup.objects.get(id=globalpermissiongroup.id)

        assert obj.permissions['viewHashlistAccess'] == globalpermissiongroup.permissions['viewHashlistAccess']

        globalpermissiongroup.delete()

    def test_delete_globalpermissiongroup(self):
        stamp = int(time.time())
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
        )
        globalpermissiongroup.save()

        id = globalpermissiongroup.id

        globalpermissiongroup.delete()

        objs = globalpermissiongroup.objects.filter(id=id)
        assert objs == []

    def test_exception_globalpermissiongroup(self):
        stamp = int(time.time())
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
            permissions='test',
        )
        globalpermissiongroup.save()
        with self.assertRaises(AttributeError):
            globalpermissiongroup.id


if __name__ == '__main__':
    unittest.main()
