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
        pass

    def test_delete_globalpermissiongroup(self):
        pass

    def test_exception_globalpermissiongroup(self):
        pass

if __name__ == '__main__':
    unittest.main()
