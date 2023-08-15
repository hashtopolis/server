#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
from utils import BaseTest
from hashtopolis import Speed

from test_hashlists import do_create_hashlist
from test_tasks import do_create_task
from test_agent import do_create_agent


class SpeedTest(BaseTest):
    def test_speed_value(self):
        dummy_agent, agent = do_create_agent()
        self.delete_after_test(agent)

        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist)
        self.delete_after_test(task)

        #TODO: Assign agent to task
        dummy_agent.get_task()
        self.assertEqual(dummy_agent.task['hashlistId'], hashlist.id,
                         "Hashlist created is not being working on by agent!")
        dummy_agent.get_hashlist()

        dummy_agent.get_chunk()
        dummy_agent.send_keyspace()
        dummy_agent.get_chunk()
        dummy_agent.send_benchmark()
        dummy_agent.get_chunk()
        dummy_agent.send_process(progress=50)

        self.assertTrue(Speed.objects.filter(taskId=task.id))
