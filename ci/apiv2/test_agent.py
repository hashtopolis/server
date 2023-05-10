#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# PoC testing/development framework for APIv2
# Written in python to work on creation of hashtopolis APIv2 python binding.
#
import unittest
import datetime
import time

from hashtopolis_agent import TestAgent
from hashtopolis import Agent, Voucher

class Agents(unittest.TestCase):    

    def test_new_agent(self):
        stamp = int(time.time() * 1000)
        voucher = Voucher(voucher=f'dummy-test-{stamp}')
        obj = voucher.save()
        agent = TestAgent()
        agent.register(voucher=voucher.voucher, name=f'test-agent-{stamp}')
        agent.login()
        agent.get_task()
        agent.get_hashlist()
        agent.get_chunk()

        # No delete of voucher, should be automatically deleted when an test-agent claims the voucher
        #voucher.delete()

        # De-register agent

    def test_patch_agent(self):
        if len(Agent.objects.all()) == 0:
            # We cannot setup an agent through the API, for now skip this test of
            # no agent was created.
            return

        agent = Agent.objects.all()[0]
        old_name = agent.agentName
        new_name = f'agent-patch-{datetime.datetime.now().isoformat()}'
        agent.agentName = new_name
        agent.save()

        obj = Agent.objects.get(agentName=new_name)
        assert obj.agentName == new_name

        # Cleanup
        agent.agentName = old_name
