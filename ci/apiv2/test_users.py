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
    
    def test_patch_user(self):
        pass

    def test_hidden_user(self):
        pass

    def test_delete_user(self):
        pass

    def test_exception_user(self):
        pass

    def test_expand_user(self):
        pass

if __name__ == '__main__':
    unittest.main()
