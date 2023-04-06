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

from hashtopolis import User
from hashtopolis import GlobalPermissionGroup


class UserTest(unittest.TestCase):
    def test_create_user(self):
        stamp = int(time.time())
        username = f'test-{stamp}'
        user = User(
            name = username,
            email = 'test@example.com',
            globalPermissionGroupId = 1
        )
        user.save()

        obj = User.objects.get(id=user.id)
        assert obj.name == user.name

        user.delete()
    
    def test_patch_user(self):
        stamp = int(time.time())
        username = f'test-{stamp}'
        groupname = f'gpg-{stamp}'

        user = User(
            name = username,
            email = 'test@example.com',
            globalPermissionGroupId = 1
        )
        user.save()
        
        globalpermissiongroup = GlobalPermissionGroup(
            name = groupname
        )
        globalpermissiongroup.save()

        user.globalPermissionGroupId = globalpermissiongroup.id
        user.save()

        obj = user.objects.get(id=user.id)

        assert obj.globalPermissionGroupId == globalpermissiongroup.id

        user.delete()
        globalpermissiongroup.delete()
    
    def test_disable_enable_user(self):
        stamp = int(time.time())
        username = f'test-{stamp}'

        user = User(
            name = username,
            email = 'test@example.com',
            globalPermissionGroupId = 1,
        )
        user.save()
        id = user.id

        # Test disabling user
        user.isValid = False
        user.save()

        obj = User.objects.get(id=id)
        assert obj.isValid == False

        # Test disabling disabled user
        user = User.objects.get(id=id)
        user.isValid = False
        user.save()

        obj = User.objects.get(id=id)
        assert obj.isValid == False

        # Test enabling user
        user = User.objects.get(id=id)
        user.isValid = True
        user.save()

        obj = User.objects.get(id=id)
        assert obj.isValid == True

        # Test enabling enabled user
        user = User.objects.get(id=id)
        user.isValid = True
        user.save()

        obj = User.objects.get(id=id)
        assert obj.isValid == True


    def test_delete_user(self):
        stamp = int(time.time())
        username = f'test-{stamp}'

        user = User(
            name = username,
            email = 'test@example.com',
            globalPermissionGroupId = 1,
        )
        user.save()
        id = user.id

        user.delete()

        objs = user.objects.filter(id=id)
        assert len(objs) == 0

    def test_expand_user(self):
        pass

if __name__ == '__main__':
    unittest.main()
