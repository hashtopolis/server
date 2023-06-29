#!/usr/bin/env python
# -*- coding: utf-8 -*-
import unittest

class BaseTest(unittest.TestCase):
    @classmethod
    def setUp(cls):
        cls.obj_heap = []

    @classmethod
    def tearDown(cls):
        while len(cls.obj_heap) > 0:
            obj = cls.obj_heap.pop()
            obj.delete()

    def delete_after_test(self, obj):
        self.obj_heap.append(obj)

