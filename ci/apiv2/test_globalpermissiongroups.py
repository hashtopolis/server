#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import unittest
import time

from hashtopolis import GlobalPermissionGroup
from hashtopolis import User
from hashtopolis import HashtopolisError


class GlobalPermissionGroupsTest(unittest.TestCase):
    def test_create_globalpermissiongroup(self):
        stamp = int(time.time() * 1000)
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
            permissions={'viewHashlistAccess': True}
        )
        obj = globalpermissiongroup.save()

        obj = GlobalPermissionGroup.objects.get(id=globalpermissiongroup.id)
        assert obj.name == globalpermissiongroup.name
        globalpermissiongroup.delete()

    def test_patch_globalpermissiongroup(self):
        stamp = int(time.time() * 1000)
        permissions = {'viewHashlistAccess': True}
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
            permissions=permissions
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
        stamp = int(time.time() * 1000)
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
        )
        globalpermissiongroup.save()

        id = globalpermissiongroup.id

        globalpermissiongroup.delete()

        objs = globalpermissiongroup.objects.filter(id=id)
        assert objs == []

    def test_exception_globalpermissiongroup(self):
        stamp = int(time.time() * 1000)
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
            permissions='test',
        )

        with self.assertRaises(HashtopolisError) as e:
            globalpermissiongroup.save()
        assert e.exception.args[1] == 'Creation of object failed'
        assert 'is not of type dict' in e.exception.args[4]

    def test_expand_globalpermissiongroup(self):
        stamp = int(time.time() * 1000)
        globalpermissiongroup = GlobalPermissionGroup(
            name=f'test-{stamp}',
        )
        globalpermissiongroup.save()

        user = User(
            name=f'user-{stamp}',
            email='test@example.com',
            globalPermissionGroupId=globalpermissiongroup.id,
        )
        user.save()

        obj = GlobalPermissionGroup.objects.get(id=globalpermissiongroup.id, expand='user')
        assert len(obj.user_set) > 0
        assert obj.user_set[0].name == user.name


if __name__ == '__main__':
    unittest.main()
