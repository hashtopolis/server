#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import unittest

from hashtopolis import Config 


class Configs(unittest.TestCase):
    def test_patch_config(self):
        config = Config()
        obj = config.objects.get(item='hashcatBrainEnable')
        obj.value = "0"
        obj.save()

        assert obj.value == "0"
        obj.value = "1"
        obj.save()

        obj2 = config.objects.get(item='hashcatBrainEnable')
        assert obj2.value == "1"

        obj2.value = "0"
        obj.save() 


if __name__ == '__main__':
    unittest.main()